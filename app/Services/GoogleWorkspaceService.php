<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleWorkspaceService
{
    private static function token(array $cfg): ?string
    {
        if (empty($cfg['gw_json'])) return null;
        $k=@json_decode($cfg['gw_json'],true); if(!$k || empty($k['client_email']) || empty($k['private_key']) || empty($k['token_uri'])) return null;
        $h=self::b64(json_encode(['alg'=>'RS256','typ'=>'JWT']));
        $c=self::b64(json_encode(['iss'=>$k['client_email'],'scope'=>'https://www.googleapis.com/auth/admin.directory.user','aud'=>$k['token_uri'],'exp'=>time()+3600,'iat'=>time()]));
        openssl_sign("$h.$c",$sig,$k['private_key'],'sha256WithRSAEncryption');
        $jwt="$h.$c.".self::b64($sig);
        $r=Http::asForm()->post($k['token_uri'],['grant_type'=>'urn:ietf:params:oauth:grant-type:jwt-bearer','assertion'=>$jwt]);
        if(!$r->successful()){ CloudIntegrationService::erro('Google Workspace Token',$r->body()); return null; }
        return $r->json('access_token');
    }

    public static function manage(string $email,string $action,?string $password=null,array $data=[],array $cfg=[]): array
    {
        $token=self::token($cfg); if(!$token) return ['sucesso'=>false,'erro'=>'Falha ao autenticar no Google Workspace.'];
        $url='https://admin.googleapis.com/admin/directory/v1/users/'.rawurlencode($email); $method='put'; $payload=[];
        if($action==='criar'){
            $method='post'; $url='https://admin.googleapis.com/admin/directory/v1/users';
            $parts=preg_split('/\s+/',trim($data['nome']??''));
            $payload=['primaryEmail'=>$email,'password'=>$password,'name'=>['givenName'=>$parts[0]??'Usuário','familyName'=>count($parts)>1?end($parts):'.'],'changePasswordAtNextLogin'=>false];
        } elseif($action==='bloquear') $payload=['suspended'=>true];
        elseif(in_array($action,['atualizar','ativar'],true)) $payload=['suspended'=>false];
        elseif($action==='reset_senha') $payload=['password'=>$password,'suspended'=>false];
        elseif($action==='excluir') $method='delete';
        else return ['sucesso'=>false,'erro'=>'Ação Google Workspace não suportada.'];
        $r=Http::withToken($token)->acceptJson()->$method($url,$payload);
        if(!$r->successful()) CloudIntegrationService::erro('Google Workspace '.$action,$r->body());
        return ['sucesso'=>$r->successful(),'status'=>$r->status(),'erro'=>$r->successful()?null:data_get($r->json(),'error.message',$r->body())];
    }

    public static function diagnose(string $email,array $cfg=[]): array
    {
        $token=self::token($cfg); if(!$token) return ['sucesso'=>false,'erro'=>'Falha ao autenticar no Google Workspace.'];
        $r=Http::withToken($token)->acceptJson()->get('https://admin.googleapis.com/admin/directory/v1/users/'.rawurlencode($email));
        return ['sucesso'=>$r->successful(),'status'=>$r->status(),'usuario'=>$r->successful()?$r->json():null,'erro'=>$r->successful()?null:data_get($r->json(),'error.message',$r->body())];
    }

    private static function b64($value): string
    {
        return rtrim(strtr(base64_encode($value),'+/','-_'),'=');
    }
}
