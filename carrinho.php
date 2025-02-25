<?php
require_once 'config.php';
require_once 'database/conexao.php';

// Garantir que a sessão está iniciada
ensure_session();

// Debug da sessão
error_log('Carrinho - Início do script');
error_log('Carrinho - Session ID: ' . session_id());
error_log('Carrinho - Cliente ID: ' . (isset($_SESSION['cliente_id']) ? $_SESSION['cliente_id'] : 'Não logado'));

// Verificar se o usuário está logado
$logado = isset($_SESSION['cliente_id']);
error_log('Carrinho - Usuário está logado? ' . ($logado ? 'Sim' : 'Não'));

// Se não estiver logado, salvar URL atual para redirecionamento
if (!$logado) {
    $_SESSION['redirect_after_login'] = 'carrinho.php';
    error_log('Carrinho - URL de redirecionamento salva: carrinho.php');
}

// Inicializar carrinho se necessário
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = array();
    error_log('Carrinho - Carrinho inicializado');
}

// Configurações da página
$title = 'Carrinho de Compras - Crow Tech';
require_once 'includes/header.php';

// Calcular totais
$subtotal = 0;
$quantidade_total = 0;

if (!empty($_SESSION['carrinho'])) {
    foreach ($_SESSION['carrinho'] as $item) {
        $quantidade_total += $item['quantidade'];
        $subtotal += $item['quantidade'] * $item['preco'];
    }
    
    // Inicialmente o total é igual ao subtotal (sem frete)
    $total = $subtotal;

    // Remover cálculo inicial do frete
    unset($_SESSION['frete']);

    // Buscar informações atualizadas dos produtos
    $ids = array_keys($_SESSION['carrinho']);
    if (!empty($ids)) {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        
        $stmt = $conn->prepare("SELECT id, nome, preco, imagem, quantidade_estoque FROM produtos WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Atualizar informações do carrinho com dados do banco
        foreach ($produtos as $produto) {
            $id = $produto['id'];
            if (isset($_SESSION['carrinho'][$id])) {
                $_SESSION['carrinho'][$id]['nome'] = $produto['nome'];
                $_SESSION['carrinho'][$id]['preco'] = $produto['preco'];
                $_SESSION['carrinho'][$id]['imagem'] = $produto['imagem'];
                $_SESSION['carrinho'][$id]['estoque_disponivel'] = $produto['quantidade_estoque'];
            }
        }
    }
} else {
    $total = 0;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho de Compras</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: Arial, sans-serif; }
        .table > tbody > tr > td { 
            vertical-align: middle;
            padding: 1rem;
        }
        .produto-imagem {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .quantidade-input {
            width: 80px !important;
            text-align: center;
            border-radius: 6px;
        }
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
        }
        .produto-nome {
            font-weight: 500;
            color: #2c3e50;
        }
        .preco {
            font-weight: 600;
            color: #2c3e50;
        }
        .subtotal {
            font-weight: 600;
            color: #2c3e50;
        }
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            padding: 1rem;
            font-weight: 600;
            color: #2c3e50;
        }
        .table tbody tr {
            border-bottom: 1px solid #dee2e6;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .btn-remover {
            transition: all 0.2s ease-in-out;
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
        }
        .btn-remover:hover {
            transform: scale(1.05);
            background-color: #dc3545;
            color: white;
        }
        .btn-remover i {
            font-size: 1rem;
        }
        .alert {
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <?php require_once 'includes/header.php'; ?>

    <?php if (isset($_SESSION['mensagem'])): ?>
        <div class="alert alert-<?php echo $_SESSION['tipo_mensagem'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['mensagem'];
            unset($_SESSION['mensagem']);
            unset($_SESSION['tipo_mensagem']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!isset($_SESSION['cliente_id'])): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Você precisa estar logado para finalizar a compra. 
            <a href="login.php?redirect=carrinho.php" class="alert-link">Clique aqui para fazer login</a> ou 
            <a href="cadastro.php" class="alert-link">cadastre-se</a>.
        </div>
    <?php endif; ?>

    <div class="container py-4">
        <h1 class="mb-4"><i class="fas fa-shopping-cart me-2"></i>Carrinho de Compras</h1>

        <?php if (empty($_SESSION['carrinho'])): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Seu carrinho está vazio.
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th scope="col" class="ps-4">Produto</th>
                                            <th scope="col" class="text-center" style="width: 140px;">Quantidade</th>
                                            <th scope="col" class="text-end">Preço</th>
                                            <th scope="col" class="text-end pe-4">Subtotal</th>
                                            <th scope="col" class="text-center" style="width: 60px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($_SESSION['carrinho'] as $id => $item): ?>
                                            <tr data-produto-id="<?php echo $id; ?>">
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <?php if (!empty($item['imagem'])): ?>
                                                            <img src="uploads/produtos/<?php echo htmlspecialchars($item['imagem']); ?>" 
                                                                 alt="<?php echo htmlspecialchars($item['nome']); ?>" 
                                                                 class="produto-imagem me-3">
                                                        <?php endif; ?>
                                                        <div>
                                                            <h6 class="produto-nome mb-1"><?php echo htmlspecialchars($item['nome']); ?></h6>
                                                            <small class="text-muted">Código: #<?php echo $id; ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="quantidade-controle mx-auto">
                                                        <button type="button" class="btn btn-outline-secondary diminuir-quantidade" data-produto-id="<?php echo $id; ?>">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                        <span class="quantidade-valor" data-produto-id="<?php echo $id; ?>"><?php echo $item['quantidade']; ?></span>
                                                        <button type="button" class="btn btn-outline-secondary aumentar-quantidade" data-produto-id="<?php echo $id; ?>">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?>
                                                </td>
                                                <td class="text-end pe-4">
                                                    R$ <?php echo number_format($item['preco'] * $item['quantidade'], 2, ',', '.'); ?>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-outline-danger btn-sm remover-item" data-produto-id="<?php echo $id; ?>">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-receipt me-2"></i>Resumo do Pedido
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span>Subtotal:</span>
                                <span class="fw-bold" id="subtotal">R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Calcular Frete:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="cep" placeholder="Digite seu CEP" maxlength="9">
                                    <button class="btn btn-outline-primary" type="button" onclick="calcularFrete()">
                                        <i class="fas fa-calculator"></i>
                                    </button>
                                </div>
                                <div id="endereco-entrega" class="mt-2 small text-muted" style="display: none;">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <span id="endereco-completo"></span>
                                </div>
                            </div>

                            <div id="info-frete" style="display: none;">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Frete:</span>
                                    <span class="fw-bold" id="valor-frete">R$ 0,00</span>
                                </div>

                                <div id="prazo-entrega" class="small text-muted mb-3">
                                    <i class="fas fa-truck me-1"></i>
                                    <span id="dias-entrega"></span>
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-bold">Total:</span>
                                <span class="fw-bold fs-5" id="valor-total">R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                            </div>

                            <button class="btn btn-success w-100 mb-2" id="finalizar-compra">
                                <i class="fas fa-check me-2"></i>Finalizar Compra
                            </button>
                            
                            <a href="index.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-shopping-bag me-2"></i>Continuar Comprando
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal de Confirmação -->
    <div class="modal fade" id="confirmacaoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Remoção</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja remover este item do carrinho?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmarRemocao">Remover</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Container para alertas dinâmicos -->
    <div class="alert-container"></div>

    <?php require_once 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/carrinho.js"></script>
    <script>
        function finalizarCompra() {
            $.ajax({
                url: 'finalizar_compra.php',
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        window.location.reload();
                    } else {
                        alert(response.message || 'Erro ao finalizar compra');
                    }
                },
                error: function() {
                    alert('Erro ao processar a compra. Tente novamente.');
                }
            });
        }

        $(document).ready(function() {
            $('#finalizar-compra').click(function(e) {
                e.preventDefault();
                finalizarCompra();
            });
        });
    </script>
</body>
</html>

<?php 
// Fechar conexão
$conn = null;
?>
