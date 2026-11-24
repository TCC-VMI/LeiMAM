<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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

// Captura o ID da redação enviado por GET
$id_redacao = $_GET['id'] ?? null;

if (!$id_redacao) {
    die("ID da redação não fornecido.");
}

$mensagem_erro = "";

// Identifica o ID do corretor loggado (se houver na sessão, caso contrário assume 1 ou NULL)
session_start();
$id_corretor = $_SESSION['id_corretor'] ?? 1; 

// Processa o formulário ao salvar a edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nota_c1 = intval($_POST['nota_c1'] ?? 0);
    $nota_c2 = intval($_POST['nota_c2'] ?? 0);
    $nota_c3 = intval($_POST['nota_c3'] ?? 0);
    $nota_c4 = intval($_POST['nota_c4'] ?? 0);
    $nota_c5 = intval($_POST['nota_c5'] ?? 0);
    $nota_total = $nota_c1 + $nota_c2 + $nota_c3 + $nota_c4 + $nota_c5;
    
    $comentario = trim($_POST['comentario'] ?? '');
    $favoritar = isset($_POST['favoritar']) ? 1 : 0;
    
    // Data e hora atual no Horário de Brasília
    $corrigido_em = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d H:i:s');

    // Atualiza a tabela de correções
    $sql_update = "UPDATE correcoes 
                   SET nota_c1 = ?, nota_c2 = ?, nota_c3 = ?, nota_c4 = ?, nota_c5 = ?, nota_total = ?, comentario_pedagogico = ?, corrigido_em = ?
                   WHERE id_redacao = ?";
    
    $stmt_up = $conn->prepare($sql_update);
    if ($stmt_up) {
        $stmt_up->bind_param("iiiiisssi", $nota_c1, $nota_c2, $nota_c3, $nota_c4, $nota_c5, $nota_total, $comentario, $corrigido_em, $id_redacao);
        $stmt_up->execute();
        $stmt_up->close();
    }

    // Gerencia os favoritos na tabela 'favoritas'
    if ($favoritar) {
        // Verifica se já existe favorito para esta redação e corretor para evitar duplicidade
        $sql_check_fav = "SELECT id_favorita FROM favoritas WHERE id_redacao = ? AND id_corretor = ?";
        $stmt_cf = $conn->prepare($sql_check_fav);
        $stmt_cf->bind_param("ii", $id_redacao, $id_corretor);
        $stmt_cf->execute();
        $res_cf = $stmt_cf->get_result();
        
        if ($res_cf->num_rows === 0) {
            $sql_ins_fav = "INSERT INTO favoritas (id_redacao, id_corretor, criado_em) VALUES (?, ?, ?)";
            $stmt_ins = $conn->prepare($sql_ins_fav);
            $stmt_ins->bind_param("iis", $id_redacao, $id_corretor, $corrigido_em);
            $stmt_ins->execute();
            $stmt_ins->close();
        }
        $stmt_cf->close();
    } else {
        // Remove dos favoritos se desmarcado
        $sql_del_fav = "DELETE FROM favoritas WHERE id_redacao = ? AND id_corretor = ?";
        $stmt_del = $conn->prepare($sql_del_fav);
        $stmt_del->bind_param("ii", $id_redacao, $id_corretor);
        $stmt_del->execute();
        $stmt_del->close();
    }

    // Redireciona de volta para a visualização
    $redirect_to = $_POST['redirect_to'] ?? '2-visualizar-redacao.php?id=' . $id_redacao;
    header("Location: " . $redirect_to);
    exit;
}

// Busca os dados atuais da redação, aluno, proposta, correção e verifica se está favoritada
$sql_busca = "SELECT 
                r.id_redacao, r.enviado_em, r.url_foto, r.id_aluno, r.id_proposta,
                a.nome AS nome_aluno, a.serie, a.turma, a.curso,
                p.titulo AS titulo_proposta,
                c.id_correcao, c.nota_total, c.nota_c1, c.nota_c2, c.nota_c3, c.nota_c4, c.nota_c5, c.comentario_pedagogico AS comentario,
                (SELECT COUNT(*) FROM favoritas f WHERE f.id_redacao = r.id_redacao AND f.id_corretor = ?) AS eh_favorito
            FROM redacoes r
            JOIN aluno a ON r.id_aluno = a.id_aluno
            JOIN propostas p ON r.id_proposta = p.id_proposta
            LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao
            WHERE r.id_redacao = ?";

$stmt = $conn->prepare($sql_busca);
$stmt->bind_param("ii", $id_corretor, $id_redacao);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Redação não encontrada.");
}

$dados_redacao = $res->fetch_assoc();
$stmt->close();

$is_favorito = ($dados_redacao['eh_favorito'] > 0);
?>