<!DOCTYPE html>
<html lang="pt-br" class="dark">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CADCOLAB PRO | Enterprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');
        body { background: #020617; font-family: 'Inter', sans-serif; color: #f1f5f9; }
        .glass-panel { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.05); }
        .nav-link { display: flex; align-items: center; gap: 1rem; padding: 1rem; border-radius: 1rem; color: #94a3b8; font-weight: 600; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { background: #2563eb; color: #fff; box-shadow: 0 0 15px rgba(37,99,235,0.3); }
        .tab-btn.active { border-bottom: 2px solid #3b82f6; color: #3b82f6; }
    </style>
</head>
<body class="flex min-h-screen">
    <aside class="w-72 glass-panel h-screen fixed flex flex-col z-50 border-r border-slate-800">
        <div class="p-8 mb-4 flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/30 text-white font-black text-xl"><i class="fa-solid fa-layer-group"></i></div>
            <div><h1 class="text-xl font-black tracking-tighter">CADCOLAB</h1><p class="text-[10px] text-blue-400 font-bold uppercase tracking-[2px]">IAM Identity</p></div>
        </div>
        <nav class="flex-grow overflow-y-auto px-4 space-y-2">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-4 mb-2 mt-4">Gestão Central</p>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fa-solid fa-users w-5"></i> Colaboradores</a>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-4 mb-2 mt-8">Sistemas & API</p>
            <a href="{{ route('configuracoes') }}" class="nav-link {{ request()->routeIs('configuracoes') ? 'active' : '' }}"><i class="fa-solid fa-gears w-5"></i> Configurações</a>
        </nav>
        <div class="p-6 border-t border-slate-800">
            <a href="/logout" class="flex items-center gap-3 text-red-500 text-sm font-bold hover:text-red-400"><i class="fa-solid fa-power-off"></i> Encerrar Sessão</a>
        </div>
    </aside>
    <main class="ml-72 flex-grow p-10 w-full">@yield('content')</main>
    @if(session('sucesso')) <script>Swal.fire({title:'Sucesso!', text:'{{session("sucesso")}}', icon:'success', confirmButtonColor:'#2563eb', background:'#0f172a', color:'#fff'});</script> @endif
</body>
</html>
