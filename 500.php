<?php
http_response_code(500);
$title = 'Erro Interno';
require_once 'includes/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <h1 class="display-1 text-warning mb-4">500</h1>
            <h2 class="mb-4">Erro Interno do Servidor</h2>
            <p class="lead mb-4">Desculpe, ocorreu um erro interno. Nossa equipe técnica já foi notificada.</p>
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <a href="index.php" class="btn btn-primary btn-lg px-4">
                    <i class="fas fa-home me-2"></i>Voltar para Home
                </a>
                <a href="contato.php" class="btn btn-outline-warning btn-lg px-4">
                    <i class="fas fa-headset me-2"></i>Suporte
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
