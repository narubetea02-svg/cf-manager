<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('messenger_order_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_mapping_id');
            $table->unsignedBigInteger('order_id');
            $table->string('status', 30)->default('attached');
            $table->string('matched_by', 50)->nullable();
            $table->string('confidence', 20)->nullable();
            $table->unsignedBigInteger('attached_by')->nullable();
            $table->timestamp('detached_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['customer_mapping_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messenger_order_links');
    }
};
