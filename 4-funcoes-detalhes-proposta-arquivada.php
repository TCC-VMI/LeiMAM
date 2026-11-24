<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Sao_Paulo');

$host    = "localhost";
$usuario = "aluno";      
$senha   = "ceep@1234";  
$banco   = "VMI"; 

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Captura o ID da proposta e o ID do aluno via GET
$id_proposta = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_aluno    = isset($_GET['aluno']) ? (int)$_GET['aluno'] : (isset($_SESSION['id_aluno']) ? $_SESSION['id_aluno'] : 0);

if ($id_proposta <= 0 || $id_aluno <= 0) {
    die("Parâmetros inválidos para visualização.");
}

// Busca os dados da proposta
$stmt_p = $conn->prepare("SELECT * FROM propostas WHERE id_proposta = ?");
$stmt_p->bind_param("i", $id_proposta);
$stmt_p->execute();
$proposta = $stmt_p->get_result()->fetch_assoc();
$stmt_p->close();

if (!$proposta) {
    die("Proposta não encontrada.");
}

// Busca a redação enviada pelo aluno para esta proposta
$stmt_r = $conn->prepare("SELECT * FROM redacoes WHERE id_proposta = ? AND id_aluno = ?");
$stmt_r->bind_param("ii", $id_proposta, $id_aluno);
$stmt_r->execute();
$redacao = $stmt_r->get_result()->fetch_assoc();
$stmt_r->close();

$correcao = null;
if ($redacao) {
    $id_redacao = $redacao['id_redacao'];
    // Busca a correção associada à redação
    $stmt_c = $conn->prepare("SELECT * FROM correcoes WHERE id_redacao = ?");
    $stmt_c->bind_param("i", $id_redacao);
    $stmt_c->execute();
    $correcao = $stmt_c->get_result()->fetch_assoc();
    $stmt_c->close();
}
?>