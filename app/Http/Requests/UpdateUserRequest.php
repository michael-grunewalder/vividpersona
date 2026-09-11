<?php

namespace App\Http\Requests;

use App\Enums\Plan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
            'plan' => ['required', Rule::enum(Plan::class)],
            'max_teams' => ['required', 'integer', 'min:1'],
            'is_super_admin' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Enforce the maximum team count against the teams the user already owns.
     *
     * @return array<int, \Closure>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('max_teams')) {
                    return;
                }

                $ownedTeams = $this->route('user')->ownedTeams()->count();

                if ($ownedTeams > (int) $this->input('max_teams')) {
                    $validator->errors()->add('max_teams', __('admin.users.max_teams_too_low', ['count' => $ownedTeams]));
                }
            },
        ];
    }
}
