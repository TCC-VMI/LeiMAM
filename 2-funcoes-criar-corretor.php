<?php
// ==========================================
// CONFIGURAÇÃO DA CONEXÃO COM O BANCO DE DADOS
// ==========================================
$host = 'localhost';
$dbname = 'VMI'; 
$username = 'aluno'; 
$password = 'ceep@1234'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome      = trim($_POST['nome'] ?? '');
    $matricula = trim($_POST['matricula'] ?? '');
    $senha     = trim($_POST['senha'] ?? ''); 
    
    // Captura dos checkboxes das 12 turmas
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

    if (!empty($nome) && !empty($matricula) && !empty($senha)) {
        $sqlInsert = "INSERT INTO corretores 
                      (nome, matricula, senha_hash, criado_em, t_1ainfo, t_1binfo, t_1arede, t_1brede, t_2ainfo, t_2binfo, t_2arede, t_2brede, t_3ainfo, t_3binfo, t_3arede, t_3brede) 
                      VALUES (:nome, :matricula, :senha, NOW(), :t_1ainfo, :t_1binfo, :t_1arede, :t_1brede, :t_2ainfo, :t_2binfo, :t_2arede, :t_2brede, :t_3ainfo, :t_3binfo, :t_3arede, :t_3brede)";
        
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([
            ':nome'      => $nome,
            ':matricula' => $matricula,
            ':senha'     => $senha,
            ':t_1ainfo'  => $t_1ainfo, ':t_1binfo' => $t_1binfo, ':t_1arede' => $t_1arede, ':t_1brede' => $t_1brede,
            ':t_2ainfo'  => $t_2ainfo, ':t_2binfo' => $t_2binfo, ':t_2arede' => $t_2arede, ':t_2brede' => $t_2brede,
            ':t_3ainfo'  => $t_3ainfo, ':t_3binfo' => $t_3binfo, ':t_3arede' => $t_3arede, ':t_3brede' => $t_3brede
        ]);

        header("Location: 2-corretores.php?status=cadastrado");
        exit;
    } else {
        header("Location: 2-criar-corretor.php?erro=campos_vazios");
        exit;
    }
}
?>