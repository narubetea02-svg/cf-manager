<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('customer_name')->nullable(); $table->string('customer_phone')->nullable();
            $table->string('customer_username'); $table->string('code');
            $table->integer('quantity')->default(1); $table->decimal('total_price', 10, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->string('slip_image')->nullable(); $table->string('tracking_number')->nullable();
            $table->text('notes')->nullable(); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('orders'); }
};
