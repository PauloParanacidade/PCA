<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $guid
 * @property string|null $username
 * @property string|null $domain
 * @property string|null $manager
 * @property string|null $department
 * @property string|null $employeeNumber
 * @property bool $isExterno
 * @property bool $active
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $area_responsavel_formatada
 * @property-read string $area_solicitante_formatada
 * @property-read mixed $nome_gestor
 * @property-read mixed $sigla_area_gestor
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmployeeNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsExterno($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereManager($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable implements LdapAuthenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;
    use AuthenticatesWithLdap;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'manager',
        'department',
        'employeeNumber',
        'dataAssinaturaTermo',
        'nomeSetor',
        'isExterno',
        'orgao',
        'cpf',
        'telefone',
        'empresa',
        'cargo',
        'data_nascimento',
        'status',
        'active',
        'guid',
        'domain',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'active'            => 'boolean',
            'isExterno'         => 'boolean',
        ];
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getLdapAuthIdentifierName()
    {
        return 'username';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getLdapAuthIdentifier()
    {
        return $this->username;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getLdapAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the LDAP domain for the user.
     *
     * @return string
     */
    public function getLdapDomain(): string
    {
        return $this->domain ?? '';
    }

    /**
     * Get the LDAP GUID for the user.
     *
     * @return string
     */
    public function getLdapGuid(): string
    {
        return $this->guid ?? '';
    }

    /**
     * Relacionamento many-to-many com roles
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Verifica se o usuário tem um role específico
     * 
     * @param string|array $role Nome do role ou array de roles
     * @return bool
     */
    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }
        
        // Garantir que sempre trabalhamos com Collection
        $roleNames = collect($role)->map(function($r) {
            return is_string($r) ? $r : (is_object($r) ? $r->name : $r);
        });
        
        return $this->roles->pluck('name')->intersect($roleNames)->isNotEmpty();
    }

    /**
     * Verifica se o usuário tem pelo menos um dos roles especificados
     * 
     * @param string|array $roles Nome do role ou array de roles
     * @return bool
     */
    public function hasAnyRole($roles): bool
    {
        if (is_string($roles)) {
            return $this->hasRole($roles);
        }
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        return false;
    }



/**
 * Garante que o usuário tenha o papel de gestor
 * Atribui automaticamente o role 'gestor' se não tiver
 */
public function garantirPapelGestor(): void
{
    // Verificar se já tem algum role de gestão
    if (!$this->hasAnyRole(['admin', 'daf', 'gestor', 'secretaria'])) {
        // Buscar o role 'gestor'
        $gestorRole = \App\Models\Role::where('name', 'gestor')->first();
        
        if ($gestorRole) {
            // Atribuir o role gestor mantendo os roles existentes
            $this->roles()->syncWithoutDetaching([$gestorRole->id]);
            
            \Illuminate\Support\Facades\Log::info("Role 'gestor' atribuído automaticamente", [
                'user_id' => $this->id,
                'user_name' => $this->name,
                'context' => 'garantirPapelGestor'
            ]);
        } else {
            \Illuminate\Support\Facades\Log::error("Role 'gestor' não encontrado no sistema", [
                'user_id' => $this->id,
                'user_name' => $this->name
            ]);
        }
    }
}

    public function getAreaResponsavelFormatadaAttribute(): string
    {
        $manager = $this->manager;

        if (!$manager) {
            return '';
        }

        if (preg_match('/CN=([^,]+),OU=([^,]+)/', $manager, $matches)) {
            $nome = $matches[1];
            $sigla = $matches[2];
            return "{$sigla} - {$nome}";
        }

        return '';
    }

        public function getAreaSolicitanteFormatadaAttribute(): string
    {
        $sigla = $this->department; // ex: "CTI"
        $nome = $this->name;        // ex: "Jose da Silva"

        if (!$sigla || !$nome) {
            return '';
        }

        return "{$sigla} - {$nome}";
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPassword($token));
    }

    /**
     * Extrai o nome do gestor do campo manager
     */
    public function getNomeGestorAttribute()
    {
        if (!$this->manager) {
            return null;
        }
        
        if (preg_match('/CN=([^,]+)/', $this->manager, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }

    /**
     * Extrai a sigla da área do gestor do campo manager
     */
    public function getSiglaAreaGestorAttribute()
    {
        if (!$this->manager) {
            return null;
        }
        
        if (preg_match('/OU=([^,]+)/', $this->manager, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }
}
