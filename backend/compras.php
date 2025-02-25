<?php
session_start();

// Lista de produtos (simulado)
$produtos = [
    1 => ['nome' => 'Produto A', 'preco' => 50.00],
    2 => ['nome' => 'Produto B', 'preco' => 100.00],
    3 => ['nome' => 'Produto C', 'preco' => 75.00],
];

// Inicializa o carrinho
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Adiciona produto ao carrinho
if (isset($_POST['adicionar'])) {
    $id = intval($_POST['id']);
    if (isset($produtos[$id])) {
        $_SESSION['carrinho'][$id] = ($_SESSION['carrinho'][$id] ?? 0) + 1;
        echo json_encode(['status' => 'sucesso', 'mensagem' => 'Produto adicionado ao carrinho.']);
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Produto não encontrado.']);
    }
    exit;
}

// Remove produto do carrinho
if (isset($_POST['remover'])) {
    $id = intval($_POST['id']);
    if (isset($_SESSION['carrinho'][$id])) {
        unset($_SESSION['carrinho'][$id]);
        echo json_encode(['status' => 'sucesso', 'mensagem' => 'Produto removido do carrinho.']);
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Produto não encontrado no carrinho.']);
    }
    exit;
}

// Retorna os itens do carrinho
if (isset($_GET['listar'])) {
    $carrinho = [];
    foreach ($_SESSION['carrinho'] as $id => $quantidade) {
        if (isset($produtos[$id])) {
            $carrinho[] = [
                'id' => $id,
                'nome' => $produtos[$id]['nome'],
                'preco' => $produtos[$id]['preco'],
                'quantidade' => $quantidade,
            ];
        }
    }
    echo json_encode($carrinho);
    exit;
}
