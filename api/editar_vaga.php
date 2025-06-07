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

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

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

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id'], $input['titulo'], $input['descricao'], $input['salario'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados incompletos']);
    exit;
}

$id = intval($input['id']);
$titulo = trim($input['titulo']);
$descricao = trim($input['descricao']);
$requisitos = trim($input['requisitos'] ?? '');
$salario = floatval($input['salario']);

// Atualiza apenas vagas da empresa logada
try {
    $stmt = $pdo -> prepare("UPDATE vagas SET titulo = :titulo, descricao = :descricao, requisitos = :requisitos, salario = :salario WHERE id = :id AND empresa_id = :empresa_id");
    $stmt -> execute([
        ':titulo' => $titulo,
        ':descricao' => $descricao,
        ':requisitos' => $requisitos,
        ':salario' => $salario,
        ':id' => $id,
        ':empresa_id' => $empresa_id
    ]);

    echo json_encode(['sucesso' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao atualizar vaga: ' . $e -> getMessage()]);
}
?>
