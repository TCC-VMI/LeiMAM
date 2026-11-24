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
    die("Falha na conexão com o banco de dados.");
}

// Recupera o ID do aluno autenticado na sessão de login
$id_aluno_atual = isset($_SESSION['id_aluno']) ? $_SESSION['id_aluno'] : 1; 
$ano_atual = date('Y');

// Busca os dados do aluno logado
$stmt_aluno = $conn->prepare("SELECT * FROM aluno WHERE id_aluno = ?");
$stmt_aluno->bind_param("i", $id_aluno_atual);
$stmt_aluno->execute();
$aluno_info = $stmt_aluno->get_result()->fetch_assoc();
$stmt_aluno->close();

if (!$aluno_info) {
    die("Estudante não encontrado.");
}

$nome_aluno  = $aluno_info['nome'];
$serie_atual = $aluno_info['serie'];
$turma_atual = strtolower($aluno_info['turma']);
$curso_atual = mb_strtoupper($aluno_info['curso'], 'UTF-8');

// Descobre o nome exato da coluna da turma na tabela propostas (ex: t_3ainfo, t_3arede, etc.)
$termo_curso = (strpos(strtolower($aluno_info['curso']), 'rede') !== false) ? 'rede' : 'info';
$coluna_turma_proposta = "t_" . $serie_atual . $turma_atual . $termo_curso;

/**
 * Função auxiliar para calcular se uma proposta está ATIVA no momento atual (Horário de Brasília)
 */
function statusPropostaAtiva($prazo_abertura, $prazo_entrega) {
    $agora = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    $dt_abertura = !empty($prazo_abertura) ? new DateTime($prazo_abertura, new DateTimeZone('America/Sao_Paulo')) : null;
    $dt_entrega  = !empty($prazo_entrega)  ? new DateTime($prazo_entrega,  new DateTimeZone('America/Sao_Paulo')) : null;

    if ($dt_abertura && $agora < $dt_abertura) {
        return false; // Ainda não abriu (agendada)
    }
    if ($dt_entrega && $agora > $dt_entrega) {
        return false; // Já encerrou
    }
    return true; // Está ativa dentro do prazo
}

// Inicializa contadores do aluno
$aluno_redacoes_pendentes  = 0;
$aluno_aguardando_correcao = 0;
$aluno_redacoes_corrigidas = 0;

// Busca todas as propostas direcionadas à turma deste aluno
$check_col = $conn->query("SHOW COLUMNS FROM propostas LIKE '$coluna_turma_proposta'");
if ($check_col && $check_col->num_rows > 0) {
    $sql_prop = "SELECT id_proposta, prazo_abertura, prazo_entrega FROM propostas WHERE `$coluna_turma_proposta` = 1";
    $res_prop = $conn->query($sql_prop);

    if ($res_prop && $res_prop->num_rows > 0) {
        while ($prop = $res_prop->fetch_assoc()) {
            $id_prop = $prop['id_proposta'];
            
            // Só contabiliza para pendentes se a proposta estiver ATIVA no momento
            $is_ativa = statusPropostaAtiva($prop['prazo_abertura'], $prop['prazo_entrega']);

            // Verifica se o aluno enviou redação para esta proposta
            $stmt_env = $conn->prepare("SELECT id_redacao FROM redacoes WHERE id_aluno = ? AND id_proposta = ?");
            $stmt_env->bind_param("ii", $id_aluno_atual, $id_prop);
            $stmt_env->execute();
            $res_env = $stmt_env->get_result();
            $envio = $res_env->fetch_assoc();
            $stmt_env->close();

            if (!$envio) {
                // Se NÃO há registro na tabela redacoes E a proposta está ativa -> É PENDENTE
                if ($is_ativa) {
                    $aluno_redacoes_pendentes++;
                }
            } else {
                // Se HÁ registro na tabela redacoes, verificamos se foi corrigida (tabela correcoes)
                $id_redacao = $envio['id_redacao'];
                $stmt_corr = $conn->prepare("SELECT id_correcao FROM correcoes WHERE id_redacao = ?");
                $stmt_corr->bind_param("i", $id_redacao);
                $stmt_corr->execute();
                $res_corr = $stmt_corr->get_result();
                $correcao = $res_corr->fetch_assoc();
                $stmt_corr->close();

                if ($correcao) {
                    // Está tanto em redacoes quanto em correcoes -> CORRIGIDA
                    $aluno_redacoes_corrigidas++;
                } else {
                    // Está em redacoes mas NÃO em correcoes -> AGUARDANDO CORREÇÃO
                    $aluno_aguardando_correcao++;
                }
            }
        }
    }
}

// 2. Média Geral e Notas por Competência do Aluno
$stmt_med = $conn->prepare("SELECT AVG(c.nota_total) AS media, AVG(c.nota_c1) as c1, AVG(c.nota_c2) as c2, AVG(c.nota_c3) as c3, AVG(c.nota_c4) as c4, AVG(c.nota_c5) as c5 FROM correcoes c JOIN redacoes r ON c.id_redacao = r.id_redacao WHERE r.id_aluno = ?");
$stmt_med->bind_param("i", $id_aluno_atual);
$stmt_med->execute();
$res_med = $stmt_med->get_result()->fetch_assoc();
$stmt_med->close();

$aluno_media_geral = $res_med['media'] ? round($res_med['media'], 1) : '-';
$aluno_competencias = [
    'c1' => $res_med['c1'] ? round($res_med['c1'], 1) : 0,
    'c2' => $res_med['c2'] ? round($res_med['c2'], 1) : 0,
    'c3' => $res_med['c3'] ? round($res_med['c3'], 1) : 0,
    'c4' => $res_med['c4'] ? round($res_med['c4'], 1) : 0,
    'c5' => $res_med['c5'] ? round($res_med['c5'], 1) : 0,
];

// Histórico de Temas Realizados pelo Aluno
$historico_temas = [];
$sql_hist = "SELECT p.titulo AS tema, r.enviado_em, c.nota_total FROM redacoes r JOIN propostas p ON r.id_proposta = p.id_proposta LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao WHERE r.id_aluno = ? ORDER BY r.enviado_em DESC";
$stmt_h = $conn->prepare($sql_hist);
$stmt_h->bind_param("i", $id_aluno_atual);
$stmt_h->execute();
$res_h = $stmt_h->get_result();
while ($h = $res_h->fetch_assoc()) {
    $historico_temas[] = [
        'tema' => $h['tema'],
        'data' => date('d/m/Y', strtotime($h['enviado_em'])),
        'nota' => $h['nota_total'] !== null ? $h['nota_total'] : 'Aguardando'
    ];
}
$stmt_h->close();
?>