<?php
require_once 'config.php';
require_once 'backend/classes/Database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'subtotal' => 0,
    'total' => 0
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método não permitido';
    echo json_encode($response);
    exit;
}

if (!isset($_POST['produto_id']) || !isset($_POST['quantidade']) || 
    !is_numeric($_POST['produto_id']) || !is_numeric($_POST['quantidade'])) {
    $response['message'] = 'Dados inválidos';
    echo json_encode($response);
    exit;
}

$produto_id = (int)$_POST['produto_id'];
$quantidade = (int)$_POST['quantidade'];

if ($quantidade < 1) {
    $response['message'] = 'Quantidade inválida';
    echo json_encode($response);
    exit;
}

try {
    $db = new Database();
    
    // Verifica se o produto existe e tem estoque suficiente
    $stmt = $db->query(
        "SELECT id, preco, quantidade_estoque FROM produtos WHERE id = ?",
        [$produto_id]
    );
    $produto = $stmt->fetch();

    if (!$produto) {
        throw new Exception('Produto não encontrado');
    }

    if ($quantidade > $produto['quantidade_estoque']) {
        throw new Exception('Quantidade solicitada maior que o estoque disponível');
    }

    // Atualiza a quantidade no carrinho
    $_SESSION['carrinho'][$produto_id] = $quantidade;

    // Calcula o novo subtotal para este produto
    $subtotal = $produto['preco'] * $quantidade;

    // Calcula o novo total do carrinho
    $total = 0;
    foreach ($_SESSION['carrinho'] as $pid => $qtd) {
        $stmt = $db->query(
            "SELECT preco FROM produtos WHERE id = ?",
            [$pid]
        );
        $p = $stmt->fetch();
        if ($p) {
            $total += $p['preco'] * $qtd;
        }
    }

    $response = [
        'success' => true,
        'message' => 'Quantidade atualizada com sucesso',
        'subtotal' => $subtotal,
        'total' => $total
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Erro ao atualizar quantidade: ' . $e->getMessage());
}

echo json_encode($response);
