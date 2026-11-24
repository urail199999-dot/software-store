<?php

namespace Tests\Feature;

use App\Models\LicenseKey;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\FulfillOrder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        config(['recaptcha.site_key' => 'test-site', 'recaptcha.secret_key' => 'test-secret', 'recaptcha.hostnames' => ['localhost'], 'mail.default' => 'smtp']);
        Http::preventStrayRequests();
        $this->captcha();
    }

    private function captcha(array $response = ['success' => true, 'hostname' => 'localhost']): void
    {
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(['www.google.com/recaptcha/api/siteverify' => Http::response($response)]);
    }

    private function registration(array $extra = []): array
    {
        return array_merge(['name' => 'Покупатель', 'email' => 'buyer@example.com', 'password' => 'SecurePassword1999', 'password_confirmation' => 'SecurePassword1999', 'g-recaptcha-response' => 'token'], $extra);
    }

    private function order(User $user): Order
    {
        $product = Product::factory()->create();

        return Order::create(['user_id' => $user->id, 'product_id' => $product->id, 'product_name' => $product->name, 'email' => $user->email, 'amount' => $product->price, 'access_hash' => hash('sha256', str_repeat('a', 64))]);
    }

    public function test_registration_hashes_password_sends_verification_and_cannot_grant_admin(): void
    {
        $this->post('/register', $this->registration(['email' => 'BUYER@EXAMPLE.COM', 'is_admin' => true, 'email_verified_at' => now()]))->assertRedirect(route('verification.notice'));
        $user = User::where('email', 'buyer@example.com')->firstOrFail();
        $this->assertFalse($user->is_admin);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue(Hash::check('SecurePassword1999', $user->password));
        $this->assertAuthenticatedAs($user);
        Notification::assertSentTo($user, VerifyEmail::class);
        $this->get('/account')->assertRedirect(route('verification.notice'));
        $this->get('/admin')->assertForbidden();
    }

    public function test_registration_requires_valid_captcha_and_fails_closed_without_keys(): void
    {
        $this->captcha(['success' => false]);
        $this->post('/register', $this->registration())->assertSessionHasErrors('captcha');
        config(['recaptcha.secret_key' => null]);
        $this->post('/register', $this->registration())->assertSessionHasErrors('captcha');
        $this->assertDatabaseCount('users', 0);
        Notification::assertNothingSent();
    }

    public function test_captcha_rejects_wrong_hostname_and_network_failure(): void
    {
        $this->captcha(['success' => true, 'hostname' => 'evil.example']);
        $this->post('/register', $this->registration())->assertSessionHasErrors('captcha');
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(['www.google.com/recaptcha/api/siteverify' => Http::failedConnection()]);
        $this->post('/register', $this->registration())->assertSessionHasErrors('captcha');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_missing_captcha_weak_password_and_duplicate_email_are_rejected(): void
    {
        $this->post('/register', $this->registration(['g-recaptcha-response' => '']))->assertSessionHasErrors('g-recaptcha-response');
        $this->post('/register', $this->registration(['password' => 'weak', 'password_confirmation' => 'weak']))->assertSessionHasErrors('password');
        User::factory()->create(['email' => 'buyer@example.com']);
        $this->post('/register', $this->registration())->assertSessionHasErrors('email');
    }

    public function test_signed_verification_link_unlocks_account_and_expires(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->actingAs($user)->get($url)->assertRedirect(route('account'));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->get('/account')->assertOk()->assertSee('Email подтверждён');
        $other = User::factory()->unverified()->create();
        $this->actingAs($other)->get($url)->assertForbidden();
        $expired = URL::temporarySignedRoute('verification.verify', now()->subMinute(), ['id' => $other->id, 'hash' => sha1($other->email)]);
        $this->get($expired)->assertForbidden();
        $this->assertFalse($other->fresh()->hasVerifiedEmail());
    }

    public function test_modified_verification_link_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->actingAs($user)->get($url.'&extra=1')->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_resend_verification_is_rate_limited(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);
        $this->post(route('verification.send'))->assertRedirect();
        Notification::assertSentTo($user, VerifyEmail::class);
        $this->post(route('verification.send'))->assertTooManyRequests();
    }

    public function test_verified_customer_can_login_and_logout(): void
    {
        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $this->post('/login', ['email' => 'BUYER@EXAMPLE.COM', 'password' => 'password', 'g-recaptcha-response' => 'token'])->assertRedirect(route('account'));
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect(route('store'));
        $this->assertGuest();
    }

    public function test_unverified_customer_is_redirected_to_verification_and_login_attempts_are_limited(): void
    {
        $user = User::factory()->unverified()->create();
        $data = ['email' => $user->email, 'password' => 'wrong', 'g-recaptcha-response' => 'token'];
        for ($index = 0; $index < 5; $index++) {
            $this->post('/login', $data)->assertSessionHasErrors('email');
        }
        $this->post('/login', array_merge($data, ['password' => 'password']))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_unverified_login_succeeds_only_to_notice(): void
    {
        $user = User::factory()->unverified()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'password', 'g-recaptcha-response' => 'token'])->assertRedirect(route('verification.notice'));
    }

    public function test_orders_are_private_and_download_opens_only_after_payment(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);
        $other = User::factory()->create();
        $this->get('/account')->assertRedirect(route('login'));
        $this->actingAs($other)->get(route('account.order', $order))->assertNotFound();
        $this->get(route('account.download', $order))->assertNotFound();
        $this->get('/account')->assertDontSee($order->product_name);
        $this->actingAs($user)->get('/account')->assertOk()->assertSee($order->product_name);
        $this->get(route('account.download', $order))->assertForbidden();
        LicenseKey::create(['product_id' => $order->product_id, 'key' => 'PRIVATE-LICENSE']);
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        app(FulfillOrder::class)->handle($order, $admin);
        $this->get(route('account.order', $order))->assertOk()->assertSee('PRIVATE-LICENSE')->assertDontSee('software.zip');
        $this->get(route('account.download', $order))->assertRedirect('https://example.com/software.zip');
        $this->actingAs($other)->get('/orders/'.str_repeat('a', 64))->assertNotFound();
    }

    public function test_checkout_attaches_only_current_verified_customer(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        LicenseKey::create(['product_id' => $product->id, 'key' => 'KEY']);
        $this->actingAs($user)->post(route('checkout', $product), ['email' => 'other@example.com', 'user_id' => 999])->assertRedirect();
        $order = Order::firstOrFail();
        $this->assertEquals($user->id, $order->user_id);
        $this->assertEquals($user->email, $order->email);
        $this->get(route('account.order', $order))->assertOk();
    }

    public function test_unverified_customer_cannot_checkout(): void
    {
        $user = User::factory()->unverified()->create();
        $product = Product::factory()->create();
        $this->actingAs($user)->post(route('checkout', $product), ['email' => $user->email])->assertRedirect(route('verification.notice'));
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_reset_password_changes_hash_and_token_cannot_be_reused(): void
    {
        $user = User::factory()->create();
        $this->post(route('password.email'), ['email' => $user->email, 'g-recaptcha-response' => 'token'])->assertRedirect();
        Notification::assertSentTo($user, ResetPassword::class);
        $token = Password::createToken($user);
        $data = ['email' => $user->email, 'token' => $token, 'password' => 'NewPassword12345', 'password_confirmation' => 'NewPassword12345'];
        $this->post(route('password.update'), $data)->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('NewPassword12345', $user->fresh()->password));
        $this->post(route('password.update'), $data)->assertSessionHasErrors('email');
    }

    public function test_customer_password_reset_does_not_reset_administrator(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $token = Password::createToken($admin);
        $this->post(route('password.update'), ['email' => $admin->email, 'token' => $token, 'password' => 'NewPassword12345', 'password_confirmation' => 'NewPassword12345'])->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('password', $admin->fresh()->password));
    }

    public function test_log_mailer_does_not_allow_registration_to_appear_live(): void
    {
        config(['mail.default' => 'log']);
        $this->post('/register', $this->registration())->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 0);
    }
}
