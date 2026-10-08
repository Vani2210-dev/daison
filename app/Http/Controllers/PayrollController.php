<?php

namespace App\Http\Controllers;

use App\Models\PayrollRecord;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    /**
     * Bootstrap data for Payroll in SPA
     */
    public function getBootstrapData()
    {
        return PayrollRecord::with('user')->get()->map(function ($p) {
            return [
                'id'              => (string) $p->id,
                'staffId'         => (string) $p->user_id,
                'staffName'       => $p->user?->name ?? null,
                'department'      => $p->user?->department ?? null,
                'role'            => $p->user?->roles->first()?->slug ?? null,
                'month'           => $p->month,
                'baseSalary'      => (float) $p->base_salary,
                'workDays'        => (float) $p->work_days,
                'kpiBonus'        => (float) $p->kpi_bonus,
                'salesCommission' => (float) $p->sales_commission,
                'allowances'      => (float) $p->allowances,
                'deductions'      => (float) $p->deductions,
                'netSalary'       => (float) $p->net_salary,
                'status'          => $p->status,
            ];
        });
    }

    /**
     * API: Get all payroll records
     */
    public function getPayroll()
    {
        return response()->json(['payroll' => $this->getBootstrapData()]);
    }
}
