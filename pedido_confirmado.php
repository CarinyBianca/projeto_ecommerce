<?php
require_once 'config.php';
require_once 'backend/classes/Database.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['cliente_id'])) {
    $_SESSION['mensagem'] = "Por favor, faça login para visualizar o pedido.";
    $_SESSION['tipo_mensagem'] = "warning";
    header('Location: login.php');
    exit;
}

// Verifica se há ID do pedido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensagem'] = "Pedido não encontrado.";
    $_SESSION['tipo_mensagem'] = "warning";
    header('Location: index.php');
    exit;
}

$db = new Database();
$compra_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
$title = 'Pedido Confirmado';

try {
    // Busca informações da compra
    $stmt = $db->query(
        "SELECT c.*, cl.nome as cliente_nome, cl.email 
         FROM compras c 
         JOIN clientes cl ON c.cliente_id = cl.id 
         WHERE c.id = ? AND c.cliente_id = ?",
        [$compra_id, $_SESSION['cliente_id']]
    );
    $compra = $stmt->fetch();

    if (!$compra) {
        throw new Exception("Pedido não encontrado");
    }

    // Busca os itens da compra
    $stmt = $db->query(
        "SELECT ci.*, p.nome as produto_nome, p.imagem 
         FROM compra_itens ci 
         JOIN produtos p ON ci.produto_id = p.id 
         WHERE ci.compra_id = ?",
        [$compra_id]
    );
    $itens = $stmt->fetchAll();

} catch (Exception $e) {
    $_SESSION['mensagem'] = "Erro ao carregar pedido: " . $e->getMessage();
    $_SESSION['tipo_mensagem'] = "danger";
    header('Location: index.php');
    exit;
}

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    <h1 class="card-title mt-3">Pedido Confirmado!</h1>
                    <p class="card-text">Seu pedido #<?php echo $compra_id; ?> foi processado com sucesso.</p>
                    <p class="text-muted">Um e-mail de confirmação foi enviado para <?php echo htmlspecialchars($compra['email']); ?></p>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Detalhes do Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <h6 class="mb-3">Dados do Cliente</h6>
                            <div><strong>Nome:</strong> <?php echo htmlspecialchars($compra['cliente_nome']); ?></div>
                            <div><strong>E-mail:</strong> <?php echo htmlspecialchars($compra['email']); ?></div>
                        </div>
                        <div class="col-sm-6">
                            <h6 class="mb-3">Informações do Pedido</h6>
                            <div><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($compra['data_compra'])); ?></div>
                            <div><strong>Status:</strong> 
                                <span class="badge bg-<?php echo $compra['status'] === 'pendente' ? 'warning' : 'success'; ?>">
                                    <?php echo $compra['status'] === 'pendente' ? 'Pendente' : ucfirst($compra['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Endereço de Entrega</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">
                                <?php echo htmlspecialchars($compra['logradouro_entrega']); ?>, 
                                <?php echo htmlspecialchars($compra['numero_entrega']); ?>
                                <?php if (!empty($compra['complemento_entrega'])): ?>
                                    - <?php echo htmlspecialchars($compra['complemento_entrega']); ?>
                                <?php endif; ?><br>
                                <?php echo htmlspecialchars($compra['bairro_entrega']); ?><br>
                                <?php echo htmlspecialchars($compra['cidade_entrega']); ?> - 
                                <?php echo htmlspecialchars($compra['estado_entrega']); ?><br>
                                CEP: <?php echo htmlspecialchars($compra['cep_entrega']); ?>
                            </p>
                        </div>
                    </div>

                    <h6 class="mb-3">Itens do Pedido</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
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
                                                <?php if (!empty($item['imagem'])): ?>
                                                    <img src="<?php echo htmlspecialchars($item['imagem']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['produto_nome']); ?>"
                                                         class="me-2" style="width: 50px; height: 50px; object-fit: contain;">
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($item['produto_nome']); ?>
                                            </div>
                                        </td>
                                        <td class="text-center"><?php echo $item['quantidade']; ?></td>
                                        <td class="text-end">R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                                        <td class="text-end">R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td class="text-end">R$ <?php echo number_format($compra['subtotal'], 2, ',', '.'); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Frete:</strong></td>
                                    <td class="text-end">R$ <?php echo number_format($compra['frete'], 2, ',', '.'); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-end"><strong>R$ <?php echo number_format($compra['total'], 2, ',', '.'); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag me-2"></i>Voltar para a Loja
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Mostrar alerta de sucesso quando a página carregar
window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    const fromCheckout = urlParams.get('from') === 'checkout';
    
    if (fromCheckout) {
        Swal.fire({
            title: 'Pedido Confirmado!',
            text: 'Sua compra foi realizada com sucesso! Em breve você receberá mais informações por e-mail.',
            icon: 'success',
            confirmButtonText: 'Continuar',
            confirmButtonColor: '#28a745',
            footer: 'Agradecemos a preferência!'
        });
    }
};
</script>

<?php include 'includes/footer.php'; ?>
