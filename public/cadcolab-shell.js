(() => {
    const VERSION = '4.3.1';
    const qs = (s, r = document) => r.querySelector(s);
    const qsa = (s, r = document) => [...r.querySelectorAll(s)];
    const page = () => new URLSearchParams(location.search).get('p') || 'dashboard';
    const esc = (v='') => String(v ?? '').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    let navigating = false;

    function sameDashboard(url) {
        return url.origin === location.origin && url.pathname === '/dashboard' && url.searchParams.has('p');
    }

    function loading(on, label='Abrindo módulo…') {
        let el = qs('#cadcolabSoftLoader');
        if (!el) {
            el = document.createElement('div');
            el.id = 'cadcolabSoftLoader';
            el.innerHTML = '<div class="ce-shell-loader-card"><span class="ce-shell-spinner"></span><div><strong>CADCOLAB</strong><small></small></div></div>';
            document.body.appendChild(el);
        }
        const small = qs('small', el);
        if (small) small.textContent = label;
        el.classList.toggle('show', !!on);
        document.body.classList.toggle('ce-shell-busy', !!on);
    }

    function loadFresh(src) {
        return new Promise(resolve => {
            const s = document.createElement('script');
            s.src = src + (src.includes('?') ? '&' : '?') + 'soft=' + Date.now();
            s.async = true;
            s.onload = s.onerror = () => { s.remove(); resolve(); };
            document.body.appendChild(s);
        });
    }

    async function activatePage() {
        const current = page();
        setActiveState();
        updateTopbar();
        if (current === 'badge-studio') {
            await loadFresh('/cadcolab-badge-studio.js');
        } else if (current === 'changelog') {
            await renderChangelog();
        } else {
            await loadFresh('/cadcolab-enterprise.js');
            await loadFresh('/cadcolab-intelligence.js');
        }
        document.dispatchEvent(new CustomEvent('cadcolab:navigated', {detail:{page:current,url:location.href}}));
    }

    async function softNavigate(href, push=true) {
        const target = new URL(href, location.href);
        if (!sameDashboard(target) || navigating) return false;
        if (target.href === location.href) return true;

        navigating = true;
        loading(true, `Abrindo ${target.searchParams.get('p') || 'módulo'}…`);
        try {
            const response = await fetch(target.href, {
                credentials:'same-origin',
                headers:{Accept:'text/html','X-Cadcolab-Navigation':'soft'},
                cache:'no-store'
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const html = await response.text();
            const parsed = new DOMParser().parseFromString(html, 'text/html');
            const nextArea = parsed.querySelector('.content-area');
            const currentArea = qs('.content-area');
            if (!nextArea || !currentArea) throw new Error('Área do módulo não encontrada');

            currentArea.innerHTML = nextArea.innerHTML;
            currentArea.className = nextArea.className;

            const nextTopbar = parsed.querySelector('.topbar');
            const currentTopbar = qs('.topbar');
            if (nextTopbar && currentTopbar) currentTopbar.innerHTML = nextTopbar.innerHTML;

            if (push) history.pushState({cadcolab:true}, '', target.href);
            else history.replaceState({cadcolab:true}, '', target.href);

            window.scrollTo({top:0,behavior:'auto'});
            await activatePage();
            return true;
        } catch (error) {
            console.error('[CADCOLAB soft navigation]', error);
            location.assign(target.href);
            return false;
        } finally {
            navigating = false;
            loading(false);
        }
    }

    function bindNavigation() {
        if (window.__cadcolabNavBound) return;
        window.__cadcolabNavBound = true;
        document.addEventListener('click', event => {
            if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            const link = event.target.closest('a[href]');
            if (!link || link.hasAttribute('download') || link.target === '_blank') return;
            const url = new URL(link.href, location.href);
            if (!sameDashboard(url)) return;
            event.preventDefault();
            softNavigate(url.href, true);
        }, true);
        window.addEventListener('popstate', () => {
            if (location.pathname === '/dashboard') softNavigate(location.href, false);
        });
    }

    function organizeSidebar() {
        const sidebar = qs('.sidebar');
        const nav = qs('.sidebar nav');
        if (!sidebar || !nav) return;
        sidebar.classList.add('ce-shell-sidebar');
        const footer = qsa('.sidebar .mt-auto').at(-1);
        if (footer) footer.innerHTML = `<div class="ce-shell-version"><strong>CADCOLAB v${VERSION}</strong><span>Identity & People Experience</span><small>ENFAS • 2026</small></div>`;
        qsa('.ce-nav-group').forEach(group => { group.open = !!group.querySelector('.ce-nav-link.active'); });
        const root = qs('.ce-nav-root');
        if (root && !qs('.ce-module-search')) {
            const search = document.createElement('div');
            search.className = 'ce-module-search';
            search.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i><input type="search" placeholder="Buscar módulo…" autocomplete="off"><kbd>/</kbd>';
            root.before(search);
            const input = qs('input', search);
            input.addEventListener('input', () => filterModules(input.value));
            document.addEventListener('keydown', e => {
                if (e.key === '/' && !/INPUT|TEXTAREA|SELECT/.test(document.activeElement?.tagName || '')) { e.preventDefault(); input.focus(); }
            });
        }
    }

    function filterModules(value) {
        const term = String(value || '').trim().toLocaleLowerCase('pt-BR');
        qsa('.ce-nav-group').forEach(group => {
            let visible = 0;
            qsa('.ce-nav-link', group).forEach(link => {
                const show = !term || link.textContent.toLocaleLowerCase('pt-BR').includes(term);
                link.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            group.style.display = visible ? '' : 'none';
            if (term && visible) group.open = true;
        });
    }

    function setActiveState() {
        const current = page();
        qsa('.ce-nav-link').forEach(link => {
            let active = false;
            try { active = new URL(link.href, location.href).searchParams.get('p') === current; } catch (_) {}
            link.classList.toggle('active', active);
        });
        qsa('.ce-nav-group').forEach(group => { group.open = !!group.querySelector('.ce-nav-link.active'); });
    }

    function updateTopbar() {
        const title = qs('.topbar h5');
        if (!title) return;
        const labels = {dashboard:'Visão Geral',colaboradores:'Colaboradores',unidades:'Unidades',setores:'Setores',cargos:'Cargos e Funções',grupos:'Perfis de Acesso',robos:'Automações',relatorios:'Relatórios',auditoria:'Auditoria',sistemas:'Aplicações SSO',usuarios:'Administradores',configuracoes:'Configurações Mestres',status:'Saúde das Integrações',erros:'Diagnósticos',ajuda:'Central de Ajuda',changelog:'Notas de Versão','identity-directory':'Identidade e Diretório','badge-studio':'Crachás & Modelos'};
        const label = labels[page()] || 'CADCOLAB';
        title.innerHTML = `<span class="ce-breadcrumb-root">CADCOLAB</span><i class="fa-solid fa-chevron-right ce-breadcrumb-sep"></i><span class="ce-breadcrumb-page">${esc(label)}</span>`;
    }

    async function renderChangelog() {
        if (page() !== 'changelog') return;
        const area = qs('.content-area');
        if (!area) return;
        area.innerHTML = '<div class="ce-release-loading"><span class="ce-shell-spinner"></span> Carregando histórico de versões…</div>';
        try {
            const response = await fetch('/enterprise/changelog',{headers:{Accept:'application/json'},credentials:'same-origin',cache:'no-store'});
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const data = await response.json();
            const versions = data.versions || [];
            area.innerHTML = `<section class="ce-release-hero"><div><span class="ce-release-kicker">RELEASE CENTER</span><h2>Histórico de evolução do CADCOLAB</h2><p>Todas as versões relevantes da plataforma, organizadas por entrega e impacto.</p></div><div class="ce-release-current"><small>VERSÃO ATUAL</small><strong>v${esc(data.current?.version || VERSION)}</strong><span>${esc(data.current?.date || '')}</span></div></section><div class="ce-release-toolbar"><div><strong>${versions.length}</strong><span> versões documentadas</span></div><input id="ceReleaseFilter" class="form-control" type="search" placeholder="Filtrar versões ou recursos…"></div><div class="ce-release-timeline">${versions.map(v=>`<article class="ce-release-item ${esc(v.type||'feature')}"><div class="ce-release-rail"><span></span></div><div class="ce-release-card"><div class="ce-release-head"><div><span class="ce-release-version">v${esc(v.version)}</span><span class="ce-release-date">${esc(v.date)}</span></div>${v.type==='current'?'<span class="ce-release-badge">ATUAL</span>':''}</div><h3>${esc(v.title)}</h3><p>${esc(v.summary)}</p><ul>${(v.items||[]).map(i=>`<li><i class="fa-solid fa-check"></i><span>${esc(i)}</span></li>`).join('')}</ul></div></article>`).join('')}</div>`;
            qs('#ceReleaseFilter')?.addEventListener('input',e=>{const term=e.target.value.toLocaleLowerCase('pt-BR');qsa('.ce-release-item').forEach(item=>item.style.display=!term||item.textContent.toLocaleLowerCase('pt-BR').includes(term)?'':'none');});
        } catch (error) {
            area.innerHTML = `<div class="alert alert-danger">Não foi possível carregar o histórico. ${esc(error.message)}</div>`;
        }
    }

    function init() {
        document.documentElement.dataset.cadcolabVersion = VERSION;
        organizeSidebar();
        setActiveState();
        updateTopbar();
        bindNavigation();
        activatePage();
        loading(false);
    }

    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', init, {once:true}) : init();
})();
