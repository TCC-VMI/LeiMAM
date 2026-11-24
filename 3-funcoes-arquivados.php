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

$id_corretor_sessao = (int) $_SESSION['id_corretor'];

// 1. Busca os dados do corretor atualmente logado para obter nome e matrícula
$stmt_logado = $conn->prepare("SELECT nome, matricula FROM corretores WHERE id_corretor = ?");
$stmt_logado->bind_param("i", $id_corretor_sessao);
$stmt_logado->execute();
$dados_sessao = $stmt_logado->get_result()->fetch_assoc();
$stmt_logado->close();

if (!$dados_sessao) {
    session_destroy();
    header("Location: 1-login-professor.html");
    exit;
}

$nome_prof = $dados_sessao['nome'];
$matricula_prof = $dados_sessao['matricula'];

// 2. Busca TODOS os registros de corretores com o mesmo nome e matrícula (agrupando anos diferentes da mesma pessoa)
$stmt_historico = $conn->prepare("SELECT * FROM corretores WHERE nome = ? AND matricula = ?");
$stmt_historico->bind_param("ss", $nome_prof, $matricula_prof);
$stmt_historico->execute();
$res_historico = $stmt_historico->get_result();

// Mapeia quais turmas o professor lecionou em cada ano específico
// Exemplo estrutural: $turmas_por_ano[2025] = ['t_1ainfo' => 1, 't_2ainfo' => 1, ...]
$turmas_por_ano = [];
while ($row = $res_historico->fetch_assoc()) {
    $ano_cadastro = !empty($row['criado_em']) ? (int) date('Y', strtotime($row['criado_em'])) : null;
    if ($ano_cadastro) {
        $turmas_por_ano[$ano_cadastro] = $row;
    }
}
$stmt_historico->close();

// Ano atual dinâmico no fuso de Brasília
$ano_atual = (int) date('Y');
$ano_1 = $ano_atual - 1; // Ex: 2025
$ano_2 = $ano_atual - 2; // Ex: 2024

// Mapeamento fixo das 12 turmas
$todas_as_turmas_template = [
    ['chave' => '1a-info',  'serie' => 1, 'turma' => 'A', 'curso' => 'informatica',          'nome' => '1° A INFO', 'coluna' => 't_1ainfo'],
    ['chave' => '1b-info',  'serie' => 1, 'turma' => 'B', 'curso' => 'informatica',          'nome' => '1° B INFO', 'coluna' => 't_1binfo'],
    ['chave' => '1a-redes', 'serie' => 1, 'turma' => 'A', 'curso' => 'redes de computadores', 'nome' => '1° A REDES', 'coluna' => 't_1arede'],
    ['chave' => '1b-redes', 'serie' => 1, 'turma' => 'B', 'curso' => 'redes de computadores', 'nome' => '1° B REDES', 'coluna' => 't_1brede'],

    ['chave' => '2a-info',  'serie' => 2, 'turma' => 'A', 'curso' => 'informatica',          'nome' => '2° A INFO', 'coluna' => 't_2ainfo'],
    ['chave' => '2b-info',  'serie' => 2, 'turma' => 'B', 'curso' => 'informatica',          'nome' => '2° B INFO', 'coluna' => 't_2binfo'],
    ['chave' => '2a-redes', 'serie' => 2, 'turma' => 'A', 'curso' => 'redes de computadores', 'nome' => '2° A REDES', 'coluna' => 't_2arede'],
    ['chave' => '2b-redes', 'serie' => 2, 'turma' => 'B', 'curso' => 'redes de computadores', 'nome' => '2° B REDES', 'coluna' => 't_2brede'],

    ['chave' => '3a-info',  'serie' => 3, 'turma' => 'A', 'curso' => 'informatica',          'nome' => '3° A INFO', 'coluna' => 't_3ainfo'],
    ['chave' => '3b-info',  'serie' => 3, 'turma' => 'B', 'curso' => 'informatica',          'nome' => '3° B INFO', 'coluna' => 't_3binfo'],
    ['chave' => '3a-redes', 'serie' => 3, 'turma' => 'A', 'curso' => 'redes de computadores', 'nome' => '3° A REDES', 'coluna' => 't_3arede'],
    ['chave' => '3b-redes', 'serie' => 3, 'turma' => 'B', 'curso' => 'redes de computadores', 'nome' => '3° B REDES', 'coluna' => 't_3brede'],
];

/**
 * Função para buscar os dados consolidados de uma turma em um determinado ano
 */
function obterDadosTurmaAno($conn, $t, $ano) {
    $stmt_a = $conn->prepare("SELECT COUNT(*) AS total FROM aluno WHERE serie = ? AND turma = ? AND curso = ? AND ano = ?");
    $stmt_a->bind_param("issi", $t['serie'], $t['turma'], $t['curso'], $ano);
    $stmt_a->execute();
    $alunos_matriculados = $stmt_a->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt_a->close();

    $coluna_prop = $t['coluna'];
    $sql_p = "SELECT COUNT(*) AS total FROM propostas WHERE {$coluna_prop} = 1 AND YEAR(criado_em) = ?";
    $stmt_p = $conn->prepare($sql_p);
    $stmt_p->bind_param("i", $ano);
    $stmt_p->execute();
    $propostas_realizadas = $stmt_p->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt_p->close();

    $sql_m = "SELECT AVG(c.nota_total) AS media 
              FROM aluno a
              JOIN redacoes r ON a.id_aluno = r.id_aluno
              JOIN correcoes c ON r.id_redacao = c.id_redacao
              WHERE a.serie = ? AND a.turma = ? AND a.curso = ? AND a.ano = ?";
    $stmt_m = $conn->prepare($sql_m);
    $stmt_m->bind_param("issi", $t['serie'], $t['turma'], $t['curso'], $ano);
    $stmt_m->execute();
    $media_geral = $stmt_m->get_result()->fetch_assoc()['media'];
    $stmt_m->close();

    return [
        'nome'                 => $t['nome'],
        'chave'                => $t['chave'],
        'ano'                  => $ano,
        'alunos_matriculados'  => $alunos_matriculados,
        'propostas_realizadas' => $propostas_realizadas,
        'media_geral'          => $media_geral ? round($media_geral) : 0
    ];
}

/**
 * Retorna apenas as turmas em que o corretor lecionou naquele ano específico
 */
function obterTurmasArquivadasProfessorPorAno($conn, $template, $turmas_registro_ano, $ano) {
    $lista = [];
    
    // Se não houver registro do professor para este ano específico, retorna vazio
    if (!isset($turmas_registro_ano)) {
        return $lista;
    }

    foreach ($template as $t) {
        $coluna = $t['coluna'];
        // Verifica se o professor estava alocado nesta turma neste ano
        if (isset($turmas_registro_ano[$coluna]) && (int)$turmas_registro_ano[$coluna] === 1) {
            $lista[] = obterDadosTurmaAno($conn, $t, $ano);
        }
    }
    return $lista;
}

$turmas_ordenadas_ano_1 = obterTurmasArquivadasProfessorPorAno($conn, $todas_as_turmas_template, $turmas_por_ano[$ano_1] ?? null, $ano_1);
$turmas_ordenadas_ano_2 = obterTurmasArquivadasProfessorPorAno($conn, $todas_as_turmas_template, $turmas_por_ano[$ano_2] ?? null, $ano_2);
?>