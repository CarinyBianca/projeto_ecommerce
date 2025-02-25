<?php
// Primeiro carrega as configurações e inicia a sessão
require_once 'config.php';
require_once 'database/conexao.php';

// Define o título da página
$title = 'Produtos';

// Inclui o header antes de qualquer saída
require_once 'includes/header.php';

try {
    // Busca os produtos
    $stmt = $conn->query("SELECT * FROM produtos WHERE quantidade_estoque > 0 ORDER BY nome");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $produtos = [];
    error_log('Erro ao buscar produtos: ' . $e->getMessage());
}
?>

<div class="container py-5">
    <h1 class="mb-4">Nossos Produtos</h1>

    <?php if (empty($produtos)): ?>
        <div class="alert alert-info">
            Nenhum produto disponível no momento.
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($produtos as $produto): ?>
                <div class="col">
                    <div class="card h-100">
                        <?php if (!empty($produto['imagem']) && file_exists(__DIR__ . '/uploads/produtos/' . $produto['imagem'])): ?>
                            <img src="uploads/produtos/<?= htmlspecialchars($produto['imagem']) ?>" 
                                 class="card-img-top product-image" 
                                 alt="<?= htmlspecialchars($produto['nome']) ?>">
                        <?php else: ?>
                            <div class="card-img-top product-image bg-light d-flex align-items-center justify-content-center">
                                <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($produto['nome']) ?></h5>
                            <?php if (!empty($produto['descricao'])): ?>
                                <p class="card-text"><?= nl2br(htmlspecialchars($produto['descricao'])) ?></p>
                            <?php endif; ?>
                            
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 mb-0">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></span>
                                    <form action="adicionar_ao_carrinho.php" method="post" class="add-to-cart-form">
                                        <input type="hidden" name="produto_id" value="<?= $produto['id'] ?>">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-cart-plus me-2"></i>Adicionar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="mensagem-feedback" class="alert alert-dismissible fade" role="alert" style="display: none; position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 1050;">
    <span class="mensagem-texto"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mensagemFeedback = document.getElementById('mensagem-feedback');
    
    function mostrarMensagem(texto, tipo) {
        if (mensagemFeedback) {
            const mensagemTexto = mensagemFeedback.querySelector('.mensagem-texto');
            if (mensagemTexto) {
                mensagemTexto.textContent = texto;
                mensagemFeedback.className = `alert alert-${tipo} alert-dismissible fade show`;
                mensagemFeedback.style.display = 'block';
                
                // Esconde a mensagem após 3 segundos
                setTimeout(() => {
                    mensagemFeedback.style.display = 'none';
                }, 3000);
            }
        }
    }
    
    function atualizarContadorCarrinho(quantidade) {
        const contador = document.getElementById('contador-carrinho');
        if (contador) {
            contador.textContent = quantidade;
            contador.style.display = quantidade > 0 ? 'inline' : 'none';
        }
    }
    
    document.querySelectorAll('.add-to-cart-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const botaoSubmit = this.querySelector('button[type="submit"]');
            
            if (botaoSubmit) {
                botaoSubmit.disabled = true;
                botaoSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adicionando...';
            }
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na requisição');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    mostrarMensagem(data.message, 'success');
                    atualizarContadorCarrinho(data.carrinho_quantidade);
                } else {
                    mostrarMensagem(data.message || 'Erro ao adicionar produto ao carrinho', 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarMensagem('Erro ao adicionar produto ao carrinho', 'danger');
            })
            .finally(() => {
                if (botaoSubmit) {
                    botaoSubmit.disabled = false;
                    botaoSubmit.innerHTML = '<i class="fas fa-cart-plus me-2"></i>Adicionar';
                }
            });
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>