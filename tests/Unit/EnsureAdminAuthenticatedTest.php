<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureAdminAuthenticated;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class EnsureAdminAuthenticatedTest extends TestCase
{
    public function test_allows_authenticated_admin(): void
    {
        $request = Request::create('/dashboard');
        $session = new Store('test', new MockArraySessionStorage());
        $session->put('admin_logado', true);
        $request->setLaravelSession($session);

        $response = (new EnsureAdminAuthenticated())->handle(
            $request,
            fn () => response('ok')
        );

        $this->assertSame(200, $response->getStatusCode());
    }
}
