<?php

namespace App\Http\Requests;

use App\DTOs\UserData;
use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->route('user');

        return $this->user()?->can('update', $user) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', Password::default(), 'confirmed'],
            'status' => ['required', Rule::enum(UserStatus::class)],
            'role' => ['required', 'string', Rule::in($this->assignableRoles())],
        ];
    }

    public function toDto(): UserData
    {
        /** @var array{name: string, email: string, password?: string|null, status: string, role: string} $data */
        $data = $this->validated();

        return new UserData(
            name: $data['name'],
            email: $data['email'],
            status: UserStatus::from($data['status']),
            role: $data['role'],
            password: $data['password'] ?? null,
        );
    }

    /**
     * @return list<string>
     */
    protected function assignableRoles(): array
    {
        return [
            RolesAndPermissionsSeeder::ROLE_ADMIN,
            RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO,
            RolesAndPermissionsSeeder::ROLE_TECNICO,
            RolesAndPermissionsSeeder::ROLE_CONSULTA,
        ];
    }
}
