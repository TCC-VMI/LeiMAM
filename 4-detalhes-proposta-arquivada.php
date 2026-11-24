<?php
require_once '4-funcoes-detalhes-proposta-arquivada.php';
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
        .adm-detalhes-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-top: 20px; }
        @media (max-width: 900px) { .adm-detalhes-grid { grid-template-columns: 1fr; } }
        .adm-bloco-texto { background: var(--adm-fundo-pagina); padding: 16px; border-radius: 8px; border: 1px solid var(--adm-cor-borda); white-space: pre-wrap; font-size: 14px; line-height: 1.6; color: var(--adm-cor-texto-principal); max-height: 400px; overflow-y: auto; }
        .adm-competencias-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; margin-top: 15px; }
        .adm-comp-card { background: var(--adm-fundo-pagina); padding: 12px; border-radius: 8px; border: 1px solid var(--adm-cor-borda); text-align: center; }
        .adm-comp-titulo { font-size: 12px; color: var(--adm-cor-texto-secundario); font-weight: 600; margin-bottom: 5px; }
        .adm-comp-nota { font-size: 18px; font-weight: 800; color: var(--adm-cor-marrom); }
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
                <a href="javascript:history.back()" style="text-decoration: none; color: var(--adm-cor-laranja); font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
                    <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Voltar
                </a>
            </div>

            <header class="adm-cabecalho-pagina">
                <div>
                    <h1 class="adm-titulo-pagina"><?php echo htmlspecialchars($proposta['titulo']); ?></h1>
                    <p class="adm-subtitulo-pagina">Consulta de Proposta Arquivada e Histórico de Envio</p>
                </div>
            </header>

            <div class="adm-detalhes-grid">
                <!-- COLUNA DA ESQUERDA: INFORMAÇÕES DA PROPOSTA -->
                <section class="adm-cartao-secao">
                    <h2 class="adm-titulo-secao">Instruções e Texto de Apoio</h2>
                    
                    <div style="margin-bottom: 20px;">
                        <strong style="font-size: 13px; color: var(--adm-cor-texto-secundario);">INSTRUÇÕES:</strong>
                        <div class="adm-bloco-texto" style="margin-top: 5px;"><?php echo !empty($proposta['instrucoes']) ? htmlspecialchars($proposta['instrucoes']) : 'Nenhuma instrução informada.'; ?></div>
                    </div>

                    <div>
                        <strong style="font-size: 13px; color: var(--adm-cor-texto-secundario);">TEXTO DE APOIO:</strong>
                        <div class="adm-bloco-texto" style="margin-top: 5px;"><?php echo !empty($proposta['texto_apoio']) ? htmlspecialchars($proposta['texto_apoio']) : 'Nenhum texto de apoio informado.'; ?></div>
                    </div>
                </section>

                <!-- COLUNA DA DIREITA: REDAÇÃO ENVIADA E CORREÇÃO -->
                <div style="display: flex; flex-direction: column; gap: 25px;">
                    <section class="adm-cartao-secao">
                        <h2 class="adm-titulo-secao">Sua Redação Enviada</h2>
                        <?php if ($redacao): ?>
                            <div style="font-size: 12px; color: var(--adm-cor-texto-secundario); margin-bottom: 8px;">
                                Enviado em: <strong><?php echo date('d/m/Y - H:i', strtotime($redacao['enviado_em'])); ?></strong>
                            </div>
                            <div class="adm-bloco-texto"><?php echo htmlspecialchars($redacao['texto_redacao']); ?></div>
                        <?php else: ?>
                            <div style="text-align: center; color: var(--adm-cor-texto-secundario); padding: 30px 0;">
                                Você não enviou redação para esta proposta.
                            </div>
                        <?php endif; ?>
                    </section>

                    <?php if ($correcao): ?>
                        <section class="adm-cartao-secao">
                            <h2 class="adm-titulo-secao" style="display: flex; justify-content: space-between; align-items: center;">
                                Avaliação e Correção
                                <span style="background: var(--adm-cor-verde); color: #fff; font-size: 14px; padding: 4px 12px; border-radius: 6px;">Nota Total: <?php echo $correcao['nota_total']; ?></span>
                            </h2>

                            <div class="adm-competencias-grid">
                                <div class="adm-comp-card">
                                    <div class="adm-comp-titulo">Competência 1</div>
                                    <div class="adm-comp-nota"><?php echo $correcao['nota_c1']; ?> / 200</div>
                                </div>
                                <div class="adm-comp-card">
                                    <div class="adm-comp-titulo">Competência 2</div>
                                    <div class="adm-comp-nota"><?php echo $correcao['nota_c2']; ?> / 200</div>
                                </div>
                                <div class="adm-comp-card">
                                    <div class="adm-comp-titulo">Competência 3</div>
                                    <div class="adm-comp-nota"><?php echo $correcao['nota_c3']; ?> / 200</div>
                                </div>
                                <div class="adm-comp-card">
                                    <div class="adm-comp-titulo">Competência 4</div>
                                    <div class="adm-comp-nota"><?php echo $correcao['nota_c4']; ?> / 200</div>
                                </div>
                                <div class="adm-comp-card" style="grid-column: 1 / -1;">
                                    <div class="adm-comp-titulo">Competência 5</div>
                                    <div class="adm-comp-nota"><?php echo $correcao['nota_c5']; ?> / 200</div>
                                </div>
                            </div>

                            <?php if (!empty($correcao['comentario'])): ?>
                                <div style="margin-top: 20px;">
                                    <strong style="font-size: 13px; color: var(--adm-cor-texto-secundario);">COMENTÁRIO DO CORRETOR:</strong>
                                    <div class="adm-bloco-texto" style="margin-top: 5px;"><?php echo htmlspecialchars($correcao['comentario']); ?></div>
                                </div>
                            <?php endif; ?>
                        </section>
                    <?php elseif ($redacao): ?>
                        <section class="adm-cartao-secao" style="text-align: center; padding: 20px;">
                            <p style="color: var(--adm-cor-texto-secundario); font-size: 14px;">Redação enviada, aguardando correção do professor/corretor.</p>
                        </section>
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