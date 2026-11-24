<?php
// Configurações de conexão com o banco de dados
$host    = "localhost";
$usuario = "aluno";      
$senha   = "ceep@1234";  
$banco   = "VMI"; 

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Define dinamicamente o ano atual via calendário (ex: 2026)
$ano_atual = (int) date('Y');

// Mapeia (Série, Turma, Curso) para a coluna correspondente na tabela propostas
function obterColunaTurmaProposta($serie, $turma, $curso) {
    $c = strtolower(trim($curso));
    $t = strtoupper(trim($turma));
    $s = (int)$serie;

    $is_info  = strpos($c, 'info') !== false;
    $is_redes = strpos($c, 'rede') !== false;

    if ($s === 1) {
        if ($t === 'A' && $is_info)  return 't_1ainfo';
        if ($t === 'B' && $is_info)  return 't_1binfo';
        if ($t === 'A' && $is_redes) return 't_1arede';
        if ($t === 'B' && $is_redes) return 't_1brede';
    } elseif ($s === 2) {
        if ($t === 'A' && $is_info)  return 't_2ainfo';
        if ($t === 'B' && $is_info)  return 't_2binfo';
        if ($t === 'A' && $is_redes) return 't_2arede';
        if ($t === 'B' && $is_redes) return 't_2brede';
    } elseif ($s === 3) {
        if ($t === 'A' && $is_info)  return 't_3ainfo';
        if ($t === 'B' && $is_info)  return 't_3binfo';
        if ($t === 'A' && $is_redes) return 't_3arede';
        if ($t === 'B' && $is_redes) return 't_3brede';
    }

    return null;
}

// 1. Conta o total de alunos ativos de uma turma no ANO ATUAL
function contarAlunos($conn, $serie, $turma, $curso, $ano) {
    $sql = "SELECT COUNT(*) AS total FROM aluno WHERE serie = ? AND turma = ? AND curso = ? AND ano = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $serie, $turma, $curso, $ano);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $res['total'] ?? 0;
}

// 2. Conta redações por status geral para o ANO ATUAL
function contarRedacoesPorStatusGeral($conn, $status_busca, $ano) {
    if ($status_busca === 'corrigida') {
        $sql = "SELECT COUNT(DISTINCT r.id_redacao) AS total 
                FROM redacoes r 
                INNER JOIN correcoes c ON r.id_redacao = c.id_redacao
                INNER JOIN aluno a ON r.id_aluno = a.id_aluno
                WHERE a.ano = ?";
    } else {
        $sql = "SELECT COUNT(r.id_redacao) AS total 
                FROM redacoes r 
                LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao 
                INNER JOIN aluno a ON r.id_aluno = a.id_aluno
                WHERE c.id_redacao IS NULL AND a.ano = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ano);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $res['total'] ?? 0;
}

// 3. Média geral de todas as redações corrigidas no ANO ATUAL
function obterMediaGeral($conn, $ano) {
    $sql = "SELECT AVG(c.nota_total) AS media 
            FROM correcoes c
            JOIN redacoes r ON c.id_redacao = r.id_redacao
            JOIN aluno a ON r.id_aluno = a.id_aluno
            WHERE a.ano = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ano);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $res['media'] ? round($res['media']) : 0;
}

// 4. Estatísticas de uma turma filtrando apenas o ANO ATUAL e as propostas liberadas
function obterEstatisticasTurma($conn, $serie, $turma, $curso, $ano) {
    $coluna_turma = obterColunaTurmaProposta($serie, $turma, $curso);

    if (!$coluna_turma) {
        return ['media' => 0, 'aguardando' => 0, 'corrigidas' => 0, 'pendentes' => 0];
    }

    // Média da turma, Redações Corrigidas e Aguardando Correção no ANO ATUAL
    $sql_stats = "SELECT 
                    AVG(c.nota_total) AS media,
                    SUM(CASE WHEN c.id_redacao IS NOT NULL THEN 1 ELSE 0 END) AS corrigidas,
                    SUM(CASE WHEN c.id_redacao IS NULL THEN 1 ELSE 0 END) AS aguardando
                  FROM aluno a
                  JOIN redacoes r ON a.id_aluno = r.id_aluno
                  JOIN propostas p ON r.id_proposta = p.id_proposta
                  LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao
                  WHERE a.serie = ? AND a.turma = ? AND a.curso = ? AND a.ano = ? AND p.{$coluna_turma} = 1";

    $stmt = $conn->prepare($sql_stats);
    $stmt->bind_param("issi", $serie, $turma, $curso, $ano);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Redações Pendentes da Turma no ANO ATUAL
    $pendentes = calcularRedacoesPendentesTurma($conn, $serie, $turma, $curso, $ano, $coluna_turma);

    return [
        'media'      => $res['media'] ? round($res['media']) : 0,
        'aguardando' => $res['aguardando'] ?? 0,
        'corrigidas' => $res['corrigidas'] ?? 0,
        'pendentes'  => $pendentes
    ];
}

// 5. Cálculo de pendências por turma no ANO ATUAL
function calcularRedacoesPendentesTurma($conn, $serie, $turma, $curso, $ano, $coluna_turma) {
    $total_alunos = contarAlunos($conn, $serie, $turma, $curso, $ano);

    // Total de propostas liberadas para essa turma
    $sql_prop = "SELECT COUNT(*) AS total FROM propostas WHERE {$coluna_turma} = 1";
    $res_prop = $conn->query($sql_prop)->fetch_assoc();
    $propostas_turma = $res_prop['total'] ?? 0;

    // Total de redações enviadas por alunos desta turma no ano atual
    $sql_env = "SELECT COUNT(r.id_redacao) AS total
                FROM redacoes r
                JOIN aluno a ON r.id_aluno = a.id_aluno
                JOIN propostas p ON r.id_proposta = p.id_proposta
                WHERE a.serie = ? AND a.turma = ? AND a.curso = ? AND a.ano = ? AND p.{$coluna_turma} = 1";

    $stmt = $conn->prepare($sql_env);
    $stmt->bind_param("issi", $serie, $turma, $curso, $ano);
    $stmt->execute();
    $res_env = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $enviadas = $res_env['total'] ?? 0;
    $pendentes = ($total_alunos * $propostas_turma) - $enviadas;

    return $pendentes > 0 ? $pendentes : 0;
}

// 6. Cálculo geral de pendências de todas as 12 turmas no ANO ATUAL
function calcularRedacoesPendentesGerais($conn, $ano) {
    $turmas = [
        [1, 'A', 'informatica'], [1, 'B', 'informatica'], [1, 'A', 'redes de computadores'], [1, 'B', 'redes de computadores'],
        [2, 'A', 'informatica'], [2, 'B', 'informatica'], [2, 'A', 'redes de computadores'], [2, 'B', 'redes de computadores'],
        [3, 'A', 'informatica'], [3, 'B', 'informatica'], [3, 'A', 'redes de computadores'], [3, 'B', 'redes de computadores']
    ];

    $total_pendentes = 0;

    foreach ($turmas as $t) {
        $coluna = obterColunaTurmaProposta($t[0], $t[1], $t[2]);
        if ($coluna) {
            $total_pendentes += calcularRedacoesPendentesTurma($conn, $t[0], $t[1], $t[2], $ano, $coluna);
        }
    }

    return $total_pendentes;
}

// 7. Médias das 5 Competências do ENEM no ANO ATUAL
function obterMediasCompetencias($conn, $ano) {
    $sql = "SELECT 
                AVG(c.nota_c1) AS c1,
                AVG(c.nota_c2) AS c2,
                AVG(c.nota_c3) AS c3,
                AVG(c.nota_c4) AS c4,
                AVG(c.nota_c5) AS c5
            FROM correcoes c
            JOIN redacoes r ON c.id_redacao = r.id_redacao
            JOIN aluno a ON r.id_aluno = a.id_aluno
            WHERE a.ano = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ano);
    $stmt->execute();
    $dados = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return [
        'c1' => $dados['c1'] ? round($dados['c1']) : 0,
        'c2' => $dados['c2'] ? round($dados['c2']) : 0,
        'c3' => $dados['c3'] ? round($dados['c3']) : 0,
        'c4' => $dados['c4'] ? round($dados['c4']) : 0,
        'c5' => $dados['c5'] ? round($dados['c5']) : 0,
    ];
}

// 8. Top 5 Alunos com maior média no ANO ATUAL
function obterTop5Alunos($conn, $ano) {
    $sql = "SELECT 
                a.nome,
                CONCAT(a.serie, '° ', a.turma, ' ', UPPER(a.curso)) AS turma_formatada,
                ROUND(AVG(c.nota_total)) AS media,
                COUNT(r.id_redacao) AS enviadas
            FROM aluno a
            JOIN redacoes r ON a.id_aluno = r.id_aluno
            JOIN correcoes c ON r.id_redacao = c.id_redacao
            WHERE a.ano = ?
            GROUP BY a.id_aluno
            ORDER BY media DESC, enviadas DESC
            LIMIT 5";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ano);
    $stmt->execute();
    $result = $stmt->get_result();
    $alunos = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $alunos[] = $row;
        }
    }
    $stmt->close();

    return $alunos;
}

// --- Carregamento dos dados do Painel ---
$media_geral        = obterMediaGeral($conn, $ano_atual);
$aguardando_correc  = contarRedacoesPorStatusGeral($conn, 'aguardando_correcao', $ano_atual);
$redacoes_corrigida = contarRedacoesPorStatusGeral($conn, 'corrigida', $ano_atual);
$redacoes_pendentes = calcularRedacoesPendentesGerais($conn, $ano_atual);

// Carregamento das 12 Turmas (Organizadas por Série e Turma/Curso)
$turmas_dados = [];
$lista_turmas_config = [
    // 1º Ano
    '1a_info'  => [1, 'A', 'informatica'],
    '1b_info'  => [1, 'B', 'informatica'],
    '1a_redes' => [1, 'A', 'redes de computadores'],
    '1b_redes' => [1, 'B', 'redes de computadores'],
    // 2º Ano
    '2a_info'  => [2, 'A', 'informatica'],
    '2b_info'  => [2, 'B', 'informatica'],
    '2a_redes' => [2, 'A', 'redes de computadores'],
    '2b_redes' => [2, 'B', 'redes de computadores'],
    // 3º Ano
    '3a_info'  => [3, 'A', 'informatica'],
    '3b_info'  => [3, 'B', 'informatica'],
    '3a_redes' => [3, 'A', 'redes de computadores'],
    '3b_redes' => [3, 'B', 'redes de computadores'],
];

foreach ($lista_turmas_config as $chave => $cfg) {
    $turmas_dados[$chave] = [
        'total_alunos' => contarAlunos($conn, $cfg[0], $cfg[1], $cfg[2], $ano_atual),
        'stats'        => obterEstatisticasTurma($conn, $cfg[0], $cfg[1], $cfg[2], $ano_atual)
    ];
}

// Competências e Top 5
$competencias = obterMediasCompetencias($conn, $ano_atual);
$top_alunos   = obterTop5Alunos($conn, $ano_atual);
?>