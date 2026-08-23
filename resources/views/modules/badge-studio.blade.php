<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CADCOLAB • Crachás & Modelos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/cadcolab-enterprise.css?v=20260823-v4320">
    <link rel="stylesheet" href="/cadcolab-shell.css?v=20260823-v4320">
    <link rel="stylesheet" href="/cadcolab-badge-studio.css?v=20260823-v4320">
    <style>
        html,body{margin:0;min-height:100%;background:#0f1420;color:#eef3fb;font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.app{display:grid;grid-template-columns:300px minmax(0,1fr);min-height:100vh}.sidebar{background:#0d1728;border-right:1px solid rgba(255,255,255,.07);padding:24px 18px;display:flex;flex-direction:column;gap:22px}.brand{padding:8px 12px 20px;border-bottom:1px solid rgba(255,255,255,.06)}.brand strong{font-size:22px;letter-spacing:.05em}.brand strong span{color:#11a8e8}.brand small{display:block;color:#77869f;letter-spacing:.18em;margin-top:6px}.nav-section{display:flex;flex-direction:column;gap:6px}.nav-section>span{font-size:11px;font-weight:800;color:#71809a;text-transform:uppercase;letter-spacing:.13em;padding:10px 12px 4px}.nav-link{display:flex;align-items:center;gap:12px;padding:12px 13px;border-radius:12px;color:#cbd5e5;text-decoration:none}.nav-link:hover,.nav-link.active{background:#112d45;color:#39b9f2}.nav-link i{width:19px;text-align:center}.sidebar-foot{margin-top:auto;padding:14px 12px;color:#74849d;font-size:12px;border-top:1px solid rgba(255,255,255,.06)}.main{min-width:0}.topbar{height:72px;display:flex;align-items:center;justify-content:space-between;padding:0 28px;border-bottom:1px solid rgba(255,255,255,.08);background:#111621;position:sticky;top:0;z-index:20}.topbar h1{font-size:20px;margin:0}.top-actions{display:flex;gap:10px;align-items:center}.pill{border:1px solid rgba(255,255,255,.12);border-radius:999px;padding:9px 13px;color:#dfe8f7}.logout{background:#e84757;color:white;text-decoration:none;border-radius:10px;padding:10px 18px;font-weight:700}.content-area{padding:28px;min-height:calc(100vh - 72px)}@media(max-width:980px){.app{grid-template-columns:1fr}.sidebar{display:none}.content-area{padding:16px}}
    </style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="brand"><strong>CADCOLAB <span>ENFAS</span></strong><small>IDENTITY & PEOPLE SUITE</small></div>
        <nav>
            <div class="nav-section"><span>Visão Geral</span><a class="nav-link" href="/dashboard?p=dashboard"><i class="fa-solid fa-house"></i>Início</a></div>
            <div class="nav-section"><span>Pessoas & Estrutura</span><a class="nav-link" href="/dashboard?p=colaboradores"><i class="fa-solid fa-users"></i>Colaboradores</a><a class="nav-link" href="/dashboard?p=unidades"><i class="fa-solid fa-building"></i>Unidades</a><a class="nav-link" href="/dashboard?p=setores"><i class="fa-solid fa-sitemap"></i>Setores</a><a class="nav-link" href="/dashboard?p=cargos"><i class="fa-solid fa-briefcase"></i>Cargos e Funções</a><a class="nav-link active" href="/dashboard?p=badge-studio"><i class="fa-solid fa-id-card"></i>Crachás & Modelos</a></div>
            <div class="nav-section"><span>Identidade & Acessos</span><a class="nav-link" href="/dashboard?p=identity-directory"><i class="fa-solid fa-address-book"></i>Identidade e Diretório</a><a class="nav-link" href="/dashboard?p=perfis"><i class="fa-solid fa-shield-halved"></i>Perfis de Acesso</a></div>
            <div class="nav-section"><span>Governança</span><a class="nav-link" href="/dashboard?p=relatorios"><i class="fa-solid fa-chart-column"></i>Relatórios</a><a class="nav-link" href="/dashboard?p=auditoria"><i class="fa-solid fa-file-shield"></i>Auditoria</a><a class="nav-link" href="/dashboard?p=configuracoes"><i class="fa-solid fa-sliders"></i>Configurações Mestres</a><a class="nav-link" href="/dashboard?p=changelog"><i class="fa-solid fa-clock-rotate-left"></i>Notas de Versão</a></div>
        </nav>
        <div class="sidebar-foot"><strong>CADCOLAB v4.3.2</strong><br>Server-rendered module shell</div>
    </aside>
    <main class="main">
        <header class="topbar"><h1><i class="fa-solid fa-id-card"></i>&nbsp; Crachás & Modelos</h1><div class="top-actions"><span class="pill">{{ session('admin_nome') ?: session('admin_usuario') ?: 'Admin' }}</span><a class="logout" href="/logout">Sair</a></div></header>
        <section class="content-area"><input type="hidden" name="_token" value="{{ csrf_token() }}"><div style="padding:40px;text-align:center;color:#8fa0b8"><i class="fa-solid fa-circle-notch fa-spin"></i> Carregando Designer de Crachás…</div></section>
    </main>
</div>
<script src="/cadcolab-badge-studio.js?v=20260823-v4320"></script>
</body>
</html>
