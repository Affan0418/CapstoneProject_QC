<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'branch_id',
        'agency_id',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public const ROLE_REGION = 'region';
    public const ROLE_BRANCH = 'admin_branch';
    public const ROLE_AGENCY = 'agency';

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function homeRoute(): string
    {
        return match ($this->role) {
            self::ROLE_REGION => 'region.dashboard',
            self::ROLE_BRANCH => 'branch.dashboard',
            self::ROLE_AGENCY => 'agency.dashboard',
            default           => 'login',
        };
    }
}
