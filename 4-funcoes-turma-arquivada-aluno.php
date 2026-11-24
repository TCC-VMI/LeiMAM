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

// Recupera o ID do aluno autenticado atualmente
$id_aluno_atual = isset($_SESSION['id_aluno']) ? $_SESSION['id_aluno'] : 1; 

// Captura o ID do aluno específico daquele ano passado pela URL (ex: ?id=15&ano=2025)
$id_aluno_historico = isset($_GET['id']) ? (int)$_GET['id'] : $id_aluno_atual;
$ano_arquivado = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y') - 1;

// Busca os dados cadastrais do aluno para aquele ano específico no histórico
$stmt_aluno = $conn->prepare("SELECT * FROM aluno WHERE id_aluno = ?");
$stmt_aluno->bind_param("i", $id_aluno_historico);
$stmt_aluno->execute();
$info_aluno_ano = $stmt_aluno->get_result()->fetch_assoc();
$stmt_aluno->close();

if (!$info_aluno_ano) {
    die("Registro do estudante não encontrado para este ano.");
}

$serie_turma = $info_aluno_ano['serie'];
$turma_letra = strtolower($info_aluno_ano['turma']);
$termo_curso = (strpos(strtolower($info_aluno_ano['curso']), 'rede') !== false) ? 'rede' : 'info';
$coluna_prop = "t_" . $serie_turma . $turma_letra . $termo_curso;
$nome_turma_completa = $serie_turma . "° " . strtoupper($info_aluno_ano['turma']) . " " . strtoupper($info_aluno_ano['curso']);

// --- ABA 1: ESTATÍSTICAS PESSOAIS DO ALUNO NO ANO ARQUIVADO ---
function obterEstatisticasPessoaisAluno($conn, $id_aluno, $coluna_prop, $ano) {
    // Total de propostas liberadas para aquela turma no ano
    $stmt_p = $conn->prepare("SELECT COUNT(*) AS total FROM propostas WHERE {$coluna_prop} = 1 AND YEAR(criado_em) = ?");
    $stmt_p->bind_param("i", $ano);
    $stmt_p->execute();
    $total_propostas = $stmt_p->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt_p->close();

    // Média anual pessoal, redações corrigidas e aguardando correção
    $sql_m = "SELECT 
                AVG(c.nota_total) AS media_anual,
                SUM(CASE WHEN c.id_correcao IS NOT NULL THEN 1 ELSE 0 END) AS corrigidas,
                SUM(CASE WHEN c.id_correcao IS NULL THEN 1 ELSE 0 END) AS aguardando
              FROM redacoes r
              JOIN propostas p ON r.id_proposta = p.id_proposta
              LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao
              WHERE r.id_aluno = ? AND YEAR(p.criado_em) = ?";
    
    $stmt_m = $conn->prepare($sql_m);
    $stmt_m->bind_param("ii", $id_aluno, $ano);
    $stmt_m->execute();
    $res_m = $stmt_m->get_result()->fetch_assoc();
    $stmt_m->close();

    // Total de redações enviadas pelo aluno no ano
    $stmt_e = $conn->prepare("SELECT COUNT(*) AS total FROM redacoes r JOIN propostas p ON r.id_proposta = p.id_proposta WHERE r.id_aluno = ? AND YEAR(p.criado_em) = ?");
    $stmt_e->bind_param("ii", $id_aluno, $ano);
    $stmt_e->execute();
    $total_enviadas = $stmt_e->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt_e->close();

    $pendentes = $total_propostas - $total_enviadas;

    return [
        'media_anual' => $res_m['media_anual'] ? round($res_m['media_anual']) : 0,
        'pendentes'   => $pendentes > 0 ? $pendentes : 0,
        'aguardando'  => $res_m['aguardando'] ?? 0,
        'corrigidas'  => $res_m['corrigidas'] ?? 0
    ];
}

function obterCompetenciasPessoaisAluno($conn, $id_aluno, $ano) {
    $sql = "SELECT 
                AVG(c.nota_c1) AS c1, AVG(c.nota_c2) AS c2, 
                AVG(c.nota_c3) AS c3, AVG(c.nota_c4) AS c4, 
                AVG(c.nota_c5) AS c5
            FROM redacoes r
            JOIN propostas p ON r.id_proposta = p.id_proposta
            JOIN correcoes c ON r.id_redacao = c.id_redacao
            WHERE r.id_aluno = ? AND YEAR(p.criado_em) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_aluno, $ano);
    $stmt->execute();
    $d = $stmt->get_result()->fetch_assoc();
    $stmt_stmt = $stmt->close();

    return [
        'c1' => $d['c1'] ? round($d['c1']) : 0,
        'c2' => $d['c2'] ? round($d['c2']) : 0,
        'c3' => $d['c3'] ? round($d['c3']) : 0,
        'c4' => $d['c4'] ? round($d['c4']) : 0,
        'c5' => $d['c5'] ? round($d['c5']) : 0
    ];
}

// Top 5 Melhores Notas de Propostas do Aluno no Ano
function obterTop5PropostasAluno($conn, $id_aluno, $ano) {
    $sql = "SELECT 
                p.titulo,
                c.nota_total
            FROM redacoes r
            JOIN propostas p ON r.id_proposta = p.id_proposta
            JOIN correcoes c ON r.id_redacao = c.id_redacao
            WHERE r.id_aluno = ? AND YEAR(p.criado_em) = ?
            ORDER BY c.nota_total DESC
            LIMIT 5";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_aluno, $ano);
    $stmt->execute();
    $res = $stmt->get_result();

    $top = [];
    while ($row = $res->fetch_assoc()) {
        $top[] = $row;
    }
    $stmt->close();
    return $top;
}

// --- ABA 2: PROPOSTAS DA TURMA NO ANO ARQUIVADO (Com status de envio do aluno) ---
function obterPropostasTurmaArquivadaAluno($conn, $id_aluno, $coluna_prop, $ano) {
    $sql = "SELECT id_proposta, titulo, prazo_abertura, prazo_entrega, criado_em FROM propostas WHERE {$coluna_prop} = 1 AND YEAR(criado_em) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ano);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $propostas = [];

    if ($res && $res->num_rows > 0) {
        while ($p = $res->fetch_assoc()) {
            $id_p = $p['id_proposta'];
            
            // Verifica se o aluno enviou redação para esta proposta
            $stmt_e = $conn->prepare("SELECT r.id_redacao, c.nota_total FROM redacoes r LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao WHERE r.id_aluno = ? AND r.id_proposta = ?");
            $stmt_e->bind_param("ii", $id_aluno, $id_p);
            $stmt_e->execute();
            $res_env = $stmt_e->get_result()->fetch_assoc();
            $stmt_e->close();

            if ($res_env) {
                $p['status'] = 'enviada';
                $p['status_nome'] = isset($res_env['nota_total']) ? 'Corrigida (' . $res_env['nota_total'] . ' pts)' : 'Enviada (Aguardando)';
                $p['id_redacao'] = $res_env['id_redacao'];
            } else {
                $p['status'] = 'pendente';
                $p['status_nome'] = 'Não Enviada / Perdida';
            }

            $propostas[] = $p;
        }
    }
    $stmt->close();
    return $propostas;
}

// Carregamento dos dados
$stats_pessoais  = obterEstatisticasPessoaisAluno($conn, $id_aluno_historico, $coluna_prop, $ano_arquivado);
$competencias    = obterCompetenciasPessoaisAluno($conn, $id_aluno_historico, $ano_arquivado);
$top_propostas   = obterTop5PropostasAluno($conn, $id_aluno_historico, $ano_arquivado);
$lista_propostas = obterPropostasTurmaArquivadaAluno($conn, $id_aluno_historico, $coluna_prop, $ano_arquivado);
?>