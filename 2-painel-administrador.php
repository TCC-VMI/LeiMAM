<?php
require_once '2-funcoes-painel-administrador.php';
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
        <button class="adm-botao-menu" id="admBotaoMenu" onclick="alternarMenu()">
            ☰
        </button>
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
                    <li><a href="2-painel-administrador.php" class="adm-menu-item-link adm-ativo">Início</a></li>
                    <li><a href="2-propostas-administrador.php" class="adm-menu-item-link">Propostas</a></li>
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
                <h1 class="adm-titulo-pagina">Painel Geral (<?php echo $ano_atual; ?>)</h1>
                <p class="adm-subtitulo-pagina">Visão geral do desempenho de todas as turmas e redações do ano letivo.</p>
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
                    <h2 class="adm-titulo-secao">Turmas Cadastradas - <?php echo $ano_atual; ?></h2>
                </div>

                <!-- FILEIRA 1: 1º ANO -->
                <div class="adm-grid-turmas">
                    <a href="2-turma.php?id=1a-info" class="adm-balao-turma">
                        <div class="adm-turma-nome">1° A INFO - <?php echo $ano_atual; ?></div>
                        <div class="adm-turma-dados">
                            <div class="adm-turma-dado-item"><span>Alunos:</span> <strong><?php echo $turmas_dados['1a_info']['total_alunos']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Média:</span> <strong><?php echo $turmas_dados['1a_info']['stats']['media']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Pendentes:</span> <strong><?php echo $turmas_dados['1a_info']['stats']['pendentes']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Corrigidas:</span> <strong><?php echo $turmas_dados['1a_info']['stats']['corrigidas']; ?></strong></div>
                        </div>
                    </a>

                    <a href="2-turma.php?id=1b-info" class="adm-balao-turma">
                        <div class="adm-turma-nome">1° B INFO - <?php echo $ano_atual; ?></div>
                        <div class="adm-turma-dados">
                            <div class="adm-turma-dado-item"><span>Alunos:</span> <strong><?php echo $turmas_dados['1b_info']['total_alunos']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Média:</span> <strong><?php echo $turmas_dados['1b_info']['stats']['media']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Pendentes:</span> <strong><?php echo $turmas_dados['1b_info']['stats']['pendentes']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Corrigidas:</span> <strong><?php echo $turmas_dados['1b_info']['stats']['corrigidas']; ?></strong></div>
                        </div>
                    </a>

                    <a href="2-turma.php?id=1a-redes" class="adm-balao-turma">
                        <div class="adm-turma-nome">1° A REDES - <?php echo $ano_atual; ?></div>
                        <div class="adm-turma-dados">
                            <div class="adm-turma-dado-item"><span>Alunos:</span> <strong><?php echo $turmas_dados['1a_redes']['total_alunos']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Média:</span> <strong><?php echo $turmas_dados['1a_redes']['stats']['media']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Pendentes:</span> <strong><?php echo $turmas_dados['1a_redes']['stats']['pendentes']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Corrigidas:</span> <strong><?php echo $turmas_dados['1a_redes']['stats']['corrigidas']; ?></strong></div>
                        </div>
                    </a>

                    <a href="2-turma.php?id=1b-redes" class="adm-balao-turma">
                        <div class="adm-turma-nome">1° B REDES - <?php echo $ano_atual; ?></div>
                        <div class="adm-turma-dados">
                            <div class="adm-turma-dado-item"><span>Alunos:</span> <strong><?php echo $turmas_dados['1b_redes']['total_alunos']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Média:</span> <strong><?php echo $turmas_dados['1b_redes']['stats']['media']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Pendentes:</span> <strong><?php echo $turmas_dados['1b_redes']['stats']['pendentes']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Corrigidas:</span> <strong><?php echo $turmas_dados['1b_redes']['stats']['corrigidas']; ?></strong></div>
                        </div>
                    </a>
                </div>

                <!-- FILEIRA 2: 2º ANO -->
                <div class="adm-grid-turmas">
                    <a href="2-turma.php?id=2a-info" class="adm-balao-turma">
                        <div class="adm-turma-nome">2° A INFO - <?php echo $ano_atual; ?></div>
                        <div class="adm-turma-dados">
                            <div class="adm-turma-dado-item"><span>Alunos:</span> <strong><?php echo $turmas_dados['2a_info']['total_alunos']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Média:</span> <strong><?php echo $turmas_dados['2a_info']['stats']['media']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Pendentes:</span> <strong><?php echo $turmas_dados['2a_info']['stats']['pendentes']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Corrigidas:</span> <strong><?php echo $turmas_dados['2a_info']['stats']['corrigidas']; ?></strong></div>
                        </div>
                    </a>

                    <a href="2-turma.php?id=2b-info" class="adm-balao-turma">
                        <div class="adm-turma-nome">2° B INFO - <?php echo $ano_atual; ?></div>
                        <div class="adm-turma-dados">
                            <div class="adm-turma-dado-item"><span>Alunos:</span> <strong><?php echo $turmas_dados['2b_info']['total_alunos']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Média:</span> <strong><?php echo $turmas_dados['2b_info']['stats']['media']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Pendentes:</span> <strong><?php echo $turmas_dados['2b_info']['stats']['pendentes']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Corrigidas:</span> <strong><?php echo $turmas_dados['2b_info']['stats']['corrigidas']; ?></strong></div>
                        </div>
                    </a>

                    <a href="2-turma.php?id=2a-redes" class="adm-balao-turma">
                        <div class="adm-turma-nome">2° A REDES - <?php echo $ano_atual; ?></div>
                        <div class="adm-turma-dados">
                            <div class="adm-turma-dado-item"><span>Alunos:</span> <strong><?php echo $turmas_dados['2a_redes']['total_alunos']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Média:</span> <strong><?php echo $turmas_dados['2a_redes']['stats']['media']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Pendentes:</span> <strong><?php echo $turmas_dados['2a_redes']['stats']['pendentes']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Corrigidas:</span> <strong><?php echo $turmas_dados['2a_redes']['stats']['corrigidas']; ?></strong></div>
                        </div>
                    </a>

                    <a href="2-turma.php?id=2b-redes" class="adm-balao-turma">
                        <div class="adm-turma-nome">2° B REDES - <?php echo $ano_atual; ?></div>
                        <div class="adm-turma-dados">
                            <div class="adm-turma-dado-item"><span>Alunos:</span> <strong><?php echo $turmas_dados['2b_redes']['total_alunos']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Média:</span> <strong><?php echo $turmas_dados['2b_redes']['stats']['media']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Pendentes:</span> <strong><?php echo $turmas_dados['2b_redes']['stats']['pendentes']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Corrigidas:</span> <strong><?php echo $turmas_dados['2b_redes']['stats']['corrigidas']; ?></strong></div>
                        </div>
                    </a>
                </div>

                <!-- FILEIRA 3: 3º ANO -->
                <div class="adm-grid-turmas">
                    <a href="2-turma.php?id=3a-info" class="adm-balao-turma">
                        <div class="adm-turma-nome">3° A INFO - <?php echo $ano_atual; ?></div>
                        <div class="adm-turma-dados">
                            <div class="adm-turma-dado-item"><span>Alunos:</span> <strong><?php echo $turmas_dados['3a_info']['total_alunos']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Média:</span> <strong><?php echo $turmas_dados['3a_info']['stats']['media']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Pendentes:</span> <strong><?php echo $turmas_dados['3a_info']['stats']['pendentes']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Corrigidas:</span> <strong><?php echo $turmas_dados['3a_info']['stats']['corrigidas']; ?></strong></div>
                        </div>
                    </a>

                    <a href="2-turma.php?id=3b-info" class="adm-balao-turma">
                        <div class="adm-turma-nome">3° B INFO - <?php echo $ano_atual; ?></div>
                        <div class="adm-turma-dados">
                            <div class="adm-turma-dado-item"><span>Alunos:</span> <strong><?php echo $turmas_dados['3b_info']['total_alunos']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Média:</span> <strong><?php echo $turmas_dados['3b_info']['stats']['media']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Pendentes:</span> <strong><?php echo $turmas_dados['3b_info']['stats']['pendentes']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Corrigidas:</span> <strong><?php echo $turmas_dados['3b_info']['stats']['corrigidas']; ?></strong></div>
                        </div>
                    </a>

                    <a href="2-turma.php?id=3a-redes" class="adm-balao-turma">
                        <div class="adm-turma-nome">3° A REDES - <?php echo $ano_atual; ?></div>
                        <div class="adm-turma-dados">
                            <div class="adm-turma-dado-item"><span>Alunos:</span> <strong><?php echo $turmas_dados['3a_redes']['total_alunos']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Média:</span> <strong><?php echo $turmas_dados['3a_redes']['stats']['media']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Pendentes:</span> <strong><?php echo $turmas_dados['3a_redes']['stats']['pendentes']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Corrigidas:</span> <strong><?php echo $turmas_dados['3a_redes']['stats']['corrigidas']; ?></strong></div>
                        </div>
                    </a>

                    <a href="2-turma.php?id=3b-redes" class="adm-balao-turma">
                        <div class="adm-turma-nome">3° B REDES - <?php echo $ano_atual; ?></div>
                        <div class="adm-turma-dados">
                            <div class="adm-turma-dado-item"><span>Alunos:</span> <strong><?php echo $turmas_dados['3b_redes']['total_alunos']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Média:</span> <strong><?php echo $turmas_dados['3b_redes']['stats']['media']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Pendentes:</span> <strong><?php echo $turmas_dados['3b_redes']['stats']['pendentes']; ?></strong></div>
                            <div class="adm-turma-dado-item"><span>Corrigidas:</span> <strong><?php echo $turmas_dados['3b_redes']['stats']['corrigidas']; ?></strong></div>
                        </div>
                    </a>
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
                                    <td colspan="4">Nenhuma redação corrigida neste ano letivo.</td>
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