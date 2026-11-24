<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['access_hash', 'download_url'];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime', 'download_url' => 'encrypted'];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function licenseKey()
    {
        return $this->hasOne(LicenseKey::class);
    }
}
