<?php
require_once 'conexao.php';

$input = json_decode(file_get_contents('php://input'), true); 

if (
    !isset($input['nome_empresa']) || empty(trim($input['nome_empresa'])) ||
    !isset($input['email']) || empty(trim($input['email'])) ||
    !isset($input['senha']) || empty(trim($input['senha']))
) {
    http_response_code(400);
    echo json_encode(['erro' => 'Todos os campos são obrigatórios.']);
    exit;
}

$nome_empresa = trim($input['nome_empresa']);
$email = trim($input['email']);
$senha = password_hash(trim($input['senha']), PASSWORD_DEFAULT); // Criptografa a senha

$stmt = $pdo -> prepare("SELECT id FROM empresas WHERE email = ?");
$stmt -> execute([$email]);
if ($stmt -> rowCount() > 0) {
    http_response_code(409); 
    echo json_encode(['erro' => 'Já existe uma empresa com esse email.']);
    exit;
}


try {
    $stmt = $pdo->prepare("INSERT INTO empresas (nome_empresa, email, senha) VALUES (:nome_empresa, :email, :senha)");
    $stmt->bindParam(':nome_empresa', $nome_empresa);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':senha', $senha);

    $stmt->execute();

    echo json_encode(['mensagem' => 'Empresa cadastrada com sucesso!']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao cadastrar empresa: ' . $e -> getMessage()]);
    exit;
}
?>
