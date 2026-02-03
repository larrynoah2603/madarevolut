<?php

namespace App\Actions\Fortify;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the user's profile information.
     *
     * @param  \App\Models\User  $user
     * @param  array<string, string|null>  $input
     */
    public function update($user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users')->ignore($user->id),
            ],
            'subscription_plan' => ['nullable', 'string', Rule::in(['standard', 'plus', 'premium', 'metal', 'ultra'])],
            'mobile_money_number' => ['nullable', 'string', 'max:25'],
            'mobile_money_provider' => ['nullable', 'string', Rule::in(['mvola', 'orange', 'airtel'])],
        ])->validate();

        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'phone_number' => $input['phone_number'],
            'subscription_plan' => $input['subscription_plan'] ?? $user->subscription_plan,
            'mobile_money_number' => $input['mobile_money_number'] ?? $user->mobile_money_number,
            'mobile_money_provider' => $input['mobile_money_provider'] ?? $user->mobile_money_provider,
        ])->save();
    }
}
