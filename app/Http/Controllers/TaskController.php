<?php

namespace App\Http\Controllers;

use App\Models\TaskAssignment;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Bootstrap data for Tasks in SPA
     */
    public function getBootstrapData()
    {
        return TaskAssignment::with(['assignedTo', 'createdBy'])->get()->map(function ($t) {
            return [
                'id'                   => (string) $t->id,
                'code'                 => $t->code,
                'title'                => $t->title,
                'description'          => $t->description ?? '',
                'category'             => $t->category,
                'assignedToId'         => (string) $t->assigned_to_id,
                'assignedToName'       => $t->assignedTo?->name ?? null,
                'assignedToRole'       => $t->assignedTo?->roles->first()?->slug ?? null,
                'assignedToDepartment' => $t->assignedTo?->department ?? null,
                'createdById'          => (string) $t->created_by_id,
                'createdByName'        => $t->createdBy?->name ?? null,
                'deadline'             => $t->deadline ? $t->deadline->format('Y-m-d H:i') : date('Y-m-d 18:00'),
                'priority'             => $t->priority,
                'status'               => $t->status,
                'progressPercent'      => (int) $t->progress_percent,
                'relatedOrderCode'     => $t->related_order_code,
                'checklist'            => $t->checklist ?? [],
                'reports'              => [],
                'createdAt'            => $t->created_at ? $t->created_at->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
            ];
        });
    }

    /**
     * API: Get all tasks
     */
    public function getTasks()
    {
        return response()->json(['tasks' => $this->getBootstrapData()]);
    }
}
