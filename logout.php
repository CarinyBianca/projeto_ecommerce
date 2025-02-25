<?php
require_once 'config.php';

// Debug da sessão antes do logout
error_log('Logout - Session ID antes: ' . session_id());
error_log('Logout - Cliente ID antes: ' . (isset($_SESSION['cliente_id']) ? $_SESSION['cliente_id'] : 'Não logado'));

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Destruir o cookie da sessão
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// Destruir a sessão
session_destroy();

// Iniciar uma nova sessão para garantir que tudo está limpo
session_start();
session_regenerate_id(true);

// Debug da sessão após o logout
error_log('Logout - Session ID após: ' . session_id());
error_log('Logout - Sessão limpa');

// Redirecionar para a página inicial com uma mensagem
$_SESSION['mensagem'] = 'Você saiu com sucesso!';
$_SESSION['tipo_mensagem'] = 'success';

// Redirecionar para a página inicial
header('Location: index.php');
exit;
?>
