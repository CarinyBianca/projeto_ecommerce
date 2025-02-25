<?php
$title = 'Cadastro';
require_once 'config.php';
require_once 'backend/classes/Database.php';

// Garantir que a sessão está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';
        $cpf = $_POST['cpf'] ?? '';
        $telefone = $_POST['telefone'] ?? '';
        $cep = $_POST['cep'] ?? '';
        $logradouro = $_POST['logradouro'] ?? '';
        $numero = $_POST['numero'] ?? '';
        $complemento = $_POST['complemento'] ?? '';
        $bairro = $_POST['bairro'] ?? '';
        $cidade = $_POST['cidade'] ?? '';
        $estado = $_POST['estado'] ?? '';

        // Validações básicas
        if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha) || 
            empty($cpf) || empty($telefone) || empty($cep) || empty($logradouro) || 
            empty($numero) || empty($bairro) || empty($cidade) || empty($estado)) {
            throw new Exception('Todos os campos obrigatórios devem ser preenchidos');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('E-mail inválido');
        }

        if ($senha !== $confirmar_senha) {
            throw new Exception('As senhas não coincidem');
        }

        if (strlen($senha) < 8) {
            throw new Exception('A senha deve ter no mínimo 8 caracteres');
        }

        // Limpa formatação do CPF e telefone
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        $cep = preg_replace('/[^0-9]/', '', $cep);

        // Validações específicas
        if (strlen($cpf) !== 11) {
            throw new Exception('CPF inválido');
        }

        if (strlen($telefone) < 10 || strlen($telefone) > 11) {
            throw new Exception('Telefone inválido');
        }

        if (strlen($cep) !== 8) {
            throw new Exception('CEP inválido');
        }

        $db = new Database();
        
        // Verifica se o e-mail já está cadastrado
        $stmt = $db->query("SELECT id FROM clientes WHERE email = ?", [$email]);
        if ($stmt->fetch()) {
            throw new Exception('Este e-mail já está cadastrado');
        }

        // Verifica se o CPF já está cadastrado
        $stmt = $db->query("SELECT id FROM clientes WHERE cpf = ?", [$cpf]);
        if ($stmt->fetch()) {
            throw new Exception('Este CPF já está cadastrado');
        }

        // Hash da senha
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        // Insere o novo cliente
        $db->query(
            "INSERT INTO clientes (nome, email, senha, cpf, telefone, cep, logradouro, numero, 
                                 complemento, bairro, cidade, estado) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $nome, $email, $senha_hash, $cpf, $telefone, $cep, $logradouro, $numero,
                $complemento, $bairro, $cidade, $estado
            ]
        );

        $mensagem = 'Cadastro realizado com sucesso! Faça login para continuar.';
        $tipo_mensagem = 'success';

        // Redireciona após 2 segundos
        header("refresh:2;url=login.php");

    } catch (Exception $e) {
        $mensagem = $e->getMessage();
        $tipo_mensagem = 'danger';
    }
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h2 class="card-title text-center mb-4">Cadastro</h2>

                    <?php if ($mensagem): ?>
                        <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($mensagem) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <!-- Dados Pessoais -->
                            <div class="col-md-6">
                                <label for="nome" class="form-label">Nome Completo *</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">E-mail *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="cpf" class="form-label">CPF *</label>
                                <input type="text" class="form-control" id="cpf" name="cpf" required>
                            </div>
                            <div class="col-md-6">
                                <label for="telefone" class="form-label">Telefone *</label>
                                <input type="text" class="form-control" id="telefone" name="telefone" required>
                            </div>
                            <div class="col-md-6">
                                <label for="senha" class="form-label">Senha *</label>
                                <input type="password" class="form-control" id="senha" name="senha" required 
                                       minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}">
                                <div class="form-text">
                                    Mínimo 8 caracteres, incluindo maiúsculas, minúsculas e números
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="confirmar_senha" class="form-label">Confirmar Senha *</label>
                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
                            </div>

                            <!-- Endereço -->
                            <div class="col-12 mt-4">
                                <h4>Endereço</h4>
                            </div>
                            <div class="col-md-4">
                                <label for="cep" class="form-label">CEP *</label>
                                <input type="text" class="form-control" id="cep" name="cep" required>
                            </div>
                            <div class="col-md-8">
                                <label for="logradouro" class="form-label">Logradouro *</label>
                                <input type="text" class="form-control" id="logradouro" name="logradouro" required>
                            </div>
                            <div class="col-md-4">
                                <label for="numero" class="form-label">Número *</label>
                                <input type="text" class="form-control" id="numero" name="numero" required>
                            </div>
                            <div class="col-md-8">
                                <label for="complemento" class="form-label">Complemento</label>
                                <input type="text" class="form-control" id="complemento" name="complemento">
                            </div>
                            <div class="col-md-4">
                                <label for="bairro" class="form-label">Bairro *</label>
                                <input type="text" class="form-control" id="bairro" name="bairro" required>
                            </div>
                            <div class="col-md-6">
                                <label for="cidade" class="form-label">Cidade *</label>
                                <input type="text" class="form-control" id="cidade" name="cidade" required>
                            </div>
                            <div class="col-md-2">
                                <label for="estado" class="form-label">Estado *</label>
                                <select class="form-select" id="estado" name="estado" required>
                                    <option value="">UF</option>
                                    <option value="AC">AC</option>
                                    <option value="AL">AL</option>
                                    <option value="AP">AP</option>
                                    <option value="AM">AM</option>
                                    <option value="BA">BA</option>
                                    <option value="CE">CE</option>
                                    <option value="DF">DF</option>
                                    <option value="ES">ES</option>
                                    <option value="GO">GO</option>
                                    <option value="MA">MA</option>
                                    <option value="MT">MT</option>
                                    <option value="MS">MS</option>
                                    <option value="MG">MG</option>
                                    <option value="PA">PA</option>
                                    <option value="PB">PB</option>
                                    <option value="PR">PR</option>
                                    <option value="PE">PE</option>
                                    <option value="PI">PI</option>
                                    <option value="RJ">RJ</option>
                                    <option value="RN">RN</option>
                                    <option value="RS">RS</option>
                                    <option value="RO">RO</option>
                                    <option value="RR">RR</option>
                                    <option value="SC">SC</option>
                                    <option value="SP">SP</option>
                                    <option value="SE">SE</option>
                                    <option value="TO">TO</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Cadastrar</button>
                            <a href="login.php" class="btn btn-outline-secondary">Já tem conta? Faça login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para máscaras e validações -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
$(document).ready(function() {
    // Máscaras
    $('#cpf').mask('000.000.000-00');
    $('#telefone').mask('(00) 00000-0000');
    $('#cep').mask('00000-000');

    // Busca CEP
    $('#cep').blur(function() {
        const cep = $(this).val().replace(/\D/g, '');
        if (cep.length === 8) {
            $.getJSON(`https://viacep.com.br/ws/${cep}/json/`, function(data) {
                if (!data.erro) {
                    $('#logradouro').val(data.logradouro);
                    $('#bairro').val(data.bairro);
                    $('#cidade').val(data.localidade);
                    $('#estado').val(data.uf);
                    $('#numero').focus();
                }
            });
        }
    });

    // Validação do formulário
    const form = document.querySelector('.needs-validation');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);
});
</script>

<?php require_once 'includes/footer.php'; ?>
