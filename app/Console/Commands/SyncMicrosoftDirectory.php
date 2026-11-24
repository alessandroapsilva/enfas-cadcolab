<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SyncMicrosoftDirectory extends Command
{
    protected $signature = 'm365:sync-users {--apply : Save inventory locally}';
    protected $description = 'Read Microsoft Entra users without modifying cloud accounts';

    public function handle(): int
    {
        try {
            $cfg = DB::table('configuracoes')->whereIn('chave', ['m365_tenant','m365_client','m365_secret'])->pluck('valor','chave');
            $tenant = strtolower(trim($cfg['m365_tenant'] ?? ''));
            if (!Str::isUuid($tenant) || empty($cfg['m365_client']) || empty($cfg['m365_secret'])) {
                $this->error('Configuracao Microsoft 365 incompleta.'); return self::FAILURE;
            }
            $auth = Http::asForm()->timeout(30)->post("https://login.microsoftonline.com/$tenant/oauth2/v2.0/token", [
                'client_id'=>$cfg['m365_client'], 'client_secret'=>$cfg['m365_secret'],
                'grant_type'=>'client_credentials', 'scope'=>'https://graph.microsoft.com/.default'
            ]);
            $token = $auth->json('access_token');
            if (!$auth->successful() || !is_string($token) || !$token) {
                $this->error('Falha de autenticacao Microsoft: HTTP '.$auth->status()); return self::FAILURE;
            }
            $url = 'https://graph.microsoft.com/v1.0/users?'.http_build_query(['$select'=>'id,userPrincipalName,mail,displayName,jobTitle,department,mobilePhone,accountEnabled,userType,assignedLicenses','$top'=>100]);
            $rows = []; $visited = []; $now = now();
            while ($url) {
                if (!str_starts_with($url, 'https://graph.microsoft.com/v1.0/users?') || isset($visited[$url])) {
                    throw new \RuntimeException('Invalid pagination');
                }
                $visited[$url] = true;
                $response = Http::withToken($token)->withOptions(['allow_redirects'=>false])->timeout(45)->get($url);
                if (!$response->successful()) {
                    $this->error('Falha na consulta Graph: HTTP '.$response->status()); return self::FAILURE;
                }
                $data = $response->json();
                if (!is_array($data['value'] ?? null)) throw new \RuntimeException('Invalid response');
                foreach ($data['value'] as $u) {
                    if (!Str::isUuid($u['id'] ?? '') || empty($u['userPrincipalName'])) throw new \RuntimeException('Invalid user');
                    $rows[$u['id']] = [
                        'tenant_id'=>$tenant, 'object_id'=>$u['id'], 'upn'=>$u['userPrincipalName'],
                        'email'=>$u['mail'] ?? null, 'display_name'=>$u['displayName'] ?? null,
                        'job_title'=>$u['jobTitle'] ?? null, 'department'=>$u['department'] ?? null,
                        'phone'=>$u['mobilePhone'] ?? null, 'enabled'=>$u['accountEnabled'] ?? null,
                        'user_type'=>$u['userType'] ?? null, 'licenses'=>json_encode($u['assignedLicenses'] ?? [], JSON_THROW_ON_ERROR),
                        'last_seen_at'=>$now
                    ];
                }
                $url = $data['@odata.nextLink'] ?? null;
            }
            if ($this->option('apply')) {
                DB::transaction(function () use ($rows) {
                    foreach (array_chunk(array_values($rows), 100) as $chunk) {
                        DB::table('microsoft_directory_users')->upsert($chunk, ['tenant_id','object_id'],
                            ['upn','email','display_name','job_title','department','phone','enabled','user_type','licenses','last_seen_at']);
                    }
                });
            }
            $this->info(count($rows).' contas consultadas. '.($this->option('apply') ? 'Inventario atualizado.' : 'Simulacao: nenhuma gravacao.'));
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Sincronizacao interrompida. Nenhuma alteracao foi enviada ao Microsoft 365.');
            return self::FAILURE;
        }
    }
}
