<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['transaction_shoe_id', 'service_id', 'subtotal_price'])]
class ShoeService extends Model
{
    /** @use HasFactory<\Database\Factories\ShoeServiceFactory> */
    use HasFactory;

    public function transactionShoe()
    {
        return $this->belongsTo(TransactionShoe::class, 'transaction_shoe_id', 'id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }
}
