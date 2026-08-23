<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardRouterController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->query('p', 'dashboard');

        if ($page === 'identity-directory') {
            $response = app(IdentityDirectoryController::class)->dashboard($request);
        } else {
            if ($page === 'badge-studio') {
                $request->query->set('p', 'dashboard');
            }
            $response = app(AdminController::class)->index($request);
            if ($page === 'badge-studio') {
                $request->query->set('p', 'badge-studio');
            }
        }

        if (!$response instanceof View) return $response;

        $html = $response->render();
        $version = '20260823-v430';
        $head = '<link rel="stylesheet" href="/cadcolab-enterprise.css?v='.$version.'">'
              . '<link rel="stylesheet" href="/cadcolab-intelligence.css?v='.$version.'">'
              . '<link rel="stylesheet" href="/cadcolab-shell.css?v='.$version.'">'
              . '<link rel="stylesheet" href="/cadcolab-badge-studio.css?v='.$version.'">';
        $body = '<script src="/cadcolab-enterprise.js?v='.$version.'"></script>'
              . '<script src="/cadcolab-intelligence.js?v='.$version.'"></script>'
              . '<script src="/cadcolab-shell.js?v='.$version.'"></script>'
              . '<script src="/cadcolab-modules.js?v='.$version.'"></script>'
              . '<script src="/cadcolab-badge-studio.js?v='.$version.'"></script>';
        $html = str_replace('</head>', $head.'</head>', $html);
        $html = str_replace('</body>', $body.'</body>', $html);

        return response($html);
    }
}
