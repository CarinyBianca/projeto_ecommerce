<?php
require_once 'config.php';
require_once 'database/conexao.php';

// Garantir que a sessão está iniciada
ensure_session();

// Debug da sessão
error_log('Login - Início do script');
error_log('Login - Session ID: ' . session_id());

$title = 'Login';
$mensagem = '';
$tipo_mensagem = '';

// Verificar se há uma URL de redirecionamento na query string
if (isset($_GET['redirect'])) {
    $_SESSION['redirect_after_login'] = $_GET['redirect'];
    error_log('Login - URL de redirecionamento definida via GET: ' . $_GET['redirect']);
}

// Se já estiver logado, redireciona para a página inicial
if (isset($_SESSION['cliente_id'])) {
    // Se houver uma URL para redirecionar após o login, use-a
    if (isset($_SESSION['redirect_after_login'])) {
        $redirect = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
    } else {
        header('Location: index.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        // Validações básicas
        if (empty($email) || empty($senha)) {
            throw new Exception('Por favor, preencha todos os campos');
        }

        // Busca o cliente pelo email
        $stmt = $conn->prepare("SELECT id, nome, email, senha FROM clientes WHERE email = ?");
        $stmt->execute([$email]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cliente) {
            throw new Exception('E-mail ou senha incorretos');
        }

        // Verifica a senha
        if (!password_verify($senha, $cliente['senha'])) {
            throw new Exception('E-mail ou senha incorretos');
        }

        // Preserva os itens do carrinho
        $carrinhoAtual = isset($_SESSION['carrinho']) ? $_SESSION['carrinho'] : array();
        
        // Regenera o ID da sessão por segurança
        session_regenerate_id(true);

        // Restaura os itens do carrinho
        $_SESSION['carrinho'] = $carrinhoAtual;

        // Define as variáveis de sessão do usuário
        $_SESSION['cliente_id'] = $cliente['id'];
        $_SESSION['cliente_nome'] = $cliente['nome'];
        $_SESSION['cliente_email'] = $cliente['email'];

        // Log de sucesso
        error_log('Login - Login bem sucedido para cliente ID: ' . $cliente['id']);
        error_log('Login - Novo Session ID: ' . session_id());

        // Redireciona para a página apropriada
        if (isset($_SESSION['redirect_after_login'])) {
            $redirect = $_SESSION['redirect_after_login'];
            unset($_SESSION['redirect_after_login']);
            error_log('Login - Redirecionando para: ' . $redirect);
            header("Location: " . $redirect);
        } else {
            header("Location: index.php");
        }
        exit;
    } catch (Exception $e) {
        $mensagem = $e->getMessage();
        $tipo_mensagem = 'danger';
        error_log('Erro no login: ' . $e->getMessage());
    }
}

// Inclui o header depois de processar o login
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Login</h2>
                    
                    <?php if ($mensagem): ?>
                        <div class="alert alert-<?php echo $tipo_mensagem; ?>" role="alert">
                            <?php echo $mensagem; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="senha" name="senha" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Entrar</button>
                        </div>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <p>Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
