<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['download_url'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'download_url' => 'encrypted'];
    }

    public function licenseKeys()
    {
        return $this->hasMany(LicenseKey::class);
    }

    public function availableKeys()
    {
        return $this->licenseKeys()->whereNull('order_id');
    }
}
