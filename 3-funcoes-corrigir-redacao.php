<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configura o fuso horário oficial para o Brasil (Brasília)
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
    die("Falha na conexão: " . $conn->connect_error);
}

// Captura o ID da redação via GET
$id_redacao = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_redacao) {
    die("Redação não informada ou ID inválido.");
}

// ID do corretor logado na sessão
$id_corretor = (int) $_SESSION['id_corretor'];

// Busca dados do corretor logado para validação de turmas
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

// Busca os dados completos da redação e do aluno
$sql_detalhes = "SELECT 
                    r.id_redacao, r.url_foto, r.enviado_em,
                    a.id_aluno, a.nome AS nome_aluno, a.serie, a.turma, a.curso, a.matricula,
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

// --- NORMALIZAÇÃO E VALIDAÇÃO ROBUSTA DA TURMA ---
$serie_aluno = (int) $dados_redacao['serie'];
$turma_aluno = strtoupper(trim($dados_redacao['turma']));
$curso_raw   = strtolower(trim($dados_redacao['curso']));

// Normaliza o nome do curso independentemente de acentos ou variações no banco
$curso_aluno = 'informatica';
if (strpos($curso_raw, 'rede') !== false) {
    $curso_aluno = 'redes de computadores';
}

$mapa_colunas_turmas = [
    1 => ['A' => ['informatica' => 't_1ainfo', 'redes de computadores' => 't_1arede'], 'B' => ['informatica' => 't_1binfo', 'redes de computadores' => 't_1brede']],
    2 => ['A' => ['informatica' => 't_2ainfo', 'redes de computadores' => 't_2arede'], 'B' => ['informatica' => 't_2binfo', 'redes de computadores' => 't_2brede']],
    3 => ['A' => ['informatica' => 't_3ainfo', 'redes de computadores' => 't_3arede'], 'B' => ['informatica' => 't_3binfo', 'redes de computadores' => 't_3brede']]
];

$coluna_corretor_turma = $mapa_colunas_turmas[$serie_aluno][$turma_aluno][$curso_aluno] ?? '';

// Verificação de segurança permissiva e robusta
if (empty($coluna_corretor_turma) || !isset($corretor[$coluna_corretor_turma]) || (int)$corretor[$coluna_corretor_turma] !== 1) {
    // Para facilitar o diagnóstico caso ainda ocorra, exibe qual coluna esperava
    $detalhe_erro = "Série: $serie_aluno, Turma: $turma_aluno, Curso: $curso_aluno (Coluna: $coluna_corretor_turma)";
    echo "<script>alert('Acesso negado: Você não possui permissão para corrigir redações desta turma. ($detalhe_erro)'); window.location.href='3-painel-professor.php';</script>";
    exit;
}

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
    
    $redirect_to = $_POST['redirect_to'] ?? '3-painel-professor.php';

    if ($c1 !== false && $c2 !== false && $c3 !== false && $c4 !== false && $c5 !== false) {
        $nota_total = $c1 + $c2 + $c3 + $c4 + $c5;

        // Verifica se já existe correção para esta redação
        $stmt_chk = $conn->prepare("SELECT id_correcao FROM correcoes WHERE id_redacao = ?");
        $stmt_chk->bind_param("i", $id_redacao);
        $stmt_chk->execute();
        $res_chk = $stmt_chk->get_result();

        if ($res_chk->num_rows > 0) {
            $row_c = $res_chk->fetch_assoc();
            $id_correcao = $row_c['id_correcao'];

            $stmt_up = $conn->prepare("UPDATE correcoes SET id_corretor = ?, nota_c1 = ?, nota_c2 = ?, nota_c3 = ?, nota_c4 = ?, nota_c5 = ?, nota_total = ?, comentario_pedagogico = ? WHERE id_redacao = ?");
            $stmt_up->bind_param("iiiiiiisi", $id_corretor, $c1, $c2, $c3, $c4, $c5, $nota_total, $comentario, $id_redacao);
            $stmt_up->execute();
            $stmt_up->close();
        } else {
            $stmt_ins = $conn->prepare("INSERT INTO correcoes (id_redacao, id_corretor, nota_c1, nota_c2, nota_c3, nota_c4, nota_c5, nota_total, comentario_pedagogico) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_ins->bind_param("iiiiiiiis", $id_redacao, $id_corretor, $c1, $c2, $c3, $c4, $c5, $nota_total, $comentario);
            $stmt_ins->execute();
            $stmt_ins->close();
        }
        $stmt_chk->close();

        // Gerencia o favorito vinculado ao corretor logado
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

        header("Location: " . $redirect_to);
        exit;
    } else {
        $mensagem_erro = "Por favor, preencha todas as notas corretamente.";
    }
}

// Verifica status de favorito atual
$stmt_fav = $conn->prepare("SELECT id_favorita FROM favoritas WHERE id_redacao = ? AND id_corretor = ?");
$stmt_fav->bind_param("ii", $id_redacao, $id_corretor);
$stmt_fav->execute();
$is_favorito = $stmt_fav->get_result()->num_rows > 0;
$stmt_fav->close();
?>