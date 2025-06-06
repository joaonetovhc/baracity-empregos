<?php
require_once 'conexao.php';
require_once 'jwt.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

$token = $_GET['token'] ?? null;
$id_vaga = $_GET['id_vaga'] ?? null;

if (!$token || !$id_vaga) {
    http_response_code(400);
    echo json_encode(['erro' => 'Token ou id_vaga não fornecidos']);
    exit;
}

$payload = verificarJWT($token);
if (!$payload || $payload['tipo'] !== 'candidato') {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado']);
    exit;
}

try {
    $stmt = $pdo -> prepare("SELECT COUNT(*) FROM candidaturas WHERE id_candidato = :id_candidato AND id_vaga = :id_vaga");
    $stmt->execute([
        ':id_candidato' => $payload['id'],
        ':id_vaga' => $id_vaga
    ]);
    $count = $stmt -> fetchColumn();

    echo json_encode(['ja_candidatou' => $count > 0]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao verificar candidatura']);
}
?>
