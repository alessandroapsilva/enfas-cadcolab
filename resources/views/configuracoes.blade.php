@extends('layouts.app')

@section('content')
<header class="mb-10"><h2 class="text-3xl font-black uppercase tracking-tight text-white">Configurações <span class="text-blue-500">Gerais</span></h2></header>

<div class="glass-panel rounded-3xl overflow-hidden" x-data="{ configTab: 1 }">
    <div class="flex border-b border-slate-800 bg-slate-900/40 px-6 overflow-x-auto">
        <button @click="configTab = 1" :class="configTab == 1 ? 'border-b-2 border-blue-500 text-blue-400' : 'text-slate-500 border-transparent'" class="px-8 py-5 font-bold text-xs uppercase tracking-widest"><i class="fa-solid fa-cloud mr-2"></i> Integrações API</button>
        <button @click="configTab = 2" :class="configTab == 2 ? 'border-b-2 border-blue-500 text-blue-400' : 'text-slate-500 border-transparent'" class="px-8 py-5 font-bold text-xs uppercase tracking-widest"><i class="fa-solid fa-envelope mr-2"></i> Mensageria & Google</button>
        <button @click="configTab = 3" :class="configTab == 3 ? 'border-b-2 border-blue-500 text-blue-400' : 'text-slate-500 border-transparent'" class="px-8 py-5 font-bold text-xs uppercase tracking-widest"><i class="fa-solid fa-palette mr-2"></i> Layouts HTML</button>
    </div>
    
    <form method="POST" action="{{ route('salvar.configuracoes') }}" class="p-10">
        @csrf
        
        <div x-show="configTab == 1" class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <div class="bg-slate-900/50 p-6 rounded-2xl border border-slate-800">
                <h4 class="font-bold text-blue-400 mb-6 text-lg"><i class="fa-brands fa-microsoft"></i> Microsoft 365 (Graph API)</h4>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Tenant ID</label>
                <input type="text" name="cfg[m365_tenant]" value="{{ $dados['configs']['m365_tenant'] ?? '' }}" class="mb-4">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Client ID</label>
                <input type="text" name="cfg[m365_client]" value="{{ $dados['configs']['m365_client'] ?? '' }}" class="mb-4">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Client Secret</label>
                <input type="password" name="cfg[m365_secret]" value="{{ $dados['configs']['m365_secret'] ?? '' }}">
            </div>
            <div class="bg-slate-900/50 p-6 rounded-2xl border border-slate-800">
                <h4 class="font-bold text-emerald-400 mb-6 text-lg"><i class="fa-brands fa-whatsapp"></i> WhatsApp Meta API Oficial</h4>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Phone Number ID</label>
                <input type="text" name="cfg[wp_phone_id]" value="{{ $dados['configs']['wp_phone_id'] ?? '' }}" class="mb-4">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Access Token Permanente</label>
                <input type="password" name="cfg[wp_token]" value="{{ $dados['configs']['wp_token'] ?? '' }}" class="mb-4">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Nome do Template (Aviso de Senha)</label>
                <input type="text" name="cfg[wp_template_senha]" value="{{ $dados['configs']['wp_template_senha'] ?? '' }}" placeholder="Ex: aviso_senha_enfas">
            </div>
        </div>

        <div x-show="configTab == 2" class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <div class="bg-slate-900/50 p-6 rounded-2xl border border-slate-800">
                <h4 class="font-bold text-amber-400 mb-6 text-lg"><i class="fa-brands fa-google"></i> Google Workspace (Directory)</h4>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Domínio Oficial</label>
                <input type="text" name="cfg[gw_domain]" value="{{ $dados['configs']['gw_domain'] ?? '' }}" placeholder="enfas.com.br" class="mb-4">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Service Account (JSON Key)</label>
                <textarea name="cfg[gw_json]" rows="6" class="font-mono text-[10px] text-amber-200">{{ $dados['configs']['gw_json'] ?? '' }}</textarea>
            </div>
            <div class="bg-slate-900/50 p-6 rounded-2xl border border-slate-800">
                <h4 class="font-bold text-white mb-6 text-lg"><i class="fa-solid fa-envelope text-slate-400"></i> SMTP (Brevo / Relay)</h4>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Host SMTP</label>
                <input type="text" name="cfg[smtp_host]" value="{{ $dados['configs']['smtp_host'] ?? '' }}" class="mb-4">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div><label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Porta</label><input type="text" name="cfg[smtp_port]" value="{{ $dados['configs']['smtp_port'] ?? '' }}"></div>
                    <div><label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Usuário</label><input type="text" name="cfg[smtp_user]" value="{{ $dados['configs']['smtp_user'] ?? '' }}"></div>
                </div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Senha SMTP</label>
                <input type="password" name="cfg[smtp_pass]" value="{{ $dados['configs']['smtp_pass'] ?? '' }}">
            </div>
        </div>

        <div x-show="configTab == 3" class="space-y-6">
            <div class="bg-slate-900/50 p-6 rounded-2xl border border-slate-800">
                <label class="block text-xs font-bold text-blue-400 uppercase mb-2"><i class="fa-solid fa-id-badge"></i> Template Crachá (HTML e CSS In-line)</label>
                <p class="text-[10px] text-slate-500 mb-4">Variáveis suportadas: {NOME}, {ID}, {CARGO}, {FOTO}</p>
                <textarea name="cfg[tpl_cracha]" rows="8" class="font-mono text-[11px] text-green-400 w-full bg-slate-950 p-4 rounded-xl">{{ $dados['configs']['tpl_cracha'] ?? '' }}</textarea>
            </div>
            <div class="bg-slate-900/50 p-6 rounded-2xl border border-slate-800">
                <label class="block text-xs font-bold text-emerald-400 uppercase mb-2"><i class="fa-solid fa-envelope-open-text"></i> Template E-mail Corporativo (Recuperação de Senha)</label>
                <textarea name="cfg[tpl_email]" rows="6" class="font-mono text-[11px] text-green-400 w-full bg-slate-950 p-4 rounded-xl">{{ $dados['configs']['tpl_email'] ?? '' }}</textarea>
            </div>
        </div>
        
        <div class="mt-8 pt-8 border-t border-slate-800 flex justify-end">
            <button class="bg-blue-600 hover:bg-blue-500 transition-all px-12 py-4 text-white font-black rounded-xl tracking-widest shadow-xl shadow-blue-500/20"><i class="fa-solid fa-save mr-2"></i> GRAVAR ECOSSISTEMA</button>
        </div>
    </form>
</div>
@endsection
