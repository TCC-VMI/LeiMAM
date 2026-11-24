<?php
require_once '3-funcoes-visualizar-redacao.php';

// Captura parâmetros de retorno para gerenciar o menu e evitar loops no botão Voltar
$origem = $_GET['origem'] ?? '';
$id_prop = $_GET['id_proposta'] ?? '';
$origem_retorno_custom = $_GET['origem_retorno'] ?? '';

if ($origem === 'detalhes' && !empty($id_prop)) {
    $link_voltar = "3-detalhes-proposta.php?id=" . $id_prop;
    $menu_ativo = 'propostas';
} elseif (!empty($origem_retorno_custom)) {
    $link_voltar = $origem_retorno_custom;
    $menu_ativo = 'propostas';
} else {
    $origem_retorno = $_SERVER['HTTP_REFERER'] ?? '3-propostas-professor.php';
    $link_voltar = $origem_retorno;
    $arquivo_origem = basename(parse_url($origem_retorno, PHP_URL_PATH));

    if (strpos($arquivo_origem, 'proposta') !== false) {
        $menu_ativo = 'propostas';
    } elseif (strpos($arquivo_origem, 'calendario') !== false) {
        $menu_ativo = 'calendario';
    } elseif (strpos($arquivo_origem, 'favorito') !== false) {
        $menu_ativo = 'favoritos';
    } elseif (strpos($arquivo_origem, 'arquivado') !== false) {
        $menu_ativo = 'arquivados';
    } else {
        $menu_ativo = 'inicio';
    }
}
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
        .modal-icone {
            color: #ef4444; margin-bottom: 12px;
        }
        .modal-titulo { font-size: 1.25rem; margin-bottom: 8px; color: #1e293b; font-weight: 700; }
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
        .btn-excluir-cabecalho {
            background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5;
            padding: 8px 14px; border-radius: 6px; cursor: pointer;
            display: inline-flex; align-items: center; gap: 6px; font-weight: 600;
            transition: background 0.2s;
        }
        .btn-excluir-cabecalho:hover { background: #fca5a5; color: #991b1b; }
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
            <a href="3-painel-professor.php" class="adm-logo-area">
                <img src="LOGO PNG - LeiMAM.png" alt="Logo LeiMAM" class="adm-logo-imagem">
                <div class="adm-logo-textos-grupo">
                    <span class="adm-logo-texto">Lei<span>MAM</span></span>
                    <span class="adm-subtitulo-logo">Portal do Corretor</span>
                </div>
            </a>

            <nav class="adm-nav-container">
                <ul class="adm-menu-lista">
                    <li><a href="3-painel-professor.php" class="adm-menu-item-link <?php echo ($menu_ativo === 'inicio') ? 'adm-ativo' : ''; ?>">Início</a></li>
                    <li><a href="3-propostas-professor.php" class="adm-menu-item-link <?php echo ($menu_ativo === 'propostas') ? 'adm-ativo' : ''; ?>">Propostas</a></li>
                    <li><a href="3-calendario-professor.php" class="adm-menu-item-link <?php echo ($menu_ativo === 'calendario') ? 'adm-ativo' : ''; ?>">Calendário</a></li>
                    <li><a href="3-favoritos.php" class="adm-menu-item-link <?php echo ($menu_ativo === 'favoritos') ? 'adm-ativo' : ''; ?>">Favoritos</a></li>
                    <li><a href="3-arquivados.php" class="adm-menu-item-link <?php echo ($menu_ativo === 'arquivados') ? 'adm-ativo' : ''; ?>">Arquivados</a></li>
                </ul>

                <div class="adm-menu-rodape">
                    <a href="index.html" class="adm-menu-item-link adm-item-sair">
                        <i data-lucide="arrow-left"></i> Sair
                    </a>
                </div>
            </nav>
        </aside>

        <main class="adm-area-conteudo">
            
            <div class="adm-navegacao-voltar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                <a href="<?php echo htmlspecialchars($link_voltar); ?>" class="adm-link-voltar" style="color: var(--adm-cor-laranja); text-decoration: none; font-weight: 700;">← Voltar</a>
                
                <?php if ($tem_correcao): ?>
                    <div style="display: flex; gap: 8px;">
                        <a href="3-corrigir-redacao.php?id=<?php echo $id_redacao; ?>&origem=visualizar&id_proposta=<?php echo $dados['id_proposta']; ?>" class="adm-botao-acao-tabela" style="background: var(--adm-cor-laranja); color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                            <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i> Editar Correção
                        </a>
                        <button type="button" onclick="abrirModalExclusao()" class="btn-excluir-cabecalho">
                            <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i> Excluir Correção
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <header class="adm-cabecalho-detalhe-proposta" style="margin-bottom: 25px;">
                <h1 class="adm-titulo-pagina">Correção: <?php echo htmlspecialchars($dados['titulo_proposta']); ?></h1>
                <p class="adm-subtitulo-pagina">
                    Aluno(a): <strong><?php echo htmlspecialchars($dados['nome_aluno']); ?></strong> | 
                    Turma: <strong><?php echo $dados['serie'] . '° ' . $dados['turma'] . ' ' . strtoupper($dados['curso']); ?></strong><br>
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
                        Esta redação ainda não foi avaliada. Você pode ir para a página de correção clicando no botão abaixo.
                    </p>
                    <a href="3-corrigir-redacao.php?id=<?php echo $id_redacao; ?>" class="adm-botao-acao-tabela adm-btn-corrigir" style="margin-top: 15px; display: inline-block; background: var(--adm-cor-laranja); color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold;">Corrigir Agora</a>
                </section>
            <?php else: ?>
                <section class="adm-cartao-secao" style="margin-bottom: 25px; text-align: center; background: linear-gradient(135deg, #fff 0%, #f9fbf0 100%);">
                    <h2 class="adm-indicador-titulo" style="font-size: 15px; margin-bottom: 5px;">Nota Total Obtida</h2>
                    <div style="font-size: 48px; font-weight: 800; color: var(--adm-cor-amarelo);">
                        <?php echo htmlspecialchars($dados['nota_total']); ?> <span style="font-size: 20px; color: var(--adm-cor-texto-secundario);">/ 1000</span>
                    </div>
                </section>

                <section class="adm-cartao-secao" style="margin-bottom: 25px;">
                    <h2 class="adm-titulo-secao" style="margin-bottom: 15px;">Notas por Competência</h2>
                    
                    <?php 
                    $competencias = [
                        ['nome' => 'Competência 1: Domínio da norma culta', 'nota' => $dados['nota_c1']],
                        ['nome' => 'Competência 2: Compreensão do tema e repertório', 'nota' => $dados['nota_c2']],
                        ['nome' => 'Competência 3: Seleção e interpretação de informações', 'nota' => $dados['nota_c3']],
                        ['nome' => 'Competência 4: Conhecimento dos mecanismos linguísticos', 'nota' => $dados['nota_c4']],
                        ['nome' => 'Competência 5: Elaboração de proposta de intervenção', 'nota' => $dados['nota_c5']]
                    ];
                    ?>

                    <div class="adm-lista-competencias">
                        <?php foreach ($competencias as $comp): ?>
                            <?php 
                                $val = intval($comp['nota']);
                                $pct = ($val / 200) * 100; 
                            ?>
                            <div class="adm-competencia-item" style="margin-bottom: 15px;">
                                <div class="adm-competencia-info" style="display: flex; justify-content: space-between; font-weight: 600; font-size: 13px; color: var(--adm-cor-marrom);">
                                    <span><?php echo $comp['nome']; ?></span>
                                    <span><strong><?php echo $val; ?></strong> / 200</span>
                                </div>
                                <div class="adm-barra-progresso-fundo" style="width: 100%; height: 6px; background-color: var(--adm-fundo-pagina); border-radius: 3px; overflow: hidden; margin-top: 6px;">
                                    <div class="adm-barra-progresso-preenchimento" style="width: <?php echo $pct; ?>%; height: 100%; background-color: var(--adm-cor-amarelo);"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="adm-cartao-secao" style="margin-bottom: 25px;">
                    <h2 class="adm-titulo-secao" style="margin-bottom: 12px;">Comentário / Feedback do Corretor</h2>
                    <div style="background-color: var(--adm-fundo-pagina); padding: 18px; border-radius: 8px; border: 1px solid var(--adm-cor-borda); font-size: 14px; line-height: 1.6; color: var(--adm-cor-texto-principal);">
                        <?php echo !empty($dados['comentario']) ? nl2br(htmlspecialchars($dados['comentario'])) : '<span style="color: var(--adm-cor-texto-secundario);">Nenhum comentário textual registrado para esta correção.</span>'; ?>
                    </div>
                </section>
            <?php endif; ?>

            <section class="adm-cartao-secao">
                <h2 class="adm-titulo-secao" style="margin-bottom: 15px;">Redação Enviada pelo Aluno</h2>
                <?php 
                    $url_foto = $dados['url_foto'] ?? '';
                    if (!empty($url_foto) && !str_starts_with($url_foto, 'uploads/')) {
                        $url_foto = "uploads/" . $url_foto;
                    }
                    $extensao = strtolower(pathinfo($url_foto, PATHINFO_EXTENSION));
                ?>
                <div style="text-align: center; background: var(--adm-fundo-pagina); padding: 15px; border-radius: 8px; border: 1px solid var(--adm-cor-borda);">
                    <?php if (!empty($url_foto)): ?>
                        <?php if ($extensao === 'pdf'): ?>
                            <iframe src="<?php echo htmlspecialchars($url_foto); ?>" width="100%" height="600px" style="border: 1px solid var(--adm-cor-borda); border-radius: 6px;">
                                Seu navegador não suporta a exibição de PDFs. <a href="<?php echo htmlspecialchars($url_foto); ?>" target="_blank">Clique aqui para abrir o PDF.</a>
                            </iframe>
                        <?php elseif (in_array($extensao, ['jpg', 'jpeg', 'png', 'webp'])): ?>
                            <img src="<?php echo htmlspecialchars($url_foto); ?>" alt="Redação do Aluno" style="max-width: 100%; max-height: 600px; border-radius: 6px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars($url_foto); ?>" target="_blank" class="adm-botao-criar-turma">Abrir Arquivo Enviado</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <p style="color: var(--adm-cor-texto-secundario);">Nenhum arquivo anexado.</p>
                    <?php endif; ?>
                </div>
            </section>

        </main>
    </div>

    <div class="modal-mascara" id="modalExclusao">
        <div class="modal-conteudo">
            <div class="modal-icone">
                <i data-lucide="alert-triangle" style="width: 48px; height: 48px;"></i>
            </div>
            <h3 class="modal-titulo">Tem certeza absoluta?</h3>
            <p class="modal-texto">
                Você está prestes a excluir a correção da redação do aluno(a) <strong><?php echo htmlspecialchars($dados['nome_aluno']); ?></strong>.<br>
                Esta ação removerá permanentemente os dados da correção do banco de dados e não poderá ser desfeita.
            </p>
            <form action="3-visualizar-redacao.php?id=<?php echo $id_redacao; ?><?php echo !empty($origem) ? '&origem='.$origem : ''; ?><?php echo !empty($id_prop) ? '&id_proposta='.$id_prop : ''; ?>" method="POST">
                <input type="hidden" name="acao" value="apagar_correcao">
                <input type="hidden" name="id_redacao" value="<?php echo $id_redacao; ?>">
                
                <div class="modal-acoes">
                    <button type="button" class="btn-modal-cancelar" onclick="fecharModalExclusao()">Cancelar</button>
                    <button type="submit" class="btn-modal-excluir">Sim, Excluir Correção</button>
                </div>
            </form>
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

        function abrirModalExclusao() {
            document.getElementById('modalExclusao').classList.add('ativo');
        }

        function fecharModalExclusao() {
            document.getElementById('modalExclusao').classList.remove('ativo');
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
<?php 
if (isset($conn) && $conn) {
    $conn->close(); 
}
?>