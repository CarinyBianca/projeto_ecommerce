<?php
require_once 'config.php';
require_once 'backend/classes/Database.php';

// Inicia a sessão se não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se há uma mensagem de compra
if (!isset($_SESSION['mensagem']) || !isset($_SESSION['tipo_mensagem'])) {
    header('Location: index.php');
    exit;
}

$mensagem = $_SESSION['mensagem'];
$tipoMensagem = $_SESSION['tipo_mensagem'];

// Limpa as mensagens da sessão
unset($_SESSION['mensagem']);
unset($_SESSION['tipo_mensagem']);

$title = "Confirmação de Compra";
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card text-center">
                <div class="card-body">
                    <?php if ($tipoMensagem == 'success'): ?>
                        <div class="alert alert-success">
                            <h2>Compra Finalizada com Sucesso!</h2>
                            <p><?= htmlspecialchars($mensagem) ?></p>
                        </div>
                        <a href="index.php" class="btn btn-primary">Continuar Comprando</a>
                        <a href="meus_pedidos.php" class="btn btn-secondary">Ver Meus Pedidos</a>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <h2>Erro na Compra</h2>
                            <p><?= htmlspecialchars($mensagem) ?></p>
                        </div>
                        <a href="carrinho.php" class="btn btn-primary">Voltar ao Carrinho</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
