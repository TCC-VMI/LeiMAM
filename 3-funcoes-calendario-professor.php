<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configura fuso horário padrão para Brasília
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['id_corretor'])) {
    header("Location: 1-login-professor.html");
    exit;
}

// Conexão com o Banco de Dados
$host    = "localhost";
$usuario = "aluno";      
$senha   = "ceep@1234";  
$banco   = "VMI";      

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$id_corretor = (int) $_SESSION['id_corretor'];

// Busca dados do corretor logado para verificar permissões de turmas
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

/**
 * Constrói a cláusula SQL para filtrar propostas apenas nas turmas permitidas ao professor
 */
function obterFiltroTurmasPropostasProfessor($corretor) {
    $colunas_turmas = [
        't_1ainfo', 't_1binfo', 't_1arede', 't_1brede',
        't_2ainfo', 't_2binfo', 't_2arede', 't_2brede',
        't_3ainfo', 't_3binfo', 't_3arede', 't_3brede'
    ];

    $turmas_permitidas = [];
    foreach ($colunas_turmas as $col) {
        if (isset($corretor[$col]) && (int)$corretor[$col] === 1) {
            $turmas_permitidas[] = "p.$col = 1";
        }
    }

    if (empty($turmas_permitidas)) {
        return "1 = 0"; // Nenhuma turma alocada
    }

    return "(" . implode(" OR ", $turmas_permitidas) . ")";
}

// --- BUSCA EVENTOS DO MÊS (EVENTOS MANUAIS + PROPOSTAS DAS TURMAS DO CORRETOR) ---
function obterEventosMesProfessor($conn, $corretor, $ano, $mes, $total_dias) {
    $data_inicio = sprintf("%04d-%02d-01", $ano, $mes);
    $data_fim    = sprintf("%04d-%02d-%02d", $ano, $mes, $total_dias);
    $filtro_turmas = obterFiltroTurmasPropostasProfessor($corretor);

    // Consulta unificada: Eventos manuais (gerais) + Prazos de entrega das propostas permitidas ao professor
    $sql = "SELECT id_calendario AS id, titulo, tipo, data_evento, hora_limite, descricao, 'manual' AS origem
            FROM Calendário 
            WHERE data_evento BETWEEN '$data_inicio' AND '$data_fim'

            UNION ALL

            SELECT p.id_proposta AS id, p.titulo, 'proposta' AS tipo, DATE(p.prazo_entrega) AS data_evento, TIME(p.prazo_entrega) AS hora_limite, 'Prazo de entrega da proposta' AS descricao, 'proposta' AS origem
            FROM propostas p
            WHERE p.prazo_entrega IS NOT NULL AND DATE(p.prazo_entrega) BETWEEN '$data_inicio' AND '$data_fim' AND $filtro_turmas

            ORDER BY hora_limite ASC";

    $res = $conn->query($sql);
    $eventos = [];

    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $eventos[$row['data_evento']][] = $row;
        }
    }

    return $eventos;
}

// --- BUSCA OS PRÓXIMOS EVENTOS A PARTIR DE HOJE PARA O PROFESSOR ---
function obterProximosEventosProfessor($conn, $corretor) {
    $hoje = date('Y-m-d');
    $filtro_turmas = obterFiltroTurmasPropostasProfessor($corretor);

    $sql = "SELECT id_calendario AS id, titulo, tipo, data_evento, hora_limite, descricao, 'manual' AS origem
            FROM Calendário 
            WHERE data_evento >= '$hoje'

            UNION ALL

            SELECT p.id_proposta AS id, p.titulo, 'proposta' AS tipo, DATE(p.prazo_entrega) AS data_evento, TIME(p.prazo_entrega) AS hora_limite, 'Prazo de entrega da proposta' AS descricao, 'proposta' AS origem
            FROM propostas p
            WHERE p.prazo_entrega IS NOT NULL AND DATE(p.prazo_entrega) >= '$hoje' AND $filtro_turmas

            ORDER BY data_evento ASC, hora_limite ASC 
            LIMIT 4";

    $res = $conn->query($sql);
    $proximos = [];

    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $proximos[] = $row;
        }
    }

    return $proximos;
}
?>