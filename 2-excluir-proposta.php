<?php
// 1. Conexão com o banco de dados
$host    = "localhost";
$usuario = "aluno";       
$senha   = "ceep@1234";   
$banco   = "VMI";         

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// 2. Verifica se o ID foi passado na URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_proposta = (int)$_GET['id'];

    // -------------------------------------------------------------
    // ETAPA A: APAGAR OS ARQUIVOS FÍSICOS EM DISCO (UPLOADS)
    // -------------------------------------------------------------

    // A.1 - Busca e apaga o arquivo de apoio da proposta (se houver)
    $sql_prop = "SELECT texto_apoio FROM propostas WHERE id_proposta = ?";
    $stmt_prop = $conn->prepare($sql_prop);
    $stmt_prop->bind_param("i", $id_proposta);
    $stmt_prop->execute();
    $res_prop = $stmt_prop->get_result();

    if ($res_prop && $row_prop = $res_prop->fetch_assoc()) {
        $caminho_apoio = $row_prop['texto_apoio'];
        if (!empty($caminho_apoio) && file_exists($caminho_apoio)) {
            unlink($caminho_apoio);
        }
    }
    $stmt_prop->close();

    // A.2 - Busca e apaga as imagens das redações enviadas para esta proposta
    $sql_red_fotos = "SELECT url_foto FROM redacoes WHERE id_proposta = ?";
    $stmt_fotos = $conn->prepare($sql_red_fotos);
    $stmt_fotos->bind_param("i", $id_proposta);
    $stmt_fotos->execute();
    $res_fotos = $stmt_fotos->get_result();

    if ($res_fotos) {
        while ($foto = $res_fotos->fetch_assoc()) {
            $caminho_foto = $foto['url_foto'];
            if (!empty($caminho_foto) && file_exists($caminho_foto)) {
                unlink($caminho_foto);
            }
        }
    }
    $stmt_fotos->close();

    // -------------------------------------------------------------
    // ETAPA B: EXCLUSÃO DOS REGISTROS NO BANCO DE DADOS
    // -------------------------------------------------------------

    // B.1 - Deleta todas as CORREÇÕES vinculadas às redações desta proposta
    $sql_del_correcoes = "DELETE c FROM correcoes c 
                          INNER JOIN redacoes r ON c.id_redacao = r.id_redacao 
                          WHERE r.id_proposta = ?";
    $stmt_del_cor = $conn->prepare($sql_del_correcoes);
    $stmt_del_cor->bind_param("i", $id_proposta);
    $stmt_del_cor->execute();
    $stmt_del_cor->close();

    // B.2 - Deleta todas as REDAÇÕES enviadas para esta proposta
    $sql_del_redacoes = "DELETE FROM redacoes WHERE id_proposta = ?";
    $stmt_del_red = $conn->prepare($sql_del_redacoes);
    $stmt_del_red->bind_param("i", $id_proposta);
    $stmt_del_red->execute();
    $stmt_del_red->close();

    // B.3 - Deleta a PROPOSTA
    $sql_del_proposta = "DELETE FROM propostas WHERE id_proposta = ?";
    $stmt_del_prop = $conn->prepare($sql_del_proposta);
    $stmt_del_prop->bind_param("i", $id_proposta);

    if ($stmt_del_prop->execute()) {
        $stmt_del_prop->close();
        $conn->close();

        // Redireciona de volta para a lista de propostas
        header("Location: 2-propostas-administrador.php?mensagem=excluido");
        exit();
    } else {
        echo "Erro ao apagar registro da proposta: " . $conn->error;
    }

} else {
    // Redireciona se não informou o ID
    header("Location: 2-propostas-administrador.php");
    exit();
}
?>