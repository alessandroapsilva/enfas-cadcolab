<?php

namespace App\Http\Controllers;

use App\Services\EmailAuditService;
use Illuminate\Http\Request;

class ActionRouterController extends Controller
{
    public function __construct(private readonly EmailAuditService $emailAudit) {}

    public function handle(Request $request)
    {
        if ($request->input('action') === 'save_todas_configs' && is_array($request->input('cfg'))) {
            $cfg = $request->input('cfg');
            foreach (['m365_secret','gw_json','smtp_pass','wp_token','wp_app_secret','wp_verify_token'] as $secret) {
                if (array_key_exists($secret, $cfg) && trim((string) $cfg[$secret]) === '') {
                    unset($cfg[$secret]);
                }
            }
            $request->merge(['cfg' => $cfg]);
        }

        $response = app(AdminController::class)->acaoRapida($request);

        if ($request->input('action') === 'send_pwd_email') {
            $failed = session()->has('swal_error');
            $this->emailAudit->record([
                'recipient' => $request->input('email_pessoal'),
                'subject' => 'Credencial Corporativa',
                'template_key' => 'password_reset',
                'status' => $failed ? 'failed' : 'sent',
                'provider' => 'smtp',
                'error_message' => $failed ? session('swal_error') : null,
                'related_type' => 'colaborador',
                'related_id' => $request->input('id'),
            ]);
        }

        return $response;
    }
}
