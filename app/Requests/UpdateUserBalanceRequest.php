<?php

namespace App\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserBalanceRequest extends FormRequest
{
    public function rules() {
        return [
            'user_id' => 'required | exists: users, id',
            'amount' => [
                'required','numeric', 'not_in: 0', function ($attribute, $value, $fail) {
                    $user = User::find($this->input('user_id'));
                    if ($value < 0 && abs($value) > $user->balance) {
                        $fail('余额不足!');
                    }
                }
            ],
        ];
    }

}
