<?php

namespace App\Enums;

enum Plan: string
{
    case Free = 'free';
    case Starter = 'starter';
    case Pro = 'pro';
    case Premium = 'premium';
    case Agency = 'agency';

    /**
     * The human-readable, localized label for the plan.
     */
    public function label(): string
    {
        return __("plans.{$this->value}");
    }
}
