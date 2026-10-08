<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'requester_id',
        'department',
        'type',
        'amount',
        'description',
        'status',
        'current_step',
        'total_steps',
        'attachment_url',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function creator()
    {
        return $this->requester();
    }

    public function steps()
    {
        return $this->hasMany(ProposalStep::class)->orderBy('step_index');
    }
}
