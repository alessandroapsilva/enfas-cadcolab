<?php
require_once '../../autoatendimento/config/database.php';
date_default_timezone_set('America/Sao_Paulo');
$hora_atual = date('H:i:s');
$hoje = date('Y-m-d');

$cfg = []; $res = $conn->query("SELECT chave, valor FROM configuracoes");
while($row = $res->fetch_assoc()) { $cfg[$row['chave']] = $row['valor']; }
if(empty($cfg['m365_tenant']) || empty($cfg['m365_client']) || empty($cfg['m365_secret'])) die("Sem chaves API.");

$ch = curl_init("https://login.microsoftonline.com/{$cfg['m365_tenant']}/oauth2/v2.0/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['client_id'=>$cfg['m365_client'], 'scope'=>'https://graph.microsoft.com/.default', 'client_secret'=>$cfg['m365_secret'], 'grant_type'=>'client_credentials']));
$token_resp = json_decode(curl_exec($ch), true); curl_close($ch);
if(!isset($token_resp['access_token'])) die("Falha Token.");
$token = $token_resp['access_token'];

$colabs = $conn->query("SELECT id, username_criado, jornada_inicio, jornada_fim, ignora_jornada, m365_status_atual, ferias_inicio, ferias_fim FROM pre_registros WHERE status='ativo' AND username_criado != ''");
while($c = $colabs->fetch_assoc()) {
    $email = $c['username_criado'] . "@enfas.com.br";
    
    // REGRA DE OURO: SE ESTIVER DE FÉRIAS, BLOQUEIA IMEDIATAMENTE (IGNORA TUDO)
    $em_ferias = (!empty($c['ferias_inicio']) && !empty($c['ferias_fim']) && $hoje >= $c['ferias_inicio'] && $hoje <= $c['ferias_fim']);
    
    if($em_ferias) {
        $status_desejado = 'bloqueado';
    } else {
        if($c['ignora_jornada'] == 1) continue; // Pula os Diretores se não estiverem de férias
        $dentro_da_jornada = ($hora_atual >= $c['jornada_inicio'] && $hora_atual <= $c['jornada_fim']);
        $status_desejado = $dentro_da_jornada ? 'ativo' : 'bloqueado';
    }
    
    if($c['m365_status_atual'] != $status_desejado) {
        $estado_m365 = ($status_desejado == 'ativo') ? true : false;
        $ch2 = curl_init("https://graph.microsoft.com/v1.0/users/$email"); curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, "PATCH"); curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode(['accountEnabled' => $estado_m365])); curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch2, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]); curl_exec($ch2); $http_code = curl_getinfo($ch2, CURLINFO_HTTP_CODE); curl_close($ch2);
        
        if($http_code >= 200 && $http_code < 300) {
            $conn->query("UPDATE pre_registros SET m365_status_atual='$status_desejado' WHERE id=".$c['id']);
            $motivo = $em_ferias ? "Suspensão por Férias/Afastamento" : (($status_desejado == 'bloqueado') ? "Fim de Expediente" : "Início de Expediente");
            $hash = 'ICP-BR.' . strtoupper(hash('sha256', uniqid(rand(), true) . 'ROBO' . time()));
            $conn->query("INSERT INTO logs_auditoria (usuario_admin, acao, detalhes, codigo_controle) VALUES ('ROBÔ SISTEMA', '$status_desejado', 'Acesso de $email alterado: $motivo', '$hash')");
        }
    }
}
echo "Robô de Jornada e Férias executado.";
?>
