-- Limpar tabela produtos
TRUNCATE TABLE produtos;

-- Inserir produtos únicos
INSERT INTO produtos (categoria_id, nome, descricao, preco, quantidade_estoque, destaque, imagem) VALUES
(1, 'Smartphone XYZ', 'Smartphone de última geração com câmera incrível', 1999.99, 50, 1, 'celular.jpg'),
(1, 'Notebook ABC', 'Notebook potente para trabalho e jogos', 3999.99, 30, 1, 'notebook.jpg'),
(1, 'Mouse gamer brabo', 'para jogar', 49.99, 100, 0, 'mousegamer.jpg'),
(2, 'Teclado gamer', 'Teclado confortável e resistente para longas horas de uso', 129.99, 80, 0, 'teclado.jpg'),
(3, 'Relógio Inteligente', 'Smartwatch com monitor cardíaco', 599.99, 40, 1, 'relogiodigital.png'),
(3, 'Fone de Ouvido', 'Fone bluetooth com cancelamento de ruído', 299.99, 60, 1, 'fone.jpg');
