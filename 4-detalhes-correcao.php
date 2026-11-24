<?php
require_once '4-funcoes-detalhes-correcao.php';
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

    <div class="adm-barras-mob">
        <button class="adm-botao-menu" id="admBotaoMenu" onclick="alternarMenu()">
            ☰
        </button>
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
                    <li><a href="4-propostas-aluno.php" class="adm-menu-item-link adm-ativo">Propostas</a></li>
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

        <main class="adm-area-conteudo">
            
            <!-- Botão de Voltar para os Detalhes da Proposta -->
            <div class="adm-navegacao-voltar" style="margin-bottom: 20px;">
                <a href="4-detalhes-proposta.php?id=<?php echo $dados['id_proposta']; ?>" class="adm-link-voltar" style="color: var(--adm-cor-laranja); text-decoration: none; font-weight: 700;">← Voltar</a>
            </div>

            <header class="adm-cabecalho-detalhe-proposta" style="margin-bottom: 25px;">
                <h1 class="adm-titulo-pagina">Correção: <?php echo htmlspecialchars($dados['titulo_proposta']); ?></h1>
                <p class="adm-subtitulo-pagina">
                    Enviado em: <strong><?php echo date('d/m/Y \à\s H:i', strtotime($dados['enviado_em'])); ?></strong>
                    <?php if ($tem_correcao && !empty($dados['corrigido_em'])): ?>
                        | Corrigido em: <strong><?php echo date('d/m/Y \à\s H:i', strtotime($dados['corrigido_em'])); ?></strong>
                    <?php endif; ?>
                </p>
            </header>

            <?php if (!$tem_correcao): ?>
                <section class="adm-cartao-secao" style="text-align: center; padding: 40px; margin-bottom: 25px;">
                    <h2 class="adm-titulo-secao" style="color: #1976d2; margin-bottom: 10px;">Aguardando Correção</h2>
                    <p style="color: var(--adm-cor-texto-secundario); font-size: 15px;">
                        Sua redação foi enviada com sucesso, mas ainda não foi avaliada pelo professor. Assim que a correção for concluída, as notas e os comentários aparecerão aqui.
                    </p>
                </section>
            <?php else: ?>
                <!-- CARTÃO DE NOTA TOTAL -->
                <section class="adm-cartao-secao" style="margin-bottom: 25px; text-align: center; background: linear-gradient(135deg, #fff 0%, #fcf8f2 100%);">
                    <h2 class="adm-indicador-titulo" style="font-size: 15px; margin-bottom: 5px;">Nota Total Obtida</h2>
                    <div style="font-size: 48px; font-weight: 800; color: var(--adm-cor-laranja);">
                        <?php echo htmlspecialchars($dados['nota_total']); ?> <span style="font-size: 20px; color: var(--adm-cor-texto-secundario);">/ 1000</span>
                    </div>
                </section>

                <!-- DESEMPENHO POR COMPETÊNCIAS (C1 a C5) -->
                <section class="adm-cartao-secao" style="margin-bottom: 25px;">
                    <h2 class="adm-titulo-secao" style="margin-bottom: 15px;">Notas por Competência</h2>
                    
                    <?php 
                    $competencias = [
                        ['nome' => 'Competência 1: Domínio da norma culta', 'nota' => $dados['nota_c1']],
                        ['nome' => 'Competência 2: Compreensão do tema', 'nota' => $dados['nota_c2']],
                        ['nome' => 'Competência 3: coerência', 'nota' => $dados['nota_c3']],
                        ['nome' => 'Competência 4: Coesão', 'nota' => $dados['nota_c4']],
                        ['nome' => 'Competência 5: Proposta de intervenção ', 'nota' => $dados['nota_c5']]
                    ];
                    ?>

                    <div class="adm-lista-competencias">
                        <?php foreach ($competencias as $comp): ?>
                            <?php 
                                $val = intval($comp['nota']);
                                $pct = ($val / 200) * 100; // Cada competência vale até 200 pontos
                            ?>
                            <div class="adm-competencia-item" style="margin-bottom: 15px;">
                                <div class="adm-competencia-info">
                                    <span><?php echo $comp['nome']; ?></span>
                                    <span><strong><?php echo $val; ?></strong> / 200</span>
                                </div>
                                <div class="adm-barra-progresso-fundo" style="margin-top: 6px;">
                                    <div class="adm-barra-progresso-preenchimento" style="width: <?php echo $pct; ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- COMENTÁRIO DO CORRETOR -->
                <section class="adm-cartao-secao" style="margin-bottom: 25px;">
                    <h2 class="adm-titulo-secao" style="margin-bottom: 12px;">Comentário / Feedback do Corretor</h2>
                    <div style="background-color: var(--adm-fundo-pagina); padding: 18px; border-radius: 8px; border: 1px solid var(--adm-cor-borda); font-size: 14px; line-height: 1.6; color: var(--adm-cor-texto-principal);">
                        <?php echo !empty($dados['comentario']) ? nl2br(htmlspecialchars($dados['comentario'])) : '<span style="color: var(--adm-cor-texto-secundario);">Nenhum comentário textual registrado para esta correção.</span>'; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- VISUALIZAÇÃO DO ARQUIVO ENVIADO PELO ALUNO -->
            <section class="adm-cartao-secao">
                <h2 class="adm-titulo-secao" style="margin-bottom: 15px;">Sua Redação Enviada</h2>
                <?php 
                    $url_foto = $dados['url_foto'];
                    $extensao = strtolower(pathinfo($url_foto, PATHINFO_EXTENSION));
                ?>
                <div style="text-align: center; background: var(--adm-fundo-pagina); padding: 15px; border-radius: 8px; border: 1px solid var(--adm-cor-borda);">
                    <?php if ($extensao === 'pdf'): ?>
                        <iframe src="<?php echo htmlspecialchars($url_foto); ?>" width="100%" height="500px" style="border: 1px solid var(--adm-cor-borda); border-radius: 6px;">
                            Seu navegador não suporta a exibição de PDFs. <a href="<?php echo htmlspecialchars($url_foto); ?>" target="_blank">Clique aqui para abrir o PDF.</a>
                        </iframe>
                    <?php elseif (in_array($extensao, ['jpg', 'jpeg', 'png', 'webp'])): ?>
                        <img src="<?php echo htmlspecialchars($url_foto); ?>" alt="Redação do Aluno" style="max-width: 100%; max-height: 600px; border-radius: 6px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <?php else: ?>
                        <a href="<?php echo htmlspecialchars($url_foto); ?>" target="_blank" class="adm-botao-criar-turma">Abrir Arquivo Enviado</a>
                    <?php endif; ?>
                </div>
            </section>

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