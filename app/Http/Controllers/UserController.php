<?php

namespace App\Http\Controllers;

use App\Actions\Users\CreateUser;
use App\Actions\Users\DeactivateUser;
use App\Actions\Users\UpdateUser;
use App\Enums\UserStatus;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->with('roles')
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->string('status')->toString(), function ($query, string $status): void {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status->value,
                'status_label' => $user->status->label(),
                'role' => $user->getRoleNames()->first(),
                'created_at' => $user->created_at?->toIso8601String(),
            ]);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
            ],
            'statuses' => $this->statusOptions(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('Users/Create', [
            'roles' => $this->roleOptions(),
            'statuses' => $this->statusOptions(),
        ]);
    }

    public function store(StoreUserRequest $request, CreateUser $createUser): RedirectResponse
    {
        $createUser->handle($request->toDto());

        return redirect()
            ->route('users.index')
            ->with('success', __('Usuario creado correctamente.'));
    }

    public function show(User $user): Response
    {
        $this->authorize('view', $user);

        $user->load('roles');

        return Inertia::render('Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status->value,
                'status_label' => $user->status->label(),
                'role' => $user->getRoleNames()->first(),
                'created_at' => $user->created_at?->toIso8601String(),
            ],
        ]);
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        $user->load('roles');

        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status->value,
                'role' => $user->getRoleNames()->first(),
            ],
            'roles' => $this->roleOptions(),
            'statuses' => $this->statusOptions(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUser $updateUser): RedirectResponse
    {
        $updateUser->handle($user, $request->toDto(), $request->user());

        return redirect()
            ->route('users.index')
            ->with('success', __('Usuario actualizado correctamente.'));
    }

    public function destroy(Request $request, User $user, DeactivateUser $deactivateUser): RedirectResponse
    {
        $this->authorize('delete', $user);

        $deactivateUser->handle($user, $request->user());

        return redirect()
            ->route('users.index')
            ->with('success', __('Usuario desactivado correctamente.'));
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function roleOptions(): array
    {
        return [
            ['value' => RolesAndPermissionsSeeder::ROLE_ADMIN, 'label' => 'Administrador'],
            ['value' => RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO, 'label' => 'Administrativo'],
            ['value' => RolesAndPermissionsSeeder::ROLE_TECNICO, 'label' => 'Técnico'],
            ['value' => RolesAndPermissionsSeeder::ROLE_CONSULTA, 'label' => 'Consulta'],
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function statusOptions(): array
    {
        return collect(UserStatus::cases())
            ->map(fn (UserStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
            ])
            ->values()
            ->all();
    }
}
