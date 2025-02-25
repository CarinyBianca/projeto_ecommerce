<?php
// Configurações de conexão
$host = 'localhost';
$dbname = 'ecommerce';
$username = 'root';
$password = '';

try {
    // Criar conexão PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Configurar PDO para lançar exceções em caso de erro
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Habilitar exceções para erros de SQL
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Em caso de erro, exibir mensagem detalhada
    die("Erro de conexão: " . $e->getMessage());
}
?>
