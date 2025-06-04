<?php
require_once 'conexao.php';

$data = json_decode(file_get_contents("php://input"), true); 


if (!isset($data['nome'], $data['email'], $data['senha'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Preencha todos os campos obrigatorios.']);
    exit;
}


$nome = trim($data['nome']);
$email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
$senha = $data['senha'];

if (!$email) {
    http_response_code(400);
    echo json_encode(['erro' => 'Email inválido.']);
    exit;
}

// Criptografar a senha
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);


$stmt = $pdo -> prepare("SELECT id FROM candidatos WHERE email = ?");
$stmt -> execute([$email]);
if ($stmt -> rowCount() > 0) {
    http_response_code(409); 
    echo json_encode(['erro' => 'Já existe um candidato com esse email.']);
    exit;
}

try {
    $sql = "INSERT INTO candidatos (nome, email, senha) VALUES (?, ?, ?)";
    $stmt = $pdo -> prepare($sql);
    $stmt -> execute([$nome, $email, $senhaHash]);

    echo json_encode(['mensagem' => 'Candidato cadastrado com sucesso!']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao cadastrar candidato: ' . $e -> getMessage()]);
}
?>
