<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

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

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }
        return ! ! $role->intersect($this->roles)->count();
    }

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

/**
 * Garante que o usuário tenha o papel de gestor
 * Se não tiver, atribui automaticamente
 */
public function garantirPapelGestor(): void
{
    if (!$this->hasRole('gestor')) {
        $gestorRole = \App\Models\Role::where('name', 'gestor')->first();
        
        if ($gestorRole) {
            $this->roles()->attach($gestorRole->id);
            \Illuminate\Support\Facades\Log::info('Papel de gestor atribuído automaticamente', [
                'user_id' => $this->id,
                'user_name' => $this->name,
                'role_id' => $gestorRole->id
            ]);
        }
    }
}
}
