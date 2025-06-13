<?php
namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserRoleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $users  = User::with('roles')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10);

        $roles = Role::all();

        return view('admin.users.roles', compact('users', 'roles', 'search'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'roles'   => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->roles()->sync($request->roles);

        return redirect()->back()->with('success', 'Permissões atualizadas com sucesso!');
    }

    public function massUpdate(Request $request)
    {
        $request->validate([
            'user_ids'   => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'roles'      => 'required|array',
            'roles.*'    => 'exists:roles,id',
        ]);

        // Encontrar o role de admin
        $adminRole = Role::where('name', 'admin')->first();

        User::whereIn('id', $request->user_ids)->each(function ($user) use ($request, $adminRole) {
            // Se o usuário já tem role admin, mantém
            if ($adminRole && $user->roles->contains($adminRole->id)) {
                $newRoles = collect($request->roles)
                    ->push($adminRole->id)
                    ->unique()
                    ->toArray();
                $user->roles()->sync($newRoles);
            }
            // Se o usuário não tem role admin, não adiciona
            else {
                $newRoles = collect($request->roles)
                    ->reject(function ($roleId) use ($adminRole) {
                        return $adminRole && $roleId == $adminRole->id;
                    })
                    ->toArray();
                $user->roles()->sync($newRoles);
            }
        });

        return redirect()->back()->with('success', 'Permissões atualizadas em massa com sucesso!');
    }

    public function toggleActive(User $user)
    {
        // Não permite desativar usuário admin
        if ($user->hasRole('admin')) {
            return redirect()->back()->with('error', 'Não é possível desativar um usuário administrador.');
        }

        $user->active = ! $user->active;
        $user->save();

        $status = $user->active ? 'ativado' : 'desativado';
        return redirect()->back()->with('success', "Usuário {$status} com sucesso!");
    }

    /**
     * Exclui um usuário externo
     *
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        // Verifica se é um usuário externo
        if (! $user->isExterno) {
            return redirect()->back()->with('error', 'Apenas usuários externos podem ser excluídos.');
        }

        // Não permite excluir usuário admin
        if ($user->hasRole('admin')) {
            return redirect()->back()->with('error', 'Não é possível excluir um usuário administrador.');
        }

        try {
            $user->delete();
            return redirect()->back()->with('success', 'Usuário excluído com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir usuário: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao excluir usuário. Por favor, tente novamente.');
        }
    }

    /**
     * Sincroniza usuários do LDAP com o banco de dados local
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function syncLdapUsers()
    {
        try {
            $ldapUsers = \LdapRecord\Models\ActiveDirectory\User::get();
            $stats     = [
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'reasons' => [
                    'sem_username'       => 0,
                    'sem_nome_email'     => 0,
                    'erro_criacao'       => 0,
                    'erro_processamento' => 0,
                ],
            ];

            Log::info('Iniciando sincronização LDAP. Total de usuários no LDAP: ' . $ldapUsers->count());

            foreach ($ldapUsers as $ldapUser) {
                try {
                    $username = $ldapUser->samaccountname[0] ?? null;

                    if (! $username) {
                        $this->logSkippedUser('sem_username', 'Usuário LDAP sem samaccountname encontrado', $stats);
                        continue;
                    }

                    $userData = $this->prepareUserData($ldapUser, $username);

                    if (! $this->validateUserData($userData, $username, $stats)) {
                        continue;
                    }

                    $this->processUser($userData, $username, $stats);
                } catch (\Exception $e) {
                    $this->logSkippedUser('erro_processamento', "Erro ao processar usuário LDAP: " . $e->getMessage(), $stats);
                }
            }

            return $this->returnSyncResults($stats);
        } catch (\Exception $e) {
            Log::error('Erro na sincronização LDAP: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao sincronizar com o LDAP: ' . $e->getMessage());
        }
    }

    /**
     * Prepara os dados do usuário a partir do LDAP
     *
     * @param \LdapRecord\Models\ActiveDirectory\User $ldapUser
     * @param string $username
     * @return array
     */
    private function prepareUserData($ldapUser, $username)
    {
        return [
            'name'                  => $ldapUser->displayname[0] ?? null,
            'email'                 => $ldapUser->mail[0] ?? null,
            'username'              => $username,
            'manager'               => $ldapUser->manager[0] ?? null,
            'department'            => $ldapUser->departmentnumber[0] ?? null,
            'employeeNumber'        => $ldapUser->employeenumber[0] ?? null,
            'active'                => true,
            'force_password_change' => true,
        ];
    }

    /**
     * Valida os dados obrigatórios do usuário
     *
     * @param array $userData
     * @param string $username
     * @param array $stats
     * @return bool
     */
    private function validateUserData($userData, $username, &$stats)
    {
        if (empty($userData['name']) || empty($userData['email'])) {
            $this->logSkippedUser('sem_nome_email', "Usuário {$username} sem nome ou email", $stats);
            Log::warning("Dados do usuário: " . json_encode($userData));
            return false;
        }
        return true;
    }

    /**
     * Processa o usuário (cria ou atualiza)
     *
     * @param array $userData
     * @param string $username
     * @param array $stats
     */
    private function processUser($userData, $username, &$stats)
    {
        $user = User::where('username', $username)->first();

        if ($user) {
            $user->update($userData);
            $stats['updated']++;
            Log::info("Usuário atualizado: {$username}");
        } else {
            try {
                $userData['password'] = bcrypt(Str::random(32));
                User::create($userData);
                $stats['created']++;
                Log::info("Novo usuário criado: {$username}");
            } catch (\Exception $e) {
                $this->logSkippedUser('erro_criacao', "Erro ao criar usuário {$username}: " . $e->getMessage(), $stats);
                Log::error("Dados do usuário: " . json_encode($userData));
            }
        }
    }

    /**
     * Registra um usuário pulado e atualiza as estatísticas
     *
     * @param string $reason
     * @param string $message
     * @param array $stats
     */
    private function logSkippedUser($reason, $message, &$stats)
    {
        Log::warning($message);
        $stats['skipped']++;
        $stats['reasons'][$reason]++;
    }

    /**
     * Retorna o resultado da sincronização
     *
     * @param array $stats
     * @return \Illuminate\Http\RedirectResponse
     */
    private function returnSyncResults($stats)
    {
        Log::info("Sincronização concluída. Criados: {$stats['created']}, Atualizados: {$stats['updated']}, Pulados: {$stats['skipped']}");
        Log::info("Motivos dos pulos: " . json_encode($stats['reasons']));

        $message = "Sincronização concluída! {$stats['created']} usuários criados e {$stats['updated']} atualizados.";

        return redirect()->back()->with('success', $message);
    }

    /**
     * Cria um novo usuário externo
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles'    => 'required|array',
            'roles.*'  => 'exists:roles,id',
        ]);

        try {
            // Gera o username a partir do email (parte antes do @)
            $username = explode('@', $request->email)[0];

            $user = User::create([
                'name'                  => $request->name,
                'email'                 => $request->email,
                'username'              => $username,
                'password'              => bcrypt($request->password),
                'active'                => true,
                'force_password_change' => false,
                'isExterno'             => true,
            ]);

            $user->roles()->sync($request->roles);

            return redirect()->back()->with('success', 'Usuário externo criado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao criar usuário externo: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao criar usuário externo. Por favor, tente novamente.');
        }
    }
}
