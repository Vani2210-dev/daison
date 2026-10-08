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
            $table->integer('annual_leave_days')->default(12)->after('base_salary');
            $table->decimal('remaining_leave_days', 4, 1)->default(12.0)->after('annual_leave_days');
            $table->date('end_date')->nullable()->after('remaining_leave_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'annual_leave_days',
                'remaining_leave_days',
                'end_date',
            ]);
        });
    }
};
