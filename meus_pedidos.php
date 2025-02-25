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

// Buscar pedidos do cliente
$stmt = $conn->prepare("
    SELECT c.*, COUNT(ci.id) as total_itens
    FROM compras c
    LEFT JOIN compra_itens ci ON c.id = ci.compra_id
    WHERE c.cliente_id = ?
    GROUP BY c.id
    ORDER BY c.data_compra DESC
");
$stmt->execute([$_SESSION['cliente_id']]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = 'Meus Pedidos - Crow Tech';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <h1 class="mb-4">
        <i class="fas fa-shopping-bag me-2"></i>Meus Pedidos
    </h1>

    <?php if (empty($pedidos)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Você ainda não tem pedidos. <a href="produtos.php" class="alert-link">Comece a comprar</a>!
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($pedidos as $pedido): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Pedido #<?php echo $pedido['id']; ?></h5>
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
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <strong>Data:</strong> 
                                <?php echo date('d/m/Y H:i', strtotime($pedido['data_compra'])); ?>
                            </div>
                            <div class="mb-2">
                                <strong>Itens:</strong> 
                                <?php echo $pedido['total_itens']; ?>
                            </div>
                            <div class="mb-2">
                                <strong>Total:</strong> 
                                R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?>
                            </div>
                            <a href="detalhes_pedido.php?id=<?php echo $pedido['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye me-2"></i>Ver Detalhes
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
