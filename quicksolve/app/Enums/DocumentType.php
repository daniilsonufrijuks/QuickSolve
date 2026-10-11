<?php

namespace App\Enums;

enum DocumentType: string
{
    case Invoice = 'invoice';
    case Quote = 'quote';
}
