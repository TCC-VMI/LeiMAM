<?php
require_once '2-funcoes-corrigir-redacao.php';

// Captura a página de origem (ex: 2-propostas-administrador.php)
$origem_retorno = $_SERVER['HTTP_REFERER'] ?? '2-painel-administrador.php';
$arquivo_origem = basename(parse_url($origem_retorno, PHP_URL_PATH));

// Identifica automaticamente qual item do menu deve ficar ativo
$menu_ativo = 'inicio'; // padrão

if (strpos($arquivo_origem, 'proposta') !== false) {
    $menu_ativo = 'propostas';
} elseif (strpos($arquivo_origem, 'calendario') !== false) {
    $menu_ativo = 'calendario';
} elseif (strpos($arquivo_origem, 'favorito') !== false) {
    $menu_ativo = 'favoritos';
} elseif (strpos($arquivo_origem, 'arquivado') !== false) {
    $menu_ativo = 'arquivados';
} elseif (strpos($arquivo_origem, 'corretor') !== false) {
    $menu_ativo = 'corretores';
} else {
    $menu_ativo = 'inicio';
}
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
        .adm-input-nota-num {
            width: 75px;
            padding: 4px 8px;
            font-weight: 700;
            font-size: 13px;
            border-radius: 6px;
            border: 1px solid var(--adm-cor-borda);
            background-color: var(--adm-fundo-cartao);
            color: var(--adm-cor-marrom);
            text-align: center;
            outline: none;
        }
        .adm-input-nota-num:focus {
            border-color: var(--adm-cor-laranja);
        }
        .adm-barra-progresso-competencia {
            width: 100%;
            height: 5px;
            background-color: var(--adm-fundo-cartao);
            border-radius: 3px;
            overflow: hidden;
            margin-top: 6px;
        }
        .adm-progresso-fill {
            height: 100%;
            background-color: var(--adm-cor-verde);
            width: 0%;
            transition: width 0.3s ease;
        }
        .adm-textarea-feedback {
            width: 100%;
            min-height: 110px;
            padding: 10px;
            border: 1px solid var(--adm-cor-borda);
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            background-color: var(--adm-fundo-cartao);
            color: var(--adm-cor-texto-principal);
            outline: none;
            resize: vertical;
        }
        .adm-textarea-feedback:focus {
            border-color: var(--adm-cor-laranja);
        }
        .grade-competencia-box {
            margin-bottom: 10px !important;
            padding: 10px 14px !important;
            background: var(--adm-fundo-pagina);
            border: 1px solid var(--adm-cor-borda);
            border-radius: 8px;
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
                    <li><a href="2-painel-administrador.php" class="adm-menu-item-link <?php echo ($menu_ativo === 'inicio') ? 'adm-ativo' : ''; ?>">Início</a></li>
                    <li><a href="2-propostas-administrador.php" class="adm-menu-item-link <?php echo ($menu_ativo === 'propostas') ? 'adm-ativo' : ''; ?>">Propostas</a></li>
                    <li><a href="2-calendario-administrador.php" class="adm-menu-item-link <?php echo ($menu_ativo === 'calendario') ? 'adm-ativo' : ''; ?>">Calendário</a></li>
                    <li><a href="2-corretores.php" class="adm-menu-item-link <?php echo ($menu_ativo === 'corretores') ? 'adm-ativo' : ''; ?>">Corretores</a></li>
                    <li><a href="2-favoritos.php" class="adm-menu-item-link <?php echo ($menu_ativo === 'favoritos') ? 'adm-ativo' : ''; ?>">Favoritos</a></li>
                    <li><a href="2-arquivados.php" class="adm-menu-item-link <?php echo ($menu_ativo === 'arquivados') ? 'adm-ativo' : ''; ?>">Arquivados</a></li>
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
                <a href="<?php echo htmlspecialchars($origem_retorno); ?>" class="adm-link-voltar">
                    <i data-lucide="arrow-left" style="width: 16px; height: 16px; vertical-align: middle;"></i> Voltar
                </a>
            </div>

            <header class="adm-cabecalho-pagina" style="margin-bottom: 15px;">
                <div>
                    <h1 class="adm-titulo-pagina">Avaliação de Redação</h1>
                    <p class="adm-subtitulo-pagina">
                        Aluno(a): <strong><?php echo htmlspecialchars($dados_redacao['nome_aluno']); ?></strong> | 
                        Turma: <strong><?php echo $dados_redacao['serie'] . '° ' . $dados_redacao['turma'] . ' - ' . strtoupper($dados_redacao['curso']); ?></strong>
                    </p>
                </div>
            </header>

            <?php if (!empty($mensagem_erro)): ?>
                <div class="adm-alerta-erro"><?php echo $mensagem_erro; ?></div>
            <?php endif; ?>

            <form action="2-corrigir-redacao.php?id=<?php echo $id_redacao; ?>" method="POST">
                <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($origem_retorno); ?>">

                <div class="adm-grid-duplo" style="align-items: flex-start;">
                    
                    <!-- COLUNA ESQUERDA: VISUALIZADOR DA REDAÇÃO -->
                    <section class="adm-cartao-secao">
                        <div class="adm-cabecalho-visualizador-redacao">
                            <h2 class="adm-titulo-secao" style="margin-bottom: 0; font-size: 16px;"><i data-lucide="file-text" style="width: 16px; height: 16px;"></i> Arquivo Enviado</h2>
                        </div>
                        <p style="font-size: 12px; color: var(--adm-cor-texto-secundario); margin: 6px 0 10px 0;">
                            Proposta: <strong><?php echo htmlspecialchars($dados_redacao['titulo_proposta']); ?></strong> <br>
                            Enviado em: <strong><?php echo date('d/m/Y \à\s H:i', strtotime($dados_redacao['enviado_em'])); ?></strong>
                        </p>
                        
                        <div class="adm-caixa-visualizacao-arquivo">
                            <?php if (!empty($dados_redacao['url_foto'])): ?>
                                <?php 
                                    $url_foto = $dados_redacao['url_foto'];
                                    if (!str_starts_with($url_foto, 'uploads/')) {
                                        $url_foto = "uploads/" . $url_foto;
                                    }
                                    $extensao = strtolower(pathinfo($url_foto, PATHINFO_EXTENSION));
                                ?>

                                <?php if ($extensao === 'pdf'): ?>
                                    <iframe src="<?php echo htmlspecialchars($url_foto); ?>" width="100%" height="600px" style="border: 1px solid var(--adm-cor-borda); border-radius: 6px;">
                                        Seu navegador não suporta a exibição de PDFs. <a href="<?php echo htmlspecialchars($url_foto); ?>" target="_blank">Clique aqui para abrir o PDF.</a>
                                    </iframe>
                                <?php elseif (in_array($extensao, ['jpg', 'jpeg', 'png', 'webp'])): ?>
                                    <img src="<?php echo htmlspecialchars($url_foto); ?>" alt="Redação do Aluno" style="max-width: 100%; height: auto; border-radius: 6px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                <?php else: ?>
                                    <a href="<?php echo htmlspecialchars($url_foto); ?>" target="_blank" class="adm-botao-criar-turma">Abrir Arquivo Enviado</a>
                                <?php endif; ?>

                            <?php else: ?>
                                <p style="text-align: center; color: var(--adm-cor-texto-secundario); padding-top: 180px;">Nenhum arquivo associado.</p>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- COLUNA DIREITA: PAINEL DE AVALIAÇÃO INTEGRADO -->
                    <section class="adm-cartao-secao" style="padding: 15px;">
                        
                        <!-- BLOCO DA NOTA TOTAL -->
                        <section class="adm-cartao-secao" style="margin-bottom: 15px; padding: 12px; text-align: center; background: linear-gradient(135deg, #fff 0%, #f4fbf0 100%);">
                            <span class="adm-indicador-titulo" style="font-size: 12px; margin-bottom: 2px; display: block;">Nota Total Obtida</span>
                            <div style="font-size: 36px; font-weight: 800; color: #2e7d32; line-height: 1.1;">
                                <span id="exibeNotaNumerica"><?php echo isset($dados_redacao['nota_total']) ? $dados_redacao['nota_total'] : '0'; ?></span> 
                                <span style="font-size: 16px; color: var(--adm-cor-texto-secundario);">/ 1000</span>
                            </div>
                        </section>

                        <h2 class="adm-titulo-secao" style="font-size: 16px; margin-bottom: 10px;">Grade de Avaliação</h2>

                        <?php 
                        $competencias = [
                            ['id' => 1, 'nome' => 'Competência 1 (Norma Culta)', 'desc' => 'Domínio da modalidade escrita formal.'],
                            ['id' => 2, 'nome' => 'Competência 2 (Tema e Repertório)', 'desc' => 'Compreensão do tema e áreas do conhecimento.'],
                            ['id' => 3, 'nome' => 'Competência 3 (Argumentação)', 'desc' => 'Seleção e interpretação de informações.'],
                            ['id' => 4, 'nome' => 'Competência 4 (Coesão)', 'desc' => 'Mecanismos linguísticos de argumentação.'],
                            ['id' => 5, 'nome' => 'Competência 5 (Intervenção)', 'desc' => 'Proposta de intervenção e direitos humanos.']
                        ];

                        foreach ($competencias as $comp): 
                            $val_atual = isset($dados_redacao['nota_c' . $comp['id']]) ? $dados_redacao['nota_c' . $comp['id']] : 0;
                            $pct_atual = ($val_atual / 200) * 100;
                        ?>
                            <div class="grade-competencia-box" title="<?php echo $comp['desc']; ?>">
                                <div class="grade-competencia-titulo" style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-weight: 700; color: var(--adm-cor-marrom); font-size: 13px;"><?php echo $comp['nome']; ?></span>
                                    <div>
                                        <input type="number" name="nota_c<?php echo $comp['id']; ?>" value="<?php echo $val_atual; ?>" min="0" max="200" step="40" class="nota-competencia adm-input-nota-num">
                                        <span style="font-size: 12px; color: var(--adm-cor-texto-secundario); font-weight: 600; margin-left: 2px;">/200</span>
                                    </div>
                                </div>
                                <div class="adm-barra-progresso-competencia">
                                    <div class="adm-progresso-fill" id="prog_c<?php echo $comp['id']; ?>" style="width: <?php echo $pct_atual; ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- COMENTÁRIOS E FEEDBACK -->
                        <div style="margin-top: 15px;">
                            <h2 class="adm-titulo-secao" style="font-size: 16px; margin-bottom: 8px;">Comentário Pedagógico</h2>
                            <textarea name="comentario" class="adm-textarea-feedback" placeholder="Escreva o feedback detalhado..."><?php echo isset($dados_redacao['comentario']) ? htmlspecialchars($dados_redacao['comentario']) : ''; ?></textarea>
                        </div>

                        <!-- AÇÕES FINAIS (FAVORITAR + PUBLICAR) -->
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--adm-cor-borda); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 13px; font-weight: 600; color: var(--adm-cor-marrom);">
                                <input type="checkbox" name="favoritar" value="1" <?php echo $is_favorito ? 'checked' : ''; ?> style="width: 16px; height: 16px; accent-color: var(--adm-cor-laranja); cursor: pointer;"> Favoritar
                            </label>

                            <button type="submit" class="adm-botao-acao-tabela adm-btn-corrigir" style="background: var(--adm-cor-laranja); color: white; padding: 10px 18px; font-size: 14px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                Salvar e Publicar Correção
                            </button>
                        </div>

                    </section>

                </div>
            </form>
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

        const inputsNotas = document.querySelectorAll('.nota-competencia');
        const elementoExibeNotaNumerica = document.getElementById('exibeNotaNumerica');

        function calcularNotaTotal() {
            let total = 0;
            inputsNotas.forEach(input => {
                let valor = parseInt(input.value) || 0;
                if (valor < 0) valor = 0;
                if (valor > 200) valor = 200;
                total += valor;

                const idInput = input.getAttribute('name');
                const compNum = idInput.replace('nota_c', '');
                const barraFill = document.getElementById('prog_c' + compNum);
                if (barraFill) {
                    const pct = (valor / 200) * 100;
                    barraFill.style.width = pct + '%';
                }
            });
            elementoExibeNotaNumerica.textContent = total;
        }

        inputsNotas.forEach(input => {
            input.addEventListener('input', calcularNotaTotal);
            input.addEventListener('change', calcularNotaTotal);
        });

        calcularNotaTotal();

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