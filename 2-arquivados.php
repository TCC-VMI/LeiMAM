<?php
require_once '2-funcoes-arquivados.php';
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
                        <li><a href="2-propostas-administrador.php" class="adm-menu-item-link">Propostas</a></li>
                        <li><a href="2-calendario-administrador.php" class="adm-menu-item-link">Calendário</a></li>
                        <li><a href="2-corretores.php" class="adm-menu-item-link">Corretores</a></li>
                        <li><a href="2-favoritos.php" class="adm-menu-item-link">Favoritos</a></li>
                        <li><a href="2-arquivados.php" class="adm-menu-item-link adm-ativo">Arquivados</a></li>
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
                        <h1 class="adm-titulo-pagina">Turmas Arquivadas</h1>
                        <p class="adm-subtitulo-pagina">Consulte o histórico dos anos letivos anteriores (<strong><?php echo $ano_1; ?></strong> e <strong><?php echo $ano_2; ?></strong>).</p>
                    </div>
                </header>

                <div class="adm-container-anos-arquivados">

                    <!-- GRUPO ANO - 1 (Ex: 2025) -->
                    <section class="adm-secao-ano-arquivado">
                        <div class="adm-cabecalho-ano">
                            <h2 class="adm-titulo-ano">Ano Letivo <?php echo $ano_1; ?></h2>
                            <span class="adm-qtd-turmas-ano">12 Turmas Organizadas</span>
                        </div>

                        <div class="adm-grid-turmas" style="margin-top: 15px;">
                            <?php 
                            // Renderiza as 12 turmas ordenadas para o Ano 1
                            foreach ($turmas_ordenadas_ano_1 as $t): 
                            ?>
                                <a href="2-turma-arquivada.php?id=<?php echo $t['chave']; ?>&ano=<?php echo $ano_1; ?>" class="adm-balao-turma">
                                    <div class="adm-turma-nome"><?php echo $t['nome'] . ' - ' . $ano_1; ?></div>
                                    <div class="adm-turma-dados">
                                        <div class="adm-turma-dado-item"><span>Alunos:</span> <strong><?php echo $t['alunos_matriculados']; ?></strong></div>
                                        <div class="adm-turma-dado-item"><span>Propostas:</span> <strong><?php echo $t['propostas_realizadas']; ?></strong></div>
                                        <div class="adm-turma-dado-item" style="grid-column: span 2;"><span>Média Geral:</span> <strong><?php echo $t['media_geral']; ?></strong></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- GRUPO ANO - 2 (Ex: 2024) -->
                    <section class="adm-secao-ano-arquivado" style="margin-top: 40px;">
                        <div class="adm-cabecalho-ano">
                            <h2 class="adm-titulo-ano">Ano Letivo <?php echo $ano_2; ?></h2>
                            <span class="adm-qtd-turmas-ano">12 Turmas Organizadas</span>
                        </div>

                        <div class="adm-grid-turmas" style="margin-top: 15px;">
                            <?php 
                            // Renderiza as 12 turmas ordenadas para o Ano 2
                            foreach ($turmas_ordenadas_ano_2 as $t): 
                            ?>
                                <a href="2-turma-arquivada.php?id=<?php echo $t['chave']; ?>&ano=<?php echo $ano_2; ?>" class="adm-balao-turma">
                                    <div class="adm-turma-nome"><?php echo $t['nome'] . ' - ' . $ano_2; ?></div>
                                    <div class="adm-turma-dados">
                                        <div class="adm-turma-dado-item"><span>Alunos:</span> <strong><?php echo $t['alunos_matriculados']; ?></strong></div>
                                        <div class="adm-turma-dado-item"><span>Propostas:</span> <strong><?php echo $t['propostas_realizadas']; ?></strong></div>
                                        <div class="adm-turma-dado-item" style="grid-column: span 2;"><span>Média Geral:</span> <strong><?php echo $t['media_geral']; ?></strong></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>

                </div>
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