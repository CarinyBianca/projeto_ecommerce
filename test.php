<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP está funcionando!<br>";
echo "PHP version: " . phpversion() . "<br>";

// Testar conexão com o banco
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=ecommerce;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Conexão com o banco de dados OK!<br>";
} catch (PDOException $e) {
    echo "Erro na conexão com o banco: " . $e->getMessage() . "<br>";
}

// Verificar diretórios
$dirs = [
    'uploads',
    'uploads/produtos',
    'assets/img',
    'logs'
];

foreach ($dirs as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    if (file_exists($fullPath)) {
        echo "Diretório {$dir} existe e " . (is_writable($fullPath) ? "tem" : "não tem") . " permissão de escrita<br>";
    } else {
        echo "Diretório {$dir} não existe<br>";
    }
}

// Verificar includes
$files = [
    'config.php',
    'includes/header.php',
    'includes/footer.php',
    'backend/classes/Database.php'
];

foreach ($files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "Arquivo {$file} existe<br>";
    } else {
        echo "Arquivo {$file} não existe<br>";
    }
}
