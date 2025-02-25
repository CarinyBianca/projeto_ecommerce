ALTER TABLE compras
ADD COLUMN cep_entrega VARCHAR(9) NOT NULL AFTER data_compra,
ADD COLUMN endereco_entrega TEXT NOT NULL AFTER cep_entrega,
ADD COLUMN prazo_entrega INT NOT NULL AFTER endereco_entrega;
