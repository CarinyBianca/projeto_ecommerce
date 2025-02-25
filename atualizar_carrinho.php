<?php
session_start();

// Previne que erros PHP apareçam na resposta JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// Verifica se é uma requisição AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die(json_encode(['success' => false, 'message' => 'Acesso negado']));
}

// Verifica se os dados necessários foram enviados
if (!isset($_POST['produto_id']) || !isset($_POST['quantidade'])) {
    die(json_encode(['success' => false, 'message' => 'Dados incompletos']));
}

$produto_id = filter_input(INPUT_POST, 'produto_id', FILTER_SANITIZE_NUMBER_INT);
$quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_SANITIZE_NUMBER_INT);

// Log para debug
error_log("Atualizando carrinho - Produto ID: " . $produto_id . ", Quantidade: " . $quantidade);

// Validação básica
if ($quantidade < 1) {
    die(json_encode(['success' => false, 'message' => 'Quantidade inválida']));
}

try {
    // Inclui o arquivo de configuração do banco de dados
    require_once __DIR__ . '/database/conexao.php';
    
    // Verifica se o produto existe e está disponível
    $stmt = $conn->prepare("SELECT id, nome, preco, quantidade_estoque FROM produtos WHERE id = ?");
    $stmt->execute([$produto_id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$produto) {
        error_log("Produto não encontrado: " . $produto_id);
        die(json_encode(['success' => false, 'message' => 'Produto não encontrado']));
    }
    
    // Verifica estoque
    if ($quantidade > $produto['quantidade_estoque']) {
        error_log("Quantidade solicitada maior que estoque - Produto: " . $produto_id . ", Solicitado: " . $quantidade . ", Estoque: " . $produto['quantidade_estoque']);
        die(json_encode([
            'success' => false, 
            'message' => 'Quantidade solicitada não disponível em estoque',
            'quantidade_atual' => min($quantidade, $produto['quantidade_estoque'])
        ]));
    }
    
    // Atualiza a quantidade no carrinho
    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }
    
    $_SESSION['carrinho'][$produto_id] = [
        'id' => $produto['id'],
        'nome' => $produto['nome'],
        'preco' => $produto['preco'],
        'quantidade' => $quantidade
    ];
    
    // Calcula subtotal do item
    $subtotal = floatval($produto['preco']) * intval($quantidade);
    
    // Calcula total do carrinho
    $total_carrinho = 0;
    $quantidade_total = 0;
    foreach ($_SESSION['carrinho'] as $item) {
        $total_carrinho += floatval($item['preco']) * intval($item['quantidade']);
        $quantidade_total += intval($item['quantidade']);
    }
    
    // Calcula frete baseado no subtotal
    $valor_frete = $total_carrinho >= 100 ? 0 : 20.00;
    $_SESSION['frete']['valor'] = $valor_frete;
    
    $total_com_frete = $total_carrinho + $valor_frete;
    
    error_log("Carrinho atualizado com sucesso - Total: " . $total_carrinho . ", Quantidade total: " . $quantidade_total);
    
    echo json_encode([
        'success' => true,
        'message' => 'Carrinho atualizado com sucesso',
        'produto' => [
            'id' => intval($produto['id']),
            'nome' => $produto['nome'],
            'preco' => floatval($produto['preco'])
        ],
        'quantidade' => intval($quantidade),
        'quantidade_total' => intval($quantidade_total),
        'subtotal' => number_format($subtotal, 2, '.', ''),
        'total_carrinho' => number_format($total_carrinho, 2, '.', ''),
        'valor_frete' => number_format($valor_frete, 2, '.', ''),
        'total_com_frete' => number_format($total_com_frete, 2, '.', '')
    ]);
    
} catch (PDOException $e) {
    error_log("Erro no banco de dados: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar sua solicitação. Tente novamente.'
    ]);
} catch (Exception $e) {
    error_log("Erro geral: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar sua solicitação. Tente novamente.'
    ]);
}
