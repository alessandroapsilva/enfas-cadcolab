<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal do Colaborador | ENFAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #f3f4f6; font-family: 'Arial', sans-serif; }
    </style>
</head>
<body class="flex flex-col min-h-screen">
    
    <header class="bg-[#004B87] border-b-[5px] border-[#00A3E0] shadow-md py-4 px-6 flex items-center justify-between">
        <div class="flex items-center gap-4 max-w-5xl mx-auto w-full">
            <img src="https://autoatendimento.enfas.com.br/Content/images/logo_enfas.png" alt="ENFAS" class="h-10 bg-white px-2 py-1 rounded">
            <h1 class="text-white text-lg sm:text-xl font-normal tracking-wide">Autenticação Central</h1>
        </div>
    </header>

    <main class="flex-grow flex items-center justify-center p-4">
        <div class="w-full max-w-[420px]">
            
            <div class="bg-white border border-gray-200 rounded shadow-lg overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <h2 class="text-[#004B87] font-bold text-lg">Acessar minha conta</h2>
                </div>
                
                <div class="p-6">
                    @if(session('erro'))
                        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 text-sm mb-5 rounded shadow-sm">
                            {{ session('erro') }}
                        </div>
                    @endif

                    <form action="{{ route('portal.autenticar') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-gray-700 text-xs font-bold mb-2">Usuário ou CPF</label>
                            <input type="text" name="usuario" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#00A3E0] focus:ring-1 focus:ring-[#00A3E0] transition-colors" required autocomplete="off">
                        </div>
                        <div class="mb-6">
                            <label class="block text-gray-700 text-xs font-bold mb-2">Senha</label>
                            <input type="password" name="senha" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#00A3E0] focus:ring-1 focus:ring-[#00A3E0] transition-colors">
                        </div>
                        <button type="submit" class="w-full bg-[#004B87] hover:bg-[#003366] text-white font-bold py-2.5 rounded transition-colors text-sm shadow">
                            Entrar
                        </button>
                    </form>
                    
                    <div class="mt-6 border-t border-gray-100 pt-4 text-center text-xs">
                        <a href="#" class="text-[#004B87] hover:underline">Esqueci minha senha</a>
                        <span class="text-gray-300 mx-2">|</span>
                        <button onclick="document.getElementById('setup').classList.remove('hidden')" class="text-[#004B87] font-bold hover:underline">
                            Primeiro Acesso
                        </button>
                    </div>
                </div>
            </div>

            <div id="setup" class="hidden mt-6 bg-white border border-[#00A3E0] rounded shadow-lg overflow-hidden">
                <div class="bg-[#f0f9ff] border-b border-[#00A3E0]/30 px-6 py-4">
                    <h3 class="text-[#004B87] font-bold text-sm">Criação de Conta Cloud (M365/Google)</h3>
                </div>
                <div class="p-6">
                    @if(session('sucesso_criacao'))
                        <div class="bg-green-50 border border-green-200 text-green-700 p-3 text-sm mb-4 rounded font-medium shadow-sm">
                            {!! session('sucesso_criacao') !!}
                        </div>
                    @endif
                    <form action="{{ route('portal.criar_usuario') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-gray-700 text-xs font-bold mb-2">Seu CPF</label>
                            <input type="text" name="cpf_novo" placeholder="Apenas números" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#00A3E0]" required>
                        </div>
                        <div class="mb-5">
                            <label class="block text-gray-700 text-xs font-bold mb-2">Prefixo Desejado</label>
                            <div class="flex items-center">
                                <input type="text" name="prefixo" placeholder="nome.sobrenome" class="w-full border border-gray-300 rounded-l px-3 py-2 text-sm focus:outline-none focus:border-[#00A3E0]" required>
                                <span class="bg-gray-100 border border-gray-300 border-l-0 rounded-r px-3 py-2 text-sm text-gray-500">@enfas.com.br</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" onclick="document.getElementById('setup').classList.add('hidden')" class="w-1/3 bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 font-bold py-2 rounded transition-colors text-xs">Cancelar</button>
                            <button type="submit" class="w-2/3 bg-[#00A3E0] hover:bg-[#008CBA] text-white font-bold py-2 rounded transition-colors text-xs shadow">Sincronizar Cloud</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    @if(session('sucesso_criacao') || session('erro_criacao'))
    <script>document.getElementById('setup').classList.remove('hidden');</script>
    @endif
</body>
</html>
