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
        // 1. Thêm documents vào bảng driver_cash_handovers
        Schema::table('driver_cash_handovers', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_cash_handovers', 'documents')) {
                $table->json('documents')->nullable()->after('notes');
            }
        });

        // 2. Thêm delivery_documents và recipient_name vào bảng orders
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'delivery_documents')) {
                $table->json('delivery_documents')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('orders', 'recipient_name')) {
                $table->string('recipient_name', 255)->nullable()->after('customer_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_cash_handovers', function (Blueprint $table) {
            if (Schema::hasColumn('driver_cash_handovers', 'documents')) {
                $table->dropColumn('documents');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'delivery_documents')) {
                $table->dropColumn('delivery_documents');
            }
            if (Schema::hasColumn('orders', 'recipient_name')) {
                $table->dropColumn('recipient_name');
            }
        });
    }
};
