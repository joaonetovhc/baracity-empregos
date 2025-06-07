<?php
require_once 'conexao.php';
require_once 'jwt.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Verifica token
$token = $_GET['token'] ?? null;
if (!$token) {
    http_response_code(401);
    echo json_encode(['erro' => 'Token não fornecido']);
    exit;
}

$payload = verificarJWT($token);
if (!$payload || $payload['tipo'] !== 'candidato') {
    http_response_code(403);
    echo json_encode(['erro' => 'Apenas candidatos podem acessar suas candidaturas']);
    exit;
}

$id_candidato = $payload['id'];

try {
    $stmt = $pdo -> prepare("
        SELECT 
            c.id,
            v.titulo, 
            v.salario, 
            c.data_envio AS data_candidatura, 
            u.nome AS empresa
        FROM candidaturas c
        JOIN vagas v ON c.id_vaga = v.id
        JOIN usuarios u ON v.empresa_id = u.id
        WHERE c.id_candidato = :id_candidato
        ORDER BY c.data_envio DESC
    ");


    $stmt -> execute([':id_candidato' => $id_candidato]);
    $candidaturas = $stmt -> fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($candidaturas);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao buscar candidaturas: ' . $e -> getMessage()]);
}
