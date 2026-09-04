<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('messenger_reply_drafts', function (Blueprint $table) {
            $table->timestamp('sent_at')->nullable()->after('preview_payload');
            $table->json('response_payload')->nullable()->after('sent_at');
            $table->text('failure_reason')->nullable()->after('response_payload');
        });
    }

    public function down(): void
    {
        Schema::table('messenger_reply_drafts', function (Blueprint $table) {
            $table->dropColumn(['sent_at', 'response_payload', 'failure_reason']);
        });
    }
};
