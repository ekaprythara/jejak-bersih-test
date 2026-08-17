<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['transaction_id', 'shoe_brand', 'shoe_color', 'shoe_size', 'status_id', 'shoe_condition'])]
class TransactionShoe extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionShoeFactory> */
    use HasFactory;


    /**
     * Relasi ke tabel statuses (Status spesifik pengerjaan sepatu ini)
     */
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    /**
     * Relasi ke layanan apa saja yang dikerjakan pada sepatu ini (One to Many)
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }

    public function shoeServices()
    {
        return $this->hasMany(ShoeService::class, 'transaction_shoe_id', 'id');
    }
    public function services()
    {
        return $this->belongsToMany(Service::class, 'shoe_services', 'transaction_shoe_id', 'service_id')
            ->withPivot('subtotal_price') // Jika ingin mencatat subtotal harga saat transaksi
            ->withTimestamps();
    }
}
