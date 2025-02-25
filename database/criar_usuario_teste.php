<?php
require_once '../config.php';
require_once 'conexao.php';

try {
    // Criar tabela
    $sql = file_get_contents(__DIR__ . '/criar_tabela_clientes.sql');
    $conn->exec($sql);
    echo "Tabela clientes criada ou já existe.\n";
    
    // Criar usuário de teste
    $nome = 'Usuário Teste';
    $email = 'teste@teste.com';
    $senha = password_hash('123456', PASSWORD_DEFAULT);
    
    // Verificar se o usuário já existe
    $stmt = $conn->prepare("SELECT id FROM clientes WHERE email = ?");
    $stmt->execute([$email]);
    
    if (!$stmt->fetch()) {
        // Inserir usuário de teste
        $stmt = $conn->prepare("INSERT INTO clientes (nome, email, senha) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $email, $senha]);
        echo "Usuário de teste criado com sucesso!\n";
        echo "Email: teste@teste.com\n";
        echo "Senha: 123456\n";
    } else {
        echo "Usuário de teste já existe.\n";
        echo "Email: teste@teste.com\n";
        echo "Senha: 123456\n";
    }
    
} catch(PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
