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

// Captura o ano atual automaticamente
$ano_atual_brasil = (int) date('Y');

// Captura o ID da proposta vindo da URL
$id_proposta = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_proposta <= 0) {
    header("Location: 2-propostas-administrador.php");
    exit();
}

// Busca os dados da proposta
$sql = "SELECT * FROM propostas WHERE id_proposta = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_proposta);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: 2-propostas-administrador.php");
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

// --- MAPEAMENTO DAS 12 TURMAS COM ACESSO E CONTAGEM DE ALUNOS (ANO ATUAL) ---
$total_alunos_acesso = 0;
$turmas_liberadas = [];

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

foreach ($mapa_todas_turmas as $coluna => $dados) {
    if (isset($proposta[$coluna]) && $proposta[$coluna] == 1) {
        $stmt_cnt = $conn->prepare("SELECT COUNT(*) AS total FROM aluno WHERE serie = ? AND turma = ? AND curso = ? AND ano = ?");
        $stmt_cnt->bind_param("issi", $dados['serie'], $dados['turma'], $dados['curso'], $ano_atual_brasil);
        $stmt_cnt->execute();
        $r = $stmt_cnt->get_result()->fetch_assoc();
        $stmt_cnt->close();

        $total_alunos_acesso += $r['total'] ?? 0;
        $turmas_liberadas[] = $dados;
    }
}

// Total de redações enviadas para esta proposta no ano atual
$sql_enviadas = "SELECT COUNT(r.id_redacao) AS total 
                FROM redacoes r 
                JOIN aluno a ON r.id_aluno = a.id_aluno 
                WHERE r.id_proposta = ? AND a.ano = ?";
$stmt_env = $conn->prepare($sql_enviadas);
$stmt_env->bind_param("ii", $id_proposta, $ano_atual_brasil);
$stmt_env->execute();
$redacoes_enviadas = $stmt_env->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_env->close();

// 1. CÁLCULO DAS REDAÇÕES PENDENTES
$redacoes_pendentes = $total_alunos_acesso - $redacoes_enviadas;
if ($redacoes_pendentes < 0) {
    $redacoes_pendentes = 0;
}

// 2. CÁLCULO DAS REDAÇÕES AGUARDANDO CORREÇÃO (Ano Atual)
$sql_aguardando = "SELECT COUNT(r.id_redacao) AS total 
                   FROM redacoes r 
                   JOIN aluno a ON r.id_aluno = a.id_aluno
                   LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao 
                   WHERE r.id_proposta = ? AND a.ano = ? AND c.id_correcao IS NULL";
$stmt_agu = $conn->prepare($sql_aguardando);
$stmt_agu->bind_param("ii", $id_proposta, $ano_atual_brasil);
$stmt_agu->execute();
$aguardando_correcao = $stmt_agu->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_agu->close();

// 3 e 4. CÁLCULO DAS REDAÇÕES CORRIGIDAS E MÉDIA GERAL (Ano Atual)
$sql_corrigidas = "SELECT COUNT(c.id_correcao) AS total, AVG(c.nota_total) AS media 
                   FROM redacoes r 
                   JOIN aluno a ON r.id_aluno = a.id_aluno
                   JOIN correcoes c ON r.id_redacao = c.id_redacao 
                   WHERE r.id_proposta = ? AND a.ano = ?";
$stmt_cor = $conn->prepare($sql_corrigidas);
$stmt_cor->bind_param("ii", $id_proposta, $ano_atual_brasil);
$stmt_cor->execute();
$res_cor = $stmt_cor->get_result()->fetch_assoc();
$redacoes_corrigidas = $res_cor['total'] ?? 0;
$media_proposta      = $res_cor['media'] ? round($res_cor['media']) : 0;
$stmt_cor->close();

// --- CONSULTA PARA A TABELA DE REDAÇÕES ENVIADAS COM FILTROS ---
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
              WHERE r.id_proposta = ? AND a.ano = ?";

$params_types  = "ii";
$params_values = [$id_proposta, $ano_atual_brasil];

if (!empty($busca_aluno)) {
    $sql_lista .= " AND a.nome LIKE ?";
    $params_types .= "s";
    $params_values[] = "%" . $busca_aluno . "%";
}

if (!empty($filtro_turma)) {
    $partes = explode('-', $filtro_turma); // Ex: '1a-info' -> ['1a', 'info']
    if (count($partes) === 2) {
        $serie = (int)substr($partes[0], 0, 1);
        $turma = strtoupper(substr($partes[0], 1, 1));
        $curso = ($partes[1] === 'info') ? 'informatica' : 'redes de computadores';

        $sql_lista .= " AND a.serie = ? AND a.turma = ? AND a.curso = ?";
        $params_types .= "iss";
        $params_values[] = $serie;
        $params_values[] = $turma;
        $params_values[] = $curso;
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