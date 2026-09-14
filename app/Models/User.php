<?php

namespace App\Models;

use App\Casts\AsUserSettings;
use App\Notifications\EmailVerificationCode;
use App\Support\TeamContext;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'meta', 'avatar_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, HasUlids, Notifiable;

    /**
     * The user's avatar URL: uploaded avatar, otherwise Gravatar.
     */
    public function avatarUrl(): ?string
    {
        if ($this->avatar_path) {
            return Storage::disk('public')->url($this->avatar_path);
        }

        return 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($this->email))).'?s=96&d=404';
    }

    /**
     * Initials derived from the user's name (up to two letters).
     */
    public function initials(): string
    {
        $letters = array_map(
            fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)),
            array_slice(preg_split('/\s+/', trim($this->name) ?: '?'), 0, 2),
        );

        return implode('', $letters) ?: '?';
    }

    /**
     * Default attribute values for new model instances.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'meta' => '{}',
    ];

    /**
     * @return BelongsToMany<Team, $this>
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->withTimestamps();
    }

    /**
     * @return HasMany<Team, $this>
     */
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    public function belongsToTeam(Team $team): bool
    {
        return $this->teams()->whereKey($team->getKey())->exists();
    }

    /**
     * The user's active team (session-backed, defaulting to their first team).
     */
    public function currentTeam(): ?Team
    {
        $teams = $this->teams;

        if ($teamId = session('current_team_id')) {
            $team = $teams->firstWhere('id', $teamId);

            if ($team) {
                return $team;
            }

            session()->forget('current_team_id');
        }

        $team = $teams->sortBy('created_at')->first();

        if ($team) {
            session(['current_team_id' => $team->getKey()]);
        }

        return $team;
    }

    /**
     * Set the user's active team in the session.
     */
    public function setCurrentTeam(Team $team): void
    {
        session(['current_team_id' => $team->getKey()]);
    }

    public function hasRoleInTeam(Team $team, string|array $roles): bool
    {
        return TeamContext::run($team, function () use ($roles): bool {
            $this->unsetRelation('roles')->unsetRelation('permissions');

            return $this->hasRole($roles);
        });
    }

    public function hasPermissionInTeam(Team $team, string $permission): bool
    {
        return TeamContext::run($team, function () use ($permission): bool {
            $this->unsetRelation('roles')->unsetRelation('permissions');

            return $this->hasPermissionTo($permission);
        });
    }

    public function roleInTeam(Team $team): ?string
    {
        return TeamContext::run($team, function () {
            $this->unsetRelation('roles')->unsetRelation('permissions');

            return $this->roles->first()?->name;
        });
    }

    /**
     * Send the email verification notification with a fresh confirmation code.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new EmailVerificationCode($this->issueEmailConfirmationCode()));
    }

    /**
     * Generate and persist a new 6-digit confirmation code, returning the
     * plain code so it can be delivered to the user.
     */
    public function issueEmailConfirmationCode(): string
    {
        $code = (string) random_int(100000, 999999);

        $this->forceFill([
            'email_confirmation_code' => Hash::make($code),
            'email_confirmation_code_expires_at' => now()->addMinutes(10),
        ])->save();

        return $code;
    }

    /**
     * Validate a confirmation code and activate the account if it matches
     * and has not expired.
     */
    public function verifyEmailConfirmationCode(string $code): bool
    {
        if ($this->email_confirmation_code_expires_at?->isPast()) {
            return false;
        }

        if ($this->email_confirmation_code === null || ! Hash::check($code, $this->email_confirmation_code)) {
            return false;
        }

        $this->forceFill([
            'email_confirmation_code' => null,
            'email_confirmation_code_expires_at' => null,
        ])->save();

        $this->markEmailAsVerified();

        return true;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_confirmation_code_expires_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'is_super_admin' => 'boolean',
            'meta' => AsUserSettings::class,
            'password' => 'hashed',
        ];
    }
}
