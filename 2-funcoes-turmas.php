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

// Captura o ano atual automaticamente pelo calendário brasileiro (ex: 2026)
$ano_atual_brasil = (int) date('Y');

// 1. Mapeia o id_turma recebido na URL (?id=1a-info) para seus atributos e coluna na tabela propostas
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

// --- ABA 1: ESTATÍSTICAS DA TURMA NO ANO ATUAL ---
function obterEstatisticasGeraisTurma($conn, $serie, $turma, $curso, $coluna_prop, $ano) {
    // Alunos ativos da turma no ano atual
    $stmt_a = $conn->prepare("SELECT COUNT(*) AS total FROM aluno WHERE serie = ? AND turma = ? AND curso = ? AND ano = ?");
    $stmt_a->bind_param("issi", $serie, $turma, $curso, $ano);
    $stmt_a->execute();
    $total_alunos = $stmt_a->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt_a->close();

    // Média, Corrigidas e Aguardando no ano atual
    $sql_m = "SELECT 
                AVG(c.nota_total) AS media_geral,
                SUM(CASE WHEN c.id_redacao IS NOT NULL THEN 1 ELSE 0 END) AS corrigidas,
                SUM(CASE WHEN c.id_redacao IS NULL THEN 1 ELSE 0 END) AS aguardando
              FROM aluno a
              JOIN redacoes r ON a.id_aluno = r.id_aluno
              JOIN propostas p ON r.id_proposta = p.id_proposta
              LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao
              WHERE a.serie = ? AND a.turma = ? AND a.curso = ? AND a.ano = ? AND p.{$coluna_prop} = 1";

    $stmt_m = $conn->prepare($sql_m);
    $stmt_m->bind_param("issi", $serie, $turma, $curso, $ano);
    $stmt_m->execute();
    $res_m = $stmt_m->get_result()->fetch_assoc();
    $stmt_m->close();

    // Propostas liberadas para essa turma
    $res_p = $conn->query("SELECT COUNT(*) AS total FROM propostas WHERE {$coluna_prop} = 1")->fetch_assoc();
    $total_propostas = $res_p['total'] ?? 0;

    // Redações enviadas no ano atual
    $sql_e = "SELECT COUNT(r.id_redacao) AS total 
              FROM redacoes r 
              JOIN aluno a ON r.id_aluno = a.id_aluno 
              JOIN propostas p ON r.id_proposta = p.id_proposta
              WHERE a.serie = ? AND a.turma = ? AND a.curso = ? AND a.ano = ? AND p.{$coluna_prop} = 1";
    $stmt_e = $conn->prepare($sql_e);
    $stmt_e->bind_param("issi", $serie, $turma, $curso, $ano);
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

function obterCompetenciasTurma($conn, $serie, $turma, $curso, $coluna_prop, $ano) {
    $sql = "SELECT 
                AVG(c.nota_c1) AS c1, AVG(c.nota_c2) AS c2, 
                AVG(c.nota_c3) AS c3, AVG(c.nota_c4) AS c4, 
                AVG(c.nota_c5) AS c5
            FROM aluno a
            JOIN redacoes r ON a.id_aluno = r.id_aluno
            JOIN propostas p ON r.id_proposta = p.id_proposta
            JOIN correcoes c ON r.id_redacao = c.id_redacao
            WHERE a.serie = ? AND a.turma = ? AND a.curso = ? AND a.ano = ? AND p.{$coluna_prop} = 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $serie, $turma, $curso, $ano);
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

function obterTop5AlunosTurma($conn, $serie, $turma, $curso, $ano) {
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

// --- ABA 2: PROPOSTAS DA TURMA NO ANO ATUAL (CORRIGIDO PARA CONSIDERAR APENAS OS ALUNOS DA TURMA) ---
function obterPropostasTurmaOrdenadas($conn, $coluna_prop, $total_alunos, $serie, $turma, $curso, $ano) {
    $sql = "SELECT id_proposta, titulo, prazo_abertura, prazo_entrega, criado_em FROM propostas WHERE {$coluna_prop} = 1";
    $res = $conn->query($sql);
    
    $agora = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    $propostas = [];

    if ($res && $res->num_rows > 0) {
        while ($p = $res->fetch_assoc()) {
            $dt_abertura = !empty($p['prazo_abertura']) ? new DateTime($p['prazo_abertura'], new DateTimeZone('America/Sao_Paulo')) : null;
            $dt_entrega  = !empty($p['prazo_entrega'])  ? new DateTime($p['prazo_entrega'],  new DateTimeZone('America/Sao_Paulo')) : null;

            if ($dt_abertura && $agora < $dt_abertura) {
                $status = 'agendada';
                $status_nome = 'Agendada';
                $prazo_texto = 'Libera em: ' . $dt_abertura->format('d/m/Y - H:i');
                $prioridade = 3;
            } elseif ($dt_entrega && $agora > $dt_entrega) {
                $status = 'encerrada';
                $status_nome = 'Encerrada';
                $prazo_texto = 'Encerrado em: ' . $dt_entrega->format('d/m/Y - H:i');
                $prioridade = 4;
            } else {
                $status = 'ativa';
                $status_nome = 'Ativa';
                if ($dt_entrega) {
                    $prazo_texto = 'Prazo: ' . $dt_entrega->format('d/m/Y - H:i');
                    $prioridade = 1;
                } else {
                    $prazo_texto = 'Disponível';
                    $prioridade = 2;
                }
            }

            $id_p = $p['id_proposta'];
            // Conta apenas os envios feitos por alunos desta turma específica no ano atual
            $stmt_e = $conn->prepare("SELECT COUNT(*) AS total FROM redacoes r JOIN aluno a ON r.id_aluno = a.id_aluno WHERE r.id_proposta = ? AND a.serie = ? AND a.turma = ? AND a.curso = ? AND a.ano = ?");
            $stmt_e->bind_param("iissi", $id_p, $serie, $turma, $curso, $ano);
            $stmt_e->execute();
            $enviados = $stmt_e->get_result()->fetch_assoc()['total'] ?? 0;
            $stmt_e->close();

            $porcentagem = $total_alunos > 0 ? round(($enviados / $total_alunos) * 100) : 0;

            $p['status']       = $status;
            $p['status_nome']  = $status_nome;
            $p['prazo_texto']  = $prazo_texto;
            $p['prioridade']   = $prioridade;
            $p['enviados']     = $enviados;
            $p['porcentagem']  = $porcentagem > 100 ? 100 : $porcentagem; // Garante limite máximo de 100%

            $propostas[] = $p;
        }
    }

    usort($propostas, function($a, $b) {
        if ($a['prioridade'] !== $b['prioridade']) {
            return $a['prioridade'] <=> $b['prioridade'];
        }
        if ($a['prioridade'] == 1) {
            return strtotime($a['prazo_entrega']) <=> strtotime($b['prazo_entrega']);
        }
        if ($a['prioridade'] == 2) {
            return strtotime($b['criado_em']) <=> strtotime($a['criado_em']);
        }
        if ($a['prioridade'] == 3) {
            return strtotime($a['prazo_abertura']) <=> strtotime($b['prazo_abertura']);
        }
        if ($a['prioridade'] == 4) {
            return strtotime($b['prazo_entrega']) <=> strtotime($a['prazo_entrega']);
        }
        return 0;
    });

    return $propostas;
}

// --- ABA 3: LISTA DE ALUNOS DO ANO ATUAL ---
function obterAlunosTurma($conn, $serie, $turma, $curso, $coluna_prop, $ano) {
    $res_p = $conn->query("SELECT COUNT(*) AS total FROM propostas WHERE {$coluna_prop} = 1")->fetch_assoc();
    $total_propostas = $res_p['total'] ?? 0;

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

// --- ABA 4: HISTÓRICO DE ENVIOS DO ANO ATUAL ---
function obterHistoricoEnviosTurma($conn, $serie, $turma, $curso, $ano) {
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

// --- CARREGAMENTO GLOBAL DOS DADOS ---
$stats_turma     = obterEstatisticasGeraisTurma($conn, $serie_turma, $turma_letra, $curso_turma, $coluna_prop, $ano_atual_brasil);
$competencias    = obterCompetenciasTurma($conn, $serie_turma, $turma_letra, $curso_turma, $coluna_prop, $ano_atual_brasil);
$top_alunos      = obterTop5AlunosTurma($conn, $serie_turma, $turma_letra, $curso_turma, $ano_atual_brasil);
// Passando os parâmetros corretos para filtrar apenas os alunos da turma no cálculo do progresso da proposta:
$lista_propostas = obterPropostasTurmaOrdenadas($conn, $coluna_prop, $stats_turma['total_alunos'], $serie_turma, $turma_letra, $curso_turma, $ano_atual_brasil);
$lista_alunos    = obterAlunosTurma($conn, $serie_turma, $turma_letra, $curso_turma, $coluna_prop, $ano_atual_brasil);
$historico_env   = obterHistoricoEnviosTurma($conn, $serie_turma, $turma_letra, $curso_turma, $ano_atual_brasil);
?>