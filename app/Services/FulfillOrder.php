<?php

namespace App\Services;

use App\Models\LicenseKey;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FulfillOrder
{
    public function handle(Order $order, User $admin): Order
    {
        abort_unless($admin->is_admin, 403);

        return DB::transaction(function () use ($order, $admin) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($order->status === 'paid') {
                return $order;
            }
            $key = LicenseKey::where('product_id', $order->product_id)->whereNull('order_id')->lockForUpdate()->orderBy('id')->first();
            if (! $key) {
                throw ValidationException::withMessages(['stock' => 'Нет свободных ключей. Добавьте ключи и повторите подтверждение.']);
            }
            $key->update(['order_id' => $order->id]);
            $order->update(['status' => 'paid', 'paid_at' => now(), 'confirmed_by' => $admin->id, 'download_url' => $order->product->download_url]);

            return $order;
        }, 5);
    }
}
