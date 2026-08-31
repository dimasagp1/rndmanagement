<?php

namespace App\Http\Controllers;

use App\Models\Formula;
use App\Models\FormulaApprovalForm;
use App\Models\NpdProposal;
use App\Models\PackagingDevelopment;
use App\Models\PreformulationStudy;
use App\Models\Prf;
use App\Models\Qbd;
use App\Models\SampleEvaluation;
use App\Models\StabilityTest;
use App\Models\TechnologyTransfer;
use App\Models\NieApproval;
use App\Models\TrialPm;
use App\Models\TrialRm;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class TimelineController extends Controller
{
    public const MODULE_META = [
        'prf'                     => ['label' => 'PRF',              'group' => 'Concept',   'color' => 'blue',    'route' => 'prfs.show'],
        'npd-proposal'            => ['label' => 'NPD Proposal',     'group' => 'Concept',   'color' => 'blue',    'route' => 'npd-proposals.show'],
        'qbd'                     => ['label' => 'QbD',              'group' => 'Development','color' => 'indigo',  'route' => 'qbds.show'],
        'formula'                 => ['label' => 'Formula',          'group' => 'Formulation','color' => 'emerald', 'route' => 'formulas.show'],
        'trial-rm'                => ['label' => 'Trial RM',         'group' => 'Formulation','color' => 'emerald', 'route' => 'trial-rms.show'],
        'trial-pm'                => ['label' => 'Trial PM',         'group' => 'Formulation','color' => 'emerald', 'route' => 'trial-pms.show'],
        'preformulation-study'    => ['label' => 'Preformulasi',     'group' => 'Development','color' => 'indigo',  'route' => 'preformulation-studies.show'],
        'sample-evaluation'       => ['label' => 'Sample Evaluation','group' => 'Evaluation','color' => 'violet',  'route' => 'sample-evaluations.show'],
        'formula-approval'        => ['label' => 'Formula Approval', 'group' => 'Approval',  'color' => 'amber',   'route' => 'formula-approvals.show'],
        'packaging-development'   => ['label' => 'Packaging Dev',    'group' => 'Packaging', 'color' => 'orange',  'route' => 'packaging-developments.show'],
        'stability-test'          => ['label' => 'Stability Test',   'group' => 'Stability', 'color' => 'teal',    'route' => 'stability-tests.show'],
        'technology-transfer'     => ['label' => 'Tech Transfer',    'group' => 'Transfer',  'color' => 'cyan',    'route' => 'technology-transfers.show'],
        'nie-approval'            => ['label' => 'NIE Approved',     'group' => 'Regulatory','color' => 'rose',    'route' => 'nie-approvals.show'],
    ];

    public function index(Request $request)
    {
        $user = auth()->user();
        $isStaff = $user->hasRole(['Staff R&D', 'Staff Packdev']);
        $isManager = $user->hasRole('Operational Manager');
        $isGM = $user->hasRole('General Manager');
        $staffScope = $isStaff ? $user->id : null;

        // ── 1. Collect items from all 13 modules ──────────────
        $items = $this->collectAllItems($staffScope);

        // ── 2. Filtering ──────────────────────────────────────
        $moduleFilter = $request->get('module');
        $statusFilter = $request->get('status');
        $search = $request->get('search');

        if ($moduleFilter && isset(self::MODULE_META[$moduleFilter])) {
            $items = $items->where('module_key', $moduleFilter);
        }

        if ($search) {
            $lower = strtolower($search);
            $items = $items->filter(fn ($item) =>
                str_contains(strtolower($item['name']), $lower) ||
                str_contains(strtolower($item['code'] ?? ''), $lower)
            );
        }

        if ($statusFilter) {
            $items = $items->filter(fn ($item) => strtolower($item['status'] ?? '') === strtolower($statusFilter));
        }

        $items = $items->values();
        $totalItems = $items->count();

        // ── 3. Summary stats ──────────────────────────────────
        $approved = $items->whereIn('status', ['Approved', 'Completed', 'Completed by GM', 'Lulus'])->count();
        $pending = $items->filter(fn ($i) => str_starts_with(strtolower($i['status'] ?? ''), 'pending'))->count();
        $rejected = $items->where('status', 'Rejected')->count();
        $draft = $items->where('status', 'Draft')->count();
        $pipelinePercent = $totalItems > 0 ? round($approved / $totalItems * 100) : 0;

        // ── 4. Module stat cards ──────────────────────────────
        $moduleStats = $this->getModuleStats($staffScope);

        // ── 5. Pending action items (role-based) ──────────────
        $pendingItems = $this->getPendingItems($user, $isStaff, $isManager, $isGM, $staffScope);

        // ── 6. Activity feed ──────────────────────────────────
        $activities = $this->getActivityFeed($user, $isStaff);

        // ── 7. Workload (manager/GM only) ─────────────────────
        $workload = collect();
        if (!$isStaff) {
            $workload = $this->getWorkload();
        }

        // ── 8. Owner options for filter ───────────────────────
        $ownerOptions = User::whereHas('formulas')->orderBy('name')->get(['id', 'name']);

        return view('timeline.index', compact(
            'items', 'totalItems', 'approved', 'pending', 'rejected', 'draft',
            'pipelinePercent', 'moduleStats', 'pendingItems', 'activities',
            'workload', 'ownerOptions', 'isStaff', 'isManager', 'isGM'
        ));
    }

    // ── Collect items from all 13 modules ──────────────────────
    private function collectAllItems(?int $userId): \Illuminate\Support\Collection
    {
        $items = collect();
        $userField = 'created_by';

        // Helper: map a model to uniform array
        $mapItem = function ($model, $moduleKey, $nameField, $statusField = null, $codeField = 'code', $routeParam = null) use ($userField) {
            $meta = self::MODULE_META[$moduleKey];
            return [
                'module_key' => $moduleKey,
                'module'     => $meta['label'],
                'group'      => $meta['group'],
                'color'      => $meta['color'],
                'name'       => $model->{$nameField} ?? $model->title ?? '—',
                'code'       => $model->{$codeField} ?? null,
                'status'     => $statusField ? ($model->{$statusField} ?? null) : null,
                'owner'      => $model->creator?->name ?? '—',
                'owner_id'   => $model->{$userField},
                'updated_at' => $model->updated_at,
                'route'      => route($meta['route'], $routeParam ?? $model),
            ];
        };

        // 1. PRF
        $q = Prf::with('creator')->latest();
        if ($userId) $q->where($userField, $userId);
        foreach ($q->get() as $m) {
            $items->push($mapItem($m, 'prf', 'product_name', null, 'code'));
        }

        // 2. NPD Proposal
        $q = NpdProposal::with('creator')->latest();
        if ($userId) $q->where($userField, $userId);
        foreach ($q->get() as $m) {
            $items->push($mapItem($m, 'npd-proposal', 'product_name', 'project_status', 'code'));
        }

        // 3. QbD
        $q = Qbd::with('creator')->latest();
        if ($userId) $q->where($userField, $userId);
        foreach ($q->get() as $m) {
            $items->push($mapItem($m, 'qbd', 'product_name', null, null));
        }

        // 4. Formula
        $q = Formula::with('creator')->latest();
        if ($userId) $q->where($userField, $userId);
        foreach ($q->get() as $m) {
            $items->push($mapItem($m, 'formula', 'name', 'approval_status', 'code'));
        }

        // 5. Trial RM
        $q = TrialRm::with('creator')->latest();
        if ($userId) $q->where($userField, $userId);
        foreach ($q->get() as $m) {
            $items->push($mapItem($m, 'trial-rm', 'sample_identity', 'approval_status', 'code'));
        }

        // 6. Trial PM
        $q = TrialPm::with('creator')->latest();
        if ($userId) $q->where($userField, $userId);
        foreach ($q->get() as $m) {
            $items->push($mapItem($m, 'trial-pm', 'packaging_material', 'approval_status', 'code'));
        }

        // 7. Preformulation Study
        $q = PreformulationStudy::with('creator')->latest();
        if ($userId) $q->where($userField, $userId);
        foreach ($q->get() as $m) {
            $items->push($mapItem($m, 'preformulation-study', 'product_name', 'approval_status', 'code'));
        }

        // 8. Sample Evaluation
        $q = SampleEvaluation::with('creator')->latest();
        if ($userId) $q->where($userField, $userId);
        foreach ($q->get() as $m) {
            $items->push($mapItem($m, 'sample-evaluation', 'product_name', 'status', 'sample_id'));
        }

        // 9. Formula Approval (both sources)
        $q = FormulaApprovalForm::with('creator')->latest();
        if ($userId) $q->where($userField, $userId);
        foreach ($q->get() as $m) {
            $items->push($mapItem($m, 'formula-approval', 'product_name', 'approval_status', null));
        }

        // 10. Packaging Development
        $q = PackagingDevelopment::with('creator')->latest();
        if ($userId) $q->where($userField, $userId);
        foreach ($q->get() as $m) {
            $items->push($mapItem($m, 'packaging-development', 'product_name', 'approval_status', null));
        }

        // 11. Stability Test
        $q = StabilityTest::with('creator')->latest();
        if ($userId) $q->where($userField, $userId);
        foreach ($q->get() as $m) {
            $items->push($mapItem($m, 'stability-test', 'title', null, null));
        }

        // 12. Technology Transfer
        $q = TechnologyTransfer::with('creator')->latest();
        if ($userId) $q->where($userField, $userId);
        foreach ($q->get() as $m) {
            $items->push($mapItem($m, 'technology-transfer', 'title', null, null));
        }

        // 13. NIE Approval
        $q = NieApproval::with('creator')->latest();
        if ($userId) $q->where($userField, $userId);
        foreach ($q->get() as $m) {
            $items->push($mapItem($m, 'nie-approval', 'product_name', null, null));
        }

        return $items->sortByDesc('updated_at')->values();
    }

    // ── Module stat cards ──────────────────────────────────────
    private function getModuleStats(?int $userId): array
    {
        $scope = fn ($q) => $userId ? $q->where('created_by', $userId) : $q;

        return [
            'prf'               => $scope(Prf::query())->count(),
            'npd_proposal'      => $scope(NpdProposal::query())->count(),
            'formula_approved'  => $scope(Formula::query()->whereIn('approval_status', ['Approved', 'Completed']))->count(),
            'trial_rm'          => $scope(TrialRm::query())->count(),
            'trial_pm'          => $scope(TrialPm::query())->count(),
            'sample_evaluation' => $scope(SampleEvaluation::query())->count(),
        ];
    }

    // ── Pending action items (role-based) ──────────────────────
    private function getPendingItems($user, bool $isStaff, bool $isManager, bool $isGM, ?int $userId): \Illuminate\Support\Collection
    {
        $pending = collect();

        if ($isStaff) {
            // Staff: items they created that need submit/reformulate
            $draftFormulas = Formula::where('created_by', $userId)
                ->whereIn('approval_status', ['Draft', 'Rejected'])->latest()->get();
            foreach ($draftFormulas as $f) {
                $pending->push([
                    'module' => 'Formula', 'name' => $f->name, 'code' => $f->code,
                    'status' => $f->approval_status, 'action' => $f->approval_status === 'Draft' ? 'Submit' : 'Reformulasi',
                    'route' => route('formulas.show', $f),
                ]);
            }

            $draftTrials = TrialRm::where('created_by', $userId)
                ->whereIn('approval_status', ['Draft', 'Rejected'])->latest()->get();
            foreach ($draftTrials as $t) {
                $pending->push([
                    'module' => 'Trial RM', 'name' => $t->sample_identity, 'code' => $t->code,
                    'status' => $t->approval_status, 'action' => $t->approval_status === 'Draft' ? 'Submit' : 'Reformulasi',
                    'route' => route('trial-rms.show', $t),
                ]);
            }

            $draftPreforms = PreformulationStudy::where('created_by', $userId)
                ->whereIn('approval_status', ['Draft', 'Rejected'])->latest()->get();
            foreach ($draftPreforms as $p) {
                $pending->push([
                    'module' => 'Preformulasi', 'name' => $p->product_name, 'code' => $p->code,
                    'status' => $p->approval_status, 'action' => $p->approval_status === 'Draft' ? 'Submit' : 'Reformulasi',
                    'route' => route('preformulation-studies.show', $p),
                ]);
            }

            // Staff: Trial PM needing department approval
            $pendingPm = TrialPm::where('approval_status', 'Pending Review')->latest()->get();
            foreach ($pendingPm as $tp) {
                $pending->push([
                    'module' => 'Trial PM', 'name' => $tp->packaging_material, 'code' => $tp->code,
                    'status' => $tp->approval_status, 'action' => 'Dept Approve',
                    'route' => route('trial-pms.show', $tp),
                ]);
            }
        }

        if ($isManager || $isGM) {
            // Manager: Pending Tahap 1
            $pendingT1Formula = Formula::where('approval_status', 'Pending Tahap 1')->latest()->get();
            foreach ($pendingT1Formula as $f) {
                $pending->push([
                    'module' => 'Formula', 'name' => $f->name, 'code' => $f->code,
                    'status' => $f->approval_status, 'action' => 'Approve T1',
                    'route' => route('formulas.show', $f),
                ]);
            }

            $pendingT1Trial = TrialRm::where('approval_status', 'Pending Tahap 1')->latest()->get();
            foreach ($pendingT1Trial as $t) {
                $pending->push([
                    'module' => 'Trial RM', 'name' => $t->sample_identity, 'code' => $t->code,
                    'status' => $t->approval_status, 'action' => 'Approve T1',
                    'route' => route('trial-rms.show', $t),
                ]);
            }

            $pendingT1Preform = PreformulationStudy::where('approval_status', 'Pending Tahap 1')->latest()->get();
            foreach ($pendingT1Preform as $p) {
                $pending->push([
                    'module' => 'Preformulasi', 'name' => $p->product_name, 'code' => $p->code,
                    'status' => $p->approval_status, 'action' => 'Approve T1',
                    'route' => route('preformulation-studies.show', $p),
                ]);
            }
        }

        if ($isGM) {
            // GM: Pending Tahap 2
            $pendingT2Formula = Formula::where('approval_status', 'Pending Tahap 2')->latest()->get();
            foreach ($pendingT2Formula as $f) {
                $pending->push([
                    'module' => 'Formula', 'name' => $f->name, 'code' => $f->code,
                    'status' => $f->approval_status, 'action' => 'Approve T2',
                    'route' => route('formulas.show', $f),
                ]);
            }

            $pendingT2Trial = TrialRm::where('approval_status', 'Pending Tahap 2')->latest()->get();
            foreach ($pendingT2Trial as $t) {
                $pending->push([
                    'module' => 'Trial RM', 'name' => $t->sample_identity, 'code' => $t->code,
                    'status' => $t->approval_status, 'action' => 'Approve T2',
                    'route' => route('trial-rms.show', $t),
                ]);
            }

            $pendingT2Preform = PreformulationStudy::where('approval_status', 'Pending Tahap 2')->latest()->get();
            foreach ($pendingT2Preform as $p) {
                $pending->push([
                    'module' => 'Preformulasi', 'name' => $p->product_name, 'code' => $p->code,
                    'status' => $p->approval_status, 'action' => 'Approve T2',
                    'route' => route('preformulation-studies.show', $p),
                ]);
            }

            // GM: Pending approval forms
            $pendingApproval = FormulaApprovalForm::where('approval_status', 'Pending')->latest()->get();
            foreach ($pendingApproval as $a) {
                $pending->push([
                    'module' => 'Formula Approval', 'name' => $a->product_name, 'code' => $a->code,
                    'status' => $a->approval_status, 'action' => 'Approve',
                    'route' => route('formula-approvals.show', $a),
                ]);
            }

            // GM: Pending packaging
            $pendingPkg = PackagingDevelopment::where('approval_status', 'Pending GM')->latest()->get();
            foreach ($pendingPkg as $p) {
                $pending->push([
                    'module' => 'Packaging Dev', 'name' => $p->product_name, 'code' => null,
                    'status' => $p->approval_status, 'action' => 'Approve',
                    'route' => route('packaging-developments.show', $p),
                ]);
            }
        }

        return $pending->sortByDesc('status')->values();
    }

    // ── Activity feed ──────────────────────────────────────────
    private function getActivityFeed($user, bool $isStaff): \Illuminate\Support\Collection
    {
        try {
            $q = Activity::with('causer')->latest();
            if ($isStaff) {
                $q->where('causer_id', $user->id);
            }
            return $q->limit(15)->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    // ── Workload by owner ──────────────────────────────────────
    private function getWorkload(): \Illuminate\Support\Collection
    {
        $models = [
            Formula::class, TrialRm::class, TrialPm::class,
            Prf::class, NpdProposal::class, PreformulationStudy::class,
            SampleEvaluation::class, FormulaApprovalForm::class,
            PackagingDevelopment::class,
        ];

        $counts = [];
        foreach ($models as $modelClass) {
            $rows = (new $modelClass)->newQuery()
                ->selectRaw('created_by, COUNT(*) as total')
                ->groupBy('created_by')
                ->get();
            foreach ($rows as $row) {
                $uid = $row->created_by;
                $counts[$uid] = ($counts[$uid] ?? 0) + $row->total;
            }
        }

        arsort($counts);

        return collect($counts)->take(10)->map(function ($total, $uid) {
            $user = User::find($uid);
            return [
                'name'  => $user?->name ?? 'Unknown',
                'initials' => $this->initials($user?->name ?? '?'),
                'total' => $total,
            ];
        })->values();
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            if ($part !== '') {
                $initials .= strtoupper(mb_substr($part, 0, 1));
            }
        }
        return $initials ?: '?';
    }
}
