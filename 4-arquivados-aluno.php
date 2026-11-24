<?php
require_once '4-funcoes-arquivados-aluno.php';
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
        .adm-container-anos-arquivados {
            display: flex;
            flex-direction: column;
            gap: 35px;
        }

        .adm-secao-ano-arquivado {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .adm-cabecalho-ano {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--adm-cor-borda);
        }

        .adm-titulo-ano {
            font-size: 18px;
            font-weight: 800;
            color: var(--adm-cor-marrom);
        }

        .adm-qtd-turmas-ano {
            font-size: 13px;
            font-weight: 600;
            color: var(--adm-cor-texto-secundario);
        }

        .adm-grid-turmas-arquivadas {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .adm-balao-turma-arquivada {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background-color: var(--adm-fundo-cartao);
            border: 1px solid var(--adm-cor-borda);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px var(--adm-cor-sombra);
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.2s, border-color 0.2s;
            min-height: 160px;
        }

        .adm-balao-turma-arquivada:hover {
            border-color: var(--adm-cor-laranja);
            transform: translateY(-2px);
        }

        .adm-turma-arquivada-topo {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .adm-turma-arquivada-titulo {
            font-size: 16px;
            font-weight: 800;
            color: var(--adm-cor-marrom);
            line-height: 1.3;
            margin-bottom: 16px;
        }

        .adm-turma-arquivada-detalhes {
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 13px;
            color: var(--adm-cor-texto-principal);
            padding-top: 12px;
            border-top: 1px solid var(--adm-fundo-pagina);
            margin-top: auto;
        }

        .adm-turma-arquivada-detalhes p {
            display: flex;
            justify-content: space-between;
        }

        .adm-turma-arquivada-detalhes p span {
            color: var(--adm-cor-texto-secundario);
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
            <header class="adm-cabecalho-pagina">
                <div>
                    <h1 class="adm-titulo-pagina">Turmas Arquivadas</h1>
                    <p class="adm-subtitulo-pagina">Consulte o seu histórico acadêmico de anos letivos anteriores.</p>
                </div>
            </header>

            <div class="adm-container-anos-arquivados" style="margin-top: 20px;">
                <?php if (empty($turmas_arquivadas_historico)): ?>
                    <div style="background-color: var(--adm-fundo-cartao); border: 1px solid var(--adm-cor-borda); border-radius: 12px; padding: 30px; text-align: center;">
                        <p style="color: var(--adm-cor-texto-secundario); font-size: 0.95rem;">Nenhum registro de turma anterior encontrado em seu histórico.</p>
                    </div>
                <?php else: ?>
                    <div class="adm-grid-turmas-arquivadas">
                        <?php foreach ($turmas_arquivadas_historico as $hist): ?>
                            <a href="4-turma-arquivada.php?id=<?php echo $hist['id_aluno']; ?>&ano=<?php echo $hist['ano']; ?>" class="adm-balao-turma-arquivada">
                                <div class="adm-turma-arquivada-topo">
                                    <span class="adm-tag-categoria" style="background-color: var(--adm-cor-laranja); color: #fff; font-size: 11px; padding: 4px 10px; border-radius: 6px; font-weight: 700;">Ano Letivo <?php echo $hist['ano']; ?></span>
                                </div>
                                <h3 class="adm-turma-arquivada-titulo"><?php echo htmlspecialchars($hist['nome_turma']); ?></h3>
                                
                                <div class="adm-turma-arquivada-detalhes">
                                    <p><span>Redações Enviadas:</span> <strong><?php echo $hist['total_redacoes']; ?></strong></p>
                                    <p><span>Média Anual:</span> <strong><?php echo $hist['media_anual']; ?></strong></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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