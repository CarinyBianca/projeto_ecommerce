<?php
$title = 'Acesso Negado';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <h1 class="display-1 text-danger mb-4">403</h1>
            <h2 class="mb-4">Acesso Negado</h2>
            <p class="lead mb-4">Desculpe, você não tem permissão para acessar esta página.</p>
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <a href="index.php" class="btn btn-primary btn-lg px-4">
                    <i class="fas fa-home me-2"></i>Voltar para Home
                </a>
                <a href="contato.php" class="btn btn-outline-secondary btn-lg px-4">
                    <i class="fas fa-envelope me-2"></i>Contato
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
