<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Impedir acesso direto
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Validar e limpar CEP
$cep = isset($_GET['cep']) ? preg_replace('/\D/', '', $_GET['cep']) : null;

if (!$cep || strlen($cep) !== 8) {
    http_response_code(400);
    echo json_encode(['error' => 'CEP inválido']);
    exit;
}

try {
    // Configurações de cURL para consulta segura
    $ch = curl_init("https://viacep.com.br/ws/{$cep}/json/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        throw new Exception('Erro de conexão: ' . curl_error($ch));
    }
    
    curl_close($ch);

    // Verificar resposta
    if ($httpCode !== 200) {
        throw new Exception("Erro HTTP: {$httpCode}");
    }

    $data = json_decode($response, true);

    // Verificar dados recebidos
    if (!$data || isset($data['erro'])) {
        http_response_code(404);
        echo json_encode(['error' => 'CEP não encontrado']);
        exit;
    }

    // Calcula frete fictício baseado no CEP
    $frete = [
        ['tipo' => 'PAC', 'valor' => 15.00],
        ['tipo' => 'SEDEX', 'valor' => 30.00]
    ];

    // Retornar dados tratados
    echo json_encode([
        'success' => true,
        'cep' => $data['cep'] ?? '',
        'logradouro' => $data['logradouro'] ?? '',
        'bairro' => $data['bairro'] ?? '',
        'cidade' => $data['localidade'] ?? '',
        'estado' => $data['uf'] ?? '',
        'frete' => $frete
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
exit;
