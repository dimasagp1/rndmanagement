<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Formula;
use App\Models\TrialRm;
use App\Models\TrialPm;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();

        // ── Stat Cards ───────────────────────────────────
        $formulaQuery = Formula::where('approval_status', 'Approved');
        $trialRmQuery = TrialRm::query();
        $trialPmQuery = TrialPm::query();

        if ($user->hasRole('Staff R&D')) {
            $formulaQuery->where('created_by', $user->id);
            $trialRmQuery->where('created_by', $user->id);
            $trialPmQuery->where('created_by', $user->id);
        }

        $totalFormulas = $formulaQuery->count();
        $trialRmCount  = $trialRmQuery->count();
        $trialPmCount  = $trialPmQuery->count();

        // Pending approvals (role-aware)
        $pendingCount = 0;
        if ($user->hasRole('Operational Manager')) {
            $pendingCount = Formula::where('approval_status', 'Pending Tahap 1')->count();
        } elseif ($user->hasRole('General Manager')) {
            $pendingCount = Formula::where('approval_status', 'Pending Tahap 2')->count();
        } elseif ($user->hasRole('Staff R&D')) {
            // Staff sees their own items needing revision
            $pendingCount = Formula::where('created_by', $user->id)
                ->where('approval_status', 'Rejected')
                ->count();
        }

        // ── Approval Pipeline Stats (for manager/GM) ─────
        $pipelineStats = null;
        if ($user->can('approval_center.access')) {
            $pipelineStats = [
                'draft'   => Formula::where('approval_status', 'Draft')->count(),
                'tahap1'  => Formula::where('approval_status', 'Pending Tahap 1')->count(),
                'tahap2'  => Formula::where('approval_status', 'Pending Tahap 2')->count(),
                'approved'=> Formula::where('approval_status', 'Approved')->count(),
            ];
        }

        // ── Recent Activity (Activity Log) ────────────────
        $activityQuery = Activity::with('causer', 'subject')->latest();

        if ($user->hasRole('Staff R&D')) {
            $activityQuery->where('causer_id', $user->id);
        }

        $recentActivity = $activityQuery
            ->take(8)
            ->get()
            ->map(function ($log) {
                $subjectType = class_basename($log->subject_type ?? '');
                $module = match ($subjectType) {
                    'Formula'   => 'Formulasi RM',
                    'TrialRm'   => 'Trial RM',
                    'TrialPm'   => 'Trial PM',
                    'LogbookPm' => 'Logbook PM',
                    default     => $subjectType ?: 'Sistem',
                };
                return [
                    'module'    => $module,
                    'code'      => $log->subject?->code ?? '—',
                    'name'      => match ($subjectType) {
                        'Formula'   => $log->subject?->name ?? '—',
                        'TrialRm'   => $log->subject?->sample_identity ?? '—',
                        'TrialPm'   => $log->subject?->packaging_material ?? '—',
                        'LogbookPm' => $log->subject?->nama_material ?? '—',
                        default     => '—',
                    },
                    'event'     => $log->event,
                    'status'    => $log->properties['attributes']['approval_status']
                                   ?? $log->subject?->approval_status
                                   ?? $log->subject?->status_pengujian
                                   ?? 'Draft',
                    'causer'    => $log->causer?->name ?? 'Sistem',
                    'updated'   => $log->created_at,
                    'route'     => $this->getRoute($subjectType, $log->subject),
                ];
            });

        // Fallback: jika belum ada activity log, tampilkan data langsung dari model
        if ($recentActivity->isEmpty()) {
            $recentActivity = $this->getFallbackActivity($user);
        }

        // ── My Items (Staff) ─────────────────────────────
        $myItems = null;
        if ($user->hasRole('Staff R&D')) {
            $myItems = Formula::where('created_by', $user->id)
                ->latest()
                ->take(5)
                ->get();
        }

        return view('dashboard', compact(
            'totalFormulas',
            'trialRmCount',
            'trialPmCount',
            'pendingCount',
            'pipelineStats',
            'recentActivity',
            'myItems',
        ));
    }

    private function getRoute(string $type, $subject): ?string
    {
        if (! $subject) return null;

        return match ($type) {
            'Formula'   => route('formulas.show', $subject),
            'TrialRm'   => route('trial-rms.show', $subject),
            'TrialPm'   => route('trial-pms.show', $subject),
            'LogbookPm' => route('logbook-pm.show', $subject),
            default     => null,
        };
    }

    private function getFallbackActivity($user = null)
    {
        $formulaQuery = Formula::with('creator')->latest();
        $trialRmQuery = TrialRm::with(['creator', 'formula'])->latest();
        $trialPmQuery = TrialPm::with('creator')->latest();

        if ($user && $user->hasRole('Staff R&D')) {
            $formulaQuery->where('created_by', $user->id);
            $trialRmQuery->where('created_by', $user->id);
            $trialPmQuery->where('created_by', $user->id);
        }

        $formulas = $formulaQuery->take(4)->get()
            ->map(fn($f) => [
                'module'  => 'Formulasi RM',
                'code'    => $f->code,
                'name'    => $f->name,
                'event'   => 'created',
                'status'  => $f->approval_status,
                'causer'  => $f->creator?->name ?? '—',
                'updated' => $f->updated_at,
                'route'   => route('formulas.show', $f),
            ]);

        $trials = $trialRmQuery->take(2)->get()
            ->map(fn($t) => [
                'module'  => 'Trial RM',
                'code'    => $t->code,
                'name'    => $t->sample_identity,
                'event'   => 'created',
                'status'  => $t->decision ?? 'Draft',
                'causer'  => $t->creator?->name ?? '—',
                'updated' => $t->updated_at,
                'route'   => route('trial-rms.show', $t),
            ]);

        $trialPms = $trialPmQuery->take(2)->get()
            ->map(fn($tp) => [
                'module'  => 'Trial PM',
                'code'    => $tp->code,
                'name'    => $tp->packaging_material,
                'event'   => 'created',
                'status'  => $tp->approval_status ?? 'Draft',
                'causer'  => $tp->creator?->name ?? '—',
                'updated' => $tp->updated_at,
                'route'   => route('trial-pms.show', $tp),
            ]);

        return $formulas->concat($trials)->concat($trialPms)
            ->sortByDesc('updated')
            ->take(8)
            ->values();
    }
}
