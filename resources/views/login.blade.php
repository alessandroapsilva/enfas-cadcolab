<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Login - Gestão ENFAS IAM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; display: flex; height: 100vh; background-color: #1a1a1a; }
        
        .split-layout { display: flex; width: 100%; height: 100%; }
        
        /* Lado Escuro (Formulário) */
        .left-side { flex: 1; background-color: #1e1e1e; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 40px; }
        .logo-container { margin-bottom: 40px; text-align: center; }
        .logo-container img { height: 60px; filter: brightness(0) invert(1); }
        .sys-title { color: #fff; font-size: 18px; font-weight: bold; margin-bottom: 30px; letter-spacing: 1px; text-align: center; }
        
        .login-form { width: 100%; max-width: 350px; }
        .input-group { position: relative; margin-bottom: 20px; }
        .input-group i { position: absolute; left: 15px; top: 15px; color: #888; font-size: 16px; }
        .input-group input { width: 100%; background: #2a2a2a; border: 1px solid #444; color: #fff; padding: 14px 15px 14px 45px; border-radius: 6px; box-sizing: border-box; font-size: 14px; outline: none; transition: 0.2s; }
        .input-group input:focus { border-color: #00a2e8; background: #333; }
        
        .btn-login { width: 100%; background-color: #00a2e8; color: white; border: none; padding: 14px; border-radius: 6px; font-size: 15px; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-login:hover { background-color: #008bca; }
        
        .error-msg { background: #3f1515; border: 1px solid #a32a32; color: #ff8a8a; padding: 12px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; text-align: center; }

        /* Lado Azul (Avisos HC Style) */
        .right-side { flex: 1; background-color: #008bca; display: flex; flex-direction: column; justify-content: center; padding: 60px; color: white; }
        .right-content { max-width: 450px; margin: 0 auto;}
        .right-title { font-size: 32px; font-weight: bold; margin: 0 0 30px 0; display: flex; align-items: center; gap: 15px; }
        
        .info-item { margin-bottom: 30px; }
        .info-item h4 { margin: 0 0 5px 0; font-size: 18px; color: #ffd700; display: flex; align-items: center; gap: 10px; }
        .info-item p { margin: 0; font-size: 14px; color: rgba(255,255,255,0.9); line-height: 1.5; }
        
        /* CAIXA DE AVISO IDÊNTICA AO DO HC */
        .hc-alert-box { background-color: #172a45; border: 1px solid #1e293b; padding: 20px; border-radius: 6px; margin-top: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .hc-alert-box strong { color: #00a2e8; font-size: 16px; display: block; margin-bottom: 8px; letter-spacing: 0.5px; }
        .hc-alert-box p { color: #8892b0; font-size: 14px; margin: 0; line-height: 1.5; }
        .hc-alert-box a { color: #64ffda; text-decoration: none; font-weight: bold; }
        .hc-alert-box a:hover { text-decoration: underline; }

        @media (max-width: 768px) { .split-layout { flex-direction: column; } .left-side, .right-side { width: 100%; height: auto; padding: 40px 20px; } }
    </style>
</head>
<body>
    <div class="split-layout">
        <div class="left-side">
            <div class="login-form">
                <div class="logo-container">
                    <img src="/Content/images/logo_enfas.png" onerror="this.outerHTML='<h1 style=\'color:#00a2e8; font-size:40px; margin:0;\'>ENFAS</h1>'">
                </div>
                <div class="sys-title">SISTEMA INTERNO DE CADASTRO<br>DE COLABORADORES ENFAS</div>
                
                @if(session('erro')) <div class="error-msg"><i class="fa-solid fa-triangle-exclamation"></i> {{ session('erro') }}</div> @endif
                
                <form method="POST" action="/login">
                    @csrf
                    <div class="input-group">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="u" placeholder="Usuário Admin" required autofocus>
                    </div>
                    <div class="input-group">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="p" placeholder="Senha" required>
                    </div>
                    <button type="submit" class="btn-login">Acessar Sistema</button>
                </form>
            </div>
        </div>

        <div class="right-side">
            <div class="right-content">
                <h1 class="right-title"><i class="fa-solid fa-shield-halved"></i> Orientações de Segurança</h1>
                <div class="info-item">
                    <h4><i class="fa-solid fa-triangle-exclamation"></i> Acesso Restrito</h4>
                    <p>Área exclusiva para Diretoria, RH e TI da Clínica ENFAS.</p>
                </div>
                <div class="info-item">
                    <h4><i class="fa-solid fa-eye"></i> Auditoria de Logs</h4>
                    <p>Modificações são monitoradas ativamente.</p>
                </div>
                <div class="info-item" style="margin-top: 50px;">
                    <h4 style="color: white; font-size: 14px;"><i class="fa-solid fa-certificate"></i> PROTEGIDO POR CRIPTOGRAFIA</h4>
                    <p style="font-size: 12px; color: rgba(255,255,255,0.7);">Ambiente em conformidade com as normas ICP-Brasil e LGPD.</p>
                </div>
                <div class="hc-alert-box">
                    <strong>ATENÇÃO!</strong>
                    <p>Caso não possua usuário corporativo ou tenha esquecido a senha corporativa, acesse o link <a href="https://autoatendimento.enfas.com.br" target="_blank">autoatendimento</a> para criação e redefinição do mesmo.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
