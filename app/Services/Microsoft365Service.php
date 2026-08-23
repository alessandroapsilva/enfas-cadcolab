<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class Microsoft365Service
{
    private static function token(array $cfg): ?string
    {
        if (empty($cfg['m365_tenant']) || empty($cfg['m365_client']) || empty($cfg['m365_secret'])) return null;
        $r = Http::asForm()->post("https://login.microsoftonline.com/{$cfg['m365_tenant']}/oauth2/v2.0/token", [
            'client_id'=>$cfg['m365_client'], 'scope'=>'https://graph.microsoft.com/.default',
            'client_secret'=>$cfg['m365_secret'], 'grant_type'=>'client_credentials'
        ]);
        if (!$r->successful()) { CloudIntegrationService::erro('M365 Token', $r->body()); return null; }
        return $r->json('access_token');
    }

    private static function step(string $token, string $method, string $url, array $payload, string $step): array
    {
        $r = Http::withToken($token)->acceptJson()->$method($url, $payload);
        $result = ['etapa'=>$step,'sucesso'=>$r->successful(),'status'=>$r->status(),'request_id'=>$r->header('request-id') ?: $r->header('client-request-id')];
        if (!$r->successful()) {
            $body = $r->json();
            $result['codigo'] = data_get($body, 'error.code');
            $result['erro'] = data_get($body, 'error.message', $r->body());
            CloudIntegrationService::erro("M365 {$step}", json_encode($result, JSON_UNESCAPED_UNICODE));
        }
        return $result;
    }

    public static function manage(string $email, string $action, ?string $password=null, array $data=[], array $cfg=[]): array
    {
        $token = self::token($cfg);
        if (!$token) return ['sucesso'=>false,'erro'=>'Falha ao autenticar no Microsoft Graph.','etapas'=>[]];
        $userUrl = 'https://graph.microsoft.com/v1.0/users/' . rawurlencode($email);
        $steps = [];

        if ($action === 'criar') {
            $payload = ['accountEnabled'=>true,'displayName'=>$data['nome']??'','mailNickname'=>explode('@',$email)[0],'userPrincipalName'=>$email,'passwordProfile'=>['forceChangePasswordNextSignIn'=>false,'password'=>$password],'usageLocation'=>'BR','companyName'=>'Clínica ENFAS'];
            if (!empty($data['cargo'])) $payload['jobTitle']=$data['cargo'];
            if (!empty($data['setor'])) $payload['department']=$data['setor'];
            $steps[] = self::step($token,'post','https://graph.microsoft.com/v1.0/users',$payload,'criar_usuario');
            if (!end($steps)['sucesso']) return ['sucesso'=>false,'etapas'=>$steps];
            if (!empty($data['telefone'])) $steps[] = self::step($token,'patch',$userUrl,['mobilePhone'=>self::phone($data['telefone'])],'telefone');
        } elseif ($action === 'atualizar') {
            $profile=['usageLocation'=>'BR'];
            if (!empty($data['nome'])) $profile['displayName']=$data['nome'];
            if (array_key_exists('cargo',$data)) $profile['jobTitle']=$data['cargo'] ?: null;
            if (array_key_exists('setor',$data)) $profile['department']=$data['setor'] ?: null;
            $steps[] = self::step($token,'patch',$userUrl,$profile,'perfil');
            if (!empty($data['telefone'])) $steps[] = self::step($token,'patch',$userUrl,['mobilePhone'=>self::phone($data['telefone'])],'telefone');
            $steps[] = self::step($token,'patch',$userUrl,['accountEnabled'=>true],'conta');
        } elseif ($action === 'bloquear') {
            $steps[] = self::step($token,'patch',$userUrl,['accountEnabled'=>false],'conta');
        } elseif ($action === 'ativar') {
            $steps[] = self::step($token,'patch',$userUrl,['accountEnabled'=>true],'conta');
        } elseif ($action === 'reset_senha') {
            $steps[] = self::step($token,'patch',$userUrl,['passwordProfile'=>['forceChangePasswordNextSignIn'=>false,'password'=>$password]],'senha');
        } elseif ($action === 'excluir') {
            $r=Http::withToken($token)->delete($userUrl);
            $steps[]=['etapa'=>'excluir_usuario','sucesso'=>$r->successful(),'status'=>$r->status(),'request_id'=>$r->header('request-id')];
            if(!$r->successful()) CloudIntegrationService::erro('M365 excluir_usuario',$r->body());
        } else return ['sucesso'=>false,'erro'=>'Ação Microsoft 365 não suportada.','etapas'=>[]];

        if ($action==='bloquear' && !empty($cfg['m365_sku_basic'])) {
            $steps[] = self::step($token,'post',"$userUrl/assignLicense",['addLicenses'=>[],'removeLicenses'=>[$cfg['m365_sku_basic']]],'licenca');
        } elseif (isset($data['m365_perfil']) && in_array($action,['criar','atualizar','ativar'],true) && !empty($cfg['m365_sku_basic'])) {
            $sku=$cfg['m365_sku_basic']; $basic=$data['m365_perfil']==='Basic';
            $steps[] = self::step($token,'post',"$userUrl/assignLicense",['addLicenses'=>$basic?[['skuId'=>$sku]]:[],'removeLicenses'=>$basic?[]:[$sku]],'licenca');
        }
        return ['sucesso'=>!collect($steps)->contains(fn($s)=>empty($s['sucesso'])),'etapas'=>$steps];
    }

    public static function diagnose(string $email, array $cfg=[]): array
    {
        $token=self::token($cfg); if(!$token) return ['sucesso'=>false,'erro'=>'Falha ao autenticar no Microsoft Graph.'];
        $url='https://graph.microsoft.com/v1.0/users/'.rawurlencode($email). '?$select=id,displayName,userPrincipalName,accountEnabled,mobilePhone,jobTitle,department,usageLocation,assignedLicenses';
        $r=Http::withToken($token)->acceptJson()->get($url);
        if(!$r->successful()) return ['sucesso'=>false,'status'=>$r->status(),'erro'=>data_get($r->json(),'error.message',$r->body())];
        return ['sucesso'=>true,'usuario'=>$r->json(),'request_id'=>$r->header('request-id')];
    }

    private static function phone(string $value): string
    {
        $n=preg_replace('/\D+/','',$value); return str_starts_with($n,'55')?'+'.$n:'+55'.$n;
    }
}
