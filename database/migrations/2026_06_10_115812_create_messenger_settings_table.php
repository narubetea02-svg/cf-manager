<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('messenger_settings', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('shop_id');
            $table->string('fb_page_id')->nullable(); $table->text('fb_page_token')->nullable();
            $table->boolean('is_active')->default(true); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('messenger_settings'); }
};
