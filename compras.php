<?php
session_start();
require_once 'config.php';
require_once 'database/conexao.php';
require_once 'includes/header.php';

try {
    // Buscar todas as compras
    $stmt = $conn->query("
        SELECT c.*, 
               COUNT(ic.produto_id) AS total_itens 
        FROM compras c
        LEFT JOIN itens_compra ic ON c.id = ic.compra_id
        GROUP BY c.id
        ORDER BY c.data DESC
    ");
    $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erro ao buscar compras: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Minhas Compras</title>
    <?php include 'includes/head.php'; ?>
</head>
<body>
    <div class="container mt-4">
        <h1>Histórico de Compras</h1>
        
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID da Compra</th>
                    <th>Data</th>
                    <th>Valor Total</th>
                    <th>Status</th>
                    <th>Total de Itens</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($compras as $compra): ?>
                    <tr>
                        <td><?= htmlspecialchars($compra['id']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($compra['data'])) ?></td>
                        <td>R$ <?= number_format($compra['valor_total'], 2, ',', '.') ?></td>
                        <td><?= htmlspecialchars($compra['status']) ?></td>
                        <td><?= $compra['total_itens'] ?></td>
                        <td>
                            <button class="btn btn-info btn-sm" onclick="verDetalhes('<?= $compra['id'] ?>')">
                                Ver Detalhes
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Modal para detalhes da compra -->
        <div class="modal fade" id="detalhesCompraModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalhes da Compra</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="detalhesCompraConteudo">
                        <!-- Conteúdo dos detalhes será carregado aqui -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
    function verDetalhes(compraId) {
        // Buscar detalhes da compra via AJAX
        fetch(`buscar_detalhes_compra.php?compra_id=${compraId}`)
            .then(response => response.text())
            .then(html => {
                // Inserir HTML no modal
                document.getElementById('detalhesCompraConteudo').innerHTML = html;
                
                // Mostrar modal
                var modal = new bootstrap.Modal(document.getElementById('detalhesCompraModal'));
                modal.show();
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Não foi possível carregar os detalhes da compra.');
            });
    }
    </script>
</body>
</html>

<?php 
// Fechar conexão
$conn = null;
?>
