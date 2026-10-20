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
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('price', 15, 2)->default(0)->after('cost_price'); // Giá bán theo ĐVT chuẩn
            });

            // Populate initial 'price' from existing 'price_tiers'
            $products = DB::table('products')->select('id', 'price_tiers', 'cost_price')->get();
            foreach ($products as $p) {
                $tiers = json_decode($p->price_tiers ?? '[]', true);
                $extractedPrice = 0;
                if (is_array($tiers) && !empty($tiers)) {
                    // Try to find retail price tier first
                    foreach ($tiers as $tier) {
                        $name = strtolower($tier['name'] ?? '');
                        if (str_contains($name, 'bán lẻ') || str_contains($name, 'tiêu chuẩn')) {
                            $extractedPrice = (float)($tier['price'] ?? 0);
                            break;
                        }
                    }
                    // If not found, take the last tier or first positive
                    if ($extractedPrice <= 0) {
                        $lastTier = end($tiers);
                        $extractedPrice = (float)($lastTier['price'] ?? 0);
                    }
                    if ($extractedPrice <= 0) {
                        foreach ($tiers as $tier) {
                            if ((float)($tier['price'] ?? 0) > 0) {
                                $extractedPrice = (float)$tier['price'];
                                break;
                            }
                        }
                    }
                }
                if ($extractedPrice <= 0 && (float)$p->cost_price > 0) {
                    $extractedPrice = (float)$p->cost_price;
                }

                DB::table('products')->where('id', $p->id)->update([
                    'price' => $extractedPrice
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('price');
            });
        }
    }
};
