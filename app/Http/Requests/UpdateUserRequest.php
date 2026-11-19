<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var User|null $targetUser */
        $targetUser = $this->route('user');

        return $targetUser && ($this->user()?->can('update', $targetUser) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $targetUser = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($targetUser),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', new Enum(UserRole::class)],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Pesan validasi Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email dinas wajib diisi.',
            'email.email' => 'Format email dinas tidak valid.',
            'email.unique' => 'Email ini telah digunakan oleh akun lain.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'role.required' => 'Peran pengguna (Role) wajib dipilih.',
        ];
    }
}
