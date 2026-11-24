<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    public function index()
    {
        return view('store.index', ['products' => Product::where('is_active', true)->withCount('availableKeys')->get()]);
    }

    public function checkout(Request $request, Product $product)
    {
        abort_unless($product->is_active, 404);
        $data = $request->validate(['email' => 'required|email|max:254']);
        if (! $product->availableKeys()->exists()) {
            return back()->withErrors(['stock' => 'Товар закончился.']);
        }
        $token = Str::random(64);
        Order::create(['product_id' => $product->id, 'product_name' => $product->name, 'email' => $data['email'], 'amount' => $product->price, 'access_hash' => hash('sha256', $token)]);

        return redirect()->route('order.show', $token);
    }

    public function show(string $token)
    {
        $order = Order::where('access_hash', hash('sha256', $token))->firstOrFail();

        return response()->view('store.order', compact('order', 'token'))->header('Cache-Control', 'no-store, private')->header('Referrer-Policy', 'no-referrer')->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function download(string $token)
    {
        $order = Order::where('access_hash', hash('sha256', $token))->firstOrFail();
        abort_unless($order->status === 'paid' && $order->licenseKey, 403);
        abort_unless(filter_var($order->download_url, FILTER_VALIDATE_URL) && in_array(parse_url($order->download_url, PHP_URL_SCHEME), ['https', 'http']), 404);

        return redirect()->away($order->download_url)->header('Referrer-Policy','no-referrer')->header('Cache-Control','no-store');
    }
}
