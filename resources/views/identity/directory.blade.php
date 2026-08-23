<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Identidade e Diretório | CADCOLAB ENFAS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root{--primary:#00a2e8;--sidebar:#151720;--body:#1c1e29;--card:#151720;--border:#2a2d3d;--text:#f8fafc;--muted:#94a3b8}
        body{margin:0;background:var(--body);color:var(--text);font-family:'Segoe UI',Arial,sans-serif;min-height:100vh;display:flex}
        .sidebar{width:260px;background:var(--sidebar);position:fixed;inset:0 auto 0 0;border-right:1px solid var(--border);padding:18px 14px;overflow:auto}
        .brand{display:block;padding:12px 10px 24px;text-decoration:none}.brand b{font-size:20px;color:#fff}.brand span{font-size:20px;color:var(--primary);font-weight:900}.brand small{display:block;color:#8892b0;letter-spacing:2px;font-size:9px}
        .nav-title{font-size:11px;color:#8892b0;text-transform:uppercase;font-weight:700;margin:22px 12px 8px}.nav-item{display:flex;gap:12px;align-items:center;padding:11px 14px;margin:4px 0;border-radius:8px;color:#cbd5e1;text-decoration:none;font-size:14px}.nav-item:hover,.nav-item.active{background:rgba(0,162,232,.12);color:var(--primary)}
        .main{margin-left:260px;width:calc(100% - 260px)}.topbar{height:70px;background:var(--card);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 30px}.content{padding:30px 40px}
        .cardx{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:22px;box-shadow:0 4px 20px rgba(0,0,0,.08)}.metric{font-size:30px;font-weight:800}.muted{color:var(--muted)}.code{font-family:ui-monospace,monospace;color:#7dd3fc;font-size:12px}
        .form-control,.form-select{background:#111827!important;color:#e2e8f0!important;border:1px solid #334155!important}.form-control:focus{box-shadow:none!important;border-color:var(--primary)!important}.form-check-input:checked{background-color:var(--primary);border-color:var(--primary)}
        .btn-primary{background:var(--primary);border-color:var(--primary)}.table{--bs-table-bg:transparent;--bs-table-color:#e2e8f0;--bs-table-border-color:#2a2d3d}.status-dot{width:10px;height:10px;border-radius:50%;display:inline-block;margin-right:7px}.hub-card{height:100%;text-decoration:none;color:inherit;display:block}.hub-card:hover{border-color:var(--primary);transform:translateY(-2px);transition:.2s}.hub-icon{width:44px;height:44px;border-radius:10px;background:rgba(0,162,232,.12);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:20px}
        @media(max-width:900px){.sidebar{display:none}.main{margin-left:0;width:100%}.content{padding:20px}}
    </style>
</head>
<body>
@if(session('swal'))<script>Swal.fire({icon:'success',title:'Concluído',text:@json(session('swal')),background:'#151720',color:'#f8fafc',confirmButtonColor:'#00a2e8'});</script>@endif
@if(session('swal_error'))<script>Swal.fire({icon:'error',title:'Atenção',text:@json(session('swal_error')),background:'#151720',color:'#f8fafc',confirmButtonColor:'#ef4444'});</script>@endif

<aside class="sidebar">
    <a href="/dashboard?p=dashboard" class="brand"><b>CADCOLAB</b><span> ENFAS</span><small>GESTÃO DE IDENTIDADES</small></a>
    <a href="/dashboard?p=dashboard" class="nav-item"><i class="fa-solid fa-house"></i> Início</a>
    <div class="nav-title">Governança</div>
    <a href="/dashboard?p=colaboradores" class="nav-item"><i class="fa-solid fa-users"></i> Colaboradores</a>
    <a href="/dashboard?p=grupos" class="nav-item"><i class="fa-solid fa-user-lock"></i> Perfis de Acesso</a>
    <a href="/dashboard?p=robos" class="nav-item"><i class="fa-solid fa-robot"></i> Automação de Jornada</a>
    <div class="nav-title">Identidade</div>
    <a href="/dashboard?p=identity-directory" class="nav-item active"><i class="fa-solid fa-network-wired"></i> Identidade e Diretório</a>
    <a href="/dashboard?p=sistemas" class="nav-item"><i class="fa-solid fa-desktop"></i> Aplicações Web SSO</a>
    <a href="/dashboard?p=usuarios" class="nav-item"><i class="fa-solid fa-user-shield"></i> Administradores</a>
    <div class="nav-title">Operação</div>
    <a href="/dashboard?p=status" class="nav-item"><i class="fa-solid fa-server"></i> Status das Integrações</a>
    <a href="/dashboard?p=auditoria" class="nav-item"><i class="fa-solid fa-file-shield"></i> Auditoria</a>
    <a href="/dashboard?p=configuracoes" class="nav-item"><i class="fa-solid fa-sliders"></i> Configurações</a>
</aside>

<main class="main">
    <div class="topbar">
        <div><div class="small text-info fw-bold text-uppercase">Identity & Access Management</div><h5 class="m-0 fw-bold">Identidade e Diretório</h5></div>
        <div class="d-flex align-items-center gap-3"><span class="small muted">{{ session('admin_perfil') }} · {{ session('admin_nome') }}</span><a class="btn btn-sm btn-outline-light" href="/logout">Sair</a></div>
    </div>

    <div class="content">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div><h2 class="fw-bold mb-1">Central de Identidade Corporativa</h2><div class="muted">LDAP/Active Directory, provisionamento, perfis e integrações em um único ponto.</div></div>
            <div class="d-flex gap-2"><form method="POST" action="/identity-directory/test">@csrf<button class="btn btn-outline-info"><i class="fa-solid fa-plug me-2"></i>Testar conexão</button></form><form method="POST" action="/identity-directory/sync">@csrf<button class="btn btn-primary"><i class="fa-solid fa-rotate me-2"></i>Sincronizar agora</button></form></div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-2"><div class="cardx"><div class="muted small">Diretório</div><div class="metric {{ $enabled?'text-success':'text-warning' }}">{{ $enabled?'Ativo':'Off' }}</div></div></div>
            <div class="col-md-2"><div class="cardx"><div class="muted small">Usuários</div><div class="metric">{{ $stats['users'] }}</div></div></div>
            <div class="col-md-2"><div class="cardx"><div class="muted small">Ativos</div><div class="metric text-success">{{ $stats['active'] }}</div></div></div>
            <div class="col-md-2"><div class="cardx"><div class="muted small">Bloqueados</div><div class="metric text-danger">{{ $stats['inactive'] }}</div></div></div>
            <div class="col-md-2"><div class="cardx"><div class="muted small">Grupos</div><div class="metric">{{ $stats['groups'] }}</div></div></div>
            <div class="col-md-2"><div class="cardx"><div class="muted small">Setores</div><div class="metric">{{ $stats['departments'] }}</div></div></div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="cardx hub-card"><div class="d-flex gap-3"><div class="hub-icon"><i class="fa-solid fa-user-plus"></i></div><div><b>Onboarding</b><div class="small muted">Criar identidade, aplicar grupos e provisionar serviços.</div></div></div></div></div>
            <div class="col-md-3"><div class="cardx hub-card"><div class="d-flex gap-3"><div class="hub-icon"><i class="fa-solid fa-user-slash"></i></div><div><b>Offboarding</b><div class="small muted">Bloquear conta, revogar acessos e registrar auditoria.</div></div></div></div></div>
            <div class="col-md-3"><div class="cardx hub-card"><div class="d-flex gap-3"><div class="hub-icon"><i class="fa-solid fa-shield-halved"></i></div><div><b>Políticas</b><div class="small muted">Perfis e grupos por função, setor e responsabilidade.</div></div></div></div></div>
            <div class="col-md-3"><div class="cardx hub-card"><div class="d-flex gap-3"><div class="hub-icon"><i class="fa-solid fa-key"></i></div><div><b>Identidade Única</b><div class="small muted">Mesma credencial corporativa para os sistemas integrados.</div></div></div></div></div>
        </div>

        <div class="cardx mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3"><div><h5 class="mb-1">Saúde das integrações</h5><div class="small muted">Visão rápida dos provedores usados pelo CADCOLAB.</div></div><span class="small muted">Última sincronização: {{ $stats['last_sync'] ?: 'ainda não realizada' }}</span></div>
            <div class="row g-3">
                @foreach(['ldap'=>'LDAP / Active Directory','m365'=>'Microsoft 365','google'=>'Google Workspace','whatsapp'=>'WhatsApp'] as $key=>$label)
                    <div class="col-md-3"><div class="border rounded p-3" style="border-color:var(--border)!important"><span class="status-dot" style="background:{{ $integrations[$key]?'#10b981':'#f59e0b' }}"></span><strong>{{ $label }}</strong><div class="small muted mt-1">{{ $integrations[$key]?'Configurado/online':'Pendente de configuração' }}</div></div></div>
                @endforeach
            </div>
        </div>

        <div class="cardx mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3"><div><h5 class="mb-1">Configuração LDAP / Active Directory</h5><div class="small muted">A senha de bind é criptografada com a APP_KEY e nunca é exibida novamente.</div></div><span class="badge {{ $connection['success']?'bg-success':'bg-warning text-dark' }}">{{ $connection['success']?'Conectado':'Não validado' }}</span></div>
            <form method="POST" action="/identity-directory/settings">@csrf
                <div class="row g-3">
                    <div class="col-md-5"><label class="form-label">Servidor LDAP/AD</label><input class="form-control" name="host" value="{{ $ldap['host'] }}" placeholder="dc01.empresa.local" required></div>
                    <div class="col-md-2"><label class="form-label">Porta</label><input class="form-control" type="number" name="port" value="{{ $ldap['port'] }}" required></div>
                    <div class="col-md-2"><label class="form-label">Timeout</label><input class="form-control" type="number" name="timeout" value="{{ $ldap['timeout'] }}" required></div>
                    <div class="col-md-3 d-flex align-items-end gap-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="ssl" value="1" {{ $ldap['ssl']?'checked':'' }}><label class="form-check-label">LDAPS</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="start_tls" value="1" {{ $ldap['start_tls']?'checked':'' }}><label class="form-check-label">StartTLS</label></div></div>
                    <div class="col-md-4"><label class="form-label">Base DN</label><input class="form-control" name="base_dn" value="{{ $ldap['base_dn'] }}" placeholder="DC=enfas,DC=local" required></div>
                    <div class="col-md-4"><label class="form-label">Users DN / OU</label><input class="form-control" name="users_dn" value="{{ $ldap['users_dn'] }}" placeholder="OU=Usuarios,DC=enfas,DC=local" required></div>
                    <div class="col-md-4"><label class="form-label">Groups DN / OU</label><input class="form-control" name="groups_dn" value="{{ $ldap['groups_dn'] }}" placeholder="OU=Grupos,DC=enfas,DC=local" required></div>
                    <div class="col-md-5"><label class="form-label">Conta de serviço / Bind DN</label><input class="form-control" name="bind_dn" value="{{ $ldap['bind_dn'] }}" required></div>
                    <div class="col-md-4"><label class="form-label">Senha de bind {{ $bindPasswordConfigured?'· configurada':'' }}</label><input class="form-control" type="password" name="bind_password" placeholder="Deixe vazio para manter"></div>
                    <div class="col-md-3"><label class="form-label">Sufixo de login</label><input class="form-control" name="account_suffix" value="{{ $ldap['account_suffix'] }}" placeholder="@enfas.local"></div>
                    <div class="col-md-4"><label class="form-label">Grupo Admin</label><input class="form-control" name="admin_groups" value="{{ implode(',', $profiles['admin_groups']) }}"></div>
                    <div class="col-md-4"><label class="form-label">Grupo TI</label><input class="form-control" name="ti_groups" value="{{ implode(',', $profiles['ti_groups']) }}"></div>
                    <div class="col-md-4"><label class="form-label">Grupo RH</label><input class="form-control" name="rh_groups" value="{{ implode(',', $profiles['rh_groups']) }}"></div>
                    <div class="col-12"><div class="d-flex flex-wrap gap-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="enabled" value="1" {{ $enabled?'checked':'' }}><label class="form-check-label">Ativar autenticação LDAP</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="sync_on_login" value="1" {{ $syncOnLogin?'checked':'' }}><label class="form-check-label">Sincronizar ao entrar</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="local_fallback" value="1" {{ $localFallback?'checked':'' }}><label class="form-check-label">Permitir contingência local</label></div></div></div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-4"><div class="small {{ $connection['success']?'text-success':'text-warning' }}"><i class="fa-solid {{ $connection['success']?'fa-circle-check':'fa-triangle-exclamation' }} me-2"></i>{{ $connection['message'] }}</div><button class="btn btn-primary px-4"><i class="fa-solid fa-floppy-disk me-2"></i>Salvar configuração</button></div>
            </form>
        </div>

        <div class="cardx mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="m-0">Usuários sincronizados</h5><span class="small muted">{{ $stats['admins'] }} contas administrativas por grupo</span></div>
            <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Usuário</th><th>Nome</th><th>E-mail</th><th>Setor</th><th>Cargo</th><th>Perfil</th><th>Status</th></tr></thead><tbody>
                @forelse($users as $u)<tr><td class="code">{{ $u->username }}</td><td>{{ $u->display_name ?: '—' }}</td><td>{{ $u->email ?: '—' }}</td><td>{{ $u->department ?: '—' }}</td><td>{{ $u->title ?: '—' }}</td><td><span class="badge bg-info-subtle text-info">{{ $u->profile }}</span></td><td><span class="badge {{ $u->is_active?'bg-success':'bg-danger' }}">{{ $u->is_active?'Ativo':'Bloqueado' }}</span></td></tr>
                @empty<tr><td colspan="7" class="text-center muted py-4">Nenhum usuário sincronizado.</td></tr>@endforelse
            </tbody></table></div>
        </div>

        <div class="cardx"><h5>Grupos do diretório</h5><div class="row g-2 mt-1">@forelse($groups as $g)<div class="col-md-4"><div class="border rounded p-3" style="border-color:var(--border)!important"><strong>{{ $g->name }}</strong><div class="code text-truncate mt-1" title="{{ $g->distinguished_name }}">{{ $g->distinguished_name }}</div></div></div>@empty<div class="muted">Nenhum grupo sincronizado.</div>@endforelse</div></div>
    </div>
</main>
</body>
</html>
