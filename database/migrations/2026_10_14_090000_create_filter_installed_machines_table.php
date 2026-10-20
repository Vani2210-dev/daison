<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('filter_installed_machines', function (Blueprint $table) {
            $table->id();
            $table->string('machine_code', 50)->unique();
            $table->string('qr_code', 50)->unique();
            $table->string('qr_token', 64)->unique();
            $table->unsignedBigInteger('installation_id')->nullable();
            
            // unactivated (Mã trống chưa gán) | pending_activation (KTV gửi yêu cầu kích hoạt) | activated (Đã kích hoạt BH) | expired (Hết hạn)
            $table->string('status', 30)->default('unactivated');
            
            // Thông tin khách hàng & địa điểm lắp
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 30)->nullable();
            $table->text('customer_address')->nullable();
            $table->string('water_source_type', 100)->default('Nước máy thành phố');
            
            // Thông tin model máy lọc
            $table->unsignedBigInteger('machine_model_id')->nullable();
            $table->string('machine_model')->nullable();
            $table->string('machine_serial', 100)->nullable();
            
            // Kỹ thuật viên & Lắp đặt
            $table->unsignedBigInteger('technician_id')->nullable();
            $table->string('technician_name')->nullable();
            $table->dateTime('installed_at')->nullable();
            $table->dateTime('activated_at')->nullable();
            $table->unsignedBigInteger('activated_by')->nullable();
            $table->string('activated_by_name')->nullable();
            
            // Thời hạn bảo hành & Thay lõi
            $table->integer('warranty_months')->default(24);
            $table->date('warranty_until')->nullable();
            $table->string('warranty_card_code', 50)->nullable();
            $table->integer('filter_replacement_months')->default(6);
            $table->date('next_maintenance_date')->nullable();
            $table->text('warranty_notes')->nullable();
            
            // Đo đạc kỹ thuật khi lắp đặt
            $table->integer('tds_in')->nullable();
            $table->integer('tds_out')->nullable();
            $table->string('pressure', 100)->nullable();
            
            // Ghi chú
            $table->text('activation_notes')->nullable();
            $table->text('admin_notes')->nullable();
            
            // Tracking lượt quét QR
            $table->integer('view_count')->default(0);
            $table->dateTime('last_scanned_at')->nullable();
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('customer_phone');
            $table->index('technician_id');
        });

        // Di chuyển dữ liệu máy đã lắp hiện có từ bảng filter_installations sang bảng mới
        $existing = DB::table('filter_installations')->get();
        foreach ($existing as $idx => $inst) {
            $qrToken = Str::lower(Str::random(16));
            $seq = sprintf('%04d', $idx + 1);
            $mCode = 'ML-' . date('Ym') . '-' . $seq;
            $qrCode = 'QR-' . date('Ym') . '-' . $seq;
            
            $status = ($inst->status === 'completed' || $inst->installed_at) ? 'activated' : 'unactivated';

            DB::table('filter_installed_machines')->insert([
                'machine_code'              => $mCode,
                'qr_code'                   => $qrCode,
                'qr_token'                  => $qrToken,
                'installation_id'           => $inst->id,
                'status'                    => $status,
                'customer_name'             => $inst->customer_name,
                'customer_phone'            => $inst->customer_phone,
                'customer_address'          => $inst->customer_address,
                'water_source_type'         => $inst->water_source_type ?? 'Nước máy thành phố',
                'machine_model_id'          => $inst->machine_model_id,
                'machine_model'             => $inst->machine_model,
                'machine_serial'            => $inst->machine_serial,
                'technician_id'             => $inst->technician_id,
                'technician_name'           => $inst->technician_name,
                'installed_at'              => $inst->installed_at ?? $inst->completed_date ?? now(),
                'activated_at'              => ($status === 'activated') ? now() : null,
                'activated_by_name'         => 'Hệ thống tự động',
                'warranty_months'           => $inst->warranty_months ?? 24,
                'warranty_until'            => $inst->warranty_until ?? date('Y-m-d', strtotime('+24 months')),
                'warranty_card_code'        => $inst->warranty_card_code ?? $inst->code,
                'filter_replacement_months' => $inst->filter_replacement_months ?? 6,
                'next_maintenance_date'     => $inst->next_maintenance_date ?? date('Y-m-d', strtotime('+6 months')),
                'warranty_notes'            => $inst->warranty_notes,
                'tds_in'                    => $inst->tds_in,
                'tds_out'                   => $inst->tds_out,
                'pressure'                  => $inst->pressure,
                'created_by'                => $inst->created_by,
                'created_at'                => $inst->created_at ?? now(),
                'updated_at'                => $inst->updated_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filter_installed_machines');
    }
};
