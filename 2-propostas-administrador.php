<?php
require_once '2-funcoes-propostas-administrador.php';

$busca  = isset($_GET['busca'])  ? trim($_GET['busca'])  : '';
$turma  = isset($_GET['turma'])  ? trim($_GET['turma'])  : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

$propostas = buscarPropostasFiltradas($conn, $busca, $turma, $status);

// Mapeamento das colunas da proposta para os nomes das tags visuais
$mapa_turmas_tags = [
    't_1ainfo'  => '1° A INFO',
    't_1binfo'  => '1° B INFO',
    't_1arede'  => '1° A REDES',
    't_1brede'  => '1° B REDES',
    't_2ainfo'  => '2° A INFO',
    't_2binfo'  => '2° B INFO',
    't_2arede'  => '2° A REDES',
    't_2brede'  => '2° B REDES',
    't_3ainfo'  => '3° A INFO',
    't_3binfo'  => '3° B INFO',
    't_3arede'  => '3° A REDES',
    't_3brede'  => '3° B REDES',
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
                    <li><a href="2-propostas-administrador.php" class="adm-menu-item-link adm-ativo">Propostas</a></li>
                    <li><a href="2-calendario-administrador.php" class="adm-menu-item-link">Calendário</a></li>
                    <li><a href="2-corretores.php" class="adm-menu-item-link">Corretores</a></li>
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
                    <h1 class="adm-titulo-pagina">Gerenciamento de Propostas</h1>
                    <p class="adm-subtitulo-pagina">Acompanhe as propostas ativas por turma ou crie novos temas de redação.</p>
                </div>
            </header>

            <div class="adm-barra-acoes-propostas">
                <form method="GET" action="2-propostas-administrador.php" class="adm-filtros-grupo" style="display: flex; gap: 10px; width: 100%;">
                    <input type="text" name="busca" class="adm-campo-busca" placeholder="Buscar por tema..." value="<?php echo htmlspecialchars($busca); ?>">
                    
                    <select name="turma" class="adm-seletor-filtro" onchange="this.form.submit()">
                        <option value="">Todas as Turmas</option>
                        <optgroup label="1º Ano">
                            <option value="1a-info"  <?php if ($turma == '1a-info')  echo 'selected'; ?>>1° A INFO</option>
                            <option value="1b-info"  <?php if ($turma == '1b-info')  echo 'selected'; ?>>1° B INFO</option>
                            <option value="1a-redes" <?php if ($turma == '1a-redes') echo 'selected'; ?>>1° A REDES</option>
                            <option value="1b-redes" <?php if ($turma == '1b-redes') echo 'selected'; ?>>1° B REDES</option>
                        </optgroup>
                        <optgroup label="2º Ano">
                            <option value="2a-info"  <?php if ($turma == '2a-info')  echo 'selected'; ?>>2° A INFO</option>
                            <option value="2b-info"  <?php if ($turma == '2b-info')  echo 'selected'; ?>>2° B INFO</option>
                            <option value="2a-redes" <?php if ($turma == '2a-redes') echo 'selected'; ?>>2° A REDES</option>
                            <option value="2b-redes" <?php if ($turma == '2b-redes') echo 'selected'; ?>>2° B REDES</option>
                        </optgroup>
                        <optgroup label="3º Ano">
                            <option value="3a-info"  <?php if ($turma == '3a-info')  echo 'selected'; ?>>3° A INFO</option>
                            <option value="3b-info"  <?php if ($turma == '3b-info')  echo 'selected'; ?>>3° B INFO</option>
                            <option value="3a-redes" <?php if ($turma == '3a-redes') echo 'selected'; ?>>3° A REDES</option>
                            <option value="3b-redes" <?php if ($turma == '3b-redes') echo 'selected'; ?>>3° B REDES</option>
                        </optgroup>
                    </select>

                    <select name="status" class="adm-seletor-filtro" onchange="this.form.submit()">
                        <option value="">Todos os Status</option>
                        <option value="ativa"    <?php if ($status == 'ativa')    echo 'selected'; ?>>Ativa</option>
                        <option value="agendada" <?php if ($status == 'agendada') echo 'selected'; ?>>Agendada</option>
                        <option value="encerrada"<?php if ($status == 'encerrada')echo 'selected'; ?>>Encerrada</option>
                    </select>
                </form>
                
                <a href="2-criar-propostas.html" class="adm-botao-criar-turma" style="white-space: nowrap;">+ Criar Nova Proposta</a>
            </div>

            <section class="adm-grid-propostas">
                
                <?php if (!empty($propostas)): ?>
                    <?php foreach ($propostas as $proposta): ?>
                        <a href="2-detalhes-proposta.php?id=<?php echo $proposta['id_proposta']; ?>" class="adm-balao-proposta">
                            <div class="adm-proposta-cabecalho">
                                <span class="adm-badge-status adm-badge-<?php echo $proposta['status']; ?>"><?php echo $proposta['status_nome']; ?></span>
                                <span class="adm-proposta-prazo"><?php echo $proposta['prazo_texto']; ?></span>
                            </div>
                            <h2 class="adm-proposta-titulo"><?php echo htmlspecialchars($proposta['titulo']); ?></h2>
                            
                            <div class="adm-proposta-turmas">
                                <?php 
                                foreach ($mapa_turmas_tags as $coluna => $nome_tag) {
                                    if (isset($proposta[$coluna]) && $proposta[$coluna] == 1) {
                                        echo '<span class="adm-tag-turma">' . $nome_tag . '</span> ';
                                    }
                                }
                                ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="grid-column: 1 / -1; color: #64748b; text-align: center; padding: 40px 0;">
                        Nenhuma proposta encontrada com os filtros selecionados.
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