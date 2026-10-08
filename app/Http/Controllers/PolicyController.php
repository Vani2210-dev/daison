<?php

namespace App\Http\Controllers;

use App\Models\PolicyPreservation;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    /**
     * Bootstrap data for Policy Preservations
     */
    public function getBootstrapData()
    {
        return PolicyPreservation::latest('id')->get()->map(function ($p) {
            return [
                'id'                 => (string) $p->id,
                'code'               => $p->code ?? "BL-{$p->id}",
                'customerId'         => (string) $p->customer_id,
                'customerName'       => $p->customer_name,
                'productId'          => (string) $p->product_id,
                'productName'        => $p->product_name,
                'totalDepositAmount' => (float) $p->total_deposit_amount,
                'usedAmount'         => (float) $p->used_amount,
                'remainingAmount'    => (float) $p->remaining_amount,
                'startDate'          => $p->start_date ? $p->start_date->format('Y-m-d') : '',
                'endDate'            => $p->end_date ? $p->end_date->format('Y-m-d') : '',
                'status'             => $p->status,
                'createdByName'      => $p->created_by_name ?? null,
                'notes'              => $p->notes ?? '',
                'historyLogs'        => $p->history_logs ?? [],
            ];
        });
    }

    /**
     * API: Get policy preservations
     */
    public function getPolicies()
    {
        return response()->json(['policies' => $this->getBootstrapData()]);
    }

    /**
     * API: Save Policy Preservation
     */
    public function savePolicy(Request $request)
    {
        $id = $request->input('id');
        $data = [
            'code'                 => $request->input('code') ?: ('BL-' . date('ymd') . '-' . rand(100, 999)),
            'customer_id'          => $request->input('customerId'),
            'customer_name'        => $request->input('customerName'),
            'product_id'           => $request->input('productId'),
            'product_name'         => $request->input('productName'),
            'total_deposit_amount' => (float) $request->input('totalDepositAmount', 0),
            'used_amount'          => (float) $request->input('usedAmount', 0),
            'remaining_amount'     => (float) $request->input('remainingAmount', 0),
            'start_date'           => $request->input('startDate', date('Y-m-d')),
            'end_date'             => $request->input('endDate', date('Y-m-d', strtotime('+30 days'))),
            'status'               => $request->input('status', 'Còn hiệu lực'),
            'created_by_name'      => $request->input('createdByName', auth()->user()?->name ?? 'Admin KBTech'),
            'notes'                => $request->input('notes', ''),
        ];

        if ($id) {
            $pol = PolicyPreservation::find($id);
            if ($pol) {
                $pol->update($data);
                return response()->json(['success' => true, 'policy' => $pol]);
            }
        }

        $pol = PolicyPreservation::create($data);
        return response()->json(['success' => true, 'policy' => $pol]);
    }
}
