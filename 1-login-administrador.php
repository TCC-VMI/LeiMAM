
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include('conexao.php');

if (isset($_POST['usuario']) && isset($_POST['senha'])) {
     
    if (empty($_POST['usuario'])) {
 
         echo "Preencha sua credencial";

     } elseif (empty($_POST['senha'])) {
 
         echo "Preencha sua senha";
  } else {

 $usuario = $mysqli->real_escape_string($_POST['usuario']);
 $senha = $mysqli->real_escape_string($_POST['senha']);
 
        $sql_code = "SELECT * FROM admin WHERE credencial = '$usuario'
AND senha_hash = '$senha'";


     $sql_query = $mysqli->query($sql_code);
 
        if (!$sql_query) {
             die("Falha na execução: " . $mysqli->error);
 
             }

            $quantidade = $sql_query->num_rows;
        
            if ($quantidade == 1) {

        $usuario = $sql_query->fetch_assoc();
 
        $_SESSION['name'] = $usuario['Nome'];

        header("Location: 2-painel-administrador.php");
 exit;

 } else {
 echo "<script>alert('Falha ao logar! usuario ou senha incorretos.'); window.history.back();</script>";
 }
 }
}
?>