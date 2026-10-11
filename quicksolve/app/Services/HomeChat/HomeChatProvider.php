<?php

namespace App\Services\HomeChat;

interface HomeChatProvider
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function reply(array $input): array;
}
