<?php
require_once '../config.php';
require_once 'classes/Database.php';

$title = 'Carrinho - Nossa Loja';
$mensagem = '';
$tipo_mensagem = '';

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

$db = new Database();
$total = 0;
$produtos_carrinho = [];

try {
    // Obtém os produtos do carrinho
    if (!empty($_SESSION['carrinho'])) {
        $ids = array_keys($_SESSION['carrinho']);
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        
        $stmt = $db->query(
            "SELECT id, nome, preco, quantidade_estoque, peso FROM produtos WHERE id IN ($placeholders)",
            $ids
        );
        $produtos = $stmt->fetchAll();

        foreach ($produtos as $produto) {
            $quantidade = $_SESSION['carrinho'][$produto['id']];
            $subtotal = $produto['preco'] * $quantidade;
            $total += $subtotal;

            $produtos_carrinho[] = [
                'id' => $produto['id'],
                'nome' => $produto['nome'],
                'preco' => $produto['preco'],
                'quantidade' => $quantidade,
                'subtotal' => $subtotal,
                'estoque' => $produto['quantidade_estoque'],
                'peso' => $produto['peso']
            ];
        }
    }
} catch (Exception $e) {
    $mensagem = "Erro ao carregar produtos: " . $e->getMessage();
    $tipo_mensagem = "danger";
}

// Atualiza quantidade
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remover_item'])) {
        $id = (int)$_POST['remover_item'];
        if (isset($_SESSION['carrinho'][$id])) {
            unset($_SESSION['carrinho'][$id]);
            $mensagem = "Item removido do carrinho com sucesso!";
            $tipo_mensagem = "success";
            header("Location: carrinho.php");
            exit;
        }
    } elseif (isset($_POST['atualizar'])) {
        $quantidades = $_POST['quantidade'] ?? [];
        
        try {
            foreach ($quantidades as $id => $quantidade) {
                $id = (int)$id;
                $quantidade = (int)$quantidade;

                // Verifica estoque
                $stmt = $db->query("SELECT quantidade_estoque FROM produtos WHERE id = ?", [$id]);
                $produto = $stmt->fetch();

                if ($quantidade <= 0) {
                    unset($_SESSION['carrinho'][$id]);
                } elseif ($quantidade <= $produto['quantidade_estoque']) {
                    $_SESSION['carrinho'][$id] = $quantidade;
                } else {
                    throw new Exception("Quantidade solicitada maior que o estoque disponível");
                }
            }
            
            $mensagem = "Carrinho atualizado com sucesso!";
            $tipo_mensagem = "success";
            
            header("Location: carrinho.php");
            exit;
        } catch (Exception $e) {
            $mensagem = $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
}

include '../header.php';
?>

<div class="container py-5">
    <h1 class="mb-4">Carrinho de Compras</h1>

    <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensagem; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($produtos_carrinho)): ?>
        <div class="alert alert-info">
            Seu carrinho está vazio. <a href="produtos.php">Continue comprando</a>
        </div>
    <?php else: ?>
        <form method="post" action="carrinho.php">
            <div class="table-responsive">
                <table class="table table-hover">
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
                        <?php foreach ($produtos_carrinho as $item): ?>
                            <tr class="cart-item" data-id="<?php echo $item['id']; ?>">
                                <td><?php echo htmlspecialchars($item['nome']); ?></td>
                                <td>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                                <td>
                                    <div class="input-group" style="max-width: 150px;">
                                        <button type="button" class="btn btn-outline-secondary quantity-btn minus">-</button>
                                        <input type="number" name="quantidade[<?php echo $item['id']; ?>]" 
                                               class="form-control text-center quantity-input" 
                                               value="<?php echo $item['quantidade']; ?>"
                                               min="1" max="<?php echo $item['estoque']; ?>"
                                               onchange="validarQuantidade(this, <?php echo $item['estoque']; ?>)">
                                        <button type="button" class="btn btn-outline-secondary quantity-btn plus">+</button>
                                    </div>
                                </td>
                                <td>R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                                <td>
                                    <button type="submit" name="remover_item" value="<?php echo $item['id']; ?>" 
                                            class="btn btn-danger btn-sm remove-item">
                                        <i class="bi bi-trash"></i> Remover
                                    </button>
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
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="produtos.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Continuar Comprando
                </a>
                <div>
                    <button type="submit" name="atualizar" class="btn btn-primary me-2">
                        <i class="bi bi-arrow-clockwise"></i> Atualizar Carrinho
                    </button>
                    <a href="checkout.php" class="btn btn-success">
                        <i class="bi bi-cart-check"></i> Finalizar Compra
                    </a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
function validarQuantidade(input, max) {
    let value = parseInt(input.value);
    if (value < 1) input.value = 1;
    if (value > max) input.value = max;
}
</script>

<script src="../assets/js/cart.js"></script>

<?php include '../footer.php'; ?>
