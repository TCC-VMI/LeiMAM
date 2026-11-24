<?php
require_once '2-funcoes-detalhes-proposta.php';

// Captura a página de origem para o botão de voltar e menu inteligente
$origem_retorno = $_SERVER['HTTP_REFERER'] ?? '2-propostas-administrador.php';
$arquivo_origem = basename(parse_url($origem_retorno, PHP_URL_PATH));

// Identifica automaticamente qual item do menu deve ficar ativo
$menu_ativo = 'propostas'; // padrão para detalhes de proposta

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

// Define o texto dinâmico do botão de voltar com base em de onde o usuário veio
$texto_voltar = "Voltar";
if (strpos($arquivo_origem, 'favorito') !== false) {
    $texto_voltar = "Voltar";
} elseif (strpos($arquivo_origem, 'arquivado') !== false) {
    $texto_voltar = "Voltar";
} elseif (strpos($arquivo_origem, 'painel') !== false) {
    $texto_voltar = "Voltar";
} else {
    $texto_voltar = "Voltar";
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
                ☰
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
                
                <div class="adm-navegacao-voltar" style="display: flex; justify-content: space-between; align-items: center;">
                    <a href="<?php echo htmlspecialchars($origem_retorno); ?>" class="adm-link-voltar">← <?php echo $texto_voltar; ?></a>
                    
                    <button type="button" class="btn-excluir-cabecalho" onclick="abrirModalExclusao()">
                        <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i> Excluir Proposta
                    </button>
                </div>

                <header class="adm-cabecalho-detalhe-proposta">
                    <div class="adm-proposta-status-linha">
                        <span class="adm-badge-status adm-badge-<?php echo htmlspecialchars($proposta['status']); ?>">
                            <?php echo ucfirst(htmlspecialchars($proposta['status'])); ?>
                        </span>
                        <span class="adm-data-criacao">
                            Postado em: <?php echo date('d/m/Y \à\s H:i', strtotime($proposta['criado_em'])); ?>
                        </span>
                    </div>
                    
                    <h1 class="adm-titulo-pagina"><?php echo htmlspecialchars($proposta['titulo']); ?></h1>
                    
                    <div class="adm-meta-proposta-linha">
                        <div class="adm-meta-item">
                            <strong>Prazo de Entrega:</strong> 
                            <?php 
                                echo !empty($proposta['prazo_entrega']) 
                                    ? date('d/m/Y \à\s H:i', strtotime($proposta['prazo_entrega'])) 
                                    : "Sem data limite"; 
                            ?>
                        </div>
                        <div class="adm-meta-item">
                            <strong>Turmas com acesso:</strong>
                            <div class="adm-proposta-turmas">
                                <?php if (!empty($turmas_liberadas)): ?>
                                    <?php foreach ($turmas_liberadas as $t_lib): ?>
                                        <span class="adm-tag-turma"><?php echo $t_lib['nome']; ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span style="color: #64748b;">Nenhuma turma vinculada</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </header>

                <section class="adm-cartao-secao adm-detalhamento-bloco">
                    <h2 class="adm-titulo-secao">Instruções</h2>
                    
                    <div class="adm-detalhamento-texto">
                        <p><?php echo nl2br(htmlspecialchars($proposta['instrucoes'])); ?></p>
                    </div>

                    <?php if (!empty($proposta['texto_apoio']) && file_exists($proposta['texto_apoio'])): ?>
                        <?php 
                            $extensao = strtolower(pathinfo($proposta['texto_apoio'], PATHINFO_EXTENSION));
                            $caminho_arquivo = htmlspecialchars($proposta['texto_apoio']);
                        ?>
                        
                        <div class="adm-anexo-container" style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <strong style="color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                    <i data-lucide="file-text" style="width: 18px; height: 18px;"></i> Arquivo Anexo de Apoio
                                </strong>
                                <a href="<?php echo $caminho_arquivo; ?>" download class="adm-link-anexo" style="color: #2563eb; font-size: 0.85rem; text-decoration: none; font-weight: 600;">
                                    Baixar Arquivo original
                                </a>
                            </div>

                            <?php if ($extensao === 'pdf'): ?>
                                <iframe src="<?php echo $caminho_arquivo; ?>" width="100%" height="550px" style="border: 1px solid #cbd5e1; border-radius: 8px;">
                                    Seu navegador não suporta a exibição direta de PDFs. 
                                    <a href="<?php echo $caminho_arquivo; ?>" target="_blank">Clique aqui para abrir o PDF em nova aba.</a>
                                </iframe>

                            <?php elseif (in_array($extensao, ['jpg', 'jpeg', 'png', 'webp'])): ?>
                                <div style="text-align: center; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #cbd5e1;">
                                    <img src="<?php echo $caminho_arquivo; ?>" alt="Arquivo de Apoio" style="max-width: 100%; max-height: 500px; border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                                </div>

                            <?php else: ?>
                                <p><a href="<?php echo $caminho_arquivo; ?>" target="_blank" class="adm-link-anexo">Clique para visualizar o anexo</a></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- CARD DE INDICADORES DA PROPOSTA -->
                <section class="adm-grid-indicadores">
                    <div class="adm-cartao-indicador">
                        <div class="adm-indicador-titulo">Redações Pendentes</div>
                        <div class="adm-indicador-valor"><?php echo $redacoes_pendentes; ?></div>
                    </div>
                    <div class="adm-cartao-indicador">
                        <div class="adm-indicador-titulo">Aguardando Correção</div>
                        <div class="adm-indicador-valor"><?php echo $aguardando_correcao; ?></div>
                    </div>
                    <div class="adm-cartao-indicador">
                        <div class="adm-indicador-titulo">Redações Corrigidas</div>
                        <div class="adm-indicador-valor"><?php echo $redacoes_corrigidas; ?></div>
                    </div>
                    <div class="adm-cartao-indicador">
                        <div class="adm-indicador-titulo">Média Geral da Proposta</div>
                        <div class="adm-indicador-valor"><?php echo $media_proposta; ?></div>
                    </div>
                </section>

                <section class="adm-secao-turmas-desempenho">
                    <h2 class="adm-titulo-secao">Desempenho por Turma</h2>
                    
                    <div class="adm-grid-turmas-desempenho">
                        <?php if (!empty($turmas_liberadas)): ?>
                            <?php foreach ($turmas_liberadas as $t): ?>
                                <?php
                                    $stmt_t = $conn->prepare("SELECT 
                                                                COUNT(r.id_redacao) AS enviadas,
                                                                AVG(c.nota_total) AS media
                                                            FROM aluno a
                                                            LEFT JOIN redacoes r ON a.id_aluno = r.id_aluno AND r.id_proposta = ?
                                                            LEFT JOIN correcoes c ON r.id_redacao = c.id_redacao
                                                            WHERE a.serie = ? AND a.turma = ? AND a.curso = ? AND a.ano = ?");
                                    $stmt_t->bind_param("iissi", $id_proposta, $t['serie'], $t['turma'], $t['curso'], $ano_atual_brasil);
                                    $stmt_t->execute();
                                    $res_t = $stmt_t->get_result()->fetch_assoc();
                                    $stmt_t->close();

                                    $stmt_tot = $conn->prepare("SELECT COUNT(*) AS total FROM aluno WHERE serie = ? AND turma = ? AND curso = ? AND ano = ?");
                                    $stmt_tot->bind_param("issi", $t['serie'], $t['turma'], $t['curso'], $ano_atual_brasil);
                                    $stmt_tot->execute();
                                    $tot_alunos_t = $stmt_tot->get_result()->fetch_assoc()['total'] ?? 0;
                                    $stmt_tot->close();

                                    $env_t = $res_t['enviadas'] ?? 0;
                                    $med_t = $res_t['media'] ? round($res_t['media']) : 0;
                                    $pct_t = $tot_alunos_t > 0 ? round(($env_t / $tot_alunos_t) * 100, 1) : 0;
                                ?>
                                <div class="adm-cartao-turma-desempenho">
                                    <div class="adm-turma-desempenho-topo">
                                        <span class="adm-turma-desempenho-nome"><?php echo $t['nome']; ?></span>
                                        <span class="adm-turma-desempenho-media">Média: <strong><?php echo $med_t; ?></strong></span>
                                    </div>
                                    <div class="adm-turma-desempenho-metricas">
                                        <p><span>Alunos da Turma:</span> <strong><?php echo $tot_alunos_t; ?></strong></p>
                                        <p><span>Enviadas:</span> <strong><?php echo $env_t; ?> / <?php echo $tot_alunos_t; ?></strong></p>
                                    </div>
                                    <div class="adm-turma-progresso-container">
                                        <div class="adm-turma-progresso-label">
                                            <span>Porcentagem de Envio</span>
                                            <span><?php echo $pct_t; ?>%</span>
                                        </div>
                                        <div class="adm-barra-progresso-fundo">
                                            <div class="adm-barra-progresso-preenchimento" style="width: <?php echo $pct_t; ?>%;"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: #64748b;">Nenhuma turma vinculada a esta proposta.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="adm-cartao-secao">
                    <div class="adm-cabecalho-secao-tabela">
                        <h2 class="adm-titulo-secao">Redações Enviadas</h2>
                        
                        <form method="GET" action="2-detalhes-proposta.php" class="adm-filtros-grupo">
                            <input type="hidden" name="id" value="<?php echo $id_proposta; ?>">
                            
                            <input type="text" name="busca_aluno" class="adm-campo-busca" placeholder="Buscar aluno..." value="<?php echo htmlspecialchars($busca_aluno); ?>">
                            
                            <select name="filtro_turma" class="adm-seletor-filtro" onchange="this.form.submit()">
                                <option value="">Todas as Turmas</option>
                                <optgroup label="1º Ano">
                                    <option value="1a-info"  <?php if ($filtro_turma === '1a-info')  echo 'selected'; ?>>1° A INFO</option>
                                    <option value="1b-info"  <?php if ($filtro_turma === '1b-info')  echo 'selected'; ?>>1° B INFO</option>
                                    <option value="1a-redes" <?php if ($filtro_turma === '1a-redes') echo 'selected'; ?>>1° A REDES</option>
                                    <option value="1b-redes" <?php if ($filtro_turma === '1b-redes') echo 'selected'; ?>>1° B REDES</option>
                                </optgroup>
                                <optgroup label="2º Ano">
                                    <option value="2a-info"  <?php if ($filtro_turma === '2a-info')  echo 'selected'; ?>>2° A INFO</option>
                                    <option value="2b-info"  <?php if ($filtro_turma === '2b-info')  echo 'selected'; ?>>2° B INFO</option>
                                    <option value="2a-redes" <?php if ($filtro_turma === '2a-redes') echo 'selected'; ?>>2° A REDES</option>
                                    <option value="2b-redes" <?php if ($filtro_turma === '2b-redes') echo 'selected'; ?>>2° B REDES</option>
                                </optgroup>
                                <optgroup label="3º Ano">
                                    <option value="3a-info"  <?php if ($filtro_turma === '3a-info')  echo 'selected'; ?>>3° A INFO</option>
                                    <option value="3b-info"  <?php if ($filtro_turma === '3b-info')  echo 'selected'; ?>>3° B INFO</option>
                                    <option value="3a-redes" <?php if ($filtro_turma === '3a-redes') echo 'selected'; ?>>3° A REDES</option>
                                    <option value="3b-redes" <?php if ($filtro_turma === '3b-redes') echo 'selected'; ?>>3° B REDES</option>
                                </optgroup>
                            </select>

                            <select name="filtro_status" class="adm-seletor-filtro" onchange="this.form.submit()">
                                <option value="">Todos os Status</option>
                                <option value="corrigida" <?php if ($filtro_status === 'corrigida') echo 'selected'; ?>>Corrigida</option>
                                <option value="pendente"  <?php if ($filtro_status === 'pendente')  echo 'selected'; ?>>Aguardando Correção</option>
                            </select>

                            <button type="submit" style="display: none;"></button>
                        </form>
                    </div>

                    <div class="adm-tabela-container">
                        <table class="adm-tabela-alunos">
                            <thead>
                                <tr>
                                    <th>Nome do Aluno</th>
                                    <th>Turma</th>
                                    <th>Data de Envio</th>
                                    <th>Status</th>
                                    <th>Nota</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($lista_redacoes && $lista_redacoes->num_rows > 0): ?>
                                    <?php while ($red = $lista_redacoes->fetch_assoc()): ?>
                                        <?php 
                                            $is_corrigida = !empty($red['id_correcao']);
                                            $status_label = $is_corrigida ? 'Corrigida' : 'Aguardando Correção';
                                            $status_class = $is_corrigida ? 'adm-badge-ativa' : 'adm-badge-agendada';
                                            $nota_display = $is_corrigida ? $red['nota_total'] : '-';
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($red['nome_aluno']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($red['turma_formatada']); ?></td>
                                            <td><?php echo date('d/m/Y - H:i', strtotime($red['enviado_em'])); ?></td>
                                            <td><span class="adm-badge-status <?php echo $status_class; ?>"><?php echo $status_label; ?></span></td>
                                            <td><strong><?php echo $nota_display; ?></strong></td>
                                            <td>
                                                <?php if ($is_corrigida): ?>
                                                    <a href="2-visualizar-redacao.php?id=<?php echo $red['id_redacao']; ?>" class="adm-botao-acao-tabela adm-btn-visualizar">Visualizar</a>
                                                <?php else: ?>
                                                    <a href="2-corrigir-redacao.php?id=<?php echo $red['id_redacao']; ?>" class="adm-botao-acao-tabela adm-btn-corrigir">Corrigir</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; color: #64748b; padding: 20px 0;">
                                            Nenhuma redação enviada encontrada para esta proposta no ano atual.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>

        <!-- MODAL DE CONFIRMAÇÃO DE EXCLUSÃO -->
        <div class="modal-mascara" id="modalExclusao">
            <div class="modal-conteudo">
                <div class="modal-icone">
                    <i data-lucide="alert-triangle" style="width: 48px; height: 48px;"></i>
                </div>
                <h3 class="modal-titulo">Tem certeza absoluta?</h3>
                <p class="modal-texto">
                    Você está prestes a excluir a proposta <strong>"<?php echo htmlspecialchars($proposta['titulo']); ?>"</strong>.<br>
                    Esta ação removerá permanentemente o registro do banco de dados e não poderá ser desfeita.
                </p>
                <div class="modal-acoes">
                    <button type="button" class="btn-modal-cancelar" onclick="fecharModalExclusao()">Cancelar</button>
                    <a href="2-excluir-proposta.php?id=<?php echo $proposta['id_proposta']; ?>" class="btn-modal-excluir">Sim, Excluir do Banco</a>
                </div>
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
$stmt_lista->close();
$conn->close(); 
?>