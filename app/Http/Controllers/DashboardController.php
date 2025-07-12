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
        // if (!Auth::user()->hasPermission('dashboard-view') && !Auth::user()->hasRole(['super-admin', 'admin'])) {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }
        $user = Auth::user();
        $companyId = $user->company_id;
        
        $isSuperAdmin = $user->roles()->where('name', 'super-admin')->exists();
        
        if ($isSuperAdmin) {
            return $this->superAdminDashboard();
        }
        // Current month data
        $now = now();
        $currentMonth = now();
        $previousMonth = now()->subMonth();
        $currentMonthStart = $currentMonth->copy()->startOfMonth();
        $previousMonthStart = $previousMonth->copy()->startOfMonth();
        $previousMonthEnd = $previousMonth->copy()->endOfMonth();
        
        // Subjects data - current month
        $subjects = Subject::where('company_id', $companyId)->get();
        $totalSubjects = $subjects->count();
        $compliantSubjects = $subjects->filter(function($subject) {
            return $subject->compliance_status === true;
        })->count();
        
        // Subjects data - previous month
        $previousMonthSubjects = Subject::where('company_id', $companyId)
            ->whereDate('created_at', '<=', $previousMonthEnd)
            ->get();
        $previousMonthSubjectsCount = $previousMonthSubjects->count();
        
        // Documents data
        $currentDocumentsCount = Document::where('company_id', $companyId)->count();
        $previousMonthDocumentsCount = Document::where('company_id', $companyId)
            ->whereDate('created_at', '<=', $previousMonthEnd)
            ->count();
            
        // Subject Types data
        $currentSubjectTypesCount = SubjectType::where('company_id', $companyId)->count();
        $previousMonthSubjectTypesCount = SubjectType::where('company_id', $companyId)
            ->whereDate('created_at', '<=', $previousMonthEnd)
            ->count();
            
        // Document Types data
        $currentDocumentTypesCount = DocumentType::where('company_id', $companyId)->count();
        $previousMonthDocumentTypesCount = DocumentType::where('company_id', $companyId)
            ->whereDate('created_at', '<=', $previousMonthEnd)
            ->count();
            
        // Users data
        $currentUsersCount = User::where('company_id', $companyId)->count();
        $previousMonthUsersCount = User::where('company_id', $companyId)
            ->whereDate('created_at', '<=', $previousMonthEnd)
            ->count();
            
        // Expiring documents data
        $currentExpiringDocsCount = Document::where('company_id', $companyId)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $now)
            ->whereDate('expiry_date', '<=', $now->copy()->addDays(30))
            ->count();
        
        // Last month's expiring documents (documents that were expiring in the previous month's window)
        $previousMonthExpiringDocsCount = Document::where('company_id', $companyId)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $previousMonth)
            ->whereDate('expiry_date', '<=', $previousMonth->copy()->addDays(30))
            ->count();
        
        $calculateTrend = function($current, $previous) {
            if ($previous == 0) return $current > 0 ? 100 : 0;
            return round((($current - $previous) / $previous) * 100, 1);
        };
        
        // Compliance percentage current vs previous month
        $previousMonthCompliancePercentage = 0;
        if ($previousMonthSubjectsCount > 0) {
            $previousMonthCompliantSubjects = $previousMonthSubjects->filter(function($subject) {
                return $subject->compliance_status === true;
            })->count();
            $previousMonthCompliancePercentage = round(($previousMonthCompliantSubjects / $previousMonthSubjectsCount) * 100, 2);
        }
        $currentCompliancePercentage = $totalSubjects > 0 ? round(($compliantSubjects / $totalSubjects) * 100, 2) : 0;
        
        $stats = [
            'subjects' => $totalSubjects,
            'subjectsTrend' => $calculateTrend($totalSubjects, $previousMonthSubjectsCount),
            'compliantPercentage' => $currentCompliancePercentage,
            'complianceTrend' => $calculateTrend($currentCompliancePercentage, $previousMonthCompliancePercentage),
            'documents' => $currentDocumentsCount,
            'documentsTrend' => $calculateTrend($currentDocumentsCount, $previousMonthDocumentsCount),
            'subjectTypes' => $currentSubjectTypesCount,
            'subjectTypesTrend' => $calculateTrend($currentSubjectTypesCount, $previousMonthSubjectTypesCount),
            'documentTypes' => $currentDocumentTypesCount,
            'documentTypesTrend' => $calculateTrend($currentDocumentTypesCount, $previousMonthDocumentTypesCount),
            'users' => $currentUsersCount,
            'usersTrend' => $calculateTrend($currentUsersCount, $previousMonthUsersCount),
            'expiringDocuments' => $currentExpiringDocsCount,
            'expiringDocumentsTrend' => $calculateTrend($currentExpiringDocsCount, $previousMonthExpiringDocsCount),
        ];
        
        // Get recent subjects (last 5)
        $recentSubjects = Subject::where('company_id', $companyId)
            ->with(['subjectType', 'documents', 'documents.documentType'])
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
            ->with('user', 'company')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($log) {
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
            
        // Get expiring documents (next 30 days)
        $expiringDocuments = Document::where('company_id', $companyId)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', now())
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->with(['subject', 'documentType'])
            ->orderBy('expiry_date', 'asc')
            ->limit(5)
            ->get();
            
        // Get all documents with expiry dates for the calendar
        $calendarDocuments = Document::where('company_id', $companyId)
            ->whereNotNull('expiry_date')
            ->with(['subject', 'documentType'])
            ->get();
        
        // Get all documents for charts
        $allDocuments = Document::where('company_id', $companyId)
            ->with(['subject', 'documentType'])
            ->get();
            
        // Get all subjects for charts
        $allSubjects = Subject::where('company_id', $companyId)
            ->select('id', 'name', 'subject_type_id')
            ->with('subjectType:id,name')
            ->get();
            
        // Get document counts by status
        $documentsByStatus = [
            'draft' => Document::where('company_id', $companyId)->where('status', 0)->count(),
            'active' => Document::where('company_id', $companyId)->where('status', 1)->count(),
            'expired' => Document::where('company_id', $companyId)->where('status', 2)->count(),
            'archived' => Document::where('company_id', $companyId)->where('status', 3)->count(),
        ];
            
        // Get company info
        $company = $user->company;
        
        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentSubjects' => $recentSubjects,
            'recentDocuments' => $recentDocuments,
            'recentActivities' => $recentActivities,
            'expiringDocuments' => $expiringDocuments,
            'calendarDocuments' => $calendarDocuments,
            'allDocuments' => $allDocuments,
            'allSubjects' => $allSubjects,
            'documentsByStatus' => $documentsByStatus,
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
