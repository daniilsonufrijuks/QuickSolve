<?php

namespace App\Policies;

use App\Enums\PurchaseStatus;
use App\Models\Purchase;
use App\Models\User;

class PurchasePolicy
{
    public function download(User $user, Purchase $purchase): bool
    {
        return $purchase->user_id === $user->id
            && $purchase->status === PurchaseStatus::Paid
            && $purchase->template?->fileExists() === true;
    }
}
