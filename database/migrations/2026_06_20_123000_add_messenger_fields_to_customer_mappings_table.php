<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customer_mappings', function (Blueprint $table) {
            $table->string('facebook_psid')->nullable()->after('live_stream_id')->index();
            $table->string('facebook_name')->nullable()->after('facebook_psid');
            $table->string('messenger_ref')->nullable()->after('facebook_name')->index();
            $table->string('connected_source', 30)->nullable()->after('messenger_ref');
        });
    }

    public function down(): void
    {
        Schema::table('customer_mappings', function (Blueprint $table) {
            $table->dropIndex(['facebook_psid']);
            $table->dropIndex(['messenger_ref']);
            $table->dropColumn(['facebook_psid', 'facebook_name', 'messenger_ref', 'connected_source']);
        });
    }
};
