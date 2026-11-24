<?php
require_once '2-funcoes-calendario-administrador.php';

// --- CONTROLO DA NAVEGAÇÃO DE MESES ---
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

// Carrega os eventos unificados
$eventos_por_data = obterEventosMes($conn, $ano_atual, $mes_atual, $total_dias_mes);
$eventos_proximos = obterProximosEventos($conn);
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
    <style>
        .adm-btn-mes-link {
            text-decoration: none;
            color: inherit;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .modal-mascara {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none; align-items: center; justify-content: center;
            z-index: 9999;
        }
        .modal-mascara.ativo { display: flex; }
        .modal-conteudo {
            background: #fff; padding: 24px; border-radius: 12px;
            max-width: 420px; width: 90%; text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .modal-icone { color: #ef4444; margin-bottom: 12px; }
        .modal-titulo { font-size: 1.25rem; margin-bottom: 8px; color: #1e293b; }
        .modal-texto { font-size: 0.9rem; color: #64748b; margin-bottom: 20px; line-height: 1.4; }
        .modal-acoes { display: flex; gap: 10px; justify-content: center; }
        .btn-modal-cancelar {
            background: #e2e8f0; color: #475569; border: none; padding: 10px 18px;
            border-radius: 6px; cursor: pointer; font-weight: 600;
        }
        .btn-modal-excluir {
            background: #ef4444; color: #fff; border: none; padding: 10px 18px;
            border-radius: 6px; cursor: pointer; font-weight: 600; text-decoration: none;
        }
        .btn-modal-excluir:hover { background: #dc2626; }
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
                    <li><a href="2-calendario-administrador.php" class="adm-menu-item-link adm-ativo">Calendário</a></li>
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
            <header class="adm-cabecalho-pagina adm-cabecalho-calendario">
                <div>
                    <h1 class="adm-titulo-pagina">Calendário Acadêmico</h1>
                    <p class="adm-subtitulo-pagina">Acompanhe prazos de propostas, reuniões e datas limite de correções.</p>
                </div>
                <button class="adm-botao-criar-turma" onclick="abrirModalEvento()">+ Adicionar Evento</button>
            </header>

            <div class="adm-legenda-eventos">
                <span class="adm-item-legenda"><span class="adm-dot-cor adm-evento-proposta"></span> Prazo de Proposta</span>
                <span class="adm-item-legenda"><span class="adm-dot-cor adm-evento-reuniao"></span> Reunião Pedagógica</span>
                <span class="adm-item-legenda"><span class="adm-dot-cor adm-evento-notas"></span> Fechamento de Notas / Correções</span>
            </div>

            <div class="adm-grid-calendario-layout">
                
                <section class="adm-cartao-secao adm-bloco-calendario-principal">
                    <div class="adm-topo-mes-navegacao">
                        <a href="2-calendario-administrador.php?mes=<?php echo $mes_anterior; ?>&ano=<?php echo $ano_anterior; ?>" class="adm-btn-mes adm-btn-mes-link">◄</a>
                        <h2 class="adm-titulo-secao"><?php echo $meses_nomes[$mes_atual] . ' de ' . $ano_atual; ?></h2>
                        <a href="2-calendario-administrador.php?mes=<?php echo $mes_proximo; ?>&ano=<?php echo $ano_proximo; ?>" class="adm-btn-mes adm-btn-mes-link">►</a>
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
                                        $id_evt = (int)$evt['id'];
                                        $origem = $evt['origem'];
                                    ?>
                                        <div class="adm-marcador-evento <?php echo $classe_tipo; ?>" title="<?php echo $titulo_evt; ?>">
                                            <?php echo $titulo_evt; ?>
                                            
                                            <!-- Exibe botão de exclusão apenas para eventos manuais -->
                                            <?php if ($origem === 'manual'): ?>
                                                <button type="button" 
                                                        class="adm-btn-excluir-evento" 
                                                        onclick="confirmarExclusaoEvento(<?php echo $id_evt; ?>, '<?php echo htmlspecialchars(addslashes($titulo_evt)); ?>')" 
                                                        title="Apagar Evento">✕</button>
                                            <?php endif; ?>
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

    <!-- Modal Adicionar Evento Manual -->
    <div class="adm-mascara-modal" id="modalEvento">
        <div class="adm-modal-conteudo">
            <div class="adm-modal-cabecalho">
                <h2>Adicionar Novo Evento</h2>
                <button class="adm-modal-fechar" onclick="fecharModalEvento()">✕</button>
            </div>
            <form action="2-calendario-administrador.php" method="post" class="adm-form-modal">
                <input type="hidden" name="acao" value="inserir_evento">
                <div class="adm-form-grupo">
                    <label class="adm-label-form">Título do Evento</label>
                    <input type="text" name="titulo" class="adm-input-form" placeholder="Ex: Fechamento de Notas 3º Bimestre" required>
                </div>

                <div class="adm-form-grupo">
                    <label class="adm-label-form">Tipo de Evento</label>
                    <select name="tipo" class="adm-seletor-form" required>
                        <option value="proposta">Dia Final de Proposta</option>
                        <option value="reuniao">Reunião Pedagógica</option>
                        <option value="notas">Fechamento de Notas / Correções</option>
                    </select>
                </div>

                <div class="adm-form-linha">
                    <div class="adm-form-grupo">
                        <label class="adm-label-form">Data</label>
                        <input type="date" name="data_evento" class="adm-input-form" required>
                    </div>
                    <div class="adm-form-grupo">
                        <label class="adm-label-form">Hora Limite</label>
                        <input type="time" name="hora_limite" class="adm-input-form">
                    </div>
                </div>

                <div class="adm-form-grupo">
                    <label class="adm-label-form">Descrição / Observações</label>
                    <textarea name="descricao" class="adm-textarea-form" rows="3" placeholder="Insira detalhes adicionais do evento..."></textarea>
                </div>

                <div class="adm-form-acoes">
                    <button type="button" class="adm-botao-cancelar" onclick="fecharModalEvento()">Cancelar</button>
                    <button type="submit" class="adm-botao-salvar">Salvar Evento</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Confirmação de Exclusão -->
    <div class="modal-mascara" id="modalExclusaoEvento">
        <div class="modal-conteudo">
            <div class="modal-icone">
                <i data-lucide="alert-triangle" style="width: 48px; height: 48px;"></i>
            </div>
            <h3 class="modal-titulo">Tem certeza absoluta?</h3>
            <p class="modal-texto">
                Você está prestes a apagar o evento <strong id="nomeEventoExcluir"></strong>.<br>
                Esta ação removerá permanentemente o evento do banco de dados.
            </p>
            <div class="modal-acoes">
                <button type="button" class="btn-modal-cancelar" onclick="fecharModalExclusaoEvento()">Cancelar</button>
                <button type="button" class="btn-modal-excluir" onclick="executarExclusaoEvento()">Sim, Excluir</button>
            </div>
        </div>
    </div>

    <!-- Formulário oculto para Exclusão -->
    <form id="formExcluirEvento" action="2-calendario-administrador.php" method="POST" style="display: none;">
        <input type="hidden" name="acao" value="excluir_evento">
        <input type="hidden" name="id_evento" id="inputExcluirIdEvento" value="">
        <input type="hidden" name="mes" value="<?php echo $mes_atual; ?>">
        <input type="hidden" name="ano" value="<?php echo $ano_atual; ?>">
    </form>

    <script>
        lucide.createIcons();

        function alternarMenu() {
            const barra = document.getElementById('admBarraLateral');
            const mascara = document.getElementById('admMascaraMenu');
            barra.classList.toggle('adm-aberto');
            mascara.classList.toggle('adm-ativo');
        }

        function abrirModalEvento() {
            document.getElementById('modalEvento').classList.add('adm-modal-visivel');
        }

        function fecharModalEvento() {
            document.getElementById('modalEvento').classList.remove('adm-modal-visivel');
        }

        function confirmarExclusaoEvento(idEvento, tituloEvento) {
            document.getElementById('nomeEventoExcluir').textContent = '"' + tituloEvento + '"';
            document.getElementById('inputExcluirIdEvento').value = idEvento;
            document.getElementById('modalExclusaoEvento').classList.add('ativo');
        }

        function fecharModalExclusaoEvento() {
            document.getElementById('modalExclusaoEvento').classList.remove('ativo');
        }

        function executarExclusaoEvento() {
            document.getElementById('formExcluirEvento').submit();
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