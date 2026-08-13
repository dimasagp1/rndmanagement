<?php

namespace App\Http\Controllers;

use App\Models\Formula;
use App\Models\TrialPm;
use App\Models\TrialRm;
use App\Models\User;
use Illuminate\Http\Request;

class TimelineController extends Controller
{
    private const STAGE_ORDER = [
        'Draf', 'Pra-Trial', 'Optimalisasi', 'Final', 'Product Form',
        'Laboratory Trial', 'Sensory Test', 'Plant Trial', 'Market Test',
    ];

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Formula::with('creator')->latest();

        $isStaff = $user->hasRole('Staff R&D');
        if ($isStaff) {
            $query->where('created_by', $user->id);
        }

        // ── Stats modul (sama seperti dashboard) ───────────────
        $statsFormulaQuery = Formula::whereIn('approval_status', ['Approved', 'Completed']);
        $statsTrialRmQuery = TrialRm::query();
        $statsTrialPmQuery = TrialPm::query();

        if ($isStaff) {
            $statsFormulaQuery->where('created_by', $user->id);
            $statsTrialRmQuery->where('created_by', $user->id);
            $statsTrialPmQuery->where('created_by', $user->id);
        }

        $moduleStats = [
            'formulaApproved' => $statsFormulaQuery->count(),
            'trialRm'         => $statsTrialRmQuery->count(),
            'trialPm'         => $statsTrialPmQuery->count(),
            'myItems'         => Formula::where('created_by', $user->id)->count(),
        ];

        $statusMap = [
            'on-track' => ['Approved'],
            'in-review' => ['Pending Tahap 1', 'Pending Tahap 2'],
            'blocked' => ['Rejected'],
            'completed' => ['Completed'],
        ];

        if ($status = $request->get('status')) {
            $query->whereIn('approval_status', $statusMap[$status] ?? []);
        }

        if ($owner = $request->get('owner')) {
            $query->where('created_by', $owner);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('development_stage', 'like', "%{$search}%")
                  ->orWhereHas('creator', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $formulas = $query->get()->sortBy(function ($f) {
            $idx = array_search($f->development_stage, self::STAGE_ORDER);
            return $idx === false ? 999 : $idx;
        })->values();

        $total = $formulas->count();
        $onTrack = $formulas->whereIn('approval_status', ['Approved'])->count();
        $completed = $formulas->whereIn('approval_status', ['Completed'])->count();
        $inReview = $formulas->whereIn('approval_status', ['Pending Tahap 1', 'Pending Tahap 2'])->count();
        $blocked = $formulas->whereIn('approval_status', ['Rejected'])->count();
        $pipelinePercent = $total > 0 ? round(($onTrack + $completed) / $total * 100) : 0;

        $base = Formula::with('creator');
        if ($user->hasRole('Staff R&D')) {
            $base->where('created_by', $user->id);
        }
        $workload = $base
            ->selectRaw('created_by, COUNT(*) as total')
            ->groupBy('created_by')
            ->orderByDesc('total')
            ->get();

        $owners = $workload->map(function ($w) {
            $owner = $w->creator;
            return [
                'name'  => $owner?->name ?? 'Tanpa Owner',
                'initials' => $this->initials($owner?->name ?? 'T'),
                'total' => $w->total,
            ];
        });

        $ownerOptions = User::whereHas('formulas')->orderBy('name')->get(['id', 'name']);

        $rows = $formulas->map(function ($f) {
            return [
                'stage'  => $f->development_stage,
                'code'   => $f->code,
                'name'   => $f->name,
                'status' => $f->approval_status,
                'owner'  => $f->creator?->name ?? '—',
                'initials' => $this->initials($f->creator?->name ?? '—'),
                'target' => $f->updated_at->format('d M Y'),
                'target_date' => $f->updated_at,
            ];
        });

        $decisionPoints = $rows
            ->filter(fn ($r) => in_array($r['status'], ['Pending Tahap 1', 'Pending Tahap 2', 'Rejected']))
            ->sortBy('target_date')
            ->values();

        return view('timeline.index', compact(
            'total', 'onTrack', 'completed', 'inReview', 'blocked', 'pipelinePercent',
            'rows', 'decisionPoints', 'owners', 'ownerOptions', 'statusMap', 'moduleStats'
        ));
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
        return $initials ?: 'T';
    }
}
