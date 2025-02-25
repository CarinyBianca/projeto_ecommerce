<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<meta name="description" content="Loja E-commerce de Produtos">
<meta name="keywords" content="ecommerce, loja online, produtos">

<!-- Content Security Policy -->
<meta http-equiv="Content-Security-Policy" 
      content="default-src 'self' cdn.jsdelivr.net cdnjs.cloudflare.com; 
               style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com; 
               script-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com; 
               img-src 'self' data: uploads/*; 
               connect-src 'self'">

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<!-- Custom CSS -->
<link href="assets/css/custom.css" rel="stylesheet">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Favicon -->
<link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">

<!-- Título dinâmico -->
<title><?php echo isset($page_title) ? $page_title : 'Loja E-commerce'; ?></title>
