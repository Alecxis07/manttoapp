<?php

namespace App\Http\Requests;

use App\DTOs\UserData;
use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::default(), 'confirmed'],
            'status' => ['required', Rule::enum(UserStatus::class)],
            'role' => ['required', 'string', Rule::in($this->assignableRoles())],
        ];
    }

    public function toDto(): UserData
    {
        /** @var array{name: string, email: string, password: string, status: string, role: string} $data */
        $data = $this->validated();

        return new UserData(
            name: $data['name'],
            email: $data['email'],
            status: UserStatus::from($data['status']),
            role: $data['role'],
            password: $data['password'],
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
