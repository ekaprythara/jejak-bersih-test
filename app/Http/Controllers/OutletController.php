<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class OutletController extends Controller
{
    public function index()
    {
        // ✅ Perbaikan: Masukkan Outlet::class
        Gate::authorize('viewAny', Outlet::class);

        return Inertia::render('Outlet', [
            'outlets' => Outlet::latest()->paginate(10),
        ]);
    }

    public function store(Request $request, CloudinaryService $cloudinary)
    {
        // ✅ Perbaikan: Masukkan Outlet::class
        Gate::authorize('create', Outlet::class);

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'address'      => 'required|string',
            'phone_number' => 'required|string|max:20',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $upload = $cloudinary->upload($request->file('image'), 'outlets');
            $validated['image_url']       = $upload['secure_url'];
            $validated['image_public_id'] = $upload['public_id'];
        }

        Outlet::create($validated);

        return back()->with('success', 'Cabang baru berhasil ditambahkan!');
    }

    public function update(Request $request, Outlet $outlet, CloudinaryService $cloudinary)
    {
        // ✅ Perbaikan: Masukkan instance $outlet
        Gate::authorize('update', $outlet);

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'address'      => 'required|string',
            'phone_number' => 'required|string|max:20',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            // Hapus gambar lama dari Cloudinary jika ada
            if ($outlet->image_public_id) {
                $cloudinary->destroy($outlet->image_public_id);
            }

            // Upload gambar baru
            $upload = $cloudinary->upload($request->file('image'), 'outlets');
            $validated['image_url']       = $upload['secure_url'];
            $validated['image_public_id'] = $upload['public_id'];
        }

        $outlet->update($validated);

        return back()->with('success', 'Data cabang berhasil diperbarui!');
    }

    public function destroy(Outlet $outlet, CloudinaryService $cloudinary)
    {
        // ✅ Perbaikan: Masukkan instance $outlet
        Gate::authorize('delete', $outlet);

        // Hapus gambar dari Cloudinary jika ada
        if ($outlet->image_public_id) {
            $cloudinary->destroy($outlet->image_public_id);
        }

        $outlet->delete();

        return back()->with('success', 'Cabang berhasil dihapus!');
    }
}
