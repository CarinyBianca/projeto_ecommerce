<?php
session_start();
header('Content-Type: application/json');

// Verifica se é uma requisição AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die(json_encode(['success' => false, 'message' => 'Acesso negado']));
}

// Verifica se os dados necessários foram enviados
if (!isset($_POST['cep']) || !isset($_POST['endereco'])) {
    die(json_encode(['success' => false, 'message' => 'Dados incompletos']));
}

// Sanitiza os dados
$cep = filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_STRING);
$endereco = filter_input(INPUT_POST, 'endereco', FILTER_SANITIZE_STRING);
$valor_frete = filter_input(INPUT_POST, 'valor_frete', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$prazo_entrega = filter_input(INPUT_POST, 'prazo_entrega', FILTER_SANITIZE_NUMBER_INT);

// Salva na sessão
$_SESSION['frete'] = [
    'cep' => $cep,
    'endereco' => $endereco,
    'valor' => $valor_frete,
    'prazo' => $prazo_entrega
];

echo json_encode([
    'success' => true,
    'message' => 'Informações de entrega salvas com sucesso'
]);
