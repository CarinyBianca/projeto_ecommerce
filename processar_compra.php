<?php
header('Content-Type: application/json');

// Iniciar sessão para gerenciar dados do usuário
session_start();

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

    // Validar dados recebidos
    if (!$dadosCompra || 
        !isset($dadosCompra['produtos']) || 
        !isset($dadosCompra['endereco']) || 
        !isset($dadosCompra['frete']) || 
        !isset($dadosCompra['total'])) {
        throw new Exception('Dados de compra inválidos');
    }

    // Iniciar transação
    $conn->beginTransaction();

    // Gerar ID do pedido
    $pedidoId = 'PED-' . uniqid();

    // Inserir dados do pedido
    $sqlPedido = "INSERT INTO pedidos (
        pedido_id, 
        usuario_id, 
        total, 
        status, 
        cep, 
        logradouro, 
        bairro, 
        cidade, 
        estado, 
        frete_tipo, 
        frete_valor
    ) VALUES (
        :pedido_id, 
        :usuario_id, 
        :total, 
        'PENDENTE', 
        :cep, 
        :logradouro, 
        :bairro, 
        :cidade, 
        :estado, 
        :frete_tipo, 
        :frete_valor
    )";

    $stmtPedido = $conn->prepare($sqlPedido);
    $stmtPedido->execute([
        ':pedido_id' => $pedidoId,
        ':usuario_id' => $_SESSION['usuario_id'] ?? 1, // Usuário padrão se não logado
        ':total' => $dadosCompra['total'],
        ':cep' => $dadosCompra['endereco']['cep'],
        ':logradouro' => $dadosCompra['endereco']['logradouro'],
        ':bairro' => $dadosCompra['endereco']['bairro'],
        ':cidade' => $dadosCompra['endereco']['cidade'],
        ':estado' => $dadosCompra['endereco']['estado'],
        ':frete_tipo' => $dadosCompra['frete']['tipo'],
        ':frete_valor' => $dadosCompra['frete']['valor']
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

        // Atualizar estoque (opcional)
        $sqlEstoque = "UPDATE produtos SET estoque = estoque - :quantidade WHERE id = :produto_id";
        $stmtEstoque = $conn->prepare($sqlEstoque);
        $stmtEstoque->execute([
            ':quantidade' => $produto['quantidade'],
            ':produto_id' => $produto['id']
        ]);
    }

    // Confirmar transação
    $conn->commit();

    // Responder com sucesso
    echo json_encode([
        'success' => true, 
        'pedidoId' => $pedidoId,
        'message' => 'Pedido processado com sucesso'
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
        'message' => 'Erro ao processar compra: ' . $e->getMessage()
    ]);
} finally {
    // Fechar conexão
    $conn = null;
}
exit;
