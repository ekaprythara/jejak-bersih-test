<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class CashierController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Transaction::class);

        $user = Auth::user();

        return inertia('Cashier', [
            'customers' => Customer::all(),
            "serviceData" => Service::all(),
            "outlet_data" => $user->outlet,
            "user_data" => $user,
        ]);
    }

    public function store(StoreTransactionRequest $request)
    {
        Gate::authorize('create', Transaction::class);

        $user = Auth::user();
        $outletId = $user ? $user->outlet_id : null;

        // 1. Validasi Data
        $validated = $request->validate([
            'customer_id'             => 'required|exists:customers,id',
            'payment_status'          => 'required|in:unpaid,paid',
            'payment_method'          => 'required|in:cash,transfer',
            'notes'                   => 'nullable|string',

            // Validasi array sepatu (transaction_shoes)
            'shoes'                   => 'required|array|min:1',
            'shoes.*.shoe_brand'      => 'required|string|max:255',
            'shoes.*.shoe_color'      => 'required|string|max:255',
            'shoes.*.shoe_size'       => 'nullable|numeric|max:50',
            'shoes.*.shoe_condition'  => 'nullable|string',

            // Validasi layanan yang dipilih di setiap sepatu
            'shoes.*.services'        => 'required|array|min:1',
            'shoes.*.services.*.id'   => 'required|exists:services,id',
        ]);

        try {
            DB::beginTransaction();

            // 2. Kumpulkan semua ID service dari request untuk query data asli dari database
            $serviceIds = [];
            foreach ($validated['shoes'] as $shoe) {
                foreach ($shoe['services'] as $service) {
                    $serviceIds[] = $service['id'];
                }
            }
            $servicesFromDb = Service::whereIn('id', $serviceIds)->get()->keyBy('id');

            // 3. Hitung total_price & estimasi hari secara otomatis dari database
            $totalPrice = 0;
            $maxEstimatedDays = 0; // Mengambil estimasi layanan paling lama

            foreach ($validated['shoes'] as $shoe) {
                foreach ($shoe['services'] as $service) {
                    if (isset($servicesFromDb[$service['id']])) {
                        $realService = $servicesFromDb[$service['id']];

                        // Akumulasi harga
                        $totalPrice += $realService->price;

                        // OPSI A (Rekomendasi): Ambil durasi layanan terlama
                        // if ($realService->estimated_days > $maxEstimatedDays) {
                        //     $maxEstimatedDays = $realService->estimated_days;
                        // }

                        // OPSI B: Jika ingin menambahkan semua hari layanan (kumulatif):
                        $maxEstimatedDays += $realService->estimated_days;
                    }
                }
            }

            // Hitung tanggal jatuh tempo (overdue_date)
            $overdueDate = now()->addDays($maxEstimatedDays);

            // 4. Generate Nomor Invoice unik
            $datePrefix = date('Ymd');
            $randomString = strtoupper(substr(uniqid(), -5));
            $invoiceNumber = 'INV-' . $datePrefix . '-' . $randomString;

            // 5. Simpan Transaksi Utama ke Database
            $transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'total_price'    => $totalPrice,
                'payment_status' => $validated['payment_status'],
                'payment_method' => $validated['payment_method'],
                'notes'          => $validated['notes'] ?? null,
                'status_id'      => 1, // Status per transaction, begin from 1 to 4
                'customer_id'    => $validated['customer_id'],
                'outlet_id'      => $outletId,
                'overdue_date'   => $overdueDate, // 👈 Ditambahkan ke database
            ]);

            // 6. Simpan Data Sepatu & Relasi Layanannya ke Tabel Pivot (shoe_services)
            foreach ($validated['shoes'] as $shoe) {
                $transactionShoe = $transaction->shoes()->create([
                    'shoe_brand'     => $shoe['shoe_brand'],
                    'shoe_color'     => $shoe['shoe_color'],
                    'shoe_size'      => $shoe['shoe_size'] ?? null,
                    'shoe_condition' => $shoe['shoe_condition'] ?? null,
                    'status_id'      => 5, // Status per shoe, begin from 5 to 8
                ]);

                // Siapkan data untuk tabel pivot 'shoe_services' beserta subtotal harganya
                $servicesDataToAttach = [];
                foreach ($shoe['services'] as $service) {
                    if (isset($servicesFromDb[$service['id']])) {
                        $realService = $servicesFromDb[$service['id']];
                        $servicesDataToAttach[$service['id']] = [
                            'subtotal_price' => $realService->price
                        ];
                    }
                }

                // Masukkan relasi ke tabel pivot
                $transactionShoe->services()->attach($servicesDataToAttach);
            }

            // Commit perubahan database jika semua sukses
            DB::commit();

            return redirect()->route('cashier.index')->with('success', 'Transaksi berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Store Transaction: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
    }
}
