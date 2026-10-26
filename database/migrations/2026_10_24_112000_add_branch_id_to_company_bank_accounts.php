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
        if (Schema::hasTable('company_bank_accounts') && !Schema::hasColumn('company_bank_accounts', 'branch_id')) {
            Schema::table('company_bank_accounts', function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('account_holder')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('company_bank_accounts') && Schema::hasColumn('company_bank_accounts', 'branch_id')) {
            Schema::table('company_bank_accounts', function (Blueprint $table) {
                $table->dropColumn('branch_id');
            });
        }
    }
};
