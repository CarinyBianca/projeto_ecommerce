<?php
require_once 'config.php';
require_once 'database/conexao.php';

// Garantir que a sessão está iniciada
ensure_session();

// Verificar se o usuário está logado
if (!isset($_SESSION['cliente_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['PHP_SELF']));
    exit;
}

// Verificar se foi fornecido um ID de pedido
if (!isset($_GET['id'])) {
    header('Location: meus_pedidos.php');
    exit;
}

$pedido_id = (int)$_GET['id'];

// Buscar informações do pedido
$stmt = $conn->prepare("
    SELECT c.*, 
           ci.produto_id, 
           ci.quantidade, 
           ci.preco_unitario, 
           ci.subtotal as item_subtotal,
           p.nome as produto_nome,
           p.imagem as produto_imagem
    FROM compras c
    JOIN compra_itens ci ON c.id = ci.compra_id
    JOIN produtos p ON ci.produto_id = p.id
    WHERE c.id = ? AND c.cliente_id = ?
");
$stmt->execute([$pedido_id, $_SESSION['cliente_id']]);
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($itens)) {
    header('Location: meus_pedidos.php');
    exit;
}

$pedido = [
    'id' => $itens[0]['id'],
    'status' => $itens[0]['status'],
    'subtotal' => $itens[0]['subtotal'],
    'frete' => $itens[0]['frete'],
    'total' => $itens[0]['total'],
    'data_compra' => $itens[0]['data_compra']
];

$title = 'Detalhes do Pedido #' . $pedido_id . ' - Crow Tech';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="fas fa-receipt me-2"></i>Pedido #<?php echo $pedido_id; ?>
        </h1>
        <a href="meus_pedidos.php" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Itens do Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th class="text-center">Quantidade</th>
                                    <th class="text-end">Preço Unit.</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($itens as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo $item['produto_imagem']; ?>" 
                                                     alt="<?php echo $item['produto_nome']; ?>"
                                                     class="me-2"
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php echo $item['produto_nome']; ?>
                                            </div>
                                        </td>
                                        <td class="text-center"><?php echo $item['quantidade']; ?></td>
                                        <td class="text-end">R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                                        <td class="text-end">R$ <?php echo number_format($item['item_subtotal'], 2, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Resumo do Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Status:</strong>
                        <span class="badge bg-<?php 
                            echo match($pedido['status']) {
                                'pendente' => 'warning',
                                'aprovado' => 'success',
                                'cancelado' => 'danger',
                                default => 'secondary'
                            };
                        ?>">
                            <?php echo ucfirst($pedido['status']); ?>
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Data:</strong>
                        <?php echo date('d/m/Y H:i', strtotime($pedido['data_compra'])); ?>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>R$ <?php echo number_format($pedido['subtotal'], 2, ',', '.'); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Frete:</span>
                        <span><?php echo $pedido['frete'] > 0 ? 'R$ ' . number_format($pedido['frete'], 2, ',', '.') : 'Grátis'; ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total:</strong>
                        <strong>R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
