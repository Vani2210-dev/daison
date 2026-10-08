<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    /**
     * Bootstrap data for Proposals in SPA
     */
    public function getBootstrapData()
    {
        return Proposal::with(['steps.approver', 'requester'])->get()->map(function ($prop) {
            $chain = $prop->steps->map(function ($step) {
                return [
                    'stepIndex'    => (int) $step->step_index,
                    'roleName'     => $step->role_name,
                    'approverName' => $step->approver?->name,
                    'status'       => $step->status,
                    'signedAt'     => $step->signed_at ? $step->signed_at->format('Y-m-d H:i:s') : null,
                    'comment'      => $step->comment,
                ];
            });

            return [
                'id'            => (string) $prop->id,
                'code'          => $prop->code,
                'title'         => $prop->title,
                'requesterId'   => (string) $prop->requester_id,
                'requesterName' => $prop->requester?->name ?? null,
                'department'    => $prop->department,
                'type'          => $prop->type,
                'amount'        => (float) $prop->amount,
                'description'   => $prop->description ?? '',
                'createdAt'     => $prop->created_at ? $prop->created_at->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
                'status'        => $prop->status,
                'currentStep'   => (int) $prop->current_step,
                'totalSteps'    => (int) $prop->total_steps,
                'approvalChain' => $chain,
            ];
        });
    }

    /**
     * API: Get all proposals
     */
    public function getProposals()
    {
        return response()->json(['proposals' => $this->getBootstrapData()]);
    }
}
