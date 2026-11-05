<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Association;
use App\Models\User;
use App\Models\Cotisation;
use App\Models\Meeting;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    /**
     * Dashboard main entry.
     */
    public function index()
    {
        $user = Auth::user();
        $topAssociations = collect();
        if ($user->hasRole('superadmin')) {
            $data = $this->getSuperAdminDashboardData();
            return view('admin.dashboards.superadmin', $data);
        } elseif ($user->hasRole('admin')) {
            $data = $this->getAdminDashboardData();
            return view('admin.dashboards.admin', $data);
        } elseif ($user->hasRole('board')) {
            $data = $this->getBoardDashboardData();
            return view('admin.dashboards.board', $data);
        } elseif ($user->hasRole('supervisor')) {
            $data = $this->getSupervisorDashboardData();
            return view('admin.dashboards.supervisor', $data);
        } elseif ($user->hasRole('member')) {
            $data = $this->getMemberDashboardData();
            return view('admin.dashboards.member', $data);
        }

        abort(403);
    }


    /**
     * Get Super Admin Dashboard data.
     */
    private function getConsolidatedCashflowData($associationId = null)
    {
        $now = Carbon::now();

        $membershipsQuery = \App\Models\Membership::where('status', \App\Models\Membership::STATUS_PAID)->whereNotNull('paid_at');
        $cotisationsQuery = Cotisation::where('status', 1)->whereNotNull('paid_at');
        $contributionsQuery = \App\Models\Contribution::query();

        if ($associationId) {
            $membershipsQuery->where('association_id', $associationId);
            $cotisationsQuery->where('association_id', $associationId);
            $contributionsQuery->where('association_id', $associationId);
        }

        $items = collect();

        foreach ($membershipsQuery->get() as $m) {
            $items->push(['date' => Carbon::parse($m->paid_at), 'amount' => (float)$m->amount]);
        }
        foreach ($cotisationsQuery->get() as $c) {
            $items->push(['date' => Carbon::parse($c->paid_at), 'amount' => (float)$c->amount]);
        }
        foreach ($contributionsQuery->get() as $cb) {
            $dateField = $cb->received_at ?? $cb->created_at;
            $items->push(['date' => Carbon::parse($dateField), 'amount' => (float)$cb->amount]);
        }

        // Saídas (despesas)
        $expensesQuery = \App\Models\Expense::query();
        if ($associationId) {
            $expensesQuery->where('association_id', $associationId);
        }

        $outflowItems = collect();
        foreach ($expensesQuery->get() as $e) {
            $dateField = $e->spent_at ?? $e->created_at;
            $outflowItems->push(['date' => Carbon::parse($dateField), 'amount' => (float)$e->amount]);
        }

        $days30Map = [];
        for ($i = 30; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->format('Y-m-d');
            $days30Map[$d] = 0.0;
        }

        $months180Map = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i)->format('Y-m');
            $months180Map[$m] = 0.0;
        }

        $months365Map = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i)->format('Y-m');
            $months365Map[$m] = 0.0;
        }

        $aggregate = function (iterable $entries, array $daysMap, array $m180Map, array $m365Map): array {
            foreach ($entries as $entry) {
                $dt = $entry['date'];
                $amt = $entry['amount'];

                $dKey = $dt->format('Y-m-d');
                if (isset($daysMap[$dKey])) {
                    $daysMap[$dKey] += $amt;
                }

                $mKey = $dt->format('Y-m');
                if (isset($m180Map[$mKey])) {
                    $m180Map[$mKey] += $amt;
                }
                if (isset($m365Map[$mKey])) {
                    $m365Map[$mKey] += $amt;
                }
            }

            return [$daysMap, $m180Map, $m365Map];
        };

        [$days30MapIn, $months180MapIn, $months365MapIn] = $aggregate($items, $days30Map, $months180Map, $months365Map);
        [$days30MapOut, $months180MapOut, $months365MapOut] = $aggregate($outflowItems, $days30Map, $months180Map, $months365Map);

        return [
            '30' => [
                'categories' => array_keys($days30Map),
                'series' => [
                    ['name' => 'Entradas', 'data' => array_values($days30MapIn)],
                    ['name' => 'Saídas', 'data' => array_values($days30MapOut)],
                ],
            ],
            '180' => [
                'categories' => array_keys($months180Map),
                'series' => [
                    ['name' => 'Entradas', 'data' => array_values($months180MapIn)],
                    ['name' => 'Saídas', 'data' => array_values($months180MapOut)],
                ],
            ],
            '365' => [
                'categories' => array_keys($months365Map),
                'series' => [
                    ['name' => 'Entradas', 'data' => array_values($months365MapIn)],
                    ['name' => 'Saídas', 'data' => array_values($months365MapOut)],
                ],
            ],
        ];
    }

    protected function getSuperAdminDashboardData(): array
    {
        $now = Carbon::now();

        // Associations
        $totalAssociations = Association::count();
        $validatedAssociations = Association::where('is_validated', true)->count();
        $pendingAssociations = Association::where('is_validated', false)->count();

        $associations = [
            'total' => $totalAssociations,
            'validated' => $validatedAssociations,
            'pending' => $pendingAssociations,
            'progress' => $totalAssociations > 0
                ? round(($validatedAssociations / $totalAssociations) * 100, 1)
                : 0,
        ];

        // Users
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $inactiveUsers = User::where('is_active', false)->count();

        $users = [
            'total' => $totalUsers,
            'active' => $activeUsers,
            'inactive' => $inactiveUsers,
            'progress' => $totalUsers > 0
                ? round(($activeUsers / $totalUsers) * 100, 1)
                : 0,
        ];

        // Financials: Entradas / Saídas / Saldo
        $membershipsPaidMAD = \App\Models\Membership::where('status', \App\Models\Membership::STATUS_PAID)->sum('amount');
        $cotisationsPaidMAD = Cotisation::where('status', 1)->sum('amount');
        $contributionsMAD = \App\Models\Contribution::sum('amount');
        $expensesMAD = \App\Models\Expense::sum('amount');

        $totalInflow = $membershipsPaidMAD + $cotisationsPaidMAD + $contributionsMAD;
        $totalOutflow = $expensesMAD;
        $cashflowDelta = $totalInflow - $totalOutflow;

        $financials = [
            'memberships' => $membershipsPaidMAD,
            'cotisations' => $cotisationsPaidMAD,
            'contributions' => $contributionsMAD,
            'totalInflow' => $totalInflow,
            'totalOutflow' => $totalOutflow,
            'delta' => $cashflowDelta,
            'membershipsCount' => \App\Models\Membership::where('status', \App\Models\Membership::STATUS_PAID)->count(),
            'contributionsCount' => \App\Models\Contribution::count(),
            'expensesCount' => \App\Models\Expense::count(),
        ];

        // Meetings
        $totalMeetings = Meeting::count();
        $upcomingMeetings = Meeting::where('datetime', '>=', $now)->count();

        $meetings = [
            'held' => $totalMeetings,
            'upcoming' => $upcomingMeetings,
        ];

        // Events
        $activeEvents = Event::where('status', 1)->count();

        $events = [
            'active' => $activeEvents,
            'attendees' => 0,
        ];

        // Cashflow for charts (Consolidated)
        $cashflowData = $this->getConsolidatedCashflowData();

        // Top Associations (Receitas consolidadas)
        $topAssociations = Association::select('associations.id', 'associations.name', 'associations.is_validated')
            ->get()
            ->map(function ($assoc) {
                $membersCount = User::where('association_id', $assoc->id)->count();
                $membershipsSum = \App\Models\Membership::where('association_id', $assoc->id)
                    ->where('status', \App\Models\Membership::STATUS_PAID)
                    ->sum('amount');
                $cotisationsSum = Cotisation::where('association_id', $assoc->id)
                    ->where('status', 1)
                    ->sum('amount');
                $contributionsSum = \App\Models\Contribution::where('association_id', $assoc->id)
                    ->sum('amount');

                return [
                    'name' => $assoc->name,
                    'members' => $membersCount,
                    'revenue' => $membershipsSum + $cotisationsSum + $contributionsSum,
                    'status_label' => $assoc->is_validated ? 'Ativo' : 'Pendente',
                    'status_color' => $assoc->is_validated ? 'success' : 'warning',
                ];
            })
            ->sortByDesc('revenue')
            ->values()
            ->take(5)
            ->toArray();

        return compact(
            'users',
            'financials',
            'meetings',
            'events',
            'topAssociations',
            'cashflowData'
        );
    }

    /**
     * Get Admin Dashboard data.
     */
    protected function getAdminDashboardData(): array
    {
        $now = Carbon::now();
        $user = Auth::user();
        $associationId = $user->association_id;

        // Association info
        $association = Association::find($associationId);

        // Users
        $activeUsers = User::where('association_id', $associationId)
            ->where('is_active', true)
            ->count();

        $inactiveUsers = User::where('association_id', $associationId)
            ->where('is_active', false)
            ->count();

        $totalUsers = $activeUsers + $inactiveUsers;

        $users = [
            'total' => $totalUsers,
            'active' => $activeUsers,
            'inactive' => $inactiveUsers,
            'progress' => $totalUsers > 0
                ? round(($activeUsers / $totalUsers) * 100, 1)
                : 0,
        ];

        // Financials: Entradas / Saídas / Saldo
        $membershipsPaidMAD = \App\Models\Membership::where('association_id', $associationId)
            ->where('status', \App\Models\Membership::STATUS_PAID)
            ->sum('amount');
        $cotisationsPaidMAD = Cotisation::where('association_id', $associationId)
            ->where('status', 1)
            ->sum('amount');
        $contributionsMAD = \App\Models\Contribution::where('association_id', $associationId)
            ->sum('amount');
        $expensesMAD = \App\Models\Expense::where('association_id', $associationId)
            ->sum('amount');

        $totalInflow = $membershipsPaidMAD + $cotisationsPaidMAD + $contributionsMAD;
        $totalOutflow = $expensesMAD;
        $cashflowDelta = $totalInflow - $totalOutflow;

        $financials = [
            'memberships' => $membershipsPaidMAD,
            'cotisations' => $cotisationsPaidMAD,
            'contributions' => $contributionsMAD,
            'totalInflow' => $totalInflow,
            'totalOutflow' => $totalOutflow,
            'delta' => $cashflowDelta,
            'membershipsCount' => \App\Models\Membership::where('association_id', $associationId)
                ->where('status', \App\Models\Membership::STATUS_PAID)->count(),
            'contributionsCount' => \App\Models\Contribution::where('association_id', $associationId)->count(),
            'expensesCount' => \App\Models\Expense::where('association_id', $associationId)->count(),
        ];

        // Compatibilidade
        $cotisations = [
            'total' => $totalInflow,
        ];

        $cotisationsStatus = [
            'paid' => Cotisation::where('association_id', $associationId)
                ->where('status', Cotisation::STATUS_PAID)
                ->count(),
            'pending' => Cotisation::where('association_id', $associationId)
                ->where('status', Cotisation::STATUS_PENDING)
                ->count(),
            'overdue' => Cotisation::where('association_id', $associationId)
                ->where('status', Cotisation::STATUS_OVERDUE)
                ->count(),
            'rejected' => Cotisation::where('association_id', $associationId)
                ->where('status', Cotisation::STATUS_REJECTED)
                ->count(),
        ];

        // Meetings
        $totalMeetings = Meeting::where('association_id', $associationId)->count();
        $upcomingMeetings = Meeting::where('association_id', $associationId)
            ->where('datetime', '>=', $now)
            ->count();

        $meetings = [
            'held' => $totalMeetings,
            'upcoming' => $upcomingMeetings,
        ];

        // Events
        $activeEvents = Event::where('association_id', $associationId)
            ->where('status', 1)
            ->count();

        $events = [
            'active' => $activeEvents,
        ];

        // Cashflow for charts (Consolidated: Mensalidades + Aportes + Diárias)
        $cashflowData = $this->getConsolidatedCashflowData($associationId);

        // Recent memberships
        $recentMemberships = \App\Models\Membership::with('user')
            ->where('association_id', $associationId)
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Recent cotisations
        $recentCotisations = Cotisation::with('user')
            ->where('association_id', $associationId)
            ->orderByDesc('created_at')
            ->take(4)
            ->get();

        return compact(
            'association',
            'users',
            'financials',
            'meetings',
            'events',
            'cashflowData',
            'recentMemberships',
            'recentCotisations'
        );
    }

    protected function getBoardDashboardData(): array
    {
        $now = Carbon::now();
        $user = Auth::user();
        $associationId = $user->association_id;

        // Association info
        $association = Association::find($associationId);

        // Users
        $activeUsers = User::where('association_id', $associationId)
            ->where('is_active', true)
            ->count();

        $inactiveUsers = User::where('association_id', $associationId)
            ->where('is_active', false)
            ->count();

        $totalUsers = $activeUsers + $inactiveUsers;

        $users = [
            'total' => $totalUsers,
            'active' => $activeUsers,
            'inactive' => $inactiveUsers,
            'progress' => $totalUsers > 0
                ? round(($activeUsers / $totalUsers) * 100, 1)
                : 0,
        ];

        // Financials: Entradas / Saídas / Saldo
        $membershipsPaidMAD = \App\Models\Membership::where('association_id', $associationId)
            ->where('status', \App\Models\Membership::STATUS_PAID)
            ->sum('amount');
        $cotisationsPaidMAD = Cotisation::where('association_id', $associationId)
            ->where('status', 1)
            ->sum('amount');
        $contributionsMAD = \App\Models\Contribution::where('association_id', $associationId)
            ->sum('amount');
        $expensesMAD = \App\Models\Expense::where('association_id', $associationId)
            ->sum('amount');

        $totalInflow = $membershipsPaidMAD + $cotisationsPaidMAD + $contributionsMAD;
        $totalOutflow = $expensesMAD;
        $cashflowDelta = $totalInflow - $totalOutflow;

        $financials = [
            'memberships' => $membershipsPaidMAD,
            'cotisations' => $cotisationsPaidMAD,
            'contributions' => $contributionsMAD,
            'totalInflow' => $totalInflow,
            'totalOutflow' => $totalOutflow,
            'delta' => $cashflowDelta,
            'membershipsCount' => \App\Models\Membership::where('association_id', $associationId)
                ->where('status', \App\Models\Membership::STATUS_PAID)->count(),
            'contributionsCount' => \App\Models\Contribution::where('association_id', $associationId)->count(),
            'expensesCount' => \App\Models\Expense::where('association_id', $associationId)->count(),
        ];

        // Compatibilidade
        $cotisations = [
            'total' => $totalInflow,
        ];

        $cotisationsStatus = [
            'paid' => Cotisation::where('association_id', $associationId)
                ->where('status', Cotisation::STATUS_PAID)
                ->count(),
            'pending' => Cotisation::where('association_id', $associationId)
                ->where('status', Cotisation::STATUS_PENDING)
                ->count(),
            'overdue' => Cotisation::where('association_id', $associationId)
                ->where('status', Cotisation::STATUS_OVERDUE)
                ->count(),
            'rejected' => Cotisation::where('association_id', $associationId)
                ->where('status', Cotisation::STATUS_REJECTED)
                ->count(),
        ];

        // Meetings
        $totalMeetings = Meeting::where('association_id', $associationId)->count();
        $upcomingMeetings = Meeting::where('association_id', $associationId)
            ->where('datetime', '>=', $now)
            ->count();

        $meetings = [
            'held' => $totalMeetings,
            'upcoming' => $upcomingMeetings,
        ];

        // Events
        $activeEvents = Event::where('association_id', $associationId)
            ->where('status', 1)
            ->count();

        $events = [
            'active' => $activeEvents,
        ];

        // Cashflow chart (Consolidated: Mensalidades + Aportes + Diárias)
        $cashflowData = $this->getConsolidatedCashflowData($associationId);

        // Recent activities (static for now)
        $recentActivities = collect([
            (object)[
                'member_name'   => 'Sarah Board',
                'action'        => 'Approved cotisation',
                'details'       => 'for Member #1',
                'activity_time' => now()->subHours(1),
            ],
            (object)[
                'member_name'   => 'Board User',
                'action'        => 'Created event',
                'details'       => 'Summer Strategy Session',
                'activity_time' => now()->subDays(1),
            ],
            (object)[
                'member_name'   => 'Member X',
                'action'        => 'Participated in meeting',
                'details'       => 'Budget Planning',
                'activity_time' => now()->subDays(2),
            ],
        ]);

        return compact(
            'association',
            'users',
            'financials',
            'cotisations',
            'cotisationsStatus',
            'meetings',
            'events',
            'cashflowData',
            'recentActivities'
        );
    }

    /**
     * Get Supervisor Dashboard data.
     */
    protected function getSupervisorDashboardData(): array
    {
        $now = Carbon::now();
        $user = Auth::user();
        $associationId = $user->association_id;

        // Association info
        $association = Association::find($associationId);

        // Users
        $activeUsers = User::where('association_id', $associationId)
            ->where('is_active', true)
            ->count();

        $inactiveUsers = User::where('association_id', $associationId)
            ->where('is_active', false)
            ->count();

        $totalUsers = $activeUsers + $inactiveUsers;

        $users = [
            'total' => $totalUsers,
            'active' => $activeUsers,
            'inactive' => $inactiveUsers,
            'progress' => $totalUsers > 0
                ? round(($activeUsers / $totalUsers) * 100, 1)
                : 0,
        ];

        // Meetings
        $totalMeetings = Meeting::where('association_id', $associationId)->count();
        $upcomingMeetings = Meeting::where('association_id', $associationId)
            ->where('datetime', '>=', $now)
            ->count();

        $meetings = [
            'held' => $totalMeetings,
            'upcoming' => $upcomingMeetings,
        ];

        // Events
        $activeEvents = Event::where('association_id', $associationId)
            ->where('status', 1)
            ->count();

        $events = [
            'active' => $activeEvents,
        ];

        // Cotisations (supervisors typically only view, not manage amounts)
        $totalCotisations = Cotisation::where('association_id', $associationId)
            ->sum('amount');

        $cotisations = [
            'total' => $totalCotisations,
        ];

        $cotisationsStatus = [
            'paid' => Cotisation::where('association_id', $associationId)
                ->where('status', Cotisation::STATUS_PAID)
                ->count(),
            'pending' => Cotisation::where('association_id', $associationId)
                ->where('status', Cotisation::STATUS_PENDING)
                ->count(),
            'overdue' => Cotisation::where('association_id', $associationId)
                ->where('status', Cotisation::STATUS_OVERDUE)
                ->count(),
            'rejected' => Cotisation::where('association_id', $associationId)
                ->where('status', Cotisation::STATUS_REJECTED)
                ->count(),
        ];

        // Cashflow chart (Consolidated: Mensalidades + Aportes + Diárias)
        $cashflowData = $this->getConsolidatedCashflowData($associationId);

        // Recent activities (static examples)
        $recentActivities = collect([
            (object)[
                'member_name'   => 'Supervisor User',
                'action'        => 'Scheduled a meeting',
                'details'       => 'Board Review',
                'activity_time' => now()->subHours(2),
            ],
            (object)[
                'member_name'   => 'Ali Supervisor',
                'action'        => 'Updated event',
                'details'       => 'Community Workshop',
                'activity_time' => now()->subDays(1),
            ],
            (object)[
                'member_name'   => 'Hassan',
                'action'        => 'Reviewed member profile',
                'details'       => 'Member #12',
                'activity_time' => now()->subDays(2),
            ],
        ]);

        return compact(
            'association',
            'users',
            'cotisations',
            'cotisationsStatus',
            'meetings',
            'events',
            'cashflowData',
            'recentActivities'
        );
    }

    /**
     * Get Member Dashboard data.
     */
    protected function getMemberDashboardData(): array
    {
        $auth = auth()->user();

        // --- 1) Cotisation Summary ---
        $paidTotal = Cotisation::where('user_id', $auth->id)
            ->where('status', Cotisation::STATUS_PAID)
            ->sum('amount');

        $pendingTotal = Cotisation::where('user_id', $auth->id)
            ->where('status', Cotisation::STATUS_PENDING)
            ->sum('amount');

        $overdueTotal = Cotisation::where('user_id', $auth->id)
            ->where('status', Cotisation::STATUS_OVERDUE)
            ->sum('amount');

        // --- 2) Profile completeness (dummy calculation) ---
        // Compute from user's actual filled fields instead of statistics table
        $fields = [
            $auth->name,
            $auth->email,
            $auth->phone,
            $auth->address,
        ];

        $filled = collect($fields)->filter(fn($v) => !empty($v))->count();
        $total = count($fields);

        $profileCompletion = $total > 0
            ? round(($filled / $total) * 100, 1)
            : 0;

        // --- 3) Cotisation Chart (Last 6 months) ---
        $cashflow = Cotisation::select(
            DB::raw("DATE_FORMAT(paid_at, '%Y-%m') as month"),
            DB::raw("SUM(amount) as total")
        )
            ->where('user_id', $auth->id)
            ->where('status', Cotisation::STATUS_PAID)
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', now()->copy()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $cashflowLabels = $cashflow->pluck('month')->toArray();
        $cashflowValues = $cashflow->pluck('total')->toArray();

        // --- 4) My Cotisation History ---
        $myCotisationsList = Cotisation::where('user_id', $auth->id)
            ->select([
                DB::raw('year as cycle'),
                'amount',
                DB::raw('IFNULL(paid_at, "N/A") as due_date'),
                'status',
            ])
            ->orderByDesc('year')
            ->limit(10)
            ->get();

        // --- 5) Upcoming Meetings & Events ---
        $upcomingMeetingsEvents = collect([
            [
                'title' => 'Quarterly Review',
                'date' => now()->addDays(7)->format('M d, Y'),
                'location' => 'Main Hall',
                'time_diff' => 'in 7 days',
                'link' => route('admin.meetings.index'),
            ],
            [
                'title' => 'Annual Charity Gala',
                'date' => now()->addDays(15)->format('M d, Y'),
                'location' => 'Conference Center',
                'time_diff' => 'in 15 days',
                'link' => route('membre.events.index'),
            ],
        ]);

        // --- 6) Recent Activities (dummy) ---
        $recentActivities = collect([
            (object)[
                'date' => now()->subDays(2)->format('Y-m-d'),
                'type' => 'Paid Cotisation',
                'details' => 'Annual Dues 2024',
            ],
            (object)[
                'date' => now()->subDays(5)->format('Y-m-d'),
                'type' => 'Joined Event',
                'details' => 'Community Workshop',
            ],
        ]);

        return [
            'user' => $auth,
            'profileCompletion' => $profileCompletion,
            'paidTotal' => $paidTotal,
            'pendingTotal' => $pendingTotal,
            'overdueTotal' => $overdueTotal,
            'cashflowLabels' => $cashflowLabels,
            'cashflowValues' => $cashflowValues,
            'myCotisationsList' => $myCotisationsList,
            'upcomingMeetingsEvents' => $upcomingMeetingsEvents,
            'recentActivities' => $recentActivities,
        ];
    }
}
