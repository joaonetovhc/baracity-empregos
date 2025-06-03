<?php
$host = 'localhost';       // ou IP do servidor
$dbname = 'baracity';      // nome do banco de dados
$user = 'root';            // usuário do MySQL
$pass = '';                // senha (coloque se tiver)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    // Ativa os erros como exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro na conexão com o banco de dados: ' . $e->getMessage()]);
    exit;
}
?>
