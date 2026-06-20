<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Access/Users/Index', [
            'users' => $this->userService->paginate($request->query()),
            'filters' => $request->only(['search', 'status', 'per_page']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Access/Users/Form', [
            'managedUser' => null,
            'roles' => $this->roles(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->create($request->validated(), $request);

        return to_route('users.index')->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user): Response
    {
        $user->load('roles:id,name');

        return Inertia::render('Access/Users/Form', [
            'managedUser' => [
                ...$user->only(['id', 'name', 'email', 'is_active']),
                'roles' => $user->roles->pluck('name')->values(),
            ],
            'roles' => $this->roles(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->update($user, $request->validated(), $request);

        return to_route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->can('hapus_user'), 403);

        $this->userService->deactivate($user, $request);

        return back()->with('success', 'User berhasil dinonaktifkan.');
    }

    private function roles(): array
    {
        return Role::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->all();
    }
}
