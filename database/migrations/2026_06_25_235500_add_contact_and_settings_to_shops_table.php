<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('email')->nullable()->after('description');
            $table->string('phone')->nullable()->after('email');
            $table->string('country')->nullable()->after('phone');
            $table->text('address')->nullable()->after('country');
            $table->string('sub_district')->nullable()->after('address');
            $table->string('postal_code')->nullable()->after('sub_district');
            $table->string('instagram')->nullable()->after('logo');
            $table->json('settings')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'phone',
                'country',
                'address',
                'sub_district',
                'postal_code',
                'instagram',
                'settings'
            ]);
        });
    }
};
