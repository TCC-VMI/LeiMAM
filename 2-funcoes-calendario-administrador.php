<?php
// Configura fuso horário padrão para Brasília
date_default_timezone_set('America/Sao_Paulo');

// Conexão com o Banco de Dados
$host    = "localhost";
$usuario = "aluno";       
$senha   = "ceep@1234";   
$banco   = "VMI";      

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// --- PROCESSA A EXCLUSÃO DE EVENTO MANUAL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'excluir_evento') {
    $id_excluir = (int)$_POST['id_evento'];
    
    if ($id_excluir > 0) {
        $sql_delete = "DELETE FROM Calendário WHERE id_calendario = ?";
        $stmt = $conn->prepare($sql_delete);
        $stmt->bind_param("i", $id_excluir);
        
        if ($stmt->execute()) {
            $stmt->close();
            $mes_redir = isset($_POST['mes']) ? (int)$_POST['mes'] : date('n');
            $ano_redir = isset($_POST['ano']) ? (int)$_POST['ano'] : date('Y');
            header("Location: 2-calendario-administrador.php?mes=" . $mes_redir . "&ano=" . $ano_redir);
            exit();
        } else {
            echo "<script>alert('Erro ao excluir evento do banco: " . $conn->error . "');</script>";
        }
    }
}

// --- PROCESSA INSERÇÃO DE NOVO EVENTO MANUAL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'inserir_evento') {
    $titulo      = $_POST['titulo'];
    $tipo        = $_POST['tipo'];
    $data_evento = $_POST['data_evento'];
    $hora_limite = !empty($_POST['hora_limite']) ? $_POST['hora_limite'] : NULL;
    $descricao   = $_POST['descricao'];

    $sql_insert = "INSERT INTO Calendário (titulo, tipo, data_evento, hora_limite, descricao) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("sssss", $titulo, $tipo, $data_evento, $hora_limite, $descricao);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: 2-calendario-administrador.php?mes=" . date("n", strtotime($data_evento)) . "&ano=" . date("Y", strtotime($data_evento)));
        exit();
    } else {
        echo "<script>alert('Erro ao guardar evento: " . $conn->error . "');</script>";
    }
}

// --- BUSCA EVENTOS DO MÊS (UNINDO EVENTOS MANUAIS + PROPOSTAS AUTOMÁTICAS) ---
function obterEventosMes($conn, $ano, $mes, $total_dias) {
    $data_inicio = sprintf("%04d-%02d-01", $ano, $mes);
    $data_fim    = sprintf("%04d-%02d-%02d", $ano, $mes, $total_dias);

    // Consulta unificada: Eventos manuais + Prazos de entrega das propostas
    $sql = "SELECT id_calendario AS id, titulo, tipo, data_evento, hora_limite, descricao, 'manual' AS origem
            FROM Calendário 
            WHERE data_evento BETWEEN '$data_inicio' AND '$data_fim'

            UNION ALL

            SELECT id_proposta AS id, titulo, 'proposta' AS tipo, DATE(prazo_entrega) AS data_evento, TIME(prazo_entrega) AS hora_limite, 'Prazo de entrega da proposta' AS descricao, 'proposta' AS origem
            FROM propostas 
            WHERE prazo_entrega IS NOT NULL AND DATE(prazo_entrega) BETWEEN '$data_inicio' AND '$data_fim'

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
function obterProximosEventos($conn) {
    $hoje = date('Y-m-d');

    $sql = "SELECT id_calendario AS id, titulo, tipo, data_evento, hora_limite, descricao, 'manual' AS origem
            FROM Calendário 
            WHERE data_evento >= '$hoje'

            UNION ALL

            SELECT id_proposta AS id, titulo, 'proposta' AS tipo, DATE(prazo_entrega) AS data_evento, TIME(prazo_entrega) AS hora_limite, 'Prazo de entrega da proposta' AS descricao, 'proposta' AS origem
            FROM propostas 
            WHERE prazo_entrega IS NOT NULL AND DATE(prazo_entrega) >= '$hoje'

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