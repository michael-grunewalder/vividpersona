<?php

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Str;

class CreateTeam
{
    /**
     * Create a team owned by the given user and assign them the given role.
     */
    public function handle(User $owner, string $name, string $ownerRole = 'admin'): Team
    {
        $team = Team::query()->create([
            'name' => $name,
            'slug' => $this->uniqueSlug($name),
            'owner_id' => $owner->getKey(),
        ]);

        (new AddTeamMember)->handle($team, $owner, $ownerRole);

        return $team;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'team';

        $slug = $base;

        while (Team::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.Str::lower(Str::random(6));
        }

        return $slug;
    }
}
