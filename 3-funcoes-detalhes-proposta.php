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

// Captura o ano atual automaticamente
$ano_atual_brasil = (int) date('Y');
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

// Captura o ID da proposta vindo da URL
$id_proposta = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_proposta <= 0) {
    header("Location: 3-propostas-professor.php");
    exit();
}

// Busca os dados da proposta
$sql = "SELECT * FROM propostas WHERE id_proposta = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_proposta);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: 3-propostas-professor.php");
    exit();
}

$proposta = $resultado->fetch_assoc();
$stmt->close();

// --- CÁLCULO DINÂMICO DO STATUS DA PROPOSTA ---
$agora = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
$dt_abertura = !empty($proposta['prazo_abertura']) ? new DateTime($proposta['prazo_abertura'], new DateTimeZone('America/Sao_Paulo')) : null;
$dt_entrega  = !empty($proposta['prazo_entrega'])  ? new DateTime($proposta['prazo_entrega'],  new DateTimeZone('America/Sao_Paulo')) : null;

if ($dt_abertura && $agora < $dt_abertura) {
    $proposta['status'] = 'agendada';
} elseif ($dt_entrega && $agora > $dt_entrega) {
    $proposta['status'] = 'encerrada';
} else {
    $proposta['status'] = 'ativa';
}

// --- MAPEAMENTO DAS 12 TURMAS ---
$mapa_todas_turmas = [
    't_1ainfo' => ['nome' => '1° A INFO',  'serie' => 1, 'turma' => 'A', 'curso' => 'informatica'],
    't_1binfo' => ['nome' => '1° B INFO',  'serie' => 1, 'turma' => 'B', 'curso' => 'informatica'],
    't_1arede' => ['nome' => '1° A REDES', 'serie' => 1, 'turma' => 'A', 'curso' => 'redes de computadores'],
    't_1brede' => ['nome' => '1° B REDES', 'serie' => 1, 'turma' => 'B', 'curso' => 'redes de computadores'],

    't_2ainfo' => ['nome' => '2° A INFO',  'serie' => 2, 'turma' => 'A', 'curso' => 'informatica'],
    't_2binfo' => ['nome' => '2° B INFO',  'serie' => 2, 'turma' => 'B', 'curso' => 'informatica'],
    't_2arede' => ['nome' => '2° A REDES', 'serie' => 2, 'turma' => 'A', 'curso' => 'redes de computadores'],
    't_2brede' => ['nome' => '2° B REDES', 'serie' => 2, 'turma' => 'B', 'curso' => 'redes de computadores'],

    't_3ainfo' => ['nome' => '3° A INFO',  'serie' => 3, 'turma' => 'A', 'curso' => 'informatica'],
    't_3binfo' => ['nome' => '3° B INFO',  'serie' => 3, 'turma' => 'B', 'curso' => 'informatica'],
    't_3arede' => ['nome' => '3° A REDES', 'serie' => 3, 'turma' => 'A', 'curso' => 'redes de computadores'],
    't_3brede' => ['nome' => '3° B REDES', 'serie' => 3, 'turma' => 'B', 'curso' => 'redes de computadores'],
];

// Turmas permitidas para o professor (onde a proposta está ativa E o professor leciona)
$total_alunos_acesso = 0;
$turmas_liberadas = [];
$condicoes_turmas_sql = [];
$params_turmas_types = "";
$params_turmas_values = [];

foreach ($mapa_todas_turmas as $coluna => $dados) {
    // Verifica se a proposta é para esta turma E se o corretor leciona nela
    if (isset($proposta[$coluna]) && $proposta[$coluna] == 1 && isset($corretor[$coluna]) && (int)$corretor[$coluna] === 1) {
        
        $stmt_cnt = $conn->prepare("SELECT COUNT(*) AS total FROM aluno WHERE serie = ? AND turma = ? AND curso = ? AND ano = ?");
        $stmt_cnt->bind_param("issi", $dados['serie'], $dados['turma'], $dados['curso'], $ano_atual_brasil);
        $stmt_cnt->execute();
        $r = $stmt_cnt->get_result()->fetch_assoc();
        $stmt_cnt->close();

        $total_alunos_acesso += $r['total'] ?? 0;
        $turmas_liberadas[] = $dados;

        // Monta condição para filtrar alunos das turmas do professor
        $condicoes_turmas_sql[] = "(a.serie = ? AND a.turma = ? AND a.curso = ?)";
        $params_turmas_types .= "iss";
        $params_turmas_values[] = $dados['serie'];
        $params_turmas_values[] = $dados['turma'];
        $params_turmas_values[] = $dados['curso'];
    }
}

// Se o professor não tem acesso a nenhuma turma desta proposta, restringe o escopo de forma segura
$filtro_turmas_sql = "";
if (!empty($condicoes_turmas_sql)) {
    $filtro_turmas_sql = " AND (" . implode(" OR ", $condicoes_turmas_sql) . ")";
} else {
    $filtro_turmas_sql = " AND 1 = 0"; // Nenhuma turma permitida
}

// Total de redações enviadas para esta proposta nas turmas do professor (ano atual)
$sql_enviadas = "SELECT COUNT(r.id_redacao) AS total 
                 FROM redacoes r 
                 JOIN aluno a ON r.id_aluno = a.id_aluno 
                 WHERE r.id_proposta = ? AND a.ano = ?" . $filtro_turmas_sql;
$stmt_env = $conn->prepare($sql_enviadas);
$bind_types_env = "ii" . $params_turmas_types;
$bind_values_env = array_merge([$id_proposta, $ano_atual_brasil], $params_turmas_values);
$stmt_env->bind_param($bind_types_env, ...$bind_values_env);
$stmt_env->execute();
$redacoes_enviadas = $stmt_env->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_env->close();

// 1. CÁLCULO DAS REDAÇÕES PENDENTES (apenas turmas do professor)
$redacoes_pendentes = $total_alunos_acesso - $redacoes_enviadas;
if ($redacoes_pendentes < 0) {
    $redacoes_pendentes = 0;
}

// 2. CÁLCULO DAS REDAÇÕES AGUARDANDO CORREÇÃO (apenas turmas do professor)
$sql_aguardando = "SELECT COUNT(r.id_redacao) AS total 
                   FROM redacoes r 
                   JOIN aluno a ON r.id_aluno = a.id_aluno
                   LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao 
                   WHERE r.id_proposta = ? AND a.ano = ? AND c.id_correcao IS NULL" . $filtro_turmas_sql;
$stmt_agu = $conn->prepare($sql_aguardando);
$stmt_agu->bind_param($bind_types_env, ...$bind_values_env);
$stmt_agu->execute();
$aguardando_correcao = $stmt_agu->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_agu->close();

// 3. CÁLCULO DAS REDAÇÕES CORRIGIDAS (apenas turmas do professor)
$sql_corrigidas = "SELECT COUNT(c.id_correcao) AS total 
                   FROM redacoes r 
                   JOIN aluno a ON r.id_aluno = a.id_aluno
                   JOIN correcoes c ON r.id_redacao = c.id_redacao 
                   WHERE r.id_proposta = ? AND a.ano = ?" . $filtro_turmas_sql;
$stmt_cor = $conn->prepare($sql_corrigidas);
$stmt_cor->bind_param($bind_types_env, ...$bind_values_env);
$stmt_cor->execute();
$redacoes_corrigidas = $stmt_cor->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_cor->close();

// 4. MÉDIA GERAL DA PROPOSTA (Considerando todos os dados globais conforme solicitado)
$sql_media_geral = "SELECT AVG(c.nota_total) AS media 
                    FROM redacoes r 
                    JOIN aluno a ON r.id_aluno = a.id_aluno
                    JOIN correcoes c ON r.id_redacao = c.id_redacao 
                    WHERE r.id_proposta = ? AND a.ano = ?";
$stmt_med = $conn->prepare($sql_media_geral);
$stmt_med->bind_param("ii", $id_proposta, $ano_atual_brasil);
$stmt_med->execute();
$media_proposta = $stmt_med->get_result()->fetch_assoc()['media'];
$media_proposta = $media_proposta ? round($media_proposta) : 0;
$stmt_med->close();

// --- CONSULTA PARA A TABELA DE REDAÇÕES ENVIADAS COM FILTROS (Restrito às turmas do professor) ---
$busca_aluno   = isset($_GET['busca_aluno']) ? trim($_GET['busca_aluno']) : '';
$filtro_turma  = isset($_GET['filtro_turma']) ? trim($_GET['filtro_turma']) : '';
$filtro_status = isset($_GET['filtro_status']) ? trim($_GET['filtro_status']) : '';

$sql_lista = "SELECT 
                r.id_redacao, 
                r.enviado_em, 
                a.nome AS nome_aluno, 
                CONCAT(a.serie, '° ', a.turma, ' ', UPPER(a.curso)) AS turma_formatada,
                c.id_correcao, 
                c.nota_total
              FROM redacoes r
              JOIN aluno a ON r.id_aluno = a.id_aluno
              LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao
              WHERE r.id_proposta = ? AND a.ano = ?" . $filtro_turmas_sql;

$params_types  = "ii" . $params_turmas_types;
$params_values = array_merge([$id_proposta, $ano_atual_brasil], $params_turmas_values);

if (!empty($busca_aluno)) {
    $sql_lista .= " AND a.nome LIKE ?";
    $params_types .= "s";
    $params_values[] = "%" . $busca_aluno . "%";
}

if (!empty($filtro_turma)) {
    $partes = explode('-', $filtro_turma); 
    if (count($partes) === 2) {
        $serie_f = (int)substr($partes[0], 0, 1);
        $turma_f = strtoupper(substr($partes[0], 1, 1));
        $curso_f = ($partes[1] === 'info') ? 'informatica' : 'redes de computadores';

        $sql_lista .= " AND a.serie = ? AND a.turma = ? AND a.curso = ?";
        $params_types .= "iss";
        $params_values[] = $serie_f;
        $params_values[] = $turma_f;
        $params_values[] = $curso_f;
    }
}

if ($filtro_status === 'corrigida') {
    $sql_lista .= " AND c.id_correcao IS NOT NULL";
} elseif ($filtro_status === 'pendente') {
    $sql_lista .= " AND c.id_correcao IS NULL";
}

$sql_lista .= " ORDER BY r.enviado_em DESC";

$stmt_lista = $conn->prepare($sql_lista);
$stmt_lista->bind_param($params_types, ...$params_values);
$stmt_lista->execute();
$lista_redacoes = $stmt_lista->get_result();
?>