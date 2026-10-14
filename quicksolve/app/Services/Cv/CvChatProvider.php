<?php

namespace App\Services\Cv;

interface CvChatProvider
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function reply(array $input): array;
}
