<?php
require_once 'conexao.php';
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

try {
    $stmt = $pdo -> query("
        SELECT 
            vagas.id,
            vagas.titulo,
            vagas.descricao,
            vagas.requisitos,
            vagas.salario,
            vagas.data_publicacao,
            usuarios.nome AS nome_empresa
        FROM vagas
        INNER JOIN usuarios ON usuarios.id = vagas.empresa_id
        ORDER BY vagas.data_publicacao DESC
    ");

    $vagas = $stmt -> fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['vagas' => $vagas]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao listar vagas: ' . $e -> getMessage()]);
}
