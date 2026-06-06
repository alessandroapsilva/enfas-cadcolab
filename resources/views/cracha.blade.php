<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Crachá - {{ $c->nome_completo ?? 'Colaborador' }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #e2e8f0; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
        
        .cracha-container { width: 340px; height: 540px; background: #fff; border-radius: 20px; position: relative; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.15); display: flex; flex-direction: column; }
        
        /* CABEÇALHO BRANCO */
        .cracha-top { background: #fff; padding: 25px 25px 15px 25px; position: relative; z-index: 2; display: flex; align-items: center; gap: 15px; }
        .cracha-top img.logo { height: 45px; }
        .cracha-top .titles { display: flex; flex-direction: column; }
        .cracha-top h1 { margin: 0; color: #002d72; font-size: 26px; font-weight: 900; letter-spacing: -0.5px; }
        .cracha-top h2 { margin: 0; color: #0d6efd; font-size: 10px; font-weight: 800; letter-spacing: 1px; }
        .cracha-top p { margin: 2px 0 0 0; font-size: 9px; color: #64748b; line-height: 1.2; }
        .watermark-cross { position: absolute; right: 20px; top: 20px; opacity: 0.1; font-size: 60px; color: #0d6efd; }

        /* FUNDO AZUL CIDADE E FOTO */
        .cracha-middle { flex: 1; background: linear-gradient(to bottom, rgba(13,110,253,0.8), #002d72), url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=600&auto=format&fit=crop') center/cover; position: relative; display: flex; flex-direction: column; align-items: center; padding-top: 25px; }
        .cracha-middle::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 100%; background: radial-gradient(circle at center, transparent 0%, rgba(0,45,114,0.9) 100%); pointer-events: none; }
        
        .photo-container { position: relative; z-index: 3; width: 140px; height: 160px; border-radius: 12px; border: 4px solid #fff; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.3); background: #f1f5f9; }
        .photo-container img { width: 100%; height: 100%; object-fit: cover; }

        /* DADOS DO COLABORADOR */
        .colab-data { position: relative; z-index: 3; text-align: center; color: #fff; width: 100%; padding: 15px 20px; }
        .colab-name { font-size: 22px; font-weight: 800; margin: 0 0 15px 0; line-height: 1.1; text-shadow: 0 2px 5px rgba(0,0,0,0.5); }
        .colab-unit { display: inline-flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 500; margin-bottom: 12px; }
        .divider { width: 30px; height: 2px; background: #0d6efd; margin: 0 auto 10px auto; }
        .colab-role { font-size: 16px; font-weight: 800; color: #38bdf8; margin: 0; }
        .colab-team { font-size: 12px; color: #cbd5e1; margin: 5px 0 0 0; }

        /* RODAPÉ BRANCO COM QR CODE */
        .cracha-bottom { background: #fff; height: 90px; padding: 0 20px; display: flex; align-items: center; justify-content: space-between; position: relative; z-index: 2; }
        .cracha-bottom .left-info { display: flex; align-items: center; gap: 10px; }
        .cracha-bottom .icon-shield { color: #0d6efd; font-size: 24px; }
        .cracha-bottom .text-mission { font-size: 8px; font-weight: 700; color: #0f172a; border-left: 1px solid #e2e8f0; padding-left: 10px; line-height: 1.4; }
        .cracha-bottom .qr-box { display: flex; align-items: center; gap: 15px; }
        .id-badge { text-align: center; }
        .id-badge i { font-size: 24px; color: #0d6efd; margin-bottom: 2px; }
        .id-badge span { display: block; font-size: 9px; font-weight: 700; color: #475569; }
        .qr-code { width: 55px; height: 55px; background: #000; border-radius: 4px; display: flex; align-items: center; justify-content: center; position: relative; }
        .qr-code img { width: 100%; height: 100%; }
        .qr-center { position: absolute; width: 15px; height: 15px; background: #fff; border-radius: 3px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 900; color: #0d6efd; }
    </style>
</head>
<body>

    @php
        $c = \Illuminate\Support\Facades\DB::table('pre_registros')
            ->leftJoin('unidades', 'unidade_id', '=', 'unidades.id')
            ->leftJoin('cargos', 'cargo_id', '=', 'cargos.id')
            ->select('pre_registros.*', 'unidades.nome as un', 'cargos.nome as crg')
            ->where('pre_registros.id', request()->route('id'))
            ->first();
        if(!$c) die("Colaborador não encontrado.");
        
        $foto = $c->foto_path ? '/'.$c->foto_path : 'https://ui-avatars.com/api/?name='.urlencode($c->nome_completo).'&background=f1f5f9&color=0d6efd&size=300';
    @endphp

    <div class="cracha-container">
        <!-- HEADER -->
        <div class="cracha-top">
            <img src="/Content/images/logo_enfas.png" alt="Logo" class="logo" onerror="this.outerHTML='<i class=\'fa-solid fa-hospital-user\' style=\'font-size:40px; color:#0d6efd;\'></i>'">
            <div class="titles">
                <h1>ENFAS</h1>
                <h2>CADCOLAB</h2>
                <p>Sistema Interno de Cadastro<br>de Colaboradores</p>
            </div>
            <i class="fa-solid fa-truck-medical watermark-cross"></i>
        </div>

        <!-- MIDDLE (FOTO E DADOS) -->
        <div class="cracha-middle">
            <div class="photo-container">
                <img src="{{ $foto }}" alt="Foto Colaborador">
            </div>
            <div class="colab-data">
                <h2 class="colab-name">{{ $c->nome_completo }}</h2>
                <div class="colab-unit"><i class="fa-regular fa-building"></i> {{ $c->un ?: 'Unidade Principal' }}</div>
                <div class="divider"></div>
                <h3 class="colab-role">{{ $c->crg ?: 'Colaborador' }}</h3>
                <p class="colab-team">Equipe ENFAS</p>
            </div>
        </div>

        <!-- FOOTER (QR CODE) -->
        <div class="cracha-bottom">
            <div class="left-info">
                <i class="fa-solid fa-shield-halved icon-shield"></i>
                <div class="text-mission">CUIDAR DE PESSOAS.<br>CONECTAR SOLUÇÕES.<br>TRANSFORMAR REALIDADES.</div>
            </div>
            <div class="qr-box">
                <div class="id-badge">
                    <i class="fa-regular fa-id-card"></i>
                    <span>ID ENF{{ $c->id }}{{ date('Y') }}</span>
                </div>
                <div class="qr-code">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=ENFAS-{{ $c->cpf }}" alt="QR">
                    <div class="qr-center">E</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
