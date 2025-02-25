<?php
require_once 'config.php';

// Garantir que a sessão está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$response = [
    'success' => true,
    'subtotal' => 0,
    'valor_frete' => 0,
    'total' => 0
];

try {
    // Calcula o subtotal
    foreach ($_SESSION['carrinho'] as $item) {
        $response['subtotal'] += $item['preco'] * $item['quantidade'];
    }

    // Calcula o frete
    $response['valor_frete'] = $response['subtotal'] >= 100 ? 0 : 20.00;
    
    // Calcula o total
    $response['total'] = $response['subtotal'] + $response['valor_frete'];

    // Formata os valores
    $response['subtotal'] = number_format($response['subtotal'], 2, '.', '');
    $response['valor_frete'] = number_format($response['valor_frete'], 2, '.', '');
    $response['total'] = number_format($response['total'], 2, '.', '');

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Erro ao calcular totais';
    error_log('Erro ao calcular totais do carrinho: ' . $e->getMessage());
}

echo json_encode($response);
