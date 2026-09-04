<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customer_mappings', function (Blueprint $table) {
            $table->string('facebook_page_id')->nullable()->after('live_stream_id')->index();
            $table->timestamp('messenger_link_pending_at')->nullable()->after('messenger_ref')->index();
        });
    }

    public function down(): void
    {
        Schema::table('customer_mappings', function (Blueprint $table) {
            $table->dropIndex(['facebook_page_id']);
            $table->dropIndex(['messenger_link_pending_at']);
            $table->dropColumn(['facebook_page_id', 'messenger_link_pending_at']);
        });
    }
};
