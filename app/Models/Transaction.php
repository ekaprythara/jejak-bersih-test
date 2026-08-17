<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'overdue_date',
        'total_price',
        'notes',
        'payment_method',
        'payment_status',
        'status_id',
        'customer_id',
        'outlet_id',
    ];

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    /**
     * Relasi ke tabel customers (Pemilik transaksi)
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relasi ke tabel outlets (Cabang tempat transaksi)
     */
    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    /**
     * Relasi ke sepatu-sepatu yang ada di dalam transaksi ini (One to Many)
     */
    public function transactionShoes()
    {
        return $this->hasMany(TransactionShoe::class, 'transaction_id', 'id');
    }

    public function shoes()
    {
        return $this->hasMany(TransactionShoe::class);
    }
}
