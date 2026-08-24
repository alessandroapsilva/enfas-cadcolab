<!doctype html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $module['label'] ?? 'CADCOLAB' }} • CADCOLAB v{{ $cadcolabVersion }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/cadcolab-v5.css?v=500a1">
    @stack('styles')
</head>
<body>
<div class="v5-app" id="cadcolabApp">
    <aside class="v5-sidebar" id="cadcolabSidebar">
        <div class="v5-brand">
            <a href="/dashboard?p=dashboard"><strong>CADCOLAB <span>ENFAS</span></strong><small>ENTERPRISE IDENTITY SUITE</small></a>
            <button type="button" id="sidebarCollapse" aria-label="Recolher menu"><i class="fa-solid fa-angles-left"></i></button>
        </div>
        <div class="v5-search"><i class="fa-solid fa-magnifying-glass"></i><input id="moduleSearch" type="search" placeholder="Buscar módulo…"><kbd>/</kbd></div>
        <nav class="v5-nav">
            @foreach($moduleGroups as $groupKey => $group)
                @php $open = collect(array_keys($group['modules']))->contains($page); @endphp
                <details class="v5-group" data-group="{{ $groupKey }}" {{ $open ? 'open' : '' }}>
                    <summary><span><i class="fa-solid {{ $group['icon'] ?? 'fa-folder' }}"></i>{{ $group['label'] }}</span><i class="fa-solid fa-chevron-down"></i></summary>
                    <div class="v5-items">
                        @foreach($group['modules'] as $key => $item)
                            <a class="v5-link {{ $page === $key ? 'active' : '' }}" href="/dashboard?p={{ urlencode($key) }}" data-page="{{ $key }}"><i class="fa-solid {{ $item['icon'] ?? 'fa-circle' }}"></i><span>{{ $item['label'] }}</span></a>
                        @endforeach
                    </div>
                </details>
            @endforeach
        </nav>
        <div class="v5-foot"><strong>CADCOLAB v{{ $cadcolabVersion }}</strong><span>Enterprise Platform</span><small>ENFAS • 2026</small></div>
    </aside>
    <div class="v5-main">
        <header class="v5-topbar">
            <div class="v5-title-wrap"><button type="button" id="mobileMenu" aria-label="Abrir menu"><i class="fa-solid fa-bars"></i></button><div><small>{{ $module['group_label'] ?? 'CADCOLAB' }}</small><h1>{{ $module['label'] ?? 'CADCOLAB' }}</h1><p>{{ $module['description'] ?? 'Gestão integrada de pessoas, identidades, acessos e integrações.' }}</p></div></div>
            <div class="v5-user"><span><i class="fa-regular fa-circle-user"></i>{{ session('admin_nome') ?: session('admin_usuario') ?: 'Administrador' }}</span><form method="POST" action="/logout">@csrf<button class="v5-logout" type="submit" style="display:inline-flex;align-items:center;gap:7px;border:1px solid var(--v5-border);background:transparent;color:var(--v5-muted);border-radius:9px;padding:7px 10px;cursor:pointer"><i class="fa-solid fa-arrow-right-from-bracket"></i>Sair</button></form></div>
        </header>
        <main class="v5-content">
            @if(session('swal'))<div class="v5-alert success"><i class="fa-solid fa-circle-check"></i>{{ session('swal') }}</div>@endif
            @if(session('swal_error'))<div class="v5-alert danger"><i class="fa-solid fa-triangle-exclamation"></i>{{ session('swal_error') }}</div>@endif
            @yield('content')
        </main>
    </div>
</div>
<div class="v5-overlay" id="sidebarOverlay"></div>
<script src="/cadcolab-v5.js?v=500a1"></script>
@stack('scripts')
</body>
</html>
