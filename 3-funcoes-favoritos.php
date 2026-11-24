<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configura fuso horário padrão para Horário de Brasília
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

// Se a requisição for AJAX via POST (para favoritar/desfavoritar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'alternar_favorito') {
    header('Content-Type: application/json');
    
    $id_redacao = filter_input(INPUT_POST, 'id_redacao', FILTER_VALIDATE_INT);

    if (!$id_redacao) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'ID de redação inválido.']);
        exit;
    }

    // Verifica se já está favoritada por este corretor específico
    $stmtCheck = $conn->prepare("SELECT id_favorita FROM favoritas WHERE id_redacao = ? AND id_corretor = ?");
    $stmtCheck->bind_param("ii", $id_redacao, $id_corretor);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();

    if ($resCheck->num_rows > 0) {
        $stmtDel = $conn->prepare("DELETE FROM favoritas WHERE id_redacao = ? AND id_corretor = ?");
        $stmtDel->bind_param("ii", $id_redacao, $id_corretor);
        $stmtDel->execute();
        $stmtDel->close();
        echo json_encode(['sucesso' => true, 'favoritado' => false, 'mensagem' => 'Removida dos favoritos.']);
    } else {
        $stmtIns = $conn->prepare("INSERT INTO favoritas (id_redacao, id_corretor) VALUES (?, ?)");
        $stmtIns->bind_param("ii", $id_redacao, $id_corretor);
        $stmtIns->execute();
        $stmtIns->close();
        echo json_encode(['sucesso' => true, 'favoritado' => true, 'mensagem' => 'Adicionada aos favoritos!']);
    }
    $stmtCheck->close();
    exit;
}

// Função para buscar as redações favoritas do corretor logado, ordenadas por ordem de envio (enviado_em DESC)
function obterRedacoesFavoritasProfessor($conn, $id_corretor) {
    $sql = "SELECT 
                f.id_favorita,
                f.criado_em AS data_favoritado,
                r.id_redacao,
                r.enviado_em,
                a.nome AS nome_aluno,
                a.serie,
                a.turma,
                a.curso,
                p.titulo AS titulo_proposta,
                c.nota_total
            FROM favoritas f
            INNER JOIN redacoes r ON f.id_redacao = r.id_redacao
            INNER JOIN aluno a ON r.id_aluno = a.id_aluno
            INNER JOIN propostas p ON r.id_proposta = p.id_proposta
            LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao
            WHERE f.id_corretor = ?
            ORDER BY r.enviado_em DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_corretor);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $favoritas = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $favoritas[] = $row;
        }
    }
    $stmt->close();
    return $favoritas;
}

$redacoes_favoritas = obterRedacoesFavoritasProfessor($conn, $id_corretor);
?>