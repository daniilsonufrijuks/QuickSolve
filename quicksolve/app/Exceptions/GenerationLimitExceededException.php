<?php

namespace App\Exceptions;

use RuntimeException;

class GenerationLimitExceededException extends RuntimeException
{
    public function __construct(public readonly int $limit, public readonly string $window)
    {
        parent::__construct("You have reached the {$limit} generation limit for this {$window}.");
    }
}
