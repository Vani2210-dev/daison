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
        Schema::table('users', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('id');
            $table->string('phone')->nullable()->after('email');
            $table->string('department')->default('Sales')->after('phone'); // Sales, Accounting, Warehouse, Logistics, Executive
            $table->string('status')->default('Đang làm việc')->after('department'); // Đang làm việc, Tạm nghỉ, Đã nghỉ việc
            $table->decimal('base_salary', 15, 2)->default(0)->after('status');
            $table->string('branch')->nullable()->after('base_salary');
            $table->string('bank_account')->nullable()->after('branch');
            $table->string('bank_name')->nullable()->after('bank_account');
            $table->string('address')->nullable()->after('bank_name');
            $table->string('avatar')->nullable()->after('address');
            $table->string('affiliate_code')->nullable()->after('avatar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'code',
                'phone',
                'department',
                'status',
                'base_salary',
                'branch',
                'bank_account',
                'bank_name',
                'address',
                'avatar',
                'affiliate_code',
            ]);
        });
    }
};
