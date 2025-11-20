<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    protected array $roles = [
        'admin' => 'Administrator',
        'tester' => 'Tester',
    ];

    public function index(): View
    {
        $users = User::orderBy('name')->paginate(15);

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $this->roles,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => $this->roles,
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::create($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Użytkownik został utworzony.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => $this->roles,
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $payload = $request->validated();

        if (empty($payload['password'])) {
            unset($payload['password']);
        }

        $user->update($payload);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Dane użytkownika zostały zaktualizowane.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Nie możesz usunąć swojego konta.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Użytkownik został usunięty.');
    }
}