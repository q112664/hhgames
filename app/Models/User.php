<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\PanelRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'is_admin', 'panel_role', 'avatar_path'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token', 'avatar_path'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = ['avatar', 'panel_role_label'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin' => 'boolean',
            'panel_role' => PanelRole::class,
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Resolve the public avatar URL for the current user.
     */
    protected function avatar(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->avatar_path
            ? Storage::disk('public')->url($this->avatar_path)
            : null);
    }

    /**
     * Determine whether the user can access the given Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() !== 'admin') {
            return true;
        }

        return $this->is_admin === true;
    }

    /**
     * Determine whether the user is a super admin for backstage management.
     */
    public function isSuperAdmin(): bool
    {
        return $this->panel_role === PanelRole::SuperAdmin
            || ($this->is_admin === true && $this->panel_role === null);
    }

    /**
     * Resolve the human-readable panel role label.
     */
    protected function panelRoleLabel(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->panel_role instanceof PanelRole) {
                return $this->panel_role->label();
            }

            if ($this->is_admin === true) {
                return PanelRole::SuperAdmin->label();
            }

            return null;
        });
    }

    /**
     * Get the resources liked by the user.
     */
    public function likedResources(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class, 'resource_user_likes')
            ->withTimestamps();
    }
}
