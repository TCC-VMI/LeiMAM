<?php
require_once '4-funcoes-painel-aluno.php';
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
        <button class="adm-botao-menu" id="admBotaoMenu" onclick="alternarMenu()">
            ☰
        </button>
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
                    <li><a href="4-painel-aluno.php" class="adm-menu-item-link adm-ativo">Início</a></li>
                    <li><a href="4-propostas-aluno.php" class="adm-menu-item-link">Propostas</a></li>
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
                <h1 class="adm-titulo-pagina">Olá, <?php echo htmlspecialchars($nome_aluno); ?>!</h1>
                <p class="adm-subtitulo-pagina">Turma: <strong><?php echo $serie_atual . '° ' . strtoupper($turma_atual) . ' ' . ucfirst(strtolower($aluno_info['curso'])); ?></strong> | Ano Letivo: <strong><?php echo $ano_atual; ?></strong></p>            </header>

            <!-- 4 CARTÕES DE INDICADORES -->
            <section class="adm-grid-indicadores"> 
                <div class="adm-cartao-indicador">
                    <div class="adm-indicador-titulo">Sua Média Geral</div>
                    <div class="adm-indicador-valor"><?php echo $aluno_media_geral; ?></div>
                </div>
                <div class="adm-cartao-indicador">
                    <div class="adm-indicador-titulo">Redações Pendentes</div>
                    <div class="adm-indicador-valor"><?php echo $aluno_redacoes_pendentes; ?></div>
                </div>
                <div class="adm-cartao-indicador">
                    <div class="adm-indicador-titulo">Aguardando Correção</div>
                    <div class="adm-indicador-valor"><?php echo $aluno_aguardando_correcao; ?></div>
                </div>
                <div class="adm-cartao-indicador">
                    <div class="adm-indicador-titulo">Redações Corrigidas</div>
                    <div class="adm-indicador-valor"><?php echo $aluno_redacoes_corrigidas; ?></div>
                </div>
            </section>

            <!-- DOIS BALÕES GRANDES: COMPETÊNCIAS E HISTÓRICO DE TEMAS -->
            <div class="adm-grid-duplo">
                <!-- Média por Competência (ENEM) -->
                <section class="adm-cartao-secao">
                    <h2 class="adm-titulo-secao">Sua Média por Competência (ENEM)</h2>
                    <div class="adm-lista-competencias">
                        <div class="adm-competencia-item">
                            <div class="adm-competencia-info">
                                <span>C1 - Norma Culta</span>
                                <span><?php echo $aluno_competencias['c1']; ?> / 200</span>
                            </div>
                            <div class="adm-barra-progresso-fundo">
                                <div class="adm-barra-progresso-preenchimento" style="width: <?php echo ($aluno_competencias['c1'] / 200) * 100; ?>%;"></div>
                            </div>
                        </div>

                        <div class="adm-competencia-item">
                            <div class="adm-competencia-info">
                                <span>C2 - Compreensão do Tema</span>
                                <span><?php echo $aluno_competencias['c2']; ?> / 200</span>
                            </div>
                            <div class="adm-barra-progresso-fundo">
                                <div class="adm-barra-progresso-preenchimento" style="width: <?php echo ($aluno_competencias['c2'] / 200) * 100; ?>%;"></div>
                            </div>
                        </div>

                        <div class="adm-competencia-item">
                            <div class="adm-competencia-info">
                                <span>C3 - Argumentação</span>
                                <span><?php echo $aluno_competencias['c3']; ?> / 200</span>
                            </div>
                            <div class="adm-barra-progresso-fundo">
                                <div class="adm-barra-progresso-preenchimento" style="width: <?php echo ($aluno_competencias['c3'] / 200) * 100; ?>%;"></div>
                            </div>
                        </div>

                        <div class="adm-competencia-item">
                            <div class="adm-competencia-info">
                                <span>C4 - Coesão</span>
                                <span><?php echo $aluno_competencias['c4']; ?> / 200</span>
                            </div>
                            <div class="adm-barra-progresso-fundo">
                                <div class="adm-barra-progresso-preenchimento" style="width: <?php echo ($aluno_competencias['c4'] / 200) * 100; ?>%;"></div>
                            </div>
                        </div>

                        <div class="adm-competencia-item">
                            <div class="adm-competencia-info">
                                <span>C5 - Proposta de Intervenção</span>
                                <span><?php echo $aluno_competencias['c5']; ?> / 200</span>
                            </div>
                            <div class="adm-barra-progresso-fundo">
                                <div class="adm-barra-progresso-preenchimento" style="width: <?php echo ($aluno_competencias['c5'] / 200) * 100; ?>%;"></div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Desempenho / Histórico de Temas Realizados -->
                <section class="adm-cartao-secao">
                    <h2 class="adm-titulo-secao">Desempenho por Temas Realizados</h2>
                    <table class="adm-tabela-alunos">
                        <thead>
                            <tr>
                                <th>Proposta</th>
                                <th>Data de Envio</th>
                                <th>Nota Final</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($historico_temas)): ?>
                                <?php foreach ($historico_temas as $item): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($item['tema']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($item['data']); ?></td>
                                        <td><strong><?php echo $item['nota']; ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; color: var(--adm-cor-texto-secundario); padding: 20px 0;">Ainda não enviou redações para correção.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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