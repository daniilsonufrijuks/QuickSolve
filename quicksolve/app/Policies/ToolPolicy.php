<?php

namespace App\Policies;

use App\Models\Tool;
use App\Models\User;

class ToolPolicy
{
    public function view(?User $user, Tool $tool): bool
    {
        return $tool->is_published || ($user?->is_admin ?? false);
    }

    public function manage(User $user): bool
    {
        return $user->is_admin;
    }
}
