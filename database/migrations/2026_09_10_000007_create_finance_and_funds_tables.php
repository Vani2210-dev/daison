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
        // 1. Financial Funds Table (Sổ quỹ tiền mặt & Tài khoản ngân hàng)
        Schema::create('financial_funds', function (Blueprint $table) {
            $table->id();
            $table->string('name');                   // Quỹ Tiền Mặt, Tài Khoản MBBank, Vietcombank
            $table->string('code')->unique();         // FUND_CASH, FUND_MB, FUND_VCB
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->decimal('current_balance', 15, 2)->default(0); // Số dư hiện tại
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Transactions Table (Phiếu Thu / Chi & Hóa đơn VAT)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();         // PT-2026-0001, PC-2026-0001
            $table->string('type');                   // Thu, Chi
            $table->foreignId('fund_id')->constrained('financial_funds')->onDelete('restrict');
            $table->string('category');               // Thu tiền hàng, Thu nợ, Chi nhập hàng, Chi xăng...
            $table->decimal('amount', 15, 2);         // Tiền trước VAT
            $table->string('customer_partner_name');  // Khách hàng / Nhà cung cấp
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->foreignId('created_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('date');
            $table->text('notes')->nullable();
            $table->string('receipt_url')->nullable();
            
            // Hóa đơn VAT điện tử
            $table->boolean('has_vat_invoice')->default(false);
            $table->string('vat_invoice_no')->nullable(); // Số HĐĐT (vd: 0012890)
            $table->string('tax_code')->nullable();       // Mã số thuế
            $table->decimal('vat_rate', 5, 2)->default(10); // 0, 5, 8, 10%
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('total_with_vat', 15, 2)->default(0);
            $table->timestamps();
        });

        // 3. Driver Cash Collections (Bảng kê quyết toán thu tiền mặt giao hàng của Lái xe)
        Schema::create('driver_cash_collections', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();         // BKTT-2026-001
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->decimal('total_collected', 15, 2);
            $table->string('status')->default('Đang lập'); // Đang lập, Đã nộp kế toán, Đã nhập quỹ
            $table->foreignId('confirmed_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_cash_collections');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('financial_funds');
    }
};
