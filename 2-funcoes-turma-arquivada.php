<?php
// Configura o fuso horário oficial para o Brasil
date_default_timezone_set('America/Sao_Paulo');

// Conexão com o banco de dados
$host    = "localhost";
$usuario = "aluno";      
$senha   = "ceep@1234";  
$banco   = "VMI"; 

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Captura o ano atual do sistema
$ano_atual_brasil = (int) date('Y');

// Captura o ano passado pela URL (ex: ?id=1a-info&ano=2025) ou define o ano-1 por padrão
$ano_arquivado = isset($_GET['ano']) ? (int) $_GET['ano'] : ($ano_atual_brasil - 1);

// Mapeia o id_turma recebido na URL para seus atributos e coluna na tabela propostas
function mapearIdentificadorTurma($id_url) {
    switch ($id_url) {
        // --- 1º ANO ---
        case '1a-info':
            return ['serie' => 1, 'turma' => 'A', 'curso' => 'informatica', 'coluna_proposta' => 't_1ainfo', 'nome_completo' => 'Turma 1° A Informática'];
        case '1b-info':
            return ['serie' => 1, 'turma' => 'B', 'curso' => 'informatica', 'coluna_proposta' => 't_1binfo', 'nome_completo' => 'Turma 1° B Informática'];
        case '1a-redes':
            return ['serie' => 1, 'turma' => 'A', 'curso' => 'redes de computadores', 'coluna_proposta' => 't_1arede', 'nome_completo' => 'Turma 1° A Redes de Computadores'];
        case '1b-redes':
            return ['serie' => 1, 'turma' => 'B', 'curso' => 'redes de computadores', 'coluna_proposta' => 't_1brede', 'nome_completo' => 'Turma 1° B Redes de Computadores'];

        // --- 2º ANO ---
        case '2a-info':
            return ['serie' => 2, 'turma' => 'A', 'curso' => 'informatica', 'coluna_proposta' => 't_2ainfo', 'nome_completo' => 'Turma 2° A Informática'];
        case '2b-info':
            return ['serie' => 2, 'turma' => 'B', 'curso' => 'informatica', 'coluna_proposta' => 't_2binfo', 'nome_completo' => 'Turma 2° B Informática'];
        case '2a-redes':
            return ['serie' => 2, 'turma' => 'A', 'curso' => 'redes de computadores', 'coluna_proposta' => 't_2arede', 'nome_completo' => 'Turma 2° A Redes de Computadores'];
        case '2b-redes':
            return ['serie' => 2, 'turma' => 'B', 'curso' => 'redes de computadores', 'coluna_proposta' => 't_2brede', 'nome_completo' => 'Turma 2° B Redes de Computadores'];

        // --- 3º ANO ---
        case '3b-info':
            return ['serie' => 3, 'turma' => 'B', 'curso' => 'informatica', 'coluna_proposta' => 't_3binfo', 'nome_completo' => 'Turma 3° B Informática'];
        case '3a-redes':
            return ['serie' => 3, 'turma' => 'A', 'curso' => 'redes de computadores', 'coluna_proposta' => 't_3arede', 'nome_completo' => 'Turma 3° A Redes de Computadores'];
        case '3b-redes':
            return ['serie' => 3, 'turma' => 'B', 'curso' => 'redes de computadores', 'coluna_proposta' => 't_3brede', 'nome_completo' => 'Turma 3° B Redes de Computadores'];
        case '3a-info':
        default:
            return ['serie' => 3, 'turma' => 'A', 'curso' => 'informatica', 'coluna_proposta' => 't_3ainfo', 'nome_completo' => 'Turma 3° A Informática'];
    }
}

// Captura a turma via GET ou define padrão
$id_turma_url = $_GET['id'] ?? '3a-info';
$info_turma   = mapearIdentificadorTurma($id_turma_url);

$serie_turma  = $info_turma['serie'];
$turma_letra  = $info_turma['turma'];
$curso_turma  = $info_turma['curso'];
$coluna_prop  = $info_turma['coluna_proposta'];

// --- ABA 1: ESTATÍSTICAS DA TURMA NO ANO ARQUIVADO ---
function obterEstatisticasGeraisTurmaArquivada($conn, $serie, $turma, $curso, $coluna_prop, $ano) {
    // Alunos ativos da turma no ano arquivado
    $stmt_a = $conn->prepare("SELECT COUNT(*) AS total FROM aluno WHERE serie = ? AND turma = ? AND curso = ? AND ano = ?");
    $stmt_a->bind_param("issi", $serie, $turma, $curso, $ano);
    $stmt_a->execute();
    $total_alunos = $stmt_a->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt_a->close();

    // Média, Corrigidas e Aguardando no ano arquivado
    $sql_m = "SELECT 
                AVG(c.nota_total) AS media_geral,
                SUM(CASE WHEN c.id_redacao IS NOT NULL THEN 1 ELSE 0 END) AS corrigidas,
                SUM(CASE WHEN c.id_redacao IS NULL THEN 1 ELSE 0 END) AS aguardando
              FROM aluno a
              JOIN redacoes r ON a.id_aluno = r.id_aluno
              JOIN propostas p ON r.id_proposta = p.id_proposta
              LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao
              WHERE a.serie = ? AND a.turma = ? AND a.curso = ? AND a.ano = ? AND p.{$coluna_prop} = 1 AND YEAR(p.criado_em) = ?";

    $stmt_m = $conn->prepare($sql_m);
    $stmt_m->bind_param("issii", $serie, $turma, $curso, $ano, $ano);
    $stmt_m->execute();
    $res_m = $stmt_m->get_result()->fetch_assoc();
    $stmt_m->close();

    // Propostas liberadas para essa turma no ano arquivado
    $stmt_p = $conn->prepare("SELECT COUNT(*) AS total FROM propostas WHERE {$coluna_prop} = 1 AND YEAR(criado_em) = ?");
    $stmt_p->bind_param("i", $ano);
    $stmt_p->execute();
    $total_propostas = $stmt_p->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt_p->close();

    // Redações enviadas no ano arquivado
    $sql_e = "SELECT COUNT(r.id_redacao) AS total 
              FROM redacoes r 
              JOIN aluno a ON r.id_aluno = a.id_aluno 
              JOIN propostas p ON r.id_proposta = p.id_proposta
              WHERE a.serie = ? AND a.turma = ? AND a.curso = ? AND a.ano = ? AND p.{$coluna_prop} = 1 AND YEAR(p.criado_em) = ?";
    $stmt_e = $conn->prepare($sql_e);
    $stmt_e->bind_param("issii", $serie, $turma, $curso, $ano, $ano);
    $stmt_e->execute();
    $total_enviadas = $stmt_e->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt_e->close();

    $pendentes = ($total_alunos * $total_propostas) - $total_enviadas;

    return [
        'total_alunos' => $total_alunos,
        'media_geral'  => $res_m['media_geral'] ? round($res_m['media_geral']) : 0,
        'corrigidas'   => $res_m['corrigidas'] ?? 0,
        'aguardando'   => $res_m['aguardando'] ?? 0,
        'pendentes'    => $pendentes > 0 ? $pendentes : 0
    ];
}

function obterCompetenciasTurmaArquivada($conn, $serie, $turma, $curso, $coluna_prop, $ano) {
    $sql = "SELECT 
                AVG(c.nota_c1) AS c1, AVG(c.nota_c2) AS c2, 
                AVG(c.nota_c3) AS c3, AVG(c.nota_c4) AS c4, 
                AVG(c.nota_c5) AS c5
            FROM aluno a
            JOIN redacoes r ON a.id_aluno = r.id_aluno
            JOIN propostas p ON r.id_proposta = p.id_proposta
            JOIN correcoes c ON r.id_redacao = c.id_redacao
            WHERE a.serie = ? AND a.turma = ? AND a.curso = ? AND a.ano = ? AND p.{$coluna_prop} = 1 AND YEAR(p.criado_em) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issii", $serie, $turma, $curso, $ano, $ano);
    $stmt->execute();
    $d = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return [
        'c1' => $d['c1'] ? round($d['c1']) : 0,
        'c2' => $d['c2'] ? round($d['c2']) : 0,
        'c3' => $d['c3'] ? round($d['c3']) : 0,
        'c4' => $d['c4'] ? round($d['c4']) : 0,
        'c5' => $d['c5'] ? round($d['c5']) : 0
    ];
}

function obterTop5AlunosTurmaArquivada($conn, $serie, $turma, $curso, $ano) {
    $sql = "SELECT 
                a.id_aluno,
                a.nome,
                ROUND(AVG(c.nota_total)) AS media,
                COUNT(DISTINCT r.id_redacao) AS enviadas
            FROM aluno a
            INNER JOIN redacoes r ON a.id_aluno = r.id_aluno
            INNER JOIN correcoes c ON r.id_redacao = c.id_redacao
            WHERE a.serie = ? AND a.turma = ? AND a.curso = ? AND a.ano = ?
            GROUP BY a.id_aluno, a.nome
            ORDER BY media DESC, enviadas DESC
            LIMIT 5";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $serie, $turma, $curso, $ano);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $top = [];
    while ($row = $res->fetch_assoc()) {
        $top[] = $row;
    }
    $stmt->close();
    return $top;
}

// --- ABA 2: PROPOSTAS DA TURMA NO ANO ARQUIVADO ---
function obterPropostasTurmaArquivadaOrdenadas($conn, $coluna_prop, $total_alunos, $ano) {
    $sql = "SELECT id_proposta, titulo, prazo_abertura, prazo_entrega, criado_em FROM propostas WHERE {$coluna_prop} = 1 AND YEAR(criado_em) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ano);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $propostas = [];

    if ($res && $res->num_rows > 0) {
        while ($p = $res->fetch_assoc()) {
            $id_p = $p['id_proposta'];
            $stmt_e = $conn->prepare("SELECT COUNT(*) AS total FROM redacoes r JOIN aluno a ON r.id_aluno = a.id_aluno WHERE r.id_proposta = ? AND a.ano = ?");
            $stmt_e->bind_param("ii", $id_p, $ano);
            $stmt_e->execute();
            $enviados = $stmt_e->get_result()->fetch_assoc()['total'] ?? 0;
            $stmt_e->close();

            $porcentagem = $total_alunos > 0 ? round(($enviados / $total_alunos) * 100) : 0;

            $p['status']       = 'encerrada';
            $p['status_nome']  = 'Encerrada (Ano ' . $ano . ')';
            $p['prazo_texto']  = 'Ano Letivo Concluído';
            $p['enviados']     = $enviados;
            $p['porcentagem']  = $porcentagem;

            $propostas[] = $p;
        }
    }
    $stmt->close();
    return $propostas;
}

// --- ABA 3: LISTA DE ALUNOS DO ANO ARQUIVADO ---
function obterAlunosTurmaArquivada($conn, $serie, $turma, $curso, $coluna_prop, $ano) {
    $stmt_p = $conn->prepare("SELECT COUNT(*) AS total FROM propostas WHERE {$coluna_prop} = 1 AND YEAR(criado_em) = ?");
    $stmt_p->bind_param("i", $ano);
    $stmt_p->execute();
    $total_propostas = $stmt_p->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt_p->close();

    $sql = "SELECT 
                a.id_aluno,
                a.matricula,
                a.nome,
                COUNT(DISTINCT r.id_redacao) AS entregues,
                ROUND(AVG(c.nota_total)) AS media
            FROM aluno a
            LEFT JOIN redacoes r ON a.id_aluno = r.id_aluno
            LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao
            WHERE a.serie = ? AND a.turma = ? AND a.curso = ? AND a.ano = ?
            GROUP BY a.id_aluno, a.matricula, a.nome
            ORDER BY a.nome ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $serie, $turma, $curso, $ano);
    $stmt->execute();
    $res = $stmt->get_result();

    $alunos = [];
    while ($row = $res->fetch_assoc()) {
        $row['total_propostas'] = $total_propostas;
        $alunos[] = $row;
    }
    $stmt->close();
    return $alunos;
}

// --- ABA 4: HISTÓRICO DE ENVIOS DO ANO ARQUIVADO ---
function obterHistoricoEnviosTurmaArquivada($conn, $serie, $turma, $curso, $ano) {
    $sql = "SELECT 
                r.id_redacao,
                r.enviado_em,
                a.nome AS aluno_nome,
                p.titulo AS proposta_titulo,
                c.id_correcao,
                c.nota_total
            FROM redacoes r
            JOIN aluno a ON r.id_aluno = a.id_aluno
            JOIN propostas p ON r.id_proposta = p.id_proposta
            LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao
            WHERE a.serie = ? AND a.turma = ? AND a.curso = ? AND a.ano = ?
            ORDER BY r.enviado_em DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $serie, $turma, $curso, $ano);
    $stmt->execute();
    $res = $stmt->get_result();

    $historico = [];
    while ($row = $res->fetch_assoc()) {
        $historico[] = $row;
    }
    $stmt->close();
    return $historico;
}

// --- CARREGAMENTO DOS DADOS PARA O ANO ESPECIFICADO ---
$stats_turma     = obterEstatisticasGeraisTurmaArquivada($conn, $serie_turma, $turma_letra, $curso_turma, $coluna_prop, $ano_arquivado);
$competencias    = obterCompetenciasTurmaArquivada($conn, $serie_turma, $turma_letra, $curso_turma, $coluna_prop, $ano_arquivado);
$top_alunos      = obterTop5AlunosTurmaArquivada($conn, $serie_turma, $turma_letra, $curso_turma, $ano_arquivado);
$lista_propostas = obterPropostasTurmaArquivadaOrdenadas($conn, $coluna_prop, $stats_turma['total_alunos'], $ano_arquivado);
$lista_alunos    = obterAlunosTurmaArquivada($conn, $serie_turma, $turma_letra, $curso_turma, $coluna_prop, $ano_arquivado);
$historico_env   = obterHistoricoEnviosTurmaArquivada($conn, $serie_turma, $turma_letra, $curso_turma, $ano_arquivado);
?>