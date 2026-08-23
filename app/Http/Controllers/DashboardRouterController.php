<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class DashboardRouterController extends Controller
{
    public function index(Request $request)
    {
        if (!session('admin_logado')) {
            return redirect('/login');
        }

        $page = $request->query('p', 'dashboard');

        if ($page === 'badge-studio') {
            return response()
                ->view('modules.badge-studio')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }

        if ($page === 'identity-directory') {
            $response = app(IdentityDirectoryController::class)->dashboard($request);
        } else {
            $response = app(AdminController::class)->index($request);
        }

        $html = null;
        $status = 200;
        $headers = [];

        if ($response instanceof View) {
            $html = $response->render();
        } elseif ($response instanceof SymfonyResponse) {
            $contentType = (string) $response->headers->get('Content-Type', '');
            if ($response->isRedirection() || ($contentType !== '' && !str_contains(strtolower($contentType), 'text/html'))) {
                return $response;
            }
            $html = $response->getContent();
            $status = $response->getStatusCode();
            $headers = $response->headers->all();
        }

        if (!is_string($html) || $html === '' || !str_contains($html, '</body>')) {
            return $response;
        }

        $version = '20260823-v4320';
        $head = '<link rel="stylesheet" href="/cadcolab-enterprise.css?v='.$version.'">'
              . '<link rel="stylesheet" href="/cadcolab-intelligence.css?v='.$version.'">'
              . '<link rel="stylesheet" href="/cadcolab-shell.css?v='.$version.'">';
        $body = '<script src="/cadcolab-enterprise.js?v='.$version.'"></script>'
              . '<script src="/cadcolab-intelligence.js?v='.$version.'"></script>'
              . '<script src="/cadcolab-shell.js?v='.$version.'"></script>'
              . '<script src="/cadcolab-modules.js?v='.$version.'"></script>';

        if (!str_contains($html, '/cadcolab-shell.css')) $html = str_replace('</head>', $head.'</head>', $html);
        if (!str_contains($html, '/cadcolab-shell.js')) $html = str_replace('</body>', $body.'</body>', $html);

        $final = response($html, $status);
        foreach ($headers as $name => $values) {
            if (in_array(strtolower($name), ['content-length', 'transfer-encoding'], true)) continue;
            foreach ((array) $values as $value) $final->headers->set($name, $value, false);
        }
        $final->headers->set('Content-Type', 'text/html; charset=UTF-8');
        $final->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        return $final;
    }
}
