<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configura o fuso horário oficial para o Brasil (Brasília)
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

/**
 * Função para buscar propostas filtradas especificamente para o corretor logado,
 * considerando apenas o ano atual e turmas em comum entre a proposta e o professor.
 */
function buscarPropostasProfessor($conn, $corretor, $busca = '', $filtro_turma = '', $filtro_status = '') {
    $ano_atual = (int) date('Y');
    
    // Identifica quais turmas o professor leciona
    $colunas_turmas = [
        't_1ainfo', 't_1binfo', 't_1arede', 't_1brede',
        't_2ainfo', 't_2binfo', 't_2arede', 't_2brede',
        't_3ainfo', 't_3binfo', 't_3arede', 't_3brede'
    ];

    $turmas_permitidas_sql = [];
    foreach ($colunas_turmas as $col) {
        if (isset($corretor[$col]) && (int)$corretor[$col] === 1) {
            $turmas_permitidas_sql[] = "p.$col = 1";
        }
    }

    // Se o professor não leciona em nenhuma turma, retorna vazio
    if (empty($turmas_permitidas_sql)) {
        return [];
    }

    // A proposta deve ser do ano atual E estar em pelo menos uma turma do professor
    $sql = "SELECT p.* FROM propostas p WHERE YEAR(p.criado_em) = ? AND (" . implode(" OR ", $turmas_permitidas_sql) . ")";
    
    $params_types = "i";
    $params_values = [$ano_atual];

    // Filtro por termo de busca (título)
    if (!empty($busca)) {
        $sql .= " AND p.titulo LIKE ?";
        $params_types .= "s";
        $params_values[] = "%" . $busca . "%";
    }

    // Filtro específico por turma selecionada no select
    if (!empty($filtro_turma)) {
        $partes = explode('-', $filtro_turma);
        if (count($partes) === 2) {
            $serie = (int)substr($partes[0], 0, 1);
            $turma = strtoupper(substr($partes[0], 1, 1));
            $curso = ($partes[1] === 'info') ? 'informatica' : 'redes de computadores';
            
            $mapa_col = [
                1 => ['A' => ['informatica' => 't_1ainfo', 'redes de computadores' => 't_1arede'], 'B' => ['informatica' => 't_1binfo', 'redes de computadores' => 't_1brede']],
                2 => ['A' => ['informatica' => 't_2ainfo', 'redes de computadores' => 't_2arede'], 'B' => ['informatica' => 't_2binfo', 'redes de computadores' => 't_2brede']],
                3 => ['A' => ['informatica' => 't_3ainfo', 'redes de computadores' => 't_3arede'], 'B' => ['informatica' => 't_3binfo', 'redes de computadores' => 't_3brede']]
            ];

            $coluna_alvo = $mapa_col[$serie][$turma][$curso] ?? '';
            if (!empty($coluna_alvo) && isset($corretor[$coluna_alvo]) && (int)$corretor[$coluna_alvo] === 1) {
                $sql .= " AND p.$coluna_alvo = 1";
            } else {
                return []; // Turma não permitida para este professor
            }
        }
    }

    $sql .= " ORDER BY p.criado_em DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($params_types, ...$params_values);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $propostas = [];
    $agora = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));

    while ($row = $resultado->fetch_assoc()) {
        // Cálculo dinâmico do status
        $dt_abertura = !empty($row['prazo_abertura']) ? new DateTime($row['prazo_abertura'], new DateTimeZone('America/Sao_Paulo')) : null;
        $dt_entrega  = !empty($row['prazo_entrega'])  ? new DateTime($row['prazo_entrega'],  new DateTimeZone('America/Sao_Paulo')) : null;

        if ($dt_abertura && $agora < $dt_abertura) {
            $row['status'] = 'agendada';
            $row['status_nome'] = 'Agendada';
            $row['prazo_texto'] = 'Abre em: ' . $dt_abertura->format('d/m/Y H:i');
        } elseif ($dt_entrega && $agora > $dt_entrega) {
            $row['status'] = 'encerrada';
            $row['status_nome'] = 'Encerrada';
            $row['prazo_texto'] = 'Encerrado em: ' . $dt_entrega->format('d/m/Y H:i');
        } else {
            $row['status'] = 'ativa';
            $row['status_nome'] = 'Ativa';
            $row['prazo_texto'] = $dt_entrega ? 'Entrega até: ' . $dt_entrega->format('d/m/Y H:i') : 'Sem prazo limite';
        }

        // Filtro por status dinâmico calculado
        if (!empty($filtro_status) && $row['status'] !== $filtro_status) {
            continue;
        }

        $propostas[] = $row;
    }

    $stmt->close();
    return $propostas;
}
?>