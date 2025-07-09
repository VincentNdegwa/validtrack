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
        $query = ActivityLog::with(['user', 'company'])
            ->where('company_id', Auth::user()->company_id);
        
        // Handle search if provided
        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('action_type', 'like', "%{$searchTerm}%")
                  ->orWhere('target_type', 'like', "%{$searchTerm}%");
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
        $activityLogs = $query->paginate($perPage);

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
    public function show(ActivityLog $activityLog)
    {
        // Ensure user can only view logs from their company
        if ($activityLog->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        // Load relationships
        $activityLog->load(['user', 'company']);

        return Inertia::render('activity-logs/Show', [
            'activityLog' => $activityLog,
        ]);
    }

    /**
     * These methods aren't needed for activity logs since they're created automatically
     */
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
