<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()->activeCompanyId();

        $users = User::where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'created_at']);

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Users/Create');
    }

    public function store(Request $request)
    {
        abort_if(!$request->user()->is_admin, 403);

        $companyId = $request->user()->activeCompanyId();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,manager,viewer',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'company_id' => $companyId,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(Request $request, User $user): Response
    {
        $companyId = $request->user()->activeCompanyId();
        abort_if($user->company_id !== $companyId, 403);
        abort_if(!$request->user()->is_admin, 403);

        return Inertia::render('Users/Edit', [
            'editUser' => $user->only(['id', 'name', 'email', 'role']),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $companyId = $request->user()->activeCompanyId();
        abort_if($user->company_id !== $companyId, 403);
        abort_if(!$request->user()->is_admin, 403);

        // Cannot change owner role
        if ($user->role === 'owner') {
            abort(403, 'Cannot modify owner account.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,manager,viewer',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        $companyId = $request->user()->activeCompanyId();
        abort_if($user->company_id !== $companyId, 403);
        abort_if(!$request->user()->is_admin, 403);

        // Cannot delete yourself or owner
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        if ($user->role === 'owner') {
            return back()->with('error', 'Tidak dapat menghapus akun owner.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}