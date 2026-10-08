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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'failed_delivery_reason')) {
                $table->string('failed_delivery_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('orders', 'failed_delivery_note')) {
                $table->text('failed_delivery_note')->nullable()->after('failed_delivery_reason');
            }
            if (!Schema::hasColumn('orders', 'failed_delivery_at')) {
                $table->string('failed_delivery_at')->nullable()->after('failed_delivery_note');
            }
            if (!Schema::hasColumn('orders', 'failed_delivery_by')) {
                $table->string('failed_delivery_by')->nullable()->after('failed_delivery_at');
            }
            if (!Schema::hasColumn('orders', 'rescheduled_date')) {
                $table->string('rescheduled_date')->nullable()->after('failed_delivery_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $cols = ['failed_delivery_reason', 'failed_delivery_note', 'failed_delivery_at', 'failed_delivery_by', 'rescheduled_date'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
