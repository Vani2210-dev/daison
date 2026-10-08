<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('price_tiers')) {
            Schema::create('price_tiers', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->nullable()->unique();
                $table->string('name');
                $table->string('status')->default('Hoạt động');
                $table->integer('order_num')->default(0);
                $table->timestamps();
            });

            // Insert initial default tiers if empty
            DB::table('price_tiers')->insert([
                ['code' => 'TIER_1', 'name' => 'Mức giá 1 (Giá sỉ/Đại lý)', 'status' => 'Hoạt động', 'order_num' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'TIER_2', 'name' => 'Mức giá 2 (Khách thân thiết)', 'status' => 'Hoạt động', 'order_num' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'TIER_RETAIL', 'name' => 'Giá bán lẻ tiêu chuẩn', 'status' => 'Hoạt động', 'order_num' => 3, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_tiers');
    }
};
