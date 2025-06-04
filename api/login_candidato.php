<?php
require_once 'conexao.php';

$input = json_decode(file_get_contents('php://input'), true); 

if (!isset($input['email'], $input['senha'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Email e senha são obrigatórios.']);
    exit;
}

$email = trim($input['email']);
$senha = trim($input['senha']);

try {
    $stmt = $pdo->prepare("SELECT * FROM candidatos WHERE email = :email");
    $stmt -> bindParam(':email', $email);
    $stmt -> execute();

    $usuario = $stmt -> fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        echo json_encode([
            'mensagem' => 'Login realizado com sucesso!',
            'candidato' => [
                'id' => $usuario['id'],
                'nome' => $usuario['nome'],
                'email' => $usuario['email']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['erro' => 'Email ou senha inválidos.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor: ' . $e -> getMessage()]);
    exit;
}
?>
