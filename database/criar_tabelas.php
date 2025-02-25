<?php
require_once __DIR__ . '/../config.php';
require_once 'conexao.php';

try {
    // Lê o arquivo SQL
    $sql = file_get_contents(__DIR__ . '/criar_tabelas.sql');
    
    // Executa as queries
    $conn->exec($sql);
    
    echo "Tabelas criadas com sucesso!\n";
} catch(PDOException $e) {
    echo "Erro ao criar tabelas: " . $e->getMessage() . "\n";
}
