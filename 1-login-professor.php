<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include('conexao.php');

function alertaRedirecionar($mensagem, $url) {
    echo "<script>
        alert('" . addslashes($mensagem) . "');
        window.location.href = '" . $url . "';
    </script>";
    exit;
}

if (isset($_POST['matricula']) && isset($_POST['senha'])) {
    if (empty($_POST['matricula'])) {
        alertaRedirecionar("Preencha sua matrícula", "1-login-professor.html");
    } elseif (empty($_POST['senha'])) {
        alertaRedirecionar("Preencha sua senha", "1-login-professor.html");
    } else {
        $matricula = $mysqli->real_escape_string($_POST['matricula']);
        $senha_informada = $_POST['senha']; // Nota: se armazenado em hash, usar password_verify, caso texto puro comparar direto conforme código anterior do usuário

        // Busca todos os registros para esta matrícula ordenados pelo mais recente (criado_em DESC, id_corretor DESC)
        $sql_code = "SELECT * FROM corretores WHERE matricula = '$matricula' ORDER BY criado_em DESC, id_corretor DESC";
        $sql_query = $mysqli->query($sql_code);

        if (!$sql_query) {
            die("Falha na execução: " . $mysqli->error);
        }

        if ($sql_query->num_rows > 0) {
            $todos_registros = [];
            while ($row = $sql_query->fetch_assoc()) {
                $todos_registros[] = $row;
            }

            // O primeiro elemento é o mais recente
            $mais_recente = $todos_registros[0];
            
            // Verifica o ano atual do sistema (2026)
            $ano_atual = (int)date('Y');
            $ano_criacao_recente = (int)date('Y', strtotime($mais_recente['criado_em']));

            // Verifica se a senha bate com o registro mais recente
            // (Considerando a estrutura anterior onde senha_hash guardava a senha diretamente ou via hash)
            if ($mais_recente['senha_hash'] === $senha_informada) {
                // Valida se o registro mais recente é do ano atual
                if ($ano_criacao_recente !== $ano_atual) {
                    alertaRedirecionar("Acesso negado: Este professor não possui mais acesso à plataforma. Seu registro não faz mais parte da equipe de corretores atual do LeiMAM.", "1-login-professor.html");
                }

                // Login bem-sucedido
                $_SESSION['name'] = $mais_recente['nome'];
                $_SESSION['id_corretor'] = $mais_recente['id_corretor'];
                
                echo "<script>
                    window.location.href = '3-painel-professor.php';
                </script>";
                exit;
            } else {
                // A senha não bate com o mais recente. Vamos verificar se bate com algum registro ANTIGO
                $senha_em_registro_antigo = false;
                for ($i = 1; $i < count($todos_registros); $i++) {
                    if ($todos_registros[$i]['senha_hash'] === $senha_informada) {
                        $senha_em_registro_antigo = true;
                        break;
                    }
                }

                if ($senha_em_registro_antigo) {
                    alertaRedirecionar("Esta é sua senha antiga e não a em vigor.", "1-login-professor.html");
                } else {
                    alertaRedirecionar("Falha ao logar! Matrícula ou senha incorretos.", "1-login-professor.html");
                }
            }
        } else {
            alertaRedirecionar("Falha ao logar! Matrícula ou senha incorretos.", "1-login-professor.html");
        }
    }
}
?>