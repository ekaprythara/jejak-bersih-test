<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\Outlet;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', User::class);

        // Gunakan ejaan nama Role yang sesuai di DB (contoh: 'Owner' / 'Admin')
        $owners = User::whereHas('role', fn($q) => $q->where('name', 'Owner'))
            ->latest()
            ->paginate(100);

        $admins = User::whereHas('role', fn($q) => $q->where('name', 'Admin'))
            ->with('outlet')
            ->latest()
            ->paginate(100);

        return Inertia::render('User', [
            'owners'  => $owners,
            'admins'  => $admins,
            'outlets' => Outlet::select('id', 'name')->get(),
            'roles'   => Role::select('id', 'name')->get(),
        ]);
    }

    /**
     * Menyimpan data OWNER Baru
     */
    public function storeOwner(StoreUserRequest $request)
    {
        // ✅ Perbaikan: Masukkan User::class
        Gate::authorize('create', User::class);

        $validated = $request->validated();

        $ownerRole = Role::where('name', 'Owner')->firstOrFail();

        $validated['role_id']   = $ownerRole->id;
        $validated['outlet_id'] = null; // Owner tidak terikat outlet
        $validated['password']  = Hash::make($validated['password']);
        $validated['is_active'] = true;

        User::create($validated);

        return back()->with('success', 'Data Owner berhasil disimpan!');
    }

    /**
     * Menyimpan data ADMIN Baru
     */
    public function storeAdmin(StoreUserRequest $request)
    {
        // ✅ Perbaikan: Masukkan User::class
        Gate::authorize('create', User::class);

        $validated = $request->validated();

        if (empty($validated['outlet_id'])) {
            return back()->withErrors(['outlet_id' => 'Cabang Outlet wajib dipilih untuk Admin.']);
        }

        $adminRole = Role::where('name', 'Admin')->firstOrFail();

        $validated['role_id']   = $adminRole->id;
        $validated['password']  = Hash::make($validated['password']);
        $validated['is_active'] = true;

        User::create($validated);

        return back()->with('success', 'Data Admin berhasil disimpan!');
    }

    /**
     * Update penugasan outlet dan status aktif admin.
     */
    public function updateAdmin(Request $request, User $user)
    {
        // ✅ Perbaikan: Masukkan $user sebagai target
        Gate::authorize('update', $user);

        $validated = $request->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        $user->update([
            'outlet_id' => $validated['outlet_id'],
            'is_active' => $validated['is_active'],
        ]);

        return back()->with('success', 'Akses & cabang Admin berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyAdmin(User $user)
    {
        // ✅ Perbaikan: Masukkan $user sebagai target
        Gate::authorize('delete', $user);

        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }

        $user->delete();

        return back()->with('success', 'Akun Admin berhasil dihapus!');
    }

    /**
     * Update status aktif owner.
     */
    public function updateOwner(Request $request, User $user)
    {
        // ✅ Perbaikan: Masukkan $user sebagai target
        Gate::authorize('update', $user);

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $user->update([
            'is_active' => $validated['is_active'],
        ]);

        return back()->with('success', 'Status Owner berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyOwner(User $user)
    {
        // ✅ Perbaikan: Masukkan $user sebagai target
        Gate::authorize('delete', $user);

        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }

        $user->delete();

        return back()->with('success', 'Akun Owner berhasil dihapus!');
    }
}
