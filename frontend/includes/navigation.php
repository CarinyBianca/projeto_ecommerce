<?php
// Verifica se o usuário está logado
$logado = isset($_SESSION['usuario_id']);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/ecommerce/frontend/index.php">Nossa Loja</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/ecommerce/frontend/produtos.php">Produtos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/ecommerce/frontend/carrinho.php">Carrinho</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if ($logado): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/ecommerce/frontend/minha-conta.php">Minha Conta</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ecommerce/frontend/logout.php">Sair</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/ecommerce/frontend/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ecommerce/frontend/cadastro.php">Cadastro</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
