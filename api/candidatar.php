<?php
require_once 'conexao.php';
require_once 'jwt.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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

$id_vaga = $_POST['id_vaga'] ?? null;

if (!$id_vaga) {
    http_response_code(400);
    echo json_encode(['erro' => 'ID da vaga é obrigatório']);
    exit;
}

if (!isset($_FILES['curriculo']) || $_FILES['curriculo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['erro' => 'Erro ao enviar o arquivo do currículo']);
    exit;
}

$allowed_extensions = ['pdf', 'doc', 'docx', 'txt'];
$file_name = $_FILES['curriculo']['name'];
$file_tmp = $_FILES['curriculo']['tmp_name'];
$file_size = $_FILES['curriculo']['size'];

$ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

if (!in_array($ext, $allowed_extensions)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Formato de arquivo inválido. Use PDF, DOC ou DOCX']);
    exit;
}

// Define a pasta de destino fora da pasta /api
$upload_dir = dirname(__DIR__) . '/uploads';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$new_file_name = uniqid('curriculo_') . '.' . $ext;
$dest_path = $upload_dir . '/' . $new_file_name;

if (!move_uploaded_file($file_tmp, $dest_path)) {
    http_response_code(500);
    echo json_encode(['erro' => 'Falha ao salvar arquivo no servidor']);
    exit;
}

// Verifica se já foi feita candidatura
$verifica = $pdo -> prepare("SELECT * FROM candidaturas WHERE id_candidato = :id_candidato AND id_vaga = :id_vaga");
$verifica -> execute([
    ':id_candidato' => $payload['id'],
    ':id_vaga' => $id_vaga
]);

if ($verifica -> rowCount() > 0) {
    echo json_encode(['erro' => 'Você já se candidatou a esta vaga']);
    exit;
}

// Insere no banco
try {
    $stmt = $pdo -> prepare("INSERT INTO candidaturas (id_candidato, id_vaga, curriculo) VALUES (:id_candidato, :id_vaga, :curriculo)");
    $stmt -> execute([
        ':id_candidato' => $payload['id'],
        ':id_vaga' => $id_vaga,
        ':curriculo' => '/uploads/' . $new_file_name
    ]);

    echo json_encode(['sucesso' => 'Candidatura realizada com sucesso']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao salvar candidatura: ' . $e -> getMessage()]);
}
