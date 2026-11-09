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
        Schema::table('debt_handovers', function (Blueprint $table) {
            if (!Schema::hasColumn('debt_handovers', 'documents')) {
                $table->json('documents')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('debt_handovers', 'accountant_id')) {
                $table->unsignedBigInteger('accountant_id')->nullable()->after('from_user_name');
            }
            if (!Schema::hasColumn('debt_handovers', 'accountant_name')) {
                $table->string('accountant_name', 255)->nullable()->after('accountant_id');
            }
            if (!Schema::hasColumn('debt_handovers', 'accountant_notes')) {
                $table->text('accountant_notes')->nullable()->after('accountant_name');
            }
            if (!Schema::hasColumn('debt_handovers', 'accountant_verified_at')) {
                $table->timestamp('accountant_verified_at')->nullable()->after('accountant_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('debt_handovers', function (Blueprint $table) {
            $cols = ['documents', 'accountant_id', 'accountant_name', 'accountant_notes', 'accountant_verified_at'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('debt_handovers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

