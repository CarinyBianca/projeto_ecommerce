<?php
require_once 'config.php';

// Garantir que a sessão está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'carrinho_quantidade' => 0
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método não permitido';
    echo json_encode($response);
    exit;
}

if (!isset($_POST['produto_id']) || !is_numeric($_POST['produto_id'])) {
    $response['message'] = 'ID do produto inválido';
    echo json_encode($response);
    exit;
}

$produto_id = (int)$_POST['produto_id'];

try {
    // Remove o produto do carrinho
    if (isset($_SESSION['carrinho'][$produto_id])) {
        unset($_SESSION['carrinho'][$produto_id]);
    }

    // Calcula a quantidade total de itens no carrinho
    $total_itens = array_sum($_SESSION['carrinho']);

    $response = [
        'success' => true,
        'message' => 'Produto removido do carrinho',
        'carrinho_quantidade' => $total_itens
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Erro ao remover produto do carrinho: ' . $e->getMessage());
}

echo json_encode($response);
