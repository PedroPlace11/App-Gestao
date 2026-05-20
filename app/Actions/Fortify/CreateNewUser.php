<?php

namespace App\Actions\Fortify;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        if (Schema::hasTable('companies')) {
            $slug = Str::slug($input['name']) ?: 'tenant';
            $slug .= '-' . $user->id;

            $company = Company::create([
                'owner_user_id' => $user->id,
                'name' => ($input['name'] . ' Workspace'),
                'slug' => $slug,
                'address' => '',
                'postal_code' => '',
                'city' => '',
                'tax_id' => null,
                'settings' => [
                    'onboarding' => [
                        'branding' => false,
                        'users' => false,
                        'permissions' => false,
                    ],
                    'preferences' => [
                        'default_landing_page' => '/dashboard',
                    ],
                ],
            ]);

            if (Schema::hasTable('company_user')) {
                $company->users()->attach($user->id, ['role' => 'owner', 'is_owner' => true]);
            }

            if (Schema::hasColumn('users', 'active_company_id')) {
                $user->forceFill(['active_company_id' => $company->id])->save();
            }

            if (Schema::hasTable('subscriptions')) {
                $plan = Plan::query()->where('is_active', true)->orderBy('monthly_price', 'asc')->first();

                Subscription::query()->updateOrCreate(
                    ['company_id' => $company->id],
                    [
                        'plan_id' => $plan?->id,
                        'status' => 'trialing',
                        'current_period_start' => now(),
                        'current_period_end' => now()->addMonth(),
                        'trial_ends_at' => now()->addDays(14),
                        'cancel_at_period_end' => false,
                    ]
                );
            }
        }

        if (Schema::hasColumn('users', 'active_company_id')) {
            $user->forceFill(['active_company_id' => $company->id])->save();
        }

        return $user;
    }
}
