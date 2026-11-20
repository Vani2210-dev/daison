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
        if (Schema::hasTable('products')) {
            $products = DB::table('products')->get();
            foreach ($products as $p) {
                $curPrice = (float)($p->price ?? 0);
                $costPrice = (float)($p->cost_price ?? 0);
                
                if ($curPrice <= 0 && !empty($p->price_tiers)) {
                    $tiers = json_decode($p->price_tiers, true);
                    if (is_array($tiers)) {
                        foreach ($tiers as $tier) {
                            $name = mb_strtolower($tier['name'] ?? '', 'UTF-8');
                            if (str_contains($name, 'bán lẻ') || str_contains($name, 'tiêu chuẩn') || str_contains($name, 'giá bán')) {
                                if ((float)($tier['price'] ?? 0) > 0) {
                                    $curPrice = (float)$tier['price'];
                                    break;
                                }
                            }
                        }
                        if ($curPrice <= 0) {
                            foreach ($tiers as $tier) {
                                if ((float)($tier['price'] ?? 0) > 0) {
                                    $curPrice = (float)$tier['price'];
                                    break;
                                }
                            }
                        }
                    }
                }

                if ($curPrice <= 0 && $costPrice > 0) {
                    $curPrice = $costPrice;
                }

                DB::table('products')
                    ->where('id', $p->id)
                    ->update([
                        'price' => $curPrice,
                        'price_tiers' => null,
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
