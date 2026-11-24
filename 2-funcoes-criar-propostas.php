<?php
// Certifique-se de que a conexão ($conn) está ativa aqui ou incluída corretamente
require_once '2-funcoes-propostas-administrador.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo         = trim($_POST['tituloProposta'] ?? '');
    $prazo_abertura = !empty($_POST['prazoAbertura']) ? $_POST['prazoAbertura'] : null;
    $prazo_entrega  = !empty($_POST['prazoProposta']) ? $_POST['prazoProposta'] : null;
    $texto_apoio    = trim($_POST['textoApoio'] ?? '');

    // Captura dos 12 checkboxes das turmas
    $t_1ainfo = isset($_POST['t_1ainfo']) ? 1 : 0;
    $t_1binfo = isset($_POST['t_1binfo']) ? 1 : 0;
    $t_1arede = isset($_POST['t_1arede']) ? 1 : 0;
    $t_1brede = isset($_POST['t_1brede']) ? 1 : 0;

    $t_2ainfo = isset($_POST['t_2ainfo']) ? 1 : 0;
    $t_2binfo = isset($_POST['t_2binfo']) ? 1 : 0;
    $t_2arede = isset($_POST['t_2arede']) ? 1 : 0;
    $t_2brede = isset($_POST['t_2brede']) ? 1 : 0;

    $t_3ainfo = isset($_POST['t_3ainfo']) ? 1 : 0;
    $t_3binfo = isset($_POST['t_3binfo']) ? 1 : 0;
    $t_3arede = isset($_POST['t_3arede']) ? 1 : 0;
    $t_3brede = isset($_POST['t_3brede']) ? 1 : 0;

    // Upload de arquivo de apoio
    $caminho_arquivo = null;
    if (isset($_FILES['arquivoPDF']) && $_FILES['arquivoPDF']['error'] === UPLOAD_ERR_OK) {
        $pasta_destino = "uploads/";
        if (!is_dir($pasta_destino)) {
            mkdir($pasta_destino, 0777, true);
        }
        $extensao = strtolower(pathinfo($_FILES['arquivoPDF']['name'], PATHINFO_EXTENSION));
        $nome_unico = uniqid("proposta_") . "." . $extensao;
        $caminho_completo = $pasta_destino . $nome_unico;

        if (move_uploaded_file($_FILES['arquivoPDF']['tmp_name'], $caminho_completo)) {
            $caminho_arquivo = $caminho_completo;
        }
    }

    // Ajustado para usar $texto_apoio tanto para instrucoes quanto para texto_apoio (ou ajuste conforme sua tabela)
    $sql = "INSERT INTO propostas 
            (titulo, prazo_abertura, prazo_entrega, 
             t_1ainfo, t_1binfo, t_1arede, t_1brede,
             t_2ainfo, t_2binfo, t_2arede, t_2brede,
             t_3ainfo, t_3binfo, t_3arede, t_3brede,
             instrucoes, texto_apoio, criado_em) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        // 3 strings + 12 inteiros + 2 strings = sssiiiiiiiiiiiiss (17 tipos)
        $stmt->bind_param(
            "sssiiiiiiiiiiiiss", 
            $titulo, 
            $prazo_abertura, 
            $prazo_entrega, 
            $t_1ainfo, 
            $t_1binfo, 
            $t_1arede, 
            $t_1brede,
            $t_2ainfo, 
            $t_2binfo, 
            $t_2arede, 
            $t_2brede,
            $t_3ainfo, 
            $t_3binfo, 
            $t_3arede, 
            $t_3brede,
            $texto_apoio, 
            $caminho_arquivo
        );

        if ($stmt->execute()) {
            header("Location: 2-propostas-administrador.php?status=sucesso");
            exit();
        } else {
            echo "Erro ao salvar no banco de dados: " . htmlspecialchars($stmt->error);
        }

        $stmt->close();
    } else {
        echo "Erro na preparação da query: " . htmlspecialchars($conn->error);
    }
    
    if (isset($conn)) {
        $conn->close();
    }
}
?>