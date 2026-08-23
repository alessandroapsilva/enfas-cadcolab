<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identity & Directory | CADCOLAB ENFAS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body{background:#0f172a;color:#e2e8f0;font-family:Segoe UI,Arial,sans-serif}.card{background:#111827;border:1px solid #273244;color:#e2e8f0}.muted{color:#94a3b8}.metric{font-size:28px;font-weight:800}.badge-soft{background:#0b3b55;color:#7dd3fc}.table{--bs-table-bg:transparent;--bs-table-color:#e2e8f0;--bs-table-border-color:#273244}.form-label{color:#94a3b8}.code{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12px;color:#bae6fd}.btn-enfas{background:#00a2e8;color:#fff;border:0}.btn-enfas:hover{background:#0284c7;color:#fff}.form-control,.form-select{background:#0f172a;border-color:#334155;color:#e2e8f0}.form-control:focus,.form-select:focus{background:#0f172a;color:#fff;border-color:#00a2e8;box-shadow:none}.form-check-input{background-color:#0f172a;border-color:#64748b}.section-title{font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#7dd3fc;font-weight:800;margin-bottom:14px}
    </style>
</head>
<body>
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="text-info small fw-bold text-uppercase">CADCOLAB ENFAS IAM</div>
            <h1 class="h3 mb-1">Identity & Directory</h1>
            <div class="muted">LDAP/Active Directory como fonte central de identidade e autenticação.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="/dashboard" class="btn btn-outline-light"><i class="fa-solid fa-arrow-left me-2"></i>Dashboard</a>
            <form method="POST" action="/identity-directory/test">@csrf<button class="btn btn-outline-info"><i class="fa-solid fa-plug me-2"></i>Testar conexão</button></form>
            <form method="POST" action="/identity-directory/sync">@csrf<button class="btn btn-enfas"><i class="fa-solid fa-rotate me-2"></i>Sincronizar agora</button></form>
        </div>
    </div>

    @if(session('swal'))<script>Swal.fire({icon:'success',title:'Concluído',text:@json(session('swal')),background:'#111827',color:'#e2e8f0'});</script>@endif
    @if(session('swal_error'))<script>Swal.fire({icon:'error',title:'Falha',text:@json(session('swal_error')),background:'#111827',color:'#e2e8f0'});</script>@endif
    @if($errors->any())<script>Swal.fire({icon:'warning',title:'Revise os campos',html:@json(implode('<br>', $errors->all())),background:'#111827',color:'#e2e8f0'});</script>@endif

    <div class="row g-3 mb-4">
        <div class="col-md"><div class="card p-3"><div class="muted small">Diretório</div><div class="metric">{{ $enabled ? 'Ativo' : 'Desativado' }}</div></div></div>
        <div class="col-md"><div class="card p-3"><div class="muted small">Conexão</div><div class="metric {{ $connection['success'] ? 'text-success':'text-danger' }}">{{ $connection['success'] ? 'Online':'Offline' }}</div></div></div>
        <div class="col-md"><div class="card p-3"><div class="muted small">Usuários</div><div class="metric">{{ $stats['users'] }}</div></div></div>
        <div class="col-md"><div class="card p-3"><div class="muted small">Ativos</div><div class="metric text-success">{{ $stats['active'] }}</div></div></div>
        <div class="col-md"><div class="card p-3"><div class="muted small">Grupos</div><div class="metric">{{ $stats['groups'] }}</div></div></div>
    </div>

    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div><h2 class="h5 mb-1">Configuração LDAP / Active Directory</h2><div class="muted small">Tudo pode ser administrado por aqui. A senha da conta de bind é criptografada com a APP_KEY do Laravel.</div></div>
            <div class="small {{ $connection['success'] ? 'text-success':'text-warning' }}"><i class="fa-solid {{ $connection['success'] ? 'fa-circle-check':'fa-triangle-exclamation' }} me-2"></i>{{ $connection['message'] }}</div>
        </div>

        <form method="POST" action="/identity-directory/settings" autocomplete="off">
            @csrf
            <div class="section-title">Estado e segurança</div>
            <div class="row g-3 mb-4">
                <div class="col-lg-4"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="enabled" value="1" id="enabled" {{ old('enabled',$enabled) ? 'checked':'' }}><label class="form-check-label" for="enabled">Habilitar autenticação LDAP</label></div></div>
                <div class="col-lg-4"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="sync_on_login" value="1" id="sync_on_login" {{ old('sync_on_login',$syncOnLogin) ? 'checked':'' }}><label class="form-check-label" for="sync_on_login">Sincronizar usuário ao entrar</label></div></div>
                <div class="col-lg-4"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="local_fallback" value="1" id="local_fallback" {{ old('local_fallback',$localFallback) ? 'checked':'' }}><label class="form-check-label" for="local_fallback">Permitir login administrativo local</label></div></div>
            </div>

            <div class="section-title">Servidor e transporte</div>
            <div class="row g-3 mb-4">
                <div class="col-lg-5"><label class="form-label">Servidor LDAP / Controlador de Domínio</label><input class="form-control" name="host" value="{{ old('host',$ldap['host']) }}" placeholder="dc01.enfas.local" required></div>
                <div class="col-lg-2"><label class="form-label">Porta</label><input class="form-control" type="number" name="port" value="{{ old('port',$ldap['port']) }}" required></div>
                <div class="col-lg-2"><label class="form-label">Timeout (s)</label><input class="form-control" type="number" name="timeout" value="{{ old('timeout',$ldap['timeout']) }}" min="1" max="60" required></div>
                <div class="col-lg-3 pt-4"><div class="form-check form-switch d-inline-block me-4"><input class="form-check-input" type="checkbox" name="ssl" value="1" id="ssl" {{ old('ssl',$ldap['ssl']) ? 'checked':'' }}><label for="ssl">LDAPS</label></div><div class="form-check form-switch d-inline-block"><input class="form-check-input" type="checkbox" name="start_tls" value="1" id="start_tls" {{ old('start_tls',$ldap['start_tls']) ? 'checked':'' }}><label for="start_tls">StartTLS</label></div></div>
                <div class="col-lg-12"><div class="muted small">Use normalmente 636 para LDAPS ou 389 para LDAP/StartTLS. Em produção, prefira conexão criptografada e certificado confiável.</div></div>
            </div>

            <div class="section-title">Árvore do diretório</div>
            <div class="row g-3 mb-4">
                <div class="col-lg-4"><label class="form-label">Base DN</label><input class="form-control" name="base_dn" value="{{ old('base_dn',$ldap['base_dn']) }}" placeholder="DC=enfas,DC=local" required></div>
                <div class="col-lg-4"><label class="form-label">OU / DN de usuários</label><input class="form-control" name="users_dn" value="{{ old('users_dn',$ldap['users_dn']) }}" placeholder="OU=Usuarios,DC=enfas,DC=local" required></div>
                <div class="col-lg-4"><label class="form-label">OU / DN de grupos</label><input class="form-control" name="groups_dn" value="{{ old('groups_dn',$ldap['groups_dn']) }}" placeholder="OU=Grupos,DC=enfas,DC=local" required></div>
            </div>

            <div class="section-title">Conta de serviço</div>
            <div class="row g-3 mb-4">
                <div class="col-lg-6"><label class="form-label">Usuário / Bind DN</label><input class="form-control" name="bind_dn" value="{{ old('bind_dn',$ldap['bind_dn']) }}" placeholder="CN=svc-cadcolab,OU=Servicos,DC=enfas,DC=local" required></div>
                <div class="col-lg-6"><label class="form-label">Senha da conta de bind</label><input class="form-control" type="password" name="bind_password" placeholder="{{ $bindPasswordConfigured ? 'Já configurada — deixe vazio para manter' : 'Informe a senha' }}"><div class="muted small mt-1">Nunca é exibida novamente e fica criptografada no banco.</div></div>
            </div>

            <div class="section-title">Login e filtros</div>
            <div class="row g-3 mb-4">
                <div class="col-lg-4"><label class="form-label">Sufixo da conta</label><input class="form-control" name="account_suffix" value="{{ old('account_suffix',$ldap['account_suffix']) }}" placeholder="@enfas.local"></div>
                <div class="col-lg-4"><label class="form-label">Atributo de login</label><input class="form-control" name="username_attribute" value="{{ old('username_attribute',$ldap['username_attribute']) }}" required></div>
                <div class="col-lg-4"><label class="form-label">Filtro de usuários</label><input class="form-control" name="user_filter" value="{{ old('user_filter',$ldap['user_filter']) }}" required></div>
                <div class="col-lg-6"><label class="form-label">Filtro de grupos</label><input class="form-control" name="group_filter" value="{{ old('group_filter',$ldap['group_filter']) }}" required></div>
            </div>

            <div class="section-title">Mapeamento de atributos</div>
            @php($a=$ldap['attributes'])
            <div class="row g-3 mb-4">
                <div class="col-md-3"><label class="form-label">Usuário</label><input class="form-control" name="attr_username" value="{{ old('attr_username',$a['username']) }}" required></div>
                <div class="col-md-3"><label class="form-label">UPN</label><input class="form-control" name="attr_upn" value="{{ old('attr_upn',$a['upn']) }}" required></div>
                <div class="col-md-3"><label class="form-label">Nome</label><input class="form-control" name="attr_name" value="{{ old('attr_name',$a['name']) }}" required></div>
                <div class="col-md-3"><label class="form-label">E-mail</label><input class="form-control" name="attr_email" value="{{ old('attr_email',$a['email']) }}" required></div>
                <div class="col-md-3"><label class="form-label">Matrícula</label><input class="form-control" name="attr_employee_id" value="{{ old('attr_employee_id',$a['employee_id']) }}" required></div>
                <div class="col-md-3"><label class="form-label">Setor</label><input class="form-control" name="attr_department" value="{{ old('attr_department',$a['department']) }}" required></div>
                <div class="col-md-3"><label class="form-label">Cargo</label><input class="form-control" name="attr_title" value="{{ old('attr_title',$a['title']) }}" required></div>
                <div class="col-md-3"><label class="form-label">Telefone</label><input class="form-control" name="attr_phone" value="{{ old('attr_phone',$a['phone']) }}" required></div>
                <div class="col-md-3"><label class="form-label">Celular</label><input class="form-control" name="attr_mobile" value="{{ old('attr_mobile',$a['mobile']) }}" required></div>
                <div class="col-md-3"><label class="form-label">Gestor</label><input class="form-control" name="attr_manager" value="{{ old('attr_manager',$a['manager']) }}" required></div>
                <div class="col-md-3"><label class="form-label">Grupos</label><input class="form-control" name="attr_groups" value="{{ old('attr_groups',$a['groups']) }}" required></div>
                <div class="col-md-3"><label class="form-label">GUID</label><input class="form-control" name="attr_guid" value="{{ old('attr_guid',$a['guid']) }}" required></div>
                <div class="col-md-3"><label class="form-label">Status/UAC</label><input class="form-control" name="attr_account_control" value="{{ old('attr_account_control',$a['account_control']) }}" required></div>
            </div>

            <div class="section-title">Grupos → perfil CADCOLAB</div>
            <div class="row g-3 mb-4">
                <div class="col-lg-3"><label class="form-label">Administradores</label><input class="form-control" name="admin_groups" value="{{ old('admin_groups',implode(',',$profiles['admin_groups'])) }}" placeholder="CADCOLAB-ADMIN"></div>
                <div class="col-lg-3"><label class="form-label">TI</label><input class="form-control" name="ti_groups" value="{{ old('ti_groups',implode(',',$profiles['ti_groups'])) }}" placeholder="CADCOLAB-TI"></div>
                <div class="col-lg-3"><label class="form-label">RH</label><input class="form-control" name="rh_groups" value="{{ old('rh_groups',implode(',',$profiles['rh_groups'])) }}" placeholder="CADCOLAB-RH"></div>
                <div class="col-lg-3"><label class="form-label">Perfil padrão</label><select class="form-select" name="default_profile">@foreach(['Operador','RH','TI','Admin'] as $p)<option value="{{ $p }}" {{ old('default_profile',$profiles['default'])===$p?'selected':'' }}>{{ $p }}</option>@endforeach</select></div>
                <div class="col-12"><div class="muted small">Separe vários grupos por vírgula. O sistema lê o <code>memberOf</code> do AD e aplica o perfil automaticamente.</div></div>
            </div>

            <div class="d-flex justify-content-end gap-2"><button type="submit" class="btn btn-enfas px-4"><i class="fa-solid fa-floppy-disk me-2"></i>Salvar configuração</button></div>
        </form>
    </div>

    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 m-0">Usuários sincronizados</h2><span class="muted small">Última sincronização: {{ $stats['last_sync'] ?: 'ainda não realizada' }}</span></div>
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Usuário</th><th>Nome</th><th>E-mail</th><th>Setor</th><th>Cargo</th><th>Perfil</th><th>Status</th></tr></thead><tbody>
        @forelse($users as $u)<tr><td class="code">{{ $u->username }}</td><td>{{ $u->display_name }}</td><td>{{ $u->email ?: '—' }}</td><td>{{ $u->department ?: '—' }}</td><td>{{ $u->title ?: '—' }}</td><td><span class="badge badge-soft">{{ $u->profile }}</span></td><td><span class="badge {{ $u->is_active ? 'bg-success':'bg-danger' }}">{{ $u->is_active ? 'Ativo':'Bloqueado' }}</span></td></tr>
        @empty<tr><td colspan="7" class="text-center muted py-4">Nenhum usuário sincronizado.</td></tr>@endforelse
        </tbody></table></div>
    </div>

    <div class="card p-4"><h2 class="h5 mb-3">Grupos do diretório</h2><div class="row g-2">@forelse($groups as $g)<div class="col-md-4"><div class="border border-secondary rounded p-3"><div class="fw-semibold">{{ $g->name }}</div><div class="code text-truncate" title="{{ $g->distinguished_name }}">{{ $g->distinguished_name }}</div></div></div>@empty<div class="muted">Nenhum grupo sincronizado.</div>@endforelse</div></div>
</div>
</body>
</html>
