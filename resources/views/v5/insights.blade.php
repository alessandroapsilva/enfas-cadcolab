@extends('layouts.cadcolab-v5')
@section('content')
<div class="v5-grid"><div class="v5-card v5-kpi"><small>Score de Governança</small><strong id="govScore">--</strong><span>Calculado com dados internos</span></div><div class="v5-card"><small style="color:var(--v5-muted)">PRIVACIDADE</small><h3 style="margin:8px 0 6px">Análise local</h3><p style="margin:0;color:var(--v5-muted);font-size:11px">As regras de diagnóstico não enviam dados pessoais para provedores externos.</p></div></div>
<div class="v5-section" id="insightList"><div class="v5-card" style="color:var(--v5-muted)"><i class="fa-solid fa-circle-notch fa-spin"></i> Analisando governança…</div></div>
@push('scripts')
<script>
const insightEsc = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
fetch('/enterprise/insights',{headers:{Accept:'application/json'},credentials:'same-origin'})
    .then(response => response.ok ? response.json() : Promise.reject())
    .then(data => {
        document.getElementById('govScore').textContent=(data.score??0)+'/100';
        document.getElementById('insightList').innerHTML=(data.items||[]).map(item=>`<article class="v5-card" style="margin-bottom:10px"><div style="display:flex;justify-content:space-between;gap:12px"><div><span class="v5-badge">${insightEsc(String(item.severity||'info').toUpperCase())}</span><h3 style="margin:9px 0 5px">${insightEsc(item.title||'')}</h3><p style="margin:0;color:var(--v5-muted);font-size:11px">${insightEsc(item.message||'')}</p></div><small style="color:var(--v5-muted)">${insightEsc(item.module||'')}</small></div><div style="margin-top:12px;padding:10px;border-radius:10px;background:#101724;font-size:11px"><strong>Ação sugerida:</strong> ${insightEsc(item.action||'')}</div></article>`).join('')||'<div class="v5-card">Nenhuma recomendação pendente.</div>';
    })
    .catch(()=>document.getElementById('insightList').innerHTML='<div class="v5-alert danger">Falha ao carregar insights.</div>');
</script>
@endpush
@endsection
