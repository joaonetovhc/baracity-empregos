<?php
require_once 'conexao.php';
require_once 'jwt.php';

header('Content-Type: application/json');

// Pega token pela URL
$token = $_GET['token'] ?? null;
if (!$token) {
    http_response_code(401);
    echo json_encode(['erro' => 'Token não fornecido']);
    exit;
}

$payload = verificarJWT($token);
if (!$payload || $payload['tipo'] !== 'candidato') {
    http_response_code(403);
    echo json_encode(['erro' => 'Apenas candidatos podem se candidatar']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id_vaga = $data['id_vaga'] ?? null;
$curriculo = trim($data['curriculo'] ?? '');

if (!$id_vaga) {
    http_response_code(400);
    echo json_encode(['erro' => 'ID da vaga é obrigatório']);
    exit;
}

// Insere candidatura
try {
    $stmt = $pdo -> prepare("INSERT INTO candidaturas (id_candidato, id_vaga, curriculo) VALUES (:id_candidato, :id_vaga, :curriculo)");
    $stmt -> execute([
        ':id_candidato' => $payload['id'],
        ':id_vaga' => $id_vaga,
        ':curriculo' => $curriculo
    ]);

    echo json_encode(['sucesso' => 'Candidatura realizada com sucesso']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao salvar candidatura: ' . $e -> getMessage()]);
}
