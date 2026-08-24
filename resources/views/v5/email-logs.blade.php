@extends('layouts.cadcolab-v5')
@section('content')
<div class="v5-grid"><div class="v5-card v5-kpi"><small>Total</small><strong id="mailTotal">--</strong><span>E-mails auditados</span></div><div class="v5-card v5-kpi"><small>Enviados</small><strong id="mailSent">--</strong><span>Entregas registradas</span></div><div class="v5-card v5-kpi"><small>Falhas</small><strong id="mailFailed">--</strong><span>Precisam de atenção</span></div></div>
<div class="v5-section v5-card"><div class="v5-section-head"><div><h2>Histórico de E-mails</h2><small style="color:var(--v5-muted)">Destinatário, assunto, status, provedor e operador.</small></div></div><div style="overflow:auto"><table class="v5-table"><thead><tr><th>Data</th><th>Destinatário</th><th>Assunto</th><th>Status</th><th>Operador</th><th>Erro</th></tr></thead><tbody id="mailRows"><tr><td colspan="6" style="color:var(--v5-muted)">Carregando…</td></tr></tbody></table></div></div>
@push('scripts')
<script>
const mailEsc = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
fetch('/communication-logs/emails?limit=100',{headers:{Accept:'application/json'},credentials:'same-origin'})
    .then(response => response.ok ? response.json() : Promise.reject())
    .then(data => {
        document.getElementById('mailTotal').textContent = data.stats?.total ?? 0;
        document.getElementById('mailSent').textContent = data.stats?.sent ?? 0;
        document.getElementById('mailFailed').textContent = data.stats?.failed ?? 0;
        document.getElementById('mailRows').innerHTML = (data.items || []).map(item => `<tr><td>${mailEsc(item.sent_at||item.created_at||'-')}</td><td>${mailEsc(item.recipient||'-')}</td><td>${mailEsc(item.subject||'-')}</td><td><span class="v5-badge">${mailEsc(item.status||'-')}</span></td><td>${mailEsc(item.operator||item.operator_name||'-')}</td><td>${mailEsc(item.error_message||item.error||'')}</td></tr>`).join('') || '<tr><td colspan="6">Nenhum e-mail registrado.</td></tr>';
    })
    .catch(() => document.getElementById('mailRows').innerHTML='<tr><td colspan="6">Falha ao carregar histórico.</td></tr>');
</script>
@endpush
@endsection
