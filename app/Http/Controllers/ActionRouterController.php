<?php

namespace App\Http\Controllers;

use App\Services\EmailAuditService;
use App\Services\ModuleRegistryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ActionRouterController extends Controller
{
    public function __construct(
        private readonly EmailAuditService $emailAudit,
        private readonly ModuleRegistryService $modules,
    ) {}

    public function handle(Request $request)
    {
        $this->authorizeAction($request);

        if ($request->input('action') === 'save_todas_configs' && is_array($request->input('cfg'))) {
            $cfg = $request->input('cfg');
            foreach (['m365_secret','gw_json','smtp_pass','wp_token','wp_app_secret','wp_verify_token'] as $secret) {
                if (array_key_exists($secret, $cfg) && trim((string)$cfg[$secret]) === '') unset($cfg[$secret]);
            }
            $request->merge(['cfg'=>$cfg]);
        }

        $response = app(AdminController::class)->acaoRapida($request);

        if (str_starts_with((string) $request->input('action'), 'save_') && $request->filled('return_page') && method_exists($response, 'setTargetUrl')) {
            $response->setTargetUrl('/dashboard?p='.urlencode((string) $request->input('return_page')));
        }

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

    private function authorizeAction(Request $request): void
    {
        abort_unless(session('admin_logado'), 401);

        $role = $this->modules->role();
        $action = (string) $request->input('action');
        $table = (string) $request->input('table');
        $tables = ['pre_registros','unidades','setores','cargos','perfis_acesso','sistemas_hc','usuarios_admin'];

        if ($table !== '' && ! in_array($table, $tables, true)) {
            abort(422, 'Recurso administrativo inválido.');
        }

        $specialSaves = ['save_colab','save_todas_configs','save_meu_perfil'];
        if (str_starts_with($action, 'save_') && ! in_array($action, $specialSaves, true)) {
            $aliases = ['sistema'=>'sistemas_hc','perfil'=>'perfis_acesso','grupos'=>'perfis_acesso','cargo'=>'cargos','setor'=>'setores','unidade'=>'unidades'];
            $actionTable = $table !== '' ? $table : ($aliases[substr($action, 5)] ?? substr($action, 5));
            abort_unless(in_array($actionTable, $tables, true), 422, 'Cadastro administrativo inválido.');
        }

        if (in_array($role, ['admin','ti'], true)) {
            return;
        }

        $rhActions = ['save_colab','change_status'];
        $rhTables = ['unidades','setores','cargos'];
        if ($role === 'rh' && (in_array($action, $rhActions, true) || (str_starts_with($action, 'save_') && in_array($table, $rhTables, true)))) {
            return;
        }

        abort(403, 'Seu perfil não possui permissão para executar esta ação.');
    }
}
