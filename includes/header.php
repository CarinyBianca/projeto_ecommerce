<?php
require_once __DIR__ . '/../config.php';

// Garantir que a sessão está iniciada
ensure_session();

// Define o caminho base do site
$base_url = '/ecommerce';

// Debug da sessão
error_log('Header - Session ID: ' . session_id());
error_log('Header - Cliente ID: ' . (isset($_SESSION['cliente_id']) ? $_SESSION['cliente_id'] : 'Não logado'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Crow Tech'; ?></title>
    
    <!-- Meta tags de segurança -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- CSS -->
    <link href="//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo $base_url; ?>/assets/css/theme.css" rel="stylesheet">
    <link href="<?php echo $base_url; ?>/assets/css/custom-theme.css" rel="stylesheet">
    
    <!-- JavaScript -->
    <script src="//cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/jquery.mask.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo $base_url; ?>/assets/js/carrinho.js"></script>
    
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        main {
            flex: 1;
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: contain;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<main class="container py-4 flex-grow-1"><?php // Fechamento da tag main está no footer.php ?>
