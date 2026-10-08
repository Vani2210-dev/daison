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
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'credit_overrun_status')) {
                    $table->string('credit_overrun_status')->nullable()->after('payment_status');
                }
                if (!Schema::hasColumn('orders', 'credit_overrun_amount')) {
                    $table->decimal('credit_overrun_amount', 15, 2)->default(0)->after('credit_overrun_status');
                }
                if (!Schema::hasColumn('orders', 'credit_overrun_approved_by')) {
                    $table->unsignedBigInteger('credit_overrun_approved_by')->nullable()->after('credit_overrun_amount');
                }
                if (!Schema::hasColumn('orders', 'credit_overrun_approved_name')) {
                    $table->string('credit_overrun_approved_name')->nullable()->after('credit_overrun_approved_by');
                }
                if (!Schema::hasColumn('orders', 'credit_overrun_approved_at')) {
                    $table->dateTime('credit_overrun_approved_at')->nullable()->after('credit_overrun_approved_name');
                }
                if (!Schema::hasColumn('orders', 'credit_overrun_reason')) {
                    $table->text('credit_overrun_reason')->nullable()->after('credit_overrun_approved_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $columns = [
                    'credit_overrun_status',
                    'credit_overrun_amount',
                    'credit_overrun_approved_by',
                    'credit_overrun_approved_name',
                    'credit_overrun_approved_at',
                    'credit_overrun_reason',
                ];
                foreach ($columns as $c) {
                    if (Schema::hasColumn('orders', $c)) {
                        $table->dropColumn($c);
                    }
                }
            });
        }
    }
};
