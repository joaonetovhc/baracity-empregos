<?php
require_once 'conexao.php';
require_once 'jwt.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Pega token
$token = $_GET['token'] ?? null;
if (!$token) {
    http_response_code(401);
    echo json_encode(['erro' => 'Token não fornecido']);
    exit;
}

// Verifica e decodifica token
$payload = verificarJWT($token);
if (!$payload || $payload['tipo'] !== 'empresa') {
    http_response_code(403);
    echo json_encode(['erro' => 'Apenas empresas podem acessar essa informação']);
    exit;
}

$id_empresa = $payload['id'];

try {
    $stmt = $pdo -> prepare("
        SELECT 
            c.id AS id_candidatura,
            v.id AS id_vaga,
            v.titulo AS vaga,
            u.nome AS candidato,
            u.email,
            c.data_envio AS data_candidatura
        FROM candidaturas c
        JOIN vagas v ON c.id_vaga = v.id
        JOIN usuarios u ON c.id_candidato = u.id
        WHERE v.empresa_id = :id_empresa AND v.status = 'ativa'
        ORDER BY c.data_envio DESC
    ");

    $stmt -> execute([':id_empresa' => $id_empresa]);
    $candidatos = $stmt -> fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($candidatos);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao buscar candidatos: ' . $e -> getMessage()]);
}
