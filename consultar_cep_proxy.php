<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Verifica se a extensão cURL está disponível
if (!function_exists('curl_init')) {
    http_response_code(500);
    echo json_encode(['erro' => 'Extensão cURL não está disponível']);
    exit;
}

// Verifica se o CEP foi fornecido
if (!isset($_GET['cep'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'CEP não fornecido']);
    exit;
}

$cep = preg_replace('/[^0-9]/', '', $_GET['cep']);

// Valida o CEP
if (strlen($cep) !== 8) {
    http_response_code(400);
    echo json_encode(['erro' => 'CEP inválido']);
    exit;
}

$url = "https://viacep.com.br/ws/{$cep}/json/";

// Inicializa cURL
$ch = curl_init($url);

// Configura opções do cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout de 10 segundos

// Executa a requisição
$response = curl_exec($ch);

// Verifica erros do cURL
if ($response === false) {
    $error = curl_error($ch);
    http_response_code(500);
    echo json_encode([
        'erro' => 'Erro ao consultar CEP',
        'detalhes' => $error
    ]);
    curl_close($ch);
    exit;
}

// Fecha a conexão cURL
curl_close($ch);

// Verifica se a resposta é um JSON válido
$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode([
        'erro' => 'Resposta inválida do ViaCEP',
        'resposta_bruta' => $response
    ]);
    exit;
}

// Retorna a resposta
echo json_encode($data);
exit;
