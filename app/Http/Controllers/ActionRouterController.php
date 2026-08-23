<?php

namespace App\Http\Controllers;

use App\Services\EmailAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ActionRouterController extends Controller
{
    public function __construct(private readonly EmailAuditService $emailAudit) {}

    public function handle(Request $request)
    {
        if ($request->input('action') === 'save_todas_configs' && is_array($request->input('cfg'))) {
            $cfg = $request->input('cfg');
            foreach (['m365_secret','gw_json','smtp_pass','wp_token','wp_app_secret','wp_verify_token'] as $secret) {
                if (array_key_exists($secret, $cfg) && trim((string)$cfg[$secret]) === '') unset($cfg[$secret]);
            }
            $request->merge(['cfg'=>$cfg]);
        }

        $response = app(AdminController::class)->acaoRapida($request);

        if ($request->input('action') === 'send_pwd_email') {
            $failed = session()->has('swal_error');
            $email = trim((string)$request->input('email_pessoal'));
            $relatedId = null;
            if ($email !== '' && Schema::hasTable('pre_registros')) {
                $relatedId = DB::table('pre_registros')->where('e_mail_pessoal', $email)->value('id');
            }
            $this->emailAudit->record([
                'recipient'=>$email,
                'subject'=>'Credencial Corporativa',
                'template_key'=>'password_reset',
                'status'=>$failed ? 'failed' : 'sent',
                'provider'=>'smtp',
                'error_message'=>$failed ? session('swal_error') : null,
                'related_type'=>'colaborador',
                'related_id'=>$relatedId,
                'triggered_by'=>session('admin_nome','Sistema'),
            ]);
        }

        return $response;
    }
}
