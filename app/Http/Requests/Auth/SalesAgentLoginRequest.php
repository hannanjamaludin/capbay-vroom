<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class SalesAgentLoginRequest extends FormRequest
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
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function authenticate(): void
    {
        if (! Auth::attempt([
            'email' => $this->string('email')->lower()->toString(),
            'password' => $this->string('password')->toString(),
            'role' => \App\Models\User::ROLE_SALES_AGENT,
        ], $this->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match a sales agent account.',
            ]);
        }

        $this->session()->regenerate();
    }
}
