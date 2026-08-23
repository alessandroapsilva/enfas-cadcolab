(() => {
    const qs = (s, r=document) => r.querySelector(s);
    const esc = (v='') => String(v ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
    const param = new URLSearchParams(location.search).get('p') || 'dashboard';
    const csrf = qs('input[name="_token"]')?.value || '';

    function injectMenu() {
        const menu = qs('#menuTI');
        if (!menu || qs('[data-ce-identity-menu]')) return;
        const a = document.createElement('a');
        a.href = '/dashboard?p=configuracoes#identity-directory-settings';
        a.className = 'nav-item';
        a.dataset.ceIdentityMenu = '1';
        a.innerHTML = '<i class="fa-solid fa-address-card" style="width:20px;"></i><span>Identidade & Diretório</span>';
        const cfg = menu.querySelector('a[href="/dashboard?p=configuracoes"]');
        if (cfg) menu.insertBefore(a, cfg); else menu.appendChild(a);

        const c = document.createElement('a');
        c.href = '/dashboard?p=configuracoes#communication-audit';
        c.className = 'nav-item';
        c.dataset.ceIdentityMenu = '1';
        c.innerHTML = '<i class="fa-solid fa-envelope-circle-check" style="width:20px;"></i><span>Comunicações</span>';
        menu.appendChild(c);
    }

    async function loadIdentityPanel() {
        if (param !== 'configuracoes') return;
        const area = qs('.content-area');
        if (!area || qs('#identity-directory-settings')) return;

        const shell = document.createElement('section');
        shell.id = 'identity-directory-settings';
        shell.className = 'cadcolab-enterprise-panel';
        shell.innerHTML = '<div class="ce-header"><div><h3 class="ce-title"><i class="fa-solid fa-address-card text-primary me-2"></i>Identidade e Diretório Corporativo</h3><div class="ce-subtitle">LDAP/Active Directory, autenticação centralizada e sincronização de perfis dentro das Configurações Mestres.</div></div><span class="ce-badge warn" id="ceLdapBadge"><i class="fa-solid fa-circle-notch fa-spin"></i> Carregando</span></div><div id="ceIdentityBody"><div class="text-muted small">Carregando configurações do diretório...</div></div>';
        area.prepend(shell);

        try {
            const r = await fetch('/enterprise-settings/identity', {headers:{'Accept':'application/json'}});
            if (!r.ok) throw new Error('HTTP '+r.status);
            const d = await r.json();
            const l = d.ldap || {}, p = d.profiles || {}, c = d.counts || {};
            const badge = qs('#ceLdapBadge');
            badge.className = 'ce-badge ' + (d.connection?.success ? 'ok' : 'warn');
            badge.innerHTML = '<i class="fa-solid '+(d.connection?.success?'fa-circle-check':'fa-triangle-exclamation')+'"></i> '+esc(d.connection?.success?'Conectado':'Pendente');

            qs('#ceIdentityBody').innerHTML = `
                <div class="ce-status-grid">
                    <div class="ce-stat"><strong>${Number(c.users||0)}</strong><span>Usuários sincronizados</span></div>
                    <div class="ce-stat"><strong>${Number(c.active||0)}</strong><span>Usuários ativos</span></div>
                    <div class="ce-stat"><strong>${Number(c.groups||0)}</strong><span>Grupos do diretório</span></div>
                    <div class="ce-stat"><strong>${d.bind_password_configured?'OK':'—'}</strong><span>Credencial de bind</span></div>
                </div>
                <form method="POST" action="/enterprise-settings/identity" autocomplete="off">
                    <input type="hidden" name="_token" value="${esc(csrf)}">
                    <div class="row g-3">
                        <div class="col-md-5"><label class="form-label small fw-bold">Servidor LDAP / AD</label><input class="form-control" name="host" value="${esc(l.host)}" required></div>
                        <div class="col-md-2"><label class="form-label small fw-bold">Porta</label><input class="form-control" type="number" name="port" value="${esc(l.port||389)}" required></div>
                        <div class="col-md-2"><label class="form-label small fw-bold">Timeout</label><input class="form-control" type="number" name="timeout" value="${esc(l.timeout||8)}" required></div>
                        <div class="col-md-3 d-flex align-items-end gap-3 pb-2"><label class="form-check"><input class="form-check-input" type="checkbox" name="ssl" value="1" ${l.ssl?'checked':''}> LDAPS</label><label class="form-check"><input class="form-check-input" type="checkbox" name="start_tls" value="1" ${l.start_tls?'checked':''}> StartTLS</label></div>
                        <div class="col-md-4"><label class="form-label small fw-bold">Base DN</label><input class="form-control" name="base_dn" value="${esc(l.base_dn)}" placeholder="DC=enfas,DC=local" required></div>
                        <div class="col-md-4"><label class="form-label small fw-bold">OU / DN de usuários</label><input class="form-control" name="users_dn" value="${esc(l.users_dn)}" required></div>
                        <div class="col-md-4"><label class="form-label small fw-bold">OU / DN de grupos</label><input class="form-control" name="groups_dn" value="${esc(l.groups_dn)}" required></div>
                        <div class="col-md-5"><label class="form-label small fw-bold">Conta de serviço / Bind DN</label><input class="form-control" name="bind_dn" value="${esc(l.bind_dn)}" required></div>
                        <div class="col-md-4"><label class="form-label small fw-bold">Senha de bind</label><input class="form-control" type="password" name="bind_password" placeholder="${d.bind_password_configured?'Configurada — deixe vazio para manter':'Informe a senha'}"></div>
                        <div class="col-md-3"><label class="form-label small fw-bold">Sufixo da conta</label><input class="form-control" name="account_suffix" value="${esc(l.account_suffix)}" placeholder="@enfas.local"></div>
                        <div class="col-md-4"><label class="form-label small fw-bold">Grupos Admin</label><input class="form-control" name="admin_groups" value="${esc((p.admin_groups||[]).join(','))}"></div>
                        <div class="col-md-4"><label class="form-label small fw-bold">Grupos TI</label><input class="form-control" name="ti_groups" value="${esc((p.ti_groups||[]).join(','))}"></div>
                        <div class="col-md-4"><label class="form-label small fw-bold">Grupos RH</label><input class="form-control" name="rh_groups" value="${esc((p.rh_groups||[]).join(','))}"></div>
                    </div>
                    <div class="ce-section-title">Comportamento de autenticação</div>
                    <div class="d-flex flex-wrap gap-4 mb-3">
                        <label class="form-check"><input class="form-check-input" type="checkbox" name="enabled" value="1" ${d.enabled?'checked':''}> Ativar autenticação LDAP</label>
                        <label class="form-check"><input class="form-check-input" type="checkbox" name="sync_on_login" value="1" ${d.sync_on_login?'checked':''}> Sincronizar no login</label>
                        <label class="form-check"><input class="form-check-input" type="checkbox" name="local_fallback" value="1" ${d.local_fallback?'checked':''}> Fallback administrativo local</label>
                    </div>
                    <div class="ce-actions"><button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Salvar Identidade</button><a class="btn btn-outline-secondary" href="/dashboard?p=identity-directory"><i class="fa-solid fa-users-viewfinder me-1"></i> Ver usuários e grupos</a></div>
                </form>
                <div class="ce-actions mt-3">
                    <form method="POST" action="/enterprise-settings/identity/test"><input type="hidden" name="_token" value="${esc(csrf)}"><button class="btn btn-outline-info"><i class="fa-solid fa-plug-circle-check me-1"></i> Testar conexão</button></form>
                    <form method="POST" action="/enterprise-settings/identity/sync"><input type="hidden" name="_token" value="${esc(csrf)}"><button class="btn btn-outline-success"><i class="fa-solid fa-arrows-rotate me-1"></i> Sincronizar agora</button></form>
                    <span class="small text-muted">${esc(d.connection?.message||'')}</span>
                </div>`;
        } catch (e) {
            qs('#ceIdentityBody').innerHTML = '<div class="alert alert-warning mb-0">Não foi possível carregar o módulo de identidade. Verifique as migrations e as rotas da integração.</div>';
        }
    }

    async function loadCommunicationAudit() {
        if (param !== 'configuracoes') return;
        const area = qs('.content-area');
        if (!area || qs('#communication-audit')) return;
        const panel = document.createElement('section');
        panel.id = 'communication-audit'; panel.className = 'cadcolab-enterprise-panel';
        panel.innerHTML = '<div class="ce-header"><div><h3 class="ce-title"><i class="fa-solid fa-envelope-circle-check text-primary me-2"></i>Histórico de Comunicações</h3><div class="ce-subtitle">Auditoria dos e-mails operacionais enviados pelo CADCOLAB, com falhas e responsável pelo disparo.</div></div></div><div id="ceMailLogs" class="text-muted small">Carregando histórico...</div>';
        area.appendChild(panel);
        try {
            const r = await fetch('/communication-logs/emails?limit=50', {headers:{'Accept':'application/json'}});
            if (!r.ok) throw new Error();
            const d = await r.json();
            const rows = (d.items||[]).map(x => `<tr><td>${esc(x.created_at||'')}</td><td>${esc(x.recipient||'')}</td><td>${esc(x.subject||'')}</td><td><span class="ce-log-status ${esc(x.status||'queued')}">${esc(x.status||'')}</span></td><td>${esc(x.triggered_by||'Sistema')}</td><td class="text-danger">${esc(x.error_message||'')}</td></tr>`).join('');
            qs('#ceMailLogs').innerHTML = `<div class="ce-status-grid"><div class="ce-stat"><strong>${Number(d.stats?.total||0)}</strong><span>Total</span></div><div class="ce-stat"><strong>${Number(d.stats?.sent||0)}</strong><span>Enviados</span></div><div class="ce-stat"><strong>${Number(d.stats?.failed||0)}</strong><span>Falhas</span></div></div><div class="table-responsive"><table class="ce-log-table"><thead><tr><th>Data</th><th>Destinatário</th><th>Assunto</th><th>Status</th><th>Operador</th><th>Erro</th></tr></thead><tbody>${rows || '<tr><td colspan="6" class="text-center text-muted py-4">Ainda não há e-mails auditados.</td></tr>'}</tbody></table></div>`;
        } catch(e) { qs('#ceMailLogs').innerHTML = '<div class="alert alert-warning mb-0">Histórico indisponível até a migration de comunicação ser aplicada.</div>'; }
    }

    function refinePhotos() {
        document.querySelectorAll('.uc-avatar img').forEach(img => { img.loading='lazy'; img.decoding='async'; });
    }

    document.addEventListener('DOMContentLoaded', () => {
        injectMenu();
        refinePhotos();
        loadIdentityPanel();
        loadCommunicationAudit();
    });
})();
