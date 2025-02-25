<?php
header('Content-Type: application/json');

// Incluir configurações e conexão com banco de dados
require_once 'config.php';
require_once 'database/conexao.php';

// Impedir acesso direto
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    // Capturar dados da compra
    $dadosCompra = json_decode(file_get_contents('php://input'), true);

    // Iniciar transação
    $conn->beginTransaction();

    // Gerar ID único para o pedido
    $pedidoId = 'PED-' . uniqid();

    // Calcular valor total
    $total = 0;
    foreach ($dadosCompra['produtos'] as $produto) {
        $total += $produto['preco'] * $produto['quantidade'];
    }

    // Inserir pedido
    $sqlPedido = "INSERT INTO pedidos (
        pedido_id, 
        total, 
        status, 
        data_criacao
    ) VALUES (
        :pedido_id, 
        :total, 
        'PENDENTE', 
        NOW()
    )";

    $stmtPedido = $conn->prepare($sqlPedido);
    $stmtPedido->execute([
        ':pedido_id' => $pedidoId,
        ':total' => $total
    ]);

    // Inserir itens do pedido
    $sqlItem = "INSERT INTO pedido_itens (
        pedido_id, 
        produto_id, 
        quantidade, 
        preco_unitario
    ) VALUES (
        :pedido_id, 
        :produto_id, 
        :quantidade, 
        :preco_unitario
    )";

    $stmtItem = $conn->prepare($sqlItem);

    // Processar cada produto
    foreach ($dadosCompra['produtos'] as $produto) {
        $stmtItem->execute([
            ':pedido_id' => $pedidoId,
            ':produto_id' => $produto['id'],
            ':quantidade' => $produto['quantidade'],
            ':preco_unitario' => $produto['preco']
        ]);
    }

    // Confirmar transação
    $conn->commit();

    // Responder com sucesso
    echo json_encode([
        'success' => true, 
        'pedidoId' => $pedidoId,
        'total' => $total,
        'message' => 'Compra salva com sucesso'
    ]);

} catch (Exception $e) {
    // Reverter transação em caso de erro
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    // Responder com erro
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao salvar compra: ' . $e->getMessage()
    ]);
} finally {
    // Fechar conexão
    $conn = null;
}
exit;
