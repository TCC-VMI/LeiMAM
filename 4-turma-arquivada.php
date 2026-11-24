<?php
require_once '4-funcoes-turma-arquivada-aluno.php';
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
    <style>
        /* Estilos adicionais mantendo o padrão visual */
        .adm-barra-abas-turma {
            display: flex; gap: 10px; border-bottom: 2px solid var(--adm-cor-borda); margin-top: 20px; padding-bottom: 0;
        }
        .adm-aba-item {
            background: none; border: none; padding: 12px 20px; font-weight: 700; color: var(--adm-cor-texto-secundario); cursor: pointer; display: flex; align-items: center; gap: 8px; border-bottom: 3px solid transparent; transition: all 0.2s;
        }
        .adm-aba-item.adm-aba-ativa {
            color: var(--adm-cor-marrom); border-bottom-color: var(--adm-cor-laranja);
        }
        .adm-conteudo-aba { display: none; }
        .adm-conteudo-aba.adm-aba-visivel { display: block; }
        .adm-grid-indicadores {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;
        }
        .adm-cartao-indicador {
            background: var(--adm-fundo-cartao); padding: 20px; border-radius: 12px; border: 1px solid var(--adm-cor-borda); box-shadow: 0 4px 12px var(--adm-cor-sombra);
        }
        .adm-indicador-titulo { font-size: 13px; color: var(--adm-cor-texto-secundario); font-weight: 600; margin-bottom: 8px; }
        .adm-indicador-valor { font-size: 26px; font-weight: 800; color: var(--adm-cor-marrom); }
        .adm-grid-duplo { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px; }
        .adm-cartao-secao { background: var(--adm-fundo-cartao); padding: 24px; border-radius: 12px; border: 1px solid var(--adm-cor-borda); box-shadow: 0 4px 12px var(--adm-cor-sombra); }
        .adm-titulo-secao { font-size: 17px; font-weight: 800; color: var(--adm-cor-marrom); margin-bottom: 20px; }
        .adm-lista-competencias { display: flex; flex-direction: column; gap: 14px; }
        .adm-competencia-item { display: flex; flex-direction: column; gap: 5px; }
        .adm-competencia-info { display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; color: var(--adm-cor-texto-principal); }
        .adm-barra-progresso-fundo { background: var(--adm-fundo-pagina); border-radius: 6px; height: 8px; width: 100%; overflow: hidden; }
        .adm-barra-progresso-preenchimento { background: var(--adm-cor-laranja); height: 100%; border-radius: 6px; }
        .adm-tabela-container { overflow-x: auto; }
        .adm-tabela-alunos { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
        .adm-tabela-alunos th { padding: 12px; background: var(--adm-fundo-pagina); color: var(--adm-cor-marrom); font-weight: 700; border-bottom: 2px solid var(--adm-cor-borda); }
        .adm-tabela-alunos td { padding: 12px; border-bottom: 1px solid var(--adm-cor-borda); color: var(--adm-cor-texto-principal); }
        .adm-grid-propostas { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-top: 20px; }
        .adm-balao-proposta { background: var(--adm-fundo-cartao); border: 1px solid var(--adm-cor-borda); border-radius: 12px; padding: 20px; text-decoration: none; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s, border-color 0.2s; box-shadow: 0 4px 12px var(--adm-cor-sombra); }
        .adm-balao-proposta:hover { border-color: var(--adm-cor-laranja); transform: translateY(-2px); }
        .adm-badge-status { font-size: 11px; padding: 4px 10px; border-radius: 6px; font-weight: 700; display: inline-block; }
        .adm-badge-encerrada { background: #fef3c7; color: #d97706; }
        .adm-proposta-titulo { font-size: 16px; font-weight: 800; color: var(--adm-cor-marrom); margin-top: 12px; line-height: 1.3; }
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
                    <li><a href="4-propostas-aluno.php" class="adm-menu-item-link">Propostas</a></li>
                    <li><a href="4-calendario-aluno.php" class="adm-menu-item-link">Calendário</a></li>
                    <li><a href="4-arquivados-aluno.php" class="adm-menu-item-link adm-ativo">Arquivados</a></li>
                </ul>

                <div class="adm-menu-rodape">
                    <a href="index.html" class="adm-menu-item-link adm-item-sair">
                        <i data-lucide="arrow-left"></i> Sair
                    </a>
                </div>
            </nav>
        </aside>

        <main class="adm-area-conteudo">
            <div style="margin-bottom: 15px;">
                <a href="4-arquivados-aluno.php" style="text-decoration: none; color: var(--adm-cor-laranja); font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
                    <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Voltar para Turmas Arquivadas
                </a>
            </div>

            <header class="adm-cabecalho-pagina">
                <div>
                    <h1 class="adm-titulo-pagina"><?php echo htmlspecialchars($nome_turma_completa); ?></h1>
                    <p class="adm-subtitulo-pagina">Ano Letivo: <strong><?php echo $ano_arquivado; ?></strong> <span style="color: var(--adm-cor-laranja); margin-left: 10px;">[Histórico Pessoal]</span></p>
                </div>
            </header>

            <!-- ABAS -->
            <nav class="adm-barra-abas-turma">
                <button class="adm-aba-item adm-aba-ativa" onclick="alternarAba(event, 'aba-painel')">
                    <i data-lucide="layout-dashboard" style="width: 18px; height: 18px;"></i> Painel do Aluno
                </button>
                <button class="adm-aba-item" onclick="alternarAba(event, 'aba-propostas')">
                    <i data-lucide="file-text" style="width: 18px; height: 18px;"></i> Propostas
                </button>
            </nav>

            <!-- ABA 1: PAINEL DA TURMA (PESSOAL) -->
            <div id="aba-painel" class="adm-conteudo-aba adm-aba-visivel" style="margin-top: 20px;">
                <section class="adm-grid-indicadores">
                    <div class="adm-cartao-indicador">
                        <div class="adm-indicador-titulo">Média Anual</div>
                        <div class="adm-indicador-valor"><?php echo $stats_pessoais['media_anual']; ?></div>
                    </div>
                    <div class="adm-cartao-indicador">
                        <div class="adm-indicador-titulo">Redações Pendentes</div>
                        <div class="adm-indicador-valor"><?php echo $stats_pessoais['pendentes']; ?></div>
                    </div>
                    <div class="adm-cartao-indicador">
                        <div class="adm-indicador-titulo">Não Corrigidas</div>
                        <div class="adm-indicador-valor"><?php echo $stats_pessoais['aguardando']; ?></div>
                    </div>
                    <div class="adm-cartao-indicador">
                        <div class="adm-indicador-titulo">Redações Corrigidas</div>
                        <div class="adm-indicador-valor"><?php echo $stats_pessoais['corrigidas']; ?></div>
                    </div>
                </section>

                <div class="adm-grid-duplo">
                    <section class="adm-cartao-secao">
                        <h2 class="adm-titulo-secao">Médias por Competência (ENEM)</h2>
                        <div class="adm-lista-competencias">
                            <div class="adm-competencia-item">
                                <div class="adm-competencia-info"><span>C1 - Norma Culta</span><span><?php echo $competencias['c1']; ?> / 200</span></div>
                                <div class="adm-barra-progresso-fundo"><div class="adm-barra-progresso-preenchimento" style="width: <?php echo ($competencias['c1'] / 200) * 100; ?>%;"></div></div>
                            </div>
                            <div class="adm-competencia-item">
                                <div class="adm-competencia-info"><span>C2 - Compreensão do Tema</span><span><?php echo $competencias['c2']; ?> / 200</span></div>
                                <div class="adm-barra-progresso-fundo"><div class="adm-barra-progresso-preenchimento" style="width: <?php echo ($competencias['c2'] / 200) * 100; ?>%;"></div></div>
                            </div>
                            <div class="adm-competencia-item">
                                <div class="adm-competencia-info"><span>C3 - Argumentação</span><span><?php echo $competencias['c3']; ?> / 200</span></div>
                                <div class="adm-barra-progresso-fundo"><div class="adm-barra-progresso-preenchimento" style="width: <?php echo ($competencias['c3'] / 200) * 100; ?>%;"></div></div>
                            </div>
                            <div class="adm-competencia-item">
                                <div class="adm-competencia-info"><span>C4 - Coesão</span><span><?php echo $competencias['c4']; ?> / 200</span></div>
                                <div class="adm-barra-progresso-fundo"><div class="adm-barra-progresso-preenchimento" style="width: <?php echo ($competencias['c4'] / 200) * 100; ?>%;"></div></div>
                            </div>
                            <div class="adm-competencia-item">
                                <div class="adm-competencia-info"><span>C5 - Proposta de Intervenção</span><span><?php echo $competencias['c5']; ?> / 200</span></div>
                                <div class="adm-barra-progresso-fundo"><div class="adm-barra-progresso-preenchimento" style="width: <?php echo ($competencias['c5'] / 200) * 100; ?>%;"></div></div>
                            </div>
                        </div>
                    </section>

                    <!-- DESEMPENHO POR TEMAS REALIZADOS -->
                    <section class="adm-cartao-secao">
                        <h2 class="adm-titulo-secao">Desempenho por Temas Realizados</h2>
                        <div class="adm-tabela-container">
                            <table class="adm-tabela-alunos">
                                <thead>
                                    <tr>
                                        <th>Proposta</th>
                                        <th>Data de Envio</th>
                                        <th>Nota Final</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Se a variável $historico_temas não estiver definida no backend, usamos $top_propostas como fallback
                                    $dados_temas = isset($historico_temas) ? $historico_temas : (isset($top_propostas) ? $top_propostas : []);
                                    ?>
                                    <?php if (!empty($dados_temas)): ?>
                                        <?php foreach ($dados_temas as $item): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($item['tema'] ?? $item['titulo']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($item['data'] ?? (isset($item['enviado_em']) ? date('d/m/Y', strtotime($item['enviado_em'])) : '-')); ?></td>
                                                <td><strong><?php echo $item['nota'] ?? $item['nota_total']; ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" style="text-align: center; color: var(--adm-cor-texto-secundario); padding: 20px 0;">Ainda não enviou redações para correção.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>

            <!-- ABA 2: PROPOSTAS -->
            <div id="aba-propostas" class="adm-conteudo-aba" style="margin-top: 20px;">
                <h2 class="adm-titulo-secao">Propostas do Ano Letivo (<?php echo $ano_arquivado; ?>)</h2>
                
                <div class="adm-grid-propostas">
                    <?php if (!empty($lista_propostas)): ?>
                        <?php foreach ($lista_propostas as $prop): ?>
                            <a href="4-detalhes-proposta-arquivada.php?id=<?php echo $prop['id_proposta']; ?>&aluno=<?php echo $id_aluno_historico; ?>" class="adm-balao-proposta">
                                <div>
                                    <span class="adm-badge-status adm-badge-encerrada"><?php echo $prop['status_nome']; ?></span>
                                    <h3 class="adm-proposta-titulo"><?php echo htmlspecialchars($prop['titulo']); ?></h3>
                                </div>
                                <div style="margin-top: 20px; font-size: 12px; color: var(--adm-cor-texto-secundario);">
                                    <span>Clique para ver os detalhes e redação enviada &rarr;</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="grid-column: 1 / -1; color: var(--adm-cor-texto-secundario); text-align: center; padding: 30px 0;">Nenhuma proposta registrada para esta turma no ano de <?php echo $ano_arquivado; ?>.</p>
                    <?php endif; ?>
                </div>
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

        function alternarAba(evt, idAba) {
            const conteudos = document.querySelectorAll('.adm-conteudo-aba');
            conteudos.forEach(c => c.classList.remove('adm-aba-visivel'));

            const abas = document.querySelectorAll('.adm-aba-item');
            abas.forEach(a => a.classList.remove('adm-aba-ativa'));

            document.getElementById(idAba).classList.add('adm-aba-visivel');
            evt.currentTarget.classList.add('adm-aba-ativa');
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