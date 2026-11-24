<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Sao_Paulo');

$host    = "localhost";
$usuario = "aluno";      
$senha   = "ceep@1234";  
$banco   = "VMI"; 

$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados.");
}

// Recupera o ID do aluno autenticado
$id_aluno_atual = isset($_SESSION['id_aluno']) ? $_SESSION['id_aluno'] : 1; 

// Valida se o ID da proposta foi passado via GET
$id_proposta = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_proposta <= 0) {
    header("Location: 4-propostas-aluno.php");
    exit;
}

// --- ROTA DE DOWNLOAD DA FOLHA OFICIAL (Pasta tecnico) ---
if (isset($_GET['acao']) && $_GET['acao'] === 'baixar_folha') {
    $caminho_folha = __DIR__ . '/tecnico/FOLHA DE REDAÇÃO OFICIAL - CEEP.pdf';
    if (file_exists($caminho_folha)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="FOLHA_DE_REDACAO_OFICIAL_CEEP.pdf"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($caminho_folha));
        readfile($caminho_folha);
        exit;
    } else {
        $_SESSION['erro_upload'] = "Arquivo da folha de redação oficial não encontrado no servidor.";
    }
}

// Busca os dados do aluno para validar turma/curso
$stmt_aluno = $conn->prepare("SELECT * FROM aluno WHERE id_aluno = ?");
$stmt_aluno->bind_param("i", $id_aluno_atual);
$stmt_aluno->execute();
$aluno_info = $stmt_aluno->get_result()->fetch_assoc();
$stmt_aluno->close();

if (!$aluno_info) {
    die("Estudante não encontrado.");
}

$serie_atual = $aluno_info['serie'];
$turma_atual = strtolower($aluno_info['turma']);
$termo_curso = (strpos(strtolower($aluno_info['curso']), 'rede') !== false) ? 'rede' : 'info';
$coluna_turma_proposta = "t_" . $serie_atual . $turma_atual . $termo_curso;

// Busca os dados da proposta
$stmt_prop = $conn->prepare("SELECT * FROM propostas WHERE id_proposta = ? AND `$coluna_turma_proposta` = 1");
$stmt_prop->bind_param("i", $id_proposta);
$stmt_prop->execute();
$proposta = $stmt_prop->get_result()->fetch_assoc();
$stmt_prop->close();

if (!$proposta) {
    die("Proposta não encontrada ou não liberada para a sua turma.");
}

// Verifica se o prazo de entrega expirou
$agora = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
$dt_entrega = !empty($proposta['prazo_entrega']) ? new DateTime($proposta['prazo_entrega'], new DateTimeZone('America/Sao_Paulo')) : null;
$prazo_encerrado = ($dt_entrega && $agora > $dt_entrega);

// Busca envio anterior do aluno para esta proposta
$stmt_envio = $conn->prepare("SELECT r.*, c.id_correcao, c.nota_total FROM redacoes r LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao WHERE r.id_aluno = ? AND r.id_proposta = ?");
$stmt_envio->bind_param("ii", $id_aluno_atual, $id_proposta);
$stmt_envio->execute();
$envio_atual = $stmt_envio->get_result()->fetch_assoc();
$stmt_envio->close();

$ja_corrigida = ($envio_atual && !empty($envio_atual['id_correcao']));

// --- PROCESSAMENTO DO ENVIO DE REDAÇÃO (POST) ---
$mensagem_retorno = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo_redacao'])) {
    if ($prazo_encerrado) {
        $mensagem_retorno = "O prazo para envio desta proposta já foi encerrado.";
    } elseif ($ja_corrigida) {
        $mensagem_retorno = "Esta redação já foi corrigida e não pode ser reenviada.";
    } else {
        $arquivo = $_FILES['arquivo_redacao'];
        
        if ($arquivo['error'] === UPLOAD_ERR_OK) {
            $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];

            if (in_array($extensao, $permitidas)) {
                $pasta_destino = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
                if (!is_dir($pasta_destino)) {
                    mkdir($pasta_destino, 0777, true);
                }

                $novo_nome_arquivo = 'redacao_aluno_' . $id_aluno_atual . '_prop_' . $id_proposta . '_' . time() . '.' . $extensao;
                $caminho_completo = $pasta_destino . $novo_nome_arquivo;
                $url_banco = 'uploads/' . $novo_nome_arquivo;

                if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
                    
                    // Se já havia um envio anterior (não corrigido), apaga o arquivo físico antigo e o registro
                    if ($envio_atual) {
                        $arquivo_antigo = __DIR__ . '/' . $envio_atual['url_foto'];
                        if (file_exists($arquivo_antigo) && !is_dir($arquivo_antigo)) {
                            unlink($arquivo_antigo);
                        }

                        $stmt_del = $conn->prepare("DELETE FROM redacoes WHERE id_redacao = ?");
                        $stmt_del->bind_param("i", $envio_atual['id_redacao']);
                        $stmt_del->execute();
                        $stmt_del->close();
                    }

                    // Pega a data e hora atual EXATA com base no fuso horário do PHP ('America/Sao_Paulo')
                    $data_hora_brasilia = date('Y-m-d H:i:s');

                    // Insere o novo registro na tabela redacoes usando a variável do PHP em vez do NOW() do MySQL
                    $stmt_ins = $conn->prepare("INSERT INTO redacoes (id_aluno, id_proposta, url_foto, enviado_em) VALUES (?, ?, ?, ?)");
                    $stmt_ins->bind_param("iiss", $id_aluno_atual, $id_proposta, $url_banco, $data_hora_brasilia);
                    
                    if ($stmt_ins->execute()) {
                        header("Location: 4-detalhes-proposta.php?id=" . $id_proposta . "&sucesso=1");
                        exit;
                    } else {
                        $mensagem_retorno = "Erro ao registrar o envio no banco de dados: " . $stmt_ins->error;
                    }
                    $stmt_ins->close();

                } else {
                    $mensagem_retorno = "Erro ao mover o arquivo enviado para a pasta 'uploads'. Verifique se a pasta existe na raiz.";
                }
            } else {
                $mensagem_retorno = "Formato de arquivo inválido. Permitido apenas: JPG, PNG, WEBP e PDF.";
            }
        } else {
            if ($arquivo['error'] === UPLOAD_ERR_INI_SIZE || $arquivo['error'] === UPLOAD_ERR_FORM_SIZE) {
                $mensagem_retorno = "O arquivo enviado é muito grande para o limite permitido pelo servidor.";
            } else {
                $mensagem_retorno = "Erro no envio do arquivo (Código: " . $arquivo['error'] . ").";
            }
        }
    }
}

// Recarrega o status do envio após processamento
$stmt_envio = $conn->prepare("SELECT r.*, c.id_correcao, c.nota_total FROM redacoes r LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao WHERE r.id_aluno = ? AND r.id_proposta = ?");
$stmt_envio->bind_param("ii", $id_aluno_atual, $id_proposta);
$stmt_envio->execute();
$envio_atual = $stmt_envio->get_result()->fetch_assoc();
$stmt_envio->close();

$ja_corrigida = ($envio_atual && !empty($envio_atual['id_correcao']));
?>