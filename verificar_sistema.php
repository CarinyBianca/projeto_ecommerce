<?php
echo "Verificando sistema...\n\n";

// 1. Verificar configurações
if (file_exists('config/config.php')) {
    echo "✓ Arquivo de configuração encontrado\n";
} else {
    echo "✗ Arquivo de configuração não encontrado\n";
}

// 2. Verificar conexão com banco de dados
try {
    require_once 'config/config.php';
    require_once 'database/conexao.php';
    echo "✓ Conexão com banco de dados OK\n";
} catch (Exception $e) {
    echo "✗ Erro na conexão com banco de dados: " . $e->getMessage() . "\n";
}

// 3. Verificar pastas essenciais
$pastas = [
    'src/controllers',
    'src/models',
    'src/views',
    'public/css',
    'public/js',
    'public/images',
    'uploads'
];

foreach ($pastas as $pasta) {
    if (is_dir($pasta)) {
        echo "✓ Pasta {$pasta} encontrada\n";
    } else {
        echo "✗ Pasta {$pasta} não encontrada\n";
    }
}

// 4. Verificar arquivos essenciais
$arquivos = [
    'src/views/carrinho.php',
    'src/views/produtos.php',
    'src/controllers/finalizar_compra.php',
    'public/js/carrinho.js',
    'public/css/style.css'
];

foreach ($arquivos as $arquivo) {
    if (file_exists($arquivo)) {
        echo "✓ Arquivo {$arquivo} encontrado\n";
    } else {
        echo "✗ Arquivo {$arquivo} não encontrado\n";
    }
}

// 5. Verificar permissões
$pastas_permissao = [
    'uploads',
    'public/images'
];

foreach ($pastas_permissao as $pasta) {
    if (is_writable($pasta)) {
        echo "✓ Pasta {$pasta} tem permissão de escrita\n";
    } else {
        echo "✗ Pasta {$pasta} não tem permissão de escrita\n";
    }
}
