<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo');

// Conexão com o banco de dados
$host    = "localhost";
$usuario = "aluno";      
$senha   = "ceep@1234";  
$banco   = "VMI"; 

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// Captura o ID do aluno enviado pela URL
$id_aluno = $_GET['id_aluno'] ?? null;

if (!$id_aluno) {
    die("ID do aluno não fornecido.");
}

// 1. Busca os dados do aluno
$sql_aluno = "SELECT id_aluno, nome, serie, turma, curso FROM aluno WHERE id_aluno = ?";
$stmt_a = $conn->prepare($sql_aluno);
$stmt_a->bind_param("i", $id_aluno);
$stmt_a->execute();
$res_a = $stmt_a->get_result();

if ($res_a->num_rows === 0) {
    die("Aluno não encontrado.");
}
$aluno = $res_a->fetch_assoc();
$stmt_a->close();

// 2. Busca o histórico de redações enviadas por este aluno, ordenadas por data de envio (mais recente primeiro)
$sql_redacoes = "SELECT 
                    r.id_redacao, r.enviado_em, r.url_foto,
                    p.id_proposta, p.titulo AS titulo_proposta,
                    c.id_correcao, c.nota_total, c.corrigido_em
                 FROM redacoes r
                 JOIN propostas p ON r.id_proposta = p.id_proposta
                 LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao
                 WHERE r.id_aluno = ?
                 ORDER BY r.enviado_em DESC";

$stmt_r = $conn->prepare($sql_redacoes);
$stmt_r->bind_param("i", $id_aluno);
$stmt_r->execute();
$resultado_redacoes = $stmt_r->get_result();
$historico = [];

while ($row = $resultado_redacoes->fetch_assoc()) {
    $historico[] = $row;
}
$stmt_r->close();
?>