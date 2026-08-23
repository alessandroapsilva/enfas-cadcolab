@extends('layouts.app')
@section('content')
<header class="flex justify-between items-center mb-10">
    <h2 class="text-3xl font-black uppercase tracking-tight">Identity <span class="text-blue-500">Records</span></h2>
</header>
<div class="glass-panel rounded-3xl overflow-hidden border border-slate-800">
    <table class="w-full text-left">
        <thead class="bg-slate-900/50">
            <tr><th class="p-6 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Colaborador</th><th class="p-6 text-[10px] font-bold text-slate-500 uppercase tracking-widest">M365 License</th><th class="p-6 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Ações</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-800/50">
            @foreach($colabs as $c)
            <tr class="hover:bg-slate-800/30 transition-all">
                <td class="p-6">
                    <div class="flex items-center gap-4">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($c->nome) }}&background=2563eb&color=fff" class="w-12 h-12 rounded-xl">
                        <div><p class="font-bold text-white text-lg">{{ $c->nome }}</p><p class="text-[10px] font-mono text-blue-400">ID: {{ $c->id }} | CPF: {{ $c->cpf }}</p></div>
                    </div>
                </td>
                <td class="p-6"><span class="bg-slate-800 px-3 py-1.5 rounded-lg text-xs font-bold text-slate-300 border border-slate-700"><i class="fa-brands fa-microsoft text-blue-500 mr-1"></i> {{ $c->licenca_type }}</span></td>
                <td class="p-6 text-right space-x-2">
                    <a href="{{ route('cracha', $c->id) }}" target="_blank" class="inline-block bg-blue-500/10 text-blue-400 hover:bg-blue-500 hover:text-white border border-blue-500/20 px-4 py-2 rounded-lg text-xs font-bold transition-colors shadow-sm" title="Imprimir Crachá"><i class="fa-solid fa-id-badge mr-1"></i> Crachá</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
