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
        Schema::table('dealers', function (Blueprint $table) {
            $table->string('tax_code')->nullable()->after('phone'); // Mã số thuế
            $table->string('referrer')->nullable()->after('tax_code'); // Người giới thiệu
            $table->decimal('initial_debt', 15, 2)->default(0)->after('credit_limit'); // Nợ đầu kỳ
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dealers', function (Blueprint $table) {
            $table->dropColumn(['tax_code', 'referrer', 'initial_debt']);
        });
    }
};
