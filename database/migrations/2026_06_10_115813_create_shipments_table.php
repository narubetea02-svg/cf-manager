<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('order_id');
            $table->string('carrier')->nullable(); $table->string('tracking_number');
            $table->string('status', 20)->default('pending');
            $table->timestamp('shipped_at')->nullable(); $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('shipments'); }
};
