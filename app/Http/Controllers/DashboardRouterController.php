<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardRouterController extends Controller
{
    public function index(Request $request)
    {
        if ($request->query('p') === 'identity-directory') {
            return app(IdentityDirectoryController::class)->dashboard($request);
        }

        $response = app(AdminController::class)->index($request);
        if (!$response instanceof View) return $response;

        $html = $response->render();
        $head = '<link rel="stylesheet" href="/cadcolab-enterprise.css?v=20260823">';
        $body = '<script src="/cadcolab-enterprise.js?v=20260823"></script>';
        $html = str_replace('</head>', $head.'</head>', $html);
        $html = str_replace('</body>', $body.'</body>', $html);

        return response($html);
    }
}
