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
        if (!Schema::hasTable('warehouse_stocks')) {
            Schema::create('warehouse_stocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
                $table->decimal('stock_quantity', 12, 2)->default(0);
                $table->decimal('reserved_quantity', 12, 2)->default(0);
                $table->string('location')->nullable();
                $table->timestamps();

                $table->unique(['product_id', 'warehouse_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_stocks');
    }
};
