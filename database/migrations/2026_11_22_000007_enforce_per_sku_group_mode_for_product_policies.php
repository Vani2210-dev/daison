<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('sales_policies') && Schema::hasColumn('sales_policies', 'qty_group_mode')) {
            DB::table('sales_policies')
                ->where('apply_scope', '!=', 'order')
                ->update(['qty_group_mode' => 'per_sku']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
