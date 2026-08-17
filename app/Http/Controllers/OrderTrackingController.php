<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\Transaction;

class OrderTrackingController extends Controller
{
    public function index()
    {
        return inertia('OrderTracking');
    }

    public function show(string $invoice)
    {
        $transaction = Transaction::with([
            'customer',
            'outlet',
            'status',
            'transactionShoes.status',
        ])
            ->where('invoice_number', $invoice)
            ->firstOrFail();

        return inertia('TrackingDetail', [
            'transaction' => $transaction,
            'transactionStatus' => Status::whereType('transaction_progress')
                ->orderBy('step', 'asc')
                ->get(),
            'shoeStatus' => Status::whereType('shoes_progress')
                ->orderBy('step', 'asc')
                ->get(),
        ]);
    }
}
