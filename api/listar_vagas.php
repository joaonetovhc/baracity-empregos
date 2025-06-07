<?php
require_once 'conexao.php';
require_once 'jwt.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");


$token = $_GET['token'] ?? '';

if (!$token) {
    http_response_code(401);
    echo json_encode(['erro' => 'Token não fornecido']);
    exit;
}

$payload = verificarJWT($token);

if (!$payload) {
    http_response_code(401);
    echo json_encode(['erro' => 'Token inválido']);
    exit;
}

if (!in_array($payload['tipo'], ['candidato', 'admin'])) {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso não autorizado']);
    exit;
}

try {
    $stmt = $pdo -> query("
        SELECT 
            vagas.id,
            vagas.titulo,
            vagas.descricao,
            vagas.requisitos,
            vagas.salario,
            vagas.data_publicacao,
            usuarios.nome AS nome_empresa
        FROM vagas
        INNER JOIN usuarios ON usuarios.id = vagas.empresa_id
        WHERE vagas.status = 'ativa'
        ORDER BY vagas.data_publicacao DESC
    ");

    $vagas = $stmt -> fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['vagas' => $vagas]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao listar vagas: ' . $e -> getMessage()]);
}
