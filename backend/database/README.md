# Estrutura do Banco de Dados

Este diretório contém todos os arquivos relacionados ao banco de dados do e-commerce.

## Estrutura de Diretórios

```
database/
├── schema/           # Arquivos de estrutura do banco de dados
│   └── 01_tables.sql # Criação das tabelas
├── seeds/            # Arquivos de dados iniciais
│   └── 01_initial_data.sql  # Dados iniciais (categorias e produtos)
└── README.md         # Este arquivo
```

## Como Usar

1. Execute primeiro os arquivos da pasta `schema` para criar a estrutura do banco de dados
2. Em seguida, execute os arquivos da pasta `seeds` para inserir os dados iniciais

### Ordem de Execução

1. `schema/01_tables.sql`
2. `seeds/01_initial_data.sql`

## Tabelas do Sistema

### CLIENTES
- Armazena informações dos clientes
- Inclui dados de cadastro e endereço
- Integração com API de CEP para preenchimento automático

### PRODUTOS
- Cadastro completo de produtos
- Informações de estoque
- Dados para cálculo de frete (peso e dimensões)

### COMPRAS
- Registro das compras realizadas
- Cálculo de frete
- Endereço de entrega
- Status do pedido

### Tabelas de Suporte
- `categorias`: Categorização dos produtos
- `compra_itens`: Itens de cada compra

## Funcionalidades Principais

1. Cadastro de Clientes
   - Validação de CPF e Email
   - Busca automática de endereço por CEP
   - Senhas criptografadas

2. Gestão de Produtos
   - Controle de estoque
   - Categorização
   - Upload de imagens

3. Processo de Compra
   - Carrinho de compras
   - Cálculo de frete via CEP
   - Múltiplos status de pedido
