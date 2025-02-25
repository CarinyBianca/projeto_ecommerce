<?php
require_once 'config.php';
require_once 'database/conexao.php';

// Garantir que a sessão está iniciada
ensure_session();

header('Content-Type: application/json');

// Debug da sessão
error_log('Iniciando processamento da compra...');
error_log('Session ID: ' . session_id());
error_log('SESSION data: ' . print_r($_SESSION, true));

try {
    // Verificar se está logado
    if (!isset($_SESSION['cliente_id'])) {
        throw new Exception('Usuário não está logado');
    }

    // Verificar se há itens no carrinho
    if (empty($_SESSION['carrinho'])) {
        throw new Exception('Carrinho está vazio');
    }

    // Calcular total
    $subtotal = 0;
    foreach ($_SESSION['carrinho'] as $item) {
        $subtotal += $item['quantidade'] * $item['preco'];
    }
    error_log('Subtotal calculado: ' . $subtotal);

    // Iniciar transação
    $conn->beginTransaction();
    error_log('Transação iniciada');

    // Inserir compra
    $stmt = $conn->prepare("INSERT INTO compras (cliente_id, subtotal, total, status) VALUES (?, ?, ?, 'pendente')");
    error_log('SQL compra: ' . "INSERT INTO compras (cliente_id, subtotal, total, status) VALUES ({$_SESSION['cliente_id']}, {$subtotal}, {$subtotal}, 'pendente')");
    
    if (!$stmt->execute([$_SESSION['cliente_id'], $subtotal, $subtotal])) {
        throw new Exception('Erro ao inserir compra: ' . implode(', ', $stmt->errorInfo()));
    }
    $compra_id = $conn->lastInsertId();
    error_log('Compra inserida com ID: ' . $compra_id);

    // Inserir itens
    $stmt = $conn->prepare("INSERT INTO itens_compra (compra_id, produto_id, quantidade, preco_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($_SESSION['carrinho'] as $produto_id => $item) {
        $subtotal_item = $item['quantidade'] * $item['preco'];
        error_log("Inserindo item: Produto ID {$produto_id}, Quantidade {$item['quantidade']}, Preço {$item['preco']}, Subtotal {$subtotal_item}");
        
        if (!$stmt->execute([
            $compra_id,
            $produto_id,
            $item['quantidade'],
            $item['preco'],
            $subtotal_item
        ])) {
            throw new Exception('Erro ao inserir item: ' . implode(', ', $stmt->errorInfo()));
        }

        // Atualizar estoque
        $stmt_estoque = $conn->prepare("UPDATE produtos SET quantidade_estoque = quantidade_estoque - ? WHERE id = ?");
        if (!$stmt_estoque->execute([$item['quantidade'], $produto_id])) {
            throw new Exception('Erro ao atualizar estoque: ' . implode(', ', $stmt_estoque->errorInfo()));
        }
    }

    // Confirmar transação
    $conn->commit();
    error_log('Transação confirmada com sucesso');

    // Limpar carrinho
    $_SESSION['carrinho'] = array();

    echo json_encode([
        'success' => true,
        'message' => 'Compra finalizada com sucesso!'
    ]);

} catch (Exception $e) {
    error_log('ERRO na finalização da compra: ' . $e->getMessage());
    if (isset($conn)) {
        $conn->rollBack();
        error_log('Transação revertida');
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar a compra: ' . $e->getMessage()
    ]);
}
