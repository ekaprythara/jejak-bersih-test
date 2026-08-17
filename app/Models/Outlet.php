<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    /** @use HasFactory<\Database\Factories\OutletFactory> */
    use HasFactory;

    protected $fillable = [
        'image_url',
        'image_public_id',
        'name',
        'address',
        'phone_number'
    ];

    public function user()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Mendapatkan semua transaksi yang terjadi di outlet/cabang ini
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
