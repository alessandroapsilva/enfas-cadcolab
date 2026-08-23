<?php

namespace App\Http\Controllers;

use App\Services\EnterpriseReportService;
use App\Services\GovernanceInsightsService;
use Illuminate\Http\Request;

class EnterpriseIntelligenceController extends Controller
{
    public function insights()
    {
        abort_unless(session('admin_logado'), 401);
        return response()->json(GovernanceInsightsService::generate());
    }

    public function reportSummary()
    {
        abort_unless(session('admin_logado'), 401);
        return response()->json(EnterpriseReportService::summary());
    }

    public function operationalReport(Request $request)
    {
        abort_unless(session('admin_logado'), 401);
        return response()->json(EnterpriseReportService::operational((int)$request->query('days',30)));
    }
}
