<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicia a sessão para garantir que $_SESSION funcione corretamente
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configura o fuso horário oficial para o Brasil (Brasília)
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

// Captura o ID da redação via GET
$id_redacao = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_redacao) {
    die("Redação não informada ou ID inválido.");
}

// ID do corretor logado
$id_corretor = isset($_SESSION['id_corretor']) ? $_SESSION['id_corretor'] : 1;

// Processa o envio do formulário de correção
$mensagem_erro = "";
$mensagem_sucesso = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $c1 = filter_input(INPUT_POST, 'nota_c1', FILTER_VALIDATE_INT);
    $c2 = filter_input(INPUT_POST, 'nota_c2', FILTER_VALIDATE_INT);
    $c3 = filter_input(INPUT_POST, 'nota_c3', FILTER_VALIDATE_INT);
    $c4 = filter_input(INPUT_POST, 'nota_c4', FILTER_VALIDATE_INT);
    $c5 = filter_input(INPUT_POST, 'nota_c5', FILTER_VALIDATE_INT);
    $comentario = trim($_POST['comentario'] ?? '');
    $favoritar  = isset($_POST['favoritar']) ? true : false;
    
    // Captura a URL de redirecionamento enviada pelo formulário ou define um fallback seguro
    $redirect_to = $_POST['redirect_to'] ?? '2-painel-administrador.php';

    if ($c1 !== false && $c2 !== false && $c3 !== false && $c4 !== false && $c5 !== false) {
        $nota_total = $c1 + $c2 + $c3 + $c4 + $c5;
        
        // Data e hora atual rigorosamente no fuso de Brasília
        $data_hora_brasilia = date('Y-m-d H:i:s');

        // Verifica se já existe correção para esta redação
        $stmt_chk = $conn->prepare("SELECT id_correcao FROM correcoes WHERE id_redacao = ?");
        $stmt_chk->bind_param("i", $id_redacao);
        $stmt_chk->execute();
        $res_chk = $stmt_chk->get_result();

        if ($res_chk->num_rows > 0) {
            $row_c = $res_chk->fetch_assoc();
            $id_correcao = $row_c['id_correcao'];

            // Atualiza a correção incluindo a data atualizada (se houver a coluna correspondente no banco)
            // Caso sua tabela utilize outra coluna de data (ex: atualizado_em), ajuste abaixo:
            $stmt_up = $conn->prepare("UPDATE correcoes SET id_corretor = ?, nota_c1 = ?, nota_c2 = ?, nota_c3 = ?, nota_c4 = ?, nota_c5 = ?, nota_total = ?, comentario_pedagogico = ? WHERE id_redacao = ?");
            $stmt_up->bind_param("iiiiiiisi", $id_corretor, $c1, $c2, $c3, $c4, $c5, $nota_total, $comentario, $id_redacao);
            $stmt_up->execute();
            $stmt_up->close();
        } else {
            // Insere a nova correção
            $stmt_ins = $conn->prepare("INSERT INTO correcoes (id_redacao, id_corretor, nota_c1, nota_c2, nota_c3, nota_c4, nota_c5, nota_total, comentario_pedagogico) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_ins->bind_param("iiiiiiiis", $id_redacao, $id_corretor, $c1, $c2, $c3, $c4, $c5, $nota_total, $comentario);
            $stmt_ins->execute();
            $stmt_ins->close();
        }
        $stmt_chk->close();

        // Gerencia o favorito
        $stmt_fav_chk = $conn->prepare("SELECT id_favorita FROM favoritas WHERE id_redacao = ? AND id_corretor = ?");
        $stmt_fav_chk->bind_param("ii", $id_redacao, $id_corretor);
        $stmt_fav_chk->execute();
        $res_fav = $stmt_fav_chk->get_result();
        $ja_favorito = $res_fav->num_rows > 0;
        $stmt_fav_chk->close();

        if ($favoritar && !$ja_favorito) {
            $stmt_f_ins = $conn->prepare("INSERT INTO favoritas (id_redacao, id_corretor) VALUES (?, ?)");
            $stmt_f_ins->bind_param("ii", $id_redacao, $id_corretor);
            $stmt_f_ins->execute();
            $stmt_f_ins->close();
        } elseif (!$favoritar && $ja_favorito) {
            $stmt_f_del = $conn->prepare("DELETE FROM favoritas WHERE id_redacao = ? AND id_corretor = ?");
            $stmt_f_del->bind_param("ii", $id_redacao, $id_corretor);
            $stmt_f_del->execute();
            $stmt_f_del->close();
        }

        // Redireciona dinamicamente para a página de origem
        header("Location: " . $redirect_to);
        exit;
    } else {
        $mensagem_erro = "Por favor, preencha todas as notas corretamente.";
    }
}

// Busca os dados completos da redação
$sql_detalhes = "SELECT 
                    r.id_redacao, r.url_foto, r.enviado_em,
                    a.nome AS nome_aluno, a.serie, a.turma, a.curso, a.matricula,
                    p.titulo AS titulo_proposta, p.instrucoes AS texto_proposta,
                    c.nota_c1, c.nota_c2, c.nota_c3, c.nota_c4, c.nota_c5, c.nota_total, c.comentario_pedagogico AS comentario
                   FROM redacoes r
                   JOIN aluno a ON r.id_aluno = a.id_aluno
                   JOIN propostas p ON r.id_proposta = p.id_proposta
                   LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao
                   WHERE r.id_redacao = ?";

$stmt = $conn->prepare($sql_detalhes);
$stmt->bind_param("i", $id_redacao);
$stmt->execute();
$dados_redacao = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$dados_redacao) {
    die("Redação não encontrada no sistema.");
}

$stmt_fav = $conn->prepare("SELECT id_favorita FROM favoritas WHERE id_redacao = ? AND id_corretor = ?");
$stmt_fav->bind_param("ii", $id_redacao, $id_corretor);
$stmt_fav->execute();
$is_favorito = $stmt_fav->get_result()->num_rows > 0;
$stmt_fav->close();
?>