<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'description', 'price', 'estimated_days'])]
class Service extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    use HasFactory;

    /**
     * Mendapatkan data riwayat pemakaian layanan ini di berbagai sepatu
     */
    public function shoeServices()
    {
        return $this->hasMany(ShoeService::class);
    }
}
