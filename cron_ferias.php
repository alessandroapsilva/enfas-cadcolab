<?php
// ROBÔ DE FÉRIAS ENFAS - Roda 1x por dia de madrugada
date_default_timezone_set('America/Sao_Paulo');
require_once '/var/www/autoatendimento/config/database.php'; // Conexão com o banco

// 1. Prepara o Banco de Dados para saber quem já teve o aviso ativado
$check_col = $conn->query("SHOW COLUMNS FROM pre_registros LIKE 'm365_oof_ativo'");
if($check_col->num_rows == 0) { $conn->query("ALTER TABLE pre_registros ADD COLUMN m365_oof_ativo INT DEFAULT 0"); }

// 2. Pega as Chaves da Microsoft
$cfg = [];
$res = $conn->query("SELECT chave, valor FROM configuracoes");
while($row = $res->fetch_assoc()) { $cfg[$row['chave']] = trim($row['valor']); }
if(empty($cfg['m365_tenant'])) die("Configurações M365 ausentes.\n");

// 3. Pede o Token de Acesso M365
$ch = curl_init("https://login.microsoftonline.com/{$cfg['m365_tenant']}/oauth2/v2.0/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'client_id' => $cfg['m365_client'], 
    'scope' => 'https://graph.microsoft.com/.default', 
    'client_secret' => $cfg['m365_secret'], 
    'grant_type' => 'client_credentials'
]));
$token_resp = json_decode(curl_exec($ch), true); curl_close($ch);
if(!isset($token_resp['access_token'])) die("Falha ao obter Token M365.\n");
$token = $token_resp['access_token'];

$hoje = date('Y-m-d');
echo "Iniciando varredura de férias em: $hoje\n";

// 4. Busca todos os funcionários ativos
$sql = "SELECT p.*, g.nome_completo as nome_gestor, g.username_criado as email_gestor FROM pre_registros p LEFT JOIN pre_registros g ON p.gestor_id = g.id WHERE p.status='ativo' AND p.username_criado != ''";
$users = $conn->query($sql);

while($user = $users->fetch_assoc()) {
    $email = $user['username_criado'] . "@enfas.com.br";
    
    // Verifica se hoje está DENTRO do período de férias
    $em_ferias_agora = false;
    if(!empty($user['ferias_inicio']) && $user['ferias_inicio'] != '0000-00-00' && !empty($user['ferias_fim']) && $user['ferias_fim'] != '0000-00-00') {
        if($hoje >= $user['ferias_inicio'] && $hoje <= $user['ferias_fim']) {
            $em_ferias_agora = true;
        }
    }
    
    // AÇÃO 1: Ligar o Aviso (Se entrou de férias hoje e o aviso ainda não foi ligado)
    if($em_ferias_agora && $user['m365_oof_ativo'] == 0) {
        $data_retorno = date('d/m/Y', strtotime($user['ferias_fim'] . ' +1 day'));
        $contato_gestor = $user['nome_gestor'] ? "Em caso de urgência, por favor, entre em contato com meu gestor(a) <b>{$user['nome_gestor']}</b> ({$user['email_gestor']}@enfas.com.br)." : "Por favor, procure a recepção ou a administração da clínica.";
        
        $msg_html = "<html><body><p>Olá,</p><p>Estarei ausente por motivo de férias e terei acesso limitado ao e-mail. Retorno às atividades no dia <b>{$data_retorno}</b>.</p><p>{$contato_gestor}</p><p>Atenciosamente,<br>{$user['nome_completo']}</p></body></html>";

        $payload = [
            "automaticRepliesSetting" => [
                "status" => "alwaysEnabled",
                "internalReplyMessage" => $msg_html,
                "externalReplyMessage" => $msg_html
            ]
        ];

        $ch2 = curl_init("https://graph.microsoft.com/v1.0/users/$email/mailboxSettings");
        curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]);
        curl_exec($ch2); curl_close($ch2);
        
        $conn->query("UPDATE pre_registros SET m365_oof_ativo=1 WHERE id=".$user['id']);
        echo "[+] Férias ATIVADAS para $email\n";
    }
    
    // AÇÃO 2: Desligar o Aviso (Se as férias acabaram e o aviso ainda está ligado)
    if(!$em_ferias_agora && $user['m365_oof_ativo'] == 1) {
        $payload = [ "automaticRepliesSetting" => [ "status" => "disabled" ] ];
        
        $ch2 = curl_init("https://graph.microsoft.com/v1.0/users/$email/mailboxSettings");
        curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]);
        curl_exec($ch2); curl_close($ch2);
        
        $conn->query("UPDATE pre_registros SET m365_oof_ativo=0 WHERE id=".$user['id']);
        echo "[-] Férias DESATIVADAS para $email\n";
    }
}
echo "Varredura concluída com sucesso.\n";
?>