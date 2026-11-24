 <?php
// Exibe erros para facilitar caso algo dê errado
error_reporting(E_ALL);
ini_set('display_errors', 1);




include('conexao.php');


// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {


// Pega os valores enviados pelos inputs do HTML e protege contra caracteres maliciosos
$titulo = $mysqli->real_escape_string($_POST['titulo']);
$descricao = $mysqli->real_escape_string($_POST['descricao']);
$hora_limite = $mysqli->real_escape_string($_POST['hora_limite']);
$tipo = $mysqli->real_escape_string($_POST['tipo']);
$data_evento = $mysqli->real_escape_string($_POST['data_evento']);


// Tratamento para campos de data/hora vazios não quebrarem o banco
$campo_hora = empty($hora_limite) ? "NULL" : "'$hora_limite'";
$campo_data = empty($data_evento) ? "NULL" : "'$data_evento'";


// Comando SQL para INSERIR os dados na tabela 'Calendário'
$sql = "INSERT INTO Calendário (titulo, descricao, hora_limite, tipo, data_evento)
VALUES ('$titulo', '$descricao', $campo_hora, '$tipo', $campo_data)";


// Executa o comando no banco
if ($mysqli->query($sql) === TRUE) {


header("Location: 2-painel-administrador.php");
exit;
} else {
echo "Erro ao cadastrar o evento: " . $mysqli->error;
}


} else {
echo "Acesso inválido.";
}
?>

