<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!Auth::user()->can('activity-log-view')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $query = ActivityLog::with(['user', 'company'])
            ->where('company_id', Auth::user()->company_id);
        
        // Handle search if provided
        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('action_type', 'like', "%{$searchTerm}%")
                  ->orWhere('target_type', 'like', "%{$searchTerm}%")
                  ->orWhereHas('user', function($userQuery) use ($searchTerm) {
                      $userQuery->where('name', 'like', "%{$searchTerm}%")
                                ->orWhere('email', 'like', "%{$searchTerm}%");
                  });
            });
        }
        
        // Handle sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['created_at', 'action_type', 'target_type'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }
        
        $query->orderBy($sortField, $sortDirection);
        
        // Paginate the results
        $perPage = $request->get('per_page', 10);
        $activityLogs = $query->paginate($perPage)->through(function ($log) {
            // Add computed attributes
            return [
                'id' => $log->id,
                'slug' => $log->slug,
                'user' => $log->user ? [
                    'name' => $log->user->name,
                    'email' => $log->user->email,
                ] : null,
                'company' => $log->company ? [
                    'name' => $log->company->name,
                ] : null,
                'action_type' => $log->action_type,
                'target_type' => $log->target_type,
                'target_id' => $log->target_id,
                'message' => $log->message,
                'friendly_target_name' => $log->friendly_target_name,
                'friendly_date' => $log->friendly_date,
                'created_at' => company_datetime($log->created_at->format('Y-m-d H:i:s'), $log->company),
                'updated_at' => $log->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return Inertia::render('activity-logs/Index', [
            'activityLogs' => $activityLogs,
            'filters' => [
                'search' => $request->get('search', ''),
                'sort' => $sortField,
                'direction' => $sortDirection,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        if (!Auth::user()->can('activity-log-view')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        
        $activityLog = is_numeric($id) ? ActivityLog::findOrFail($id) : ActivityLog::findBySlugOrFail($id);

        if ($activityLog->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $activityLog->load(['user', 'company']);
        
        $log = [
            'id' => $activityLog->id,
            'slug' => $activityLog->slug,
            'user' => $activityLog->user ? [
                'name' => $activityLog->user->name,
                'email' => $activityLog->user->email,
            ] : null,
            'company' => $activityLog->company ? [
                'name' => $activityLog->company->name,
            ] : null,
            'action_type' => $activityLog->action_type,
            'target_type' => $activityLog->target_type,
            'target_id' => $activityLog->target_id,
            'message' => $activityLog->message,
            'friendly_target_name' => $activityLog->friendly_target_name,
            'friendly_date' => $activityLog->friendly_date,
            'created_at' => company_datetime($activityLog->created_at->format('Y-m-d H:i:s'), $activityLog->company),
            'updated_at' => $activityLog->updated_at->format('Y-m-d H:i:s'),
            'payload' => $activityLog->payload,
            'changes' => $activityLog->getChanges(),
        ];

        return Inertia::render('activity-logs/Show', [
            'activityLog' => $log,
        ]);
    }

    public function create()
    {
        abort(404);
    }

    public function store(Request $request)
    {
        abort(404);
    }

    public function edit(ActivityLog $activityLog)
    {
        abort(404);
    }

    public function update(Request $request, ActivityLog $activityLog)
    {
        abort(404);
    }

    public function destroy(ActivityLog $activityLog)
    {
        abort(404);
    }
}
