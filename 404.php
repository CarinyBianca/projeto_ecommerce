<?php
$title = 'Página Não Encontrada';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <h1 class="display-1 text-primary mb-4">404</h1>
            <h2 class="mb-4">Página Não Encontrada</h2>
            <p class="lead mb-4">Desculpe, a página que você está procurando não existe ou foi movida.</p>
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <a href="index.php" class="btn btn-primary btn-lg px-4">
                    <i class="fas fa-home me-2"></i>Voltar para Home
                </a>
                <a href="produtos.php" class="btn btn-outline-primary btn-lg px-4">
                    <i class="fas fa-shopping-bag me-2"></i>Ver Produtos
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
