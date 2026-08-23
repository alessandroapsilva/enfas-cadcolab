<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class LdapDirectoryService
{
    public function __construct(private readonly IdentitySettingsService $settings) {}

    public function isEnabled(): bool
    {
        $this->settings->apply();
        return (bool) config('identity.enabled');
    }

    public function testConnection(): array
    {
        try {
            $ldap = $this->connectAndBind();
            @ldap_unbind($ldap);
            return ['success'=>true,'message'=>'Conexão e bind LDAP realizados com sucesso.'];
        } catch (\Throwable $e) {
            return ['success'=>false,'message'=>$e->getMessage()];
        }
    }

    public function authenticate(string $username, string $password): ?array
    {
        if (!$this->isEnabled() || trim($username)==='' || $password==='') return null;
        $ldap = $this->connectAndBind();
        $entry = $this->findUser($username, $ldap);
        if (!$entry || !$this->isActive($entry)) return null;
        $dn = $entry['dn'] ?? null;
        if (!$dn || !@ldap_bind($ldap, $dn, $password)) return null;
        return $this->normalizeUser($entry);
    }

    public function syncAll(): array
    {
        $ldap = $this->connectAndBind();
        $users = $this->fetchUsers($ldap);
        $groups = $this->fetchGroups($ldap);
        DB::transaction(function () use ($users,$groups) {
            foreach ($groups as $g) DB::table('identity_directory_groups')->updateOrInsert(['directory_key'=>$g['directory_key']], $g + ['synced_at'=>now(),'updated_at'=>now(),'created_at'=>now()]);
            foreach ($users as $u) $this->persistUser($u);
        });
        return ['users'=>count($users),'groups'=>count($groups)];
    }

    public function persistUser(array $u): void
    {
        DB::table('identity_directory_users')->updateOrInsert(['directory_key'=>$u['directory_key']], [
            'username'=>$u['username'],'user_principal_name'=>$u['user_principal_name'],'display_name'=>$u['display_name'],'email'=>$u['email'],'employee_id'=>$u['employee_id'],'department'=>$u['department'],'title'=>$u['title'],'phone'=>$u['phone'],'mobile'=>$u['mobile'],'manager_dn'=>$u['manager_dn'],'distinguished_name'=>$u['distinguished_name'],'groups_json'=>json_encode($u['groups'],JSON_UNESCAPED_UNICODE),'profile'=>$u['profile'],'is_active'=>$u['is_active'],'synced_at'=>now(),'updated_at'=>now(),'created_at'=>now(),
        ]);
        if (in_array($u['profile'],['Admin','TI','RH'],true)) {
            DB::table('usuarios_admin')->updateOrInsert(['usuario'=>$u['username']], [
                'nome'=>$u['display_name'] ?: $u['username'],'email'=>$u['email'],'senha'=>password_hash(bin2hex(random_bytes(32)),PASSWORD_BCRYPT),'perfil'=>$u['profile'],'identity_source'=>'ldap','ldap_directory_key'=>$u['directory_key'],'ldap_dn'=>$u['distinguished_name'],'ativo'=>$u['is_active'],'ldap_synced_at'=>now(),'updated_at'=>now(),'created_at'=>now(),
            ]);
        }
    }

    public function findUser(string $username, $ldap=null): ?array
    {
        $ldap = $ldap ?: $this->connectAndBind(); $c=config('identity.ldap');
        $u=ldap_escape($username,'',LDAP_ESCAPE_FILTER); $upn=$username;
        if ($c['account_suffix'] && !str_contains($username,'@')) $upn.=$c['account_suffix'];
        $filter='(&'.$c['user_filter'].'(|('.$c['username_attribute'].'='.$u.')(userPrincipalName='.ldap_escape($upn,'',LDAP_ESCAPE_FILTER).')))';
        $r=@ldap_search($ldap,$c['users_dn'],$filter,$this->requestedAttributes()); if(!$r) return null;
        $e=ldap_get_entries($ldap,$r); return ($e['count']??0)>0?$e[0]:null;
    }

    private function fetchUsers($ldap): array
    {
        $c=config('identity.ldap'); $r=@ldap_search($ldap,$c['users_dn'],$c['user_filter'],$this->requestedAttributes());
        if(!$r) throw new RuntimeException('Não foi possível consultar usuários no LDAP.');
        $e=ldap_get_entries($ldap,$r); $out=[]; for($i=0;$i<($e['count']??0);$i++) $out[]=$this->normalizeUser($e[$i]); return $out;
    }

    private function fetchGroups($ldap): array
    {
        $c=config('identity.ldap'); $r=@ldap_search($ldap,$c['groups_dn'],$c['group_filter'],['objectGUID','cn','distinguishedName','description']);
        if(!$r) throw new RuntimeException('Não foi possível consultar grupos no LDAP.');
        $e=ldap_get_entries($ldap,$r); $out=[];
        for($i=0;$i<($e['count']??0);$i++){ $dn=$e[$i]['dn']??''; $out[]=['directory_key'=>sha1($dn),'name'=>$this->first($e[$i],'cn')?:$dn,'distinguished_name'=>$dn,'description'=>$this->first($e[$i],'description')]; }
        return $out;
    }

    private function normalizeUser(array $e): array
    {
        $a=config('identity.ldap.attributes'); $dn=$e['dn']??''; $groups=$this->values($e,strtolower($a['groups'])); $username=$this->first($e,strtolower($a['username']))?:$this->first($e,strtolower($a['upn']));
        return ['directory_key'=>sha1($dn ?: (string)$username),'username'=>$username,'user_principal_name'=>$this->first($e,strtolower($a['upn'])),'display_name'=>$this->first($e,strtolower($a['name']))?:$username,'email'=>$this->first($e,strtolower($a['email'])),'employee_id'=>$this->first($e,strtolower($a['employee_id'])),'department'=>$this->first($e,strtolower($a['department'])),'title'=>$this->first($e,strtolower($a['title'])),'phone'=>$this->first($e,strtolower($a['phone'])),'mobile'=>$this->first($e,strtolower($a['mobile'])),'manager_dn'=>$this->first($e,strtolower($a['manager'])),'distinguished_name'=>$dn,'groups'=>$groups,'profile'=>$this->profileFromGroups($groups),'is_active'=>$this->isActive($e)];
    }

    private function profileFromGroups(array $groups): string
    {
        $names=array_map(fn($dn)=>mb_strtoupper(preg_replace('/^CN=([^,]+).*$/i','$1',$dn)),$groups);
        foreach(['admin_groups'=>'Admin','ti_groups'=>'TI','rh_groups'=>'RH'] as $key=>$profile) foreach(config('identity.profiles.'.$key,[]) as $g) if(in_array(mb_strtoupper($g),$names,true)) return $profile;
        return config('identity.profiles.default','Operador');
    }

    private function isActive(array $e): bool { $uac=(int)($this->first($e,'useraccountcontrol')?:0); return ($uac & 2)!==2; }
    private function requestedAttributes(): array { return array_values(array_unique(array_merge(array_values(config('identity.ldap.attributes')),['distinguishedName']))); }
    private function first(array $e,string $k): ?string { $k=strtolower($k); return isset($e[$k][0])?trim((string)$e[$k][0]):null; }
    private function values(array $e,string $k): array { if(!isset($e[$k])||!is_array($e[$k])) return []; $v=$e[$k]; unset($v['count']); return array_values(array_map('strval',$v)); }

    private function connectAndBind()
    {
        $this->settings->apply(); if(!extension_loaded('ldap')) throw new RuntimeException('Extensão PHP LDAP não instalada.'); $c=config('identity.ldap');
        if(!$c['host']||!$c['bind_dn']) throw new RuntimeException('Servidor ou usuário de bind LDAP não configurado.');
        $ldap=@ldap_connect(($c['ssl']?'ldaps://':'ldap://').$c['host'],$c['port']); if(!$ldap) throw new RuntimeException('Não foi possível abrir conexão LDAP.');
        ldap_set_option($ldap,LDAP_OPT_PROTOCOL_VERSION,3); ldap_set_option($ldap,LDAP_OPT_REFERRALS,0); ldap_set_option($ldap,LDAP_OPT_NETWORK_TIMEOUT,$c['timeout']);
        if($c['start_tls']&&!$c['ssl']&&!@ldap_start_tls($ldap)) throw new RuntimeException('Falha ao iniciar StartTLS.');
        if(!@ldap_bind($ldap,$c['bind_dn'],$c['bind_password'])) throw new RuntimeException('Falha no bind da conta de serviço LDAP.');
        return $ldap;
    }
}
