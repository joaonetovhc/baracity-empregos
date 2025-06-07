<?php
require_once 'conexao.php';
require_once 'jwt.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$token = $_GET['token'] ?? null;
if (!$token) {
    http_response_code(401);
    echo json_encode(['erro' => 'Token não fornecido']);
    exit;
}

$payload = verificarJWT($token);
if (!$payload || $payload['tipo'] !== 'empresa') {
    http_response_code(403);
    echo json_encode(['erro' => 'Apenas empresas podem excluir vagas']);
    exit;
}

$id_empresa = $payload['id'];
$id_vaga = $_GET['id'] ?? null;

if (!$id_vaga) {
    http_response_code(400);
    echo json_encode(['erro' => 'ID da vaga não fornecido']);
    exit;
}

// Verifica se a vaga pertence à empresa
$verifica = $pdo -> prepare("SELECT COUNT(*) FROM vagas WHERE id = :id AND empresa_id = :empresa_id");
$verifica -> execute([':id' => $id_vaga, ':empresa_id' => $id_empresa]);

if ($verifica -> fetchColumn() == 0) {
    http_response_code(403);
    echo json_encode(['erro' => 'Esta vaga não pertence à empresa logada']);
    exit;
}

// Faz o soft delete (altera o status)
$update = $pdo -> prepare("UPDATE vagas SET status = 'inativa' WHERE id = :id");
$update -> execute([':id' => $id_vaga]);

echo json_encode(['sucesso' => "Vaga excluida com sucesso!"]);
