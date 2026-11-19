<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Tampilkan daftar petugas pengguna sistem.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $roleFilter = $request->query('role');
        $searchTerm = $request->query('search');

        $query = User::query()->latest('id');

        if (! empty($roleFilter)) {
            $query->where('role', $roleFilter);
        }

        if (! empty($searchTerm)) {
            $term = trim($searchTerm);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $users = $query->paginate(10)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => UserRole::cases(),
            'currentRole' => $roleFilter,
            'searchTerm' => $searchTerm,
        ]);
    }

    /**
     * Form pendaftaran pengguna baru.
     */
    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('admin.users.create', [
            'roles' => UserRole::cases(),
        ]);
    }

    /**
     * Simpan pengguna baru ke database.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_admin'] = ($validated['role'] === UserRole::ADMIN->value);

        User::create($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Akun petugas baru berhasil didaftarkan ke sistem.');
    }

    /**
     * Form edit data pengguna.
     */
    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => UserRole::cases(),
        ]);
    }

    /**
     * Perbarui data pengguna.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_admin'] = ($validated['role'] === UserRole::ADMIN->value);

        $user->update($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Data akun petugas berhasil diperbarui.');
    }

    /**
     * Hapus akun pengguna (kecuali akun sendiri).
     */
    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Akun petugas berhasil dihapus dari sistem.');
    }

    /**
     * Aktifkan / Nonaktifkan akun pengguna.
     */
    public function toggleActive(User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        $statusText = $user->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Akun {$user->name} berhasil {$statusText}.");
    }
}
