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
        // 1. Thêm trường phân loại nhân viên và công nợ nhân viên vào bảng users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'staff_type')) {
                $table->string('staff_type', 50)->default('company_sales')->after('department'); // driver, company_sales, dealer_sales, warehouse, accountant, manager
            }
            if (!Schema::hasColumn('users', 'dealer_id')) {
                $table->unsignedBigInteger('dealer_id')->nullable()->after('staff_type'); // Đại lý phụ thuộc (nếu staff_type là dealer_sales)
            }
            if (!Schema::hasColumn('users', 'credit_limit')) {
                $table->decimal('credit_limit', 15, 2)->default(0)->after('dealer_id'); // Hạn mức công nợ nhân viên
            }
            if (!Schema::hasColumn('users', 'current_debt')) {
                $table->decimal('current_debt', 15, 2)->default(0)->after('credit_limit'); // Công nợ hiện tại nhân viên đang chịu trách nhiệm
            }
        });

        // 2. Tạo bảng biên bản bàn giao công nợ (debt_handovers)
        if (!Schema::hasTable('debt_handovers')) {
            Schema::create('debt_handovers', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->string('order_code', 50)->nullable()->index();
                
                // Người bàn giao (thường là Lái xe / Người vận chuyển)
                $table->unsignedBigInteger('from_user_id')->nullable()->index();
                $table->string('from_user_name', 150)->nullable();
                
                // Người nhận nợ (Sale công ty, Sale đại lý, hoặc Kế toán tiếp nhận)
                $table->unsignedBigInteger('to_user_id')->nullable()->index();
                $table->string('to_user_name', 150)->nullable();
                $table->string('to_staff_type', 50)->nullable(); // company_sales, dealer_sales, etc.
                
                // Đại lý đích chịu nợ (nếu Sale thuộc đại lý phụ thuộc)
                $table->unsignedBigInteger('target_dealer_id')->nullable()->index();
                $table->string('target_dealer_name', 200)->nullable();

                $table->decimal('amount', 15, 2)->default(0);
                $table->string('handover_type', 50)->default('order_debt'); // order_debt, van_stock, manual
                $table->string('status', 50)->default('pending'); // pending (Chờ xác nhận), confirmed (Đã xác nhận), rejected (Từ chối)
                
                $table->text('notes')->nullable();
                
                // Thông tin xác nhận
                $table->unsignedBigInteger('confirmed_by')->nullable();
                $table->string('confirmed_by_name', 150)->nullable();
                $table->dateTime('confirmed_at')->nullable();
                
                // Thông tin từ chối
                $table->text('rejected_reason')->nullable();
                $table->dateTime('rejected_at')->nullable();

                $table->unsignedBigInteger('created_by_id')->nullable();
                $table->timestamps();
            });
        }

        // 3. Tạo bảng phiếu xuất hàng lẻ / hàng lưu động lên xe cho tài xế (van_sale_dispatches)
        if (!Schema::hasTable('van_sale_dispatches')) {
            Schema::create('van_sale_dispatches', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->unsignedBigInteger('driver_id')->index();
                $table->string('driver_name', 150);
                $table->string('vehicle_plate', 50)->nullable();
                $table->unsignedBigInteger('warehouse_id')->nullable();
                $table->string('warehouse_name', 150)->nullable();
                $table->date('dispatch_date');
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->string('status', 50)->default('dispatched'); // dispatched, settled, cancelled
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by_id')->nullable();
                $table->timestamps();
            });
        }

        // 4. Chi tiết hàng lẻ xuất xe (van_sale_dispatch_items)
        if (!Schema::hasTable('van_sale_dispatch_items')) {
            Schema::create('van_sale_dispatch_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('van_sale_dispatch_id')->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->string('sku', 100);
                $table->string('name', 255);
                $table->string('unit', 50)->default('cái');
                $table->decimal('quantity', 10, 2)->default(0);
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('amount', 15, 2)->default(0);
                $table->decimal('sold_quantity', 10, 2)->default(0);
                $table->decimal('returned_quantity', 10, 2)->default(0);
                $table->timestamps();

                $table->foreign('van_sale_dispatch_id')
                    ->references('id')->on('van_sale_dispatches')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('van_sale_dispatch_items');
        Schema::dropIfExists('van_sale_dispatches');
        Schema::dropIfExists('debt_handovers');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['staff_type', 'dealer_id', 'credit_limit', 'current_debt']);
        });
    }
};
