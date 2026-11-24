<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AccountController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = Order::where('user_id', $request->user()->id)->latest('id')->paginate(12);

        return response()->view('store.account', compact('orders'))->header('Cache-Control', 'no-store, private');
    }

    public function show(Request $request, Order $order): Response
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        return response()->view('store.order', [
            'order' => $order, 'token' => null, 'accountOrder' => true,
            'orderUrl' => route('account.order', $order), 'downloadUrl' => route('account.download', $order),
        ])->header('Cache-Control', 'no-store, private')->header('Referrer-Policy', 'no-referrer')->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function download(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);
        abort_unless($order->status === 'paid' && $order->licenseKey, 403);
        abort_unless(filter_var($order->download_url, FILTER_VALIDATE_URL) && in_array(parse_url($order->download_url, PHP_URL_SCHEME), ['https', 'http']), 404);

        return redirect()->away($order->download_url)->header('Cache-Control','no-store')->header('Referrer-Policy','no-referrer');
    }
}
