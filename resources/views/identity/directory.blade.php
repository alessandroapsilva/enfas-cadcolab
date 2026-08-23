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
        body{background:#0f172a;color:#e2e8f0;font-family:Segoe UI,Arial,sans-serif}.card{background:#111827;border:1px solid #273244;color:#e2e8f0}.muted{color:#94a3b8}.metric{font-size:28px;font-weight:800}.badge-soft{background:#0b3b55;color:#7dd3fc}.table{--bs-table-bg:transparent;--bs-table-color:#e2e8f0;--bs-table-border-color:#273244}.form-label{color:#94a3b8}.code{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12px;color:#bae6fd}.btn-enfas{background:#00a2e8;color:#fff;border:0}.btn-enfas:hover{background:#0284c7;color:#fff}
    </style>
</head>
<body>
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="text-info small fw-bold text-uppercase">CADCOLAB ENFAS IAM</div>
            <h1 class="h3 mb-1">Identity & Directory</h1>
            <div class="muted">LDAP/Active Directory como fonte central de identidade.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="/dashboard" class="btn btn-outline-light"><i class="fa-solid fa-arrow-left me-2"></i>Dashboard</a>
            <form method="POST" action="/identity-directory/test">@csrf<button class="btn btn-outline-info"><i class="fa-solid fa-plug me-2"></i>Testar LDAP</button></form>
            <form method="POST" action="/identity-directory/sync">@csrf<button class="btn btn-enfas"><i class="fa-solid fa-rotate me-2"></i>Sincronizar</button></form>
        </div>
    </div>

    @if(session('swal'))<script>Swal.fire({icon:'success',title:'Concluído',text:@json(session('swal')),background:'#111827',color:'#e2e8f0'});</script>@endif
    @if(session('swal_error'))<script>Swal.fire({icon:'error',title:'Falha',text:@json(session('swal_error')),background:'#111827',color:'#e2e8f0'});</script>@endif

    <div class="row g-3 mb-4">
        <div class="col-md"><div class="card p-3"><div class="muted small">Diretório</div><div class="metric">{{ $enabled ? 'Ativo' : 'Desativado' }}</div></div></div>
        <div class="col-md"><div class="card p-3"><div class="muted small">Conexão</div><div class="metric {{ $connection['success'] ? 'text-success':'text-danger' }}">{{ $connection['success'] ? 'Online':'Offline' }}</div></div></div>
        <div class="col-md"><div class="card p-3"><div class="muted small">Usuários</div><div class="metric">{{ $stats['users'] }}</div></div></div>
        <div class="col-md"><div class="card p-3"><div class="muted small">Ativos</div><div class="metric text-success">{{ $stats['active'] }}</div></div></div>
        <div class="col-md"><div class="card p-3"><div class="muted small">Grupos</div><div class="metric">{{ $stats['groups'] }}</div></div></div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card p-4 h-100">
                <h2 class="h5 mb-3">Configuração efetiva</h2>
                <div class="row g-3">
                    <div class="col-md-6"><div class="form-label">Servidor</div><div class="code">{{ $ldap['ssl'] ? 'ldaps' : 'ldap' }}://{{ $ldap['host'] }}:{{ $ldap['port'] }}</div></div>
                    <div class="col-md-6"><div class="form-label">Base DN</div><div class="code">{{ $ldap['base_dn'] ?: 'Não configurado' }}</div></div>
                    <div class="col-md-6"><div class="form-label">Users DN</div><div class="code">{{ $ldap['users_dn'] ?: 'Não configurado' }}</div></div>
                    <div class="col-md-6"><div class="form-label">Groups DN</div><div class="code">{{ $ldap['groups_dn'] ?: 'Não configurado' }}</div></div>
                </div>
                <hr class="border-secondary">
                <div class="small {{ $connection['success'] ? 'text-success':'text-danger' }}"><i class="fa-solid {{ $connection['success'] ? 'fa-circle-check':'fa-triangle-exclamation' }} me-2"></i>{{ $connection['message'] }}</div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card p-4 h-100">
                <h2 class="h5 mb-3">Mapeamento de perfis</h2>
                <div class="mb-3"><span class="badge badge-soft me-2">Admin</span>{{ implode(', ', $profiles['admin_groups']) ?: 'Nenhum grupo' }}</div>
                <div class="mb-3"><span class="badge badge-soft me-2">TI</span>{{ implode(', ', $profiles['ti_groups']) ?: 'Nenhum grupo' }}</div>
                <div class="mb-3"><span class="badge badge-soft me-2">RH</span>{{ implode(', ', $profiles['rh_groups']) ?: 'Nenhum grupo' }}</div>
                <div><span class="badge bg-secondary me-2">Padrão</span>{{ $profiles['default'] }}</div>
            </div>
        </div>
    </div>

    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 m-0">Usuários sincronizados</h2><span class="muted small">Última sincronização: {{ $stats['last_sync'] ?: 'ainda não realizada' }}</span></div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Usuário</th><th>Nome</th><th>E-mail</th><th>Setor</th><th>Cargo</th><th>Perfil</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($users as $u)
                    <tr>
                        <td class="code">{{ $u->username }}</td><td>{{ $u->display_name }}</td><td>{{ $u->email ?: '—' }}</td><td>{{ $u->department ?: '—' }}</td><td>{{ $u->title ?: '—' }}</td>
                        <td><span class="badge badge-soft">{{ $u->profile }}</span></td><td><span class="badge {{ $u->is_active ? 'bg-success':'bg-danger' }}">{{ $u->is_active ? 'Ativo':'Bloqueado' }}</span></td>
                    </tr>
                @empty<tr><td colspan="7" class="text-center muted py-4">Nenhum usuário sincronizado.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-4">
        <h2 class="h5 mb-3">Grupos do diretório</h2>
        <div class="row g-2">
            @forelse($groups as $g)<div class="col-md-4"><div class="border border-secondary rounded p-3"><div class="fw-semibold">{{ $g->name }}</div><div class="code text-truncate" title="{{ $g->distinguished_name }}">{{ $g->distinguished_name }}</div></div></div>
            @empty<div class="muted">Nenhum grupo sincronizado.</div>@endforelse
        </div>
    </div>
</div>
</body>
</html>
