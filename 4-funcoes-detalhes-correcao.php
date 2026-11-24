<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    die("Falha na conexão com o banco de dados.");
}

// Recupera o ID do aluno autenticado
$id_aluno_atual = isset($_SESSION['id_aluno']) ? $_SESSION['id_aluno'] : 1; 

// Valida se o ID da redação foi passado via GET
$id_redacao = isset($_GET['id_redacao']) ? intval($_GET['id_redacao']) : 0;
if ($id_redacao <= 0) {
    header("Location: 4-propostas-aluno.php");
    exit;
}

// Busca a redação, a proposta vinculada e a correção corrigindo o nome da coluna para `comentario_pedagogico`
$stmt = $conn->prepare("
    SELECT r.*, p.id_proposta, p.titulo AS titulo_proposta, p.instrucoes,
           c.id_correcao, c.nota_total, c.nota_c1, c.nota_c2, c.nota_c3, c.nota_c4, c.nota_c5, 
           c.comentario_pedagogico AS comentario, c.corrigido_em
    FROM redacoes r
    JOIN propostas p ON r.id_proposta = p.id_proposta
    LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao
    WHERE r.id_redacao = ? AND r.id_aluno = ?
");
$stmt->bind_param("ii", $id_redacao, $id_aluno_atual);
$stmt->execute();
$dados = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$dados) {
    die("Redação não encontrada ou acesso negado.");
}

$tem_correcao = !empty($dados['id_correcao']);
?>