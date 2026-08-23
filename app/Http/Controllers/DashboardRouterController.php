<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardRouterController extends Controller
{
    public function index(Request $request)
    {
        if ($request->query('p') === 'identity-directory') {
            return app(IdentityDirectoryController::class)->dashboard($request);
        }

        return app(AdminController::class)->index($request);
    }
}
