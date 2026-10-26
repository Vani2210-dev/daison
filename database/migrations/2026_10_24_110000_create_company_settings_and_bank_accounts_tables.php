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
        // 1. Bảng thông tin cấu hình công ty (Company Settings)
        if (!Schema::hasTable('company_settings')) {
            Schema::create('company_settings', function (Blueprint $table) {
                $table->id();
                $table->string('company_name')->default('Công ty Cổ phần Đại Sơn');
                $table->string('company_legal_name')->nullable()->default('CÔNG TY CỔ PHẦN THƯƠNG MẠI VÀ DỊCH VỤ ĐẠI SƠN');
                $table->string('tax_code', 50)->nullable()->default('0109888999');
                $table->string('phone', 50)->nullable()->default('024 3888 9999');
                $table->string('hotline', 50)->nullable()->default('1900 8899');
                $table->string('email')->nullable()->default('contact@daison.vn');
                $table->string('address')->nullable()->default('Số 123 Đường Cầu Giấy, Phường Quan Hoa, Quận Cầu Giấy, Hà Nội');
                $table->string('warehouse_address')->nullable()->default('Kho Tổng KCN Quang Minh, Mê Linh, Hà Nội');
                $table->string('website')->nullable()->default('https://daison.vn');
                $table->string('representative_name', 100)->nullable()->default('Nguyễn Văn Đại');
                $table->string('representative_title', 100)->nullable()->default('Tổng Giám Đốc');
                $table->string('logo_url')->nullable()->default('/images/logo-daison.png');
                $table->text('invoice_footer_note')->nullable();
                $table->text('order_terms')->nullable();
                $table->unsignedBigInteger('default_bank_account_id')->nullable();
                $table->timestamps();
            });

            // Tạo bản ghi mặc định ban đầu
            DB::table('company_settings')->insert([
                'company_name'         => 'Công ty Cổ phần Đại Sơn',
                'company_legal_name'   => 'CÔNG TY CỔ PHẦN THƯƠNG MẠI VÀ DỊCH VỤ ĐẠI SƠN',
                'tax_code'             => '0109888999',
                'phone'                => '024 3888 9999',
                'hotline'              => '1900 8899',
                'email'                => 'contact@daison.vn',
                'address'              => 'Số 123 Đường Cầu Giấy, Phường Quan Hoa, Quận Cầu Giấy, Hà Nội',
                'warehouse_address'    => 'Kho Tổng KCN Quang Minh, Mê Linh, Hà Nội',
                'website'              => 'https://daison.vn',
                'representative_name'  => 'Nguyễn Văn Đại',
                'representative_title' => 'Tổng Giám Đốc',
                'logo_url'             => '/images/logo-daison.png',
                'invoice_footer_note'  => 'Cảm ơn Quý khách hàng & Quý đối tác đã tin tưởng đồng hành cùng Đại Sơn!',
                'order_terms'          => 'Hàng hóa xuất kho được bảo hành theo tiêu chuẩn nhà sản xuất. Đổi trả trong 7 ngày nếu lỗi kỹ thuật.',
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }

        // 2. Bảng danh sách tài khoản ngân hàng của công ty (Company Bank Accounts)
        if (!Schema::hasTable('company_bank_accounts')) {
            Schema::create('company_bank_accounts', function (Blueprint $table) {
                $table->id();
                $table->string('bank_name'); // Tên ngân hàng (e.g. MB Bank, Vietcombank, Techcombank, ...)
                $table->string('bank_code', 50)->nullable(); // Mã ngân hàng (e.g. MB, VCB, TCB, ...)
                $table->string('account_number', 50); // Số tài khoản
                $table->string('account_holder'); // Tên chủ tài khoản
                $table->string('branch')->nullable(); // Chi nhánh
                $table->boolean('is_default')->default(false); // Tài khoản mặc định nhận thanh toán
                $table->boolean('is_active')->default(true); // Trạng thái hoạt động
                $table->string('qr_template', 50)->default('compact2'); // Mẫu VietQR (compact, compact2, qr_only)
                $table->text('notes')->nullable(); // Mục đích sử dụng: nhận tiền bán hàng, nộp thuế, ...
                $table->timestamps();
            });

            // Tạo tài khoản ngân hàng mặc định ban đầu (MB Bank)
            DB::table('company_bank_accounts')->insert([
                [
                    'bank_name'      => 'MB Bank (Quân Đội)',
                    'bank_code'      => 'MB',
                    'account_number' => '0903888999',
                    'account_holder' => 'CONG TY CP TM VA DV DAI SON',
                    'branch'         => 'Hội sở chính - Hà Nội',
                    'is_default'     => true,
                    'is_active'      => true,
                    'qr_template'    => 'compact2',
                    'notes'          => 'Tài khoản chính nhận chuyển khoản đơn hàng & thanh toán đại lý',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ],
                [
                    'bank_name'      => 'Vietcombank',
                    'bank_code'      => 'VCB',
                    'account_number' => '1022888999',
                    'account_holder' => 'CONG TY CP TM VA DV DAI SON',
                    'branch'         => 'Chi nhánh Thăng Long',
                    'is_default'     => false,
                    'is_active'      => true,
                    'qr_template'    => 'compact2',
                    'notes'          => 'Tài khoản thanh toán Nhà cung cấp & đối tác lớn',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_bank_accounts');
        Schema::dropIfExists('company_settings');
    }
};
