<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Subject;
use App\Models\User;
use App\Models\DocumentType;
use App\Models\SubjectType;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with statistics and recent activities.
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        
        $isSuperAdmin = $user->roles()->where('name', 'super-admin')->exists();
        
        if ($isSuperAdmin) {
            return $this->superAdminDashboard();
        }
        
        // Regular dashboard for company users
        $stats = [
            'subjects' => Subject::where('company_id', $companyId)->count(),
            'documents' => Document::where('company_id', $companyId)->count(),
            'subjectTypes' => SubjectType::where('company_id', $companyId)->count(),
            'documentTypes' => DocumentType::where('company_id', $companyId)->count(),
            'users' => User::where('company_id', $companyId)->count(),
            'expiringDocuments' => Document::where('company_id', $companyId)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', now())
                ->whereDate('expiry_date', '<=', now()->addDays(30))
                ->count(),
        ];
        
        // Get recent subjects (last 5)
        $recentSubjects = Subject::where('company_id', $companyId)
            ->with(['subjectType'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        // Get recent documents (last 5)
        $recentDocuments = Document::where('company_id', $companyId)
            ->with(['subject', 'documentType'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        // Get recent activity logs (last 10)
        $recentActivities = ActivityLog::where('company_id', $companyId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Get expiring documents (next 30 days)
        $expiringDocuments = Document::where('company_id', $companyId)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', now())
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->with(['subject', 'documentType'])
            ->orderBy('expiry_date', 'asc')
            ->limit(5)
            ->get();
            
        // Get company info
        $company = $user->company;
        
        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentSubjects' => $recentSubjects,
            'recentDocuments' => $recentDocuments,
            'recentActivities' => $recentActivities,
            'expiringDocuments' => $expiringDocuments,
            'company' => $company,
        ]);
    }
    
    /**
     * Display the super-admin dashboard with system-wide statistics and company management.
     */
    private function superAdminDashboard()
    {
        // Get all companies with user counts
        $companies = \App\Models\Company::withCount('users')->orderBy('name')->get();
        
        // Get total counts across all companies
        $stats = [
            'totalCompanies' => \App\Models\Company::count(),
            'totalUsers' => \App\Models\User::count(),
            'totalSubjects' => \App\Models\Subject::count(),
            'totalDocuments' => \App\Models\Document::count(),
            'totalSubjectTypes' => \App\Models\SubjectType::count(),
            'totalDocumentTypes' => \App\Models\DocumentType::count(),
        ];
        
        // Return specialized super-admin dashboard view
        return Inertia::render('SuperAdminDashboard', [
            'stats' => $stats,
            'companies' => $companies,
        ]);
    }
}
