<?php

namespace App\Http\Controllers;

use App\Services\IdentitySettingsService;
use App\Services\LdapDirectoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IdentityDirectoryController extends Controller
{
    public function __construct(private readonly LdapDirectoryService $directory, private readonly IdentitySettingsService $settings) {}
    private function authorizeDirectory(): void { abort_unless(session('admin_logado'),401); abort_unless(in_array(session('admin_perfil'),['TI','Admin'],true),403); }

    public function index() {
        $this->authorizeDirectory(); $form=$this->settings->allForForm();
        $connection=['success'=>false,'message'=>'Configure e habilite o LDAP para testar a conexão.'];
        if($form['enabled']&&!empty($form['ldap']['host'])) $connection=$this->directory->testConnection();
        $users=Schema::hasTable('identity_directory_users')?DB::table('identity_directory_users')->orderBy('display_name')->limit(500)->get():collect();
        $groups=Schema::hasTable('identity_directory_groups')?DB::table('identity_directory_groups')->orderBy('name')->limit(500)->get():collect();
        return view('identity.directory',['connection'=>$connection,'users'=>$users,'groups'=>$groups,'enabled'=>$form['enabled'],'ldap'=>$form['ldap'],'profiles'=>$form['profiles'],'syncOnLogin'=>$form['sync_on_login'],'localFallback'=>$form['local_fallback'],'bindPasswordConfigured'=>$form['bind_password_configured']]);
    }

    public function saveSettings(Request $request) {
        $this->authorizeDirectory();
        $d=$request->validate(['host'=>'required|string|max:255','port'=>'required|integer|min:1|max:65535','timeout'=>'required|integer|min:1|max:60','base_dn'=>'required|string|max:1000','users_dn'=>'required|string|max:1000','groups_dn'=>'required|string|max:1000','bind_dn'=>'required|string|max:1000','bind_password'=>'nullable|string|max:2000','account_suffix'=>'nullable|string|max:255','admin_groups'=>'nullable|string|max:1000','ti_groups'=>'nullable|string|max:1000','rh_groups'=>'nullable|string|max:1000']);
        if($request->boolean('ssl')&&$request->boolean('start_tls')) return back()->withInput()->with('swal_error','Escolha LDAPS ou StartTLS, não ambos.');
        $this->settings->save(['identity.enabled'=>$request->boolean('enabled'),'identity.sync_on_login'=>$request->boolean('sync_on_login'),'identity.local_fallback'=>$request->boolean('local_fallback'),'ldap.host'=>$d['host'],'ldap.port'=>$d['port'],'ldap.ssl'=>$request->boolean('ssl'),'ldap.start_tls'=>$request->boolean('start_tls'),'ldap.timeout'=>$d['timeout'],'ldap.base_dn'=>$d['base_dn'],'ldap.users_dn'=>$d['users_dn'],'ldap.groups_dn'=>$d['groups_dn'],'ldap.bind_dn'=>$d['bind_dn'],'ldap.bind_password'=>$d['bind_password']??'','ldap.account_suffix'=>$d['account_suffix']??'','profiles.admin_groups'=>$d['admin_groups']??'','profiles.ti_groups'=>$d['ti_groups']??'','profiles.rh_groups'=>$d['rh_groups']??'']);
        return back()->with('swal','Configuração LDAP salva com segurança.');
    }

    public function test(){ $this->authorizeDirectory(); $r=$this->directory->testConnection(); return back()->with($r['success']?'swal':'swal_error',$r['message']); }
    public function sync(){ $this->authorizeDirectory(); try{$r=$this->directory->syncAll(); return back()->with('swal',"Diretório sincronizado: {$r['users']} usuários e {$r['groups']} grupos.");}catch(\Throwable $e){return back()->with('swal_error','Falha na sincronização: '.$e->getMessage());}}
}
