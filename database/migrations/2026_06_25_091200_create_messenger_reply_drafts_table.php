<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('messenger_reply_drafts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_mapping_id');
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('facebook_page_id')->nullable();
            $table->string('facebook_psid')->nullable();
            $table->text('draft_text');
            $table->string('status', 30)->default('dry_run');
            $table->boolean('send_enabled')->default(false);
            $table->json('preview_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messenger_reply_drafts');
    }
};
