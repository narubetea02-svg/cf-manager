<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('portal_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('live_stream_id')->nullable();
            $table->string('sid', 50)->unique();
            $table->boolean('is_active')->default(true);
            $table->integer('connected_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('portal_sessions');
    }
};
