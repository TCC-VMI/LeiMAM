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
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

$id_aluno_atual = isset($_SESSION['id_aluno']) ? $_SESSION['id_aluno'] : 1;

// Busca dados do aluno para identificar a turma exata
$stmt_aluno = $conn->prepare("SELECT * FROM aluno WHERE id_aluno = ?");
$stmt_aluno->bind_param("i", $id_aluno_atual);
$stmt_aluno->execute();
$aluno_info = $stmt_aluno->get_result()->fetch_assoc();
$stmt_aluno->close();

if (!$aluno_info) {
    die("Estudante não encontrado.");
}

$serie_aluno = $aluno_info['serie']; // Ex: 3
$turma_aluno = strtolower($aluno_info['turma']); // Ex: a
$curso_aluno = strtolower($aluno_info['curso']); // Ex: informatica ou redes

// Constrói o nome exato da coluna da turma na tabela propostas (ex: t_3ainfo ou t_3arede)
// Exemplo: 3 + a + info = t_3ainfo
$termo_curso = (strpos($curso_aluno, 'rede') !== false) ? 'rede' : 'info';
$coluna_turma_aluno = "t_" . $serie_aluno . $turma_aluno . $termo_curso;

/**
 * Calcula o status temporal da proposta com base nas datas e fuso horário
 */
function calcularStatusProposta($prazo_abertura, $prazo_entrega) {
    $agora = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    
    $dt_abertura = !empty($prazo_abertura) ? new DateTime($prazo_abertura, new DateTimeZone('America/Sao_Paulo')) : null;
    $dt_entrega  = !empty($prazo_entrega)  ? new DateTime($prazo_entrega,  new DateTimeZone('America/Sao_Paulo')) : null;

    if ($dt_abertura && $agora < $dt_abertura) {
        return 'agendada';
    } elseif ($dt_entrega && $agora > $dt_entrega) {
        return 'encerrada';
    } else {
        return 'ativa';
    }
}

/**
 * Busca as propostas do aluno com base na sua turma, aplicando filtros e ordenação por prioridade de prazo
 */
function buscarPropostasAluno($conn, $id_aluno, $coluna_turma, $busca = '', $filtro_status = '') {
    // Verifica se a coluna da turma existe na tabela propostas
    $check_col = $conn->query("SHOW COLUMNS FROM propostas LIKE '$coluna_turma'");
    if (!$check_col || $check_col->num_rows == 0) {
        return [];
    }

    $sql = "SELECT id_proposta, titulo, prazo_abertura, prazo_entrega, criado_em 
            FROM propostas 
            WHERE `$coluna_turma` = 1";

    if (!empty($busca)) {
        $busca_escapada = $conn->real_escape_string($busca);
        $sql .= " AND titulo LIKE '%$busca_escapada%'";
    }

    $sql .= " ORDER BY id_proposta DESC";

    $resultado = $conn->query($sql);
    $lista_propostas = [];

    if ($resultado && $resultado->num_rows > 0) {
        while ($p = $resultado->fetch_assoc()) {
            $status_temporal = calcularStatusProposta($p['prazo_abertura'], $p['prazo_entrega']);

            // REGRA: Oculta propostas agendadas (ainda não abriram)
            if ($status_temporal === 'agendada') {
                continue;
            }

            // Verifica o status de envio/correção do aluno para esta proposta
            $stmt_env = $conn->prepare("SELECT id_redacao FROM redacoes WHERE id_aluno = ? AND id_proposta = ?");
            $stmt_env->bind_param("ii", $id_aluno, $p['id_proposta']);
            $stmt_env->execute();
            $res_env = $stmt_env->get_result();
            $redacao = $res_env->fetch_assoc();
            $stmt_env->close();

            $status_aluno = ''; // 'pendente', 'aguardando', 'corrigida'

            if ($status_temporal === 'encerrada') {
                $status_aluno = 'encerrada';
            } else {
                if (!$redacao) {
                    $status_aluno = 'pendente';
                } else {
                    // Verifica se já foi corrigida na tabela correcoes
                    $stmt_corr = $conn->prepare("SELECT id_correcao FROM correcoes WHERE id_redacao = ?");
                    $stmt_corr->bind_param("i", $redacao['id_redacao']);
                    $stmt_corr->execute();
                    $res_corr = $stmt_corr->get_result();
                    $corrigida = $res_corr->fetch_assoc();
                    $stmt_corr->close();

                    if ($corrigida) {
                        $status_aluno = 'corrigida';
                    } else {
                        $status_aluno = 'aguardando';
                    }
                }
            }

            // Filtro específico escolhido pelo aluno no select (pendentes, enviadas/aguardando, corrigidas)
            if (!empty($filtro_status)) {
                if ($filtro_status === 'pendente' && $status_aluno !== 'pendente') continue;
                if ($filtro_status === 'enviada' && $status_aluno !== 'aguardando') continue;
                if ($filtro_status === 'corrigida' && $status_aluno !== 'corrigida') continue;
            }

            // Formatação do texto de prazo
            if ($status_temporal === 'encerrada') {
                $dt_enc = new DateTime($p['prazo_entrega'], new DateTimeZone('America/Sao_Paulo'));
                $prazo_texto = 'Encerrado em: ' . $dt_enc->format('d/m/Y - H:i');
            } elseif (!empty($p['prazo_entrega'])) {
                $dt_ent = new DateTime($p['prazo_entrega'], new DateTimeZone('America/Sao_Paulo'));
                $prazo_texto = 'Prazo: ' . $dt_ent->format('d/m/Y - H:i');
            } else {
                $prazo_texto = 'Sem prazo de entrega';
            }

            // Prioridade de ordenação: 1 = Prazo ativo, 2 = Sem prazo, 3 = Encerrada
            $prioridade = 2;
            if ($status_temporal === 'ativa') {
                $prioridade = !empty($p['prazo_entrega']) ? 1 : 2;
            } elseif ($status_temporal === 'encerrada') {
                $prioridade = 3;
            }

            $p['status_temporal'] = $status_temporal;
            $p['status_aluno'] = $status_aluno;
            $p['prazo_texto'] = $prazo_texto;
            $p['prioridade'] = $prioridade;

            $lista_propostas[] = $p;
        }
    }

    // Ordenação personalizada: Ativas com prazo > Ativas sem prazo > Encerradas
    usort($lista_propostas, function($a, $b) {
        if ($a['prioridade'] == $b['prioridade']) {
            return $b['id_proposta'] - $a['id_proposta']; // mais recentes primeiro
        }
        return $a['prioridade'] - $b['prioridade'];
    });

    return $lista_propostas;
}
?>