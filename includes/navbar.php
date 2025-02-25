<?php
// A sessão já deve ter sido iniciada no config.php
// Não precisamos iniciar aqui novamente

// Define o caminho base do site se ainda não estiver definido
if (!isset($base_url)) {
    $base_url = '/ecommerce';
}

// Debug da sessão
error_log('Navbar - Session ID: ' . session_id());
error_log('Navbar - Cliente ID: ' . (isset($_SESSION['cliente_id']) ? $_SESSION['cliente_id'] : 'Não logado'));
error_log('Navbar - Cliente Nome: ' . (isset($_SESSION['cliente_nome']) ? $_SESSION['cliente_nome'] : 'Não logado'));

// Define a URL base com protocolo HTTP
$site_url = 'http://' . $_SERVER['HTTP_HOST'] . $base_url;

// Determina a página atual para destacar o item do menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: rgb(72,61,139);">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $base_url; ?>/index.php">
            <i class="fas fa-store me-2"></i>Crow Tech
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" 
                       href="<?php echo $base_url; ?>/index.php">
                        <i class="fas fa-home me-1"></i>Início
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'produtos.php' ? 'active' : ''; ?>" 
                       href="<?php echo $base_url; ?>/produtos.php">
                        <i class="fas fa-shopping-bag me-1"></i>Produtos
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['cliente_id'])): ?>
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-user me-1"></i>Olá, <?php echo htmlspecialchars($_SESSION['cliente_nome']); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'carrinho.php' ? 'active' : ''; ?>" 
                           href="<?php echo $base_url; ?>/carrinho.php" id="botao-carrinho">
                            <i class="fas fa-shopping-cart me-1"></i>Carrinho
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_url; ?>/logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Sair
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'login.php' ? 'active' : ''; ?>" 
                           href="<?php echo $base_url; ?>/login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'cadastro.php' ? 'active' : ''; ?>" 
                           href="<?php echo $base_url; ?>/cadastro.php">
                            <i class="fas fa-user-plus me-1"></i>Cadastro
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
