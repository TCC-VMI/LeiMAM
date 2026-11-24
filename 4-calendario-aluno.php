<?php

require_once '4-funcoes-calendario-aluno.php';

$mes_atual = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('n');
$ano_atual = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');

$mes_anterior = $mes_atual - 1;
$ano_anterior = $ano_atual;
if ($mes_anterior < 1) { $mes_anterior = 12; $ano_anterior--; }

$mes_proximo = $mes_atual + 1;
$ano_proximo = $ano_atual;
if ($mes_proximo > 12) { $mes_proximo = 1; $ano_proximo++; }

$meses_nomes = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];

$primeiro_dia_timestamp = mktime(0, 0, 0, $mes_atual, 1, $ano_atual);
$total_dias_mes = date('t', $primeiro_dia_timestamp);
$dia_semana_inicio = date('w', $primeiro_dia_timestamp);

$eventos_por_data = obterEventosMesAluno($conn, $ano_atual, $mes_atual, $total_dias_mes, $coluna_turma_proposta);
$eventos_proximos = obterProximosEventosAluno($conn, $coluna_turma_proposta);
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
        .adm-btn-mes-link {
            text-decoration: none;
            color: inherit;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        /* Ajuste para o marcador virar um balãozinho/ponto no mobile */
        @media (max-width: 768px) {
            .adm-marcador-evento {
                font-size: 0 !important;
                padding: 6px !important;
                border-radius: 50% !important;
                width: 10px;
                height: 10px;
                display: inline-block;
                margin: 2px auto;
                text-align: center;
            }
            .adm-celula-dia {
                min-height: 65px;
                text-align: center;
                padding: 4px;
            }
        }
        /* Modal Detalhe do Evento Clicado no Calendário */
        .modal-evento-detalhe {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5); display: none; align-items: center; justify-content: center; z-index: 9999;
        }
        .modal-evento-detalhe.ativo { display: flex; }
        .modal-evento-card {
            background: #fff; padding: 24px; border-radius: 12px; max-width: 400px; width: 90%; text-align: left;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2); position: relative;
        }
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
                    <li><a href="4-calendario-aluno.php" class="adm-menu-item-link adm-ativo">Calendário</a></li>
                    <li><a href="4-arquivados-aluno.php" class="adm-menu-item-link">Arquivados</a></li>
                </ul>

                <div class="adm-menu-rodape">
                    <a href="index.html" class="adm-menu-item-link adm-item-sair">
                        <i data-lucide="arrow-left"></i> Sair
                    </a>
                </div>
            </nav>
        </aside>

        <main class="adm-area-conteudo">
            <header class="adm-cabecalho-pagina adm-cabecalho-calendario" style="margin-bottom: 20px;">
                <div>
                    <h1 class="adm-titulo-pagina">Calendário Acadêmico</h1>
                    <p class="adm-subtitulo-pagina">Acompanhe prazos de suas propostas ativas e eventos da turma.</p>
                </div>
            </header>

            <div class="adm-legenda-eventos">
                <span class="adm-item-legenda"><span class="adm-dot-cor adm-evento-proposta"></span> Prazo de Proposta</span>
            </div>

            <div class="adm-grid-calendario-layout">
                
                <section class="adm-cartao-secao adm-bloco-calendario-principal">
                    <div class="adm-topo-mes-navegacao">
                        <a href="4-calendario-aluno.php?mes=<?php echo $mes_anterior; ?>&ano=<?php echo $ano_anterior; ?>" class="adm-btn-mes adm-btn-mes-link">◄</a>
                        <h2 class="adm-titulo-secao"><?php echo $meses_nomes[$mes_atual] . ' de ' . $ano_atual; ?></h2>
                        <a href="4-calendario-aluno.php?mes=<?php echo $mes_proximo; ?>&ano=<?php echo $ano_proximo; ?>" class="adm-btn-mes adm-btn-mes-link">►</a>
                    </div>

                    <div class="adm-grade-calendario">
                        <div class="adm-dia-semana">Dom</div>
                        <div class="adm-dia-semana">Seg</div>
                        <div class="adm-dia-semana">Ter</div>
                        <div class="adm-dia-semana">Qua</div>
                        <div class="adm-dia-semana">Qui</div>
                        <div class="adm-dia-semana">Sex</div>
                        <div class="adm-dia-semana">Sáb</div>

                        <?php for ($i = 0; $i < $dia_semana_inicio; $i++): ?>
                            <div class="adm-celula-dia adm-dia-fora"></div>
                        <?php endfor; ?>

                        <?php for ($dia = 1; $dia <= $total_dias_mes; $dia++): 
                            $data_formatada = sprintf("%04d-%02d-%02d", $ano_atual, $mes_atual, $dia);
                            $classe_hoje = ($data_formatada === date('Y-m-d')) ? 'adm-dia-atual' : '';
                        ?>
                            <div class="adm-celula-dia <?php echo $classe_hoje; ?>">
                                <span><?php echo $dia; ?></span>

                                <?php if (isset($eventos_por_data[$data_formatada])): ?>
                                    <?php foreach ($eventos_por_data[$data_formatada] as $evt): 
                                        $classe_tipo = "adm-evento-" . htmlspecialchars($evt['tipo']);
                                        $titulo_evt = htmlspecialchars($evt['titulo']);
                                        $desc_evt = htmlspecialchars($evt['descricao']);
                                        $data_evt = date('d/m/Y', strtotime($evt['data_evento']));
                                        $hora_evt = $evt['hora_limite'] ? ' às ' . date('H:i', strtotime($evt['hora_limite'])) : '';
                                    ?>
                                        <div class="adm-marcador-evento <?php echo $classe_tipo; ?>" 
                                             title="<?php echo $titulo_evt; ?>"
                                             onclick="abrirDetalheEvento('<?php echo addslashes($titulo_evt); ?>', '<?php echo addslashes($desc_evt); ?>', '<?php echo $data_evt . $hora_evt; ?>')">
                                            <?php echo $titulo_evt; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>

                    </div>
                </section>

                <aside class="adm-cartao-secao adm-painel-eventos-proximos">
                    <h2 class="adm-titulo-secao">Próximos Eventos</h2>
                    
                    <div class="adm-lista-eventos-proximos">
                        <?php if (empty($eventos_proximos)): ?>
                            <p style="color: var(--adm-cor-texto-secundario); font-size: 0.9rem; padding: 10px 0;">Nenhum evento futuro encontrado.</p>
                        <?php else: ?>
                            <?php foreach ($eventos_proximos as $evt): 
                                $data_br = date("d/m/Y", strtotime($evt['data_evento']));
                                $hora_br = $evt['hora_limite'] ? " às " . date("H:i", strtotime($evt['hora_limite'])) : '';
                                
                                $rotulos = [
                                    'proposta' => 'Prazo Final de Proposta',
                                    'reuniao'  => 'Reunião / Evento',
                                    'notas'    => 'Data Importante'
                                ];
                                $rotulo_tipo = isset($rotulos[$evt['tipo']]) ? $rotulos[$evt['tipo']] : 'Evento';
                            ?>
                                <div class="adm-card-evento-proximo adm-borda-<?php echo htmlspecialchars($evt['tipo']); ?>">
                                    <span class="adm-tag-categoria adm-evento-<?php echo htmlspecialchars($evt['tipo']); ?>"><?php echo $rotulo_tipo; ?></span>
                                    <h3 class="adm-titulo-evento-card"><?php echo htmlspecialchars($evt['titulo']); ?></h3>
                                    <p class="adm-data-evento-card"><?php echo $data_br . $hora_br; ?></p>
                                    <?php if (!empty($evt['descricao'])): ?>
                                        <small class="adm-detalhe-evento-card"><?php echo htmlspecialchars($evt['descricao']); ?></small>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </aside>

            </div>
        </main>
    </div>

    <!-- Modal para exibir o evento completo ao clicar no celular/computador -->
    <div class="modal-evento-detalhe" id="modalDetalheEvento">
        <div class="modal-evento-card">
            <h3 id="modalTituloEvento" style="font-size: 1.15rem; color: var(--adm-cor-marrom); margin-bottom: 8px;"></h3>
            <p id="modalDataEvento" style="font-size: 0.85rem; color: var(--adm-cor-laranja); font-weight: 700; margin-bottom: 12px;"></p>
            <p id="modalDescricaoEvento" style="font-size: 0.9rem; color: var(--adm-cor-texto-principal); line-height: 1.4; margin-bottom: 20px;"></p>
            <button type="button" onclick="fecharDetalheEvento()" style="background: var(--adm-cor-marrom); color: #fff; border: none; padding: 10px 18px; border-radius: 6px; cursor: pointer; font-weight: 600; width: 100%;">Fechar</button>
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

        function abrirDetalheEvento(titulo, descricao, dataHora) {
            document.getElementById('modalTituloEvento').textContent = titulo;
            document.getElementById('modalDataEvento').textContent = 'Data: ' + dataHora;
            document.getElementById('modalDescricaoEvento').textContent = descricao || 'Nenhuma descrição informada.';
            document.getElementById('modalDetalheEvento').classList.add('ativo');
        }

        function fecharDetalheEvento() {
            document.getElementById('modalDetalheEvento').classList.remove('ativo');
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