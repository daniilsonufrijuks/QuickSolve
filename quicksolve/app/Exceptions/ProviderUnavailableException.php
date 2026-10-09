<?php

namespace App\Exceptions;

use RuntimeException;

class ProviderUnavailableException extends RuntimeException
{
    public function __construct(string $message = 'The description provider is unavailable. Nothing was generated.')
    {
        parent::__construct($message);
    }
}
