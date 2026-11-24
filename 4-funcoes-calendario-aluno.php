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

// Recupera o ID do aluno autenticado
$id_aluno_atual = isset($_SESSION['id_aluno']) ? $_SESSION['id_aluno'] : 1; 

// Busca os dados do aluno para validar turma/curso
$stmt_aluno = $conn->prepare("SELECT * FROM aluno WHERE id_aluno = ?");
$stmt_aluno->bind_param("i", $id_aluno_atual);
$stmt_aluno->execute();
$aluno_info = $stmt_aluno->get_result()->fetch_assoc();
$stmt_aluno->close();

if (!$aluno_info) {
    die("Estudante não encontrado.");
}

$serie_atual = $aluno_info['serie'];
$turma_atual = strtolower($aluno_info['turma']);
$termo_curso = (strpos(strtolower($aluno_info['curso']), 'rede') !== false) ? 'rede' : 'info';
$coluna_turma_proposta = "t_" . $serie_atual . $turma_atual . $termo_curso;

// --- BUSCA EVENTOS DO MÊS ---
function obterEventosMesAluno($conn, $ano, $mes, $total_dias, $coluna_turma) {
    $data_inicio = sprintf("%04d-%02d-01", $ano, $mes);
    $data_fim    = sprintf("%04d-%02d-%02d", $ano, $mes, $total_dias);
    $hoje_sql    = date('Y-m-d H:i:s');

    // Regra: Exibe se a turma tem acesso, a data de entrega existe e está no mês, 
    // e o prazo de abertura ou já passou OU está vazio/nulo.
    $sql = "SELECT id_calendario AS id, titulo, tipo, data_evento, hora_limite, descricao, 'manual' AS origem
            FROM Calendário 
            WHERE data_evento BETWEEN '$data_inicio' AND '$data_fim'

            UNION ALL

            SELECT id_proposta AS id, titulo, 'proposta' AS tipo, DATE(prazo_entrega) AS data_evento, TIME(prazo_entrega) AS hora_limite, CONCAT('Prazo final da proposta: ', titulo) AS descricao, 'proposta' AS origem
            FROM propostas 
            WHERE `$coluna_turma` = 1 
              AND prazo_entrega IS NOT NULL 
              AND (prazo_abertura IS NULL || prazo_abertura <= '$hoje_sql')
              AND DATE(prazo_entrega) BETWEEN '$data_inicio' AND '$data_fim'

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

// --- BUSCA OS PRÓXIMOS EVENTOS A PARTIR DE HOJE ---
function obterProximosEventosAluno($conn, $coluna_turma) {
    $hoje = date('Y-m-d');
    $hoje_sql = date('Y-m-d H:i:s');

    $sql = "SELECT id_calendario AS id, titulo, tipo, data_evento, hora_limite, descricao, 'manual' AS origem
            FROM Calendário 
            WHERE data_evento >= '$hoje'

            UNION ALL

            SELECT id_proposta AS id, titulo, 'proposta' AS tipo, DATE(prazo_entrega) AS data_evento, TIME(prazo_entrega) AS hora_limite, CONCAT('Prazo final da proposta: ', titulo) AS descricao, 'proposta' AS origem
            FROM propostas 
            WHERE `$coluna_turma` = 1 
              AND prazo_entrega IS NOT NULL 
              AND (prazo_abertura IS NULL || prazo_abertura <= '$hoje_sql')
              AND DATE(prazo_entrega) >= '$hoje'

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