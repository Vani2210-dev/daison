<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\FinancialFund;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    /**
     * Bootstrap data for Transactions & Funds in SPA
     */
    public function getBootstrapData()
    {
        return Transaction::with(['fund', 'order.items'])->get()->map(function ($t) {
            $details = $t->details;
            if (empty($details) && $t->order) {
                $orderItems = [];
                if ($t->order->items) {
                    foreach ($t->order->items as $it) {
                        $orderItems[] = [
                            'sku'        => $it->sku ?? $it->product_sku ?? '',
                            'name'       => $it->name ?? $it->product_name ?? 'Sản phẩm',
                            'unit'       => $it->unit ?? 'Cái',
                            'quantity'   => (float) ($it->quantity ?? ($it->req_qty ?? 1)),
                            'unitPrice'  => (float) ($it->unit_price ?? 0),
                            'totalPrice' => (float) ($it->total_price ?? (($it->quantity ?? 1) * ($it->unit_price ?? 0))),
                        ];
                    }
                }
                $details = [
                    'order' => [
                        'orderCode'    => $t->order->order_code,
                        'dealerName'   => $t->order->dealer_name ?: ($t->order->customer_name ?? 'Khách hàng'),
                        'address'      => $t->order->customer_address ?? '',
                        'phone'        => $t->order->customer_phone ?? '',
                        'orderTotal'   => (float) $t->order->total_amount,
                        'collectedCod' => (float) ($t->order->driver_debt_amount ?: $t->order->total_amount),
                        'items'        => $orderItems,
                    ],
                    'items' => $orderItems,
                ];
            }

            return [
                'id'                    => (string) $t->id,
                'code'                  => $t->code,
                'type'                  => $t->type,
                'fundType'              => $t->fund?->name ?? 'Quỹ Tiền Mặt',
                'category'              => $t->category,
                'amount'                => (float) $t->amount,
                'customerOrPartnerName' => $t->customer_partner_name,
                'orderCode'             => $t->order?->order_code ?? '',
                'createdByName'         => 'Đỗ Phương Mai',
                'date'                  => $t->date ? $t->date->format('Y-m-d') : date('Y-m-d'),
                'notes'                 => $t->notes ?? '',
                'receiptUrl'            => $t->receipt_url,
                'hasVatInvoice'         => (bool) $t->has_vat_invoice,
                'vatInvoiceNo'          => $t->vat_invoice_no,
                'taxCode'               => $t->tax_code,
                'vatRate'               => (float) $t->vat_rate,
                'vatAmount'             => (float) $t->vat_amount,
                'totalWithVat'          => (float) $t->total_with_vat,
                'details'               => $details,
            ];
        });
    }

    /**
     * API: Get all transactions
     */
    public function getTransactions()
    {
        return response()->json(['transactions' => $this->getBootstrapData()]);
    }

    /**
     * API: Save Transaction (Receipt/Payment Voucher) & Sync Debt
     */
    public function saveTransaction(Request $request)
    {
        $raw = [];
        if ($request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }
        $data = array_merge($request->all(), $raw);

        $fundType = $data['fundType'] ?? 'Quỹ Tiền Mặt';
        $fund = FinancialFund::where('name', $fundType)->first() ?: FinancialFund::first();

        $code = $data['code'] ?? ('PT-' . date('Y') . '-' . rand(1000, 9999));
        $type = $data['type'] ?? 'Thu';
        $amount = (float)($data['amount'] ?? 0);
        $customerName = $data['customerOrPartnerName'] ?? ($data['customer_partner_name'] ?? '');
        $orderCode = $data['orderCode'] ?? ($data['order_code'] ?? null);

        $orderId = null;
        $orderObj = null;
        if ($orderCode) {
            $orderObj = \App\Models\Order::with(['items', 'dealer'])->where('order_code', $orderCode)->first();
            if ($orderObj) {
                $orderId = $orderObj->id;
            }
        }

        $details = $data['details'] ?? null;
        if (empty($details) && $orderObj) {
            $orderItems = [];
            if ($orderObj->items) {
                foreach ($orderObj->items as $it) {
                    $orderItems[] = [
                        'sku'        => $it->sku ?? $it->product_sku ?? '',
                        'name'       => $it->name ?? $it->product_name ?? 'Sản phẩm',
                        'unit'       => $it->unit ?? 'Cái',
                        'quantity'   => (float) ($it->quantity ?? 1),
                        'unitPrice'  => (float) ($it->unit_price ?? 0),
                        'totalPrice' => (float) ($it->amount ?? (($it->quantity ?? 1) * ($it->unit_price ?? 0))),
                    ];
                }
            }
            $details = [
                'order' => [
                    'orderCode'    => $orderObj->order_code,
                    'dealerName'   => $orderObj->dealer_name ?: ($orderObj->customer_name ?: "Khách hàng {$orderObj->order_code}"),
                    'address'      => $orderObj->customer_address ?? ($orderObj->dealer?->address ?? ''),
                    'phone'        => $orderObj->customer_phone ?? ($orderObj->dealer?->phone ?? ''),
                    'orderTotal'   => (float) $orderObj->total_amount,
                    'collectedCod' => $amount,
                    'items'        => $orderItems,
                ],
                'items' => $orderItems,
            ];
        }

        $tx = Transaction::updateOrCreate(
            ['code' => $code],
            [
                'type'                  => $type,
                'fund_id'               => $fund?->id ?: 1,
                'category'              => $data['category'] ?? 'Thu nợ đại lý',
                'amount'                => $amount,
                'customer_partner_name' => $customerName,
                'order_id'              => $orderId,
                'created_by_id'         => auth()->id(),
                'date'                  => $data['date'] ?? date('Y-m-d'),
                'notes'                 => $data['notes'] ?? '',
                'details'               => $details,
            ]
        );

        if ($orderObj && $type === 'Thu') {
            $orderObj->update([
                'payment_status' => 'Đã nộp quỹ',
            ]);
        }

        // Cập nhật công nợ đại lý nếu có
        $updatedDealerDebt = null;
        $matchedDealer = null;
        if (!empty($customerName)) {
            $matchedDealer = \App\Models\Dealer::where('name', $customerName)
                ->orWhere('name', 'like', '%' . $customerName . '%')
                ->first();
            if ($matchedDealer) {
                $updatedDealerDebt = $matchedDealer->recalculateCurrentDebt();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Lưu phiếu thu chi thành công',
            'transaction' => [
                'id'                    => (string) $tx->id,
                'code'                  => $tx->code,
                'amount'                => (float) $tx->amount,
                'type'                  => $tx->type,
            ],
            'dealerId' => $matchedDealer ? (string)$matchedDealer->id : null,
            'dealerDebt' => $updatedDealerDebt,
        ]);
    }
}
