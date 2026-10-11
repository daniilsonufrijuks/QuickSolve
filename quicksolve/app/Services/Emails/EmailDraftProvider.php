<?php

namespace App\Services\Emails;

interface EmailDraftProvider
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function generate(array $input): array;
}
