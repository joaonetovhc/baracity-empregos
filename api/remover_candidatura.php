<?php
require 'conexao.php';
require 'jwt.php';


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$id = $input['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['erro' => 'ID da candidatura não informado']);
    exit;
}

$token = $_GET['token'] ?? '';
$usuario = verificarJWT($token);

if (!$usuario || $usuario['tipo'] !== 'candidato') {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado']);
    exit;
}

$sql = "SELECT * FROM candidaturas WHERE id = ? AND id_candidato = ?";
$stmt = $pdo -> prepare($sql);
$stmt -> execute([$id, $usuario['id']]);
$candidatura = $stmt -> fetch();

if (!$candidatura) {
    http_response_code(404);
    echo json_encode(['erro' => 'Candidatura não encontrada ou não pertence a você']);
    exit;
}

// Executa o delete

try {
    $sql = "DELETE FROM candidaturas WHERE id = ?";
    $stmt = $pdo -> prepare($sql);
    $stmt -> execute([$id]);

    echo json_encode(['sucesso' => 'Candidatura removida com sucesso']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao remover candidatura: ' . $e -> getMessage()]);
}
