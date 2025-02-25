<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/Database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function verificarSessao() {
        if (!isset($_SESSION['usuario_id'])) {
            return null;
        }
        
        if (time() - $_SESSION['ultimo_acesso'] > 1800) { // 30 minutos
            $this->logout();
            return null;
        }
        
        $_SESSION['ultimo_acesso'] = time();
        return $this->getUsuarioLogado();
    }

    public function login($email, $senha) {
        try {
            $stmt = $this->db->query("SELECT * FROM clientes WHERE email = ? AND ativo = 1", [$email]);
            $usuario = $stmt->fetch();
            
            if (!$usuario) {
                return false;
            }
            
            if (!password_verify($senha, $usuario['senha'])) {
                $this->registrarTentativaFalha($usuario['id']);
                return false;
            }
            
            $this->limparTentativas($usuario['id']);
            $this->registrarLogin($usuario['id']);
            
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];
            $_SESSION['ultimo_acesso'] = time();
            
            return true;
        } catch (PDOException $e) {
            error_log("Erro no login: " . $e->getMessage());
            return false;
        }
    }
    
    private function registrarTentativaFalha($usuarioId) {
        try {
            $this->db->query(
                "UPDATE clientes SET tentativas_login = tentativas_login + 1 WHERE id = ?",
                [$usuarioId]
            );
            
            $tentativas = $this->db->query(
                "SELECT tentativas_login FROM clientes WHERE id = ?",
                [$usuarioId]
            )->fetchColumn();
            
            if ($tentativas >= 3) {
                $bloqueioAte = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                $this->db->query(
                    "UPDATE clientes SET bloqueado_ate = ? WHERE id = ?",
                    [$bloqueioAte, $usuarioId]
                );
            }
        } catch (PDOException $e) {
            error_log("Erro ao registrar tentativa falha: " . $e->getMessage());
        }
    }
    
    private function limparTentativas($usuarioId) {
        try {
            $this->db->query(
                "UPDATE clientes SET tentativas_login = 0, bloqueado_ate = NULL WHERE id = ?",
                [$usuarioId]
            );
        } catch (PDOException $e) {
            error_log("Erro ao limpar tentativas: " . $e->getMessage());
        }
    }
    
    private function registrarLogin($usuarioId) {
        try {
            $this->db->query(
                "UPDATE clientes SET ultimo_login = CURRENT_TIMESTAMP WHERE id = ?",
                [$usuarioId]
            );
        } catch (PDOException $e) {
            error_log("Erro ao registrar login: " . $e->getMessage());
        }
    }
    
    public function logout() {
        session_destroy();
    }
    
    public function isLogado() {
        if (!isset($_SESSION['usuario_id'])) {
            return false;
        }
        
        if (time() - $_SESSION['ultimo_acesso'] > 1800) { // 30 minutos
            $this->logout();
            return false;
        }
        
        $_SESSION['ultimo_acesso'] = time();
        return true;
    }
    
    public function isAdmin() {
        return $this->isLogado() && $_SESSION['usuario_tipo'] === 'admin';
    }
    
    public function getUsuarioLogado() {
        if (!$this->isLogado()) {
            return null;
        }
        
        try {
            $stmt = $this->db->query(
                "SELECT id, nome, email, tipo FROM clientes WHERE id = ?",
                [$_SESSION['usuario_id']]
            );
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erro ao buscar usuário logado: " . $e->getMessage());
            return null;
        }
    }
    
    public function registrar($nome, $email, $senha) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        try {
            $this->db->query(
                "INSERT INTO clientes (nome, email, senha, tipo, ativo) VALUES (?, ?, ?, 'cliente', 1)",
                [$nome, $email, $senhaHash]
            );
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Erro de duplicidade
                return false;
            }
            error_log("Erro ao registrar usuário: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function gerarTokenRecuperacao($email) {
        try {
            $usuario = $this->db->query(
                "SELECT id FROM clientes WHERE email = ?",
                [$email]
            )->fetch();
            
            if (!$usuario) {
                return false;
            }
            
            $token = bin2hex(random_bytes(32));
            $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $this->db->query(
                "UPDATE clientes SET token_recuperacao = ?, token_expiracao = ? WHERE id = ?",
                [$token, $expiracao, $usuario['id']]
            );
            
            return $token;
        } catch (PDOException $e) {
            error_log("Erro ao gerar token de recuperação: " . $e->getMessage());
            return false;
        }
    }
    
    public function validarTokenRecuperacao($token) {
        try {
            $usuario = $this->db->query(
                "SELECT id FROM clientes WHERE token_recuperacao = ? AND token_expiracao > CURRENT_TIMESTAMP",
                [$token]
            )->fetch();
            
            return $usuario ? $usuario['id'] : false;
        } catch (PDOException $e) {
            error_log("Erro ao validar token: " . $e->getMessage());
            return false;
        }
    }
    
    public function redefinirSenha($token, $novaSenha) {
        $usuarioId = $this->validarTokenRecuperacao($token);
        
        if (!$usuarioId) {
            return false;
        }
        
        try {
            $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
            
            $this->db->query(
                "UPDATE clientes SET senha = ?, token_recuperacao = NULL, token_expiracao = NULL WHERE id = ?",
                [$senhaHash, $usuarioId]
            );
            
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao redefinir senha: " . $e->getMessage());
            return false;
        }
    }
}
