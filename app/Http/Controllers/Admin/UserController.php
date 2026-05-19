<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search');
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('role'), function ($query) use ($request): void {
                $query->where('role', $request->input('role'));
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('is_active', $request->input('status') === 'active');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        User::query()->create($data);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if ($this->wouldLeaveNoActiveAdmin($user, $data['role'], (bool) $data['is_active'])) {
            return back()->withInput()->with('error', 'Minimal harus ada satu admin aktif agar aplikasi tetap bisa dikelola.');
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function toggle(User $user): RedirectResponse
    {
        if ($user->is_active && $this->wouldLeaveNoActiveAdmin($user, $user->role, false)) {
            return back()->with('error', 'Minimal harus ada satu admin aktif agar aplikasi tetap bisa dikelola.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'Status user berhasil diperbarui.');
    }

    private function wouldLeaveNoActiveAdmin(User $user, string $nextRole, bool $nextActive): bool
    {
        if (! $user->isAdmin() || ! $user->is_active) {
            return false;
        }

        if ($nextRole === 'admin' && $nextActive) {
            return false;
        }

        return User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->whereKeyNot($user->id)
            ->doesntExist();
    }
}
