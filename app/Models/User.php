<?php

namespace App\Models;

use Database\Factories\UserFactory;
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
        'avatar',
        'is_super_admin', // ✅ নতুন
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'password'           => 'hashed',
            'is_super_admin'     => 'boolean', // ✅ নতুন
        ];
    }

    // ── AdminLTE User Menu ──
    // storage:link CHARA kaj kore: notun photo public/uploads/ e thake,
    // purono (avatars/...) photo public/uploads ba public/storage — jekhanei thakuk — dekhabe.
    public function adminlte_image()
    {
        $fallback = 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=007bff&color=fff&rounded=true&size=128';

        if (! $this->avatar) {
            return $fallback;
        }

        $avatar = ltrim($this->avatar, '/');

        // Notun format: uploads/avatars/xxx.jpg → sorasori public/ theke serve
        if (str_starts_with($avatar, 'uploads/')) {
            return is_file(public_path($avatar)) ? asset($avatar) : $fallback;
        }

        // Purono format: avatars/xxx.jpg → prothome public/uploads, tarpor public/storage (symlink) e khunjun
        foreach (['uploads/' . $avatar, 'storage/' . $avatar] as $publicPath) {
            if (is_file(public_path($publicPath))) {
                return asset($publicPath);
            }
        }

        return $fallback;
    }

    public function adminlte_desc()
    {
        return $this->is_super_admin
            ? '👑 Super Admin'
            : $this->email;
    }

    public function adminlte_profile_url()
    {
        return route('profile');
    }

    // ── Role & Permission ──
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    public function hasRole($role): bool
    {
        // ✅ Super Admin সব role এ আছে বলে গণ্য হবে
        if ($this->isSuperAdmin()) return true;

        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }
        return $this->roles->contains($role);
    }

    public function hasPermission($permission): bool
    {
        // ✅ Super Admin সব permission পাবে
        if ($this->isSuperAdmin()) return true;

        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }
        $this->roles()->syncWithoutDetaching($role);
    }

    public function removeRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }
        $this->roles()->detach($role);
    }
}