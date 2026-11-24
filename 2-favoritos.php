<?php
require_once '2-funcoes-favoritos.php';
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
                    <li><a href="2-painel-administrador.php" class="adm-menu-item-link">Início</a></li>
                    <li><a href="2-propostas-administrador.php" class="adm-menu-item-link">Propostas</a></li>
                    <li><a href="2-calendario-administrador.php" class="adm-menu-item-link">Calendário</a></li>
                    <li><a href="2-corretores.php" class="adm-menu-item-link">Corretores</a></li>
                    <li><a href="2-favoritos.php" class="adm-menu-item-link adm-ativo">Favoritos</a></li>
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
            <header class="adm-cabecalho-pagina">
                <div>
                    <h1 class="adm-titulo-pagina">Redações Favoritas</h1>
                    <p class="adm-subtitulo-pagina">Consulte abaixo as redações marcadas como favoritas, ordenadas por <strong>ordem de envio</strong>.</p>
                </div>
            </header>

            <!-- BARRA DE ACOES COM PESQUISA -->
            <div class="adm-barra-acoes-propostas" style="margin-top: 20px;">
                <div class="adm-filtros-grupo">
                    <input type="text" id="campoBuscaFavoritos" class="adm-campo-busca" placeholder="Buscar por aluno, turma ou proposta...">
                </div>
            </div>

            <div class="adm-cartao-secao" style="margin-top: 20px;">
                <div class="adm-tabela-container">
                    <table class="adm-tabela-alunos" id="tabelaFavoritos">
                        <thead>
                            <tr>
                                <th>Aluno(a)</th>
                                <th>Turma</th>
                                <th>Proposta / Tema</th>
                                <th>Data de Envio</th>
                                <th>Nota Final</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($redacoes_favoritas)): ?>
                                <?php foreach ($redacoes_favoritas as $fav): ?>
                                    <tr class="linha-favorito">
                                        <td><strong><?php echo htmlspecialchars($fav['nome_aluno']); ?></strong></td>
                                        <td><?php echo $fav['serie'] . '° ' . $fav['turma'] . ' - ' . strtoupper($fav['curso']); ?></td>
                                        <td><?php echo htmlspecialchars($fav['titulo_proposta']); ?></td>
                                        <td><?php echo date('d/m/Y - H:i', strtotime($fav['enviado_em'])); ?></td>
                                        <td><strong><?php echo $fav['nota_total'] !== null ? $fav['nota_total'] : '-'; ?></strong></td>
                                        <td>
                                            <a href="2-visualizar-redacao.php?id=<?php echo $fav['id_redacao']; ?>" class="adm-botao-acao-tabela adm-btn-visualizar">
                                                Visualizar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr id="linhaSemResultados">
                                    <td colspan="6" style="text-align: center; color: #64748b; padding: 30px 0;">
                                        Nenhuma redação favoritada no momento.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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

        // Script de filtro dinâmico de pesquisa
        const campoBusca = document.getElementById('campoBuscaFavoritos');
        const linhasTabela = document.querySelectorAll('.linha-favorito');

        campoBusca.addEventListener('input', function () {
            const termoBusca = this.value.toLowerCase().trim();

            linhasTabela.forEach(linha => {
                const textoLinha = linha.textContent.toLowerCase();
                if (textoLinha.includes(termoBusca)) {
                    linha.style.display = '';
                } else {
                    linha.style.display = 'none';
                }
            });
        });
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