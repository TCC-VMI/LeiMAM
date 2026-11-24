<?php
require_once '4-funcoes-detalhes-proposta.php';
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
            
            <div class="adm-navegacao-voltar" style="margin-bottom: 20px;">
                <a href="4-propostas-aluno.php" class="adm-link-voltar" style="color: var(--adm-cor-laranja); text-decoration: none; font-weight: 700;">← Voltar</a>
            </div>

            <?php if (!empty($mensagem_retorno)): ?>
                <div style="background-color: #ffebee; color: #c62828; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; border: 1px solid #ffcdd2;">
                    <?php echo htmlspecialchars($mensagem_retorno); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['sucesso'])): ?>
                <div style="background-color: #e8f8d8; color: #388e3c; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; border: 1px solid #c8e6c9;">
                    Redação enviada com sucesso!
                </div>
            <?php endif; ?>

            <!-- CABEÇALHO DA PROPOSTA -->
            <header class="adm-cabecalho-detalhe-proposta" style="margin-bottom: 25px;">
                <h1 class="adm-titulo-pagina"><?php echo htmlspecialchars($proposta['titulo']); ?></h1>
                <p class="adm-subtitulo-pagina">
                    Prazo de Entrega: <strong><?php echo !empty($proposta['prazo_entrega']) ? date('d/m/Y \à\s H:i', strtotime($proposta['prazo_entrega'])) : 'Sem prazo'; ?></strong>
                </p>
            </header>

            <!-- BOTÃO PARA BAIXAR FOLHA OFICIAL -->
            <div style="margin-bottom: 25px;">
                <a href="4-detalhes-proposta.php?id=<?php echo $id_proposta; ?>&acao=baixar_folha" class="adm-botao-criar-turma" style="display: inline-flex; align-items: center; gap: 8px;">
                    <i data-lucide="download"></i> Baixar Folha de Redação Oficial (PDF)
                </a>
            </div>

            <!-- INSTRUÇÕES E TEXTO DE APOIO -->
            <section class="adm-cartao-secao" style="margin-bottom: 25px;">
                <h2 class="adm-titulo-secao" style="margin-bottom: 12px;">Instruções</h2>
                <div style="font-size: 14px; line-height: 1.6; color: var(--adm-cor-texto-principal); margin-bottom: 20px;">
                    <?php echo nl2br(htmlspecialchars($proposta['instrucoes'])); ?>
                </div>

                <?php if (!empty($proposta['texto_apoio'])): ?>
                    <h3 style="font-size: 15px; font-weight: 700; color: var(--adm-cor-marrom); margin-bottom: 10px;">Texto de Apoio / Anexo</h3>
                    <div style="background-color: var(--adm-fundo-pagina); padding: 15px; border-radius: 8px; border: 1px solid var(--adm-cor-borda);">
                        <a href="<?php echo htmlspecialchars($proposta['texto_apoio']); ?>" target="_blank" style="color: var(--adm-cor-laranja); font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                            <i data-lucide="file-text" style="width: 16px; height: 16px;"></i> Visualizar Documento de Apoio
                        </a>
                    </div>
                <?php endif; ?>
            </section>

            <!-- BALÃO DE NOTA / STATUS DA SUA REDAÇÃO -->
            <section class="adm-cartao-secao" style="margin-bottom: 25px;">
                <h2 class="adm-titulo-secao" style="margin-bottom: 15px;">Sua Submissão</h2>
                <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="font-size: 13px; color: var(--adm-cor-texto-secundario); margin-bottom: 4px;">Status Atual:</p>
                        <div style="font-size: 16px; font-weight: 800; color: var(--adm-cor-marrom);">
                            <?php if (!$envio_atual): ?>
                                <span style="color: #f57c00;">Não enviada</span>
                            <?php elseif ($ja_corrigida): ?>
                                <span style="color: #388e3c;">Corrigida</span>
                            <?php else: ?>
                                <span style="color: #1976d2;">Aguardando Correção</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <p style="font-size: 13px; color: var(--adm-cor-texto-secundario); margin-bottom: 4px;">Nota:</p>
                        <div style="font-size: 24px; font-weight: 800; color: var(--adm-cor-laranja);">
                            <?php echo $ja_corrigida ? $envio_atual['nota_total'] : '-'; ?>
                        </div>
                    </div>

                    <?php if ($ja_corrigida): ?>
                        <div>
                            <a href="4-detalhes-correcao.php?id_redacao=<?php echo $envio_atual['id_redacao']; ?>" class="adm-botao-criar-turma">
                                <i data-lucide="eye" style="width: 16px; height: 16px;"></i> Ver Detalhes da Correção
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- ÁREA DE ENVIO / REENVIO DE REDAÇÃO -->
            <section class="adm-cartao-secao">
                <h2 class="adm-titulo-secao" style="margin-bottom: 15px;">Enviar Arquivo da Redação</h2>

                <?php if ($prazo_encerrado): ?>
                    <!-- Botão Cinza Desativado -->
                    <div style="text-align: center; padding: 20px;">
                        <button type="button" disabled style="background-color: #e0e0e0; color: #757575; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: not-allowed;">
                            Prazo Encerrado
                        </button>
                        <p style="font-size: 12px; color: var(--adm-cor-texto-secundario); margin-top: 8px;">O prazo de entrega para esta proposta já expirou.</p>
                    </div>
                <?php elseif ($ja_corrigida): ?>
                    <!-- Mensagem informando que foi corrigida e travada -->
                    <div style="background-color: #f5f5f5; padding: 15px; border-radius: 8px; text-align: center; color: var(--adm-cor-texto-secundario); font-weight: 600; font-size: 14px;">
                        Esta redação já foi avaliada e corrigida pelo professor. Não é permitido realizar novos envios.
                    </div>
                <?php else: ?>
                    <!-- Formulário de Envio / Substituição -->
                    <form action="4-detalhes-proposta.php?id=<?php echo $id_proposta; ?>" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 15px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: var(--adm-cor-texto-secundario); margin-bottom: 6px;">
                                Selecione a foto ou PDF da redação preenchida:
                            </label>
                            <input type="file" name="arquivo_redacao" accept=".jpg, .jpeg, .png, .pdf, .webp" required style="padding: 10px; border: 1px solid var(--adm-cor-borda); border-radius: 8px; width: 100%; background: var(--adm-fundo-pagina);">
                        </div>
                        <div>
                            <button type="submit" class="adm-botao-criar-turma">
                                <i data-lucide="upload" style="width: 16px; height: 16px;"></i> 
                                <?php echo $envio_atual ? 'Substituir Redação Enviada' : 'Submeter Redação'; ?>
                            </button>
                        </div>
                        <?php if ($envio_atual): ?>
                            <p style="font-size: 12px; color: #f57c00; font-weight: 600;">
                                * Atenção: Como você já havia enviado uma redação anterior, ao submeter uma nova, o envio antigo será apagado e substituído.
                            </p>
                        <?php endif; ?>
                    </form>
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