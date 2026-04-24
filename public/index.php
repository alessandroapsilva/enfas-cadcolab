<?php
ob_start();
session_start();
date_default_timezone_set('America/Sao_Paulo');
ini_set('display_errors', 0); error_reporting(0); mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// === CONTROLE DE VERSÃO DO SISTEMA (Altere aqui a cada atualização) ===
define('SYS_VERSION', 'v1.0.0');
define('SYS_UPDATE', '23 Abr 2026');
// =====================================================================

require_once '../../autoatendimento/config/database.php';
$conn->query("SET time_zone = '-03:00'");

// AUTO CORREÇÕES E BLINDAGEM DO BANCO DE DADOS
$check_col = $conn->query("SHOW COLUMNS FROM pre_registros LIKE 'dados_extras'");
if($check_col && $check_col->num_rows == 0) { $conn->query("ALTER TABLE pre_registros ADD COLUMN dados_extras TEXT NULL"); }
$check_col2 = $conn->query("SHOW COLUMNS FROM usuarios_admin LIKE 'pre_registro_id'");
if($check_col2 && $check_col2->num_rows == 0) { $conn->query("ALTER TABLE usuarios_admin ADD COLUMN pre_registro_id INT NULL"); }
$check_col3 = $conn->query("SHOW COLUMNS FROM unidades LIKE 'template_assinatura'");
if($check_col3 && $check_col3->num_rows == 0) { $conn->query("ALTER TABLE unidades ADD COLUMN template_assinatura TEXT NULL"); }
$check_col4 = $conn->query("SHOW COLUMNS FROM pre_registros LIKE 'm365_oof_ativo'");
if($check_col4 && $check_col4->num_rows == 0) { $conn->query("ALTER TABLE pre_registros ADD COLUMN m365_oof_ativo INT DEFAULT 0"); }

$check_tpl1 = $conn->query("SELECT id FROM configuracoes WHERE chave='tpl_cracha'");
if($check_tpl1 && $check_tpl1->num_rows == 0) { $conn->query("INSERT INTO configuracoes (chave, valor) VALUES ('tpl_cracha', '<div style=\"width:220px;height:350px;background:white;padding:20px;border-radius:12px;box-shadow:0 10px 20px rgba(0,0,0,0.15);text-align:center;border:1px solid #ccc;\"><h4 style=\"color:#008bb9;margin:0 0 15px 0;\">+ ENFAS</h4><img src=\"{FOTO}\" style=\"width:110px;height:110px;border-radius:50%;object-fit:cover;margin:0 auto 15px;border:3px solid #008bb9;\"><h3 style=\"margin:0 0 5px 0;font-size:16px;color:#333;text-transform:uppercase;\">{NOME}</h3><p style=\"margin:0;font-size:12px;color:#777;\">{CARGO}</p><p style=\"margin-top:15px;font-weight:bold;\">ID: {ID}</p></div>')"); }

$check_tpl_email = $conn->query("SELECT id FROM configuracoes WHERE chave='tpl_email'");
if($check_tpl_email && $check_tpl_email->num_rows == 0) { 
    $default_email = "<div style=\"background-color:#1a1a1a; padding:40px 20px; font-family:Arial, sans-serif; color:#ccc; text-align:center;\"><img src=\"https://autoatendimento.enfas.com.br/Content/images/logo_enfas.png\" style=\"max-width:200px; margin-bottom:30px;\" alt=\"ENFAS\"><div style=\"max-width:500px; margin:0 auto; text-align:left;\"><h2 style=\"color:#00aeef; font-weight:normal; margin-bottom:20px;\">Recuperação de Senha Corporativa</h2><p style=\"color:#00aeef; font-size:16px;\">Você solicitou a alteração de senha para o usuário: <b style=\"color:#fff;\">{USUARIO}</b></p><p style=\"color:#ccc; font-size:16px; margin-top:30px;\">Para definir uma nova senha com segurança, clique no botão abaixo:</p><div style=\"text-align:center; margin:30px 0;\"><a href=\"{LINK}\" style=\"background-color:#00aeef; color:#fff; padding:15px 40px; text-decoration:none; border-radius:2px; font-size:16px; font-weight:bold; display:inline-block;\">Recadastrar Senha</a></div><p style=\"color:#777; font-size:12px; margin-top:40px; word-break:break-all;\">Se o botão não funcionar, copie este link: {LINK}</p><div style=\"background-color:#00aeef; padding:20px; margin-top:30px; text-align:center;\"><p style=\"color:#fff; margin:0; font-size:14px;\">Atenciosamente,<br><b>TI - Tecnologia da Informação</b></p></div></div></div>";
    $conn->query("INSERT INTO configuracoes (chave, valor) VALUES ('tpl_email', '$default_email')"); 
}

$cfg_global = [];
$q_configs = $conn->query("SELECT chave, valor FROM configuracoes");
if($q_configs) { while($row = $q_configs->fetch_assoc()){ $cfg_global[$row['chave']] = trim($row['valor']); } }

// AUDITORIA ICP-BR (CÓDIGO DE CONTROLE MATEMÁTICO)
function registrarLog($conn, $usuario, $acao, $detalhes) { 
    $letras_usuario = strlen(preg_replace('/[^a-zA-Z]/', '', $usuario));
    $soma_tudo = (int)date('Y') + (int)date('m') + (int)date('d') + (int)date('H') + (int)date('i') + (int)date('s') + $letras_usuario;
    $codigo_controle = 'ICP-BR.' . date('YmdHis') . '.' . $soma_tudo; 
    
    $stmt = $conn->prepare("INSERT INTO logs_auditoria (usuario_admin, acao, detalhes, codigo_controle) VALUES (?, ?, ?, ?)"); 
    $stmt->bind_param("ssss", $usuario, $acao, $detalhes, $codigo_controle); 
    $stmt->execute(); 
}

function registrarErro($conn, $modulo, $mensagem) {
    $stmt = $conn->prepare("INSERT INTO logs_erros (modulo, mensagem) VALUES (?, ?)");
    $stmt->bind_param("ss", $modulo, $mensagem); $stmt->execute();
}

function sincronizarAssinaturaM365($conn, $email_m365, $html_assinatura, $cfg) {
    if(empty($cfg['m365_tenant'])) return ['sucesso'=>false, 'erro'=>'Chaves ausentes.']; 
    $ch = curl_init("https://login.microsoftonline.com/{$cfg['m365_tenant']}/oauth2/v2.0/token"); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['client_id' => $cfg['m365_client'], 'scope' => 'https://graph.microsoft.com/.default', 'client_secret' => $cfg['m365_secret'], 'grant_type' => 'client_credentials'])); 
    $token_resp = json_decode(curl_exec($ch), true); curl_close($ch); 
    if(!isset($token_resp['access_token'])) return ['sucesso'=>false, 'erro'=>"Falha no Token."]; 
    $token = $token_resp['access_token']; 
    
    $ch2 = curl_init("https://graph.microsoft.com/v1.0/users/$email_m365/mailboxSettings"); 
    $payload = [ "signature" => [ "messageQuoteText" => "", "messageQuoteTextLocation" => "bottom", "signature" => mb_convert_encoding($html_assinatura, 'UTF-8', 'auto'), "type" => "html" ] ];
    curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, "PATCH"); curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch2, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]); 
    $resp_graph = curl_exec($ch2); $http_code = curl_getinfo($ch2, CURLINFO_HTTP_CODE); curl_close($ch2); 
    if($http_code >= 200 && $http_code < 300) return ['sucesso'=>true]; 
    registrarErro($conn, 'M365 Assinatura', "Erro HTTP $http_code: " . $resp_graph); return ['sucesso'=>false, 'erro'=>"HTTP $http_code."]; 
}

function gerenciarAcessoM365($conn, $email_m365, $acao = 'bloquear', $nova_senha = null, $dados_sync = [], $cfg = []) { 
    if(empty($cfg['m365_tenant'])) return ['sucesso'=>false]; 
    $ch = curl_init("https://login.microsoftonline.com/{$cfg['m365_tenant']}/oauth2/v2.0/token"); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['client_id' => $cfg['m365_client'], 'scope' => 'https://graph.microsoft.com/.default', 'client_secret' => $cfg['m365_secret'], 'grant_type' => 'client_credentials'])); 
    $token_resp = json_decode(curl_exec($ch), true); curl_close($ch); 
    if(!isset($token_resp['access_token'])) return ['sucesso'=>false]; 
    $token = $token_resp['access_token']; 
    
    $ch2 = curl_init("https://graph.microsoft.com/v1.0/users/$email_m365"); $metodo = "PATCH"; $payload = [];
    if($acao == 'excluir') { $metodo = "DELETE"; } 
    elseif($acao == 'reset_senha') { $payload = ['passwordProfile' => ['forceChangePasswordNextSignIn' => false, 'password' => $nova_senha]]; } 
    elseif($acao == 'bloquear') { $payload = ['accountEnabled' => false]; } 
    elseif($acao == 'atualizar') {
        if(!empty($dados_sync['nome'])) $payload['displayName'] = $dados_sync['nome'];
        if(!empty($dados_sync['cargo'])) $payload['jobTitle'] = $dados_sync['cargo'];
        if(!empty($dados_sync['setor'])) $payload['department'] = $dados_sync['setor'];
        if(!empty($dados_sync['telefone'])) $payload['mobilePhone'] = "+55" . preg_replace('/[^0-9]/', '', $dados_sync['telefone']);
        $payload['usageLocation'] = "BR"; $payload['accountEnabled'] = true;
    } else { $payload = ['accountEnabled' => ($acao == 'ativar')]; }
    
    curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, $metodo); 
    if(!empty($payload)) curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch2, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]); 
    $resp_graph = curl_exec($ch2); $http_code = curl_getinfo($ch2, CURLINFO_HTTP_CODE); curl_close($ch2); 
    
    if($http_code >= 200 && $http_code < 300) {
        if($acao == 'bloquear') {
            if(!empty($dados_sync['gestor_email'])) {
                $payload_fw = ["forwarding" => ["emailAddress" => ["address" => $dados_sync['gestor_email']], "deliverToMailboxAndForward" => false, "status" => "enabled"]];
                $ch_fw = curl_init("https://graph.microsoft.com/v1.0/users/$email_m365/mailboxSettings");
                curl_setopt($ch_fw, CURLOPT_CUSTOMREQUEST, "PATCH"); curl_setopt($ch_fw, CURLOPT_POSTFIELDS, json_encode($payload_fw));
                curl_setopt($ch_fw, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch_fw, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]); curl_exec($ch_fw); curl_close($ch_fw);
            }
            $sku_basic = trim($cfg['m365_sku_basic'] ?? '');
            if(!empty($sku_basic)) {
                $ch_lic = curl_init("https://graph.microsoft.com/v1.0/users/$email_m365/assignLicense");
                curl_setopt($ch_lic, CURLOPT_CUSTOMREQUEST, "POST"); curl_setopt($ch_lic, CURLOPT_POSTFIELDS, json_encode(['addLicenses' => [], 'removeLicenses' => [$sku_basic]]));
                curl_setopt($ch_lic, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch_lic, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]); curl_exec($ch_lic); curl_close($ch_lic);
            }
        }
        if($acao == 'atualizar') {
            $payload_fw = ["forwarding" => ["status" => "disabled"]];
            $ch_fw = curl_init("https://graph.microsoft.com/v1.0/users/$email_m365/mailboxSettings");
            curl_setopt($ch_fw, CURLOPT_CUSTOMREQUEST, "PATCH"); curl_setopt($ch_fw, CURLOPT_POSTFIELDS, json_encode($payload_fw));
            curl_setopt($ch_fw, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch_fw, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]); curl_exec($ch_fw); curl_close($ch_fw);
        }

        if(isset($dados_sync['m365_perfil']) && ($acao == 'atualizar' || $acao == 'bloquear')) {
            $sku_basic = trim($cfg['m365_sku_basic'] ?? '');
            if(!empty($sku_basic)) {
                $add = []; $remove = [];
                if($acao == 'bloquear' || $dados_sync['m365_perfil'] != 'Basic') { $remove[] = $sku_basic; } else { $add[] = ["skuId" => $sku_basic]; }
                $ch_lic = curl_init("https://graph.microsoft.com/v1.0/users/$email_m365/assignLicense");
                curl_setopt($ch_lic, CURLOPT_CUSTOMREQUEST, "POST"); curl_setopt($ch_lic, CURLOPT_POSTFIELDS, json_encode(['addLicenses' => $add, 'removeLicenses' => $remove]));
                curl_setopt($ch_lic, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch_lic, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]); 
                $resp_lic = curl_exec($ch_lic); $hc_lic = curl_getinfo($ch_lic, CURLINFO_HTTP_CODE); curl_close($ch_lic);
                if($hc_lic >= 400) { $log_msg = json_decode($resp_lic, true)['error']['message'] ?? $resp_lic; registrarErro($conn, 'M365 Licença', "Erro $hc_lic: $log_msg"); }
            }
        }
        return ['sucesso'=>true]; 
    }
    registrarErro($conn, 'M365 Sync', "Erro HTTP $http_code. Resp: $resp_graph"); return ['sucesso'=>false]; 
}

function gerenciarAcessoGoogle($conn, $email_google, $acao = 'bloquear', $nova_senha = null, $dados_sync = [], $cfg = []) {
    if(empty($cfg['gw_domain']) || empty($cfg['gw_json'])) return ['sucesso'=>false];
    $key = @json_decode($cfg['gw_json'], true);
    if(!$key || !isset($key['private_key'])) return ['sucesso'=>false];

    $header = json_encode(['alg'=>'RS256','typ'=>'JWT']);
    $claim = json_encode([ 'iss' => $key['client_email'], 'scope' => 'https://www.googleapis.com/auth/admin.directory.user', 'aud' => $key['token_uri'], 'exp' => time() + 3600, 'iat' => time() ]);
    $b64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $b64Claim = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($claim));
    $sig = ''; openssl_sign($b64Header . "." . $b64Claim, $sig, $key['private_key'], "sha256WithRSAEncryption");
    $b64Sig = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($sig));
    $jwt = $b64Header . "." . $b64Claim . "." . $b64Sig;

    $ch = curl_init($key['token_uri']);
    curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $jwt]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); $token_resp = json_decode(curl_exec($ch), true); curl_close($ch);

    if(!isset($token_resp['access_token'])) return ['sucesso'=>false];
    $token = $token_resp['access_token'];

    $metodo = "PUT"; $endpoint = "https://admin.googleapis.com/admin/directory/v1/users/$email_google"; $payload = [];

    if($acao == 'criar') {
        $metodo = "POST"; $endpoint = "https://admin.googleapis.com/admin/directory/v1/users";
        $partes = explode(' ', $dados_sync['nome'] ?? ''); $primeiro = $partes[0]; $ultimo = count($partes) > 1 ? end($partes) : $primeiro;
        $payload = [ "primaryEmail" => $email_google, "password" => $nova_senha, "name" => [ "givenName" => $primeiro, "familyName" => $ultimo ], "changePasswordAtNextLogin" => false, "organizations" => [ [ "title" => $dados_sync['cargo'] ?? '', "department" => $dados_sync['setor'] ?? '' ] ] ];
        if(!empty($dados_sync['telefone'])) $payload["phones"] = [ [ "value" => "+55".$dados_sync['telefone'], "type" => "mobile" ] ];
    } elseif ($acao == 'bloquear') { $payload = [ "suspended" => true ]; } 
    elseif ($acao == 'atualizar' || $acao == 'ativar') {
        $payload = [ "suspended" => false ];
        if(!empty($dados_sync['nome'])) { $partes = explode(' ', $dados_sync['nome']); $primeiro = $partes[0]; $ultimo = count($partes) > 1 ? end($partes) : $primeiro; $payload["name"] = [ "givenName" => $primeiro, "familyName" => $ultimo ]; }
        if(!empty($dados_sync['telefone'])) $payload["phones"] = [ [ "value" => "+55".$dados_sync['telefone'], "type" => "mobile" ] ];
    } elseif ($acao == 'excluir') { $metodo = "DELETE"; } 
    elseif ($acao == 'reset_senha') { $payload = [ "password" => $nova_senha, "suspended" => false ]; }

    $ch2 = curl_init($endpoint);
    if($metodo != 'POST') curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, $metodo); else curl_setopt($ch2, CURLOPT_POST, true);
    if(!empty($payload)) curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch2, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]);
    $resp = curl_exec($ch2); $hc = curl_getinfo($ch2, CURLINFO_HTTP_CODE); curl_close($ch2);

    if($hc == 200 || $hc == 201 || $hc == 204) return ['sucesso'=>true];
    registrarErro($conn, 'Google Cloud', "Erro HTTP $hc: $resp"); return ['sucesso'=>false];
}

if(isset($_POST['login'])){ 
    $u=trim($_POST['u']); $p=trim($_POST['p']); 
    if($u=='admin' && $p=='Enfas@2026'){ 
        $_SESSION['auth']=['id'=>0,'nome'=>'Administrador Master','perfil'=>'TI','usuario'=>'admin']; header("Location: ?p=dashboard"); exit; 
    } else { 
        $stmt=$conn->prepare("SELECT * FROM usuarios_admin WHERE usuario=?"); $stmt->bind_param("s", $u); $stmt->execute(); $res=$stmt->get_result()->fetch_assoc(); 
        if($res && password_verify($p, $res['senha'])){ $_SESSION['auth']=$res; header("Location: ?p=dashboard"); exit; } 
        else { $erro_login = "Credenciais invalidas."; } 
    } 
}
if(isset($_GET['logout'])){ session_destroy(); header("Location: /"); exit; }

if(!isset($_SESSION['auth'])): ?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>CadColab | ENFAS</title><meta name="viewport" content="width=device-width, initial-scale=1.0"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"><style>body { background: #121212; font-family: 'Segoe UI', sans-serif; display: flex; flex-direction:column; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding:20px;} .login-wrapper { display: flex; background: #1a1d21; border-radius: 12px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); overflow: hidden; width: 100%; max-width: 900px; } .login-form { padding: 60px 50px; width: 50%; display: flex; flex-direction: column; justify-content: center; } .login-info { padding: 50px; width: 50%; background: #008bb9; color: white; display: flex; flex-direction: column; justify-content: center; } .input-group-custom { display: flex; align-items: center; border: 1px solid #333; border-radius: 8px; background: #24282c; margin-bottom: 20px; transition: 0.3s; } .input-group-custom i { padding: 0 15px; color: #888; } .input-group-custom input { border: none; background: transparent; padding: 15px 15px 15px 0; width: 100%; outline: none; font-size: 15px; color: #fff; } .input-group-custom input::placeholder { color: #888; } .btn-login { background: #00b4d8; color: white; padding: 15px; border: none; border-radius: 8px; width: 100%; font-weight: bold; font-size: 16px; transition: 0.3s; } .btn-login:hover { background: #0096b8; } @media (max-width: 768px) { .login-wrapper { flex-direction: column; } .login-form, .login-info { width: 100%; padding: 30px; } }</style></head>
<body><?php if(!empty($cfg_global['aviso_login'])): ?><div class="alert alert-warning py-3 fw-bold text-center" style="max-width:900px; width:100%; margin-bottom:20px; border-radius:12px;"><i class="fa-solid fa-bullhorn me-2"></i> <?=htmlspecialchars($cfg_global['aviso_login'])?></div><?php endif; ?><div class="login-wrapper"><div class="login-form"><div class="text-center mb-4"><img src="https://autoatendimento.enfas.com.br/Content/images/logo_enfas.png" style="max-width: 150px; margin-bottom: 20px; filter: brightness(0) invert(1);"><h6 class="fw-bold" style="color:#fff; letter-spacing:1px; line-height:1.5;">SISTEMA INTERNO DE CADASTRO<br>DE COLABORADORES ENFAS</h6></div><?php if(isset($erro_login)) echo "<div class='alert alert-danger py-2 small fw-bold text-center'>$erro_login</div>"; ?><form method="POST"><div class="input-group-custom"><i class="fa-solid fa-user"></i><input name="u" placeholder="Usuario" required autocomplete="off"></div><div class="input-group-custom"><i class="fa-solid fa-lock"></i><input name="p" type="password" placeholder="Senha Corporativa" required></div><button name="login" class="btn-login mt-2">Acessar Sistema</button></form></div><div class="login-info"><h3 class="fw-bold mb-4"><i class="fa-solid fa-shield-halved me-2"></i> Orientacoes de Seguranca</h3><div class="mb-4"><h6 class="fw-bold text-warning mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> Acesso Restrito</h6><p class="small text-white-50">Area exclusiva para Diretoria, RH e TI da Clinica ENFAS.</p></div><div class="mb-4"><h6 class="fw-bold text-warning mb-1"><i class="fa-solid fa-eye me-1"></i> Auditoria de Logs</h6><p class="small text-white-50">Modificacoes sao monitoradas ativamente.</p></div><div class="d-flex align-items-center gap-3 mt-4 pt-4 border-top border-light border-opacity-25"><i class="fa-solid fa-certificate fa-2x text-white"></i><div style="font-size:11px;"><b class="text-white">PROTEGIDO POR CRIPTOGRAFIA</b><br><span class="text-white-50">Ambiente em conformidade com as normas ICP-Brasil e LGPD.</span></div></div></div></div></body></html>
<?php exit; endif; ?>

<?php
$p = $_GET['p'] ?? 'dashboard'; $p = str_replace('/', '', $p); if(empty($p) || $p == 'index.php') $p = 'dashboard';
$perf = $_SESSION['auth']['perfil'] ?? 'Operador'; $isAdmin = in_array($perf, ['TI', 'Admin']); $isRH = in_array($perf, ['TI', 'Admin', 'RH']); $isTI = ($perf == 'TI');

if($p == 'cracha' && isset($_GET['id'])){ 
    $id = (int)$_GET['id']; $q_col = $conn->query("SELECT p.*, c.nome as crg FROM pre_registros p LEFT JOIN cargos c ON p.cargo_id=c.id WHERE p.id=$id"); 
    $colab = $q_col ? $q_col->fetch_assoc() : null;
    if(!$colab) die("Colaborador nao encontrado."); 
    $foto = $colab['foto_path'] ? "//cadcolab.enfas.com.br/".$colab['foto_path']."?v=".time() : "https://via.placeholder.com/150"; 
    $pid = !empty($colab['public_id']) ? $colab['public_id'] : str_pad($colab['id'], 6, '0', STR_PAD_LEFT); 
    $tpl = $cfg_global['tpl_cracha'] ?? '';
    $html = str_replace(['{NOME}', '{CARGO}', '{ID}', '{FOTO}'], [$colab['nome_completo'], $colab['crg'], $pid, $foto], $tpl);
    echo "<!DOCTYPE html><html lang='pt-BR'><head><title>Cracha</title><style>body{font-family:Arial;display:flex;justify-content:center;align-items:center;height:100vh;background:#e2e8f0;margin:0;}</style></head><body onload='window.print()'>$html</body></html>"; exit; 
}

if($p == 'ficha' && isset($_GET['id'])){ 
    $id = (int)$_GET['id']; $q_col = $conn->query("SELECT p.*, c.nome as crg, u.nome as un, s.nome as seto FROM pre_registros p LEFT JOIN cargos c ON p.cargo_id=c.id LEFT JOIN unidades u ON p.unidade_id=u.id LEFT JOIN setores s ON p.setor_id=s.id WHERE p.id=$id"); 
    $colab = $q_col ? $q_col->fetch_assoc() : null;
    if(!$colab) die("Colaborador nao encontrado."); 
    $tpl = $cfg_global['tpl_ficha'] ?? '';
    $email_corp = $colab['username_criado'] ? $colab['username_criado']."@enfas.com.br" : "Nao possui";
    $html = str_replace(['{NOME}', '{CPF}', '{MATRICULA}', '{EMAIL_CORP}'], [$colab['nome_completo'], $colab['cpf'], $colab['matricula'], $email_corp], $tpl);
    echo "<!DOCTYPE html><html lang='pt-BR'><head><title>Ficha</title><style>body{font-family:Arial;padding:40px;background:#f4f4f4;} @media print { body{background:white;padding:0;} }</style></head><body>$html<br><button onclick='window.print()' style='display:block;margin:20px auto;padding:10px 20px;background:#008bb9;color:white;border:none;cursor:pointer;'>Imprimir</button></body></html>"; exit; 
}

if($p == 'dashboard'){ 
    $graf_unid = []; $q1 = $conn->query("SELECT u.nome, COUNT(pr.id) as total FROM pre_registros pr LEFT JOIN unidades u ON pr.unidade_id = u.id GROUP BY u.id"); 
    if($q1) { while($r = $q1->fetch_assoc()){ $graf_unid['labels'][] = $r['nome'] ?: 'Sem Lotacao'; $graf_unid['data'][] = $r['total']; } }
    
    $graf_status = []; $q2 = $conn->query("SELECT status, COUNT(id) as total FROM pre_registros GROUP BY status");
    if($q2) { while($r = $q2->fetch_assoc()){ $graf_status['labels'][] = strtoupper($r['status']); $graf_status['data'][] = $r['total']; } }
}

if(isset($_POST['action'])){
    $id = (int)($_POST['id'] ?? 0); $admin_logado = $_SESSION['auth']['nome'];
    
    if($_POST['action']=='save_meu_perfil') {
        $id_admin = $_SESSION['auth']['id'];
        if($id_admin > 0) {
            $n = $conn->real_escape_string($_POST['nome']);
            $e = $conn->real_escape_string($_POST['email']);
            $u = $conn->real_escape_string($_POST['usuario']);
            $senha_sql = "";
            if(!empty($_POST['senha'])) {
                $s = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                $senha_sql = ", senha='$s'";
            }
            $conn->query("UPDATE usuarios_admin SET nome='$n', email='$e', usuario='$u' $senha_sql WHERE id=$id_admin");
            $_SESSION['auth']['nome'] = $n; $_SESSION['auth']['email'] = $e; $_SESSION['auth']['usuario'] = $u;
            registrarLog($conn, $n, 'Meu Perfil', "Atualizou os próprios dados de acesso.");
        }
        header("Location: ?p=$p"); exit;
    }

    if($_POST['action']=='change_status' && $isRH) {
        $novo_status = $conn->real_escape_string($_POST['status']);
        $q_old = $conn->query("SELECT status, username_criado FROM pre_registros WHERE id=$id");
        $user_old = $q_old ? $q_old->fetch_assoc() : null;
        $conn->query("UPDATE pre_registros SET status='$novo_status' WHERE id=$id");

        $status_inativos = ['bloqueado', 'inativo', 'desligado', 'demitido'];
        if(in_array($novo_status, $status_inativos) && !in_array($user_old['status'], $status_inativos) && !empty($user_old['username_criado'])) {
            $q_full = $conn->query("SELECT p.*, c.nome as crg, u.nome as un, s.nome as seto FROM pre_registros p LEFT JOIN cargos c ON p.cargo_id=c.id LEFT JOIN unidades u ON p.unidade_id=u.id LEFT JOIN setores s ON p.setor_id=s.id WHERE p.id=$id");
            if($q_full) {
                $colab = $q_full->fetch_assoc();
                $gestor_email = "";
                if((int)$colab['gestor_id'] > 0) {
                    $g_data = $conn->query("SELECT username_criado FROM pre_registros WHERE id=".$colab['gestor_id'])->fetch_assoc();
                    if(!empty($g_data['username_criado'])) $gestor_email = $g_data['username_criado']."@enfas.com.br";
                }
                $dados_sync = ['nome' => $colab['nome_completo'], 'cargo' => $colab['crg'], 'setor' => $colab['seto'], 'unidade' => $colab['un'], 'telefone' => $colab['telefone'], 'email_pessoal' => $colab['e_mail_pessoal'], 'status' => $novo_status, 'gestor_email' => $gestor_email, 'm365_perfil' => $colab['m365_perfil'], 'm365_apps' => $colab['m365_apps']];

                gerenciarAcessoM365($conn, $user_old['username_criado']."@enfas.com.br", 'bloquear', null, $dados_sync, $cfg_global);
                gerenciarAcessoGoogle($conn, $user_old['username_criado']."@enfas.com.br", 'bloquear', null, $dados_sync, $cfg_global);
            }
        }
        if($novo_status == 'ativo' && in_array($user_old['status'], $status_inativos) && !empty($user_old['username_criado'])) {
             $q_full = $conn->query("SELECT p.*, c.nome as crg, u.nome as un, s.nome as seto FROM pre_registros p LEFT JOIN cargos c ON p.cargo_id=c.id LEFT JOIN unidades u ON p.unidade_id=u.id LEFT JOIN setores s ON p.setor_id=s.id WHERE p.id=$id");
            if($q_full) {
                $colab = $q_full->fetch_assoc();
                $gestor_email = "";
                if((int)$colab['gestor_id'] > 0) {
                    $g_data = $conn->query("SELECT username_criado FROM pre_registros WHERE id=".$colab['gestor_id'])->fetch_assoc();
                    if(!empty($g_data['username_criado'])) $gestor_email = $g_data['username_criado']."@enfas.com.br";
                }
                $dados_sync = ['nome' => $colab['nome_completo'], 'cargo' => $colab['crg'], 'setor' => $colab['seto'], 'unidade' => $colab['un'], 'telefone' => $colab['telefone'], 'email_pessoal' => $colab['e_mail_pessoal'], 'status' => $novo_status, 'gestor_email' => $gestor_email, 'm365_perfil' => $colab['m365_perfil'], 'm365_apps' => $colab['m365_apps']];

                gerenciarAcessoM365($conn, $user_old['username_criado']."@enfas.com.br", 'atualizar', null, $dados_sync, $cfg_global);
                gerenciarAcessoGoogle($conn, $user_old['username_criado']."@enfas.com.br", 'atualizar', null, $dados_sync, $cfg_global);
            }
        }
        registrarLog($conn, $admin_logado, 'Alteração de Status', "Status ID $id alterado para $novo_status.");
        die("<!DOCTYPE html><html><head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body><script>Swal.fire('Atualizado!', 'Status alterado e Nuvens sincronizadas.', 'success').then(()=>window.location.href='?p=colaboradores');</script></body></html>");
    }

    if($_POST['action']=='sync_assinatura' && $isTI) {
        $q_col = $conn->query("SELECT p.*, c.nome as crg, u.nome as un, u.template_assinatura, s.nome as seto FROM pre_registros p LEFT JOIN cargos c ON p.cargo_id=c.id LEFT JOIN unidades u ON p.unidade_id=u.id LEFT JOIN setores s ON p.setor_id=s.id WHERE p.id=$id");
        $colab = $q_col ? $q_col->fetch_assoc() : null;
        if(!$colab || empty($colab['username_criado'])) { die("<!DOCTYPE html><html><head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body><script>Swal.fire('Erro', 'Colaborador não ativou a conta M365.', 'error').then(()=>window.history.back());</script></body></html>"); }
        if(empty($colab['template_assinatura'])) { die("<!DOCTYPE html><html><head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body><script>Swal.fire('Erro', 'A unidade deste colaborador não tem Template de Assinatura configurado.', 'error').then(()=>window.history.back());</script></body></html>"); }
        
        $email_corp = $colab['username_criado']."@enfas.com.br";
        $html_final = str_replace(['{NOME}', '{CARGO}', '{SETOR}', '{TELEFONE}', '{EMAIL}'], [$colab['nome_completo'], $colab['crg']?:'', $colab['seto']?:'', $colab['telefone']?:'', $email_corp], $colab['template_assinatura']);
        $sync = sincronizarAssinaturaM365($conn, $email_corp, $html_final, $cfg_global);
        
        if($sync['sucesso']) { registrarLog($conn, $admin_logado, 'Assinatura M365', "Assinatura aplicada no Outlook de $email_corp."); die("<!DOCTYPE html><html><head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body><script>Swal.fire('Sucesso!', 'Assinatura padronizada injetada no Outlook do colaborador!', 'success').then(()=>window.history.back());</script></body></html>"); } 
        else { die("<!DOCTYPE html><html><head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body><script>Swal.fire('Falha na API', 'Verifique o Painel de Erros.', 'error').then(()=>window.history.back());</script></body></html>"); }
    }

    if($_POST['action']=='importar_csv' && $isRH) {
        if(isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
            $handle = fopen($_FILES['csv_file']['tmp_name'], "r"); fgetcsv($handle); $cadastrados = 0;
            while(($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if(count($data) >= 3) {
                    $nome = $conn->real_escape_string($data[0]); $cpf = preg_replace('/[^0-9]/','',$data[1]); $mat = $conn->real_escape_string($data[2]); 
                    $email_pessoal = isset($data[3]) ? $conn->real_escape_string($data[3]) : ''; $telefone = isset($data[4]) ? preg_replace('/[^0-9]/','',$data[4]) : '';
                    if(!empty($cpf)) { $chk = $conn->query("SELECT id FROM pre_registros WHERE cpf='$cpf'"); if($chk && $chk->num_rows == 0) { $pid = rand(100000, 999999); $conn->query("INSERT INTO pre_registros (public_id, matricula, nome_completo, cpf, e_mail_pessoal, telefone, status) VALUES ('$pid', '$mat', '$nome', '$cpf', '$email_pessoal', '$telefone', 'pendente')"); $cadastrados++; } }
                }
            }
            fclose($handle); registrarLog($conn, $admin_logado, 'Importacao CSV', "$cadastrados novos colaboradores."); header("Location: ?p=colaboradores&msg=csv_ok&qt=$cadastrados"); exit;
        }
    }
    if($_POST['action']=='save_todas_configs' && $isTI){ 
        foreach($_POST['cfg'] as $k => $v){ 
            $v = trim($v); 
            $v_esc = $conn->real_escape_string($v);
            $check = $conn->query("SELECT id FROM configuracoes WHERE chave='$k'"); 
            if($check && $check->num_rows > 0) { $conn->query("UPDATE configuracoes SET valor='$v_esc' WHERE chave='$k'"); } 
            else { $conn->query("INSERT INTO configuracoes (chave, valor) VALUES ('$k', '$v_esc')"); } 
        } 
        registrarLog($conn, $admin_logado, 'Configuracoes', "Atualizou parametros gerais."); header("Location: ?p=configuracoes"); exit; 
    }
    if($_POST['action']=='reset_senha' && $isTI){ 
        $q_col = $conn->query("SELECT username_criado FROM pre_registros WHERE id=$id"); 
        $colab = $q_col ? $q_col->fetch_assoc() : null;
        $nova_senha = "Enfas@" . rand(1000, 9999); 
        $hash_db = password_hash($nova_senha, PASSWORD_DEFAULT); 
        
        $conn->query("UPDATE pre_registros SET senha_criada='$hash_db', data_ultima_senha=NOW() WHERE id=$id"); $msg_swal = "Senha Corporativa resetada: $nova_senha."; 
        if(!empty($colab['username_criado'])) { 
            $email = $colab['username_criado'] . "@enfas.com.br"; 
            $m365_resp = gerenciarAcessoM365($conn, $email, 'reset_senha', $nova_senha, [], $cfg_global); 
            $gw_resp = gerenciarAcessoGoogle($conn, $email, 'reset_senha', $nova_senha, [], $cfg_global);
            if($m365_resp['sucesso'] || $gw_resp['sucesso']){ $msg_swal .= "\\n\\nSincronizada nas Nuvens!"; } else { $msg_swal .= "\\n\\nFalha ao enviar para Nuvem."; } 
        } 
        registrarLog($conn, $admin_logado, 'Reset Senha', "Reset efetuado ID $id."); die("<!DOCTYPE html><html><head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body><script>Swal.fire({title: 'Senha Resetada!', text: '$msg_swal', icon: 'success'}).then(()=>window.location.href='?p=colaboradores');</script></body></html>"); 
    }
    if($_POST['action']=='delete'){ 
        $t = $_POST['table']; 
        if($t == 'pre_registros'){ 
            $q_del = $conn->query("SELECT username_criado FROM pre_registros WHERE id=$id"); 
            $user_del = $q_del ? $q_del->fetch_assoc() : null;
            if($user_del && !empty($user_del['username_criado'])) { 
                gerenciarAcessoM365($conn, $user_del['username_criado']."@enfas.com.br", 'excluir', null, [], $cfg_global); 
                gerenciarAcessoGoogle($conn, $user_del['username_criado']."@enfas.com.br", 'excluir', null, [], $cfg_global); 
            } 
            registrarLog($conn, $admin_logado, 'Exclusão', "Excluiu registro ID $id na tabela $t");
        } 
        $conn->query("DELETE FROM $t WHERE id=$id"); header("Location: ?p=$p"); exit; 
    }
    if($_POST['action']=='save_unidade'){ $n=$_POST['nome']; $end=$_POST['endereco']; $tel=$_POST['telefone']; $tpl_ass = addslashes($_POST['template_assinatura'] ?? ''); if($id>0) { $conn->query("UPDATE unidades SET nome='$n', endereco='$end', telefone='$tel', template_assinatura='$tpl_ass' WHERE id=$id"); registrarLog($conn, $admin_logado, 'Unidades', "Atualizou unidade: $n"); } else { $conn->query("INSERT INTO unidades (nome, endereco, telefone, template_assinatura) VALUES ('$n', '$end', '$tel', '$tpl_ass')"); registrarLog($conn, $admin_logado, 'Unidades', "Criou unidade: $n"); } header("Location: ?p=unidades"); exit; }
    if($_POST['action']=='save_setor'){ $n=$_POST['nome']; $resp=$_POST['responsavel']; $ramal=$_POST['ramal']; if($id>0) { $conn->query("UPDATE setores SET nome='$n', responsavel='$resp', ramal='$ramal' WHERE id=$id"); registrarLog($conn, $admin_logado, 'Setores', "Atualizou setor: $n"); } else { $conn->query("INSERT INTO setores (nome, responsavel, ramal) VALUES ('$n', '$resp', '$ramal')"); registrarLog($conn, $admin_logado, 'Setores', "Criou setor: $n"); } header("Location: ?p=setores"); exit; }
    if($_POST['action']=='save_cargo'){ $n=$_POST['nome']; $pid=(int)$_POST['perfil_id']; if($id>0) { $conn->query("UPDATE cargos SET nome='$n', perfil_id=$pid WHERE id=$id"); registrarLog($conn, $admin_logado, 'Cargos', "Atualizou cargo: $n"); } else { $conn->query("INSERT INTO cargos (nome, perfil_id) VALUES ('$n', $pid)"); registrarLog($conn, $admin_logado, 'Cargos', "Criou cargo: $n"); } header("Location: ?p=cargos"); exit; }
    if($_POST['action']=='save_perfil'){ $n=$_POST['nome']; $sists=isset($_POST['sistemas'])?implode(',',$_POST['sistemas']):''; if($id>0) { $conn->query("UPDATE perfis_acesso SET nome='$n', sistemas_ids='$sists' WHERE id=$id"); registrarLog($conn, $admin_logado, 'Perfis', "Atualizou perfil: $n"); } else { $conn->query("INSERT INTO perfis_acesso (nome, sistemas_ids) VALUES ('$n', '$sists')"); registrarLog($conn, $admin_logado, 'Perfis', "Criou perfil: $n"); } header("Location: ?p=perfis"); exit; }
    if($_POST['action']=='save_sistema'){ $n=$_POST['nome']; $l=$_POST['link']; if($id>0) { $conn->query("UPDATE sistemas_hc SET nome='$n', link='$l' WHERE id=$id"); registrarLog($conn, $admin_logado, 'Sistemas', "Atualizou sistema: $n"); } else { $conn->query("INSERT INTO sistemas_hc (nome, link) VALUES ('$n', '$l')"); registrarLog($conn, $admin_logado, 'Sistemas', "Criou sistema: $n"); } header("Location: ?p=sistemas"); exit; }
    if($_POST['action']=='save_generic'){ 
        $t = $_POST['table']; $n = $_POST['nome']; 
        if($t == 'usuarios_admin'){ 
            $em = $_POST['email']; $usr = $_POST['usuario']; $novo_perf = $_POST['perfil']; $pre_reg = !empty($_POST['pre_registro_id']) ? (int)$_POST['pre_registro_id'] : "NULL"; 
            if($id > 0){ 
                $sql="UPDATE usuarios_admin SET nome='$n', email='$em', usuario='$usr', perfil='$novo_perf', pre_registro_id=$pre_reg"; 
                if(!empty($_POST['senha'])){ $s=password_hash($_POST['senha'], PASSWORD_DEFAULT); $sql.=", senha='$s'"; } 
                $conn->query($sql." WHERE id=$id"); 
                registrarLog($conn, $admin_logado, 'Administradores', "Atualizou o administrador: $usr");
            } else { 
                $s=password_hash($_POST['senha'], PASSWORD_DEFAULT); 
                $conn->query("INSERT INTO usuarios_admin (nome, email, usuario, senha, perfil, pre_registro_id) VALUES ('$n', '$em', '$usr', '$s', '$novo_perf', $pre_reg)"); 
                registrarLog($conn, $admin_logado, 'Administradores', "Criou novo administrador: $usr");
            } 
        } 
        else { if($id > 0) $conn->query("UPDATE $t SET nome='$n' WHERE id=$id"); else $conn->query("INSERT INTO $t (nome) VALUES ('$n')"); } header("Location: ?p=$p"); exit; 
    }
    
    if($_POST['action']=='save_colab'){ 
        $n = $_POST['nome']; $cpf = preg_replace('/[^0-9]/','',$_POST['cpf']); 
        $check = $conn->query("SELECT id FROM pre_registros WHERE cpf='$cpf' AND id != $id"); if($check && $check->num_rows > 0) die("<!DOCTYPE html><html><head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body><script>Swal.fire('Erro!', 'CPF ja cadastrado.', 'error').then(()=>window.history.back());</script></body></html>"); 
        
        $mae = $_POST['nome_mae'] ?? ''; $mat = $_POST['matricula'] ?? ''; $tel = preg_replace('/[^0-9]/','',$_POST['telefone'] ?? ''); $st = $_POST['status'] ?? 'pendente'; $mail_p = $_POST['email_pessoal'] ?? ''; $d_nasc = !empty($_POST['nasc']) ? "'".$_POST['nasc']."'" : "NULL"; $d_adm = !empty($_POST['data_admissao']) ? "'".$_POST['data_admissao']."'" : "NULL"; $unid = (int)($_POST['unidade'] ?? 0); $seto = (int)($_POST['setor'] ?? 0); $carg = (int)($_POST['cargo'] ?? 0); 
        $perf_m365 = $_POST['m365_perfil'] ?? 'Sem Licenca'; $j_ini = $_POST['jornada_inicio'] ?? '08:00'; $j_fim = $_POST['jornada_fim'] ?? '18:00'; $ignora = isset($_POST['ignora_jornada']) ? 1 : 0; $sist_arr = isset($_POST['sistemas']) ? implode(',', $_POST['sistemas']) : ''; $gestor = !empty($_POST['gestor_id']) ? (int)$_POST['gestor_id'] : "NULL"; $ferias_ini = !empty($_POST['ferias_inicio']) ? "'".$_POST['ferias_inicio']."'" : "NULL"; $ferias_fim = !empty($_POST['ferias_fim']) ? "'".$_POST['ferias_fim']."'" : "NULL"; $obs = addslashes($_POST['observacoes_rh'] ?? ''); $m365_apps = isset($_POST['m365_apps']) ? implode(',', $_POST['m365_apps']) : ''; $dados_ext = addslashes($_POST['dados_extras'] ?? '');
        $f_sql = ""; if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0){ $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION); $filename = "foto_".time().".".$ext; $path = "uploads/" . $filename; move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . '/' . $path); $f_sql = ", foto_path='$path'"; } 
        
        $pid_enviado = isset($_POST['public_id']) ? preg_replace('/[^0-9]/', '', $_POST['public_id']) : rand(100000, 999999);

        if($id > 0){ 
            $q_usr_old = $conn->query("SELECT status, username_criado FROM pre_registros WHERE id=$id");
            $user_old = $q_usr_old ? $q_usr_old->fetch_assoc() : null;
            $usr_m365 = $user_old ? $user_old['username_criado'] : '';
            
            $conn->query("UPDATE pre_registros SET nome_completo='$n', nome_mae='$mae', cpf='$cpf', telefone='$tel', data_nascimento=$d_nasc, data_admissao=$d_adm, matricula='$mat', status='$st', e_mail_pessoal='$mail_p', m365_perfil='$perf_m365', m365_apps='$m365_apps', unidade_id=$unid, setor_id=$seto, cargo_id=$carg, gestor_id=$gestor, ferias_inicio=$ferias_ini, ferias_fim=$ferias_fim, observacoes_rh='$obs', dados_extras='$dados_ext', sistemas_liberados='$sist_arr', jornada_inicio='$j_ini', jornada_fim='$j_fim', ignora_jornada=$ignora $f_sql WHERE id=$id"); 
            
            if(!empty($usr_m365)) { 
                $q_c = $conn->query("SELECT nome FROM cargos WHERE id=$carg"); $nm_cargo = $q_c ? $q_c->fetch_assoc()['nome'] : ''; 
                $q_s = $conn->query("SELECT nome FROM setores WHERE id=$seto"); $nm_setor = $q_s ? $q_s->fetch_assoc()['nome'] : ''; 
                $q_u = $conn->query("SELECT nome FROM unidades WHERE id=$unid"); $nm_unidade = $q_u ? $q_u->fetch_assoc()['nome'] : ''; 
                $gestor_email = ""; 
                if((int)$gestor > 0) { 
                    $g_data = $conn->query("SELECT username_criado FROM pre_registros WHERE id=$gestor")->fetch_assoc(); 
                    if(!empty($g_data['username_criado'])) { $gestor_email = $g_data['username_criado']."@enfas.com.br"; } 
                } 
                $dados_sync = ['nome' => $n, 'cargo' => $nm_cargo, 'setor' => $nm_setor, 'unidade' => $nm_unidade, 'telefone' => $tel, 'email_pessoal' => $mail_p, 'status' => $st, 'gestor_email' => $gestor_email, 'm365_perfil' => $perf_m365, 'm365_apps' => $m365_apps]; 
                
                $status_inativos = ['bloqueado', 'inativo', 'desligado', 'demitido'];
                $acao_m365 = (in_array($st, $status_inativos) && !in_array($user_old['status'], $status_inativos)) ? 'bloquear' : 'atualizar';
                gerenciarAcessoM365($conn, $usr_m365."@enfas.com.br", $acao_m365, null, $dados_sync, $cfg_global); 
                gerenciarAcessoGoogle($conn, $usr_m365."@enfas.com.br", $acao_m365, null, $dados_sync, $cfg_global);
            }
            registrarLog($conn, $admin_logado, 'Colaboradores', "Atualizou a ficha de $n.");
        } else { 
            $conn->query("INSERT INTO pre_registros (public_id, matricula, nome_completo, nome_mae, cpf, telefone, data_nascimento, data_admissao, status, e_mail_pessoal, username_criado, senha_criada, m365_perfil, m365_apps, unidade_id, setor_id, cargo_id, gestor_id, ferias_inicio, ferias_fim, observacoes_rh, dados_extras, sistemas_liberados, jornada_inicio, jornada_fim, ignora_jornada, foto_path) VALUES ('$pid_enviado', '$mat', '$n', '$mae', '$cpf', '$tel', $d_nasc, $d_adm, '$st', '$mail_p', '', '', '$perf_m365', '$m365_apps', $unid, $seto, $carg, $gestor, $ferias_ini, $ferias_fim, '$obs', '$dados_ext', '$sist_arr', '$j_ini', '$j_fim', $ignora, '".($path??'')."')"); 
            registrarLog($conn, $admin_logado, 'Colaboradores', "Cadastrou novo colaborador: $n.");
        } 
        header("Location: ?p=colaboradores"); exit; 
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Gestão ENFAS IAM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --bg-body: #f4f7f9; --bg-card: #ffffff; --text-primary: #334155; --text-muted: #64748b; --border-color: #e2e8f0; --table-bg: transparent; }
        [data-theme="dark"] { --bg-body: #121212; --bg-card: #1e1e1e; --text-primary: #f8fafc; --text-muted: #94a3b8; --border-color: #334155; --table-bg: #1e1e1e; }
        body { background: var(--bg-body); color: var(--text-primary); font-family: 'Segoe UI',sans-serif; margin: 0; display: flex; min-height: 100vh; transition: background 0.3s; overflow-x: hidden;}
        
        .sidebar { width: 260px; background: #0f172a; position: fixed; height: 100vh; padding: 20px 15px; overflow-y: auto; z-index:1000; display: flex; flex-direction: column;}
        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; transition: 0.3s; max-width: calc(100% - 260px);}
        
        .topbar { background: var(--bg-card); height: 70px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; border-bottom: 1px solid var(--border-color); }
        .content-area { padding: 40px; flex: 1; }
        .nav-link { color: #94a3b8; padding: 12px 15px; border-radius: 8px; margin-bottom: 5px; text-decoration: none; display: flex; align-items: center; font-size: 14px; }
        .nav-link:hover, .nav-link.active { background: #1d4ed8; color: #fff; }
        
        .table-card { background: var(--bg-card); border-radius: 12px; padding: 25px; border: 1px solid var(--border-color); box-shadow: 0 4px 6px rgba(0,0,0,0.02); margin-bottom: 25px; }
        
        .table { color: var(--text-primary); background: var(--table-bg); margin-bottom:0; width: 100%; }
        .table th { border-bottom: 2px solid var(--border-color); color: var(--text-muted); text-transform: uppercase; font-size: 11px; padding: 12px 10px; background: var(--table-bg); font-weight: 600; letter-spacing: 0.5px;}
        .table td { border-bottom: 1px solid var(--border-color); padding: 12px 10px; vertical-align: middle; background: var(--table-bg); color: var(--text-primary); font-size: 13px;}
        .badge { font-size: 11px; padding: 0.35rem 0.65rem !important; }
        
        .dash-card { position: relative; overflow: hidden; border-radius: 12px; padding: 25px; color: white; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.1); display: flex; flex-direction: column; align-items: flex-start; transition: 0.3s; }
        .dash-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .dash-card i.bg-icon { position: absolute; right: -10px; bottom: -20px; font-size: 6rem; opacity: 0.15; transform: rotate(-15deg); }
        .z-1 { position: relative; z-index: 1; }
        
        .bg-gradient-blue { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); }
        .bg-gradient-green { background: linear-gradient(135deg, #064e3b 0%, #10b981 100%); }
        .bg-gradient-yellow { background: linear-gradient(135deg, #78350f 0%, #f59e0b 100%); }
        .bg-gradient-red { background: linear-gradient(135deg, #7f1d1d 0%, #ef4444 100%); }
        
        .btn-action { background: transparent; border: 1px solid var(--border-color); border-radius: 50%; width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; color: var(--text-primary); font-size: 12px; cursor: pointer; transition: 0.2s; margin-left: 3px; margin-bottom: 3px;}
        .btn-action:hover { background: var(--border-color); }
        
        .theme-toggle { cursor: pointer; font-size: 20px; color: var(--text-primary); margin-right: 20px; }
        .modal-content { background: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color); }
        .form-control, .form-select { background-color: var(--bg-body); color: var(--text-primary); border: 1px solid var(--border-color); }
        .form-control:focus, .form-select:focus { background-color: var(--bg-card); color: var(--text-primary); }
        .img-p { width:45px; height:45px; border-radius:50%; object-fit:cover; border:2px solid var(--border-color); }
        .status-badge { width: 12px; height: 12px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        
        .btn-loading { pointer-events: none; opacity: 0.8; }
        .btn-loading::after { content: " ⏳"; animation: spin 1s infinite linear; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        
        @media (max-width: 768px) { .sidebar { width: 100%; height: auto; position: relative; } .main-wrapper { margin-left: 0; max-width:100%;} }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="text-center mb-5 mt-2"><i class="fa-solid fa-hospital fa-2x text-primary me-2"></i><h4 class="fw-bold text-white m-0 d-inline">ENFAS</h4></div>
    <nav class="text-start">
        <a href="?p=dashboard" class="nav-link <?=($p=='dashboard'?'active':'')?>"><i class="fa-solid fa-chart-pie me-3"></i> Dashboard</a>
        <?php if($isRH): ?>
            <a data-bs-toggle="collapse" href="#menuRH" class="nav-link mt-4" style="color:#e2e8f0; cursor:pointer;"><i class="fa-solid fa-users-gear me-3"></i> RH & Estrutura <i class="fa-solid fa-chevron-down ms-auto" style="font-size:10px;"></i></a>
            <div class="collapse <?=in_array($p, ['colaboradores','unidades','setores','cargos'])?'show':''?>" id="menuRH">
                <div class="ps-4 border-start border-secondary ms-3 mt-2">
                    <a href="?p=colaboradores" class="nav-link <?=($p=='colaboradores'?'active':'')?>"><i class="fa-solid fa-id-card-clip me-2 opacity-75"></i> Colaboradores</a>
                    <a href="?p=unidades" class="nav-link <?=($p=='unidades'?'active':'')?>"><i class="fa-solid fa-map-location-dot me-2 opacity-75"></i> Unidades</a>
                    <a href="?p=setores" class="nav-link <?=($p=='setores'?'active':'')?>"><i class="fa-solid fa-layer-group me-2 opacity-75"></i> Setores</a>
                    <a href="?p=cargos" class="nav-link <?=($p=='cargos'?'active':'')?>"><i class="fa-solid fa-briefcase me-2 opacity-75"></i> Cargos</a>
                </div>
            </div>
        <?php endif; ?>
        <?php if($isAdmin): ?>
            <a data-bs-toggle="collapse" href="#menuTI" class="nav-link mt-4" style="color:#e2e8f0; cursor:pointer;"><i class="fa-solid fa-microchip me-3"></i> TI & Sistemas <i class="fa-solid fa-chevron-down ms-auto" style="font-size:10px;"></i></a>
            <div class="collapse <?=in_array($p, ['perfis','sistemas','usuarios','configuracoes','auditoria','erros', 'status'])?'show':''?>" id="menuTI">
                <div class="ps-4 border-start border-secondary ms-3 mt-2">
                    <a href="?p=perfis" class="nav-link <?=($p=='perfis'?'active':'')?>"><i class="fa-solid fa-user-lock me-2 opacity-75"></i> Perfis de Acesso</a>
                    <a href="?p=sistemas" class="nav-link <?=($p=='sistemas'?'active':'')?>"><i class="fa-solid fa-laptop-code me-2 opacity-75"></i> Aplicações</a>
                    <a href="?p=usuarios" class="nav-link <?=($p=='usuarios'?'active':'')?>"><i class="fa-solid fa-user-shield me-2 opacity-75"></i> Admins</a>
                    <a href="?p=configuracoes" class="nav-link <?=($p=='configuracoes'?'active':'')?>"><i class="fa-solid fa-sliders me-2 opacity-75"></i> Configurações</a>
                    <a href="?p=status" class="nav-link <?=($p=='status'?'active':'')?>"><i class="fa-solid fa-server me-2 opacity-75"></i> Status do Sistema</a>
                    <a href="?p=auditoria" class="nav-link <?=($p=='auditoria'?'active':'')?>"><i class="fa-solid fa-file-shield me-2 opacity-75"></i> Auditoria LGPD</a>
                    <a href="?p=erros" class="nav-link <?=($p=='erros'?'active':'')?> text-danger"><i class="fa-solid fa-triangle-exclamation me-2 opacity-75"></i> Painel de Erros</a>
                </div>
            </div>
        <?php endif; ?>
    </nav>
    <div class="mt-auto mb-3 text-center border-top border-secondary pt-3" style="opacity: 0.7;">
        <span class="badge bg-dark border border-secondary text-secondary" style="font-size: 10px;">
            CadColab <?=SYS_VERSION?><br><?=SYS_UPDATE?>
        </span>
    </div>
</div>
<div class="main-wrapper">
    <div class="topbar">
        <div><h5 class="m-0 fw-bold text-primary" style="text-transform: capitalize;"><?=str_replace('_', ' ', $p)?></h5></div>
        <div class="d-flex align-items-center gap-3">
            <i class="fa-solid fa-moon theme-toggle" onclick="toggleTheme()" title="Modo Escuro"></i>
            
            <div class="d-flex align-items-center bg-light rounded-pill px-3 py-1 border" style="cursor:pointer; transition:0.2s;" onclick="abrirMeuPerfil()" onmouseover="this.classList.add('shadow-sm')" onmouseout="this.classList.remove('shadow-sm')">
                <div class="text-end me-2" style="line-height:1.1;">
                    <span class="text-muted" style="font-size:10px; font-weight:bold; text-transform:uppercase;"><?=htmlspecialchars($_SESSION['auth']['perfil']??'')?></span><br>
                    <b style="color:var(--text-primary); font-size:13px;"><?=htmlspecialchars($_SESSION['auth']['nome']??'')?></b>
                </div>
                <i class="fa-solid fa-circle-user fa-2x text-primary"></i>
            </div>
            
            <a href="?logout=1" class="btn btn-danger btn-sm fw-bold rounded-pill px-3 shadow-sm"><i class="fa-solid fa-right-from-bracket me-1"></i> Sair</a>
        </div>
    </div>
    <div class="content-area">
        <?php if($p == 'dashboard'): ?>
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="dash-card bg-gradient-blue">
                        <i class="fa-solid fa-users bg-icon text-white"></i>
                        <div class="z-1">
                            <p class="m-0 fw-bold mt-1 text-uppercase text-white-50" style="font-size:11px; letter-spacing:1px;">Colaboradores</p>
                            <h2 class="display-5 fw-bold mb-0 text-white"><?=$conn->query("SELECT count(*) as c FROM pre_registros")->fetch_assoc()['c']?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dash-card bg-gradient-green">
                        <i class="fa-solid fa-user-check bg-icon text-white"></i>
                        <div class="z-1">
                            <p class="m-0 fw-bold mt-1 text-uppercase text-white-50" style="font-size:11px; letter-spacing:1px;">Ativos</p>
                            <h2 class="display-5 fw-bold mb-0 text-white"><?=$conn->query("SELECT count(*) as c FROM pre_registros WHERE status='ativo'")->fetch_assoc()['c']?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dash-card bg-gradient-yellow">
                        <i class="fa-solid fa-user-clock bg-icon text-white"></i>
                        <div class="z-1">
                            <p class="m-0 fw-bold mt-1 text-uppercase text-white-50" style="font-size:11px; letter-spacing:1px;">Pendentes</p>
                            <h2 class="display-5 fw-bold mb-0 text-white"><?=$conn->query("SELECT count(*) as c FROM pre_registros WHERE status='pendente'")->fetch_assoc()['c']?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dash-card bg-gradient-red">
                        <i class="fa-solid fa-user-lock bg-icon text-white"></i>
                        <div class="z-1">
                            <p class="m-0 fw-bold mt-1 text-uppercase text-white-50" style="font-size:11px; letter-spacing:1px;">Inativos</p>
                            <h2 class="display-5 fw-bold mb-0 text-white"><?=$conn->query("SELECT count(*) as c FROM pre_registros WHERE status IN ('bloqueado','inativo','desligado','demitido')")->fetch_assoc()['c']?></h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-4"><div class="col-md-7"><div class="table-card h-100"><h5 class="fw-bold mb-4" style="color:var(--text-primary); font-size:15px;"><i class="fa-solid fa-building me-2"></i>Lotação por Unidade</h5><div style="height:300px; position:relative;"><canvas id="chartUnidade"></canvas></div></div></div><div class="col-md-5"><div class="table-card h-100"><h5 class="fw-bold mb-4" style="color:var(--text-primary); font-size:15px;"><i class="fa-solid fa-shield-halved me-2"></i>Últimos Eventos</h5><div class="table-responsive" style="max-height: 250px;"><table class="table table-hover" style="font-size:12px; min-width:unset;"><tbody><?php $logs=$conn->query("SELECT * FROM logs_auditoria ORDER BY id DESC LIMIT 5"); if($logs){ while($l=$logs->fetch_assoc()): ?><tr><td style="color:var(--text-muted); padding: 8px 5px;"><?=date('d/m', strtotime($l['data_hora']))?></td><td style="padding: 8px 5px;"><b class="text-primary"><?=$l['acao']?></b></td><td style="padding: 8px 5px; text-align:right;"><span class="badge bg-secondary border text-white" style="font-size:10px;"><?=$l['usuario_admin']?></span></td></tr><?php endwhile; } ?></tbody></table></div></div></div></div>
            <script>
                new Chart(document.getElementById('chartUnidade'),{type:'doughnut',data:{labels:<?=json_encode($graf_unid['labels']??[])?>,datasets:[{data:<?=json_encode($graf_unid['data']??[])?>,backgroundColor:['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6']}]},options:{responsive:true,maintainAspectRatio:false,cutout:'75%',plugins:{legend:{position:'bottom', labels: {boxWidth: 12, font: {size: 11}}}}}});
            </script>
        
        <?php elseif($p == 'status' && $isTI): ?>
            <?php
            $db_status = 'success'; $db_text = 'Operacional';

            $smtp_status = 'danger'; $smtp_text = 'Offline';
            if(!empty($cfg_global['smtp_host'])) {
                $sock = @fsockopen(($cfg_global['smtp_port']==465?'ssl://':'tcp://').$cfg_global['smtp_host'], (int)$cfg_global['smtp_port'], $en, $es, 3);
                if($sock) { $smtp_status = 'success'; $smtp_text = 'Operacional'; fclose($sock); }
            }

            $m365_status = 'danger'; $m365_text = 'Offline';
            if(!empty($cfg_global['m365_tenant'])) {
                $ch = curl_init("https://login.microsoftonline.com/{$cfg_global['m365_tenant']}/v2.0/.well-known/openid-configuration");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                curl_exec($ch); $hc = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
                if($hc == 200) { $m365_status = 'success'; $m365_text = 'Operacional'; }
            }

            $gw_status = 'danger'; $gw_text = 'Offline (Não Configurado)';
            if(!empty($cfg_global['gw_domain'])) {
                $gw_status = 'success'; $gw_text = 'Operacional (Pronto)';
            }

            $wp_status = 'danger'; $wp_text = 'Offline';
            if(!empty($cfg_global['wp_phone_id'])) {
                $ch_wp = curl_init("https://graph.facebook.com/v18.0/{$cfg_global['wp_phone_id']}");
                curl_setopt($ch_wp, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch_wp, CURLOPT_TIMEOUT, 3);
                curl_setopt($ch_wp, CURLOPT_HTTPHEADER, ["Authorization: Bearer {$cfg_global['wp_token']}"]);
                curl_exec($ch_wp); $hc_wp = curl_getinfo($ch_wp, CURLINFO_HTTP_CODE); curl_close($ch_wp);
                if($hc_wp == 200 || $hc_wp == 400) { $wp_status = 'success'; $wp_text = 'Operacional'; }
            }
            ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fa-solid fa-server text-primary me-2"></i> Status do Sistema (Produção)</h4>
                <a href="?p=status" class="btn btn-outline-primary"><i class="fa-solid fa-rotate-right me-1"></i> Atualizar Agora</a>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="table-card h-100 d-flex align-items-center p-4">
                        <i class="fa-solid fa-database fa-3x text-primary me-4 opacity-75"></i>
                        <div>
                            <h5 class="fw-bold mb-1">Banco de Dados</h5>
                            <span class="badge bg-<?=$db_status?>-subtle text-<?=$db_status?> border border-<?=$db_status?> px-3 py-2 mt-2"><span class="status-badge bg-<?=$db_status?>"></span> <?=$db_text?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="table-card h-100 d-flex align-items-center p-4">
                        <i class="fa-brands fa-microsoft fa-3x text-primary me-4 opacity-75"></i>
                        <div>
                            <h5 class="fw-bold mb-1">Microsoft 365</h5>
                            <span class="badge bg-<?=$m365_status?>-subtle text-<?=$m365_status?> border border-<?=$m365_status?> px-3 py-2 mt-2"><span class="status-badge bg-<?=$m365_status?>"></span> <?=$m365_text?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="table-card h-100 d-flex align-items-center p-4">
                        <i class="fa-brands fa-google fa-3x text-warning me-4 opacity-75"></i>
                        <div>
                            <h5 class="fw-bold mb-1">Google Workspace</h5>
                            <span class="badge bg-<?=$gw_status?>-subtle text-<?=$gw_status?> border border-<?=$gw_status?> px-3 py-2 mt-2"><span class="status-badge bg-<?=$gw_status?>"></span> <?=$gw_text?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="table-card h-100 d-flex align-items-center p-4">
                        <i class="fa-solid fa-envelope fa-3x text-danger me-4 opacity-75"></i>
                        <div>
                            <h5 class="fw-bold mb-1">Servidor SMTP (Brevo)</h5>
                            <span class="badge bg-<?=$smtp_status?>-subtle text-<?=$smtp_status?> border border-<?=$smtp_status?> px-3 py-2 mt-2"><span class="status-badge bg-<?=$smtp_status?>"></span> <?=$smtp_text?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="table-card h-100 d-flex align-items-center p-4">
                        <i class="fa-brands fa-whatsapp fa-3x text-success me-4 opacity-75"></i>
                        <div>
                            <h5 class="fw-bold mb-1">WhatsApp API</h5>
                            <span class="badge bg-<?=$wp_status?>-subtle text-<?=$wp_status?> border border-<?=$wp_status?> px-3 py-2 mt-2"><span class="status-badge bg-<?=$wp_status?>"></span> <?=$wp_text?></span>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif($p == 'colaboradores'): ?>
            <?php if(isset($_GET['msg']) && $_GET['msg']=='csv_ok') echo "<script>Swal.fire('Importado!','".$_GET['qt']." registros inseridos.','success');</script>"; ?>
            <div class="d-flex justify-content-between mb-4"><form method="GET" class="d-flex gap-2" style="width:400px;"><input type="hidden" name="p" value="colaboradores"><input name="busca" class="form-control" placeholder="Buscar colaborador..." value="<?=htmlspecialchars($_GET['busca']??'')?>"><button class="btn btn-dark fw-bold">Buscar</button></form><div><button class="btn btn-outline-success shadow-sm me-2 fw-bold" data-bs-toggle="modal" data-bs-target="#modalCSV"><i class="fa-solid fa-file-csv me-1"></i> Importar CSV</button><button class="btn btn-primary shadow-sm fw-bold" onclick="novoColab()"><i class="fa-solid fa-plus me-1"></i> Novo</button></div></div>
            
            <div class="table-card table-responsive">
                <table class="table table-hover align-middle">
                <thead><tr><th>Colaborador</th><th>Lotação e Admissão</th><th>Acesso Corporativo</th><th>Status</th><th class="text-end" style="min-width: 140px;">Ações</th></tr></thead>
                <tbody>
                <?php $b = $conn->real_escape_string($_GET['busca'] ?? ''); $w = $b ? "WHERE nome_completo LIKE '%$b%' OR cpf LIKE '%$b%' OR matricula LIKE '%$b%'" : ""; $c=$conn->query("SELECT pr.*, c.nome as crg, u.nome as un, s.nome as seto FROM pre_registros pr LEFT JOIN cargos c ON pr.cargo_id=c.id LEFT JOIN unidades u ON pr.unidade_id=u.id LEFT JOIN setores s ON pr.setor_id=s.id $w ORDER BY pr.id DESC"); $hoje = date('Y-m-d'); if($c){ while($r=$c->fetch_assoc()): $id_exibicao = !empty($r['public_id']) ? $r['public_id'] : str_pad($r['id'], 6, '0', STR_PAD_LEFT); $em_ferias = false; if(!empty($r['ferias_inicio']) && $r['ferias_inicio'] != '0000-00-00' && !empty($r['ferias_fim']) && $r['ferias_fim'] != '0000-00-00'){ if($hoje >= $r['ferias_inicio'] && $hoje <= $r['ferias_fim']){ $em_ferias = true; } } $idade = '-'; if(!empty($r['data_nascimento']) && $r['data_nascimento'] != '0000-00-00') { $idade = (new DateTime($hoje))->diff(new DateTime($r['data_nascimento']))->y . ' anos'; } $nascimento_fmt = (!empty($r['data_nascimento']) && $r['data_nascimento'] != '0000-00-00') ? date('d/m/Y', strtotime($r['data_nascimento'])) : '-'; ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?=$r['foto_path']?'//cadcolab.enfas.com.br/'.$r['foto_path'].'?v='.time():'https://via.placeholder.com/45'?>" class="img-p me-3">
                                <div>
                                    <div class="mb-1">
                                        <span class="badge bg-secondary text-white me-1 font-monospace" title="ID Publico">ID: <?=$id_exibicao?></span>
                                        <span class="badge bg-info text-dark font-monospace" title="Matricula">MAT: <?=$r['matricula']?:'N/A'?></span>
                                    </div>
                                    <b><?=$r['nome_completo']?></b><br><span class="text-muted" style="font-size:11px;"><?=$r['cpf']?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted"><b>Nasc:</b> <?=$nascimento_fmt?> (<?=$idade?>)</span><br>
                            <span class="text-muted"><b>Adm:</b> <?=!empty($r['data_admissao']) && $r['data_admissao'] != '0000-00-00' ? date('d/m/Y', strtotime($r['data_admissao'])) : '-'?></span><br>
                            <span class="text-muted"><b>Unidade:</b> <?=$r['un']?:'-'?></span><br>
                            <span class="text-muted"><b>Cargo:</b> <span class="text-primary"><?=$r['crg']?:'-'?></span></span>
                        </td>
                        <td>
                            <span class="text-muted"><i class="fa-solid fa-phone me-1"></i><?=$r['telefone']?:'-'?></span><br>
                            <?php if($r['username_criado']): ?>
                                <span class="text-primary fw-bold" style="font-size:12px;"><i class="fa-solid fa-envelope me-1"></i><?=$r['username_criado']?>@enfas.com.br</span>
                            <?php else: ?>
                                <span class="text-warning fw-bold" style="font-size:12px;"><i class="fa-solid fa-triangle-exclamation me-1"></i>Sem E-mail Inst.</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $bc = 'warning'; 
                            if($r['status'] == 'ativo') $bc = 'success'; 
                            elseif($r['status'] == 'bloqueado') $bc = 'danger'; 
                            elseif($r['status'] == 'inativo') $bc = 'secondary';
                            elseif(in_array($r['status'], ['desligado', 'demitido'])) $bc = 'dark';
                            ?>
                            <span class="badge bg-<?=$bc?>-subtle text-<?=$bc?> border border-<?=$bc?> px-2"><?=strtoupper($r['status'])?></span>
                            <?php if($em_ferias): ?><br><span class="badge bg-info text-dark mt-1"><i class="fa-solid fa-umbrella-beach"></i> EM FÉRIAS</span><?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="d-flex flex-wrap justify-content-end" style="gap: 3px; max-width: 140px; margin-left: auto;">
                                <?php if($isRH): ?>
                                    <form method="POST" id="form_status_<?=$r['id']?>" onsubmit="this.querySelector('button').classList.add('btn-loading');">
                                        <input type="hidden" name="id" value="<?=$r['id']?>"><input type="hidden" name="action" value="change_status"><input type="hidden" name="status" id="status_val_<?=$r['id']?>" value="">
                                        <button type="button" class="btn-action text-secondary" onclick="confirmStatus(<?=$r['id']?>, '<?=$r['status']?>')" title="Alterar Status Rápido"><i class="fa-solid fa-user-tag"></i></button>
                                    </form>
                                <?php endif; ?>
                                <?php if($isTI && !empty($r['username_criado'])): ?>
                                    <form method="POST" id="form_sig_<?=$r['id']?>" onsubmit="this.querySelector('button').classList.add('btn-loading');"><input type="hidden" name="id" value="<?=$r['id']?>"><input type="hidden" name="action" value="sync_assinatura"><button type="button" class="btn-action text-info" onclick="confirmSignature(<?=$r['id']?>)" title="Sincronizar Assinatura M365"><i class="fa-solid fa-pen-nib"></i></button></form>
                                <?php endif; ?>
                                <a href="?p=cracha&id=<?=$r['id']?>" target="_blank" class="btn-action text-dark" title="Crachá"><i class="fa-solid fa-id-badge"></i></a>
                                <a href="?p=ficha&id=<?=$r['id']?>" target="_blank" class="btn-action text-primary" title="Ficha A4"><i class="fa-solid fa-file-lines"></i></a>
                                <?php if($isTI): ?><form method="POST" id="form_reset_<?=$r['id']?>" onsubmit="this.querySelector('button').classList.add('btn-loading');"><input type="hidden" name="id" value="<?=$r['id']?>"><input type="hidden" name="action" value="reset_senha"><button type="button" class="btn-action text-warning" onclick="confirmReset(<?=$r['id']?>)" title="Reset Senha"><i class="fa-solid fa-lock-open"></i></button></form><?php endif; ?>
                                <button class="btn-action text-primary" data-json="<?=base64_encode(json_encode($r))?>" onclick="editarColab(this)" title="Editar Ficha Completa"><i class="fa-solid fa-pen"></i></button>
                                <form method="POST" id="form_del_<?=$r['id']?>" onsubmit="this.querySelector('button').classList.add('btn-loading');"><input type="hidden" name="id" value="<?=$r['id']?>"><input type="hidden" name="table" value="pre_registros"><input type="hidden" name="action" value="delete"><button type="button" class="btn-action text-danger" onclick="confirmDelete(<?=$r['id']?>)" title="Excluir Colaborador"><i class="fa-solid fa-trash"></i></button></form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; } ?>
                </tbody>
                </table>
            </div>
            
        <?php elseif(in_array($p, ['unidades','setores','cargos','perfis','sistemas', 'usuarios'])): $t = $p; if($p == 'sistemas') $t = 'sistemas_hc'; if($p == 'perfis') $t = 'perfis_acesso'; if($p == 'usuarios') $t = 'usuarios_admin'; $titulo = ucfirst($p); ?>
            <div class="d-flex justify-content-between mb-4"><h4><?=$titulo?></h4><button class="btn btn-primary fw-bold shadow-sm" onclick="<?=($p=='cargos'?'openCargo()':($p=='perfis'?'openPerfil()':"openGeneric('$t')"))?>"><i class="fa-solid fa-plus me-1"></i> Novo Registro</button></div>
            <div class="table-card table-responsive"><table class="table align-middle"><thead><tr><th>ID</th><th>Descrição</th><th class="text-end">Ações</th></tr></thead><tbody><?php $query = ($p=='cargos') ? "SELECT c.*, p.nome as perfil FROM cargos c LEFT JOIN perfis_acesso p ON c.perfil_id=p.id" : "SELECT * FROM $t"; $l=$conn->query($query); if($l){ while($r=$l->fetch_assoc()): ?><tr><td><span class="badge bg-secondary font-monospace text-white">#<?=$r['id']?></span></td><td><b><?=htmlspecialchars($r['nome'] ?? $r['usuario'])?></b> <?php if(isset($r['email'])) echo "<div class='small mt-1' style='color:var(--text-muted);'><i class='fa-solid fa-envelope me-1'></i>".htmlspecialchars($r['email'])." <span class='badge bg-primary-subtle text-primary border ms-2'>".htmlspecialchars($r['perfil'])."</span></div>"; ?></td><td class="text-end"><div class="d-flex gap-1 justify-content-end"><button class="btn-action text-primary" data-json='<?=base64_encode(json_encode($r))?>' onclick="<?=($p=='cargos'?'editCargo(this)':($p=='perfis'?'editPerfil(this)':"editGeneric('$t', this)"))?>"><i class="fa-solid fa-pen"></i></button><form method="POST" id="form_del_<?=$r['id']?>" onsubmit="this.querySelector('button').classList.add('btn-loading');"><input type="hidden" name="id" value="<?=$r['id']?>"><input type="hidden" name="table" value="<?=$t?>"><input type="hidden" name="action" value="delete"><button type="button" class="btn-action text-danger" onclick="confirmDelete(<?=$r['id']?>)"><i class="fa-solid fa-trash"></i></button></form></div></td></tr><?php endwhile; } ?></tbody></table></div>
            
        <?php elseif($p == 'configuracoes' && $isTI): ?>
            <div class="d-flex justify-content-between align-items-center mb-4"><h4><i class="fa-solid fa-gears text-primary me-2"></i> Configurações Gerais</h4></div>
            <form method="POST" onsubmit="this.querySelector('button').classList.add('btn-loading');"><input type="hidden" name="action" value="save_todas_configs">
                <div class="table-card shadow-sm border-0">
                    <ul class="nav nav-tabs mb-4 border-0" role="tablist">
                        <li class="nav-item"><button class="nav-link active border-0 rounded bg-light text-primary fw-bold me-2 px-4 py-2" data-bs-toggle="tab" data-bs-target="#cfg_apis" type="button"><i class="fa-solid fa-plug me-1"></i> Integrações de API</button></li>
                        <li class="nav-item"><button class="nav-link border-0 rounded bg-light text-primary fw-bold px-4 py-2 me-2" data-bs-toggle="tab" data-bs-target="#cfg_google" type="button"><i class="fa-brands fa-google me-1"></i> Google Workspace</button></li>
                        <li class="nav-item"><button class="nav-link border-0 rounded bg-light text-primary fw-bold px-4 py-2 me-2" data-bs-toggle="tab" data-bs-target="#cfg_layouts" type="button"><i class="fa-solid fa-palette me-1"></i> Layouts e Mensagens</button></li>
                        <li class="nav-item"><button class="nav-link border-0 rounded bg-light text-primary fw-bold px-4 py-2" data-bs-toggle="tab" data-bs-target="#cfg_print" type="button"><i class="fa-solid fa-print me-1"></i> Impressão</button></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="cfg_apis">
                            <div class="row g-4">
                                <div class="col-md-6"><div class="p-4 border rounded" style="background:var(--bg-body);"><h6 class="fw-bold mb-3 text-primary"><i class="fa-brands fa-microsoft config-icon d-inline me-2"></i>Microsoft 365 (Entra ID)</h6><?php $cfgs=['m365_tenant'=>'Tenant ID','m365_client'=>'Client ID','m365_secret'=>'Secret (Client Secret)','m365_sku_basic'=>'SKU ID (Business Basic)']; foreach($cfgs as $k=>$l): ?><div class="mb-3"><label class="small fw-bold text-muted"><?=$l?></label><input name="cfg[<?=$k?>]" value="<?=htmlspecialchars($cfg_global[$k] ?? '')?>" class="form-control"></div><?php endforeach; ?></div></div>
                                <div class="col-md-6">
                                    <div class="p-4 border rounded mb-4" style="background:var(--bg-body);"><h6 class="fw-bold mb-3" style="color:#25D366;"><i class="fa-brands fa-whatsapp config-icon d-inline me-2" style="color:#25D366;"></i>WhatsApp API Oficial</h6><?php $cfgs=['wp_token'=>'Access Token','wp_phone_id'=>'Phone Number ID']; foreach($cfgs as $k=>$l): ?><div class="mb-3"><label class="small fw-bold text-muted"><?=$l?></label><input name="cfg[<?=$k?>]" value="<?=htmlspecialchars($cfg_global[$k] ?? '')?>" class="form-control"></div><?php endforeach; ?></div>
                                    <div class="p-4 border rounded" style="background:var(--bg-body);"><h6 class="fw-bold mb-3 text-danger"><i class="fa-solid fa-envelope config-icon d-inline me-2" style="color:#ea4335;"></i>Servidor SMTP (Brevo/Relay)</h6><div class="row g-2"><?php $cfgs=['smtp_host'=>'Host SMTP','smtp_port'=>'Porta','smtp_user'=>'Usuário','smtp_pass'=>'Senha','mail_from'=>'Remetente']; foreach($cfgs as $k=>$l): ?><div class="<?=($k=='smtp_host'||$k=='mail_from')?'col-md-12':'col-md-6'?> mb-2"><label class="small fw-bold text-muted"><?=$l?></label><input name="cfg[<?=$k?>]" value="<?=htmlspecialchars($cfg_global[$k] ?? '')?>" class="form-control"></div><?php endforeach; ?></div></div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="cfg_google">
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <div class="p-4 border rounded" style="background:var(--bg-body);">
                                        <h6 class="fw-bold mb-3 text-warning"><i class="fa-brands fa-google me-2"></i>Google Workspace (Directory API)</h6>
                                        <p class="text-muted small mb-4">Para provisionar contas automáticas no Google, é necessária uma Service Account JSON com Domain-Wide Delegation ativa no G Suite.</p>
                                        <div class="row g-3">
                                            <div class="col-md-12 mb-2"><label class="small fw-bold text-muted">Domínio Principal (ex: enfas.com.br)</label><input name="cfg[gw_domain]" value="<?=htmlspecialchars($cfg_global['gw_domain'] ?? '')?>" class="form-control"></div>
                                            <div class="col-md-12 mb-2"><label class="small fw-bold text-muted">Service Account Key (Cole todo o JSON aqui)</label><textarea name="cfg[gw_json]" class="form-control font-monospace" rows="8"><?=htmlspecialchars($cfg_global['gw_json'] ?? '')?></textarea></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="cfg_layouts">
                            <div class="row g-4">
                                <div class="col-md-12"><div class="p-4 border rounded" style="background:var(--bg-body);"><label class="fw-bold text-primary mb-2">Aviso Global da Tela de Login</label><p class="small text-muted mb-2">Este texto aparecerá em uma faixa amarela para todos os usuários. Deixe em branco para ocultar.</p><input type="text" name="cfg[aviso_login]" value="<?=htmlspecialchars($cfg_global['aviso_login'] ?? '')?>" class="form-control form-control-lg"></div></div>
                                <div class="col-md-6"><div class="p-4 border rounded h-100" style="background:var(--bg-body);"><label class="fw-bold text-primary mb-2">WhatsApp: Boas-Vindas M365</label><textarea name="cfg[tpl_boas_vindas_m365]" class="form-control font-monospace" rows="5"><?=htmlspecialchars($cfg_global['tpl_boas_vindas_m365'] ?? '')?></textarea></div></div>
                                <div class="col-md-6"><div class="p-4 border rounded h-100" style="background:var(--bg-body);"><label class="fw-bold text-primary mb-2">WhatsApp: Aviso de Validade</label><textarea name="cfg[tpl_aviso_validade]" class="form-control font-monospace" rows="3"><?=htmlspecialchars($cfg_global['tpl_aviso_validade'] ?? '')?></textarea><label class="small fw-bold text-muted mt-3">Dias para expirar a senha:</label><input type="number" name="cfg[dias_validade_senha]" value="<?=htmlspecialchars($cfg_global['dias_validade_senha'] ?? '90')?>" class="form-control mt-1" style="width:100px;"></div></div>
                                <div class="col-md-12"><div class="p-4 border rounded" style="background:var(--bg-body);"><h6 class="fw-bold text-primary mb-3">Recuperação de Senha (E-mail OTP)</h6><div class="row"><div class="col-md-8"><label class="small fw-bold text-muted">E-mail HTML <span class="text-danger">(Variáveis obrigatórias: {USUARIO} e {LINK})</span></label><textarea name="cfg[tpl_email]" class="form-control font-monospace" rows="6"><?=htmlspecialchars($cfg_global['tpl_email'] ?? '')?></textarea></div><div class="col-md-4"><label class="small fw-bold text-muted">Nome Template Meta (WhatsApp Auth)</label><input type="text" name="cfg[wp_template]" value="<?=htmlspecialchars($cfg_global['wp_template'] ?? '')?>" class="form-control" placeholder="ex: codigo_recuperacao_enfas"></div></div></div></div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="cfg_print">
                            <div class="row g-4">
                                <div class="col-md-12"><div class="p-4 border rounded" style="background:var(--bg-body);"><label class="fw-bold text-primary mb-2"><i class="fa-solid fa-id-card me-1"></i> HTML do Crachá</label><textarea name="cfg[tpl_cracha]" class="form-control font-monospace" rows="6"><?=htmlspecialchars($cfg_global['tpl_cracha'] ?? '')?></textarea></div></div>
                                <div class="col-md-12"><div class="p-4 border rounded" style="background:var(--bg-body);"><label class="fw-bold text-primary mb-2"><i class="fa-solid fa-file-lines me-1"></i> HTML da Ficha Cadastral</label><textarea name="cfg[tpl_ficha]" class="form-control font-monospace" rows="6"><?=htmlspecialchars($cfg_global['tpl_ficha'] ?? '')?></textarea></div></div>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-lg mt-4 w-100 fw-bold shadow">Salvar Todas as Configurações</button>
                </div>
            </form>
        <?php elseif($p == 'auditoria' && $isTI): ?>
            <div class="table-card">
                <div class="alert alert-dark border-secondary mb-4 text-center">
                    <i class="fa-solid fa-certificate text-warning fa-2x mb-2"></i><br>
                    <b class="text-white">AUDITORIA PROTEGIDA ICP-BRASIL</b><br>
                    <span class="text-muted small">Todos os eventos possuem um código de controle algorítmico inalterável de acordo com as normas da LGPD.</span>
                </div>
                <h5 class="fw-bold mb-4">Logs do Sistema</h5>
                <div class="table-responsive">
                    <table class="table small align-middle">
                        <thead><tr><th>Data / Hora</th><th>Administrador</th><th>Ação Realizada</th><th>Código de Controle (Hash)</th></tr></thead>
                        <tbody>
                        <?php $a=$conn->query("SELECT * FROM logs_auditoria ORDER BY id DESC LIMIT 50"); if($a){ while($ra=$a->fetch_assoc()): ?>
                            <tr>
                                <td style="white-space:nowrap;"><?=date('d/m/Y H:i', strtotime($ra['data_hora']))?></td>
                                <td><span class="badge bg-dark"><?=$ra['usuario_admin']?></span></td>
                                <td><b class="text-primary"><?=$ra['acao']?></b><br><span class="text-muted" style="font-size:11px;"><?=$ra['detalhes']?></span></td>
                                <td><span class="badge bg-secondary font-monospace"><?=$ra['codigo_controle']?></span></td>
                            </tr>
                        <?php endwhile; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif($p == 'erros' && $isTI): ?>
            <div class="table-card"><div class="d-flex justify-content-between align-items-center mb-4"><h5 class="m-0 text-danger">Logs de Erro</h5><form method="POST" class="d-inline"><input type="hidden" name="action" value="limpar_erros"><button class="btn btn-outline-danger btn-sm">Limpar Logs</button></form></div><table class="table small"><thead><tr><th>Data</th><th>Módulo</th><th>Erro</th></tr></thead><tbody><?php $e=$conn->query("SELECT * FROM logs_erros ORDER BY id DESC LIMIT 50"); if($e){ while($re=$e->fetch_assoc()): ?><tr><td><?=$re['data_hora']?></td><td><b><?=$re['modulo']?></b></td><td class="text-danger"><?=$re['mensagem']?></td></tr><?php endwhile; } ?></tbody></table><?php if(isset($_POST['action']) && $_POST['action']=='limpar_erros') { $conn->query("TRUNCATE TABLE logs_erros"); header("Location: ?p=erros"); exit; } ?></div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="modalMeuPerfil" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content p-4"><form method="POST"><input type="hidden" name="action" value="save_meu_perfil"><h5 class="fw-bold mb-4"><i class="fa-solid fa-user-gear text-primary me-2"></i> Meu Perfil</h5><div class="mb-3"><label class="fw-bold small text-muted">Nome</label><input name="nome" class="form-control" value="<?=htmlspecialchars($_SESSION['auth']['nome'])?>" required></div><div class="mb-3"><label class="fw-bold small text-muted">E-mail</label><input type="email" name="email" class="form-control" value="<?=htmlspecialchars($_SESSION['auth']['email']??'')?>"></div><div class="mb-3"><label class="fw-bold small text-muted">Usuário de Login</label><input name="usuario" class="form-control" value="<?=htmlspecialchars($_SESSION['auth']['usuario']??'')?>" required></div><div class="mb-4"><label class="fw-bold small text-muted">Nova Senha (deixe em branco para manter)</label><input type="password" name="senha" class="form-control" placeholder="******"></div><button class="btn btn-primary w-100 py-2 fw-bold">Salvar Alterações</button></form></div></div></div>

<div class="modal fade" id="modalCSV" tabindex="-1"><div class="modal-dialog"><div class="modal-content p-4"><form method="POST" enctype="multipart/form-data" onsubmit="this.querySelector('button').classList.add('btn-loading');"><input type="hidden" name="action" value="importar_csv"><h5>Importação CSV</h5><input type="file" name="csv_file" accept=".csv" class="form-control mb-3" required><button class="btn btn-success w-100">Importar</button></form></div></div></div>
<div class="modal fade" id="modalDynamic" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content p-4"><form method="POST" onsubmit="this.querySelector('button').classList.add('btn-loading');"><input type="hidden" name="table" id="md_table"><input type="hidden" name="id" id="md_id"><input type="hidden" name="action" value="save_generic"><h5 class="fw-bold mb-4" id="md_title">Novo Registro</h5><div id="fields_container"></div><div id="admin_fields" class="d-none"><div class="mb-3"><label>E-mail</label><input name="email" id="g_e" class="form-control"></div><div class="mb-3"><label>Usuário</label><input name="usuario" id="g_u" class="form-control"></div><div class="mb-3"><label>Senha</label><input name="senha" type="password" class="form-control"></div><div class="mb-4"><label>Perfil</label><select name="perfil" id="g_p" class="form-select"><option value="TI">TI</option><option value="Admin">Admin</option><option value="RH">RH</option></select></div><div class="mb-4"><label>Vincular a um Colaborador (Opcional)</label><select name="pre_registro_id" id="g_colab" class="form-select"><option value="">Nenhum</option><?php $colabs = $conn->query("SELECT id, nome_completo FROM pre_registros ORDER BY nome_completo"); if($colabs){ while($c = $colabs->fetch_assoc()) echo "<option value='".$c['id']."'>".$c['nome_completo']."</option>"; } ?></select><small class="text-muted" style="font-size:11px;">Vincula esta conta de painel à identidade de um funcionário.</small></div></div><button class="btn btn-primary w-100 py-2 mt-3 fw-bold">Salvar</button></form></div></div></div>
<div class="modal fade" id="modalCargo" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content p-4"><form method="POST" onsubmit="this.querySelector('button').classList.add('btn-loading');"><input type="hidden" name="id" id="mc_id"><input type="hidden" name="action" value="save_cargo"><h5 class="fw-bold mb-4">Cadastro de Cargo</h5><div class="mb-3"><label>Nome</label><input name="nome" id="mc_n" class="form-control" required></div><div class="mb-4"><label>Perfil do Cargo</label><select name="perfil_id" id="mc_p" class="form-select" required><option value="">Selecione...</option><?php $ps=$conn->query("SELECT * FROM perfis_acesso"); if($ps){ while($p=$ps->fetch_assoc()) echo "<option value='".$p['id']."'>".$p['nome']."</option>"; } ?></select></div><button class="btn btn-primary w-100 py-2 fw-bold">Salvar Cargo</button></form></div></div></div>
<div class="modal fade" id="modalPerfil" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content p-4"><form method="POST" onsubmit="this.querySelector('button').classList.add('btn-loading');"><input type="hidden" name="id" id="mp_id"><input type="hidden" name="action" value="save_perfil"><h5 class="fw-bold mb-4">Perfil de Acesso</h5><div class="mb-3"><label>Nome do Perfil</label><input name="nome" id="mp_n" class="form-control" required></div><button class="btn btn-primary w-100 py-2 fw-bold">Salvar Perfil</button></form></div></div></div>

<div class="modal fade" id="modalE" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content p-4 border-0 shadow-lg">
            <form method="POST" enctype="multipart/form-data" onsubmit="this.querySelector('button[name=\'finalizar\']') ? this.querySelector('button[name=\'finalizar\']').classList.add('btn-loading') : this.querySelector('button').classList.add('btn-loading');">
                <input type="hidden" name="id" id="e_i"><input type="hidden" name="action" value="save_colab">
                <div class="d-flex justify-content-between align-items-center mb-4"><h4 class="fw-bold m-0" id="m_e_title">Ficha de Colaborador</h4><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <ul class="nav nav-tabs mb-4 border-0" role="tablist">
                    <li class="nav-item"><button class="nav-link active border-0 rounded bg-light text-primary fw-bold me-2 px-4 py-2" data-bs-toggle="tab" data-bs-target="#pessoal" type="button">1. Pessoal</button></li>
                    <li class="nav-item"><button class="nav-link border-0 rounded bg-light text-primary fw-bold me-2 px-4 py-2" data-bs-toggle="tab" data-bs-target="#corp" type="button">2. Institucional</button></li>
                    <li class="nav-item"><button class="nav-link border-0 rounded bg-light text-primary fw-bold me-2 px-4 py-2" data-bs-toggle="tab" data-bs-target="#sist" type="button">3. Jornada e Sistemas</button></li>
                    <li class="nav-item"><button class="nav-link border-0 rounded bg-light text-primary fw-bold px-4 py-2" data-bs-toggle="tab" data-bs-target="#ferias" type="button">4. Férias e Extras</button></li>
                </ul>
                <div class="tab-content p-2">
                    <div class="tab-pane fade show active" id="pessoal">
                        <div class="row g-4">
                            <div class="col-md-2">
                                <label class="fw-bold mb-1 text-muted small">ID Colab.</label>
                                <input type="text" name="public_id" id="e_pubid" class="form-control form-control-lg fw-bold font-monospace text-center bg-light text-muted" readonly>
                                <small class="text-danger mt-1 d-block fw-bold" style="font-size:10px;"><i class="fa-solid fa-ban"></i> Identificação única.</small>
                            </div>
                            <div class="col-md-5"><label class="fw-bold mb-1 text-muted small">Nome Completo</label><input name="nome" id="e_n" class="form-control form-control-lg" required></div>
                            <div class="col-md-5"><label class="fw-bold mb-1 text-muted small">Nome da Mãe</label><input name="nome_mae" id="e_mae" class="form-control form-control-lg"></div>
                            <div class="col-md-4"><label class="fw-bold mb-1 text-muted small">CPF</label><input name="cpf" id="e_c" class="form-control form-control-lg" required></div>
                            <div class="col-md-4"><label class="fw-bold mb-1 text-muted small">Data de Nascimento</label><input name="nasc" id="e_d" type="date" class="form-control form-control-lg" required></div>
                            <div class="col-md-4"><label class="fw-bold mb-1 text-muted small">Celular / WhatsApp</label><input name="telefone" id="e_tel" class="form-control form-control-lg"></div>
                            <div class="col-md-6"><label class="fw-bold mb-1 text-muted small">E-mail Pessoal</label><input name="email_pessoal" id="e_mal" class="form-control form-control-lg" type="email"></div>
                            <div class="col-md-6"><label class="fw-bold mb-1 text-muted small">Foto de Perfil</label><input type="file" name="foto" accept="image/*" capture="user" class="form-control form-control-lg"></div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="corp">
                        <div class="row g-4">
                            <div class="col-md-4"><label class="fw-bold mb-1 text-muted small">Matrícula</label><input name="matricula" id="e_m" class="form-control form-control-lg" required></div>
                            <div class="col-md-4"><label class="fw-bold mb-1 text-muted small">Data Admissão</label><input type="date" name="data_admissao" id="e_dadm" class="form-control form-control-lg"></div>
                            <div class="col-md-4"><label class="fw-bold mb-1 text-muted small">Status</label><select name="status" id="e_s" class="form-select form-select-lg"><option value="ativo">Ativo</option><option value="pendente">Pendente</option><option value="inativo">Inativo</option><option value="bloqueado">Bloqueado</option><option value="desligado">Desligado</option><option value="demitido">Demitido</option></select></div>
                            <div class="col-md-4"><label class="fw-bold mb-1 text-muted small">Gestor</label><select name="gestor_id" id="e_gestor" class="form-select form-select-lg"><option value="">Nenhum</option><?php $gst=$conn->query("SELECT id, nome_completo FROM pre_registros WHERE status='ativo'"); if($gst){ while($g=$gst->fetch_assoc()) echo "<option value='".$g['id']."'>".$g['nome_completo']."</option>"; } ?></select></div>
                            <div class="col-md-4"><label class="fw-bold mb-1 text-muted small">Unidade</label><select name="unidade" id="e_u" class="form-select form-select-lg"><?php $un=$conn->query("SELECT * FROM unidades"); if($un){ while($u=$un->fetch_assoc()) echo "<option value='".$u['id']."'>".$u['nome']."</option>"; } ?></select></div>
                            <div class="col-md-4"><label class="fw-bold mb-1 text-muted small">Setor</label><select name="setor" id="e_t" class="form-select form-select-lg"><?php $st=$conn->query("SELECT * FROM setores"); if($st){ while($s=$st->fetch_assoc()) echo "<option value='".$s['id']."'>".$s['nome']."</option>"; } ?></select></div>
                            <div class="col-md-4"><label class="fw-bold mb-1 text-muted small">Cargo</label><select name="cargo" id="e_g" class="form-select form-select-lg"><?php $cg=$conn->query("SELECT * FROM cargos"); if($cg){ while($c=$cg->fetch_assoc()) echo "<option value='".$c['id']."'>".$c['nome']."</option>"; } ?></select></div>
                            
                            <div class="col-md-4">
                                <label class="fw-bold mb-1 text-primary small"><i class="fa-solid fa-cloud me-1"></i>Usuário Cloud (E-mail)</label>
                                <div class="input-group">
                                    <input name="username_criado" id="e_usr_m365" class="form-control form-control-lg bg-light text-muted" readonly placeholder="Aguardando Ativação">
                                    <span class="input-group-text fw-bold">@enfas.com.br</span>
                                </div>
                                <small id="e_usr_note" class="text-danger mt-1 d-block fw-bold" style="font-size:11px;"><i class="fa-solid fa-triangle-exclamation"></i> Escolhido pelo colaborador na ativação.</small>
                            </div>
                            
                            <div class="col-md-4"><label class="fw-bold mb-1 text-primary small">Licença Office 365</label><select name="m365_perfil" id="e_l" class="form-select form-select-lg"><option value="Sem Licença">Sem Licença</option><option value="Basic">Business Basic</option></select></div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="sist">
                        <div class="row g-4">
                            <div class="col-12"><h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-clock me-2"></i>Controle de Jornada</h5><div class="p-4 border rounded d-flex gap-4 align-items-center bg-light"><div class="flex-grow-1"><label class="fw-bold mb-1 text-muted small">Horário de Início</label><input type="time" name="jornada_inicio" id="e_jini" class="form-control form-control-lg" value="08:00"></div><div class="flex-grow-1"><label class="fw-bold mb-1 text-muted small">Horário de Fim</label><input type="time" name="jornada_fim" id="e_jfim" class="form-control form-control-lg" value="18:00"></div><div class="form-check mt-4"><input type="checkbox" name="ignora_jornada" id="e_ignora" class="form-check-input" value="1" style="transform: scale(1.5); margin-right:10px;"><label class="form-check-label text-danger fw-bold">Isento de Ponto (VIP)</label></div></div></div>
                            <div class="col-12 mt-4"><h6 class="fw-bold text-primary mb-3"><i class="fa-brands fa-windows me-2"></i>Aplicativos Microsoft Permissões</h6><div class="row g-2 p-3 border rounded shadow-sm bg-light"><div class="col-md-3"><div class="form-check"><input type="checkbox" name="m365_apps[]" value="Outlook" id="app_1" class="form-check-input c-app"><label class="form-check-label fw-bold" for="app_1"> Outlook</label></div></div><div class="col-md-3"><div class="form-check"><input type="checkbox" name="m365_apps[]" value="Teams" id="app_2" class="form-check-input c-app"><label class="form-check-label fw-bold" for="app_2"> Teams</label></div></div><div class="col-md-3"><div class="form-check"><input type="checkbox" name="m365_apps[]" value="OneDrive" id="app_3" class="form-check-input c-app"><label class="form-check-label fw-bold" for="app_3"> OneDrive</label></div></div><div class="col-md-3"><div class="form-check"><input type="checkbox" name="m365_apps[]" value="SharePoint" id="app_4" class="form-check-input c-app"><label class="form-check-label fw-bold" for="app_4"> SharePoint</label></div></div></div></div>
                            <div class="col-12 mt-4"><h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-desktop me-2"></i>Aplicações Adicionais Intranet</h5><div class="row g-3">
                                <?php $shc=$conn->query("SELECT * FROM sistemas_hc"); if($shc){ while($s=$shc->fetch_assoc()): ?>
                                    <div class="col-md-4"><div class="form-check p-3 border rounded shadow-sm bg-card"><input class="form-check-input ms-2 c-sys" type="checkbox" name="sistemas[]" value="<?=$s['nome']?>" id="sys_<?=$s['id']?>" style="transform:scale(1.2);"><label class="form-check-label ms-2 fw-bold" for="sys_<?=$s['id']?>"><?=$s['nome']?></label></div></div>
                                <?php endwhile; } ?>
                            </div></div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="ferias">
                        <div class="row g-4">
                            <div class="col-12"><h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-umbrella-beach me-2"></i>Período de Férias e Gestão</h5><div class="p-4 border rounded d-flex gap-4 mb-4 bg-light" style="border-left: 5px solid #ef4444 !important;"><div class="flex-grow-1"><label class="fw-bold mb-1 text-muted small">Data de Saída</label><input type="date" name="ferias_inicio" id="e_fini" class="form-control form-control-lg"></div><div class="flex-grow-1"><label class="fw-bold mb-1 text-muted small">Data de Retorno</label><input type="date" name="ferias_fim" id="e_ffim" class="form-control form-control-lg"></div></div></div>
                            <div class="col-12 mt-2"><label class="fw-bold mb-1 text-muted small">Anotações Internas do RH</label><textarea name="observacoes_rh" id="e_obs" class="form-control" rows="4"></textarea></div>
                            <div class="col-12 mt-2"><label class="fw-bold mb-1 text-muted small">Campos Extras Personalizados</label><textarea name="dados_extras" id="e_ext" class="form-control" rows="3" placeholder="Ex: Tamanho Camisa: M..."></textarea></div>
                        </div>
                    </div>
                </div>
                <button name="finalizar" class="btn btn-primary w-100 mt-4 py-3 fw-bold fs-5 shadow-sm">Salvar Ficha Completa</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleTheme() { let html = document.documentElement; let currentTheme = html.getAttribute('data-theme'); let newTheme = currentTheme === 'light' ? 'dark' : 'light'; html.setAttribute('data-theme', newTheme); localStorage.setItem('enTheme', newTheme); }
document.addEventListener('DOMContentLoaded', () => { let savedTheme = localStorage.getItem('enTheme'); if(savedTheme) document.documentElement.setAttribute('data-theme', savedTheme); });

function abrirMeuPerfil() { new bootstrap.Modal(document.getElementById('modalMeuPerfil')).show(); }

function openGeneric(t){ document.getElementById('md_table').value = t; document.getElementById('md_id').value = ""; document.getElementById('md_title').innerText = "Novo Registro"; let html = ""; if(t == 'unidades'){ html = '<div class="mb-3"><label class="fw-bold mb-1 small text-muted">Nome</label><input name="nome" class="form-control" required></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Endereço</label><input name="endereco" class="form-control"></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Telefone</label><input name="telefone" class="form-control"></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Template de Assinatura (HTML M365)</label><textarea name="template_assinatura" class="form-control font-monospace" rows="4" placeholder="Variáveis: {NOME}, {CARGO}, {SETOR}, {TELEFONE}, {EMAIL}"></textarea></div>'; } else if(t == 'setores'){ html = '<div class="mb-3"><label class="fw-bold mb-1 small text-muted">Nome</label><input name="nome" class="form-control" required></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Responsável</label><input name="responsavel" class="form-control"></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Ramal</label><input name="ramal" class="form-control"></div>'; } else if(t == 'sistemas_hc'){ html = '<div class="mb-3"><label class="fw-bold mb-1 small text-muted">Aplicação</label><input name="nome" class="form-control" required></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Link (URL)</label><input name="link" class="form-control" required></div>'; } else { html = '<div class="mb-3"><label class="fw-bold mb-1 small text-muted">Descrição</label><input name="nome" class="form-control" required></div>'; } document.getElementById('fields_container').innerHTML = html; let adm = document.getElementById('admin_fields'); if(t == 'usuarios_admin'){ adm.classList.remove('d-none'); } else { adm.classList.add('d-none'); } new bootstrap.Modal(document.getElementById('modalDynamic')).show(); }
function editGeneric(t, btn){ let rawData = btn.getAttribute('data-json'); let d = JSON.parse(atob(rawData)); document.getElementById('md_table').value = t; document.getElementById('md_id').value = d.id; document.getElementById('md_title').innerText = "Editar Registro"; let html = ""; if(t == 'unidades'){ html = '<div class="mb-3"><label class="fw-bold mb-1 small text-muted">Nome</label><input name="nome" value="'+d.nome+'" class="form-control" required></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Endereço</label><input name="endereco" value="'+d.endereco+'" class="form-control"></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Telefone</label><input name="telefone" value="'+d.telefone+'" class="form-control"></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Template de Assinatura (HTML M365)</label><textarea name="template_assinatura" class="form-control font-monospace" rows="4" placeholder="Variáveis: {NOME}, {CARGO}, {SETOR}, {TELEFONE}, {EMAIL}">'+(d.template_assinatura?d.template_assinatura:'')+'</textarea></div>'; } else if(t == 'setores'){ html = '<div class="mb-3"><label class="fw-bold mb-1 small text-muted">Nome</label><input name="nome" value="'+d.nome+'" class="form-control" required></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Responsável</label><input name="responsavel" value="'+d.responsavel+'" class="form-control"></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Ramal</label><input name="ramal" value="'+d.ramal+'" class="form-control"></div>'; } else if(t == 'sistemas_hc'){ html = '<div class="mb-3"><label class="fw-bold mb-1 small text-muted">Aplicação</label><input name="nome" value="'+d.nome+'" class="form-control" required></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Link (URL)</label><input name="link" value="'+d.link+'" class="form-control" required></div>'; } else { let v_nome = d.nome ? d.nome : d.usuario; html = '<div class="mb-3"><label class="fw-bold mb-1 small text-muted">Descrição</label><input name="nome" value="'+v_nome+'" class="form-control" required></div>'; } document.getElementById('fields_container').innerHTML = html; let adm = document.getElementById('admin_fields'); if(t == 'usuarios_admin'){ adm.classList.remove('d-none'); document.getElementById('g_e').value = d.email; document.getElementById('g_u').value = d.usuario; document.getElementById('g_p').value = d.perfil; document.getElementById('g_colab').value = d.pre_registro_id || ''; } else { adm.classList.add('d-none'); } new bootstrap.Modal(document.getElementById('modalDynamic')).show(); }
function openCargo(){ document.getElementById('mc_id').value = ""; document.getElementById('mc_n').value = ""; document.getElementById('mc_p').value = ""; new bootstrap.Modal(document.getElementById('modalCargo')).show(); } 
function editCargo(btn){ let rawData = btn.getAttribute('data-json'); let d = JSON.parse(atob(rawData)); document.getElementById('mc_id').value = d.id; document.getElementById('mc_n').value = d.nome; document.getElementById('mc_p').value = d.perfil_id; new bootstrap.Modal(document.getElementById('modalCargo')).show(); } 
function openPerfil(){ document.getElementById('mp_id').value = ""; document.getElementById('mp_n').value = ""; document.querySelectorAll('.sys-chk').forEach(c => c.checked = false); new bootstrap.Modal(document.getElementById('modalPerfil')).show(); } 
function editPerfil(btn){ let rawData = btn.getAttribute('data-json'); let d = JSON.parse(atob(rawData)); document.getElementById('mp_id').value = d.id; document.getElementById('mp_n').value = d.nome; let sysIds = d.sistemas_ids ? d.sistemas_ids.split(',') : []; document.querySelectorAll('.sys-chk').forEach(c => { c.checked = sysIds.includes(c.value); }); new bootstrap.Modal(document.getElementById('modalPerfil')).show(); }

function str_pad(n, width, z) { z = z || '0'; n = n + ''; return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n; }

function novoColab(){ 
    document.getElementById('e_i').value = ""; 
    document.getElementById('m_e_title').innerText = "Novo Colaborador"; 
    
    let generatedId = Math.floor(100000 + Math.random() * 900000);
    document.getElementById('e_pubid').value = generatedId; 
    
    document.getElementById('e_m').value = ""; 
    document.getElementById('e_n').value = ""; 
    document.getElementById('e_mae').value = ""; 
    document.getElementById('e_c').value = ""; 
    document.getElementById('e_d').value = ""; 
    document.getElementById('e_dadm').value = ""; 
    document.getElementById('e_tel').value = ""; 
    document.getElementById('e_mal').value = ""; 
    document.getElementById('e_s').value = "pendente"; 
    
    document.getElementById('e_usr_m365').value = ""; 
    document.getElementById('e_usr_note').innerHTML = "<i class='fa-solid fa-triangle-exclamation'></i> O usuário corporativo é escolhido pelo próprio colaborador na ativação.";
    document.getElementById('e_usr_note').className = "text-danger mt-1 d-block fw-bold";

    document.getElementById('e_l').value = "Sem Licença"; 
    document.getElementById('e_jini').value = "08:00"; 
    document.getElementById('e_jfim').value = "18:00"; 
    document.getElementById('e_ignora').checked = false; 
    document.getElementById('e_gestor').value = ""; 
    document.getElementById('e_fini').value = ""; 
    document.getElementById('e_ffim').value = ""; 
    document.getElementById('e_obs').value = ""; 
    document.getElementById('e_ext').value = ""; 
    document.querySelectorAll('.c-sys').forEach(cb => cb.checked = false); document.querySelectorAll('.c-app').forEach(cb => cb.checked = false); 
    new bootstrap.Modal(document.getElementById('modalE')).show(); 
}

function editarColab(btn){ 
    let rawData = btn.getAttribute('data-json'); let d = JSON.parse(atob(rawData)); 
    document.getElementById('e_i').value = d.id; 
    document.getElementById('m_e_title').innerText = "Editar Ficha de " + d.nome_completo; 
    document.getElementById('e_pubid').value = d.public_id ? d.public_id : str_pad(d.id, 6, '0'); 
    document.getElementById('e_m').value = d.matricula || ""; 
    document.getElementById('e_n').value = d.nome_completo; 
    document.getElementById('e_mae').value = d.nome_mae || ""; 
    document.getElementById('e_c').value = d.cpf; 
    
    if(d.data_nascimento && d.data_nascimento != "0000-00-00") { document.getElementById('e_d').value = d.data_nascimento; } else { document.getElementById('e_d').value = ""; } 
    if(d.data_admissao && d.data_admissao != "0000-00-00") { document.getElementById('e_dadm').value = d.data_admissao; } else { document.getElementById('e_dadm').value = ""; } 
    
    document.getElementById('e_s').value = d.status; 
    document.getElementById('e_tel').value = d.telefone || ""; 
    document.getElementById('e_mal').value = d.e_mail_pessoal || ""; 
    document.getElementById('e_u').value = d.unidade_id || ""; 
    document.getElementById('e_t').value = d.setor_id || ""; 
    document.getElementById('e_g').value = d.cargo_id || ""; 
    
    document.getElementById('e_usr_m365').value = d.username_criado || ""; 
    if(d.username_criado) {
        document.getElementById('e_usr_note').innerHTML = "<i class='fa-solid fa-check-circle'></i> Conta provisionada via portal de Autoatendimento.";
        document.getElementById('e_usr_note').className = "text-success mt-1 d-block fw-bold";
    } else {
        document.getElementById('e_usr_note').innerHTML = "<i class='fa-solid fa-triangle-exclamation'></i> O usuário corporativo é escolhido pelo próprio colaborador na ativação.";
        document.getElementById('e_usr_note').className = "text-danger mt-1 d-block fw-bold";
    }
    
    document.getElementById('e_l').value = d.m365_perfil || "Sem Licença"; 
    document.getElementById('e_jini').value = d.jornada_inicio || "08:00"; 
    document.getElementById('e_jfim').value = d.jornada_fim || "18:00"; 
    document.getElementById('e_ignora').checked = (d.ignora_jornada == 1); 
    document.getElementById('e_gestor').value = d.gestor_id || ""; 
    if(d.ferias_inicio && d.ferias_inicio != "0000-00-00") document.getElementById('e_fini').value = d.ferias_inicio; else document.getElementById('e_fini').value = ""; 
    if(d.ferias_fim && d.ferias_fim != "0000-00-00") document.getElementById('e_ffim').value = d.ferias_fim; else document.getElementById('e_ffim').value = ""; 
    document.getElementById('e_obs').value = d.observacoes_rh || ""; 
    document.getElementById('e_ext').value = d.dados_extras || ""; 
    
    let sistStr = d.sistemas_liberados || ""; document.querySelectorAll('.c-sys').forEach(cb => { cb.checked = sistStr.includes(cb.value); }); 
    let appsStr = d.m365_apps || ""; document.querySelectorAll('.c-app').forEach(cb => { cb.checked = appsStr.includes(cb.value); }); 
    new bootstrap.Modal(document.getElementById('modalE')).show(); 
} 

function confirmReset(id){ Swal.fire({ title: 'Resetar Senha?', text: 'A senha será resetada e sincronizada nas Nuvens (M365/Google).', icon: 'warning', showCancelButton: true, confirmButtonColor: '#eab308', cancelButtonText: 'Cancelar', confirmButtonText: 'Sim, Resetar!' }).then((r) => { if(r.isConfirmed) document.getElementById('form_reset_'+id).submit(); }); }
function confirmDelete(id){ Swal.fire({ title: 'Atenção!', text: 'A conta será excluída na Microsoft e no Google Workspace!', icon: 'error', showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonText: 'Cancelar', confirmButtonText: 'Sim, Excluir' }).then((r) => { if(r.isConfirmed) document.getElementById('form_del_'+id).submit(); }); }
function confirmSignature(id){ Swal.fire({ title: 'Forçar Assinatura?', text: 'A assinatura da Unidade será injetada no Outlook do usuário. Pode demorar 10 segundos.', icon: 'info', showCancelButton: true, confirmButtonColor: '#3b82f6', cancelButtonText: 'Cancelar', confirmButtonText: 'Sim, Sincronizar' }).then((r) => { if(r.isConfirmed) document.getElementById('form_sig_'+id).submit(); }); }

async function confirmStatus(id, currentStatus) {
    const { value: status } = await Swal.fire({
        title: 'Alterar Status do Colaborador',
        input: 'select',
        inputOptions: {
            'ativo': 'Ativo',
            'pendente': 'Pendente',
            'bloqueado': 'Bloqueado',
            'inativo': 'Inativo',
            'desligado': 'Desligado',
            'demitido': 'Demitido'
        },
        inputPlaceholder: 'Selecione o novo status...',
        inputValue: currentStatus,
        showCancelButton: true,
        confirmButtonColor: '#00aeef',
        confirmButtonText: 'Confirmar Alteração',
        cancelButtonText: 'Cancelar'
    });

    if (status && status !== currentStatus) {
        document.getElementById('status_val_' + id).value = status;
        document.getElementById('form_status_' + id).submit();
    }
}
</script>
</body>
</html>