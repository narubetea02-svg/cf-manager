<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('customer_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('portal_session_id');
            $table->unsignedBigInteger('live_stream_id')->nullable();
            $table->string('tiktok_username');
            $table->string('status', 20)->default('connected');
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->foreign('portal_session_id')->references('id')->on('portal_sessions')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('customer_mappings');
    }
};
