<?php

namespace App\ValueObjects;

use App\Enums\Plan;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class UserSettings implements Arrayable, JsonSerializable
{
    public Plan $currentPlan;

    public int $maxTeams;

    public function __construct(?Plan $currentPlan = null, ?int $maxTeams = null)
    {
        $this->currentPlan = $currentPlan ?? Plan::Free;
        $this->maxTeams = $maxTeams ?? 1;
    }

    /**
     * Build settings from raw data, reading only the known keys.
     */
    public static function fromArray(?array $data): self
    {
        return new self(
            currentPlan: isset($data['current_plan']) ? (Plan::tryFrom($data['current_plan']) ?? Plan::Free) : null,
            maxTeams: isset($data['max_teams']) ? (int) $data['max_teams'] : null,
        );
    }

    /**
     * @return array{current_plan: string, max_teams: int}
     */
    public function toArray(): array
    {
        return [
            'current_plan' => $this->currentPlan->value,
            'max_teams' => $this->maxTeams,
        ];
    }

    /**
     * @return array{current_plan: string, max_teams: int}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
