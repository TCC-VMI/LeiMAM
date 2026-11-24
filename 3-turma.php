<?php
require_once '3-funcoes-turma.php';
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
            <i data-lucide="menu"></i>
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
            <div class="adm-navegacao-voltar" style="margin-bottom: 15px;">
                <a href="3-painel-professor.php" class="adm-link-voltar" style="text-decoration: none; color: var(--adm-cor-texto-secundario); font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 6px;">
                    <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Voltar para o Início
                </a>
            </div>

            <header class="adm-cabecalho-pagina">
                <div>
                    <h1 class="adm-titulo-pagina"><?php echo htmlspecialchars($info_turma['nome_completo']); ?></h1>
                    <p class="adm-subtitulo-pagina">Ano Letivo: <strong><?php echo $ano_atual_brasil; ?></strong> | Total de Estudantes: <strong><?php echo $stats_turma['total_alunos']; ?></strong></p>
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
                        <div class="adm-indicador-titulo">Aguardando Correção</div>
                        <div class="adm-indicador-valor"><?php echo $stats_turma['aguardando']; ?></div>
                    </div>
                    <div class="adm-cartao-indicador">
                        <div class="adm-indicador-titulo">Redações Corrigidas</div>
                        <div class="adm-indicador-valor"><?php echo $stats_turma['corrigidas']; ?></div>
                    </div>
                </section>

                <div class="adm-grid-duplo">
                    <section class="adm-cartao-secao">
                        <h2 class="adm-titulo-secao">Média por Competência (ENEM)</h2>
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
                        <h2 class="adm-titulo-secao">Top 5 Alunos da Turma</h2>
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
                                        <tr><td colspan="3" style="text-align: center; color: var(--adm-cor-texto-secundario); padding: 15px 0;">Nenhum aluno avaliado nesta turma ainda.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>

            <!-- ABA 2: PROPOSTAS DA TURMA -->
            <div id="aba-propostas" class="adm-conteudo-aba" style="margin-top: 20px;">
                <div class="adm-barra-acoes-propostas" style="margin-bottom: 15px;">
                    <h2 class="adm-titulo-secao">Propostas Atribuídas à Turma</h2>
                </div>

                <div class="adm-grid-propostas">
                    <?php if (!empty($lista_propostas)): ?>
                        <?php foreach ($lista_propostas as $prop): ?>
                            <a href="3-detalhes-proposta.php?id=<?php echo $prop['id_proposta']; ?>" class="adm-balao-proposta">
                                <div class="adm-proposta-cabecalho" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <span class="adm-badge-status adm-badge-<?php echo $prop['status']; ?>"><?php echo $prop['status_nome']; ?></span>
                                    <span class="adm-proposta-prazo" style="font-size: 12px; color: var(--adm-cor-texto-secundario);"><?php echo $prop['prazo_texto']; ?></span>
                                </div>
                                <h3 class="adm-proposta-titulo" style="font-size: 16px; font-weight: 700; color: var(--adm-cor-marrom);"><?php echo htmlspecialchars($prop['titulo']); ?></h3>
                                <div class="adm-turma-progresso-container" style="margin-top: 15px;">
                                    <div class="adm-turma-progresso-label" style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 5px;">
                                        <span>Porcentagem de Envio</span>
                                        <span><?php echo $prop['porcentagem']; ?>%</span>
                                    </div>
                                    <div class="adm-barra-progresso-fundo">
                                        <div class="adm-barra-progresso-preenchimento" style="width: <?php echo $prop['porcentagem']; ?>%;"></div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="grid-column: 1 / -1; color: var(--adm-cor-texto-secundario); text-align: center; padding: 30px 0;">Nenhuma proposta vinculada a esta turma no momento.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ABA 3: ALUNOS DA TURMA -->
            <div id="aba-alunos" class="adm-conteudo-aba" style="margin-top: 20px;">
                <div class="adm-barra-acoes-propostas" style="margin-bottom: 15px;">
                    <h2 class="adm-titulo-secao">Estudantes Matriculados (<?php echo count($lista_alunos); ?>)</h2>
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
                                    <th>Média</th>
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
                                                <a href="3-historico-aluno.php?id_aluno=<?php echo $aluno['id_aluno']; ?>" class="adm-botao-acao-tabela adm-btn-visualizar" style="padding: 6px 12px; background-color: var(--adm-cor-amarelo); color: var(--adm-cor-marrom); border-radius: 6px; font-weight: 600; text-decoration: none; font-size: 12px;">
                                                    Ver Histórico
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" style="text-align: center; color: var(--adm-cor-texto-secundario); padding: 20px 0;">Nenhum aluno cadastrado nesta turma no ano atual.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- ABA 4: HISTÓRICO DE ENVIOS -->
            <div id="aba-historico" class="adm-conteudo-aba" style="margin-top: 20px;">
                <div class="adm-barra-acoes-propostas" style="margin-bottom: 15px;">
                    <h2 class="adm-titulo-secao">Últimas Redações Submetidas</h2>
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
                                    <th>Nota</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($historico_env)): ?>
                                    <?php foreach ($historico_env as $h): ?>
                                        <?php 
                                            $is_corrigida = !empty($h['id_correcao']);
                                            $status_label = $is_corrigida ? 'Corrigida' : 'Aguardando Correção';
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
                                                    <a href="3-visualizar-redacao.php?id=<?php echo $h['id_redacao']; ?>" class="adm-botao-acao-tabela adm-btn-visualizar" style="padding: 6px 12px; background-color: var(--adm-cor-laranja); color: var(--adm-cor-marrom); border-radius: 6px; font-weight: 600; text-decoration: none; font-size: 12px;">Visualizar</a>
                                                <?php else: ?>
                                                    <a href="3-corrigir-redacao.php?id=<?php echo $h['id_redacao']; ?>" class="adm-botao-acao-tabela adm-btn-corrigir" style="padding: 6px 12px; background-color: var(--adm-cor-amarelo); color: var(--adm-cor-marrom); border-radius: 6px; font-weight: 600; text-decoration: none; font-size: 12px;">Corrigir</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; color: var(--adm-cor-texto-secundario); padding: 20px 0;">
                                            Nenhuma redação enviada para esta turma no ano atual até o momento.
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