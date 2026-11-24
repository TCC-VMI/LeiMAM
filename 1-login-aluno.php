<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// --- CONFIGURAÇÕES DO SEU BANCO DE DADOS (MySQL) ---
$db_host = 'localhost';
$db_user = 'aluno';         
$db_pass = 'ceep@1234';     
$db_name = 'VMI'; 

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

// --- CONFIGURAÇÕES DO SERVIDOR LDAP ---
define('LDAP_SERVER', '10.0.10.20');
define('LDAP_DOMAIN', '@ceep.plg');
define('LDAP_BASE_DN', 'OU=Alunos,OU=CEEPPLG,DC=ceep, DC=plg');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matriculaInput = isset($_POST['matricula']) ? trim($_POST['matricula']) : '';
    $senhaInput = isset($_POST['senha']) ? trim($_POST['senha']) : '';
    $anoAtual = date('Y'); // Ex: 2026

    if (empty($matriculaInput) || empty($senhaInput)) {
        echo "<script>alert('Por favor, preencha todos os campos.'); window.history.back();</script>";
        exit;
    }

    $loginSucesso = false;
    $id_aluno_db = null;
    $nome_completo = $matriculaInput;
    $turma = "Não definida";
    $serie = "Não definida";
    $curso = "Não definido";

    // =========================================================================
    // ETAPA 1: VERIFICA SE EXISTE REGISTRO PARA O ANO ATUAL
    // =========================================================================
    $stmt = $pdo->prepare("SELECT * FROM aluno WHERE matricula = :matricula AND ano = :ano LIMIT 1");
    $stmt->execute([':matricula' => $matriculaInput, ':ano' => $anoAtual]);
    $alunoAnoAtual = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($alunoAnoAtual) {
        // Encontrou no ano atual, valida a senha
        if ($senhaInput === $alunoAnoAtual['senha_hash']) {
            $loginSucesso = true;
            $id_aluno_db  = $alunoAnoAtual['id_aluno']; // ID único deste registro
            $nome_completo = $alunoAnoAtual['nome'];
            $turma         = $alunoAnoAtual['turma'];
            $serie         = $alunoAnoAtual['serie'];
            $curso         = $alunoAnoAtual['curso'];

            // Atualiza o último login
            $updateStmt = $pdo->prepare("UPDATE aluno SET ultimo_login = NOW() WHERE id_aluno = :id");
            $updateStmt->execute([':id' => $id_aluno_db]);
        } else {
            echo "<script>alert('Senha incorreta para o ano letivo atual.'); window.history.back();</script>";
            exit;
        }
    } else {
        // =========================================================================
        // ETAPA 2: NÃO ESTÁ NO ANO ATUAL. VERIFICA SE EXISTE EM ANOS ANTERIORES
        // =========================================================================
        $stmtAnt = $pdo->prepare("SELECT * FROM aluno WHERE matricula = :matricula ORDER BY ano DESC LIMIT 1");
        $stmtAnt->execute([':matricula' => $matriculaInput]);
        $alunoAnterior = $stmtAnt->fetch(PDO::FETCH_ASSOC);

        if ($alunoAnterior) {
            // O aluno existe no banco, mas em anos passados -> Já saiu da escola!
            echo "<script>alert('Este aluno não está mais cadastrado no banco de dados do CEEP Professora Lourdinha Guerra.'); window.history.back();</script>";
            exit;
        }

        // =========================================================================
        // ETAPA 3: NUNCA ESTEVE NO BANCO LOCAL, TENTA O SERVIDOR LDAP
        // =========================================================================
        $ldap_conn = @ldap_connect(LDAP_SERVER);

        if ($ldap_conn) {
            ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

            $user_dn = $matriculaInput . LDAP_DOMAIN;
            $bind = @ldap_bind($ldap_conn, $user_dn, $senhaInput);

            if ($bind) {
                $filter = "(samaccountname=$matriculaInput)";
                $attributes = ["displayname", "memberof"];

                $search = @ldap_search($ldap_conn, LDAP_BASE_DN, $filter, $attributes);
                $info = @ldap_get_entries($ldap_conn, $search);

                $rawSerie = "";
                $rawCurso = "";
                $rawTurma = "";

                if ($info && $info['count'] > 0) {
                    if (isset($info[0]['displayname'][0])) {
                        $nome_completo = $info[0]['displayname'][0];
                    }

                    if (isset($info[0]['memberof'])) {
                        for ($i = 0; $i < $info[0]['memberof']['count']; $i++) {
                            $grupo = $info[0]['memberof'][$i];
                            
                            if (strpos($grupo, 'OU=') !== false) {
                                $partes = explode(',', $grupo);
                                foreach ($partes as $parte) {
                                    if (strpos(strtoupper($parte), 'OU=') === 0) {
                                        $valorOu = trim(substr($parte, 3));
                                        
                                        if (stripos($valorOu, 'Série') !== false || stripos($valorOu, 'Ano') !== false) {
                                            $rawSerie = $valorOu;
                                        }
                                        elseif ($valorOu !== 'Alunos' && $valorOu !== 'CEEPPLG' && $valorOu != $rawSerie) {
                                            $rawCurso = $valorOu;
                                        }
                                    }
                                }
                            }

                            if (strpos($grupo, 'CN=') === 0) {
                                $cnGrupo = explode(',', $grupo)[0];
                                $rawTurma = trim(substr($cnGrupo, 3));
                            }
                        }
                    }
                }

                @ldap_unbind($ldap_conn);

                // Formatações
                $nome_completo = mb_convert_case(strtolower($nome_completo), MB_CASE_TITLE, "UTF-8");

                if (stripos($rawSerie, '3') !== false) { $serie = "3"; }
                elseif (stripos($rawSerie, '2') !== false) { $serie = "2"; }
                elseif (stripos($rawSerie, '1') !== false) { $serie = "1"; }
                else { $serie = $rawSerie; }

                $cursoLower = strtolower($rawCurso);
                if (strpos($cursoLower, 'redes') !== false) {
                    $curso = "Redes de Computadores";
                } elseif (strpos($cursoLower, 'info') !== false) {
                    $curso = "Informática";
                } else {
                    $curso = mb_convert_case(strtolower($rawCurso), MB_CASE_TITLE, "UTF-8");
                }

                if (preg_match('/_([A-Za-z])_/i', $rawTurma, $matches)) {
                    $turma = strtoupper($matches[1]);
                } else {
                    $turma = mb_strtoupper($rawTurma, "UTF-8");
                }

                // Insere o novo aluno no banco local para o ano atual
                $insertStmt = $pdo->prepare("
                    INSERT INTO aluno (matricula, senha_hash, nome, serie, turma, curso, ano, ultimo_login) 
                    VALUES (:matricula, :senha_hash, :nome, :serie, :turma, :curso, :ano, NOW())
                ");

                $insertStmt->execute([
                    ':matricula'  => $matriculaInput,
                    ':senha_hash' => $senhaInput, 
                    ':nome'       => $nome_completo,
                    ':serie'      => $serie,
                    ':turma'      => $turma,
                    ':curso'      => $curso,
                    ':ano'        => $anoAtual
                ]);

                // Pega o id_aluno gerado para este novo registro
                $id_aluno_db = $pdo->lastInsertId();
                $loginSucesso = true;
            } else {
                @ldap_unbind($ldap_conn);
                echo "<script>alert('Matrícula ou senha incorretos.'); window.history.back();</script>";
                exit;
            }
        } else {
            echo "<script>alert('Matrícula ou senha incorretos.'); window.history.back();</script>";
            exit;
        }
    }

    // =========================================================================
    // ETAPA 4: FINALIZAÇÃO DA SESSÃO COM O ID CORRETO DO ANO ATUAL
    // =========================================================================
    if ($loginSucesso) {
        $_SESSION['logado']        = true;
        $_SESSION['id_aluno']      = $id_aluno_db; // ID único e exclusivo do registro do ano atual
        $_SESSION['usuario_id']    = $matriculaInput;
        $_SESSION['usuario_nome']  = $nome_completo;
        $_SESSION['usuario_turma'] = $turma;
        $_SESSION['usuario_curso'] = $curso;

        header("Location: 4-painel-aluno.php");
        exit;
    }
}
?>