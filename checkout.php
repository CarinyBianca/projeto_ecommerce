<?php
require_once 'config.php';
require_once 'backend/classes/Database.php';

// Inicia a sessão se não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['cliente_id'])) {
    $_SESSION['mensagem'] = "Por favor, faça login para continuar.";
    $_SESSION['tipo_mensagem'] = "warning";
    header('Location: login.php');
    exit;
}

// Verifica se há itens no carrinho
if (empty($_SESSION['carrinho'])) {
    $_SESSION['mensagem'] = "Seu carrinho está vazio.";
    $_SESSION['tipo_mensagem'] = "warning";
    header('Location: carrinho.php');
    exit;
}

// Inicializa a conexão com o banco de dados
$db = new Database();

// Calcula o subtotal do carrinho
$subtotal = 0;
$itens_carrinho = [];
foreach ($_SESSION['carrinho'] as $produto_id => $quantidade) {
    $stmt = $db->query("SELECT id, nome, preco FROM produtos WHERE id = ?", [$produto_id]);
    $produto = $stmt->fetch();
    $subtotal += $produto['preco'] * $quantidade;
    
    $itens_carrinho[] = [
        'produto_id' => $produto['id'],
        'nome' => $produto['nome'],
        'quantidade' => $quantidade,
        'preco' => $produto['preco']
    ];
}

// Processa a finalização da compra
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['finalizar_compra'])) {
    // Validação dos dados do formulário
    $cep = $_POST['cep'] ?? '';
    $endereco = $_POST['endereco'] ?? '';
    $numero = $_POST['numero'] ?? '';
    $complemento = $_POST['complemento'] ?? '';
    $cidade = $_POST['cidade'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $frete = $_POST['valor_frete'] ?? 0;
    $total = $subtotal + $frete;

    try {
        // Inicia uma transação
        $db->beginTransaction();

        // Insere a compra
        $stmt = $db->prepare("INSERT INTO compras (cliente_id, data_compra, subtotal, frete, total, status) VALUES (?, NOW(), ?, ?, ?, 'pendente')");
        $stmt->execute([
            $_SESSION['cliente_id'], 
            $subtotal, 
            $frete, 
            $total
        ]);
        $compra_id = $db->lastInsertId();

        // Insere os itens da compra
        $stmt_itens = $db->prepare("INSERT INTO itens_compra (compra_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        foreach ($itens_carrinho as $item) {
            $stmt_itens->execute([
                $compra_id, 
                $item['produto_id'], 
                $item['quantidade'], 
                $item['preco']
            ]);
        }

        // Insere o endereço de entrega
        $stmt_endereco = $db->prepare("INSERT INTO enderecos_entrega (compra_id, cep, endereco, numero, complemento, cidade, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_endereco->execute([
            $compra_id,
            $cep,
            $endereco,
            $numero,
            $complemento,
            $cidade,
            $estado
        ]);

        // Commita a transação
        $db->commit();

        // Limpa o carrinho
        unset($_SESSION['carrinho']);

        // Redireciona para página de confirmação
        $_SESSION['mensagem'] = "Compra finalizada com sucesso! Número do pedido: $compra_id";
        $_SESSION['tipo_mensagem'] = "success";
        header('Location: confirmacao.php');
        exit;

    } catch (Exception $e) {
        // Rollback em caso de erro
        $db->rollBack();
        $_SESSION['mensagem'] = "Erro ao finalizar compra: " . $e->getMessage();
        $_SESSION['tipo_mensagem'] = "danger";
    }
}

// Define o título da página
$title = "Finalizar Compra";

// Include do header
include 'includes/header.php';
?>

<div class="container my-5">
    <h2>Finalizar Compra</h2>
    
    <div class="row">
        <div class="col-md-8">
            <form id="checkout-form" method="POST" action="">
                <!-- Resumo do Carrinho -->
                <div class="card mb-4">
                    <div class="card-header">Resumo do Carrinho</div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Preço</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($itens_carrinho as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['nome']) ?></td>
                                    <td><?= $item['quantidade'] ?></td>
                                    <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                                    <td>R$ <?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Endereço de Entrega -->
                <div class="card mb-4">
                    <div class="card-header">Endereço de Entrega</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="cep" class="form-label">CEP</label>
                                <input type="text" class="form-control" id="cep" name="cep" required>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label for="endereco" class="form-label">Endereço</label>
                                <input type="text" class="form-control" id="endereco" name="endereco" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="numero" class="form-label">Número</label>
                                <input type="text" class="form-control" id="numero" name="numero" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="complemento" class="form-label">Complemento</label>
                                <input type="text" class="form-control" id="complemento" name="complemento">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="cidade" class="form-label">Cidade</label>
                                <input type="text" class="form-control" id="cidade" name="cidade" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <input type="text" class="form-control" id="estado" name="estado" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumo de Pagamento -->
                <div class="card mb-4">
                    <div class="card-header">Resumo de Pagamento</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p>Subtotal: R$ <?= number_format($subtotal, 2, ',', '.') ?></p>
                                <p>Frete: R$ <span id="valor-frete">0,00</span></p>
                                <input type="hidden" name="valor_frete" id="input-frete" value="0">
                                <h4>Total: R$ <span id="valor-total"><?= number_format($subtotal, 2, ',', '.') ?></span></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botão de Finalização -->
                <button type="submit" name="finalizar_compra" class="btn btn-primary btn-lg w-100">Finalizar Compra</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cepInput = document.getElementById('cep');
    const enderecoInput = document.getElementById('endereco');
    const cidadeInput = document.getElementById('cidade');
    const estadoInput = document.getElementById('estado');
    const valorFreteSpan = document.getElementById('valor-frete');
    const inputFrete = document.getElementById('input-frete');
    const valorTotalSpan = document.getElementById('valor-total');

    // Função para consultar CEP via ViaCEP
    function consultarCEP() {
        const cep = cepInput.value.replace(/\D/g, '');
        
        if (cep.length !== 8) {
            alert('CEP inválido');
            return;
        }

        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                if (!data.erro) {
                    enderecoInput.value = data.logradouro;
                    cidadeInput.value = data.localidade;
                    estadoInput.value = data.uf;

                    // Cálculo de frete simplificado baseado no estado
                    const fretePorEstado = {
                        'SP': 15.00,
                        'RJ': 20.00,
                        'MG': 18.00,
                        'ES': 17.00
                    };

                    const frete = fretePorEstado[data.uf] || 25.00;
                    
                    valorFreteSpan.textContent = frete.toFixed(2).replace('.', ',');
                    inputFrete.value = frete;

                    // Atualiza valor total
                    const subtotal = <?= $subtotal ?>;
                    const total = subtotal + frete;
                    valorTotalSpan.textContent = total.toFixed(2).replace('.', ',');
                } else {
                    alert('CEP não encontrado');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao consultar CEP');
            });
    }

    // Adiciona evento de consulta ao perder o foco do CEP
    cepInput.addEventListener('blur', consultarCEP);
});
</script>

<?php include 'includes/footer.php'; ?>