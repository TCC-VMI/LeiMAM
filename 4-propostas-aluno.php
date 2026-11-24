<?php
require_once '4-funcoes-propostas-aluno.php';

$busca         = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$filtro_status = isset($_GET['status']) ? trim($_GET['status']) : '';

$propostas = buscarPropostasAluno($conn, $id_aluno_atual, $coluna_turma_aluno, $busca, $filtro_status);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Estudantil | LeiMAM</title>
    <link rel="icon" type="image/png" href="LOGO PNG - LeiMAM.png">
    <link rel="stylesheet" href="css-aluno.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

    <div class="adm-faixa-topo">
        <div class="adm-faixa-bloco-verde"></div>
        <div class="adm-faixa-bloco-amarelo"></div>
        <div class="adm-faixa-bloco-laranja"></div>
    </div>

    <!-- TOPO MOBILE -->
    <div class="adm-barras-mob">
        <button class="adm-botao-menu" id="admBotaoMenu" onclick="alternarMenu()">☰</button>
        <span class="adm-logo-texto-mob">Lei<span>MAM</span></span>
    </div>

    <div class="adm-mascara-menu" id="admMascaraMenu" onclick="alternarMenu()"></div>

    <div class="adm-conteiner-principal">
        
        <!-- BARRA LATERAL -->
        <aside class="adm-barra-lateral" id="admBarraLateral">
            <a href="4-painel-aluno.php" class="adm-logo-area">
                <img src="LOGO PNG - LeiMAM.png" alt="Logo LeiMAM" class="adm-logo-imagem">
                <div class="adm-logo-textos-grupo">
                    <span class="adm-logo-texto">Lei<span>MAM</span></span>
                    <span class="adm-subtitulo-logo">Portal Estudantil</span>
                </div>
            </a>

            <nav class="adm-nav-container">
                <ul class="adm-menu-lista">
                    <li><a href="4-painel-aluno.php" class="adm-menu-item-link">Início</a></li>
                    <li><a href="4-propostas-aluno.php" class="adm-menu-item-link adm-ativo">Propostas</a></li>
                    <li><a href="4-calendario-aluno.php" class="adm-menu-item-link">Calendário</a></li>
                    <li><a href="4-arquivados-aluno.php" class="adm-menu-item-link">Arquivados</a></li>
                </ul>

                <div class="adm-menu-rodape">
                    <a href="index.html" class="adm-menu-item-link adm-item-sair">
                        <i data-lucide="arrow-left"></i> Sair
                    </a>
                </div>
            </nav>
        </aside>

        <!-- CONTEÚDO PRINCIPAL -->
        <main class="adm-area-conteudo">
            <header class="adm-cabecalho-pagina">
                <div>
                    <h1 class="adm-titulo-pagina">Propostas de Redação</h1>
                    <p class="adm-subtitulo-pagina">Confira os temas disponíveis para sua turma e envie suas redações.</p>
                </div>
            </header>

            <!-- BARRA DE PESQUISA E FILTROS -->
            <div class="adm-barra-acoes-propostas">
                <form method="GET" action="4-propostas-aluno.php" class="adm-filtros-grupo" style="display: flex; gap: 10px; width: 100%;">
                    <input type="text" name="busca" class="adm-campo-busca" placeholder="Buscar por tema..." value="<?php echo htmlspecialchars($busca); ?>">
                    
                    <select name="status" class="adm-seletor-filtro" onchange="this.form.submit()">
                        <option value="">Todos os Status</option>
                        <option value="pendente"  <?php if ($filtro_status == 'pendente')  echo 'selected'; ?>>Pendentes</option>
                        <option value="enviada"   <?php if ($filtro_status == 'enviada')   echo 'selected'; ?>>Aguardando Correção</option>
                        <option value="corrigida" <?php if ($filtro_status == 'corrigida') echo 'selected'; ?>>Corrigidas</option>
                    </select>
                </form>
            </div>

            <!-- GRID DE PROPOSTAS -->
            <section class="adm-grid-propostas">
                <?php if (!empty($propostas)): ?>
                    <?php foreach ($propostas as $proposta): ?>
                        <a href="4-detalhes-proposta.php?id=<?php echo $proposta['id_proposta']; ?>" class="adm-balao-proposta">
                            <div class="adm-proposta-cabecalho">
                                <?php if ($proposta['status_aluno'] === 'pendente'): ?>
                                    <span class="adm-badge-status adm-badge-pendente">Pendente</span>
                                <?php elseif ($proposta['status_aluno'] === 'aguardando'): ?>
                                    <span class="adm-badge-status adm-badge-aguardando">Aguardando Correção</span>
                                <?php elseif ($proposta['status_aluno'] === 'corrigida'): ?>
                                    <span class="adm-badge-status adm-badge-corrigida">Corrigida</span>
                                <?php elseif ($proposta['status_aluno'] === 'encerrada'): ?>
                                    <span class="adm-badge-status adm-badge-encerrada">Encerrada</span>
                                <?php endif; ?>

                                <span class="adm-proposta-prazo"><?php echo $proposta['prazo_texto']; ?></span>
                            </div>

                            <h2 class="adm-proposta-titulo"><?php echo htmlspecialchars($proposta['titulo']); ?></h2>
                            
                            <div class="adm-proposta-turmas">
                                <span class="adm-tag-turma"><?php echo htmlspecialchars($serie_aluno . '° ' . strtoupper($turma_aluno) . ' - ' . mb_strtoupper($aluno_info['curso'], 'UTF-8')); ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="grid-column: 1 / -1; color: var(--adm-cor-texto-secundario); text-align: center; padding: 40px 0;">
                        Nenhuma proposta encontrada para sua turma no momento.
                    </p>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script>
        lucide.createIcons();

        function alternarMenu() {
            const barra = document.getElementById('admBarraLateral');
            const mascara = document.getElementById('admMascaraMenu');
            barra.classList.toggle('adm-aberto');
            mascara.classList.toggle('adm-ativo');
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
<?php $conn->close(); ?>