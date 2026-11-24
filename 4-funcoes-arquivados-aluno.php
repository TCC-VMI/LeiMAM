<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configura fuso horário padrão para Brasília
date_default_timezone_set('America/Sao_Paulo');

// Conexão com o Banco de Dados
$host    = "localhost";
$usuario = "aluno";      
$senha   = "ceep@1234";  
$banco   = "VMI"; 

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Recupera o ID do aluno autenticado atualmente
$id_aluno_atual = isset($_SESSION['id_aluno']) ? $_SESSION['id_aluno'] : 1; 

// Busca os dados do aluno atual para obter a matrícula e nome (para rastrear o histórico em anos anteriores)
$stmt_atual = $conn->prepare("SELECT * FROM aluno WHERE id_aluno = ?");
$stmt_atual->bind_param("i", $id_aluno_atual);
$stmt_atual->execute();
$aluno_atual_info = $stmt_atual->get_result()->fetch_assoc();
$stmt_atual->close();

if (!$aluno_atual_info) {
    die("Estudante não encontrado.");
}

$matricula_aluno = $aluno_atual_info['matricula'];
$nome_aluno = $aluno_atual_info['nome'];
$ano_vigente = (int)date('Y');

// Busca no histórico da tabela aluno todos os registros que pertencem a este aluno em anos ANTERIORES ao atual
$stmt_hist = $conn->prepare("SELECT * FROM aluno WHERE (matricula = ? OR nome = ?) AND ano < ? ORDER BY ano DESC");
$stmt_hist->bind_param("ssi", $matricula_aluno, $nome_aluno, $ano_vigente);
$stmt_hist->execute();
$res_hist = $stmt_hist->get_result();

$turmas_arquivadas_historico = [];
while ($row = $res_hist->fetch_assoc()) {
    $id_hist = $row['id_aluno'];
    $ano_hist = $row['ano'];
    
    // Conta quantas redações ele enviou nesse registro
    $stmt_red = $conn->prepare("SELECT COUNT(*) AS total FROM redacoes WHERE id_aluno = ?");
    $stmt_red->bind_param("i", $id_hist);
    $stmt_red->execute();
    $total_redacoes = $stmt_red->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt_red->close();

    // Calcula a média anual considerando APENAS as redações que foram enviadas E corrigidas (possuem correção)
    $stmt_not = $conn->prepare("SELECT AVG(c.nota_total) AS media FROM redacoes r JOIN correcoes c ON r.id_redacao = c.id_redacao WHERE r.id_aluno = ?");
    $stmt_not->bind_param("i", $id_hist);
    $stmt_not->execute();
    $media_anual = $stmt_not->get_result()->fetch_assoc()['media'];
    $stmt_not->close();

    $turmas_arquivadas_historico[] = [
        'id_aluno' => $id_hist,
        'ano' => $ano_hist,
        'serie' => $row['serie'],
        'turma' => $row['turma'],
        'curso' => $row['curso'],
        'nome_turma' => $row['serie'] . '° ' . $row['turma'] . ' ' . strtoupper($row['curso']),
        'total_redacoes' => $total_redacoes,
        'media_anual' => $media_anual ? round($media_anual) : 0
    ];
}
$stmt_hist->close();
?>