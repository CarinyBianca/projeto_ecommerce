<?php
require_once __DIR__ . '/../config/config.php';

class Email {
    public function enviarEmailRecuperacao($email, $nome, $token) {
        $link = SITE_URL . "/redefinir_senha.php?token=" . $token;
        
        $para = $email;
        $assunto = 'Recuperação de Senha';
        
        $mensagem = "
            <html>
            <head>
                <title>Recuperação de Senha</title>
            </head>
            <body>
                <h1>Recuperação de Senha</h1>
                <p>Olá {$nome},</p>
                <p>Recebemos uma solicitação para redefinir sua senha.</p>
                <p>Se você não fez esta solicitação, ignore este email.</p>
                <p>Para redefinir sua senha, clique no link abaixo:</p>
                <p><a href='{$link}'>Redefinir Senha</a></p>
                <p>Este link expira em 1 hora.</p>
                <p>Atenciosamente,<br>Equipe E-commerce</p>
            </body>
            </html>
        ";
        
        $headers = array(
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . EMAIL_FROM,
            'Reply-To: ' . EMAIL_FROM,
            'X-Mailer: PHP/' . phpversion()
        );
        
        try {
            return mail($para, $assunto, $mensagem, implode("\r\n", $headers));
        } catch (Exception $e) {
            error_log("Erro ao enviar email: " . $e->getMessage());
            return false;
        }
    }
}
