<?php
$usuario = 'aluno';
$senha = 'ceep@1234';
$datebase = 'VMI';
$host = 'localhost';

$mysqli = new mysqli($host, $usuario, $senha, $datebase);

if($mysqli->error) {
 die("Não encotrado no Banco de Dados:" . $mysqli->error);
}
$mysqli->set_charset("utf8");
?>

