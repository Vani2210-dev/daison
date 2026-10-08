<?php

namespace App\Http\Controllers;

use App\Models\SalesPolicy;
use Illuminate\Http\Request;

class SalesPolicyController extends Controller
{
    /**
     * Get bootstrap data for SPA initialization.
     */
    public function getBootstrapData(): array
    {
        return SalesPolicy::with(['freeProduct', 'creator'])
            ->latest('id')
            ->get()
            ->map(fn($p) => $this->formatPolicy($p))
            ->toArray();
    }

    /**
     * Display a listing of the sales policies.
     */
    public function index()
    {
        $policies = SalesPolicy::with(['freeProduct', 'creator'])
            ->latest('id')
            ->get()
            ->map(fn($p) => $this->formatPolicy($p));

        return response()->json([
            'success' => true,
            'data' => $policies
        ]);
    }

    /**
     * Store a newly created sales policy in storage.
     */
    public function store(Request $request)
    {
        $policyType = $request->input('policy_type') ?: $request->input('policyType', 'quantity');
        
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        if (!in_array($policyType, ['quantity', 'percentage', 'amount'])) {
            return response()->json([
                'success' => false,
                'message' => 'Loại chính sách không hợp lệ! (Phải là: quantity, percentage, hoặc amount)'
            ], 422);
        }

        $code = $request->input('code');
        if (!$code) {
            $nextNum = SalesPolicy::count() + 1;
            $code = 'CSBH-' . str_pad((string)$nextNum, 3, '0', STR_PAD_LEFT);
            while (SalesPolicy::where('code', $code)->exists()) {
                $nextNum++;
                $code = 'CSBH-' . str_pad((string)$nextNum, 3, '0', STR_PAD_LEFT);
            }
        }

        $customerIds = $request->input('target_customer_ids') !== null 
            ? $request->input('target_customer_ids') 
            : ($request->input('targetCustomerIds') ?: []);
        if (is_string($customerIds)) {
            $customerIds = array_filter(array_map('trim', explode(',', $customerIds)));
        }

        $productIds = $request->input('target_product_ids') !== null 
            ? $request->input('target_product_ids') 
            : ($request->input('targetProductIds') ?: []);
        if (is_string($productIds)) {
            $productIds = array_filter(array_map('trim', explode(',', $productIds)));
        }

        $buyQty = $request->input('buy_qty') ?? $request->input('buyQty');
        $freeQty = $request->input('free_qty') ?? $request->input('freeQty');
        $freeProductId = $request->input('free_product_id') ?? $request->input('freeProductId');
        $discountPercent = $request->input('discount_percent') ?? $request->input('discountPercent');
        $discountAmount = $request->input('discount_amount') ?? $request->input('discountAmount');
        $minOrderValue = $request->input('min_order_value') ?? $request->input('minOrderValue');
        $startDate = $request->input('start_date') ?? $request->input('startDate');
        $endDate = $request->input('end_date') ?? $request->input('endDate');

        $applyScope = $request->input('apply_scope') ?: $request->input('applyScope');
        if (!$applyScope) {
            $applyScope = ($policyType === 'quantity' || count($productIds) > 0) ? 'product' : 'order';
        }

        $policy = SalesPolicy::create([
            'code' => $code,
            'name' => $request->input('name'),
            'policy_type' => $policyType,
            'apply_scope' => $applyScope,
            'buy_qty' => $buyQty !== null && $buyQty !== '' ? (float)$buyQty : null,
            'free_qty' => $freeQty !== null && $freeQty !== '' ? (float)$freeQty : null,
            'free_product_id' => $freeProductId ?: null,
            'discount_percent' => $discountPercent !== null && $discountPercent !== '' ? (float)$discountPercent : null,
            'discount_amount' => $discountAmount !== null && $discountAmount !== '' ? (float)$discountAmount : null,
            'min_order_value' => $minOrderValue !== null && $minOrderValue !== '' ? (float)$minOrderValue : null,
            'target_customer_ids' => is_array($customerIds) ? array_values($customerIds) : [],
            'target_product_ids' => is_array($productIds) ? array_values($productIds) : [],
            'start_date' => $startDate ?: null,
            'end_date' => $endDate ?: null,
            'status' => $request->input('status') ?: 'Hoạt động',
            'notes' => $request->input('notes') ?: '',
            'created_by' => auth()->id(),
        ]);

        $policy->load(['freeProduct', 'creator']);

        return response()->json([
            'success' => true,
            'message' => 'Tạo chính sách bán hàng thành công!',
            'data' => $this->formatPolicy($policy)
        ], 201);
    }

    /**
     * Update the specified sales policy in storage.
     */
    public function update(Request $request, $id)
    {
        $policy = SalesPolicy::findOrFail($id);

        $policyType = $request->input('policy_type') ?: $request->input('policyType', $policy->policy_type);

        $customerIds = $request->input('target_customer_ids') !== null 
            ? $request->input('target_customer_ids') 
            : ($request->input('targetCustomerIds') !== null ? $request->input('targetCustomerIds') : $policy->target_customer_ids);
        if (is_string($customerIds)) {
            $customerIds = array_filter(array_map('trim', explode(',', $customerIds)));
        }

        $productIds = $request->input('target_product_ids') !== null 
            ? $request->input('target_product_ids') 
            : ($request->input('targetProductIds') !== null ? $request->input('targetProductIds') : $policy->target_product_ids);
        if (is_string($productIds)) {
            $productIds = array_filter(array_map('trim', explode(',', $productIds)));
        }

        $buyQty = $request->has('buy_qty') ? $request->input('buy_qty') : ($request->has('buyQty') ? $request->input('buyQty') : $policy->buy_qty);
        $freeQty = $request->has('free_qty') ? $request->input('free_qty') : ($request->has('freeQty') ? $request->input('freeQty') : $policy->free_qty);
        $freeProductId = $request->has('free_product_id') ? $request->input('free_product_id') : ($request->has('freeProductId') ? $request->input('freeProductId') : $policy->free_product_id);
        $discountPercent = $request->has('discount_percent') ? $request->input('discount_percent') : ($request->has('discountPercent') ? $request->input('discountPercent') : $policy->discount_percent);
        $discountAmount = $request->has('discount_amount') ? $request->input('discount_amount') : ($request->has('discountAmount') ? $request->input('discountAmount') : $policy->discount_amount);
        $minOrderValue = $request->has('min_order_value') ? $request->input('min_order_value') : ($request->has('minOrderValue') ? $request->input('minOrderValue') : $policy->min_order_value);
        $startDate = $request->has('start_date') ? $request->input('start_date') : ($request->has('startDate') ? $request->input('startDate') : $policy->start_date);
        $endDate = $request->has('end_date') ? $request->input('end_date') : ($request->has('endDate') ? $request->input('endDate') : $policy->end_date);

        $applyScope = $request->input('apply_scope') ?: $request->input('applyScope');
        if (!$applyScope) {
            $applyScope = $policy->apply_scope ?: (($policyType === 'quantity' || count($productIds) > 0) ? 'product' : 'order');
        }

        $policy->update([
            'name' => $request->input('name', $policy->name),
            'policy_type' => $policyType,
            'apply_scope' => $applyScope,
            'buy_qty' => $buyQty !== null && $buyQty !== '' ? (float)$buyQty : null,
            'free_qty' => $freeQty !== null && $freeQty !== '' ? (float)$freeQty : null,
            'free_product_id' => $freeProductId ?: null,
            'discount_percent' => $discountPercent !== null && $discountPercent !== '' ? (float)$discountPercent : null,
            'discount_amount' => $discountAmount !== null && $discountAmount !== '' ? (float)$discountAmount : null,
            'min_order_value' => $minOrderValue !== null && $minOrderValue !== '' ? (float)$minOrderValue : null,
            'target_customer_ids' => is_array($customerIds) ? array_values($customerIds) : [],
            'target_product_ids' => is_array($productIds) ? array_values($productIds) : [],
            'start_date' => $startDate ?: null,
            'end_date' => $endDate ?: null,
            'status' => $request->input('status', $policy->status),
            'notes' => $request->input('notes', $policy->notes),
        ]);

        $policy->load(['freeProduct', 'creator']);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật chính sách bán hàng thành công!',
            'data' => $this->formatPolicy($policy)
        ]);
    }

    /**
     * Remove the specified sales policy from storage.
     */
    public function destroy($id)
    {
        $policy = SalesPolicy::findOrFail($id);
        $policy->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa chính sách bán hàng thành công!'
        ]);
    }

    /**
     * Helper to format a SalesPolicy model into standard API structure.
     */
    private function formatPolicy(SalesPolicy $p): array
    {
        $policyType = $p->policy_type;
        $buyQty = $p->buy_qty !== null ? (float) $p->buy_qty : null;
        $freeQty = $p->free_qty !== null ? (float) $p->free_qty : null;
        $freeProductId = $p->free_product_id ? (string) $p->free_product_id : null;
        $freeProductName = $p->freeProduct?->name;
        $discountPercent = $p->discount_percent !== null ? (float) $p->discount_percent : null;
        $discountAmount = $p->discount_amount !== null ? (float) $p->discount_amount : null;
        $minOrderValue = $p->min_order_value !== null ? (float) $p->min_order_value : null;
        $targetCustomerIds = $p->target_customer_ids ?: [];
        $targetProductIds = $p->target_product_ids ?: [];
        $startDate = $p->start_date ? (is_string($p->start_date) ? substr($p->start_date, 0, 10) : $p->start_date->format('Y-m-d')) : null;
        $endDate = $p->end_date ? (is_string($p->end_date) ? substr($p->end_date, 0, 10) : $p->end_date->format('Y-m-d')) : null;
        $createdByName = $p->creator?->name ?: 'Ban Giám Đốc';
        $createdAt = $p->created_at ? $p->created_at->format('Y-m-d H:i:s') : null;

        return [
            'id' => (string) $p->id,
            'code' => $p->code,
            'name' => $p->name,
            // Direct backend columns
            'policy_type' => $policyType,
            'apply_scope' => $p->apply_scope ?: (($policyType === 'quantity' || count($targetProductIds) > 0) ? 'product' : 'order'),
            'applyScope' => $p->apply_scope ?: (($policyType === 'quantity' || count($targetProductIds) > 0) ? 'product' : 'order'),
            'buy_qty' => $buyQty,
            'free_qty' => $freeQty,
            'free_product_id' => $freeProductId,
            'free_product_name' => $freeProductName,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'min_order_value' => $minOrderValue,
            'target_customer_ids' => $targetCustomerIds,
            'target_product_ids' => $targetProductIds,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $p->status ?: 'Hoạt động',
            'notes' => $p->notes ?: '',
            'created_by' => $p->created_by,
            'created_by_name' => $createdByName,
            'created_at' => $createdAt,
            'updated_at' => $p->updated_at ? $p->updated_at->format('Y-m-d H:i:s') : null,

            // Aliases for compatibility
            'policyType' => $policyType,
            'buyQty' => $buyQty,
            'freeQty' => $freeQty,
            'freeProductId' => $freeProductId,
            'freeProductName' => $freeProductName,
            'discountPercent' => $discountPercent,
            'discountAmount' => $discountAmount,
            'minOrderValue' => $minOrderValue,
            'targetCustomerIds' => $targetCustomerIds,
            'targetProductIds' => $targetProductIds,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'createdByName' => $createdByName,
            'createdAt' => $createdAt,
        ];
    }
}
