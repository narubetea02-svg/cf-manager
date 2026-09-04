<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('live_streams', fn(Blueprint $t) => $t->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade'));
        Schema::table('products', fn(Blueprint $t) => $t->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade'));
        Schema::table('orders', function(Blueprint $t) {
            $t->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $t->foreign('product_id')->references('id')->on('products')->onDelete('set null');
        });
        Schema::table('payments', fn(Blueprint $t) => $t->foreign('order_id')->references('id')->on('orders')->onDelete('cascade'));
        Schema::table('line_settings', fn(Blueprint $t) => $t->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade'));
        Schema::table('messenger_settings', fn(Blueprint $t) => $t->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade'));
        Schema::table('shipments', fn(Blueprint $t) => $t->foreign('order_id')->references('id')->on('orders')->onDelete('cascade'));
    }
    public function down(): void {
        Schema::table('live_streams', fn(Blueprint $t) => $t->dropForeign(['shop_id']));
        Schema::table('products', fn(Blueprint $t) => $t->dropForeign(['shop_id']));
        Schema::table('orders', fn(Blueprint $t) => $t->dropForeign(['shop_id', 'product_id']));
        Schema::table('payments', fn(Blueprint $t) => $t->dropForeign(['order_id']));
        Schema::table('line_settings', fn(Blueprint $t) => $t->dropForeign(['shop_id']));
        Schema::table('messenger_settings', fn(Blueprint $t) => $t->dropForeign(['shop_id']));
        Schema::table('shipments', fn(Blueprint $t) => $t->dropForeign(['order_id']));
    }
};
