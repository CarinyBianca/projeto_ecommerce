<?php
// Título padrão caso não seja definido
$titulo = $titulo ?? 'Nossa Loja';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/ecommerce/frontend/assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include_once 'navigation.php'; ?>
