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
        Schema::create('picking_sync_events', function (Blueprint $table) {
            $table->id();
            $table->string('order_id');
            $table->string('item_id')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('picked_qty', 12, 2)->default(0);
            $table->decimal('req_qty', 12, 2)->nullable();
            $table->string('item_status')->default('Chờ bốc');
            $table->string('shortage_reason')->nullable();
            $table->string('performer_name')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('picking_sync_events');
    }
};
