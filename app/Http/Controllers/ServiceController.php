<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Support\Facades\Gate;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // ❌ Salah: Gate::authorize('viewAny');
        // ✅ Perbaikan: Sebutkan Service::class agar Laravel memanggil ServicePolicy@viewAny
        Gate::authorize('viewAny', Service::class);

        return inertia('Service', [
            "services" => Service::latest()->paginate(10)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServiceRequest $request)
    {
        // ❌ Salah: Gate::authorize('store'); (Method di policy bernama 'create', bukan 'store')
        // ✅ Perbaikan: Pakai 'create' dan sertakan Service::class
        Gate::authorize('create', Service::class);

        Service::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'estimated_days' => $request->estimated_days
        ]);

        return back()->with('success', 'Layanan baru berhasil ditambahkan!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceRequest $request, Service $service)
    {
        // ❌ Salah: Gate::authorize('update');
        // ✅ Perbaikan: Oper objek $service sebagai parameter kedua
        Gate::authorize('update', $service);

        $service->update($request->validated());

        return back()->with('success', 'Layanan berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        // ❌ Salah: Gate::authorize('delete');
        // ✅ Perbaikan: Oper objek $service sebagai parameter kedua
        Gate::authorize('delete', $service);

        $service->delete();

        return back()->with('success', 'Layanan berhasil dihapus!');
    }
}
