@extends('layouts.cadcolab-v5')
@section('content')
<div class="v5-grid">
    <div class="v5-card v5-kpi"><small>Colaboradores</small><strong>{{ $dados['tot'] ?? 0 }}</strong><span>Total cadastrado</span></div>
    <div class="v5-card v5-kpi"><small>Ativos</small><strong>{{ $dados['ati'] ?? 0 }}</strong><span>Identidades em operação</span></div>
    <div class="v5-card v5-kpi"><small>Pendentes</small><strong>{{ $dados['pen'] ?? 0 }}</strong><span>Aguardando conclusão</span></div>
    <div class="v5-card v5-kpi"><small>Inativos/Bloqueados</small><strong>{{ $dados['ina'] ?? 0 }}</strong><span>Fora de operação</span></div>
</div>
<div class="v5-section v5-card">
    <div class="v5-section-head"><div><h2>Central operacional</h2><small style="color:var(--v5-muted)">Acesso rápido aos principais domínios da plataforma.</small></div></div>
    <div class="v5-grid">
        @foreach($moduleGroups as $groupKey => $group)
            @if($groupKey !== 'overview')
                <a class="v5-card" style="text-decoration:none;color:inherit" href="/dashboard?p={{ urlencode(array_key_first($group['modules'])) }}"><div style="display:flex;align-items:center;gap:12px"><span style="width:42px;height:42px;border-radius:12px;display:grid;place-items:center;background:rgba(25,174,233,.1);color:#43c8ff"><i class="fa-solid {{ $group['icon'] ?? 'fa-folder' }}"></i></span><div><strong style="font-size:13px">{{ $group['label'] }}</strong><div style="color:var(--v5-muted);font-size:10px;margin-top:3px">{{ count($group['modules']) }} módulos</div></div></div></a>
            @endif
        @endforeach
    </div>
</div>
<div class="v5-section v5-card">
    <div class="v5-section-head"><h2>Atividade recente</h2><a href="/dashboard?p=auditoria" class="v5-badge" style="text-decoration:none">Ver auditoria</a></div>
    <div style="overflow:auto"><table class="v5-table"><thead><tr><th>Data</th><th>Operador</th><th>Ação</th><th>Detalhes</th></tr></thead><tbody>
        @forelse(array_slice($dados['lf'] ?? [],0,12) as $log)
            <tr><td>{{ $log['data_fmt'] ?? ($log['data_hora'] ?? '-') }}</td><td>{{ $log['usuario_admin'] ?? '-' }}</td><td><span class="v5-badge">{{ $log['acao'] ?? 'Evento' }}</span></td><td>{{ $log['detalhes'] ?? '-' }}</td></tr>
        @empty<tr><td colspan="4" style="color:var(--v5-muted)">Nenhum evento recente.</td></tr>@endforelse
    </tbody></table></div>
</div>
@endsection
