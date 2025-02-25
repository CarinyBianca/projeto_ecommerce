<<<<<<< HEAD
# projeto_ecommerce
=======
# E-commerce Crow Tech

Sistema de e-commerce desenvolvido em PHP com MySQL.

## Estrutura do Projeto

```
ecommerce/
├── config/              # Arquivos de configuração
│   ├── config.php      # Configurações gerais
│   └── .htaccess       # Configurações do Apache
│
├── database/           # Arquivos relacionados ao banco de dados
│   ├── migrations/     # Scripts SQL de criação/alteração de tabelas
│   └── seeds/         # Scripts de dados iniciais
│
├── public/            # Arquivos públicos
│   ├── css/          # Arquivos CSS
│   ├── js/           # Arquivos JavaScript
│   └── images/       # Imagens do site
│
├── src/              # Código fonte da aplicação
│   ├── controllers/  # Controladores (lógica de negócio)
│   ├── models/       # Modelos (acesso a dados)
│   ├── services/     # Serviços (regras de negócio)
│   └── views/        # Templates e páginas
│       └── includes/ # Componentes reutilizáveis
│
└── index.php        # Ponto de entrada da aplicação
```

## Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Apache com mod_rewrite habilitado
- Extensões PHP: PDO, mysqli, curl

## Instalação

1. Clone o repositório para sua pasta htdocs:
   ```bash
   git clone [url-do-repositorio] ecommerce
   ```

2. Configure o banco de dados:
   - Crie um banco de dados MySQL
   - Importe o arquivo `database/migrations/ecommerce.sql`

3. Configure o arquivo `config/config.php`:
   - Defina as credenciais do banco de dados
   - Ajuste a URL base do site

4. Certifique-se que o Apache tem permissão de escrita nas pastas:
   - public/images/uploads/
   - tmp/

## Funcionalidades

- Cadastro e autenticação de usuários
- Catálogo de produtos
- Carrinho de compras
- Cálculo de frete
- Finalização de compra
- Histórico de pedidos

## Desenvolvimento

Para contribuir com o projeto:

1. Mantenha a estrutura de diretórios
2. Siga os padrões de codificação PSR-4
3. Documente novas funcionalidades
4. Teste todas as alterações antes de commit

## Segurança

- Todas as entradas de usuário são sanitizadas
- Senhas são hasheadas com bcrypt
- Proteção contra CSRF implementada
- Sessões seguras configuradas
>>>>>>> 02542fc (Primeiro commit)
