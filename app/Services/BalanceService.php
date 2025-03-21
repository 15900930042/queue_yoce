<?php

namespace App\Services;

use App\Jobs\UpdateUserBalance;
use App\Models\User;
use http\Exception\InvalidArgumentException;

class BalanceService
{
    public function queueBalanceUpdate(User $user, float $amount): void
    {
        // 余额校验
        $this->validateAmount($amount);

        // 开始放队列
        UpdateUserBalance::dispatch($user, $amount)->onQueue('balance_updates');
    }

    protected function validateAmount(float $amount): void
    {
        if ($amount === 0.0) {
            throw new InvalidArgumentException("余额不能是零");
        }
    }


}
