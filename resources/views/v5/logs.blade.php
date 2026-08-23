@extends('layouts.cadcolab-v5')
@section('content')
<div class="v5-card"><div class="v5-section-head"><div><h2>{{ $module['label'] }}</h2><small style="color:var(--v5-muted)">Rastreabilidade e diagnóstico centralizados.</small></div>@if($page==='erros')<form method="POST" action="/acao">@csrf<input type="hidden" name="action" value="limpar_erros"><button style="border:0;border-radius:9px;background:#b94352;color:#fff;padding:9px 12px;cursor:pointer">Limpar logs</button></form>@endif</div><div style="overflow:auto"><table class="v5-table"><thead><tr><th>Data</th><th>Módulo/Operador</th><th>Evento</th><th>Detalhes</th></tr></thead><tbody>
@php $rows=$page==='erros'?($dados['ef']??[]):($dados['lf']??[]); @endphp
@forelse($rows as $r)<tr><td>{{ $r['data_fmt'] ?? ($r['data_hora'] ?? '-') }}</td><td>{{ $r['modulo'] ?? ($r['usuario_admin'] ?? '-') }}</td><td><span class="v5-badge">{{ $r['acao'] ?? ($page==='erros'?'ERRO':'EVENTO') }}</span></td><td>{{ $r['mensagem'] ?? ($r['detalhes'] ?? '-') }}</td></tr>@empty<tr><td colspan="4" style="color:var(--v5-muted)">Nenhum registro encontrado.</td></tr>@endforelse
</tbody></table></div></div>
@endsection
