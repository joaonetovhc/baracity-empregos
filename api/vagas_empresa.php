<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

require_once 'conexao.php';
require_once 'jwt.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Verifica o token passado na URL
if (!isset($_GET['token'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Token não fornecido']);
    exit;
}

$token = $_GET['token'];
$dados = verificarJWT($token);

if (!$dados) {
    http_response_code(401);
    echo json_encode(['erro' => 'Token inválido']);
    exit;
}

if ($dados['tipo'] !== 'empresa') {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado']);
    exit;
}

$empresa_id = $dados['id'];

try {
    $stmt = $pdo -> prepare("SELECT * FROM vagas WHERE empresa_id = :empresa_id ORDER BY data_publicacao DESC");
    $stmt -> execute([':empresa_id' => $empresa_id]);
    $vagas = $stmt -> fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['vagas' => $vagas]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao buscar vagas: ' . $e -> getMessage()]);
}
