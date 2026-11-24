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

// ==========================================
// LÓGICA: EXCLUIR CORRETOR DO BANCO
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'excluir_corretor') {
    $idExcluir = filter_input(INPUT_POST, 'id_corretor', FILTER_VALIDATE_INT);
    
    if ($idExcluir) {
        $stmtDelete = $pdo->prepare("DELETE FROM corretores WHERE id_corretor = :id");
        $stmtDelete->execute([':id' => $idExcluir]);

        header("Location: 2-corretores.php");
        exit;
    }
}

// ==========================================
// LÓGICA: CONSULTAR COM FILTRAGEM E BUSCA
// ==========================================
$turmaFiltro = $_GET['turma_filtro'] ?? '';
$busca = trim($_GET['busca'] ?? '');

$sql = "SELECT id_corretor, nome, matricula, senha_hash, 
               t_1ainfo, t_1binfo, t_1arede, t_1brede, 
               t_2ainfo, t_2binfo, t_2arede, t_2brede, 
               t_3ainfo, t_3binfo, t_3arede, t_3brede 
        FROM corretores WHERE 1=1";
$params = [];

$mapa_filtros_turmas = [
    '1a-info'  => 't_1ainfo', '1b-info'  => 't_1binfo', '1a-redes' => 't_1arede', '1b-redes' => 't_1brede',
    '2a-info'  => 't_2ainfo', '2b-info'  => 't_2binfo', '2a-redes' => 't_2arede', '2b-redes' => 't_2brede',
    '3a-info'  => 't_3ainfo', '3b-info'  => 't_3binfo', '3a-redes' => 't_3arede', '3b-redes' => 't_3brede',
];

if (!empty($turmaFiltro) && isset($mapa_filtros_turmas[$turmaFiltro])) {
    $coluna_bd = $mapa_filtros_turmas[$turmaFiltro];
    $sql .= " AND {$coluna_bd} = 1";
}

if (!empty($busca)) {
    $sql .= " AND (nome LIKE :busca OR matricula LIKE :busca)";
    $params[':busca'] = "%$busca%";
}

$sql .= " ORDER BY nome ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$corretores = $stmt->fetchAll();

$mapa_turmas_tags = [
    't_1ainfo'  => '1° A INFO',  't_1binfo'  => '1° B INFO',  't_1arede'  => '1° A REDES',  't_1brede'  => '1° B REDES',
    't_2ainfo'  => '2° A INFO',  't_2binfo'  => '2° B INFO',  't_2arede'  => '2° A REDES',  't_2brede'  => '2° B REDES',
    't_3ainfo'  => '3° A INFO',  't_3binfo'  => '3° B INFO',  't_3arede'  => '3° A REDES',  't_3brede'  => '3° B REDES',
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Administrativo | LeiMAM</title>
    <link rel="icon" type="image/png" href="LOGO PNG - LeiMAM.png">
    <link rel="stylesheet" href="cssadm.css">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        .modal-mascara {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none; align-items: center; justify-content: center;
            z-index: 9999;
        }
        .modal-mascara.ativo { display: flex; }
        .modal-conteudo {
            background: #fff; padding: 24px; border-radius: 12px;
            max-width: 420px; width: 90%; text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .modal-icone { color: #ef4444; margin-bottom: 12px; }
        .modal-titulo { font-size: 1.25rem; margin-bottom: 8px; color: #1e293b; }
        .modal-texto { font-size: 0.9rem; color: #64748b; margin-bottom: 20px; line-height: 1.4; }
        .modal-acoes { display: flex; gap: 10px; justify-content: center; }
        .btn-modal-cancelar {
            background: #e2e8f0; color: #475569; border: none; padding: 10px 18px;
            border-radius: 6px; cursor: pointer; font-weight: 600;
        }
        .btn-modal-excluir {
            background: #ef4444; color: #fff; border: none; padding: 10px 18px;
            border-radius: 6px; cursor: pointer; font-weight: 600; text-decoration: none;
        }
        .btn-modal-excluir:hover { background: #dc2626; }
    </style>
</head>
<body>

    <div class="adm-faixa-topo">
        <div class="adm-faixa-bloco-verde"></div>
        <div class="adm-faixa-bloco-amarelo"></div>
        <div class="adm-faixa-bloco-laranja"></div>
    </div>

    <div class="adm-barras-mob">
        <button class="adm-botao-menu" id="admBotaoMenu" onclick="alternarMenu()">☰</button>
        <span class="adm-logo-texto-mob">Lei<span>MAM</span></span>
    </div>

    <div class="adm-mascara-menu" id="admMascaraMenu" onclick="alternarMenu()"></div>

    <div class="adm-conteiner-principal">
        
        <aside class="adm-barra-lateral" id="admBarraLateral">
            <a href="2-painel-administrador.php" class="adm-logo-area">
                <img src="LOGO PNG - LeiMAM.png" alt="Logo LeiMAM" class="adm-logo-imagem">
                <div class="adm-logo-textos-grupo">
                    <span class="adm-logo-texto">Lei<span>MAM</span></span>
                    <span class="adm-subtitulo-logo">Portal Administrativo</span>
                </div>
            </a>

            <nav class="adm-nav-container">
                <ul class="adm-menu-lista">
                    <li><a href="2-painel-administrador.php" class="adm-menu-item-link">Início</a></li>
                    <li><a href="2-propostas-administrador.php" class="adm-menu-item-link">Propostas</a></li>
                    <li><a href="2-calendario-administrador.php" class="adm-menu-item-link">Calendário</a></li>
                    <li><a href="2-corretores.php" class="adm-menu-item-link adm-ativo">Corretores</a></li>
                    <li><a href="2-favoritos.php" class="adm-menu-item-link">Favoritos</a></li>
                    <li><a href="2-arquivados.php" class="adm-menu-item-link">Arquivados</a></li>
                </ul>

                <div class="adm-menu-rodape">
                    <a href="index.html" class="adm-menu-item-link adm-item-sair">
                        <i data-lucide="arrow-left"></i> Sair
                    </a>
                </div>
            </nav>
        </aside>

        <main class="adm-area-conteudo">
            <header class="adm-cabecalho-pagina">
                <div>
                    <h1 class="adm-titulo-pagina">Gerenciamento de Corretores</h1>
                    <p class="adm-subtitulo-pagina">Visualize os corretores cadastrados, suas matrículas e as turmas atribuídas.</p>
                </div>
            </header>

            <form method="GET" action="2-corretores.php" class="adm-barra-acoes-propostas">
                <div class="adm-filtros-grupo">
                    <input type="text" name="busca" class="adm-campo-busca" placeholder="Buscar por nome ou matrícula..." value="<?= htmlspecialchars($busca) ?>">
                    
                    <select name="turma_filtro" class="adm-seletor-filtro" onchange="this.form.submit()">
                        <option value="">Todas as Turmas</option>
                        <optgroup label="1º Ano">
                            <option value="1a-info"  <?= ($turmaFiltro === '1a-info')  ? 'selected' : '' ?>>1° A INFO</option>
                            <option value="1b-info"  <?= ($turmaFiltro === '1b-info')  ? 'selected' : '' ?>>1° B INFO</option>
                            <option value="1a-redes" <?= ($turmaFiltro === '1a-redes') ? 'selected' : '' ?>>1° A REDES</option>
                            <option value="1b-redes" <?= ($turmaFiltro === '1b-redes') ? 'selected' : '' ?>>1° B REDES</option>
                        </optgroup>
                        <optgroup label="2º Ano">
                            <option value="2a-info"  <?= ($turmaFiltro === '2a-info')  ? 'selected' : '' ?>>2° A INFO</option>
                            <option value="2b-info"  <?= ($turmaFiltro === '2b-info')  ? 'selected' : '' ?>>2° B INFO</option>
                            <option value="2a-redes" <?= ($turmaFiltro === '2a-redes') ? 'selected' : '' ?>>2° A REDES</option>
                            <option value="2b-redes" <?= ($turmaFiltro === '2b-redes') ? 'selected' : '' ?>>2° B REDES</option>
                        </optgroup>
                        <optgroup label="3º Ano">
                            <option value="3a-info"  <?= ($turmaFiltro === '3a-info')  ? 'selected' : '' ?>>3° A INFO</option>
                            <option value="3b-info"  <?= ($turmaFiltro === '3b-info')  ? 'selected' : '' ?>>3° B INFO</option>
                            <option value="3a-redes" <?= ($turmaFiltro === '3a-redes') ? 'selected' : '' ?>>3° A REDES</option>
                            <option value="3b-redes" <?= ($turmaFiltro === '3b-redes') ? 'selected' : '' ?>>3° B REDES</option>
                        </optgroup>
                    </select>
                </div>
                
                <a href="2-criar-corretor.php" class="adm-botao-criar-turma" style="white-space: nowrap;">+ Adicionar Corretor</a>
            </form>

            <section class="adm-grid-corretores">
                
                <?php if (empty($corretores)): ?>
                    <div class="adm-lista-vazia" style="grid-column: 1 / -1; text-align: center; padding: 40px 0; color: #64748b;">
                        <p>Nenhum corretor encontrado com os filtros selecionados.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($corretores as $corretor): ?>
                        <div class="adm-balao-corretor">
                            <div class="adm-corretor-topo">
                                <div class="adm-corretor-identificacao">
                                    <h2 class="adm-corretor-nome"><?= htmlspecialchars($corretor['nome']) ?></h2>
                                    <span class="adm-corretor-matricula">Matrícula: <strong><?= htmlspecialchars($corretor['matricula']) ?></strong></span>
                                    <span class="adm-corretor-matricula">Senha: <strong><?= htmlspecialchars($corretor['senha_hash']) ?></strong></span>
                                </div>

                                <form id="form-excluir-<?= $corretor['id_corretor'] ?>" action="2-corretores.php" method="POST">
                                    <input type="hidden" name="acao" value="excluir_corretor">
                                    <input type="hidden" name="id_corretor" value="<?= $corretor['id_corretor'] ?>">
                                    <button type="button" class="adm-btn-excluir-corretor" onclick="confirmarExclusao('<?= $corretor['id_corretor'] ?>', '<?= htmlspecialchars(addslashes($corretor['nome'])) ?>')">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </form>
                            </div>

                            <div class="adm-corretor-corpo">
                                <span class="adm-corretor-label-turmas">Turmas em que atua:</span>
                                <div class="adm-proposta-turmas">
                                    <?php 
                                    $tem_turma = false;
                                    foreach ($mapa_turmas_tags as $coluna => $nome_tag) {
                                        if (!empty($corretor[$coluna])) {
                                            echo '<span class="adm-tag-turma">' . $nome_tag . '</span> ';
                                            $tem_turma = true;
                                        }
                                    }

                                    if (!$tem_turma) {
                                        echo '<span class="adm-tag-turma-vazia">Nenhuma turma atribuída</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </section>
        </main>
    </div>

    <!-- MODAL DE CONFIRMAÇÃO DE EXCLUSÃO DE CORRETOR -->
    <div class="modal-mascara" id="modalExclusaoCorretor">
        <div class="modal-conteudo">
            <div class="modal-icone">
                <i data-lucide="alert-triangle" style="width: 48px; height: 48px;"></i>
            </div>
            <h3 class="modal-titulo">Tem certeza absoluta?</h3>
            <p class="modal-texto">
                Você está prestes a excluir o corretor <strong id="nomeCorretorExcluir"></strong>.<br>
                Esta ação removerá permanentemente o acesso dele do sistema.
            </p>
            <div class="modal-acoes">
                <button type="button" class="btn-modal-cancelar" onclick="fecharModalExclusao()">Cancelar</button>
                <button type="button" class="btn-modal-excluir" onclick="executarExclusao()">Sim, Excluir</button>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function alternarMenu() {
            const barra = document.getElementById('admBarraLateral');
            const mascara = document.getElementById('admMascaraMenu');
            barra.classList.toggle('adm-aberto');
            mascara.classList.toggle('adm-ativo');
        }

        let idFormParaExcluir = null;

        function confirmarExclusao(idCorretor, nomeCorretor) {
            idFormParaExcluir = idCorretor;
            document.getElementById('nomeCorretorExcluir').textContent = '"' + nomeCorretor + '"';
            document.getElementById('modalExclusaoCorretor').classList.add('ativo');
        }

        function fecharModalExclusao() {
            document.getElementById('modalExclusaoCorretor').classList.remove('ativo');
            idFormParaExcluir = null;
        }

        function executarExclusao() {
            if (idFormParaExcluir) {
                document.getElementById('form-excluir-' + idFormParaExcluir).submit();
            }
        }
        function ajustarAlturaMobile() {
            const barraLateral = document.getElementById('admBarraLateral');
            if (barraLateral) {
                // Usa a altura real visível da janela do navegador no celular
                barraLateral.style.height = window.innerHeight + 'px';
            }
            }

            window.addEventListener('resize', ajustarAlturaMobile);
            window.addEventListener('orientationchange', ajustarAlturaMobile);
            // Executa assim que a página carregar
            window.addEventListener('DOMContentLoaded', ajustarAlturaMobile);
    </script>
</body>
</html>