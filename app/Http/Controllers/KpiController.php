<?php

namespace App\Http\Controllers;

use App\Models\KpiEvaluation;
use App\Models\DepartmentKpi;
use App\Models\User;
use Illuminate\Http\Request;

class KpiController extends Controller
{
    /**
     * Bootstrap data for Department KPIs
     */
    public function getDepartmentKpisBootstrapData()
    {
        return DepartmentKpi::latest('id')->get()->map(function ($kpi) {
            return [
                'id'                   => (string) $kpi->id,
                'department'           => $kpi->department,
                'departmentName'       => $kpi->department_name,
                'overallTargetPercent' => (float) $kpi->overall_target_percent,
                'metrics'              => $kpi->metrics ?? [],
                'month'                => $kpi->month,
            ];
        });
    }

    /**
     * Bootstrap data for Staff KPI Evaluations
     */
    public function getStaffEvaluationsBootstrapData()
    {
        return KpiEvaluation::with('user')->get()->map(function ($kpi) {
            return [
                'id'           => (string) $kpi->id,
                'staffId'      => (string) $kpi->user_id,
                'staffName'    => $kpi->user?->name ?? 'Nhân viên',
                'department'   => $kpi->user?->department ?? 'Sales',
                'role'         => $kpi->user?->roles->first()?->slug ?? 'Sales_Rep',
                'month'        => $kpi->month ? (str_contains($kpi->month, 'Tháng') ? $kpi->month : 'Tháng ' . date('m/Y', strtotime($kpi->month . '-01'))) : ('Tháng ' . date('m/Y')),
                'scorePercent' => (float) $kpi->score_percent,
                'grade'        => $kpi->grade ? (str_contains($kpi->grade, '(') ? $kpi->grade : "{$kpi->grade} (Đạt tiêu chuẩn)") : ($kpi->grade ?? '---'),
                'notes'        => $kpi->notes ?? '',
                'details'      => $kpi->metrics_breakdown ?? [],
            ];
        });
    }

    /**
     * API: Get KPI evaluations
     */
    public function getKpi(Request $request)
    {
        $month = $request->input('month', date('Y-m'));

        $evaluations = KpiEvaluation::with('user')
            ->where('month', $month)
            ->get()
            ->map(function ($kpi) {
                return [
                    'id'               => (string) $kpi->id,
                    'userId'           => (string) $kpi->user_id,
                    'userName'         => $kpi->user?->name ?? 'Nhân viên',
                    'department'       => $kpi->user?->department ?? 'Sales',
                    'month'            => $kpi->month,
                    'scorePercent'     => (float) $kpi->score_percent,
                    'grade'            => $kpi->grade,
                    'metricsBreakdown' => $kpi->metrics_breakdown ?? [],
                    'notes'            => $kpi->notes ?? '',
                ];
            });

        return response()->json(['kpi' => $evaluations]);
    }

    /**
     * API: Get Department KPIs
     */
    public function getDepartmentKpis()
    {
        return response()->json(['departmentKpis' => $this->getDepartmentKpisBootstrapData()]);
    }

    /**
     * API: Save KPI Evaluation
     */
    public function saveEvaluation(Request $request)
    {
        $id = $request->input('id');
        $userId = $request->input('userId');
        $month = $request->input('month', date('Y-m'));
        $score = (float) $request->input('scorePercent', 100);

        $grade = 'A';
        if ($score >= 95) {
            $grade = 'A+';
        } elseif ($score >= 85) {
            $grade = 'A';
        } elseif ($score >= 70) {
            $grade = 'B';
        } else {
            $grade = 'C';
        }

        $evaluation = $id ? KpiEvaluation::find($id) : KpiEvaluation::firstOrNew([
            'user_id' => $userId,
            'month'   => $month,
        ]);

        if (!$evaluation) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đánh giá KPI'], 404);
        }

        $evaluation->user_id = $userId;
        $evaluation->month = $month;
        $evaluation->score_percent = $score;
        $evaluation->grade = $grade;
        $evaluation->metrics_breakdown = $request->input('metricsBreakdown', []);
        $evaluation->notes = $request->input('notes', '');
        $evaluation->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật đánh giá KPI thành công!',
            'evaluation' => $evaluation,
        ]);
    }
}
