<?php
require_once 'config.php';
require_once 'database/conexao.php';

// Resposta padrão
$resposta = [
    'success' => false,
    'message' => 'Ação inválida',
    'carrinho' => []
];

try {
    // Verificar ação
    if (!isset($_POST['acao'])) {
        throw new Exception('Ação não especificada');
    }

    $acao = $_POST['acao'];

    // Inicializar carrinho se não existir
    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }

    switch ($acao) {
        case 'adicionar':
            $produto_id = $_POST['produto_id'] ?? null;
            $quantidade = $_POST['quantidade'] ?? 1;

            if (!$produto_id) {
                throw new Exception('ID do produto não especificado');
            }

            // Buscar detalhes do produto
            $stmt = $conn->prepare("SELECT * FROM produtos WHERE id = :id");
            $stmt->execute([':id' => $produto_id]);
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$produto) {
                throw new Exception('Produto não encontrado');
            }

            // Verificar se produto já existe no carrinho
            $produtoExistente = false;
            foreach ($_SESSION['carrinho'] as &$item) {
                if ($item['id'] == $produto_id) {
                    $item['quantidade'] += $quantidade;
                    $produtoExistente = true;
                    break;
                }
            }

            // Se não existir, adicionar novo
            if (!$produtoExistente) {
                $_SESSION['carrinho'][] = [
                    'id' => $produto['id'],
                    'nome' => $produto['nome'],
                    'preco' => $produto['preco'],
                    'quantidade' => $quantidade,
                    'imagem' => $produto['imagem']
                ];
            }

            $resposta = [
                'success' => true, 
                'message' => 'Produto adicionado ao carrinho',
                'carrinho' => $_SESSION['carrinho']
            ];
            break;

        case 'remover':
            $produto_id = $_POST['produto_id'] ?? null;

            if (!$produto_id) {
                throw new Exception('ID do produto não especificado');
            }

            // Remover produto do carrinho
            foreach ($_SESSION['carrinho'] as $key => $item) {
                if ($item['id'] == $produto_id) {
                    unset($_SESSION['carrinho'][$key]);
                    break;
                }
            }

            // Reindexar array
            $_SESSION['carrinho'] = array_values($_SESSION['carrinho']);

            $resposta = [
                'success' => true, 
                'message' => 'Produto removido do carrinho',
                'carrinho' => $_SESSION['carrinho']
            ];
            break;

        default:
            throw new Exception('Ação não reconhecida');
    }
} catch (Exception $e) {
    $resposta['message'] = $e->getMessage();
}

// Enviar resposta JSON
header('Content-Type: application/json');
echo json_encode($resposta);
exit;
?>
