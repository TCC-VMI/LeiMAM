<?php
require_once '2-funcoes-historico-aluno.php';

// Captura parâmetros de retorno ou define origem padrão
$origem_retorno = $_SERVER['HTTP_REFERER'] ?? '2-propostas-administrador.php';
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
                    <li><a href="2-painel-administrador.php" class="adm-menu-item-link adm-ativo">Início</a></li>
                    <li><a href="2-propostas-administrador.php" class="adm-menu-item-link">Propostas</a></li>
                    <li><a href="2-calendario-administrador.php" class="adm-menu-item-link">Calendário</a></li>
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
            
            <div class="adm-navegacao-voltar" style="margin-bottom: 20px;">
                <a href="<?php echo htmlspecialchars($origem_retorno); ?>" class="adm-link-voltar" style="color: var(--adm-cor-laranja); text-decoration: none; font-weight: 700;">← Voltar</a>
            </div>

            <header class="adm-cabecalho-detalhe-proposta" style="margin-bottom: 25px;">
                <h1 class="adm-titulo-pagina">Histórico de Redações</h1>
                <p class="adm-subtitulo-pagina">
                    Aluno(a): <strong><?php echo htmlspecialchars($aluno['nome']); ?></strong> | 
                    Turma: <strong><?php echo $aluno['serie'] . '° ' . $aluno['turma'] . ' - ' . strtoupper($aluno['curso']); ?></strong>
                </p>
            </header>

            <section class="adm-cartao-secao">
                <h2 class="adm-titulo-secao" style="margin-bottom: 20px;">Redações Enviadas (<?php echo count($historico); ?>)</h2>

                <?php if (empty($historico)): ?>
                    <p style="text-align: center; color: var(--adm-cor-texto-secundario); padding: 30px;">Este aluno ainda não enviou nenhuma redação.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <?php foreach ($historico as $item): ?>
                            <?php $tem_correcao = !empty($item['id_correcao']); ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; background: var(--adm-fundo-pagina); padding: 15px 20px; border-radius: 8px; border: 1px solid var(--adm-cor-borda); flex-wrap: wrap; gap: 10px;">
                                
                                <div>
                                    <h3 style="font-size: 15px; color: var(--adm-cor-marrom); margin-bottom: 4px;">
                                        <?php echo htmlspecialchars($item['titulo_proposta']); ?>
                                    </h3>
                                    <p style="font-size: 12px; color: var(--adm-cor-texto-secundario);">
                                        Enviado em: <strong><?php echo date('d/m/Y \à\s H:i', strtotime($item['enviado_em'])); ?></strong>
                                    </p>
                                </div>

                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <?php if ($tem_correcao): ?>
                                        <span style="background: #e8f5e9; color: #2e7d32; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;">
                                            Nota: <?php echo $item['nota_total']; ?> / 1000
                                        </span>
                                        <a href="2-visualizar-redacao.php?id=<?php echo $item['id_redacao']; ?>&origem=historico" class="adm-botao-acao-tabela" style="background: var(--adm-cor-verde); color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600;">
                                            Ver Correção
                                        </a>
                                    <?php else: ?>
                                        <span style="background: #fff3e0; color: #e65100; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;">
                                            Aguardando Correção
                                        </span>
                                        <a href="2-corrigir-redacao.php?id=<?php echo $item['id_redacao']; ?>" class="adm-botao-acao-tabela" style="background: var(--adm-cor-laranja); color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600;">
                                            Corrigir Agora
                                        </a>
                                    <?php endif; ?>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
<?php 
if (isset($conn) && $conn) {
    $conn->close(); 
}
?>