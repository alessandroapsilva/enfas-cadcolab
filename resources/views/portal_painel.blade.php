<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Intranet ENFAS | Autoatendimento</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">
    <header class="bg-[#004B87] text-white p-4 shadow-lg flex justify-between items-center border-b-[5px] border-[#00A3E0]">
        <div class="flex items-center gap-4 max-w-6xl mx-auto w-full">
            <img src="https://autoatendimento.enfas.com.br/Content/images/logo_enfas.png" class="h-10 bg-white p-1 rounded">
            <h1 class="text-xl font-normal">Autoatendimento Colaborador</h1>
            <div class="ml-auto flex items-center gap-4">
                <div class="text-right text-xs"><p class="font-bold">{{ $colab->nome_completo }}</p><p class="text-blue-200">{{ $colab->nome_cargo }}</p></div>
                <a href="/portal/logout" class="bg-white/10 hover:bg-white/20 p-2 rounded px-4 text-xs font-bold transition">Sair</a>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto p-6 mt-6 grid grid-cols-1 md:grid-cols-12 gap-8">
        <div class="md:col-span-4 space-y-6">
            <div class="bg-white rounded shadow p-8 text-center border-t-4 border-blue-600">
                <div class="w-24 h-24 mx-auto bg-gray-100 rounded-full mb-4 flex items-center justify-center border-4 border-white shadow-inner"><i class="fa-solid fa-user text-4xl text-gray-300"></i></div>
                <h2 class="font-bold text-gray-800 uppercase leading-tight">{{ $colab->nome_completo }}</h2>
                <p class="text-blue-600 text-xs font-bold uppercase mt-1">{{ $colab->nome_cargo ?? 'Colaborador' }}</p>
                <div class="mt-6 text-left space-y-2 bg-gray-50 p-4 rounded text-xs border border-gray-100">
                    <p class="flex justify-between border-b pb-1"><b>Unidade:</b> <span>{{ $colab->nome_unidade }}</span></p>
                    <p class="flex justify-between border-b pb-1"><b>E-mail:</b> <span class="text-blue-600 font-bold">{{ $colab->username_criado }}</span></p>
                    <p class="flex justify-between"><b>Escala:</b> <span>{{ $colab->jornada_escala }}</span></p>
                </div>
                <a href="/cracha/{{ $colab->id }}" target="_blank" class="block mt-6 bg-gray-800 text-white font-bold py-2 rounded text-xs uppercase hover:bg-black transition">Visualizar Crachá</a>
            </div>
        </div>

        <div class="md:col-span-8 space-y-8">
            <div class="bg-white rounded shadow p-6 border border-gray-200">
                <h3 class="font-bold text-[#004B87] border-b pb-2 mb-6 uppercase text-sm"><i class="fa-solid fa-laptop-medical mr-2"></i> Meus Acessos Corporativos</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <a href="https://outlook.office.com" target="_blank" class="bg-gray-50 p-6 border rounded text-center hover:bg-gray-100 transition group">
                        <i class="fa-brands fa-microsoft text-3xl text-gray-300 group-hover:text-blue-600 mb-2"></i>
                        <p class="text-[10px] font-bold text-gray-600">Webmail M365</p>
                    </a>
                    <a href="https://monday.com" target="_blank" class="bg-gray-50 p-6 border rounded text-center hover:bg-gray-100 transition group">
                        <i class="fa-solid fa-cart-shopping text-3xl text-gray-300 group-hover:text-purple-600 mb-2"></i>
                        <p class="text-[10px] font-bold text-gray-600">Fluxo Compras</p>
                    </a>
                    <a href="#" class="bg-gray-50 p-6 border rounded text-center hover:bg-gray-100 transition group">
                        <i class="fa-solid fa-headset text-3xl text-gray-300 group-hover:text-amber-500 mb-2"></i>
                        <p class="text-[10px] font-bold text-gray-600">Suporte TI</p>
                    </a>
                    @foreach($sistemas as $sys)
                    <a href="{{ $sys->link }}" target="_blank" class="bg-gray-50 p-6 border rounded text-center hover:bg-gray-100 transition group">
                        <i class="fa-solid fa-link text-3xl text-gray-300 group-hover:text-blue-500 mb-2"></i>
                        <p class="text-[10px] font-bold text-gray-600">{{ $sys->nome }}</p>
                    </a>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded shadow p-6 border border-gray-200">
                <h3 class="font-bold text-[#004B87] border-b pb-2 mb-6 uppercase text-sm"><i class="fa-solid fa-signature mr-2"></i> Minha Assinatura Corporativa</h3>
                <div class="bg-gray-50 p-8 border rounded flex justify-center mb-4">
                    <div id="assinatura-box" class="bg-white p-4 shadow-sm border border-gray-100">{!! $assinatura !!}</div>
                </div>
                <button onclick="copiarAssinatura()" class="bg-emerald-600 text-white font-bold py-3 w-full rounded text-xs uppercase hover:bg-emerald-700 shadow-lg transition">Copiar para o Outlook</button>
            </div>
        </div>
    </main>
    <script>function copiarAssinatura() { const r = document.createRange(); r.selectNode(document.getElementById('assinatura-box')); window.getSelection().removeAllRanges(); window.getSelection().addRange(r); document.execCommand('copy'); alert('Assinatura copiada!'); }</script>
</body>
</html>
