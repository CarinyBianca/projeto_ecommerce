<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../classes/Database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $cep = preg_replace('/\D/', '', $data['cep']);
    $produtos = $data['produtos'];

    if (empty($cep) || strlen($cep) !== 8) {
        throw new Exception('CEP inválido');
    }

    $db = new Database();
    $total_peso = 0;
    $valor_produtos = 0;

    // Calcula peso total e valor dos produtos
    foreach ($produtos as $item) {
        $stmt = $db->query(
            "SELECT peso, preco FROM produtos WHERE id = ?",
            [$item['id']]
        );
        $produto = $stmt->fetch();
        
        $total_peso += $produto['peso'] * $item['quantidade'];
        $valor_produtos += $produto['preco'] * $item['quantidade'];
    }

    // Cálculo simplificado do frete (você pode implementar sua própria lógica ou usar uma API de frete)
    $frete = calcularFreteSimplificado($cep, $total_peso, $valor_produtos);

    echo json_encode([
        'success' => true,
        'frete' => $frete
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

function calcularFreteSimplificado($cep, $peso, $valor) {
    // Exemplo de cálculo simplificado
    // Na prática, você deve usar uma API de transportadora ou sua própria lógica
    $frete_base = 10.00; // Valor base
    
    // Adiciona 1% do valor dos produtos
    $frete_valor = $valor * 0.01;
    
    return $frete_base + $frete_valor;
}
