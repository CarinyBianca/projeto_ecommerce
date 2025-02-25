<?php
require_once 'conexao.php';

try {
    // Criar tabela compras
    $conn->exec("CREATE TABLE IF NOT EXISTS compras (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cliente_id INT NOT NULL,
        valor_total DECIMAL(10,2) NOT NULL,
        data_compra DATETIME NOT NULL,
        FOREIGN KEY (cliente_id) REFERENCES clientes(id)
    )");

    // Criar tabela itens_compra
    $conn->exec("CREATE TABLE IF NOT EXISTS itens_compra (
        id INT AUTO_INCREMENT PRIMARY KEY,
        compra_id INT NOT NULL,
        produto_id INT NOT NULL,
        quantidade INT NOT NULL,
        preco_unitario DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (compra_id) REFERENCES compras(id),
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
    )");

    echo "Tabelas criadas com sucesso!\n";
} catch(PDOException $e) {
    echo "Erro ao criar tabelas: " . $e->getMessage() . "\n";
}
