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
                    <li><a href="2-calendario-administrador.php" class="adm-menu-item-link">Calendário</a></li>
                    <li><a href="2-corretores.php" class="adm-menu-item-link adm-ativo">Corretores</a></li>
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
            <header class="adm-cabecalho-pagina">
                <div>
                    <h1 class="adm-titulo-pagina">Cadastrar Novo Corretor</h1>
                    <p class="adm-subtitulo-pagina">Preencha os dados do corretor e defina quais turmas ele poderá atender.</p>
                </div>
            </header>

            <form class="adm-formulario-container" action="2-funcoes-criar-corretor.php" method="POST">
                
                <div class="adm-form-grupo">
                    <label class="adm-label-form" for="nome">Nome Completo</label>
                    <input type="text" id="nome" name="nome" class="adm-input-form" placeholder="Digite o nome completo do corretor..." required>
                </div>

                <div class="adm-form-linha">
                    <div class="adm-form-grupo">
                        <label class="adm-label-form" for="matricula">Matrícula</label>
                        <input type="text" id="matricula" name="matricula" class="adm-input-form" placeholder="Digite a matrícula..." required>
                    </div>

                    <div class="adm-form-grupo">
                        <label class="adm-label-form" for="senha">Senha de Acesso</label>
                        <input type="text" id="senha" name="senha" class="adm-input-form" placeholder="Digite a senha..." required>
                    </div>
                </div>

                <!-- SEÇÃO DE TURMAS VINCULADAS (12 TURMAS) -->
                <div class="adm-form-grupo">
                    <label class="adm-label-form">Turmas Responsáveis</label>
                    <p class="adm-help-text">Selecione quais turmas este corretor ficará responsável por avaliar:</p>
                    
                    <div style="display: flex; flex-direction: column; gap: 15px; margin-top: 10px;">
                        
                        <!-- 1º ANO -->
                        <div style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <strong style="color: #334155; font-size: 0.9rem;">1º ANO</strong>
                            <div class="adm-checkbox-grid" style="margin-top: 8px;">
                                <label class="adm-checkbox-item"><input type="checkbox" name="t_1ainfo" value="1"> 1° A INFO</label>
                                <label class="adm-checkbox-item"><input type="checkbox" name="t_1binfo" value="1"> 1° B INFO</label>
                                <label class="adm-checkbox-item"><input type="checkbox" name="t_1arede" value="1"> 1° A REDES</label>
                                <label class="adm-checkbox-item"><input type="checkbox" name="t_1brede" value="1"> 1° B REDES</label>
                            </div>
                        </div>

                        <!-- 2º ANO -->
                        <div style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <strong style="color: #334155; font-size: 0.9rem;">2º ANO</strong>
                            <div class="adm-checkbox-grid" style="margin-top: 8px;">
                                <label class="adm-checkbox-item"><input type="checkbox" name="t_2ainfo" value="1"> 2° A INFO</label>
                                <label class="adm-checkbox-item"><input type="checkbox" name="t_2binfo" value="1"> 2° B INFO</label>
                                <label class="adm-checkbox-item"><input type="checkbox" name="t_2arede" value="1"> 2° A REDES</label>
                                <label class="adm-checkbox-item"><input type="checkbox" name="t_2brede" value="1"> 2° B REDES</label>
                            </div>
                        </div>

                        <!-- 3º ANO -->
                        <div style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <strong style="color: #334155; font-size: 0.9rem;">3º ANO</strong>
                            <div class="adm-checkbox-grid" style="margin-top: 8px;">
                                <label class="adm-checkbox-item"><input type="checkbox" name="t_3ainfo" value="1"> 3° A INFO</label>
                                <label class="adm-checkbox-item"><input type="checkbox" name="t_3binfo" value="1"> 3° B INFO</label>
                                <label class="adm-checkbox-item"><input type="checkbox" name="t_3arede" value="1"> 3° A REDES</label>
                                <label class="adm-checkbox-item"><input type="checkbox" name="t_3brede" value="1"> 3° B REDES</label>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="adm-form-acoes">
                    <a href="2-corretores.php" class="adm-botao-cancelar">Cancelar</a>
                    <button type="submit" class="adm-botao-salvar">Cadastrar Corretor</button>
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