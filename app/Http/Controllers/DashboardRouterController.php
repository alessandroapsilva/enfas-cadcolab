<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardRouterController extends Controller
{
    public function index(Request $request)
    {
        $response = $request->query('p') === 'identity-directory'
            ? app(IdentityDirectoryController::class)->dashboard($request)
            : app(AdminController::class)->index($request);

        if (!$response instanceof View) return $response;

        $html = $response->render();
        $version = '20260823-shell420';
        $head = '<link rel="stylesheet" href="/cadcolab-enterprise.css?v='.$version.'">'
              . '<link rel="stylesheet" href="/cadcolab-intelligence.css?v='.$version.'">'
              . '<link rel="stylesheet" href="/cadcolab-shell.css?v='.$version.'">';
        $body = '<script src="/cadcolab-enterprise.js?v='.$version.'"></script>'
              . '<script src="/cadcolab-intelligence.js?v='.$version.'"></script>'
              . '<script src="/cadcolab-shell.js?v='.$version.'"></script>';
        $html = str_replace('</head>', $head.'</head>', $html);
        $html = str_replace('</body>', $body.'</body>', $html);

        return response($html);
    }
}
