<?php

namespace App\Enums;

enum TeamRole: string
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Viewer = 'viewer';

    /**
     * The human-readable, localized label for the role.
     */
    public function label(): string
    {
        return __("roles.{$this->value}");
    }
}
