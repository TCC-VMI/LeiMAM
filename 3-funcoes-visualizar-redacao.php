<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['id_corretor'])) {
    header("Location: 1-login-professor.html");
    exit;
}

// Conexão com o banco de dados
$host    = "localhost";
$usuario = "aluno";      
$senha   = "ceep@1234";  
$banco   = "VMI"; 

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

$id_redacao = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_redacao) {
    die("ID da redação não fornecido ou inválido.");
}

$id_corretor = (int) $_SESSION['id_corretor'];

// Busca dados do corretor logado
$stmt_corr = $conn->prepare("SELECT * FROM corretores WHERE id_corretor = ?");
$stmt_corr->bind_param("i", $id_corretor);
$stmt_corr->execute();
$corretor = $stmt_corr->get_result()->fetch_assoc();
$stmt_corr->close();

if (!$corretor) {
    session_destroy();
    header("Location: 1-login-professor.html");
    exit;
}

// Processa a exclusão da correção se solicitada via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'apagar_correcao') {
    $id_redacao_post = (int)$_POST['id_redacao'];

    $sql_delete = "DELETE FROM correcoes WHERE id_redacao = ?";
    $stmt_del = $conn->prepare($sql_delete);
    if ($stmt_del) {
        $stmt_del->bind_param("i", $id_redacao_post);
        $stmt_del->execute();
        $stmt_del->close();
    }
    
    $redirect_url = "3-visualizar-redacao.php?id=" . $id_redacao_post;
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

// --- VALIDAÇÃO DE SEGURANÇA: PERMISSÃO DO CORRETOR NA TURMA ---
$serie_aluno = (int) $dados['serie'];
$turma_aluno = strtoupper(trim($dados['turma']));
$curso_raw   = strtolower(trim($dados['curso']));

$curso_aluno = 'informatica';
if (strpos($curso_raw, 'rede') !== false) {
    $curso_aluno = 'redes de computadores';
}

$mapa_colunas_turmas = [
    1 => ['A' => ['informatica' => 't_1ainfo', 'redes de computadores' => 't_1arede'], 'B' => ['informatica' => 't_1binfo', 'redes de computadores' => 't_1brede']],
    2 => ['A' => ['informatica' => 't_2ainfo', 'redes de computadores' => 't_2arede'], 'B' => ['informatica' => 't_2binfo', 'redes de computadores' => 't_2brede']],
    3 => ['A' => ['informatica' => 't_3ainfo', 'redes de computadores' => 't_3arede'], 'B' => ['informatica' => 't_3binfo', 'redes de computadores' => 't_3brede']]
];

$coluna_corretor_turma = $mapa_colunas_turmas[$serie_aluno][$turma_aluno][$curso_aluno] ?? '';

if (empty($coluna_corretor_turma) || !isset($corretor[$coluna_corretor_turma]) || (int)$corretor[$coluna_corretor_turma] !== 1) {
    echo "<script>alert('Acesso negado: Você não possui permissão para visualizar redações desta turma.'); window.location.href='3-painel-professor.php';</script>";
    exit;
}

$tem_correcao = !empty($dados['id_correcao']);
?>