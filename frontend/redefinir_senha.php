<?php
session_start();
require_once '../backend/config/database.php';

$mensagem = '';
$tipo_mensagem = '';
$titulo = 'Redefinir Senha - Nossa Loja';

if (!isset($_GET['token'])) {
    header('Location: login.php');
    exit;
}

$token = $_GET['token'];

try {
    // Verifica se o token é válido e não expirou
    $stmt = $conn->prepare("SELECT user_id, created_at FROM password_reset_tokens WHERE token = ? AND used = 0");
    $stmt->execute([$token]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        $mensagem = "Token inválido ou expirado.";
        $tipo_mensagem = "danger";
    } else {
        // Verifica se o token não expirou (24 horas de validade)
        $token_time = strtotime($result['created_at']);
        $current_time = time();
        
        if ($current_time - $token_time > 24 * 3600) {
            $mensagem = "Token expirado. Solicite uma nova redefinição de senha.";
            $tipo_mensagem = "danger";
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $senha = $_POST['senha'] ?? '';
                $confirmar_senha = $_POST['confirmar_senha'] ?? '';

                if (empty($senha) || empty($confirmar_senha)) {
                    $mensagem = "Por favor, preencha todos os campos.";
                    $tipo_mensagem = "danger";
                } elseif ($senha !== $confirmar_senha) {
                    $mensagem = "As senhas não coincidem.";
                    $tipo_mensagem = "danger";
                } else {
                    // Atualiza a senha do usuário
                    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                    $stmt->execute([$senha_hash, $result['user_id']]);

                    // Marca o token como usado
                    $stmt = $conn->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
                    $stmt->execute([$token]);

                    $mensagem = "Senha atualizada com sucesso! Você já pode fazer login com sua nova senha.";
                    $tipo_mensagem = "success";
                    
                    // Redireciona após 3 segundos
                    header("refresh:3;url=login.php");
                }
            }
        }
    }
} catch (Exception $e) {
    $mensagem = "Erro ao processar a solicitação. Por favor, tente novamente.";
    $tipo_mensagem = "danger";
}

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Redefinir Senha</h4>
                </div>
                <div class="card-body">
                    <?php if ($mensagem): ?>
                        <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                            <?php echo $mensagem; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($tipo_mensagem !== 'success'): ?>
                        <form method="post">
                            <div class="mb-3">
                                <label for="senha" class="form-label">Nova Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Atualizar Senha</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
