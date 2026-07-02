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
        $totalFormulas = Formula::where('approval_status', 'Approved')->count();

        $trialRmCount  = TrialRm::count();
        $trialPmCount  = TrialPm::count();

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
        $recentActivity = Activity::with('causer', 'subject')
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($log) {
                $subjectType = class_basename($log->subject_type ?? '');
                $module = match ($subjectType) {
                    'Formula'   => 'Formulasi RM',
                    'TrialRm'   => 'Trial RM',
                    'TrialPm'   => 'Trial PM',
                    default     => $subjectType ?: 'Sistem',
                };
                return [
                    'module'    => $module,
                    'code'      => $log->subject?->code ?? '—',
                    'name'      => match ($subjectType) {
                        'Formula'  => $log->subject?->name ?? '—',
                        'TrialRm'  => $log->subject?->sample_identity ?? '—',
                        'TrialPm'  => $log->subject?->packaging_material ?? '—',
                        default    => '—',
                    },
                    'event'     => $log->event,
                    'status'    => $log->properties['attributes']['approval_status']
                                   ?? $log->subject?->approval_status
                                   ?? 'Draft',
                    'causer'    => $log->causer?->name ?? 'Sistem',
                    'updated'   => $log->created_at,
                    'route'     => $this->getRoute($subjectType, $log->subject),
                ];
            });

        // Fallback: jika belum ada activity log, tampilkan data langsung dari model
        if ($recentActivity->isEmpty()) {
            $recentActivity = $this->getFallbackActivity();
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
            'Formula' => route('formulas.show', $subject),
            'TrialRm' => route('trial-rms.show', $subject),
            'TrialPm' => route('trial-pms.show', $subject),
            default   => null,
        };
    }

    private function getFallbackActivity()
    {
        $formulas = Formula::with('creator')->latest()->take(4)->get()
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

        $trials = TrialRm::with(['creator', 'formula'])->latest()->take(2)->get()
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

        return $formulas->merge($trials)
            ->sortByDesc('updated')
            ->take(5)
            ->values();
    }
}
