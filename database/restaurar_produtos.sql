-- Recria a tabela produtos
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    quantidade_estoque INT NOT NULL DEFAULT 0,
    destaque TINYINT(1) NOT NULL DEFAULT 0,
    imagem VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    peso DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Peso em kg'
);

-- Insere os produtos novamente
INSERT INTO produtos (categoria_id, nome, descricao, preco, quantidade_estoque, destaque, imagem) VALUES
(1, 'Smartphone XYZ', 'Smartphone de última geração com câmera incrível', 1999.99, 50, 1, 'celular.jpg'),
(1, 'Notebook ABC', 'Notebook potente para trabalho e jogos', 3999.99, 30, 1, 'notebook.jpg'),
(1, 'Mouse gamer brabo', 'para jogar', 49.99, 100, 0, 'mousegamer.jpg');
