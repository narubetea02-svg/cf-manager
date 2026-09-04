<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('order_id');
            $table->decimal('amount', 10, 2); $table->string('slip_image')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('verified_at')->nullable(); $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('payments'); }
};
