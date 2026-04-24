<?php
ob_start();
session_start();
date_default_timezone_set('America/Sao_Paulo');
ini_set('display_errors', 0); error_reporting(0); mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once '../../autoatendimento/config/database.php';
$conn->query("SET time_zone = '-03:00'");

function registrarLog($conn, $usuario, $acao, $detalhes) { 
    $hash = 'ICP-BR.' . strtoupper(hash('sha256', uniqid(rand(), true) . $acao . time())); 
    $stmt = $conn->prepare("INSERT INTO logs_auditoria (usuario_admin, acao, detalhes, codigo_controle) VALUES (?, ?, ?, ?)"); 
    $stmt->bind_param("ssss", $usuario, $acao, $detalhes, $hash); $stmt->execute(); 
}

function registrarErro($conn, $modulo, $mensagem) {
    $stmt = $conn->prepare("INSERT INTO logs_erros (modulo, mensagem) VALUES (?, ?)");
    $stmt->bind_param("ss", $modulo, $mensagem); $stmt->execute();
}

function enviarWhatsApp($conn, $telefone, $mensagem_texto) {
    $cfg = []; $res = $conn->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('wp_token', 'wp_phone_id')"); 
    while($row = $res->fetch_assoc()) { $cfg[$row['chave']] = trim($row['valor']); }
    if(empty($cfg['wp_token']) || empty($cfg['wp_phone_id'])) return false;
    
    $telefone_formatado = '55' . preg_replace('/[^0-9]/', '', $telefone);
    $payload = json_encode([
        "messaging_product" => "whatsapp",
        "to" => $telefone_formatado,
        "type" => "text",
        "text" => ["body" => $mensagem_texto]
    ]);
    
    $ch = curl_init("https://graph.facebook.com/v18.0/" . $cfg['wp_phone_id'] . "/messages");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $cfg['wp_token'], "Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POST, true); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resp = curl_exec($ch); 
    curl_close($ch);
    return true;
}

function gerenciarAcessoM365($conn, $email_m365, $acao = 'bloquear', $nova_senha = null, $dados_sync = []) { 
    $cfg = []; $res = $conn->query("SELECT chave, valor FROM configuracoes"); 
    while($row = $res->fetch_assoc()) { $cfg[$row['chave']] = trim($row['valor']); } 
    if(empty($cfg['m365_tenant'])) return ['sucesso'=>false, 'erro'=>'Chaves ausentes.']; 
    
    $ch = curl_init("https://login.microsoftonline.com/{$cfg['m365_tenant']}/oauth2/v2.0/token"); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => $cfg['m365_client'], 
        'scope' => 'https://graph.microsoft.com/.default', 
        'client_secret' => $cfg['m365_secret'], 
        'grant_type' => 'client_credentials'
    ])); 
    $token_resp = json_decode(curl_exec($ch), true); 
    curl_close($ch); 
    
    if(!isset($token_resp['access_token'])) { 
        registrarErro($conn, 'M365 Auth', "Falha ao obter Token."); 
        return ['sucesso'=>false, 'erro'=>"Falha no Token."]; 
    }
    
    $token = $token_resp['access_token']; 
    if($acao == 'check') return ['sucesso'=>true];
    
    $ch2 = curl_init("https://graph.microsoft.com/v1.0/users/$email_m365"); 
    $metodo = "PATCH"; $payload = [];
    
    if($acao == 'excluir') { 
        $metodo = "DELETE"; 
    } elseif($acao == 'reset_senha') { 
        $payload = ['passwordProfile' => ['forceChangePasswordNextSignIn' => false, 'password' => $nova_senha]]; 
    } elseif($acao == 'atualizar') {
        if(!empty($dados_sync['nome'])) $payload['displayName'] = $dados_sync['nome'];
        if(!empty($dados_sync['cargo'])) $payload['jobTitle'] = $dados_sync['cargo'];
        if(!empty($dados_sync['setor'])) $payload['department'] = $dados_sync['setor'];
        if(!empty($dados_sync['unidade'])) $payload['officeLocation'] = $dados_sync['unidade'];
        if(!empty($dados_sync['telefone'])) $payload['mobilePhone'] = "+55" . preg_replace('/[^0-9]/', '', $dados_sync['telefone']);
        $payload['companyName'] = "Clínica ENFAS"; 
        $payload['usageLocation'] = "BR"; 
        if(isset($dados_sync['status'])) $payload['accountEnabled'] = ($dados_sync['status'] == 'ativo');
    } else { 
        $payload = ['accountEnabled' => ($acao == 'ativar')]; 
    }
    
    curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, $metodo); 
    if(!empty($payload)) curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch2, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]); 
    $resp_graph = curl_exec($ch2); 
    $http_code = curl_getinfo($ch2, CURLINFO_HTTP_CODE); 
    curl_close($ch2); 
    
    if($http_code >= 200 && $http_code < 300) {
        if($acao == 'atualizar' && isset($dados_sync['m365_perfil'])) {
            $sku_basic = trim($cfg['m365_sku_basic'] ?? '');
            if(!empty($sku_basic)) {
                $add = []; $remove = [];
                if($dados_sync['m365_perfil'] == 'Basic') { $add[] = ["skuId" => $sku_basic]; } 
                else { $remove[] = $sku_basic; }
                
                $ch_lic = curl_init("https://graph.microsoft.com/v1.0/users/$email_m365/assignLicense");
                curl_setopt($ch_lic, CURLOPT_CUSTOMREQUEST, "POST"); 
                curl_setopt($ch_lic, CURLOPT_POSTFIELDS, json_encode(['addLicenses' => $add, 'removeLicenses' => $remove]));
                curl_setopt($ch_lic, CURLOPT_RETURNTRANSFER, true); 
                curl_setopt($ch_lic, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]); 
                curl_exec($ch_lic); curl_close($ch_lic);
            }
        }
        return ['sucesso'=>true]; 
    }
    registrarErro($conn, 'M365 Sync', "Erro HTTP $http_code."); 
    return ['sucesso'=>false, 'erro'=>"HTTP $http_code"]; 
}

// LOGIN
if(isset($_POST['login'])){ 
    $u=trim($_POST['u']); $p=trim($_POST['p']); 
    if($u=='admin' && $p=='Enfas@2026'){ 
        $_SESSION['auth']=['nome'=>'Administrador Master','perfil'=>'TI']; 
        header("Location: ?p=dashboard"); exit; 
    } else { 
        $stmt=$conn->prepare("SELECT * FROM usuarios_admin WHERE usuario=?"); 
        $stmt->bind_param("s", $u); $stmt->execute(); 
        $res=$stmt->get_result()->fetch_assoc(); 
        if($res && password_verify($p, $res['senha'])){ 
            $_SESSION['auth']=$res; header("Location: ?p=dashboard"); exit; 
        } else { 
            $erro_login = "Credenciais inválidas."; 
        } 
    } 
}

if(isset($_GET['logout'])){ session_destroy(); header("Location: /"); exit; }

$aviso_login = $conn->query("SELECT valor FROM configuracoes WHERE chave='aviso_login'")->fetch_assoc()['valor'] ?? '';

if(!isset($_SESSION['auth'])): ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>CadColab | ENFAS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #121212; font-family: 'Segoe UI', sans-serif; display: flex; flex-direction:column; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding:20px;}
        .login-wrapper { display: flex; background: #1a1d21; border-radius: 12px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); overflow: hidden; width: 100%; max-width: 900px; }
        .login-form { padding: 60px 50px; width: 50%; display: flex; flex-direction: column; justify-content: center; }
        .login-info { padding: 50px; width: 50%; background: #008bb9; color: white; display: flex; flex-direction: column; justify-content: center; }
        .input-group-custom { display: flex; align-items: center; border: 1px solid #333; border-radius: 8px; background: #24282c; margin-bottom: 20px; transition: 0.3s; }
        .input-group-custom i { padding: 0 15px; color: #888; }
        .input-group-custom input { border: none; background: transparent; padding: 15px 15px 15px 0; width: 100%; outline: none; font-size: 15px; color: #fff; }
        .input-group-custom input::placeholder { color: #888; }
        .btn-login { background: #00b4d8; color: white; padding: 15px; border: none; border-radius: 8px; width: 100%; font-weight: bold; font-size: 16px; transition: 0.3s; }
        .btn-login:hover { background: #0096b8; }
        @media (max-width: 768px) { .login-wrapper { flex-direction: column; } .login-form, .login-info { width: 100%; padding: 30px; } }
    </style>
</head>
<body>
    <?php if(!empty($aviso_login)): ?>
        <div class="alert alert-warning py-3 fw-bold text-center" style="max-width:900px; width:100%; margin-bottom:20px; border-radius:12px;">
            <i class="fa-solid fa-bullhorn me-2"></i> <?=$aviso_login?>
        </div>
    <?php endif; ?>
    
    <div class="login-wrapper">
        <div class="login-form">
            <div class="text-center mb-4">
                <img src="https://autoatendimento.enfas.com.br/Content/images/logo_enfas.png" style="max-width: 150px; margin-bottom: 20px; filter: brightness(0) invert(1);">
                <h6 class="fw-bold" style="color:#fff; letter-spacing:1px; line-height:1.5;">
                    SISTEMA INTERNO DE CADASTRO<br>DE COLABORADORES ENFAS
                </h6>
            </div>
            <?php if(isset($erro_login)) echo "<div class='alert alert-danger py-2 small fw-bold text-center'>$erro_login</div>"; ?>
            <form method="POST">
                <div class="input-group-custom">
                    <i class="fa-solid fa-user"></i>
                    <input name="u" placeholder="Usuário" required autocomplete="off">
                </div>
                <div class="input-group-custom">
                    <i class="fa-solid fa-lock"></i>
                    <input name="p" type="password" placeholder="Senha Corporativa" required>
                </div>
                <button name="login" class="btn-login mt-2">Acessar Sistema</button>
            </form>
        </div>
        <div class="login-info">
            <h3 class="fw-bold mb-4"><i class="fa-solid fa-shield-halved me-2"></i> Orientações de Segurança</h3>
            <div class="mb-4">
                <h6 class="fw-bold text-warning mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> Acesso Restrito</h6>
                <p class="small text-white-50">Área exclusiva para Diretoria, RH e TI da Clínica ENFAS.</p>
            </div>
            <div class="mb-4">
                <h6 class="fw-bold text-warning mb-1"><i class="fa-solid fa-eye me-1"></i> Auditoria de Logs</h6>
                <p class="small text-white-50">Modificações são monitoradas ativamente.</p>
            </div>
            <div class="d-flex align-items-center gap-3 mt-4 pt-4 border-top border-light border-opacity-25">
                <i class="fa-solid fa-certificate fa-2x text-white"></i>
                <div style="font-size:11px;">
                    <b class="text-white">PROTEGIDO POR CRIPTOGRAFIA</b><br>
                    <span class="text-white-50">Ambiente em conformidade com as normas ICP-Brasil (SHA-256) e LGPD.</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php exit; endif; ?>
