<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'step_index',
        'role_name',
        'approver_id',
        'status',
        'signed_at',
        'comment',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
