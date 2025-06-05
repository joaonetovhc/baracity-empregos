<?php

$user = 'joaoneto';
$password = 'JoaoNeto@574283*!';

try {
    $pdo = new PDO("mysql:host=185.137.92.120;port=3306;dbname=baracity;charset=utf8mb4", $user, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    
    http_response_code(500);
    echo json_encode(['erro' => 'Erro na conexão com o banco de dados: ' . $e -> getMessage()]);
    exit;
}

?>
