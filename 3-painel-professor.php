<?php
require_once '3-funcoes-painel-professor.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal do Corretor | LeiMAM</title>
    <link rel="icon" type="image/png" href="LOGO PNG - LeiMAM.png">
    <link rel="stylesheet" href="cssprof.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

    <div class="adm-faixa-topo">
        <div class="adm-faixa-bloco-verde"></div>
        <div class="adm-faixa-bloco-amarelo"></div>
        <div class="adm-faixa-bloco-laranja"></div>
    </div>

    <div class="adm-barras-mob">
        <button class="adm-botao-menu" id="admBotaoMenu" onclick="alternarMenu()">
            ☰
        </button>
        <span class="adm-logo-texto-mob">Lei<span>MAM</span></span>
    </div>

    <div class="adm-mascara-menu" id="admMascaraMenu" onclick="alternarMenu()"></div>

    <div class="adm-conteiner-principal">
        
        <aside class="adm-barra-lateral" id="admBarraLateral">
            <a href="3-painel-professor.php" class="adm-logo-area">
                <img src="LOGO PNG - LeiMAM.png" alt="Logo LeiMAM" class="adm-logo-imagem">
                <div class="adm-logo-textos-grupo">
                    <span class="adm-logo-texto">Lei<span>MAM</span></span>
                    <span class="adm-subtitulo-logo">Portal do Corretor</span>
                </div>
            </a>

            <nav class="adm-nav-container">
                <ul class="adm-menu-lista">
                    <li><a href="3-painel-professor.php" class="adm-menu-item-link adm-ativo">Início</a></li>
                    <li><a href="3-propostas-professor.php" class="adm-menu-item-link">Propostas</a></li>
                    <li><a href="3-calendario-professor.php" class="adm-menu-item-link">Calendário</a></li>
                    <li><a href="3-favoritos.php" class="adm-menu-item-link">Favoritos</a></li>
                    <li><a href="3-arquivados.php" class="adm-menu-item-link">Arquivados</a></li>
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
                <h1 class="adm-titulo-pagina">Olá, <?php echo htmlspecialchars($corretor['nome']); ?>!</h1>
                <p class="adm-subtitulo-pagina">Visão geral do desempenho e redações das suas turmas cadastradas no ano letivo.</p>
            </header>

            <section class="adm-grid-indicadores">
                <div class="adm-cartao-indicador">
                    <div class="adm-indicador-titulo">Média Geral das Turmas</div>
                    <div class="adm-indicador-valor"><?php echo $media_geral; ?></div>
                </div>
                <div class="adm-cartao-indicador">
                    <div class="adm-indicador-titulo">Redações Pendentes</div>
                    <div class="adm-indicador-valor"><?php echo $redacoes_pendentes; ?></div>
                </div>
                <div class="adm-cartao-indicador">
                    <div class="adm-indicador-titulo">Aguardando Correção</div>
                    <div class="adm-indicador-valor"><?php echo $aguardando_correc; ?></div>
                </div>
                <div class="adm-cartao-indicador">
                    <div class="adm-indicador-titulo">Redações Corrigidas</div>
                    <div class="adm-indicador-valor"><?php echo $redacoes_corrigida; ?></div>
                </div>
            </section>

            <section>
                <div class="adm-cabecalho-secao">
                    <h2 class="adm-titulo-secao">Suas Turmas Cadastradas - <?php echo $ano_atual; ?></h2>
                </div>

                <div class="adm-grid-turmas">
                    <?php if (!empty($turmas_dados)): ?>
                        <?php foreach ($turmas_dados as $chave => $turma): ?>
                            <a href="3-turma.php?id=<?php echo $chave; ?>" class="adm-balao-turma">
                                <div class="adm-turma-nome"><?php echo $turma['nome_exibicao']; ?> - <?php echo $ano_atual; ?></div>
                                <div class="adm-turma-dados" style="margin-top: 10px;">
                                    <div class="adm-turma-dado-item"><span>Alunos:</span> <strong><?php echo $turma['total_alunos']; ?></strong></div>
                                    <div class="adm-turma-dado-item"><span>Média:</span> <strong><?php echo $turma['stats']['media']; ?></strong></div>
                                    <div class="adm-turma-dado-item"><span>Pendentes:</span> <strong><?php echo $turma['stats']['pendentes']; ?></strong></div>
                                    <div class="adm-turma-dado-item"><span>Corrigidas:</span> <strong><?php echo $turma['stats']['corrigidas']; ?></strong></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: var(--adm-cor-texto-secundario); padding: 15px 0;">Nenhuma turma cadastrada para este corretor no ano atual.</p>
                    <?php endif; ?>
                </div>
            </section>

            <div class="adm-grid-duplo">
                <section class="adm-cartao-secao">
                    <h2 class="adm-titulo-secao">Média por Competência (ENEM)</h2>
                    <div class="adm-lista-competencias">
                        <div class="adm-competencia-item">
                            <div class="adm-competencia-info">
                                <span>C1 - Norma Culta</span>
                                <span><?php echo $competencias['c1']; ?> / 200</span>
                            </div>
                            <div class="adm-barra-progresso-fundo">
                                <div class="adm-barra-progresso-preenchimento" style="width: <?php echo ($competencias['c1'] / 200) * 100; ?>%;"></div>
                            </div>
                        </div>

                        <div class="adm-competencia-item">
                            <div class="adm-competencia-info">
                                <span>C2 - Compreensão do Tema</span>
                                <span><?php echo $competencias['c2']; ?> / 200</span>
                            </div>
                            <div class="adm-barra-progresso-fundo">
                                <div class="adm-barra-progresso-preenchimento" style="width: <?php echo ($competencias['c2'] / 200) * 100; ?>%;"></div>
                            </div>
                        </div>

                        <div class="adm-competencia-item">
                            <div class="adm-competencia-info">
                                <span>C3 - Argumentação</span>
                                <span><?php echo $competencias['c3']; ?> / 200</span>
                            </div>
                            <div class="adm-barra-progresso-fundo">
                                <div class="adm-barra-progresso-preenchimento" style="width: <?php echo ($competencias['c3'] / 200) * 100; ?>%;"></div>
                            </div>
                        </div>

                        <div class="adm-competencia-item">
                            <div class="adm-competencia-info">
                                <span>C4 - Coesão</span>
                                <span><?php echo $competencias['c4']; ?> / 200</span>
                            </div>
                            <div class="adm-barra-progresso-fundo">
                                <div class="adm-barra-progresso-preenchimento" style="width: <?php echo ($competencias['c4'] / 200) * 100; ?>%;"></div>
                            </div>
                        </div>

                        <div class="adm-competencia-item">
                            <div class="adm-competencia-info">
                                <span>C5 - Proposta de Intervenção</span>
                                <span><?php echo $competencias['c5']; ?> / 200</span>
                            </div>
                            <div class="adm-barra-progresso-fundo">
                                <div class="adm-barra-progresso-preenchimento" style="width: <?php echo ($competencias['c5'] / 200) * 100; ?>%;"></div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="adm-cartao-secao">
                    <h2 class="adm-titulo-secao">Top 5 Alunos - <?php echo $ano_atual; ?></h2>
                    <table class="adm-tabela-alunos">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Turma</th>
                                <th>Média</th>
                                <th>Enviadas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($top_alunos)): ?>
                                <?php foreach ($top_alunos as $aluno): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($aluno['nome']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($aluno['turma_formatada']); ?></td>
                                        <td><?php echo $aluno['media']; ?></td>
                                        <td><?php echo $aluno['enviadas']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--adm-cor-texto-secundario); padding: 15px 0;">Nenhuma redação corrigida neste ano letivo.</td>
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
                barraLateral.style.height = window.innerHeight + 'px';
            }
        }

        window.addEventListener('resize', ajustarAlturaMobile);
        window.addEventListener('orientationchange', ajustarAlturaMobile);
        window.addEventListener('DOMContentLoaded', ajustarAlturaMobile);
    </script>
</body>
</html>
<?php $conn->close(); ?>