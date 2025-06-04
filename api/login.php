<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'conexao.php';
require_once 'jwt.php';   

// Recebe os dados da requisição
$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'] ?? '';
$senha = $data['senha'] ?? '';
$tipo  = $data['tipo'] ?? '';

// Validação simples
if (empty($email) || empty($senha) || empty($tipo)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Todos os campos são obrigatórios.']);
    exit;
}

try {
    // Consulta segura com prepared statement
    $stmt = $pdo -> prepare("SELECT * FROM usuarios WHERE email = :email AND tipo = :tipo LIMIT 1");
    $stmt -> execute([':email' => $email, ':tipo' => $tipo]);
    $usuario = $stmt -> fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Gera o token JWT
        $payload = [
            'id'   => $usuario['id'],
            'tipo' => $usuario['tipo'],
            'email'=> $usuario['email'],
            'exp'  => time() + (60 * 60 * 4) // expira em 4 horas
        ];

        $token = gerarJWT($payload);

        echo json_encode([
            'mensagem' => 'Login bem-sucedido',
            'token' => $token,
            'usuario' => [
                'id' => $usuario['id'],
                'nome' => $usuario['nome'],
                'tipo' => $usuario['tipo']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['erro' => 'Email ou senha inválidos.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor: ' . $e -> getMessage()]);
}
