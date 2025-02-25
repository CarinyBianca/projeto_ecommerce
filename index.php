<?php
require_once 'config.php';
require_once 'database/conexao.php';

// Garantir que a sessão está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$title = 'Crow Tech - Sua Melhor Experiência de Compra';

// Debug da sessão
error_log('Index - Session ID: ' . session_id());
error_log('Index - Cliente ID: ' . (isset($_SESSION['cliente_id']) ? $_SESSION['cliente_id'] : 'Não logado'));

// Criar diretório de uploads se não existir
$upload_dir = __DIR__ . '/uploads/produtos';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Buscar produtos em destaque
try {
    $stmt = $conn->query("SELECT * FROM produtos WHERE destaque = 1 AND quantidade_estoque > 0 ORDER BY RAND() LIMIT 6");
    $produtos_destaque = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('Erro ao buscar produtos: ' . $e->getMessage());
    $produtos_destaque = [];
}

include 'includes/header.php';

// Exibe mensagens de feedback se houver
if (isset($_SESSION['mensagem'])): ?>
    <div class="alert alert-<?php echo $_SESSION['tipo_mensagem'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
        <?php 
        echo $_SESSION['mensagem'];
        unset($_SESSION['mensagem']);
        unset($_SESSION['tipo_mensagem']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Carrossel -->
<div id="mainCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="0" class="active"></button>
        <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="2"></button>
    </div>
    <div class="carousel-inner">
        <div class="carousel-item active">
            <div class="ratio ratio-21x9">
                <img src="assets\img\cameras-celulares.png" class="d-block w-100" alt="Ofertas Especiais">
            </div>
            <div class="carousel-caption d-none d-md-block">
                <h2>Ofertas Especiais</h2>
                <p>Confira nossos produtos com descontos imperdíveis!</p>
                <a href="produtos.php" class="btn btn-light btn-lg">Ver Ofertas</a>
            </div>
        </div>
        <div class="carousel-item">
            <div class="ratio ratio-21x9">
                <img src="assets\img\image.png" class="d-block w-100" alt="Novidades">
            </div>
            <div class="carousel-caption d-none d-md-block">
                <h2>Novidades</h2>
                <p>Conheça os produtos que acabaram de chegar!</p>
                <a href="produtos.php" class="btn btn-light btn-lg">Ver Novidades</a>
            </div>
        </div>
        <div class="carousel-item">
            <div class="ratio ratio-21x9">
                <img src="assets\img\promoblackfriday.png" class="d-block w-100" alt="Promoções">
            </div>
            <div class="carousel-caption d-none d-md-block">
                <h2>Promoções</h2>
                <p>Aproveite nossas promoções por tempo limitado!</p>
                <a href="produtos.php" class="btn btn-light btn-lg">Ver Promoções</a>
            </div>
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
        <span class="visually-hidden">Anterior</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
        <span class="visually-hidden">Próximo</span>
    </button>
</div>

<div class="container py-5">
    <!-- Produtos em Destaque -->
    <?php if (!empty($produtos_destaque)): ?>
        <h2 class="text-center mb-4">Produtos em Destaque</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($produtos_destaque as $produto): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($produto['imagem'])): ?>
                            <img src="uploads/produtos/<?php echo htmlspecialchars($produto['imagem']); ?>" 
                                 class="card-img-top product-image" 
                                 alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                        <?php else: ?>
                            <img src="assets/img/no-image.png" 
                                 class="card-img-top product-image" 
                                 alt="Imagem não disponível">
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                            <p class="card-text">
                                <?php echo htmlspecialchars(substr($produto['descricao'], 0, 100)) . '...'; ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 mb-0">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                                <button class="btn btn-primary adicionar-ao-carrinho" 
                                        data-produto-id="<?php echo $produto['id']; ?>">
                                    <i class="fas fa-cart-plus"></i> Adicionar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Nenhum produto em destaque disponível no momento.
        </div>
    <?php endif; ?>
</div>

<div id="mensagem-feedback" class="alert alert-dismissible fade" role="alert" style="display: none; position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 1050;">
    <span class="mensagem-texto"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.adicionar-ao-carrinho').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const produtoId = this.getAttribute('data-produto-id');
            fetch('adicionar_ao_carrinho.php', {
                method: 'POST',
                body: new URLSearchParams({ produto_id: produtoId }),
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                const mensagem = document.getElementById('mensagem-feedback');
                const mensagemTexto = mensagem.querySelector('.mensagem-texto');
                
                if (data.success) {
                    mensagem.className = 'alert alert-success alert-dismissible fade show';
                    mensagemTexto.textContent = data.message || 'Produto adicionado ao carrinho!';
                    
                    // Atualiza o contador do carrinho
                    const contador = document.getElementById('contador-carrinho');
                    if (contador) {
                        contador.textContent = data.carrinho_quantidade;
                        contador.style.display = 'inline';
                    }
                } else {
                    mensagem.className = 'alert alert-danger alert-dismissible fade show';
                    mensagemTexto.textContent = data.message || 'Erro ao adicionar produto ao carrinho';
                }
                
                mensagem.style.display = 'block';
                
                // Esconde a mensagem após 3 segundos
                setTimeout(() => {
                    mensagem.style.display = 'none';
                }, 3000);
            })
            .catch(error => {
                console.error('Erro:', error);
                const mensagem = document.getElementById('mensagem-feedback');
                const mensagemTexto = mensagem.querySelector('.mensagem-texto');
                mensagem.className = 'alert alert-danger alert-dismissible fade show';
                mensagemTexto.textContent = 'Erro ao processar a requisição';
                mensagem.style.display = 'block';
            });
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
