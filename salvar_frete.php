<?php
session_start();

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => ''
];

if (!isset($_POST['cep']) || !isset($_POST['valor_frete'])) {
    $response['message'] = 'Dados incompletos';
    echo json_encode($response);
    exit;
}

try {
    $cep = filter_var($_POST['cep'], FILTER_SANITIZE_STRING);
    $valor_frete = filter_var($_POST['valor_frete'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    // Salva na sessão
    $_SESSION['frete'] = [
        'cep' => $cep,
        'valor' => $valor_frete
    ];

    $response['success'] = true;
    $response['message'] = 'Frete salvo com sucesso';
    
} catch (Exception $e) {
    $response['message'] = 'Erro ao salvar frete';
    error_log('Erro ao salvar frete: ' . $e->getMessage());
}

echo json_encode($response);
