<?php
// Garantir que não há saída antes do início da sessão
ob_start();

// Tempo de vida da sessão (30 dias)
$session_lifetime = 30 * 24 * 60 * 60;

// Configurações de sessão
$session_dir = __DIR__ . '/tmp/sessions';
if (!file_exists($session_dir)) {
    mkdir($session_dir, 0777, true);
}

// Configurar PHP.INI antes de qualquer coisa relacionada à sessão
if (PHP_SESSION_NONE === session_status()) {
    // Configurações de cookie e sessão para maior duração
    ini_set('session.gc_maxlifetime', $session_lifetime);
    ini_set('session.cookie_lifetime', $session_lifetime);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', 0); // Desativado para localhost
    ini_set('session.cookie_path', '/');
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);
    
    // Configurar caminho e nome da sessão
    session_save_path($session_dir);
    session_name('ECOMMSESSID');
    
    // Configurar parâmetros do cookie
    session_set_cookie_params([
        'lifetime' => $session_lifetime,
        'path' => '/',
        'domain' => '',
        'secure' => false, // Desativado para localhost
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    // Iniciar a sessão
    session_start();
    
    // Regenerar ID da sessão periodicamente para segurança
    if (!isset($_SESSION['last_regeneration']) || (time() - $_SESSION['last_regeneration']) > 3600) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecommerce');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configurações gerais
if (!defined('TIMEZONE')) {
    define('TIMEZONE', 'America/Sao_Paulo');
    date_default_timezone_set(TIMEZONE);
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '/ecommerce');
}

if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/uploads/');
    if (!file_exists(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0777, true);
    }
}

// Configurações de erro em desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Função para debug
function debug($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}

// Configurações de Email
define('EMAIL_FROM', 'seu-email@seudominio.com');
define('EMAIL_NAME', 'E-commerce');

// Configurações de erro
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Headers de segurança básicos para ambiente local
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: no-referrer-when-downgrade');

// Função para conectar ao banco de dados
function conectarBD() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Erro de conexão com o banco de dados: " . $e->getMessage());
        die("Desculpe, ocorreu um erro ao conectar ao banco de dados. Por favor, tente novamente mais tarde.");
    }
}

// Funções utilitárias
function sanitize($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitize($value);
        }
    } else {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    return $data;
}

function redirect($path) {
    header("Location: " . BASE_URL . "/" . $path);
    exit;
}

function is_logged_in() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('login.php');
    }
}

function is_admin() {
    return isset($_SESSION['usuario_admin']) && $_SESSION['usuario_admin'] === true;
}

function require_admin() {
    if (!is_logged_in() || !is_admin()) {
        header("HTTP/1.1 403 Forbidden");
        die("Acesso negado");
    }
}

function format_price($price) {
    return number_format($price, 2, ',', '.');
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function validate_input($data, $type = 'string') {
    $data = trim($data);
    $data = stripslashes($data);
    
    switch ($type) {
        case 'email':
            $data = filter_var($data, FILTER_SANITIZE_EMAIL);
            return filter_var($data, FILTER_VALIDATE_EMAIL) ? $data : false;
            
        case 'int':
            return filter_var($data, FILTER_VALIDATE_INT);
            
        case 'float':
            return filter_var($data, FILTER_VALIDATE_FLOAT);
            
        case 'url':
            return filter_var($data, FILTER_VALIDATE_URL);
            
        default:
            return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}

function generate_password_hash($password) {
    return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
}

function is_password_strong($password) {
    if (strlen($password) < 8) return false;
    if (!preg_match("/[A-Z]/", $password)) return false;
    if (!preg_match("/[a-z]/", $password)) return false;
    if (!preg_match("/[0-9]/", $password)) return false;
    if (!preg_match("/[^A-Za-z0-9]/", $password)) return false;
    return true;
}

function generate_unique_token() {
    return bin2hex(random_bytes(32));
}

function validate_token($token, $max_age = 3600) {
    if (empty($token)) return false;
    
    try {
        $db = new Database();
        $stmt = $db->query(
            "SELECT created_at FROM tokens WHERE token = ? AND used = 0 AND created_at > NOW() - INTERVAL ? SECOND",
            [$token, $max_age]
        );
        return $stmt->fetch() !== false;
    } catch (Exception $e) {
        error_log("Erro ao validar token: " . $e->getMessage());
        return false;
    }
}

function invalidate_token($token) {
    try {
        $db = new Database();
        $db->query(
            "UPDATE tokens SET used = 1, used_at = NOW() WHERE token = ?",
            [$token]
        );
        return true;
    } catch (Exception $e) {
        error_log("Erro ao invalidar token: " . $e->getMessage());
        return false;
    }
}

function cleanup_old_sessions() {
    $session_files = glob(session_save_path() . '/sess_*');
    $now = time();
    
    foreach ($session_files as $file) {
        if (is_file($file) && ($now - filemtime($file) > 3600)) {
            @unlink($file);
        }
    }
}

function ensure_session() {
    if (PHP_SESSION_NONE === session_status()) {
        session_start();
    }
    
    // Verifica se o usuário está logado mas perdeu dados da sessão
    if (isset($_COOKIE['ECOMMSESSID']) && !isset($_SESSION['cliente_id'])) {
        // Regenera a sessão para segurança
        session_regenerate_id(true);
        // Redireciona para o login se necessário
        if (!strpos($_SERVER['PHP_SELF'], 'login.php')) {
            header('Location: /ecommerce/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }
    
    return session_id();
}

if (mt_rand(1, 100) === 1) {
    session_write_close();
    cleanup_old_sessions();
    session_start();
}
