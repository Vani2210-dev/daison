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
        // 1. Roles Table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');                      // Ban Giám Đốc, Kế toán bán hàng, v.v.
            $table->string('slug')->unique();            // Manager, Sales_Accountant, etc.
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Permissions Table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');                      // Xem đơn hàng & Xuất kho
            $table->string('slug')->unique();            // orders.view
            $table->string('module');                    // orders, inventory, funds, etc.
            $table->string('category');                  // Bán hàng, Kho hàng, Tài chính, v.v.
            $table->string('action');                    // view, create, edit, delete, approve, export
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Default Role Permissions Table
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->unique(['role_id', 'permission_id']);
        });

        // 4. User Roles Table (1 User has multiple Roles)
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->unique(['user_id', 'role_id']);
            $table->timestamps();
        });

        // 5. User Role Specific Permissions (User can customize permissions within each role)
        Schema::create('user_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->unique(['user_id', 'role_id', 'permission_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_role_permissions');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
