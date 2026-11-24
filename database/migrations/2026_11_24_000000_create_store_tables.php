<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('username')->nullable()->unique();
            $t->boolean('is_admin')->default(false);
        });
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description');
            $t->unsignedInteger('price');
            $t->text('download_url');
            $t->boolean('is_active')->default(false);
            $t->timestamps();
        });
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->restrictOnDelete();
            $t->string('product_name');
            $t->string('email');
            $t->unsignedInteger('amount');
            $t->string('access_hash', 64)->unique();
            $t->string('status')->default('pending');
            $t->text('download_url')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->foreignId('confirmed_by')->nullable()->constrained('users')->restrictOnDelete();
            $t->timestamps();
        });
        Schema::create('license_keys', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->restrictOnDelete();
            $t->text('key');
            $t->string('fingerprint', 64)->unique();
            $t->foreignId('order_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_keys');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['username', 'is_admin']));
    }
};
