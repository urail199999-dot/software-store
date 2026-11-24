<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseKey extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['key', 'fingerprint'];

    protected function casts(): array
    {
        return ['key' => 'encrypted'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $license) {
            if ($license->exists && $license->getOriginal('order_id') !== null && $license->isDirty()) {
                throw new \LogicException('Выданный ключ нельзя изменять.');
            }
            if ($license->isDirty('key')) {
                $license->key = trim($license->key);
                $license->fingerprint = hash('sha256', $license->key);
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
