<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankingWebhookController extends Controller
{
    /**
     * Webhook nhận biến động số dư ngân hàng và tự động trừ công nợ khách hàng
     * Hỗ trợ các cổng: SePay, Casso, MBBank, Techcombank, VietQR IPN hoặc API nội bộ
     */
    public function handleIncomingPayment(Request $request)
    {
        // 1. Đọc payload từ ngân hàng / webhook
        $raw = $request->all();
        if (empty($raw) && $request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }

        $content = (string) (
            $raw['content'] ?? 
            ($raw['description'] ?? 
            ($raw['memo'] ?? 
            ($raw['transaction_content'] ?? 
            ($raw['addInfo'] ?? ''))))
        );

        $amount = (float) (
            $raw['amount'] ?? 
            ($raw['transferAmount'] ?? 
            ($raw['creditAmount'] ?? 
            ($raw['so_tien'] ?? 0)))
        );

        $refNo = (string) (
            $raw['referenceNumber'] ?? 
            ($raw['code'] ?? 
            ($raw['transaction_id'] ?? 
            ($raw['trans_id'] ?? ('BANK-' . now()->format('YmdHis') . '-' . rand(100, 999)))))
        );

        $inputCustomerCode = $raw['customerCode'] ?? ($raw['dealerCode'] ?? null);
        $inputOrderCode = $raw['orderCode'] ?? null;

        // 2. Phân tích trích xuất Mã Khách Hàng (Đại lý) và Mã Đơn Hàng từ nội dung chuyển khoản
        $customerCode = $inputCustomerCode;
        $orderCode = $inputOrderCode;

        if (empty($customerCode)) {
            // So khớp với mã đại lý thực tế trong CSDL
            $allDealers = Dealer::select('id', 'code', 'name')->get();
            $normalizedContent = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($content));

            foreach ($allDealers as $d) {
                if ($d->code) {
                    $normalizedCode = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($d->code));
                    if (!empty($normalizedCode) && str_contains($normalizedContent, $normalizedCode)) {
                        $customerCode = $d->code;
                        break;
                    }
                }
            }

            // Nếu chưa tìm thấy, dùng regex tìm DL, KH, DS...
            if (empty($customerCode)) {
                if (preg_match('/(DL|KH|DS|MQ)[-_]?[0-9]+/i', $content, $matches)) {
                    $customerCode = strtoupper($matches[0]);
                }
            }
        }

        // Tìm mã đơn hàng từ nội dung
        if (empty($orderCode)) {
            if (preg_match('/(DH|DS|MQ)[-_]?[0-9A-Za-z]+/i', $content, $matches)) {
                $orderCode = strtoupper($matches[0]);
            }
        }

        // 3. Tìm đối tượng Đại lý và Đơn hàng
        $dealer = null;
        if ($customerCode) {
            $cleanedCode = preg_replace('/[^A-Za-z0-9]/', '', $customerCode);
            $dealer = Dealer::where('code', $customerCode)
                ->orWhere(DB::raw("REPLACE(REPLACE(code, '-', ''), '_', '')"), $cleanedCode)
                ->first();
        }

        $order = null;
        if ($orderCode) {
            $cleanedOrderCode = preg_replace('/[^A-Za-z0-9]/', '', $orderCode);
            $order = Order::where('order_code', $orderCode)
                ->orWhere(DB::raw("REPLACE(REPLACE(order_code, '-', ''), '_', '')"), $cleanedOrderCode)
                ->first();
        }

        // Nếu có đơn hàng nhưng chưa xác định đại lý, lấy đại lý từ đơn hàng
        if (!$dealer && $order) {
            if ($order->dealer_id) {
                $dealer = Dealer::where('id', $order->dealer_id)
                    ->orWhere('code', $order->dealer_id)
                    ->first();
            }
            if (!$dealer && $order->customer_name) {
                $dealer = Dealer::where('name', $order->customer_name)
                    ->orWhere('name', 'like', '%' . trim($order->customer_name) . '%')
                    ->first();
            }
        }

        if (!$dealer && !$order) {
            return response()->json([
                'success'   => false,
                'message'   => 'Không xác định được khách hàng hoặc đơn hàng từ nội dung chuyển khoản: "' . $content . '"',
                'extracted' => [
                    'customerCode' => $customerCode,
                    'orderCode'    => $orderCode,
                    'amount'       => $amount,
                    'content'      => $content
                ]
            ], 422);
        }

        // Nếu số tiền bằng 0 mà có đơn hàng, lấy theo số tiền đơn hàng
        if ($amount <= 0 && $order) {
            $amount = (float) ($order->total_amount ?? 0);
        }

        // 4. Thực hiện cấn trừ công nợ và ghi nhận giao dịch
        DB::beginTransaction();
        try {
            $oldDebt = 0;
            $newDebt = 0;

            if ($dealer) {
                $oldDebt = (float) $dealer->current_debt;
                $newDebt = max(0, $oldDebt - $amount);
                $dealer->current_debt = $newDebt;
                $dealer->save();
            }

            if ($order) {
                $order->payment_status = 'Đã thanh toán QR';
                $order->payment_method = 'VietQR';
                $order->save();
            }

            // Tạo phiếu thu tài chính vào Sổ quỹ
            $txCode = 'PT-QR-' . date('Ymd-His') . '-' . rand(10, 99);
            $tx = Transaction::create([
                'code'                  => $txCode,
                'type'                  => 'Thu',
                'fund_id'               => 2, // 2: Quỹ Ngân hàng (VietQR MBBank)
                'created_by_id'         => auth()->id() ?? 1,
                'category'              => 'Thu nợ đại lý',
                'amount'                => $amount,
                'customer_partner_name' => $dealer?->name ?? ($order?->customer_name ?? 'Khách hàng VietQR'),
                'order_id'              => $order?->id,
                'date'                  => now()->format('Y-m-d'),
                'notes'                 => "Ngân hàng tự động nhận tiền qua VietQR (+".number_format($amount, 0, ',', '.')." đ). Mã GD: [{$refNo}]. Nội dung: [{$content}]. Đã cấn trừ công nợ khách hàng [{$dealer?->code} - {$dealer?->name}].",
            ]);

            // Ghi nhật ký kiểm toán AuditLog
            AuditLog::create([
                'timestamp'            => now()->format('Y-m-d H:i:s'),
                'performer_name'       => auth()->user()?->name ?? 'VietQR Ngân Hàng Auto',
                'user_id'              => auth()->id() ?? 1,
                'action'               => 'Tự Động Trừ Công Nợ VietQR',
                'module'               => 'Kế Toán & Công Nợ',
                'target_document_code' => $dealer?->code ?? ($order?->order_code ?? 'VIETQR'),
                'field_name'           => 'Công nợ đại lý',
                'old_value'            => number_format($oldDebt, 0, ',', '.') . ' đ',
                'new_value'            => number_format($newDebt, 0, ',', '.') . ' đ',
                'notes'                => "Khách hàng {$dealer?->name} ({$dealer?->code}) chuyển khoản thành công ".number_format($amount, 0, ',', '.')." đ. Đã tự động giảm trừ công nợ từ ".number_format($oldDebt, 0, ',', '.')." đ xuống ".number_format($newDebt, 0, ',', '.')." đ. Mã phiếu thu: {$txCode}.",
            ]);

            DB::commit();

            return response()->json([
                'success'         => true,
                'message'         => "✅ Đã tự động nhận diện khách hàng [{$dealer?->code}] và cấn trừ " . number_format($amount, 0, ',', '.') . " đ vào công nợ!",
                'amount'          => $amount,
                'dealer'          => $dealer ? [
                    'id'          => (string) $dealer->id,
                    'code'        => $dealer->code,
                    'name'        => $dealer->name,
                    'oldDebt'     => $oldDebt,
                    'newDebt'     => $newDebt,
                ] : null,
                'order'           => $order ? [
                    'id'          => (string) $order->id,
                    'orderCode'   => $order->order_code,
                    'paymentStatus' => $order->payment_status,
                ] : null,
                'transactionCode' => $tx->code,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xử lý cấn trừ công nợ tự động: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API kiểm tra hoặc kích hoạt xác nhận thanh toán VietQR cho một đơn hàng cụ thể
     */
    public function checkOrConfirmOrderPayment(Request $request)
    {
        $orderCode = $request->input('orderCode');
        $orderId = $request->input('orderId');
        $customerCode = $request->input('customerCode');
        $amount = (float) $request->input('amount', 0);

        $order = null;
        if ($orderId) {
            $order = Order::find($orderId);
        }
        if (!$order && $orderCode) {
            $order = Order::where('order_code', $orderCode)->first();
        }

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        if ($amount <= 0) {
            $amount = (float) ($order->total_amount ?? 0);
        }

        $dealer = null;
        if ($customerCode) {
            $dealer = Dealer::where('code', $customerCode)->first();
        }
        if (!$dealer && $order->dealer_id) {
            $dealer = Dealer::where('id', $order->dealer_id)->orWhere('code', $order->dealer_id)->first();
        }
        if (!$dealer && $order->customer_name) {
            $dealer = Dealer::where('name', $order->customer_name)->first();
        }

        // Tạo request giả lập chuyển khoản để gọi handleIncomingPayment
        $transferContent = "TT " . ($dealer?->code ?? 'DL001') . " " . $order->order_code;
        $simulatedRequest = Request::create('/api/banking/webhook', 'POST', [
            'content'      => $transferContent,
            'amount'       => $amount,
            'orderCode'    => $order->order_code,
            'customerCode' => $dealer?->code,
            'referenceNumber' => 'SIM-QR-' . date('His'),
        ]);

        return $this->handleIncomingPayment($simulatedRequest);
    }
}
