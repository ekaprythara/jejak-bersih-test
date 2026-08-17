<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia('Customer', [
            'customers' => Customer::latest()->paginate(10),
            'totalCustomers' => Customer::count(),
            'newCustomers' => Customer::where('created_at', '>=', now()->subDays(7)->startOfDay())->count(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        Customer::create([
            'name' => $request->name,
            'phone_number' => $request->phone_number
        ]);

        return back()->with('success', 'Pelanggan baru berhasil ditambahkan!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20|unique:customers,phone_number,' . $customer->id,
        ]);

        $customer->update($validated);

        return back()->with('success', 'Data pelanggan berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return back()->with('success', 'Pelanggan berhasil dihapus!');
    }
}
