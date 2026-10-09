<?php

namespace App\Exceptions;

use RuntimeException;

class BillingNotConfiguredException extends RuntimeException
{
    public function __construct(string $message = 'Billing is not configured yet. Add your Stripe test keys before starting checkout.')
    {
        parent::__construct($message);
    }
}
