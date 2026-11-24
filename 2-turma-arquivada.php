<?php
require_once '2-funcoes-turma-arquivada.php';
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
            <i data-lucide="menu"></i>
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
            <div class="adm-navegacao-voltar">
                <a href="2-arquivados.php" class="adm-link-voltar">
                    <i data-lucide="arrow-left" style="width: 16px; height: 16px; vertical-align: middle;"></i> Voltar para Turmas Arquivadas
                </a>
            </div>

            <header class="adm-cabecalho-pagina">
                <div>
                    <h1 class="adm-titulo-pagina"><?php echo $info_turma['nome_completo']; ?> </h1>
                    <p class="adm-subtitulo-pagina">Ano Letivo: <strong><?php echo $ano_arquivado; ?></strong> | Total de Estudantes: <strong><?php echo $stats_turma['total_alunos']; ?></strong> <span style="color: #f59e0b; margin-left: 10px;">[Arquivado]</span></p>
                </div>
            </header>

            <!-- BARRA DE NAVEGAÇÃO HORIZONTAL DE 4 ABAS -->
            <nav class="adm-barra-abas-turma">
                <button class="adm-aba-item adm-aba-ativa" onclick="alternarAba(event, 'aba-painel')">
                    <i data-lucide="layout-dashboard" style="width: 18px; height: 18px;"></i> Painel da Turma
                </button>
                <button class="adm-aba-item" onclick="alternarAba(event, 'aba-propostas')">
                    <i data-lucide="file-text" style="width: 18px; height: 18px;"></i> Propostas
                </button>
                <button class="adm-aba-item" onclick="alternarAba(event, 'aba-alunos')">
                    <i data-lucide="users" style="width: 18px; height: 18px;"></i> Alunos
                </button>
                <button class="adm-aba-item" onclick="alternarAba(event, 'aba-historico')">
                    <i data-lucide="history" style="width: 18px; height: 18px;"></i> Histórico de Envios
                </button>
            </nav>

            <!-- ABA 1: PAINEL GERAL DA TURMA -->
            <div id="aba-painel" class="adm-conteudo-aba adm-aba-visivel" style="margin-top: 20px;">
                <section class="adm-grid-indicadores">
                    <div class="adm-cartao-indicador">
                        <div class="adm-indicador-titulo">Média Geral da Turma</div>
                        <div class="adm-indicador-valor"><?php echo $stats_turma['media_geral']; ?></div>
                    </div>
                    <div class="adm-cartao-indicador">
                        <div class="adm-indicador-titulo">Redações Pendentes</div>
                        <div class="adm-indicador-valor"><?php echo $stats_turma['pendentes']; ?></div>
                    </div>
                    <div class="adm-cartao-indicador">
                        <div class="adm-indicador-titulo">Não Corrigidas</div>
                        <div class="adm-indicador-valor"><?php echo $stats_turma['aguardando']; ?></div>
                    </div>
                    <div class="adm-cartao-indicador">
                        <div class="adm-indicador-titulo">Redações Concluídas</div>
                        <div class="adm-indicador-valor"><?php echo $stats_turma['corrigidas']; ?></div>
                    </div>
                </section>

                <div class="adm-grid-duplo">
                    <section class="adm-cartao-secao">
                        <h2 class="adm-titulo-secao">Médias Finais por Competência (ENEM)</h2>
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

                    <section class="adm-cartao-secao">
                        <h2 class="adm-titulo-secao">Top 5 Alunos do Ano</h2>
                        <div class="adm-tabela-container">
                            <table class="adm-tabela-alunos">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Média</th>
                                        <th>Enviadas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($top_alunos)): ?>
                                        <?php foreach ($top_alunos as $top): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($top['nome']); ?></strong></td>
                                                <td><strong><?php echo $top['media']; ?></strong></td>
                                                <td><?php echo $top['enviadas']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3" style="text-align: center; color: #64748b; padding: 15px 0;">Nenhum aluno avaliado neste ano.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>

            <!-- ABA 2: PROPOSTAS DA TURMA -->
            <div id="aba-propostas" class="adm-conteudo-aba">
                <div class="adm-barra-acoes-propostas" style="margin-top: 20px;">
                    <h2 class="adm-titulo-secao">Propostas do Ano Letivo (<?php echo $ano_arquivado; ?>)</h2>
                </div>

                <div class="adm-grid-propostas">
                    <?php if (!empty($lista_propostas)): ?>
                        <?php foreach ($lista_propostas as $prop): ?>
                            <div class="adm-balao-proposta">
                                <div class="adm-proposta-cabecalho">
                                    <span class="adm-badge-status adm-badge-encerrada"><?php echo $prop['status_nome']; ?></span>
                                </div>
                                <h3 class="adm-proposta-titulo"><?php echo htmlspecialchars($prop['titulo']); ?></h3>
                                <div class="adm-favorito-rodape" style="margin-top: 15px;">
                                    <span class="adm-favorito-data">
                                        Entregues: <strong><?php echo $prop['enviados']; ?>/<?php echo $stats_turma['total_alunos']; ?></strong> (<?php echo $prop['porcentagem']; ?>%)
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="grid-column: 1 / -1; color: #64748b; text-align: center; padding: 30px 0;">Nenhuma proposta registrada para esta turma no ano de <?php echo $ano_arquivado; ?>.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ABA 3: ALUNOS DA TURMA -->
            <div id="aba-alunos" class="adm-conteudo-aba">
                <div class="adm-barra-acoes-propostas" style="margin-top: 20px;">
                    <h2 class="adm-titulo-secao">Estudantes Matriculados em <?php echo $ano_arquivado; ?> (<?php echo count($lista_alunos); ?>)</h2>
                </div>

                <section class="adm-cartao-secao">
                    <div class="adm-tabela-container">
                        <table class="adm-tabela-alunos">
                            <thead>
                                <tr>
                                    <th>Nº</th>
                                    <th>Matrícula</th>
                                    <th>Nome Completo</th>
                                    <th>Redações Entregues</th>
                                    <th>Média Final</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($lista_alunos)): ?>
                                    <?php $num_chamada = 1; foreach ($lista_alunos as $aluno): ?>
                                        <tr>
                                            <td><strong><?php echo $num_chamada++; ?></strong></td>
                                            <td><?php echo htmlspecialchars($aluno['matricula']); ?></td>
                                            <td><strong><?php echo htmlspecialchars($aluno['nome']); ?></strong></td>
                                            <td><?php echo $aluno['entregues']; ?> / <?php echo $aluno['total_propostas']; ?></td>
                                            <td><strong><?php echo $aluno['media'] ? $aluno['media'] : '-'; ?></strong></td>
                                            <td>
                                                <a href="2-historico-aluno.php?id=<?php echo $aluno['id_aluno']; ?>" class="adm-botao-acao-tabela adm-btn-visualizar">
                                                    Ver Histórico
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 20px 0;">Nenhum aluno cadastrado nesta turma neste ano.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- ABA 4: HISTÓRICO DE ENVIOS -->
            <div id="aba-historico" class="adm-conteudo-aba">
                <div class="adm-barra-acoes-propostas" style="margin-top: 20px;">
                    <h2 class="adm-titulo-secao">Histórico de Redações Submetidas (<?php echo $ano_arquivado; ?>)</h2>
                </div>

                <section class="adm-cartao-secao">
                    <div class="adm-tabela-container">
                        <table class="adm-tabela-alunos">
                            <thead>
                                <tr>
                                    <th>Nome do Aluno</th>
                                    <th>Proposta / Tema</th>
                                    <th>Data de Envio</th>
                                    <th>Status</th>
                                    <th>Nota Final</th>
                                    <th>Consulta</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($historico_env)): ?>
                                    <?php foreach ($historico_env as $h): ?>
                                        <?php 
                                            $is_corrigida = !empty($h['id_correcao']);
                                            $status_label = $is_corrigida ? 'Corrigida' : 'Não Corrigida';
                                            $status_class = $is_corrigida ? 'adm-badge-ativa' : 'adm-badge-agendada';
                                            $nota_display = $is_corrigida ? $h['nota_total'] : '-';
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($h['aluno_nome']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($h['proposta_titulo']); ?></td>
                                            <td><?php echo date('d/m/Y - H:i', strtotime($h['enviado_em'])); ?></td>
                                            <td><span class="adm-badge-status <?php echo $status_class; ?>"><?php echo $status_label; ?></span></td>
                                            <td><strong><?php echo $nota_display; ?></strong></td>
                                            <td>
                                                <?php if ($is_corrigida): ?>
                                                    <a href="2-visualizar-redacao.php?id=<?php echo $h['id_redacao']; ?>" class="adm-botao-acao-tabela adm-btn-visualizar">Visualizar Redação</a>
                                                <?php else: ?>
                                                    <span style="color: #64748b; font-size: 13px;">Sem correção</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; color: #64748b; padding: 20px 0;">
                                            Nenhum registro encontrado para esta turma no ano de <?php echo $ano_arquivado; ?>.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

        </main>
    </div>

    <script>
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

        lucide.createIcons();
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