<?php
require_once 'conexao.php';
require_once 'jwt.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    http_response_code(204); // Sem conteúdo
    exit;
}

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

// Recupera o token da URL (GET)
$token = $_GET['token'] ?? '';

if (!$token) {
    http_response_code(401);
    echo json_encode(['erro' => 'Token não fornecido']);
    exit;
}

// Verifica o token
$payload = verificarJWT($token);
if (!$payload) {
    http_response_code(401);
    echo json_encode(['erro' => 'Token inválido ou expirado']);
    exit;
}

// Garante que é uma empresa
if ($payload['tipo'] !== 'empresa') {
    http_response_code(403);
    echo json_encode(['erro' => 'Apenas empresas podem cadastrar vagas']);
    exit;
}

// Dados do POST (JSON)
$data = json_decode(file_get_contents('php://input'), true);
$titulo = trim($data['titulo'] ?? '');
$descricao = trim($data['descricao'] ?? '');
$requisitos = trim($data['requisitos'] ?? '');
$salario = $data['salario'] ?? null;

// Validação simples
if (!$titulo || !$descricao) {
    http_response_code(400);
    echo json_encode(['erro' => 'Título e descrição são obrigatórios']);
    exit;
}

try {
    $stmt = $pdo -> prepare("INSERT INTO vagas (empresa_id, titulo, descricao, requisitos, salario)
                           VALUES (:empresa_id, :titulo, :descricao, :requisitos, :salario)");
    $stmt -> execute([
        ':empresa_id' => $payload['id'],
        ':titulo' => $titulo,
        ':descricao' => $descricao,
        ':requisitos' => $requisitos,
        ':salario' => $salario
    ]);

    echo json_encode(['sucesso' => 'Vaga cadastrada com sucesso']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao cadastrar vaga: ' . $e -> getMessage()]);
}
