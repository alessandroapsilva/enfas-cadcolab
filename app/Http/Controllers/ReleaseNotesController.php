<?php

namespace App\Http\Controllers;

use App\Services\ReleaseNotesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReleaseNotesController extends Controller
{
    public function index(Request $request, ReleaseNotesService $service): JsonResponse
    {
        if (!session('admin_logado')) abort(403);

        return response()->json([
            'current' => $service->current(),
            'versions' => $service->all(),
        ]);
    }
}
