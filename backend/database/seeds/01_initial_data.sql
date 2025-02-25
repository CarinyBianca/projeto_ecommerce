-- Inserir categorias iniciais
INSERT INTO categorias (nome, slug) VALUES
('Eletrônicos', 'eletronicos'),
('Roupas', 'roupas'),
('Acessórios', 'acessorios')
ON DUPLICATE KEY UPDATE nome=nome;

-- Inserir produtos iniciais
INSERT INTO produtos (categoria_id, nome, descricao, preco, quantidade_estoque, destaque, imagem) VALUES
(1, 'Smartphone XYZ', 'Smartphone de última geração com câmera incrível', 1999.99, 50, 1, 'smartphone-xyz.jpg'),
(1, 'Notebook ABC', 'Notebook potente para trabalho e jogos', 3999.99, 30, 1, 'notebook-abc.jpg'),
(2, 'Camiseta Casual', 'Camiseta confortável para o dia a dia', 49.99, 100, 0, 'camiseta-casual.jpg'),
(2, 'Calça Jeans', 'Calça jeans moderna e durável', 129.99, 80, 0, 'calca-jeans.jpg'),
(3, 'Relógio Inteligente', 'Smartwatch com monitor cardíaco', 599.99, 40, 1, 'relogio-inteligente.jpg'),
(3, 'Fone de Ouvido', 'Fone bluetooth com cancelamento de ruído', 299.99, 60, 1, 'fone-de-ouvido.jpg')
ON DUPLICATE KEY UPDATE nome=nome;
