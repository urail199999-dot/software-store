<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    public function index()
    {
        return view('store.index', $this->catalogData());
    }

    public function catalog(?Category $category = null): View
    {
        return view('store.catalog', $this->catalogData($category));
    }

    private function catalogData(?Category $category = null): array
    {
        $tree = Category::publishedTree();
        if ($category) {
            abort_unless($tree->contains('id', $category->id), 404);
        }
        $allProducts = Product::published()->with('category')->withCount('availableKeys')->orderBy('sort_order')->orderBy('name')->get();
        $categories = $tree->where('parent_id', $category?->id)->values();
        foreach ($categories as $item) {
            $items = $allProducts->whereIn('category_id', $item->descendantIds($tree));
            $item->catalog_count = $items->count();
            $item->catalog_price = $items->min('price');
            $item->catalog_available = $items->contains(fn ($product) => $product->available_keys_count > 0);
        }
        $products = $category ? $allProducts->whereIn('category_id', $category->descendantIds($tree))->values() : $allProducts;
        $breadcrumbs = collect();
        $ancestor = $category;
        while ($ancestor) {
            $breadcrumbs->prepend($ancestor);
            $ancestor = $tree->firstWhere('id', $ancestor->parent_id);
        }

        return compact('products', 'categories', 'category', 'breadcrumbs');
    }

    public function product(Product $product): View
    {
        abort_unless(Product::published()->whereKey($product->id)->exists(), 404);
        $product->load('category')->loadCount('availableKeys');

        return view('store.product', compact('product'));
    }

    public function checkout(Request $request, Product $product)
    {
        abort_unless(Product::published()->whereKey($product->id)->exists(), 404);
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

        return redirect()->away($order->download_url)->header('Referrer-Policy', 'no-referrer')->header('Cache-Control', 'no-store');
    }
}
