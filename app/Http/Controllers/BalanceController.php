<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Requests\UpdateUserBalanceRequest;
use App\Services\BalanceService;
use Illuminate\Http\JsonResponse;

class BalanceController extends Controller
{
    public function __construct(
        protected BalanceService $balanceService
    ) {}


    /**
     * 更新余额
     * @param UpdateUserBalanceRequest $balanceRequest
     * @return JsonResponse
     */
    public function updateBalance( UpdateUserBalanceRequest $balanceRequest) {
        $user = User::findOrFail($balanceRequest->user_id);

        $this->balanceService->queueBalanceUpdate($user, $balanceRequest->validate('amount'));

        return response()->json([
            'msg' => '余额更新成功!',
            'new_balance' => $user->balance
        ]);
    }

}
