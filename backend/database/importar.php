<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../classes/Database.php';

try {
    // Criar conexão direta com MySQL para criar o banco
    $pdo = new PDO(
        "mysql:host=" . DB_HOST,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Criar banco de dados
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $pdo->exec("USE " . DB_NAME);

    // Ler e executar o arquivo SQL
    $sql = file_get_contents(__DIR__ . '/schema/01_tables.sql');
    $pdo->exec($sql);

    // Inserir alguns produtos de exemplo
    $db = new Database();
    
    // Criar categoria
    $db->query(
        "INSERT INTO categorias (nome, slug) VALUES (?, ?)",
        ['Eletrônicos', 'eletronicos']
    );
    
    // Inserir produtos
    $produtos = [
        [
            'nome' => 'Smartphone XYZ',
            'descricao' => 'Um smartphone incrível com ótima câmera',
            'preco' => 1999.99,
            'quantidade_estoque' => 10
        ],
        [
            'nome' => 'Notebook ABC',
            'descricao' => 'Notebook potente para todas as suas necessidades',
            'preco' => 3999.99,
            'quantidade_estoque' => 5
        ],
        [
            'nome' => 'Fone de Ouvido Pro',
            'descricao' => 'Fone com cancelamento de ruído',
            'preco' => 299.99,
            'quantidade_estoque' => 20
        ]
    ];

    foreach ($produtos as $produto) {
        $db->query(
            "INSERT INTO produtos (categoria_id, nome, descricao, preco, quantidade_estoque) 
             VALUES (1, ?, ?, ?, ?)",
            [
                $produto['nome'],
                $produto['descricao'],
                $produto['preco'],
                $produto['quantidade_estoque']
            ]
        );
    }

    echo "Banco de dados criado e populado com sucesso!\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    exit(1);
}
