<?php
// Configura fuso horário padrão para Horário de Brasília
date_default_timezone_set('America/Sao_Paulo');

// Conexão com o banco de dados
$host    = "localhost";
$usuario = "aluno";      
$senha   = "ceep@1234";  
$banco   = "VMI"; 

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

/**
 * Calcula dinamicamente o status e o texto do prazo com base nas datas e no Horário de Brasília
 */
function calcularStatusProposta($prazo_abertura, $prazo_entrega) {
    $agora = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    
    $dt_abertura = !empty($prazo_abertura) ? new DateTime($prazo_abertura, new DateTimeZone('America/Sao_Paulo')) : null;
    $dt_entrega  = !empty($prazo_entrega)  ? new DateTime($prazo_entrega,  new DateTimeZone('America/Sao_Paulo')) : null;

    $status = 'ativa';
    $prazo_texto = 'Sem data de entrega';

    if ($dt_abertura && $agora < $dt_abertura) {
        $status = 'agendada';
        $prazo_texto = 'Libera em: ' . $dt_abertura->format('d/m/Y - H:i');
    } elseif ($dt_entrega && $agora > $dt_entrega) {
        $status = 'encerrada';
        $prazo_texto = 'Encerrado em: ' . $dt_entrega->format('d/m/Y - H:i');
    } else {
        $status = 'ativa';
        if ($dt_entrega) {
            $prazo_texto = 'Prazo: ' . $dt_entrega->format('d/m/Y - H:i');
        } else {
            $prazo_texto = 'Sem prazo de entrega';
        }
    }

    return [
        'status'      => $status,
        'status_nome' => ucfirst($status),
        'prazo_texto' => $prazo_texto
    ];
}

/**
 * Busca e filtra propostas considerando as 12 turmas ativas
 */
function buscarPropostasFiltradas($conn, $busca = '', $turma = '', $status_filtro = '') {
    $sql = "SELECT id_proposta, titulo, prazo_abertura, prazo_entrega, 
                   t_1ainfo, t_1binfo, t_1arede, t_1brede,
                   t_2ainfo, t_2binfo, t_2arede, t_2brede,
                   t_3ainfo, t_3binfo, t_3arede, t_3brede, criado_em 
            FROM propostas WHERE 1=1";

    if (!empty($busca)) {
        $busca_escapada = $conn->real_escape_string($busca);
        $sql .= " AND titulo LIKE '%$busca_escapada%'";
    }

    // Filtros por Turma (1º, 2º e 3º Anos)
    if ($turma == '1a-info')  $sql .= " AND t_1ainfo = 1";
    if ($turma == '1b-info')  $sql .= " AND t_1binfo = 1";
    if ($turma == '1a-redes') $sql .= " AND t_1arede = 1";
    if ($turma == '1b-redes') $sql .= " AND t_1brede = 1";

    if ($turma == '2a-info')  $sql .= " AND t_2ainfo = 1";
    if ($turma == '2b-info')  $sql .= " AND t_2binfo = 1";
    if ($turma == '2a-redes') $sql .= " AND t_2arede = 1";
    if ($turma == '2b-redes') $sql .= " AND t_2brede = 1";

    if ($turma == '3a-info')  $sql .= " AND t_3ainfo = 1";
    if ($turma == '3b-info')  $sql .= " AND t_3binfo = 1";
    if ($turma == '3a-redes') $sql .= " AND t_3arede = 1";
    if ($turma == '3b-redes') $sql .= " AND t_3brede = 1";

    $sql .= " ORDER BY id_proposta DESC";

    $resultado = $conn->query($sql);
    $lista = [];

    if ($resultado && $resultado->num_rows > 0) {
        while ($p = $resultado->fetch_assoc()) {
            $calc = calcularStatusProposta($p['prazo_abertura'], $p['prazo_entrega']);
            
            // Filtro por status dinâmico após o cálculo
            if (!empty($status_filtro) && $calc['status'] !== $status_filtro) {
                continue;
            }

            $p['status']      = $calc['status'];
            $p['status_nome'] = $calc['status_nome'];
            $p['prazo_texto'] = $calc['prazo_texto'];

            $lista[] = $p;
        }
    }

    return $lista;
}
?>