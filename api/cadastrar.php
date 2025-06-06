<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

require_once 'conexao.php'; 

$data = json_decode(file_get_contents("php://input"), true);

$nome  = trim($data['nome'] ?? '');
$email = trim($data['email'] ?? '');
$senha = $data['senha'] ?? '';
$tipo  = $data['tipo'] ?? '';

if (empty($nome) || empty($email) || empty($senha) || empty($tipo)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Todos os campos são obrigatórios']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Email inválido']);
    exit;
}

if (!in_array($tipo, ['candidato', 'empresa', 'admin'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Tipo de usuário inválido']);
    exit;
}

// Verifica se email já existe
try {
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['erro' => 'Email já cadastrado']);
        exit;
    }

    // Hash da senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // Insere usuário
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (:nome, :email, :senha, :tipo)");
    $stmt -> execute([
        ':nome' => $nome,
        ':email' => $email,
        ':senha' => $senhaHash,
        ':tipo' => $tipo
    ]);

    echo json_encode(['mensagem' => 'Usuário cadastrado com sucesso']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor: ' . $e -> getMessage()]);
}
