<?php
date_default_timezone_set('America/Sao_Paulo');
require_once '../autoatendimento/config/database.php';
$conn->query("SET time_zone = '-03:00'");

$cfg = [];
$res = $conn->query("SELECT chave, valor FROM configuracoes");
while($row = $res->fetch_assoc()) { $cfg[$row['chave']] = trim($row['valor']); }
if(empty($cfg['m365_tenant'])) die("M365 não configurado.");

$ch = curl_init("https://login.microsoftonline.com/{$cfg['m365_tenant']}/oauth2/v2.0/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['client_id' => $cfg['m365_client'], 'scope' => 'https://graph.microsoft.com/.default', 'client_secret' => $cfg['m365_secret'], 'grant_type' => 'client_credentials']));
$token_resp = json_decode(curl_exec($ch), true); curl_close($ch);
if(!isset($token_resp['access_token'])) die("Falha no Token M365.");
$token = $token_resp['access_token'];

$hora_atual = date('H:i:s');
$hoje = date('Y-m-d');

$sql = "SELECT id, username_criado, jornada_inicio, jornada_fim, ferias_inicio, ferias_fim FROM pre_registros WHERE status='ativo' AND username_criado != '' AND ignora_jornada=0";
$result = $conn->query($sql);

while($colab = $result->fetch_assoc()) {
    $email = $colab['username_criado'] . "@enfas.com.br";
    $inicio = $colab['jornada_inicio'];
    $fim = $colab['jornada_fim'];
    
    $em_ferias = false;
    if(!empty($colab['ferias_inicio']) && $colab['ferias_inicio'] != '0000-00-00' && !empty($colab['ferias_fim']) && $colab['ferias_fim'] != '0000-00-00') {
        if($hoje >= $colab['ferias_inicio'] && $hoje <= $colab['ferias_fim']){
            $em_ferias = true;
        }
    }

    $deve_bloquear = false;
    if($em_ferias) {
        $deve_bloquear = true;
    } else {
        if($hora_atual < $inicio || $hora_atual > $fim) {
            $deve_bloquear = true;
        }
    }

    $ch2 = curl_init("https://graph.microsoft.com/v1.0/users/$email");
    curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode(['accountEnabled' => !$deve_bloquear]));
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]);
    curl_exec($ch2); curl_close($ch2);
}
echo "Robô de Jornada e Férias executado com sucesso! [" . date('d/m/Y H:i:s') . "]";
