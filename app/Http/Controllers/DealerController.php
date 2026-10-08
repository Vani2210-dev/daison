<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use App\Models\DealerCareLog;
use Illuminate\Http\Request;

class DealerController extends Controller
{
    /**
     * Bootstrap data for Dealers in SPA
     */
    public function getDealersBootstrapData()
    {
        return Dealer::with('salesRep')->get()->map(function ($d) {
            return [
                'id'              => (string) $d->id,
                'code'            => $d->code,
                'name'            => $d->name,
                'ownerName'       => $d->owner_name,
                'phone'           => $d->phone,
                'taxCode'         => $d->tax_code ?? '',
                'referrer'        => $d->referrer ?? '',
                'address'         => $d->address,
                'oldWard'         => $d->old_ward ?? '',
                'oldDistrict'     => $d->old_district ?? ($d->district ?? ''),
                'oldCity'         => $d->old_city ?? ($d->city ?? ''),
                'newWard'         => $d->new_ward ?? '',
                'newDistrict'     => $d->new_district ?? '',
                'newCity'         => $d->new_city ?? '',
                'district'        => $d->old_district ?? ($d->district ?? ''),
                'city'            => $d->old_city ?? ($d->city ?? ''),
                'tier'            => $d->tier,
                'creditLimit'     => (float) $d->credit_limit,
                'initialDebt'     => (float) ($d->initial_debt ?? 0),
                'currentDebt'     => (float) $d->current_debt,
                'paymentTermDays' => (int) $d->payment_term_days,
                'overdueDays'     => (int) $d->overdue_days,
                'salesRepId'      => (string) ($d->sales_rep_id ?? ''),
                'salesRepName'    => $d->salesRep?->name ?? null,
                'createdDate'     => $d->created_at ? $d->created_at->format('Y-m-d') : null,
                'status'          => $d->status,
                'lastVisitedDate' => $d->last_visited_at ? $d->last_visited_at->format('Y-m-d') : null,
                'avatarUrl'       => $d->avatar_url ?? '',
                'truckAccessible' => (bool) $d->truck_accessible,
            ];
        });
    }

    /**
     * Bootstrap data for Dealer Care Logs in SPA
     */
    public function getDealerCareLogsBootstrapData()
    {
        return DealerCareLog::with(['dealer', 'salesRep'])->get()->map(function ($log) {
            return [
                'id'                 => (string) $log->id,
                'dealerId'           => (string) $log->dealer_id,
                'dealerName'         => $log->dealer?->name ?? 'Đại lý',
                'dealerCode'         => $log->dealer?->code ?? 'DL-001',
                'salesRepId'         => (string) $log->sales_rep_id,
                'salesRepName'       => $log->salesRep?->name ?? 'Nhân viên kinh doanh',
                'visitedDate'        => $log->visited_date ? $log->visited_date->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
                'gpsLocation'        => [
                    'lat' => (float) $log->lat,
                    'lng' => (float) $log->lng,
                ],
                'distanceMeters'     => (int) $log->distance_meters,
                'isWithinRadius'     => (bool) $log->is_within_radius,
                'purpose'            => $log->purpose,
                'marketFeedback'     => $log->marketFeedback ?? '',
                'competitorInfo'     => $log->competitor_info ?? '',
                'nextOrderEstimate'  => $log->next_order_estimate ?? '',
                'satisfactionRating' => (int) $log->satisfaction_rating,
                'photoUrl'           => $log->photo_url ?? '',
                'notes'              => $log->notes ?? '',
                'kpiCredited'        => (bool) $log->kpi_credited,
                'createdAt'          => $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
            ];
        });
    }

    /**
     * API: Get all dealers
     */
    public function getDealers()
    {
        return response()->json(['dealers' => $this->getDealersBootstrapData()]);
    }

    /**
     * API: Save (Create or Update) Customer / Dealer
     */
    public function saveDealer(Request $request)
    {
        $raw = [];
        if ($request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }

        $id = $request->input('id') ?? ($raw['id'] ?? null);
        $code = $request->input('code') ?? ($raw['code'] ?? null);
        $name = $request->input('name') ?? ($raw['name'] ?? null);

        if (!$name) {
            return response()->json(['success' => false, 'message' => 'Tên đại lý là bắt buộc'], 422);
        }

        $initialDebt = (float) ($request->input('initialDebt') ?? ($raw['initialDebt'] ?? 0));

        $salesRepId = $request->input('salesRepId') ?? ($raw['salesRepId'] ?? null);

        $data = [
            'name'              => $name,
            'owner_name'        => $request->input('ownerName') ?? ($raw['ownerName'] ?? $name),
            'phone'             => $request->input('phone') ?? ($raw['phone'] ?? ''),
            'tax_code'          => $request->input('taxCode') ?? ($raw['taxCode'] ?? null),
            'referrer'          => $request->input('referrer') ?? ($raw['referrer'] ?? null),
            'sales_rep_id'      => ($salesRepId && is_numeric($salesRepId)) ? (int) $salesRepId : null,
            'address'           => $request->input('address') ?? ($raw['address'] ?? ''),
            'old_ward'          => $request->input('oldWard') ?? ($raw['oldWard'] ?? null),
            'old_district'      => $request->input('oldDistrict') ?? ($raw['oldDistrict'] ?? null),
            'old_city'          => $request->input('oldCity') ?? ($raw['oldCity'] ?? null),
            'new_ward'          => $request->input('newWard') ?? ($raw['newWard'] ?? null),
            'new_district'      => $request->input('newDistrict') ?? ($raw['newDistrict'] ?? null),
            'new_city'          => $request->input('newCity') ?? ($raw['newCity'] ?? null),
            'tier'              => $request->input('tier') ?? ($raw['tier'] ?? 'Đại lý Cấp 2'),
            'credit_limit'      => (float) ($request->input('creditLimit') ?? ($raw['creditLimit'] ?? 100000000)),
            'initial_debt'      => $initialDebt,
            'payment_term_days' => (int) ($request->input('paymentTermDays') ?? ($raw['paymentTermDays'] ?? 30)),
            'status'            => 'Hoạt động',
        ];

        $dealer = null;
        if ($id && is_numeric($id)) {
            $dealer = Dealer::find($id);
        }
        if (!$dealer && $code) {
            $dealer = Dealer::where('code', $code)->first();
        }

        if ($dealer) {
            $dealer->update($data);
        } else {
            $data['code'] = $code ?: ('DL-' . str_pad((string) (Dealer::count() + 1), 4, '0', STR_PAD_LEFT));
            if ($initialDebt > 0 && !isset($data['current_debt'])) {
                $data['current_debt'] = $initialDebt;
            }
            $dealer = Dealer::create($data);
        }

        $dealer->load('salesRep');

        return response()->json([
            'success' => true,
            'dealer'  => [
                'id'              => (string) $dealer->id,
                'code'            => $dealer->code,
                'name'            => $dealer->name,
                'ownerName'       => $dealer->owner_name,
                'phone'           => $dealer->phone,
                'taxCode'         => $dealer->tax_code ?? '',
                'referrer'        => $dealer->referrer ?? '',
                'salesRepId'      => (string) ($dealer->sales_rep_id ?? ''),
                'salesRepName'    => $dealer->salesRep?->name ?? ($raw['salesRepName'] ?? ''),
                'address'         => $dealer->address,
                'oldWard'         => $dealer->old_ward ?? '',
                'oldDistrict'     => $dealer->old_district ?? '',
                'oldCity'         => $dealer->old_city ?? '',
                'newWard'         => $dealer->new_ward ?? '',
                'newDistrict'     => $dealer->new_district ?? '',
                'newCity'         => $dealer->new_city ?? '',
                'district'        => $dealer->new_district ?? '',
                'city'            => $dealer->new_city ?? '',
                'tier'            => $dealer->tier,
                'creditLimit'     => (float) $dealer->credit_limit,
                'initialDebt'     => (float) ($dealer->initial_debt ?? 0),
                'currentDebt'     => (float) $dealer->current_debt,
                'paymentTermDays' => (int) $dealer->payment_term_days,
                'status'          => $dealer->status,
            ],
            'message' => 'Lưu thông tin khách hàng thành công',
        ]);
    }

    /**
     * API: Delete Customer / Dealer
     */
    public function deleteDealer($id)
    {
        if (is_numeric($id)) {
            Dealer::destroy($id);
        } else {
            Dealer::where('code', $id)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa khách hàng thành công',
        ]);
    }

    /**
     * API: Update / Recalculate Dealer Debt
     */
    public function updateDebt(Request $request)
    {
        $raw = [];
        if ($request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }
        $dealerId = $request->input('dealerId') ?? ($request->input('id') ?? ($raw['dealerId'] ?? ($raw['id'] ?? null)));
        $newDebt = $request->input('newDebt') ?? ($request->input('currentDebt') ?? ($raw['newDebt'] ?? ($raw['currentDebt'] ?? null)));

        $dealer = null;
        if ($dealerId && is_numeric($dealerId)) {
            $dealer = Dealer::find($dealerId);
        }
        if (!$dealer && $dealerId) {
            $dealer = Dealer::where('code', $dealerId)->first();
        }

        if ($dealer) {
            if ($newDebt !== null) {
                $dealer->current_debt = max(0, (float)$newDebt);
                $dealer->save();
            } else {
                $dealer->recalculateCurrentDebt();
            }

            return response()->json([
                'success' => true,
                'dealer'  => [
                    'id'          => (string) $dealer->id,
                    'code'        => $dealer->code,
                    'currentDebt' => (float) $dealer->current_debt,
                ],
                'message' => 'Cập nhật công nợ đại lý thành công'
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Không tìm thấy đại lý'], 404);
    }
}
