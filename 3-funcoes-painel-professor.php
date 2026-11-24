<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configura o fuso horário oficial do Brasil
date_default_timezone_set('America/Sao_Paulo');

// Valida se o professor está logado
if (!isset($_SESSION['id_corretor'])) {
    header("Location: 1-login-professor.html");
    exit;
}

$host    = "localhost";
$usuario = "aluno";      
$senha   = "ceep@1234";  
$banco   = "VMI"; 

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Captura o ano atual do Brasil
$ano_atual = (int) date('Y');
$id_corretor = (int) $_SESSION['id_corretor'];

// Busca os dados do professor logado
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

// Mapeamento das colunas de turmas na tabela corretores
$mapeamento_turmas = [
    '1a-info'  => ['serie' => 1, 'turma' => 'A', 'curso' => 'informatica', 'col' => 't_1ainfo', 'nome_exibicao' => '1° A INFO'],
    '1b-info'  => ['serie' => 1, 'turma' => 'B', 'curso' => 'informatica', 'col' => 't_1binfo', 'nome_exibicao' => '1° B INFO'],
    '1a-redes' => ['serie' => 1, 'turma' => 'A', 'curso' => 'redes de computadores', 'col' => 't_1arede', 'nome_exibicao' => '1° A REDES'],
    '1b-redes' => ['serie' => 1, 'turma' => 'B', 'curso' => 'redes de computadores', 'col' => 't_1brede', 'nome_exibicao' => '1° B REDES'],
    
    '2a-info'  => ['serie' => 2, 'turma' => 'A', 'curso' => 'informatica', 'col' => 't_2ainfo', 'nome_exibicao' => '2° A INFO'],
    '2b-info'  => ['serie' => 2, 'turma' => 'B', 'curso' => 'informatica', 'col' => 't_2binfo', 'nome_exibicao' => '2° B INFO'],
    '2a-redes' => ['serie' => 2, 'turma' => 'A', 'curso' => 'redes de computadores', 'col' => 't_2arede', 'nome_exibicao' => '2° A REDES'],
    '2b-redes' => ['serie' => 2, 'turma' => 'B', 'curso' => 'redes de computadores', 'col' => 't_2brede', 'nome_exibicao' => '2° B REDES'],
    
    '3a-info'  => ['serie' => 3, 'turma' => 'A', 'curso' => 'informatica', 'col' => 't_3ainfo', 'nome_exibicao' => '3° A INFO'],
    '3b-info'  => ['serie' => 3, 'turma' => 'B', 'curso' => 'informatica', 'col' => 't_3binfo', 'nome_exibicao' => '3° B INFO'],
    '3a-redes' => ['serie' => 3, 'turma' => 'A', 'curso' => 'redes de computadores', 'col' => 't_3arede', 'nome_exibicao' => '3° A REDES'],
    '3b-redes' => ['serie' => 3, 'turma' => 'B', 'curso' => 'redes de computadores', 'col' => 't_3brede', 'nome_exibicao' => '3° B REDES'],
];

// Identifica as turmas associadas a este corretor no registro atual
$turmas_do_corretor = [];
foreach ($mapeamento_turmas as $chave => $info) {
    if (isset($corretor[$info['col']]) && (int)$corretor[$info['col']] === 1) {
        $turmas_do_corretor[$chave] = $info;
    }
}

// Função para mapear a coluna da tabela de propostas
function obterColunaTurmaPropostaProf($serie, $turma, $curso) {
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

// Conta alunos de uma turma no ano atual do Brasil
function contarAlunosProf($conn, $serie, $turma, $curso, $ano) {
    $sql = "SELECT COUNT(*) AS total FROM aluno WHERE serie = ? AND turma = ? AND curso = ? AND ano = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $serie, $turma, $curso, $ano);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $res['total'] ?? 0;
}

// Redações pendentes de uma turma
function calcularRedacoesPendentesTurmaProf($conn, $serie, $turma, $curso, $ano, $coluna_turma) {
    $total_alunos = contarAlunosProf($conn, $serie, $turma, $curso, $ano);

    $sql_prop = "SELECT COUNT(*) AS total FROM propostas WHERE {$coluna_turma} = 1 AND YEAR(criado_em) = ?";
    $stmt_p = $conn->prepare($sql_prop);
    $stmt_p->bind_param("i", $ano);
    $stmt_p->execute();
    $propostas_turma = $stmt_p->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt_p->close();

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

// Estatísticas da turma no ano atual
function obterEstatisticasTurmaProf($conn, $serie, $turma, $curso, $ano) {
    $coluna_turma = obterColunaTurmaPropostaProf($serie, $turma, $curso);
    if (!$coluna_turma) {
        return ['media' => 0, 'aguardando' => 0, 'corrigidas' => 0, 'pendentes' => 0];
    }

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

    $pendentes = calcularRedacoesPendentesTurmaProf($conn, $serie, $turma, $curso, $ano, $coluna_turma);

    return [
        'media'      => $res['media'] ? round($res['media']) : 0,
        'aguardando' => $res['aguardando'] ?? 0,
        'corrigidas' => $res['corrigidas'] ?? 0,
        'pendentes'  => $pendentes
    ];
}

// Monta filtro SQL para restringir às turmas do corretor
function obterFiltroTurmasCorretorSQL($turmas_do_corretor, $alias_aluno = 'a') {
    if (empty($turmas_do_corretor)) {
        return "1 = 0";
    }
    $condicoes = [];
    foreach ($turmas_do_corretor as $t) {
        $s = $t['serie'];
        $tu = $t['turma'];
        $c = $t['curso'];
        $condicoes[] = "({$alias_aluno}.serie = {$s} AND {$alias_aluno}.turma = '{$tu}' AND {$alias_aluno}.curso = '{$c}')";
    }
    return "(" . implode(" OR ", $condicoes) . ")";
}

$filtro_sql_turmas = obterFiltroTurmasCorretorSQL($turmas_do_corretor, 'a');

// Média Geral
function obterMediaGeralProf($conn, $ano, $filtro_sql) {
    $sql = "SELECT AVG(c.nota_total) AS media 
            FROM correcoes c
            JOIN redacoes r ON c.id_redacao = r.id_redacao
            JOIN aluno a ON r.id_aluno = a.id_aluno
            WHERE a.ano = ? AND {$filtro_sql}";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ano);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $res['media'] ? round($res['media']) : 0;
}

// Contagem de redações por status
function contarRedacoesStatusProf($conn, $status_busca, $ano, $filtro_sql) {
    if ($status_busca === 'corrigida') {
        $sql = "SELECT COUNT(DISTINCT r.id_redacao) AS total 
                FROM redacoes r 
                INNER JOIN correcoes c ON r.id_redacao = c.id_redacao
                INNER JOIN aluno a ON r.id_aluno = a.id_aluno
                WHERE a.ano = ? AND {$filtro_sql}";
    } else {
        $sql = "SELECT COUNT(r.id_redacao) AS total 
                FROM redacoes r 
                LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao 
                INNER JOIN aluno a ON r.id_aluno = a.id_aluno
                WHERE c.id_redacao IS NULL AND a.ano = ? AND {$filtro_sql}";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ano);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $res['total'] ?? 0;
}

// Pendências gerais
function calcularRedacoesPendentesProfGeral($conn, $ano, $turmas_do_corretor) {
    $total_pendentes = 0;
    foreach ($turmas_do_corretor as $t) {
        $coluna = obterColunaTurmaPropostaProf($t['serie'], $t['turma'], $t['curso']);
        if ($coluna) {
            $total_pendentes += calcularRedacoesPendentesTurmaProf($conn, $t['serie'], $t['turma'], $t['curso'], $ano, $coluna);
        }
    }
    return $total_pendentes;
}

// Médias de Competências (ENEM)
function obterMediasCompetenciasProf($conn, $ano, $filtro_sql) {
    $sql = "SELECT 
                AVG(c.nota_c1) AS c1, AVG(c.nota_c2) AS c2,
                AVG(c.nota_c3) AS c3, AVG(c.nota_c4) AS c4,
                AVG(c.nota_c5) AS c5
            FROM correcoes c
            JOIN redacoes r ON c.id_redacao = r.id_redacao
            JOIN aluno a ON r.id_aluno = a.id_aluno
            WHERE a.ano = ? AND {$filtro_sql}";

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

// Top 5 Alunos
function obterTop5AlunosProf($conn, $ano, $filtro_sql) {
    $sql = "SELECT 
                a.nome,
                CONCAT(a.serie, '° ', a.turma, ' ', UPPER(a.curso)) AS turma_formatada,
                ROUND(AVG(c.nota_total)) AS media,
                COUNT(r.id_redacao) AS enviadas
            FROM aluno a
            JOIN redacoes r ON a.id_aluno = r.id_aluno
            JOIN correcoes c ON r.id_redacao = c.id_redacao
            WHERE a.ano = ? AND {$filtro_sql}
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

// Execução dos carregamentos principais
$media_geral        = obterMediaGeralProf($conn, $ano_atual, $filtro_sql_turmas);
$aguardando_correc  = contarRedacoesStatusProf($conn, 'aguardando_correcao', $ano_atual, $filtro_sql_turmas);
$redacoes_corrigida = contarRedacoesStatusProf($conn, 'corrigida', $ano_atual, $filtro_sql_turmas);
$redacoes_pendentes = calcularRedacoesPendentesProfGeral($conn, $ano_atual, $turmas_do_corretor);

$turmas_dados = [];
foreach ($turmas_do_corretor as $chave => $cfg) {
    $turmas_dados[$chave] = [
        'nome_exibicao' => $cfg['nome_exibicao'],
        'total_alunos'  => contarAlunosProf($conn, $cfg['serie'], $cfg['turma'], $cfg['curso'], $ano_atual),
        'stats'         => obterEstatisticasTurmaProf($conn, $cfg['serie'], $cfg['turma'], $cfg['curso'], $ano_atual)
    ];
}

$competencias = obterMediasCompetenciasProf($conn, $ano_atual, $filtro_sql_turmas);
$top_alunos   = obterTop5AlunosProf($conn, $ano_atual, $filtro_sql_turmas);
?>