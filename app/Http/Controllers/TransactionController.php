<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Status;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        return inertia('Transaction', [
            'transactions' => Transaction::with([
                'customer',
                'outlet',
                'status',
                'transactionShoes.shoeServices.service',
                'transactionShoes.status',
            ])
                ->latest()
                ->paginate(10)
                ->through(function ($transaction) {
                    // Opsional: Memastikan key JSON berubah dari camelCase (transactionShoes) 
                    // menjadi snake_case (transaction_shoes) agar sesuai TypeScript
                    $array = $transaction->toArray();
                    $array['transaction_shoes'] = $transaction->transactionShoes;
                    return $array;
                }),


            'transactionStatus' => Status::where('type', 'transaction_progress')->get(),
            'shoeStatuses' => Status::where('type', 'shoes_progress')->get(),
        ]);
    }

    /**
     * Patch: /transactions/{transaction}/shoe-detail
     */
    public function updateShoeDetail(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'shoe_id'        => 'required|exists:transaction_shoes,id',
            'shoe_brand'     => 'required|string|max:255',
            'shoe_color'     => 'required|string|max:255',
            'shoe_size'      => 'nullable|string|max:50',
            'shoe_condition' => 'nullable|string',
        ]);

        // Cari sepatu milik transaksi ini
        $shoe = $transaction->transactionShoes()->where('id', $validated['shoe_id'])->firstOrFail();

        $shoe->update([
            'shoe_brand'     => $validated['shoe_brand'],
            'shoe_color'     => $validated['shoe_color'],
            'shoe_size'      => $validated['shoe_size'],
            'shoe_condition' => $validated['shoe_condition'],
        ]);

        return back()->with('success', 'Detail sepatu berhasil diperbarui!');
    }

    /**
     * Patch: /transactions/{transaction}/shoe-status
     */
    public function updateStatus(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'shoe_id'   => 'required|exists:transaction_shoes,id',
            'status_id' => 'required|exists:statuses,id',
        ]);

        // 1. Cari dan update sepatu milik transaksi ini
        $shoe = $transaction->transactionShoes()->where('id', $validated['shoe_id'])->firstOrFail();

        $shoe->update([
            'status_id' => $validated['status_id'],
        ]);

        // 2. Ambil data status sepatu yang baru saja di-assign (untuk tahu step-nya)
        $newShoeStatus = Status::find($validated['status_id']);

        // 3. LOGIKA OTOMATISASI STATUS TRANSAKSI UTAMA:

        // Jika sepatu ini naik ke Step >= 2 dan status transaksi saat ini masih di Step 1 (Pesanan Diterima)
        if ($newShoeStatus && $newShoeStatus->step >= 2) {
            $currentTransactionStatus = $transaction->status;

            // Jika transaksi utama masih di step 1, otomatis naikkan ke Step 2 (Dalam Pengerjaan)
            if (!$currentTransactionStatus || $currentTransactionStatus->step < 2) {
                $inProgressTransactionStatus = Status::where('type', 'transaction_progress')
                    ->where('step', 2)
                    ->first();

                if ($inProgressTransactionStatus) {
                    $transaction->update([
                        'status_id' => $inProgressTransactionStatus->id,
                    ]);
                }
            }
        }

        // 4. (OPSIONAL) Cek jika SEMUA sepatu dalam transaksi ini sudah selesai (Step 4 / Final Step)
        $this->checkAndAutoCompleteTransaction($transaction);

        return back()->with('success', 'Status pengerjaan berhasil diperbarui!');
    }

    /**
     * Helper Method Opsional: Menandai transaksi SIAP DIAMBIL (Step 3) 
     * jika SEMUA sepatu dalam transaksi sudah bernilai Selesai (Step 4 / Final)
     */
    private function checkAndAutoCompleteTransaction(Transaction $transaction)
    {
        // Muat ulang relasi sepatu beserta statusnya
        $transaction->load('transactionShoes.status');

        // Cek apakah ada sepatu yang BELUM selesai (step < 4 atau isFinalStep = false)
        $hasUnfinishedShoes = $transaction->transactionShoes->contains(function ($shoeItem) {
            return !$shoeItem->status || $shoeItem->status->step < 4; // Atau: !$shoeItem->status->isFinalStep
        });

        // Jika SEMUA sepatu sudah selesai pengerjaannya
        if (!$hasUnfinishedShoes && $transaction->transactionShoes->count() > 0) {
            $readyForPickupStatus = Status::where('type', 'transaction_progress')
                ->where('step', 3) // Step 3: Siap Diambil
                ->first();

            if ($readyForPickupStatus) {
                $transaction->update([
                    'status_id' => $readyForPickupStatus->id,
                ]);
            }
        }
    }
}
