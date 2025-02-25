<?php
session_start();
require_once '../backend/config/database.php';

$titulo = 'Nossa Loja - Sua Melhor Experiência de Compra';

// Buscar produtos em destaque
try {
    $stmt = $conn->prepare("SELECT * FROM produtos WHERE destaque = 1 LIMIT 6");
    $stmt->execute();
    $produtos_destaque = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $produtos_destaque = [];
}

include 'includes/header.php';
?>

<div class="container py-5">
    <!-- Banner Principal -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="bg-primary text-white p-5 rounded-3 text-center">
                <h1 class="display-4">Bem-vindo à Nossa Loja</h1>
                <p class="lead">Descubra produtos incríveis com os melhores preços!</p>
                <a href="produtos.php" class="btn btn-light btn-lg">Ver Produtos</a>
            </div>
        </div>
    </div>

    <!-- Produtos em Destaque -->
    <?php if (!empty($produtos_destaque)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-center mb-4">Produtos em Destaque</h2>
        </div>
    </div>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
        <?php foreach ($produtos_destaque as $produto): ?>
        <div class="col">
            <div class="card h-100">
                <?php if (!empty($produto['imagem'])): ?>
                <img src="<?php echo htmlspecialchars($produto['imagem']); ?>" 
                     class="card-img-top" 
                     alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars(substr($produto['descricao'], 0, 100)) . '...'; ?></p>
                    <p class="card-text">
                        <strong>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></strong>
                    </p>
                    <a href="produto.php?id=<?php echo $produto['id']; ?>" class="btn btn-primary">Ver Detalhes</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Categorias -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-center mb-4">Nossas Categorias</h2>
        </div>
    </div>
    <div class="row row-cols-1 row-cols-md-3 g-4 mb-5">
        <div class="col">
            <div class="card bg-light text-center h-100">
                <div class="card-body">
                    <h3 class="card-title">Eletrônicos</h3>
                    <p class="card-text">Encontre os melhores eletrônicos com preços incríveis.</p>
                    <a href="produtos.php?categoria=eletronicos" class="btn btn-primary">Explorar</a>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card bg-light text-center h-100">
                <div class="card-body">
                    <h3 class="card-title">Moda</h3>
                    <p class="card-text">As últimas tendências em moda para você.</p>
                    <a href="produtos.php?categoria=moda" class="btn btn-primary">Explorar</a>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card bg-light text-center h-100">
                <div class="card-body">
                    <h3 class="card-title">Casa</h3>
                    <p class="card-text">Tudo para deixar sua casa mais bonita e aconchegante.</p>
                    <a href="produtos.php?categoria=casa" class="btn btn-primary">Explorar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Newsletter -->
    <div class="row">
        <div class="col-12">
            <div class="bg-light p-5 rounded-3 text-center">
                <h2>Fique por dentro das novidades!</h2>
                <p class="lead">Cadastre-se para receber ofertas exclusivas e novidades.</p>
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <form class="row g-3" method="post" action="newsletter.php">
                            <div class="col-12 col-md-8">
                                <input type="email" class="form-control" name="email" placeholder="Seu melhor e-mail" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
