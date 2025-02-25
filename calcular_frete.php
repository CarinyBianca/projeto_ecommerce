<?php
// Habilitar exibição de erros para debug
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Definir cabeçalho como JSON
header('Content-Type: application/json');

try {
    // Impedir acesso direto
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    // Iniciar sessão se ainda não estiver iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Pegar o CEP da requisição
    $raw_input = file_get_contents('php://input');
    $input = json_decode($raw_input, true);
    
    if (!$input || !isset($input['cep'])) {
        throw new Exception('CEP não fornecido');
    }

    $cep = preg_replace('/\D/', '', $input['cep']);

    if (strlen($cep) !== 8) {
        throw new Exception('CEP inválido');
    }

    // Calcular total do carrinho
    $subtotal = 0;
    $total_itens = 0;
    if (isset($_SESSION['carrinho']) && !empty($_SESSION['carrinho'])) {
        foreach ($_SESSION['carrinho'] as $item) {
            if (isset($item['preco']) && isset($item['quantidade'])) {
                $subtotal += $item['preco'] * $item['quantidade'];
                $total_itens += $item['quantidade'];
            }
        }
    }

    // Cálculo do frete
    // Frete base de R$ 15,00 + R$ 0,50 por item adicional
    $frete = 15.00;
    if ($total_itens > 1) {
        $frete += ($total_itens - 1) * 0.50;
    }

    // Calcular total final
    $total = $subtotal + $frete;

    // Salvar o CEP na sessão
    $_SESSION['cep'] = $cep;

    $response = [
        'success' => true,
        'subtotal' => number_format($subtotal, 2, ',', '.'),
        'frete' => number_format($frete, 2, ',', '.'),
        'total' => number_format($total, 2, ',', '.'),
        'total_itens' => $total_itens,
        'cep' => $cep
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log('Erro no cálculo do frete: ' . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;
