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

// Ano atual dinâmico (ex: 2026)
$ano_atual = (int) date('Y');

// Definimos os anos arquivados (Ano - 1 e Ano - 2)
$ano_1 = $ano_atual - 1; // Ex: 2025
$ano_2 = $ano_atual - 2; // Ex: 2024

// Limpeza automática de dados com 3 anos ou mais (ex: 2023 ou anteriores em 2026)
$ano_limite = $ano_atual - 3;
$conn->query("DELETE r, c FROM redacoes r LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao JOIN aluno a ON r.id_aluno = a.id_aluno WHERE a.ano <= $ano_limite");
$conn->query("DELETE FROM aluno WHERE ano <= $ano_limite");
$conn->query("DELETE FROM propostas WHERE YEAR(criado_em) <= $ano_limite");

// Mapeamento fixo das 12 turmas na ordem exata solicitada (1º, 2º e 3º anos: A info, B info, A redes, B redes)
$todas_as_turmas_template = [
    // 1ª Série
    ['chave' => '1a-info',  'serie' => 1, 'turma' => 'A', 'curso' => 'informatica',           'nome' => '1° A INFO', 'coluna' => 't_1ainfo'],
    ['chave' => '1b-info',  'serie' => 1, 'turma' => 'B', 'curso' => 'informatica',           'nome' => '1° B INFO', 'coluna' => 't_1binfo'],
    ['chave' => '1a-redes', 'serie' => 1, 'turma' => 'A', 'curso' => 'redes de computadores', 'nome' => '1° A REDES', 'coluna' => 't_1arede'],
    ['chave' => '1b-redes', 'serie' => 1, 'turma' => 'B', 'curso' => 'redes de computadores', 'nome' => '1° B REDES', 'coluna' => 't_1brede'],

    // 2ª Série
    ['chave' => '2a-info',  'serie' => 2, 'turma' => 'A', 'curso' => 'informatica',           'nome' => '2° A INFO', 'coluna' => 't_2ainfo'],
    ['chave' => '2b-info',  'serie' => 2, 'turma' => 'B', 'curso' => 'informatica',           'nome' => '2° B INFO', 'coluna' => 't_2binfo'],
    ['chave' => '2a-redes', 'serie' => 2, 'turma' => 'A', 'curso' => 'redes de computadores', 'nome' => '2° A REDES', 'coluna' => 't_2arede'],
    ['chave' => '2b-redes', 'serie' => 2, 'turma' => 'B', 'curso' => 'redes de computadores', 'nome' => '2° B REDES', 'coluna' => 't_2brede'],

    // 3ª Série
    ['chave' => '3a-info',  'serie' => 3, 'turma' => 'A', 'curso' => 'informatica',           'nome' => '3° A INFO', 'coluna' => 't_3ainfo'],
    ['chave' => '3b-info',  'serie' => 3, 'turma' => 'B', 'curso' => 'informatica',           'nome' => '3° B INFO', 'coluna' => 't_3binfo'],
    ['chave' => '3a-redes', 'serie' => 3, 'turma' => 'A', 'curso' => 'redes de computadores', 'nome' => '3° A REDES', 'coluna' => 't_3arede'],
    ['chave' => '3b-redes', 'serie' => 3, 'turma' => 'B', 'curso' => 'redes de computadores', 'nome' => '3° B REDES', 'coluna' => 't_3brede'],
];

/**
 * Função para buscar os dados consolidados de uma turma em um determinado ano
 */
function obterDadosTurmaAno($conn, $t, $ano) {
    // Conta alunos da turma no ano correspondente
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
        'nome'                => $t['nome'],
        'chave'               => $t['chave'],
        'ano'                 => $ano,
        'alunos_matriculados' => $alunos_matriculados,
        'propostas_realizadas'=> $propostas_realizadas,
        'media_geral'         => $media_geral ? round($media_geral) : 0
    ];
}

function obterTurmasOrdenadasPorAno($conn, $template, $ano) {
    $lista = [];
    foreach ($template as $t) {
        // Garante que todas as 12 turmas apareçam na grade estruturada (mesmo que tenham 0 alunos)
        $lista[] = obterDadosTurmaAno($conn, $t, $ano);
    }
    return $lista;
}

$turmas_ordenadas_ano_1 = obterTurmasOrdenadasPorAno($conn, $todas_as_turmas_template, $ano_1);
$turmas_ordenadas_ano_2 = obterTurmasOrdenadasPorAno($conn, $todas_as_turmas_template, $ano_2);
?>