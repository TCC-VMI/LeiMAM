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

// Captura o ID do aluno enviado pela URL
$id_aluno = filter_input(INPUT_GET, 'id_aluno', FILTER_VALIDATE_INT);

if (!$id_aluno) {
    die("ID do aluno não fornecido ou inválido.");
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

// --- VALIDAÇÃO DE SEGURANÇA: PERMISSÃO DO CORRETOR NA TURMA DO ALUNO ---
$serie_aluno = (int) $aluno['serie'];
$turma_aluno = strtoupper(trim($aluno['turma']));
$curso_raw   = strtolower(trim($aluno['curso']));

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
    echo "<script>alert('Acesso negado: Você não possui permissão para visualizar o histórico de alunos desta turma.'); window.location.href='3-painel-professor.php';</script>";
    exit;
}

// 2. Busca o histórico de redações enviadas por este aluno
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