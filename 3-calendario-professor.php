<?php
require_once '3-funcoes-calendario-professor.php';

// --- CONTROLE DA NAVEGAÇÃO DE MESES ---
$mes_atual = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('n');
$ano_atual = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');

$mes_anterior = $mes_atual - 1;
$ano_anterior = $ano_atual;
if ($mes_anterior < 1) {
    $mes_anterior = 12;
    $ano_anterior--;
}

$mes_proximo = $mes_atual + 1;
$ano_proximo = $ano_atual;
if ($mes_proximo > 12) {
    $mes_proximo = 1;
    $ano_proximo++;
}

$meses_nomes = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];

$primeiro_dia_timestamp = mktime(0, 0, 0, $mes_atual, 1, $ano_atual);
$total_dias_mes = date('t', $primeiro_dia_timestamp);
$dia_semana_inicio = date('w', $primeiro_dia_timestamp);

// Carrega os eventos unificados restritos às turmas do corretor
$eventos_por_data = obterEventosMesProfessor($conn, $corretor, $ano_atual, $mes_atual, $total_dias_mes);
$eventos_proximos = obterProximosEventosProfessor($conn, $corretor);
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
    <style>
        .adm-btn-mes-link {
            text-decoration: none;
            color: inherit;
            display: inline-flex;
            align-items: center;
            justify-content: center;
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
            <a href="3-painel-professor.php" class="adm-logo-area">
                <img src="LOGO PNG - LeiMAM.png" alt="Logo LeiMAM" class="adm-logo-imagem">
                <div class="adm-logo-textos-grupo">
                    <span class="adm-logo-texto">Lei<span>MAM</span></span>
                    <span class="adm-subtitulo-logo">Portal do Corretor</span>
                </div>
            </a>

            <nav class="adm-nav-container">
                <ul class="adm-menu-lista">
                    <li><a href="3-painel-professor.php" class="adm-menu-item-link">Início</a></li>
                    <li><a href="3-propostas-professor.php" class="adm-menu-item-link">Propostas</a></li>
                    <li><a href="3-calendario-professor.php" class="adm-menu-item-link adm-ativo">Calendário</a></li>
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
            <header class="adm-cabecalho-pagina adm-cabecalho-calendario">
                <div>
                    <h1 class="adm-titulo-pagina">Calendário Acadêmico</h1>
                    <p class="adm-subtitulo-pagina">Acompanhe prazos das propostas de suas turmas e datas importantes.</p>
                </div>
            </header>

            <div class="adm-legenda-eventos">
                <span class="adm-item-legenda"><span class="adm-dot-cor adm-evento-proposta"></span> Prazo de Proposta</span>
                <span class="adm-item-legenda"><span class="adm-dot-cor adm-evento-reuniao"></span> Reunião Pedagógica</span>
                <span class="adm-item-legenda"><span class="adm-dot-cor adm-evento-notas"></span> Fechamento de Notas / Correções</span>
            </div>

            <div class="adm-grid-calendario-layout">
                
                <section class="adm-cartao-secao adm-bloco-calendario-principal">
                    <div class="adm-topo-mes-navegacao">
                        <a href="3-calendario-professor.php?mes=<?php echo $mes_anterior; ?>&ano=<?php echo $ano_anterior; ?>" class="adm-btn-mes adm-btn-mes-link">◄</a>
                        <h2 class="adm-titulo-secao"><?php echo $meses_nomes[$mes_atual] . ' de ' . $ano_atual; ?></h2>
                        <a href="3-calendario-professor.php?mes=<?php echo $mes_proximo; ?>&ano=<?php echo $ano_proximo; ?>" class="adm-btn-mes adm-btn-mes-link">►</a>
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
                                <?php echo $dia; ?>

                                <?php if (isset($eventos_por_data[$data_formatada])): ?>
                                    <?php foreach ($eventos_por_data[$data_formatada] as $evt): 
                                        $classe_tipo = "adm-evento-" . htmlspecialchars($evt['tipo']);
                                        $titulo_evt = htmlspecialchars($evt['titulo']);
                                    ?>
                                        <div class="adm-marcador-evento <?php echo $classe_tipo; ?>" title="<?php echo $titulo_evt; ?>">
                                            <?php echo $titulo_evt; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>

                    </div>
                </section>

                <aside class="adm-cartao-secao adm-painel-eventos-proximos">
                    <h2 class="adm-titulo-secao">Eventos Próximos</h2>
                    
                    <div class="adm-lista-eventos-proximos">
                        <?php if (empty($eventos_proximos)): ?>
                            <p style="color: #666; font-size: 0.9rem; padding: 10px 0;">Nenhum evento futuro encontrado.</p>
                        <?php else: ?>
                            <?php foreach ($eventos_proximos as $evt): 
                                $data_br = date("d/m/Y", strtotime($evt['data_evento']));
                                $hora_br = $evt['hora_limite'] ? " às " . date("H:i", strtotime($evt['hora_limite'])) : '';
                                
                                $rotulos = [
                                    'proposta' => 'Prazo Final de Proposta',
                                    'reuniao'  => 'Reunião Pedagógica',
                                    'notas'    => 'Fechamento de Notas'
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