<?php

namespace App\Http\Controllers;

use App\Models\Formula;
use App\Models\FormulaApprovalForm;
use App\Models\NpdProposal;
use App\Models\PreformulationStudy;
use App\Models\Prf;
use App\Models\Product;
use App\Models\Qbd;
use App\Models\SampleEvaluation;
use App\Models\StabilityTest;
use App\Models\TechnologyTransfer;
use App\Models\NieApproval;
use App\Models\TrialPm;
use App\Models\TrialRm;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        'stability-test'          => ['label' => 'Stability Test',   'group' => 'Stability', 'color' => 'teal',    'route' => 'stability-tests.show'],
        'technology-transfer'     => ['label' => 'Tech Transfer',    'group' => 'Transfer',  'color' => 'cyan',    'route' => 'technology-transfers.show'],
        'nie-approval'            => ['label' => 'NIE Approved',     'group' => 'Regulatory','color' => 'rose',    'route' => 'nie-approvals.show'],
    ];

    // Map module_key => [model_class, table, status_field, name_field, code_field]
    private const MODULE_MAP = [
        'prf'                  => [Prf::class,                  'prfs',                    null,               'product_name',     'code'],
        'npd-proposal'         => [NpdProposal::class,         'npd_proposals',           'project_status',   'product_name',     'code'],
        'qbd'                  => [Qbd::class,                  'qbds',                    null,               'product_name',     null],
        'formula'              => [Formula::class,              'formulas',                'approval_status',  'name',             'code'],
        'trial-rm'             => [TrialRm::class,             'trial_rms',               'approval_status',  'sample_identity',  'code'],
        'trial-pm'             => [TrialPm::class,             'trial_pms',               'approval_status',  'packaging_material','code'],
        'preformulation-study' => [PreformulationStudy::class, 'preformulation_studies',  'approval_status',  'product_name',     'code'],
        'sample-evaluation'    => [SampleEvaluation::class,    'sample_evaluations',      'status',           'product_name',     'sample_id'],
        'formula-approval'     => [FormulaApprovalForm::class,  'formula_approval_forms',  'approval_status',  'product_name',     null],
        'stability-test'       => [StabilityTest::class,       'stability_tests',         null,               'title',            null],
        'technology-transfer'  => [TechnologyTransfer::class,  'technology_transfers',    null,               'title',            null],
        'nie-approval'         => [NieApproval::class,         'nie_approvals',           null,               'product_name',     null],
    ];

    public function index(Request $request)
    {
        $user = auth()->user();
        $isStaff = $user->hasRole(['Staff R&D', 'Staff Packdev']);
        $isManager = $user->hasRole('Operational Manager');
        $isGM = $user->hasRole('General Manager');
        $staffScope = $isStaff ? $user->id : null;

        $productId = $request->get('product') ? (int) $request->get('product') : null;

        // ── 1. Product cards grid data ─────────────────────────
        $products = Product::with('category')->get();
        $productCards = $this->buildProductCards($products);

        // ── 2. Collect items (filtered by product if set) ──────
        $items = $this->collectAllItems(null, $productId);

        // ── 3. Filtering ──────────────────────────────────────
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

        // ── 4. Summary stats ──────────────────────────────────
        $approved = $items->whereIn('status', ['Approved', 'Completed', 'Completed by GM', 'Lulus'])->count();
        $pending = $items->filter(fn ($i) => str_starts_with(strtolower($i['status'] ?? ''), 'pending'))->count();
        $rejected = $items->where('status', 'Rejected')->count();
        $draft = $items->where('status', 'Draft')->count();
        $pipelinePercent = $totalItems > 0 ? round($approved / $totalItems * 100) : 0;

        // ── 5. Module stat cards (global) ─────────────────────
        $moduleStats = $this->getModuleStats(null);

        // ── 6. Pending action items (role-based + product) ────
        $pendingItems = $this->getPendingItems($user, $isStaff, $isManager, $isGM, $staffScope, $productId);

        // ── 7. Activity feed ──────────────────────────────────
        $activities = $this->getActivityFeed($user, $isStaff);

        // ── 8. Workload (manager/GM only) ─────────────────────
        $workload = collect();
        if (!$isStaff) {
            $workload = $this->getWorkload();
        }

        // ── 9. Owner options for filter ───────────────────────
        $ownerOptions = User::whereHas('formulas')->orderBy('name')->get(['id', 'name']);

        // ── 10. Current product (for header display) ──────────
        $currentProduct = $productId ? Product::with('category')->find($productId) : null;

        // ── 11. Unlinked items count ──────────────────────────
        $unlinkedCount = $this->getUnlinkedCount();

        return view('timeline.index', compact(
            'items', 'totalItems', 'approved', 'pending', 'rejected', 'draft',
            'pipelinePercent', 'moduleStats', 'pendingItems', 'activities',
            'workload', 'ownerOptions', 'isStaff', 'isManager', 'isGM',
            'productCards', 'products', 'productId', 'currentProduct', 'unlinkedCount'
        ));
    }

    // ── Build product cards for grid ────────────────────────────
    private function buildProductCards($products): \Illuminate\Support\Collection
    {
        // Aggregate per module group by product_id using efficient queries
        $aggregates = $this->getProductAggregates();

        return $products->map(function ($product) use ($aggregates) {
            $pid = $product->id;
            $agg = $aggregates[$pid] ?? ['total' => 0, 'approved' => 0, 'pending' => 0, 'rejected' => 0, 'draft' => 0];

            $pipelinePercent = $agg['total'] > 0 ? round($agg['approved'] / $agg['total'] * 100) : 0;

            // Module chips (compact counts)
            $chips = [];
            foreach (self::MODULE_META as $key => $meta) {
                $count = $aggregates[$pid]['modules'][$key] ?? 0;
                if ($count > 0) {
                    $chips[] = ['key' => $key, 'label' => $meta['label'], 'count' => $count, 'color' => $meta['color']];
                }
            }

            return [
                'id'              => $product->id,
                'name'            => $product->name,
                'category'        => $product->category?->name ?? 'Tanpa kategori',
                'total'           => $agg['total'],
                'approved'        => $agg['approved'],
                'pending'         => $agg['pending'],
                'pipelinePercent' => $pipelinePercent,
                'chips'           => $chips,
            ];
        });
    }

    // ── Aggregate counts per product (one query per module) ─────
    private function getProductAggregates(): array
    {
        $aggregates = [];

        foreach (self::MODULE_MAP as $key => [$class, $table, $statusField]) {
            $query = (new $class)->newQuery()->selectRaw('product_id, COUNT(*) as total');

            if ($statusField) {
                [$approvedStatuses, $pendingStatuses, $rejectedStatuses] = match ($key) {
                    'npd-proposal' => [
                        ['Completed'],
                        ['On Track', 'In Progress', 'On Hold', 'Delayed'],
                        [],
                    ],
                    'formula', 'trial-rm', 'preformulation-study' => [
                        ['Approved', 'Completed'],
                        ['Pending Tahap 1', 'Pending Tahap 2'],
                        ['Rejected'],
                    ],
                    'trial-pm' => [
                        ['Approved'],
                        ['Pending Review', 'Pending Approval'],
                        ['Rejected'],
                    ],
                    'sample-evaluation' => [
                        ['Approved'],
                        ['In Progress'],
                        ['Reform'],
                    ],
                    'formula-approval' => [
                        ['Approved'],
                        ['Pending', 'Approval by OM'],
                        ['Rejected'],
                    ],
                    default => [[], [], []],
                };

                if ($approvedStatuses) {
                    $query->selectRaw("SUM(CASE WHEN {$statusField} IN ('" . implode("','", $approvedStatuses) . "') THEN 1 ELSE 0 END) as is_approved");
                }
                if ($pendingStatuses) {
                    $query->selectRaw("SUM(CASE WHEN {$statusField} IN ('" . implode("','", $pendingStatuses) . "') THEN 1 ELSE 0 END) as is_pending");
                }
                if ($rejectedStatuses) {
                    $query->selectRaw("SUM(CASE WHEN {$statusField} IN ('" . implode("','", $rejectedStatuses) . "') THEN 1 ELSE 0 END) as is_rejected");
                }
            }

            $query->groupBy('product_id');

            foreach ($query->get() as $row) {
                $pid = $row->product_id;
                if ($pid === null) continue;

                $aggregates[$pid] ??= ['total' => 0, 'approved' => 0, 'pending' => 0, 'rejected' => 0, 'draft' => 0, 'modules' => []];
                $aggregates[$pid]['total'] += $row->total;
                $aggregates[$pid]['approved'] += (int) ($row->is_approved ?? 0);
                $aggregates[$pid]['pending'] += (int) ($row->is_pending ?? 0);
                $aggregates[$pid]['rejected'] += (int) ($row->is_rejected ?? 0);
                $aggregates[$pid]['modules'][$key] = $row->total;
            }
        }

        return $aggregates;
    }

    // ── Count items without product_id ──────────────────────────
    private function getUnlinkedCount(): int
    {
        $count = 0;
        foreach (self::MODULE_MAP as [$class, $table]) {
            $count += (new $class)->newQuery()->whereNull('product_id')->count();
        }
        return $count;
    }

    // ── Collect items from all modules ──────────────────────────
    private function collectAllItems(?int $userId, ?int $productId = null): \Illuminate\Support\Collection
    {
        $items = collect();
        $userField = 'created_by';

        $mapItem = function ($model, $moduleKey, $nameField, $statusField = null, $codeField = 'code', $routeParam = null) use ($userField) {
            $meta = self::MODULE_META[$moduleKey];
            return [
                'module_key'  => $moduleKey,
                'module'      => $meta['label'],
                'group'       => $meta['group'],
                'color'       => $meta['color'],
                'name'        => $model->{$nameField} ?? $model->title ?? '—',
                'code'        => $model->{$codeField} ?? null,
                'status'      => $statusField ? ($model->{$statusField} ?? null) : null,
                'owner'       => $model->creator?->name ?? '—',
                'owner_id'    => $model->{$userField},
                'product_id'  => $model->product_id ?? null,
                'product_name'=> $model->product?->name ?? null,
                'updated_at'  => $model->updated_at,
                'route'       => route($meta['route'], $routeParam ?? $model),
            ];
        };

        foreach (self::MODULE_MAP as $key => [$class, $table, $statusField, $nameField, $codeField]) {
            $q = $class::query()->with('creator')->with('product')->latest();
            if ($userId) $q->where($userField, $userId);
            if ($productId) $q->where('product_id', $productId);

            foreach ($q->get() as $m) {
                $items->push($mapItem($m, $key, $nameField, $statusField, $codeField));
            }
        }

        return $items->sortByDesc('updated_at')->values();
    }

    // ── Module stat cards (global) ──────────────────────────────
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

    // ── Pending action items (role-based + product filter) ──────
    private function getPendingItems($user, bool $isStaff, bool $isManager, bool $isGM, ?int $userId, ?int $productId = null): \Illuminate\Support\Collection
    {
        $pending = collect();

        $scopeProduct = function ($q) use ($productId) {
            return $productId ? $q->where('product_id', $productId) : $q;
        };

        if ($isStaff) {
            $draftFormulas = $scopeProduct(Formula::where('created_by', $userId)
                ->whereIn('approval_status', ['Draft', 'Rejected']))->latest()->get();
            foreach ($draftFormulas as $f) {
                $pending->push([
                    'module' => 'Formula', 'name' => $f->name, 'code' => $f->code,
                    'status' => $f->approval_status, 'action' => $f->approval_status === 'Draft' ? 'Submit' : 'Reformulasi',
                    'route' => route('formulas.show', $f),
                ]);
            }

            $draftTrials = $scopeProduct(TrialRm::where('created_by', $userId)
                ->whereIn('approval_status', ['Draft', 'Rejected']))->latest()->get();
            foreach ($draftTrials as $t) {
                $pending->push([
                    'module' => 'Trial RM', 'name' => $t->sample_identity, 'code' => $t->code,
                    'status' => $t->approval_status, 'action' => $t->approval_status === 'Draft' ? 'Submit' : 'Reformulasi',
                    'route' => route('trial-rms.show', $t),
                ]);
            }

            $draftPreforms = $scopeProduct(PreformulationStudy::where('created_by', $userId)
                ->whereIn('approval_status', ['Draft', 'Rejected']))->latest()->get();
            foreach ($draftPreforms as $p) {
                $pending->push([
                    'module' => 'Preformulasi', 'name' => $p->product_name, 'code' => $p->code,
                    'status' => $p->approval_status, 'action' => $p->approval_status === 'Draft' ? 'Submit' : 'Reformulasi',
                    'route' => route('preformulation-studies.show', $p),
                ]);
            }

            $pendingPm = $scopeProduct(TrialPm::where('approval_status', 'Pending Review'))->latest()->get();
            foreach ($pendingPm as $tp) {
                $pending->push([
                    'module' => 'Trial PM', 'name' => $tp->packaging_material, 'code' => $tp->code,
                    'status' => $tp->approval_status, 'action' => 'Dept Approve',
                    'route' => route('trial-pms.show', $tp),
                ]);
            }
        }

        if ($isManager || $isGM) {
            $pendingT1Formula = $scopeProduct(Formula::where('approval_status', 'Pending Tahap 1'))->latest()->get();
            foreach ($pendingT1Formula as $f) {
                $pending->push([
                    'module' => 'Formula', 'name' => $f->name, 'code' => $f->code,
                    'status' => $f->approval_status, 'action' => 'Approve T1',
                    'route' => route('formulas.show', $f),
                ]);
            }

            $pendingT1Trial = $scopeProduct(TrialRm::where('approval_status', 'Pending Tahap 1'))->latest()->get();
            foreach ($pendingT1Trial as $t) {
                $pending->push([
                    'module' => 'Trial RM', 'name' => $t->sample_identity, 'code' => $t->code,
                    'status' => $t->approval_status, 'action' => 'Approve T1',
                    'route' => route('trial-rms.show', $t),
                ]);
            }

            $pendingT1Preform = $scopeProduct(PreformulationStudy::where('approval_status', 'Pending Tahap 1'))->latest()->get();
            foreach ($pendingT1Preform as $p) {
                $pending->push([
                    'module' => 'Preformulasi', 'name' => $p->product_name, 'code' => $p->code,
                    'status' => $p->approval_status, 'action' => 'Approve T1',
                    'route' => route('preformulation-studies.show', $p),
                ]);
            }
        }

        if ($isGM) {
            $pendingT2Formula = $scopeProduct(Formula::where('approval_status', 'Pending Tahap 2'))->latest()->get();
            foreach ($pendingT2Formula as $f) {
                $pending->push([
                    'module' => 'Formula', 'name' => $f->name, 'code' => $f->code,
                    'status' => $f->approval_status, 'action' => 'Approve T2',
                    'route' => route('formulas.show', $f),
                ]);
            }

            $pendingT2Trial = $scopeProduct(TrialRm::where('approval_status', 'Pending Tahap 2'))->latest()->get();
            foreach ($pendingT2Trial as $t) {
                $pending->push([
                    'module' => 'Trial RM', 'name' => $t->sample_identity, 'code' => $t->code,
                    'status' => $t->approval_status, 'action' => 'Approve T2',
                    'route' => route('trial-rms.show', $t),
                ]);
            }

            $pendingT2Preform = $scopeProduct(PreformulationStudy::where('approval_status', 'Pending Tahap 2'))->latest()->get();
            foreach ($pendingT2Preform as $p) {
                $pending->push([
                    'module' => 'Preformulasi', 'name' => $p->product_name, 'code' => $p->code,
                    'status' => $p->approval_status, 'action' => 'Approve T2',
                    'route' => route('preformulation-studies.show', $p),
                ]);
            }

            $pendingApproval = $scopeProduct(FormulaApprovalForm::where('approval_status', 'Pending'))->latest()->get();
            foreach ($pendingApproval as $a) {
                $pending->push([
                    'module' => 'Formula Approval', 'name' => $a->product_name, 'code' => $a->code,
                    'status' => $a->approval_status, 'action' => 'Approve',
                    'route' => route('formula-approvals.show', $a),
                ]);
            }
        }

        return $pending->sortByDesc('status')->values();
    }

    // ── Activity feed ───────────────────────────────────────────
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

    // ── Workload by owner ───────────────────────────────────────
    private function getWorkload(): \Illuminate\Support\Collection
    {
        $models = [
            Formula::class, TrialRm::class, TrialPm::class,
            Prf::class, NpdProposal::class, PreformulationStudy::class,
            SampleEvaluation::class, FormulaApprovalForm::class,
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
