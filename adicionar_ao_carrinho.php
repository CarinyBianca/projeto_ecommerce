<?php
require_once 'config.php';
require_once 'database/conexao.php';

header('Content-Type: application/json');

// Debug da sessão
error_log('Adicionar ao Carrinho - Session ID: ' . session_id());
error_log('Adicionar ao Carrinho - Cliente ID: ' . (isset($_SESSION['cliente_id']) ? $_SESSION['cliente_id'] : 'Não logado'));

try {
    if (!isset($_POST['produto_id'])) {
        throw new Exception('ID do produto não fornecido');
    }

    $produto_id = (int)$_POST['produto_id'];
    error_log('Adicionar ao Carrinho - Produto ID: ' . $produto_id);
    
    // Busca o produto
    $stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ? AND quantidade_estoque > 0");
    $stmt->execute([$produto_id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$produto) {
        throw new Exception('Produto não encontrado ou sem estoque');
    }
    
    // Inicializa o carrinho se necessário
    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
        error_log('Adicionar ao Carrinho - Novo carrinho criado');
    }
    
    // Verifica se já existe o produto no carrinho
    if (isset($_SESSION['carrinho'][$produto_id])) {
        // Verifica se há estoque suficiente
        if ($_SESSION['carrinho'][$produto_id]['quantidade'] >= $produto['quantidade_estoque']) {
            throw new Exception('Quantidade máxima disponível em estoque atingida');
        }
        $_SESSION['carrinho'][$produto_id]['quantidade']++;
        error_log('Adicionar ao Carrinho - Quantidade incrementada para produto existente');
    } else {
        $_SESSION['carrinho'][$produto_id] = [
            'id' => $produto['id'],
            'nome' => $produto['nome'],
            'preco' => $produto['preco'],
            'quantidade' => 1,
            'imagem' => $produto['imagem'] ?? '',
            'estoque_disponivel' => $produto['quantidade_estoque']
        ];
        error_log('Adicionar ao Carrinho - Novo produto adicionado ao carrinho');
    }
    
    // Calcula totais
    $quantidade_total = 0;
    $valor_total = 0;
    foreach ($_SESSION['carrinho'] as $item) {
        $quantidade_total += $item['quantidade'];
        $valor_total += $item['quantidade'] * $item['preco'];
    }
    
    error_log('Adicionar ao Carrinho - Totais calculados: Qtd=' . $quantidade_total . ', Valor=R$' . number_format($valor_total, 2, ',', '.'));
    
    echo json_encode([
        'success' => true,
        'message' => 'Produto adicionado ao carrinho',
        'quantidade_total' => $quantidade_total,
        'valor_total' => number_format($valor_total, 2, ',', '.'),
        'produto' => [
            'nome' => $produto['nome'],
            'preco' => number_format($produto['preco'], 2, ',', '.'),
            'quantidade' => $_SESSION['carrinho'][$produto_id]['quantidade']
        ]
    ]);

} catch (Exception $e) {
    error_log('Erro ao adicionar ao carrinho: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
