<?php
// Incluir configurações primeiro, antes de qualquer saída
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../backend/config/database.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['cliente_id'])) {
    // Salvar URL atual para redirecionar de volta após o login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ../login.php');
    exit;
}

// Inicializa o carrinho se não existir
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

$titulo = 'Carrinho - Nossa Loja';
$mensagem = '';
$tipo_mensagem = '';

try {
    // Buscar informações do usuário
    $stmt = $conn->prepare("SELECT nome FROM clientes WHERE id = ?");
    $stmt->execute([$_SESSION['cliente_id']]);
    $cliente = $stmt->fetch();

    // Remove item do carrinho
    if (isset($_POST['remover']) && isset($_POST['produto_id'])) {
        $produto_id = (int)$_POST['produto_id'];
        if (isset($_SESSION['carrinho'][$produto_id])) {
            unset($_SESSION['carrinho'][$produto_id]);
            $mensagem = "Produto removido do carrinho.";
            $tipo_mensagem = "success";
        }
    }

    // Atualiza quantidade
    if (isset($_POST['atualizar']) && isset($_POST['quantidades'])) {
        foreach ($_POST['quantidades'] as $produto_id => $quantidade) {
            $quantidade = (int)$quantidade;
            if ($quantidade > 0) {
                $_SESSION['carrinho'][$produto_id] = $quantidade;
            }
        }
        $mensagem = "Carrinho atualizado com sucesso!";
        $tipo_mensagem = "success";
    }

    // Busca informações dos produtos no carrinho
    $produtos_carrinho = [];
    $total = 0;

    if (!empty($_SESSION['carrinho'])) {
        $ids = array_keys($_SESSION['carrinho']);
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        
        $stmt = $conn->prepare("SELECT * FROM produtos WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($produtos as $produto) {
            $quantidade = $_SESSION['carrinho'][$produto['id']];
            $subtotal = $produto['preco'] * $quantidade;
            $total += $subtotal;
            
            $produtos_carrinho[] = [
                'id' => $produto['id'],
                'nome' => $produto['nome'],
                'preco' => $produto['preco'],
                'quantidade' => $quantidade,
                'subtotal' => $subtotal
            ];
        }
    }
} catch (PDOException $e) {
    $mensagem = "Erro ao carregar o carrinho: " . $e->getMessage();
    $tipo_mensagem = "danger";
}

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <h1>Seu Carrinho</h1>
            <?php if ($mensagem): ?>
                <div class="alert alert-<?php echo $tipo_mensagem; ?>"><?php echo $mensagem; ?></div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($produtos_carrinho)): ?>
        <div class="alert alert-info">Seu carrinho está vazio.</div>
    <?php else: ?>
        <form method="post">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Preço</th>
                        <th>Quantidade</th>
                        <th>Subtotal</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos_carrinho as $produto): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                            <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                            <td>
                                <input type="number" name="quantidades[<?php echo $produto['id']; ?>]" 
                                       value="<?php echo $produto['quantidade']; ?>" min="1" class="form-control" style="width: 100px;">
                            </td>
                            <td>R$ <?php echo number_format($produto['subtotal'], 2, ',', '.'); ?></td>
                            <td>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="produto_id" value="<?php echo $produto['id']; ?>">
                                    <button type="submit" name="remover" class="btn btn-danger btn-sm">Remover</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td><strong>R$ <?php echo number_format($total, 2, ',', '.'); ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <div class="row">
                <div class="col">
                    <button type="submit" name="atualizar" class="btn btn-primary">Atualizar Carrinho</button>
                    <a href="finalizar_compra.php" class="btn btn-success">Finalizar Compra</a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
