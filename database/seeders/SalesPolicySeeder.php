<?php

namespace Database\Seeders;

use App\Models\Dealer;
use App\Models\SalesPolicy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesPolicySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get Dealer IDs by Tier
        $dealers = Dealer::select('id', 'tier')->get();

        $leLonIds = [];
        $leNhoIds = [];
        foreach ($dealers as $d) {
            $idStr = (string)$d->id;
            if (str_contains($d->tier ?? '', 'Lẻ lớn')) {
                $leLonIds[] = $idStr;
            } elseif (str_contains($d->tier ?? '', 'Lẻ nhỏ')) {
                $leNhoIds[] = $idStr;
            }
        }
        $leLonNhoIds = array_values(array_unique(array_merge($leLonIds, $leNhoIds)));

        // 2. Map Product groups
        // KĐR Ngọc Châu TT & Trắng sáng
        $p_kdr_tt_ts = ['25', '26', '27', '28']; // NC0806, NC0808, NC0811, NC0804
        // KĐR Ngọc Châu CG
        $p_kdr_cg = ['29', '30']; // NC0819, NC0821
        // KĐR Ngọc Châu Trẻ em 70g
        $p_kdr_te_70g = ['24']; // NC0805
        // KĐR Ngọc Châu Trẻ em 50g
        $p_kdr_te_50g = ['22', '23']; // NC0809 (dâu), NC0822 (chuối)
        // DG/DX Nguyên Xuân size nhỏ (200ml/208g/250ml/260g & Xả 150ml/160g)
        $p_dg_nx_small = ['32', '33', '34', '35', '39', '40', '41', '42', '43', '44', '45', '46', '47', '48', '49', '50'];
        // DG/DX Nguyên Xuân size vừa (350ml/365g & Xả 300ml/318g)
        $p_dg_nx_med = ['36', '37', '51', '52', '53', '54', '55', '56'];
        // DG Nguyên Xuân size lớn (450ml/470g)
        $p_dg_nx_large = ['57', '58', '59', '60', '61', '62'];
        // Dạ Hương phiên bản mới (4 loại)
        $p_dh_new = ['14', '15', '16', '17']; // DH0219, DH0220, DH0221, DH0222
        // Hàng truyền thống (KĐR Ngọc Châu các loại, DG/DX Nguyên Xuân, DDVS Dạ Hương - trừ Daily Fresh)
        $p_truyen_thong = array_values(array_unique(array_merge(
            $p_kdr_tt_ts, $p_kdr_cg, $p_kdr_te_70g, $p_kdr_te_50g, ['21', '31'],
            $p_dg_nx_small, $p_dg_nx_med, $p_dg_nx_large, ['38', '63', '64', '65', '66', '67', '68'],
            $p_dh_new, ['13']
        )));
        // Nhóm hàng phát triển (DH Daily Fresh 120ml; ST Nguyên Xuân; DG Nguyên Vương)
        $p_phat_trien = ['18', '19', '20', '85', '86', '69', '70', '71', '72', '73', '74', '75', '76', '77'];
        // Nhóm tư vấn - Zuchi & Greelux
        $p_zuchi_greelux = ['1', '2', '3', '4', '5', '6'];
        // Bàn chải Ngọc Châu
        $p_banchai_nc = ['78', '79', '80', '81', '82'];
        // Nhóm tư vấn đầy đủ (Zuchi, Greelux, BVS Dạ Hương, NSM Ngọc Châu, NSM Hương Biển, Trà Bảo Bảo, ST Ngọc Thảo, ST Ongbi, Bàn chải, NRT Mỹ Ngọc)
        $p_nhom_tu_van = array_values(array_unique(array_merge(
            $p_zuchi_greelux, $p_banchai_nc,
            ['83', '84', '8', '7', '9', '10', '11', '12']
        )));

        // Specific gift products:
        $free_kdr_te_dau_50g = 22; // NC0809
        $free_dg_nx_260g = 49;     // NX0906G Dưỡng tóc 260g
        $free_dg_nx_365g = 51;     // NX0913GKM Dưỡng tóc 365g
        $free_dg_nx_470g = 58;     // NX0925GKM Dưỡng tóc 470g
        $free_dh_mem_min = 17;     // DH0222 Dạ Hương mềm mịn

        $startDate = '2026-01-01';
        $endDate = '2028-12-31';

        $policies = [
            // I. NHÓM HÀNG TRUYỀN THỐNG - LẺ LỚN 1 (Mua 12 tặng 1, Mua 21 tặng 2)
            [
                'code' => 'CS-LL-01',
                'name' => 'CTKM Lẻ lớn 1 - KĐR Ngọc Châu truyền thống, trắng sáng (Mua 12 tặng 1, Mua 21 tặng 2)',
                'policy_type' => 'quantity',
                'apply_scope' => 'product',
                'buy_qty' => 12,
                'free_qty' => 1,
                'free_product_id' => null,
                'gift_tiers' => [
                    ['buy_qty' => 12, 'free_qty' => 1, 'free_product_id' => null],
                    ['buy_qty' => 21, 'free_qty' => 2, 'free_product_id' => null],
                ],
                'discount_percent' => null,
                'min_order_value' => null,
                'target_customer_ids' => $leLonIds,
                'target_product_ids' => $p_kdr_tt_ts,
                'notes' => 'Mua 12 tuýp cùng size bất kỳ tặng 01 tuýp Ngọc Châu TT cùng size; hoặc mua 21 tuýp tặng 02 tuýp cùng size',
            ],
            [
                'code' => 'CS-LL-02',
                'name' => 'CTKM Lẻ lớn 1 - KĐR Ngọc Châu chuyên gia 100g, 170g (Mua 12 tặng 1, Mua 21 tặng 2)',
                'policy_type' => 'quantity',
                'apply_scope' => 'product',
                'buy_qty' => 12,
                'free_qty' => 1,
                'free_product_id' => null,
                'gift_tiers' => [
                    ['buy_qty' => 12, 'free_qty' => 1, 'free_product_id' => null],
                    ['buy_qty' => 21, 'free_qty' => 2, 'free_product_id' => null],
                ],
                'discount_percent' => null,
                'min_order_value' => null,
                'target_customer_ids' => $leLonIds,
                'target_product_ids' => $p_kdr_cg,
                'notes' => 'Mua 12 tuýp cùng size bất kỳ tặng 01 tuýp Ngọc Châu CG cùng size; hoặc mua 21 tuýp tặng 02 tuýp cùng size',
            ],
            [
                'code' => 'CS-LL-03',
                'name' => 'CTKM Lẻ lớn 1 - KĐR Ngọc Châu trẻ em 70g (Mua 12 tặng 1, Mua 21 tặng 2)',
                'policy_type' => 'quantity',
                'apply_scope' => 'product',
                'buy_qty' => 12,
                'free_qty' => 1,
                'free_product_id' => 24,
                'gift_tiers' => [
                    ['buy_qty' => 12, 'free_qty' => 1, 'free_product_id' => 24],
                    ['buy_qty' => 21, 'free_qty' => 2, 'free_product_id' => 24],
                ],
                'discount_percent' => null,
                'min_order_value' => null,
                'target_customer_ids' => $leLonIds,
                'target_product_ids' => $p_kdr_te_70g,
                'notes' => 'Mua 12 tuýp tặng 01 tuýp KĐR Ngọc Châu trẻ em 70g; hoặc mua 21 tuýp tặng 02 tuýp',
            ],
            [
                'code' => 'CS-LL-04',
                'name' => 'CTKM Lẻ lớn 1 - KĐR Ngọc Châu trẻ em 50g (Mua 12 tặng 1, Mua 21 tặng 2)',
                'policy_type' => 'quantity',
                'apply_scope' => 'product',
                'buy_qty' => 12,
                'free_qty' => 1,
                'free_product_id' => $free_kdr_te_dau_50g,
                'gift_tiers' => [
                    ['buy_qty' => 12, 'free_qty' => 1, 'free_product_id' => $free_kdr_te_dau_50g],
                    ['buy_qty' => 21, 'free_qty' => 2, 'free_product_id' => $free_kdr_te_dau_50g],
                ],
                'discount_percent' => null,
                'min_order_value' => null,
                'target_customer_ids' => $leLonIds,
                'target_product_ids' => $p_kdr_te_50g,
                'notes' => 'Mua 12 tuýp bất kỳ tặng 01 tuýp KĐR Ngọc Châu trẻ em 50g hương dâu; hoặc mua 21 tuýp bất kỳ tặng 02 tuýp',
            ],
            [
                'code' => 'CS-LL-05',
                'name' => 'CTKM Lẻ lớn 1 - Dầu gội/xả Nguyên Xuân 200ml/250ml/150ml (Mua 12 tặng 1, Mua 21 tặng 2)',
                'policy_type' => 'quantity',
                'apply_scope' => 'product',
                'buy_qty' => 12,
                'free_qty' => 1,
                'free_product_id' => $free_dg_nx_260g,
                'gift_tiers' => [
                    ['buy_qty' => 12, 'free_qty' => 1, 'free_product_id' => $free_dg_nx_260g],
                    ['buy_qty' => 21, 'free_qty' => 2, 'free_product_id' => $free_dg_nx_260g],
                ],
                'discount_percent' => null,
                'min_order_value' => null,
                'target_customer_ids' => $leLonIds,
                'target_product_ids' => $p_dg_nx_small,
                'notes' => 'Mua 12 sản phẩm bất kỳ tặng 01 chai DG Nguyễn Xuân DT 260g; hoặc mua 21 sản phẩm tặng 02 chai DG Nguyễn Xuân DT 260g',
            ],
            [
                'code' => 'CS-LL-06',
                'name' => 'CTKM Lẻ lớn 1 - Dầu gội/xả Nguyên Xuân 350ml/300ml (Mua 12 tặng 1, Mua 21 tặng 2)',
                'policy_type' => 'quantity',
                'apply_scope' => 'product',
                'buy_qty' => 12,
                'free_qty' => 1,
                'free_product_id' => $free_dg_nx_365g,
                'gift_tiers' => [
                    ['buy_qty' => 12, 'free_qty' => 1, 'free_product_id' => $free_dg_nx_365g],
                    ['buy_qty' => 21, 'free_qty' => 2, 'free_product_id' => $free_dg_nx_365g],
                ],
                'discount_percent' => null,
                'min_order_value' => null,
                'target_customer_ids' => $leLonIds,
                'target_product_ids' => $p_dg_nx_med,
                'notes' => 'Mua 12 chai bất kỳ tặng 01 chai DG Nguyễn Xuân DT 365g; hoặc mua 21 chai bất kỳ tặng 02 chai DG Nguyễn Xuân DT 365g',
            ],
            [
                'code' => 'CS-LL-07',
                'name' => 'CTKM Lẻ lớn 1 - Dầu gội Nguyên Xuân 450ml/470g (Mua 12 tặng 1, Mua 21 tặng 2)',
                'policy_type' => 'quantity',
                'apply_scope' => 'product',
                'buy_qty' => 12,
                'free_qty' => 1,
                'free_product_id' => $free_dg_nx_470g,
                'gift_tiers' => [
                    ['buy_qty' => 12, 'free_qty' => 1, 'free_product_id' => $free_dg_nx_470g],
                    ['buy_qty' => 21, 'free_qty' => 2, 'free_product_id' => $free_dg_nx_470g],
                ],
                'discount_percent' => null,
                'min_order_value' => null,
                'target_customer_ids' => $leLonIds,
                'target_product_ids' => $p_dg_nx_large,
                'notes' => 'Mua 12 chai bất kỳ tặng 01 chai DG Nguyễn Xuân DT 470g; hoặc mua 21 chai bất kỳ tặng 02 chai DG Nguyễn Xuân DT 470g',
            ],
            [
                'code' => 'CS-LL-08',
                'name' => 'CTKM Lẻ lớn 1 - Dạ Hương phiên bản mới 4 loại (Mua 12 tặng 1, Mua 21 tặng 2)',
                'policy_type' => 'quantity',
                'apply_scope' => 'product',
                'buy_qty' => 12,
                'free_qty' => 1,
                'free_product_id' => $free_dh_mem_min,
                'gift_tiers' => [
                    ['buy_qty' => 12, 'free_qty' => 1, 'free_product_id' => $free_dh_mem_min],
                    ['buy_qty' => 21, 'free_qty' => 2, 'free_product_id' => $free_dh_mem_min],
                ],
                'discount_percent' => null,
                'min_order_value' => null,
                'target_customer_ids' => $leLonIds,
                'target_product_ids' => $p_dh_new,
                'notes' => 'Mua 12 chai bất kỳ tặng 01 chai DH mềm mịn; hoặc mua 21 chai bất kỳ tặng 02 chai DH mềm mịn',
            ],
            // I. NHÓM HÀNG TRUYỀN THỐNG - LẺ LỚN 2 (Chiết khấu 7% đơn ≥ 600k)
            [
                'code' => 'CS-LL-09',
                'name' => 'CTKM Lẻ lớn 2 - Chiết khấu đơn hàng từ 600.000đ (7%)',
                'policy_type' => 'percentage',
                'apply_scope' => 'order',
                'buy_qty' => null,
                'free_qty' => null,
                'free_product_id' => null,
                'gift_tiers' => null,
                'discount_percent' => 7.0,
                'min_order_value' => 600000,
                'target_customer_ids' => $leLonIds,
                'target_product_ids' => $p_truyen_thong,
                'notes' => 'Đơn hàng nhóm hàng truyền thống ≥ 600.000 VNĐ chiết khấu ngay trên đơn 7,0%',
            ],

            // I. NHÓM HÀNG TRUYỀN THỐNG - LẺ NHỎ 1 (Mua 8 tặng 1)
            [
                'code' => 'CS-LN-01',
                'name' => 'CTKM Lẻ nhỏ 1 - KĐR Ngọc Châu truyền thống, trắng sáng (Mua 8 tặng 1)',
                'policy_type' => 'quantity',
                'apply_scope' => 'product',
                'buy_qty' => 8,
                'free_qty' => 1,
                'free_product_id' => null,
                'gift_tiers' => [
                    ['buy_qty' => 8, 'free_qty' => 1, 'free_product_id' => null],
                ],
                'discount_percent' => null,
                'min_order_value' => null,
                'target_customer_ids' => $leNhoIds,
                'target_product_ids' => $p_kdr_tt_ts,
                'notes' => 'Mua 08 tuýp cùng size bất kỳ tặng 01 tuýp KĐR Ngọc Châu TT cùng size',
            ],
            [
                'code' => 'CS-LN-02',
                'name' => 'CTKM Lẻ nhỏ 1 - KĐR Ngọc Châu chuyên gia 100g, 170g (Mua 8 tặng 1)',
                'policy_type' => 'quantity',
                'apply_scope' => 'product',
                'buy_qty' => 8,
                'free_qty' => 1,
                'free_product_id' => null,
                'gift_tiers' => [
                    ['buy_qty' => 8, 'free_qty' => 1, 'free_product_id' => null],
                ],
                'discount_percent' => null,
                'min_order_value' => null,
                'target_customer_ids' => $leNhoIds,
                'target_product_ids' => $p_kdr_cg,
                'notes' => 'Mua 08 tuýp cùng size bất kỳ tặng 01 tuýp KĐR Ngọc Châu CG cùng size',
            ],
            [
                'code' => 'CS-LN-03',
                'name' => 'CTKM Lẻ nhỏ 1 - KĐR Ngọc Châu trẻ em 70g (Mua 8 tặng 1)',
                'policy_type' => 'quantity',
                'apply_scope' => 'product',
                'buy_qty' => 8,
                'free_qty' => 1,
                'free_product_id' => 24,
                'gift_tiers' => [
                    ['buy_qty' => 8, 'free_qty' => 1, 'free_product_id' => 24],
                ],
                'discount_percent' => null,
                'min_order_value' => null,
                'target_customer_ids' => $leNhoIds,
                'target_product_ids' => $p_kdr_te_70g,
                'notes' => 'Mua 08 tuýp tặng 01 tuýp KĐR Ngọc Châu trẻ em 70g',
            ],
            [
                'code' => 'CS-LN-04',
                'name' => 'CTKM Lẻ nhỏ 1 - KĐR Ngọc Châu trẻ em 50g (Mua 8 tặng 1)',
                'policy_type' => 'quantity',
                'apply_scope' => 'product',
                'buy_qty' => 8,
                'free_qty' => 1,
                'free_product_id' => $free_kdr_te_dau_50g,
                'gift_tiers' => [
                    ['buy_qty' => 8, 'free_qty' => 1, 'free_product_id' => $free_kdr_te_dau_50g],
                ],
                'discount_percent' => null,
                'min_order_value' => null,
                'target_customer_ids' => $leNhoIds,
                'target_product_ids' => $p_kdr_te_50g,
                'notes' => 'Mua 08 tuýp bất kỳ tặng 01 tuýp KĐR Ngọc Châu trẻ em 50g hương dâu',
            ],
            [
                'code' => 'CS-LN-05',
                'name' => 'CTKM Lẻ nhỏ 1 - Dầu gội/xả Nguyên Xuân 200ml/250ml/150ml (Mua 8 tặng 1)',
                'policy_type' => 'quantity',
                'apply_scope' => 'product',
                'buy_qty' => 8,
                'free_qty' => 1,
                'free_product_id' => $free_dg_nx_260g,
                'gift_tiers' => [
                    ['buy_qty' => 8, 'free_qty' => 1, 'free_product_id' => $free_dg_nx_260g],
                ],
                'discount_percent' => null,
                'min_order_value' => null,
                'target_customer_ids' => $leNhoIds,
                'target_product_ids' => $p_dg_nx_small,
                'notes' => 'Mua 08 sản phẩm bất kỳ tặng 01 chai DG Nguyễn Xuân DT 260g',
            ],
            [
                'code' => 'CS-LN-06',
                'name' => 'CTKM Lẻ nhỏ 1 - Dầu gội/xả Nguyên Xuân 350ml/300ml (Mua 8 tặng 1)',
                'policy_type' => 'quantity',
                'apply_scope' => 'product',
                'buy_qty' => 8,
                'free_qty' => 1,
                'free_product_id' => $free_dg_nx_365g,
                'gift_tiers' => [
                    ['buy_qty' => 8, 'free_qty' => 1, 'free_product_id' => $free_dg_nx_365g],
                ],
                'discount_percent' => null,
                'min_order_value' => null,
                'target_customer_ids' => $leNhoIds,
                'target_product_ids' => $p_dg_nx_med,
                'notes' => 'Mua 08 sản phẩm bất kỳ tặng 01 chai DG Nguyễn Xuân DT 365g',
            ],
            [
                'code' => 'CS-LN-07',
                'name' => 'CTKM Lẻ nhỏ 1 - Dầu gội Nguyên Xuân 450ml/470g (Mua 8 tặng 1)',
                'policy_type' => 'quantity',
                'apply_scope' => 'product',
                'buy_qty' => 8,
                'free_qty' => 1,
                'free_product_id' => $free_dg_nx_470g,
                'gift_tiers' => [
                    ['buy_qty' => 8, 'free_qty' => 1, 'free_product_id' => $free_dg_nx_470g],
                ],
                'discount_percent' => null,
                'min_order_value' => null,
                'target_customer_ids' => $leNhoIds,
                'target_product_ids' => $p_dg_nx_large,
                'notes' => 'Mua 08 chai bất kỳ tặng 01 chai DG Nguyễn Xuân DT 470g',
            ],
            [
                'code' => 'CS-LN-08',
                'name' => 'CTKM Lẻ nhỏ 1 - Dạ Hương phiên bản mới 4 loại (Mua 8 tặng 1)',
                'policy_type' => 'quantity',
                'apply_scope' => 'product',
                'buy_qty' => 8,
                'free_qty' => 1,
                'free_product_id' => $free_dh_mem_min,
                'gift_tiers' => [
                    ['buy_qty' => 8, 'free_qty' => 1, 'free_product_id' => $free_dh_mem_min],
                ],
                'discount_percent' => null,
                'min_order_value' => null,
                'target_customer_ids' => $leNhoIds,
                'target_product_ids' => $p_dh_new,
                'notes' => 'Mua 08 chai bất kỳ tặng 01 chai DH mềm mịn',
            ],
            // I. NHÓM HÀNG TRUYỀN THỐNG - LẺ NHỎ 2 (Chiết khấu 10% đơn ≥ 400k)
            [
                'code' => 'CS-LN-09',
                'name' => 'CTKM Lẻ nhỏ 2 - Chiết khấu đơn hàng từ 400.000đ (10%)',
                'policy_type' => 'percentage',
                'apply_scope' => 'order',
                'buy_qty' => null,
                'free_qty' => null,
                'free_product_id' => null,
                'gift_tiers' => null,
                'discount_percent' => 10.0,
                'min_order_value' => 400000,
                'target_customer_ids' => $leNhoIds,
                'target_product_ids' => $p_truyen_thong,
                'notes' => 'Đơn hàng nhóm hàng truyền thống ≥ 400.000 VNĐ chiết khấu ngay trên đơn 10%',
            ],

            // II. NHÓM HÀNG PHÁT TRIỂN (Chiết khấu theo số lượng)
            [
                'code' => 'CS-PT-01',
                'name' => 'Nhóm hàng phát triển - Mua từ 2 đến 4 SP chiết khấu 18%',
                'policy_type' => 'percentage',
                'apply_scope' => 'product',
                'buy_qty' => 2,
                'free_qty' => null,
                'free_product_id' => null,
                'gift_tiers' => null,
                'discount_percent' => 18.0,
                'min_order_value' => null,
                'target_customer_ids' => $leLonNhoIds,
                'target_product_ids' => $p_phat_trien,
                'notes' => 'Mua từ 02 tới 04 sản phẩm cùng nhãn hàng chiết khấu ngay trên đơn 18%',
            ],
            [
                'code' => 'CS-PT-02',
                'name' => 'Nhóm hàng phát triển - Mua từ 5 SP trở lên chiết khấu 20%',
                'policy_type' => 'percentage',
                'apply_scope' => 'product',
                'buy_qty' => 5,
                'free_qty' => null,
                'free_product_id' => null,
                'gift_tiers' => null,
                'discount_percent' => 20.0,
                'min_order_value' => null,
                'target_customer_ids' => $leLonNhoIds,
                'target_product_ids' => $p_phat_trien,
                'notes' => 'Mua ≥ 05 sản phẩm cùng nhãn hàng chiết khấu ngay trên đơn 20%',
            ],

            // III. NHÓM HÀNG TƯ VẤN
            [
                'code' => 'CS-TV-01',
                'name' => 'Nhóm hàng tư vấn - Zuchi & Greelux mua cùng loại tặng hàng (Mua 4 tặng 1, Mua 20 tặng 6)',
                'policy_type' => 'quantity',
                'apply_scope' => 'product',
                'buy_qty' => 4,
                'free_qty' => 1,
                'free_product_id' => null,
                'gift_tiers' => [
                    ['buy_qty' => 4, 'free_qty' => 1, 'free_product_id' => null],
                    ['buy_qty' => 20, 'free_qty' => 6, 'free_product_id' => null],
                ],
                'discount_percent' => null,
                'min_order_value' => null,
                'target_customer_ids' => $leLonNhoIds,
                'target_product_ids' => $p_zuchi_greelux,
                'notes' => 'Mua 04 SP cùng loại tặng 01 SP cùng loại; hoặc mua 20 SP cùng loại tặng 06 SP cùng loại',
            ],
            [
                'code' => 'CS-TV-02',
                'name' => 'Nhóm hàng tư vấn - Chiết khấu đơn hàng từ 400.000đ (18%)',
                'policy_type' => 'percentage',
                'apply_scope' => 'order',
                'buy_qty' => null,
                'free_qty' => null,
                'free_product_id' => null,
                'gift_tiers' => null,
                'discount_percent' => 18.0,
                'min_order_value' => 400000,
                'target_customer_ids' => $leLonNhoIds,
                'target_product_ids' => $p_nhom_tu_van,
                'notes' => 'Đơn hàng nhóm hàng tư vấn ≥ 400.000 VNĐ chiết khấu ngay trên đơn 18,0%',
            ],
            [
                'code' => 'CS-TV-03',
                'name' => 'Nhóm hàng tư vấn - Bàn chải Ngọc Châu mua 5 tặng 1',
                'policy_type' => 'quantity',
                'apply_scope' => 'product',
                'buy_qty' => 5,
                'free_qty' => 1,
                'free_product_id' => null,
                'gift_tiers' => [
                    ['buy_qty' => 5, 'free_qty' => 1, 'free_product_id' => null],
                ],
                'discount_percent' => null,
                'min_order_value' => null,
                'target_customer_ids' => $leLonNhoIds,
                'target_product_ids' => $p_banchai_nc,
                'notes' => 'Mua 05 sản phẩm bàn chải cùng loại tặng 01 sản phẩm cùng loại',
            ],
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        SalesPolicy::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($policies as $p) {
            SalesPolicy::create([
                'code' => $p['code'],
                'name' => $p['name'],
                'policy_type' => $p['policy_type'],
                'apply_scope' => $p['apply_scope'],
                'buy_qty' => $p['buy_qty'],
                'free_qty' => $p['free_qty'],
                'free_product_id' => $p['free_product_id'],
                'gift_tiers' => $p['gift_tiers'],
                'discount_percent' => $p['discount_percent'],
                'discount_amount' => null,
                'min_order_value' => $p['min_order_value'],
                'target_customer_ids' => $p['target_customer_ids'],
                'target_product_ids' => $p['target_product_ids'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'Hoạt động',
                'notes' => $p['notes'],
                'created_by' => 1,
            ]);
        }
    }
}
