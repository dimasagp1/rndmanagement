<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public const TABS = [
        'prf'                  => 'PRF',
        'npd-proposal'         => 'NPD Proposal',
        'preformulation-qbd'   => 'QbD',
        'formulation-development' => 'Formulation Development',
        'sample-evaluation'    => 'Sample Evaluation',
        'formula-approval'     => 'Formula Approval',
        'stability-test'       => 'Stability Test',
        'packaging-development' => 'Packaging Development',
        'approval-formula-design' => 'Approval Formula & Design',
        'regulatory-dossier'   => 'Regulatory Dossier',
        'technology-transfer'  => 'Technology Transfer',
        'nie-approved'         => 'NIE Approved',
        'commercial-production' => 'Commercial Production',
    ];

    public const GROUPS = [
        'Insight & Concept' => ['prf', 'npd-proposal'],
        'Development & Regulatory' => [
            'preformulation-qbd',
            'formulation-development',
            'sample-evaluation',
            'formula-approval',
            'stability-test',
            'packaging-development',
            'approval-formula-design',
            'regulatory-dossier',
            'technology-transfer',
            'nie-approved',
        ],
        'Commercialization' => ['commercial-production'],
    ];

    public function index()
    {
        return redirect()->route('general.show', array_key_first(self::TABS));
    }

    public function show(string $tab)
    {
        abort_unless(array_key_exists($tab, self::TABS), 404);

        if ($tab === 'prf') {
            return redirect()->route('prfs.index');
        }

        if ($tab === 'npd-proposal') {
            return redirect()->route('npd-proposals.index');
        }

        if ($tab === 'preformulation-qbd') {
            return redirect()->route('preformulation-studies.index');
        }

        return view('general.index', [
            'activeTab' => $tab,
            'tabs'      => self::TABS,
            'groups'    => self::GROUPS,
            'title'     => 'General',
        ]);
    }
}