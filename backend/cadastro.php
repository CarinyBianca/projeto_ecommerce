<?php
require_once '../config.php';
require_once 'classes/Auth.php';

$title = 'Cadastro - Nossa Loja';
$mensagem = '';
$tipo_mensagem = '';

$auth = new Auth();

// Se já estiver logado, redireciona
if ($auth->verificarSessao()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verifica CSRF token
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new Exception('Token de segurança inválido');
        }

        // Validação dos campos
        $nome = validate_input($_POST['nome'] ?? '', 'string');
        $email = validate_input($_POST['email'] ?? '', 'email');
        $senha = $_POST['senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';

        if (empty($nome) || empty($email) || empty($senha)) {
            throw new Exception('Por favor, preencha todos os campos');
        }

        if ($senha !== $confirmar_senha) {
            throw new Exception('As senhas não coincidem');
        }

        if (!is_password_strong($senha)) {
            throw new Exception('A senha deve ter pelo menos 8 caracteres, incluindo maiúsculas, minúsculas, números e caracteres especiais');
        }

        if ($auth->registrar($nome, $email, $senha)) {
            $mensagem = 'Cadastro realizado com sucesso! Você será redirecionado para a página de login.';
            $tipo_mensagem = 'success';
            header('Refresh: 3;url=login.php');
        }
    } catch (Exception $e) {
        error_log('Erro no cadastro: ' . $e->getMessage());
        $mensagem = $e->getMessage();
        $tipo_mensagem = 'danger';
    }
}

include 'header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h4 mb-0">Criar Conta</h1>
                </div>
                <div class="card-body">
                    <?php if ($mensagem): ?>
                        <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($mensagem) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="cadastro.php" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome Completo</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="nome" 
                                       name="nome" 
                                       required 
                                       minlength="3"
                                       placeholder="Digite seu nome completo"
                                       value="<?= isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : '' ?>">
                                <div class="invalid-feedback">
                                    Por favor, digite seu nome completo (mínimo 3 caracteres).
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       required 
                                       pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                                       placeholder="Digite seu email"
                                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                                <div class="invalid-feedback">
                                    Por favor, digite um email válido.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="senha" 
                                       name="senha" 
                                       required 
                                       minlength="8"
                                       placeholder="Digite sua senha">
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        onclick="togglePassword('senha')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">
                                    A senha deve ter pelo menos 8 caracteres.
                                </div>
                            </div>
                            <div id="senha-forca" class="mt-2"></div>
                        </div>

                        <div class="mb-4">
                            <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="confirmar_senha" 
                                       name="confirmar_senha" 
                                       required
                                       placeholder="Confirme sua senha">
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        onclick="togglePassword('confirmar_senha')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">
                                    Por favor, confirme sua senha.
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Criar Conta
                            </button>
                            <a href="login.php" class="btn btn-outline-secondary">
                                <i class="fas fa-sign-in-alt me-2"></i>Já tem uma conta? Faça Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validação do formulário
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()

// Mostrar/ocultar senha
function togglePassword(id) {
    const input = document.getElementById(id);
    const icon = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Validação de força da senha em tempo real
document.getElementById('senha').addEventListener('input', function(e) {
    const senha = e.target.value;
    const feedback = document.getElementById('senha-forca');
    
    if (senha.length === 0) {
        feedback.innerHTML = '';
        return;
    }
    
    let forca = 0;
    let mensagem = '';
    
    // Critérios
    if (senha.length >= 8) forca++;
    if (senha.match(/[a-z]/)) forca++;
    if (senha.match(/[A-Z]/)) forca++;
    if (senha.match(/[0-9]/)) forca++;
    if (senha.match(/[^a-zA-Z0-9]/)) forca++;
    
    // Feedback visual
    switch (forca) {
        case 0:
        case 1:
            mensagem = '<div class="progress-bar bg-danger" style="width: 20%">Muito Fraca</div>';
            break;
        case 2:
            mensagem = '<div class="progress-bar bg-warning" style="width: 40%">Fraca</div>';
            break;
        case 3:
            mensagem = '<div class="progress-bar bg-info" style="width: 60%">Média</div>';
            break;
        case 4:
            mensagem = '<div class="progress-bar bg-primary" style="width: 80%">Forte</div>';
            break;
        case 5:
            mensagem = '<div class="progress-bar bg-success" style="width: 100%">Muito Forte</div>';
            break;
    }
    
    feedback.innerHTML = `
        <div class="progress" style="height: 5px">
            ${mensagem}
        </div>
        <small class="text-muted mt-1">
            A senha deve conter pelo menos 8 caracteres, incluindo maiúsculas, minúsculas, números e caracteres especiais
        </small>
    `;
});
</script>

<?php include 'footer.php'; ?>