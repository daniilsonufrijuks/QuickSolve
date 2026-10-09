<?php

namespace App\Services\Descriptions;

interface ProductDescriptionProvider
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function generate(array $input): array;
}
