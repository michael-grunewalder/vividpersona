<?php

namespace App\Exceptions;

use App\Models\Team;
use Exception;

class StorageLimitExceededException extends Exception
{
    public function __construct(public readonly Team $team)
    {
        parent::__construct('The team has reached its storage limit.');
    }
}
