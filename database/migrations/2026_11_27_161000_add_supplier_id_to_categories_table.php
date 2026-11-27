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
        if (Schema::hasTable('categories') && !Schema::hasColumn('categories', 'supplier_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->foreignId('supplier_id')->nullable()->after('id')->constrained('suppliers')->nullOnDelete();
            });

            // Tự động gán supplier_id cho các categories hiện có dựa vào products đang thuộc loại đó
            $categories = DB::table('categories')->get();
            foreach ($categories as $cat) {
                $prodSupplier = DB::table('products')
                    ->where('category_id', $cat->id)
                    ->whereNotNull('supplier_id')
                    ->value('supplier_id');
                if ($prodSupplier) {
                    DB::table('categories')->where('id', $cat->id)->update(['supplier_id' => $prodSupplier]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'supplier_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            });
        }
    }
};
