<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add description column to products
        Schema::table('products', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
        });

        // Create product_variants table
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('code')->nullable();  // รหัส CF
            $table->decimal('cost', 10, 2)->default(0);   // ต้นทุน
            $table->decimal('price', 10, 2)->default(0);  // ราคาขาย
            $table->integer('quantity')->default(0);       // จำนวน
            $table->decimal('weight', 8, 2)->nullable();  // น้ำหนัก
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
