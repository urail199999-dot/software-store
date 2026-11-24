<?php

namespace Tests\Feature;

use App\Filament\Auth\Login;
use App\Models\LicenseKey;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\FulfillOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    private function product(): Product
    {
        return Product::create(['name' => 'Программа', 'slug' => 'test', 'description' => 'Описание', 'price' => 19900, 'download_url' => 'https://example.com/program.zip', 'is_active' => true]);
    }

    private function admin(): User
    {
        $u = User::factory()->create();
        $u->forceFill(['username' => 'admin', 'is_admin' => true])->save();

        return $u;
    }

    private function order(Product $p, string $token): Order
    {
        return Order::create(['product_id' => $p->id, 'product_name' => $p->name, 'email' => 'buyer@example.com', 'amount' => $p->price, 'access_hash' => hash('sha256', $token)]);
    }

    public function test_catalog_does_not_expose_download_or_key(): void
    {
        $p = $this->product();
        LicenseKey::create(['product_id' => $p->id, 'key' => 'SECRET-KEY']);
        $this->get('/')->assertOk()->assertSee('Программа')->assertDontSee('program.zip')->assertDontSee('SECRET-KEY');
    }

    public function test_checkout_uses_server_price_and_hides_delivery_until_paid(): void
    {
        $p = $this->product();
        LicenseKey::create(['product_id' => $p->id, 'key' => 'SECRET-KEY']);
        $response = $this->post('/checkout/'.$p->id, ['email' => 'buyer@example.com', 'amount' => 1]);
        $response->assertRedirect();
        $this->assertDatabaseHas('orders', ['amount' => 19900, 'status' => 'pending']);
        $url = $response->headers->get('Location');
        $this->get($url)->assertOk()->assertDontSee('SECRET-KEY')->assertDontSee('program.zip');
        $this->get($url.'/download')->assertForbidden();
    }

    public function test_paid_order_receives_exactly_one_key_idempotently(): void
    {
        $p = $this->product();
        $key = LicenseKey::create(['product_id' => $p->id, 'key' => 'SECRET-KEY']);
        $token = str_repeat('a', 64);
        $order = $this->order($p, $token);
        $admin = $this->admin();
        $service = app(FulfillOrder::class);
        $service->handle($order, $admin);
        $service->handle($order, $admin);
        $this->assertEquals($order->id, $key->fresh()->order_id);
        $this->assertEquals(1, LicenseKey::whereNotNull('order_id')->count());
        $this->get('/orders/'.$token)->assertOk()->assertSee('SECRET-KEY');
        $this->get('/orders/'.$token.'/download')->assertRedirect('https://example.com/program.zip');
        $this->assertStringNotContainsString('SECRET-KEY', DB::table('license_keys')->value('key'));
    }

    public function test_key_cannot_be_assigned_to_second_order(): void
    {
        $p = $this->product();
        LicenseKey::create(['product_id' => $p->id, 'key' => 'ONLY-KEY']);
        $admin = $this->admin();
        app(FulfillOrder::class)->handle($this->order($p, str_repeat('a', 64)), $admin);
        $second = $this->order($p, str_repeat('b', 64));
        try {
            app(FulfillOrder::class)->handle($second, $admin);
            $this->fail('Must reject out of stock');
        } catch (ValidationException $e) {
            $this->assertEquals('pending', $second->fresh()->status);
        }
    }

    public function test_unpublished_and_sold_out_products_cannot_be_ordered(): void
    {
        $p = $this->product();
        $this->post('/checkout/'.$p->id, ['email' => 'a@example.com'])->assertSessionHasErrors('stock');
        $p->update(['is_active' => false]);
        $this->post('/checkout/'.$p->id, ['email' => 'a@example.com'])->assertNotFound();
    }

    public function test_unknown_order_token_is_rejected(): void
    {
        $this->get('/orders/'.str_repeat('z', 64))->assertNotFound();
    }

    public function test_regular_user_cannot_access_admin(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/products')->assertForbidden();
    }

    public function test_admin_pages_render(): void
    {
        $this->actingAs($this->admin());
        foreach (['/admin', '/admin/products', '/admin/license-keys', '/admin/orders'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_admin_can_login_with_username(): void
    {
        $this->admin();
        Livewire::test(Login::class)->fillForm(['email' => 'admin', 'password' => 'password'])->call('authenticate')->assertHasNoFormErrors();
        $this->assertAuthenticated();
    }
}
