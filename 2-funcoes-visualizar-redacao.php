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

$id_redacao = $_GET['id'] ?? null;

if (!$id_redacao) {
    die("ID da redação não fornecido.");
}

// Processa a exclusão da correção se a ação for solicitada via POST (Segurança do Modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'apagar_correcao') {
    $id_redacao_post = (int)$_POST['id_redacao'];

    $sql_delete = "DELETE FROM correcoes WHERE id_redacao = ?";
    $stmt_del = $conn->prepare($sql_delete);
    if ($stmt_del) {
        $stmt_del->bind_param("i", $id_redacao_post);
        $stmt_del->execute();
        $stmt_del->close();
    }
    
    // Constrói a URL de redirecionamento mantendo os parâmetros de origem se existirem
    $redirect_url = "2-visualizar-redacao.php?id=" . $id_redacao_post;
    if (isset($_GET['origem'])) {
        $redirect_url .= '&origem=' . urlencode($_GET['origem']);
    }
    if (isset($_GET['id_proposta'])) {
        $redirect_url .= '&id_proposta=' . urlencode($_GET['id_proposta']);
    }
    
    header("Location: " . $redirect_url);
    exit;
}

// Consulta unificada para buscar dados da redação, aluno, proposta e correção
$sql = "SELECT 
            r.id_redacao, r.enviado_em, r.url_foto, r.id_aluno, r.id_proposta,
            a.nome AS nome_aluno, a.serie, a.turma, a.curso,
            p.titulo AS titulo_proposta,
            c.id_correcao, c.nota_total, c.nota_c1, c.nota_c2, c.nota_c3, c.nota_c4, c.nota_c5, c.comentario_pedagogico AS comentario, c.corrigido_em
        FROM redacoes r
        JOIN aluno a ON r.id_aluno = a.id_aluno
        JOIN propostas p ON r.id_proposta = p.id_proposta
        LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao
        WHERE r.id_redacao = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erro na preparação da consulta: " . $conn->error);
}

$stmt->bind_param("i", $id_redacao);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Redação não encontrada no sistema.");
}

$dados = $resultado->fetch_assoc();
$stmt->close();

$tem_correcao = !empty($dados['id_correcao']);
?>