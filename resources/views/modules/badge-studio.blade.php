<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CADCOLAB • Crachás & Modelos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/cadcolab-badge-studio.css?v=20260823-v4330">
    <link rel="stylesheet" href="/cadcolab-module-shell.css?v=20260823-v4330">
    <style>html,body{margin:0;min-height:100%;background:#0f1420;color:#eef3fb;font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}*,*:before,*:after{box-sizing:border-box}.cms-close-mobile{display:none}@media(max-width:980px){.cms-close-mobile{display:grid}}</style>
</head>
<body>
<div class="app">
    <div class="cms-backdrop" aria-hidden="true"></div>
    <aside class="sidebar" aria-label="Navegação principal">
        <div class="cms-sidebar-head">
            <div class="cms-brand"><strong>CADCOLAB <span>ENFAS</span></strong><small>IDENTITY & PEOPLE SUITE</small></div>
            <button id="cmsCollapse" class="cms-icon-btn" type="button" title="Recolher menu" aria-label="Recolher menu"><i class="fa-solid fa-angles-left"></i></button>
            <button id="cmsCloseMobile" class="cms-icon-btn cms-close-mobile" type="button" title="Fechar menu" aria-label="Fechar menu"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="cms-search"><i class="fa-solid fa-magnifying-glass"></i><input id="cmsSearch" type="search" placeholder="Buscar módulo…" autocomplete="off"><kbd>/</kbd></div>

        <nav class="cms-nav">
            <section class="cms-nav-group" data-group="overview">
                <button class="cms-nav-trigger" type="button"><span class="cms-group-icon"><i class="fa-solid fa-grid-2"></i></span><span class="cms-group-label">Visão Geral</span><i class="fa-solid fa-chevron-down cms-chevron"></i></button>
                <div class="cms-nav-body"><div class="cms-nav-body-inner"><a class="nav-link" href="/dashboard?p=dashboard"><i class="fa-solid fa-house"></i><span>Início</span></a></div></div>
            </section>

            <section class="cms-nav-group" data-group="people">
                <button class="cms-nav-trigger" type="button"><span class="cms-group-icon"><i class="fa-solid fa-users"></i></span><span class="cms-group-label">Pessoas & Estrutura</span><i class="fa-solid fa-chevron-down cms-chevron"></i></button>
                <div class="cms-nav-body"><div class="cms-nav-body-inner">
                    <a class="nav-link" href="/dashboard?p=colaboradores"><i class="fa-solid fa-user-group"></i><span>Colaboradores</span></a>
                    <a class="nav-link" href="/dashboard?p=unidades"><i class="fa-solid fa-building"></i><span>Unidades</span></a>
                    <a class="nav-link" href="/dashboard?p=setores"><i class="fa-solid fa-sitemap"></i><span>Setores</span></a>
                    <a class="nav-link" href="/dashboard?p=cargos"><i class="fa-solid fa-briefcase"></i><span>Cargos e Funções</span></a>
                    <a class="nav-link active" href="/dashboard?p=badge-studio"><i class="fa-solid fa-id-card"></i><span>Crachás & Modelos</span></a>
                </div></div>
            </section>

            <section class="cms-nav-group" data-group="identity">
                <button class="cms-nav-trigger" type="button"><span class="cms-group-icon"><i class="fa-solid fa-fingerprint"></i></span><span class="cms-group-label">Identidade & Acessos</span><i class="fa-solid fa-chevron-down cms-chevron"></i></button>
                <div class="cms-nav-body"><div class="cms-nav-body-inner">
                    <a class="nav-link" href="/dashboard?p=identity-directory"><i class="fa-solid fa-address-book"></i><span>Identidade e Diretório</span></a>
                    <a class="nav-link" href="/dashboard?p=perfis"><i class="fa-solid fa-shield-halved"></i><span>Perfis de Acesso</span></a>
                    <a class="nav-link" href="/dashboard?p=sistemas"><i class="fa-solid fa-cubes"></i><span>Aplicações SSO</span></a>
                </div></div>
            </section>

            <section class="cms-nav-group" data-group="automation">
                <button class="cms-nav-trigger" type="button"><span class="cms-group-icon"><i class="fa-solid fa-bolt"></i></span><span class="cms-group-label">Automação & Integrações</span><i class="fa-solid fa-chevron-down cms-chevron"></i></button>
                <div class="cms-nav-body"><div class="cms-nav-body-inner">
                    <a class="nav-link" href="/dashboard?p=jornada"><i class="fa-solid fa-clock"></i><span>Jornada & Automações</span></a>
                    <a class="nav-link" href="/dashboard?p=status_cloud"><i class="fa-solid fa-cloud"></i><span>Saúde das Integrações</span></a>
                </div></div>
            </section>

            <section class="cms-nav-group" data-group="governance">
                <button class="cms-nav-trigger" type="button"><span class="cms-group-icon"><i class="fa-solid fa-chart-line"></i></span><span class="cms-group-label">Governança & Auditoria</span><i class="fa-solid fa-chevron-down cms-chevron"></i></button>
                <div class="cms-nav-body"><div class="cms-nav-body-inner">
                    <a class="nav-link" href="/dashboard?p=relatorios"><i class="fa-solid fa-chart-column"></i><span>Relatórios</span></a>
                    <a class="nav-link" href="/dashboard?p=auditoria"><i class="fa-solid fa-file-shield"></i><span>Auditoria</span></a>
                    <a class="nav-link" href="/dashboard?p=comunicacoes"><i class="fa-solid fa-envelope-circle-check"></i><span>Comunicações</span></a>
                </div></div>
            </section>

            <section class="cms-nav-group" data-group="admin">
                <button class="cms-nav-trigger" type="button"><span class="cms-group-icon"><i class="fa-solid fa-gear"></i></span><span class="cms-group-label">Administração</span><i class="fa-solid fa-chevron-down cms-chevron"></i></button>
                <div class="cms-nav-body"><div class="cms-nav-body-inner">
                    <a class="nav-link" href="/dashboard?p=configuracoes"><i class="fa-solid fa-sliders"></i><span>Configurações Mestres</span></a>
                    <a class="nav-link" href="/dashboard?p=erros"><i class="fa-solid fa-triangle-exclamation"></i><span>Diagnósticos e Falhas</span></a>
                    <a class="nav-link" href="/dashboard?p=changelog"><i class="fa-solid fa-clock-rotate-left"></i><span>Notas de Versão</span></a>
                </div></div>
            </section>
        </nav>

        <div class="sidebar-foot"><strong>CADCOLAB v4.3.3</strong><br>Enterprise Module Shell<br><span>ENFAS • 2026</span></div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="cms-top-left"><button id="cmsMobileMenu" class="cms-icon-btn cms-mobile-menu" type="button" aria-label="Abrir menu"><i class="fa-solid fa-bars"></i></button><h1><i class="fa-solid fa-id-card"></i>&nbsp; Crachás & Modelos</h1></div>
            <div class="top-actions"><span class="pill"><i class="fa-solid fa-circle-user"></i>&nbsp; {{ session('admin_nome') ?: session('admin_usuario') ?: 'Admin' }}</span><a class="logout" href="/logout">Sair</a></div>
        </header>
        <section class="content-area"><input type="hidden" name="_token" value="{{ csrf_token() }}"><div style="padding:40px;text-align:center;color:#8fa0b8"><i class="fa-solid fa-circle-notch fa-spin"></i> Carregando Designer de Crachás…</div></section>
    </main>
</div>
<script src="/cadcolab-module-shell.js?v=20260823-v4330"></script>
<script src="/cadcolab-badge-studio.js?v=20260823-v4330"></script>
</body>
</html>
