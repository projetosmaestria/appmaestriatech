<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$json = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
$meu_nome = trim($this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name'));
if ($meu_nome === '' && isset($user_login)) {
    $meu_nome = trim((isset($user_login->first_name) ? $user_login->first_name : '') . ' ' . (isset($user_login->last_name) ? $user_login->last_name : ''));
}
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php echo $message; ?>

        <!-- Telas que deslizam: conversas -> nova conversa -> novo grupo -> conversa aberta. -->
        <div class="box box-primary mensagens-app sem-animacao" id="mensagens-app">

            <section class="mensagens-tela" data-tela="lista" aria-label="Conversas">
                <div class="box-header with-border mensagens-topo">
                    <h3 class="box-title"><i class="fa fa-comments-o"></i> Conversas</h3>
                    <div class="mensagens-topo-acoes">
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle mensagens-icon-btn" id="mensagens-lista-opcoes-btn" data-toggle="dropdown" title="Mais opções" aria-label="Mais opções" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                                <span class="mensagens-ponto-alerta" id="mensagens-topo-alerta" hidden></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right mensagens-topo-dropdown">
                                <li><a href="#" id="mensagens-abrir-favoritas"><i class="fa fa-star-o"></i> Mensagens favoritas</a></li>
                                <li><a href="#" id="mensagens-abrir-lembretes"><i class="fa fa-calendar-check-o"></i> Lembretes <span class="badge pull-right mensagens-topo-badge" id="mensagens-lembretes-qtd" hidden>0</span></a></li>
                                <li><a href="#" id="mensagens-abrir-agendadas"><i class="fa fa-clock-o"></i> Agendadas <span class="badge pull-right mensagens-topo-badge" id="mensagens-agendadas-qtd" hidden>0</span></a></li>
                                <?php if ($grupos_ativos || $canais_ativos) { ?>
                                    <li role="separator" class="divider"></li>
                                <?php } ?>
                                <?php if ($grupos_ativos) { ?>
                                    <li><a href="#" class="js-ir-grupo"><i class="fa fa-users"></i> Novo grupo</a></li>
                                <?php } ?>
                                <?php if ($canais_ativos) { ?>
                                    <li><a href="#" class="js-abrir-novo-canal"><i class="fa fa-bullhorn"></i> Novo canal</a></li>
                                <?php } ?>
                            </ul>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm mensagens-nova-btn js-ir-nova" title="Nova conversa" aria-label="Nova conversa"><i class="fa fa-plus"></i><span class="mensagens-btn-texto"> Nova</span></button>
                    </div>
                </div>
                <div class="mensagens-meu-status-bar">
                    <button type="button" class="mensagens-meu-status-btn" id="mensagens-meu-status-btn" title="Alterar meu status de disponibilidade">
                        <span class="mensagens-status-dot st-<?php echo html_escape(isset($meu_status['status']) ? $meu_status['status'] : 'online') ?>" id="mensagens-meu-status-dot"></span>
                        <span id="mensagens-meu-status-rotulo"><?php echo html_escape(isset($meu_status['rotulo']) ? $meu_status['rotulo'] : 'Online') ?></span>
                        <small id="mensagens-meu-status-texto" class="text-muted"><?php echo !empty($meu_status['texto']) ? '· ' . html_escape($meu_status['texto']) : '' ?></small>
                        <i class="fa fa-pencil text-muted" style="margin-left:auto;"></i>
                    </button>
                </div>
                <div class="mensagens-busca">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <input type="search" class="form-control" id="mensagens-busca" placeholder="Buscar conversa" autocomplete="off" aria-label="Buscar conversa">
                </div>
                <div class="mensagens-filtros-bar" id="mensagens-etiquetas-bar"></div>
                <ul class="mensagens-lista" id="mensagens-lista" aria-label="Conversas"></ul>
            </section>

            <section class="mensagens-tela" data-tela="nova" aria-label="Nova conversa">
                <div class="box-header with-border mensagens-topo">
                    <button type="button" class="btn btn-link mensagens-voltar js-voltar" aria-label="Voltar para as conversas"><i class="fa fa-arrow-left"></i></button>
                    <h3 class="box-title">Nova conversa</h3>
                </div>
                <?php if ($grupos_ativos) { ?>
                    <button type="button" class="mensagens-novo-grupo js-ir-grupo">
                        <span class="mensagens-avatar-grupo"><i class="fa fa-users"></i></span>
                        <span><strong>Novo grupo</strong><small>Converse com <?php echo (int) $minimo_grupo - 1 ?> ou mais pessoas ao mesmo tempo</small></span>
                    </button>
                <?php } ?>
                <?php if ($canais_ativos) { ?>
                    <button type="button" class="mensagens-novo-grupo js-abrir-novo-canal">
                        <span class="mensagens-avatar-grupo" style="background:#605ca8;"><i class="fa fa-bullhorn"></i></span>
                        <span><strong>Novo canal</strong><small>Crie um canal por grupos de usuários com controle de quem pode enviar</small></span>
                    </button>
                <?php } ?>
                <div class="mensagens-busca">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <input type="search" class="form-control" id="mensagens-busca-pessoas" placeholder="Buscar pessoa" autocomplete="off" aria-label="Buscar pessoa">
                </div>
                <ul class="mensagens-lista" id="mensagens-pessoas" aria-label="Pessoas"></ul>
            </section>

            <section class="mensagens-tela" data-tela="grupo" aria-label="Novo grupo">
                <div class="box-header with-border mensagens-topo">
                    <button type="button" class="btn btn-link mensagens-voltar js-voltar-grupo" aria-label="Voltar"><i class="fa fa-arrow-left"></i></button>
                    <h3 class="box-title">Novo grupo</h3>
                </div>
                <div class="mensagens-grupo-form">
                    <label for="mensagens-grupo-nome">Nome do grupo</label>
                    <input type="text" class="form-control" id="mensagens-grupo-nome" maxlength="<?php echo (int) $tamanho_nome_grupo ?>" placeholder="Ex.: Equipe de suporte" autocomplete="off">
                    <p class="mensagens-grupo-resumo" id="mensagens-grupo-resumo"></p>
                    <ul class="mensagens-escolhidos" id="mensagens-grupo-escolhidos" aria-label="Pessoas escolhidas"></ul>
                </div>
                <div class="mensagens-busca">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <input type="search" class="form-control" id="mensagens-busca-grupo" placeholder="Buscar pessoa para adicionar" autocomplete="off" aria-label="Buscar pessoa">
                </div>
                <ul class="mensagens-lista" id="mensagens-grupo-pessoas" aria-label="Escolha os participantes"></ul>
                <div class="mensagens-grupo-acoes">
                    <button type="button" class="btn btn-primary" id="mensagens-grupo-criar" disabled><i class="fa fa-check"></i> Criar grupo</button>
                </div>
            </section>

            <section class="mensagens-tela" data-tela="conversa" aria-label="Conversa">
                <div class="box-header with-border mensagens-topo">
                    <button type="button" class="btn btn-link mensagens-voltar js-voltar" aria-label="Voltar para as conversas"><i class="fa fa-arrow-left"></i></button>
                    <span id="mensagens-atual-avatar"></span>
                    <div class="mensagens-topo-titulo">
                        <h3 class="box-title" id="mensagens-atual-nome"></h3>
                        <button type="button" class="mensagens-topo-sub js-detalhes-grupo" id="mensagens-atual-sub" hidden></button>
                        <span class="mensagens-topo-status" id="mensagens-atual-status" hidden></span>
                    </div>
                    <button type="button" class="btn btn-default btn-sm" id="mensagens-ligar-btn" title="Ligação por voz" aria-label="Ligação por voz"><i class="fa fa-phone"></i></button>
                    <button type="button" class="btn btn-default btn-sm" id="mensagens-midias-btn" title="Caixa de mídias e arquivos" aria-label="Caixa de mídias"><i class="fa fa-folder-open-o"></i></button>
                    <button type="button" class="btn btn-default btn-sm" id="mensagens-notas-btn" title="Notas privadas desta conversa" aria-label="Notas privadas"><i class="fa fa-sticky-note-o"></i></button>
                    <button type="button" class="btn btn-default btn-sm" id="mensagens-etiquetas-btn" title="Etiquetas desta conversa" aria-label="Etiquetas"><i class="fa fa-tags"></i></button>
                    <button type="button" class="btn btn-default btn-sm" id="mensagens-nao-lida-btn" title="Marcar conversa como não lida" aria-label="Marcar como não lida"><i class="fa fa-envelope-o"></i></button>
                    <button type="button" class="btn btn-default btn-sm" id="mensagens-busca-conversa-btn" title="Buscar nesta conversa" aria-label="Buscar nesta conversa"><i class="fa fa-search"></i></button>
                    <button type="button" class="btn btn-default btn-sm" id="mensagens-fixadas-btn" title="Mensagens fixadas" aria-label="Mensagens fixadas"><i class="fa fa-thumb-tack"></i> <span class="badge mensagens-fixadas-badge" id="mensagens-fixadas-qtd" hidden>0</span></button>
                    <button type="button" class="btn btn-default btn-sm" id="mensagens-notif-btn" title="Configurar notificações" aria-label="Configurar notificações"><i class="fa fa-bell-o"></i></button>
                    <button type="button" class="btn btn-default btn-sm js-detalhes-grupo" id="mensagens-grupo-btn" hidden><i class="fa fa-users"></i><span class="hidden-xs"> Participantes</span></button>
                    <button type="button" class="btn btn-default btn-sm" id="mensagens-canal-config-btn" hidden title="Configurar canal"><i class="fa fa-cog"></i><span class="hidden-xs"> Canal</span></button>
                </div>

                <!-- Banner de chamada em andamento -->
                <div class="mensagens-chamada-banner" id="mensagens-chamada-banner" hidden>
                    <span id="mensagens-chamada-banner-txt"><i class="fa fa-phone"></i> Ligação em andamento</span>
                    <button type="button" class="btn btn-success btn-xs" id="mensagens-chamada-entrar"><i class="fa fa-sign-in"></i> Entrar</button>
                </div>

                <!-- Painel de chamada por voz e compartilhamento de tela -->
                <div class="mensagens-chamada-painel" id="mensagens-chamada-painel" hidden>
                    <div class="mensagens-chamada-topo">
                        <div class="mensagens-chamada-info">
                            <span class="mensagens-chamada-badge-ico" aria-hidden="true"><i class="fa fa-phone"></i></span>
                            <div class="mensagens-chamada-txt">
                                <strong id="mensagens-chamada-titulo">Ligação ativa</strong>
                                <small id="mensagens-chamada-sub">Conectando…</small>
                            </div>
                        </div>
                        <div class="mensagens-chamada-controles">
                            <button type="button" class="btn btn-default btn-sm" id="mensagens-chamada-mudo" title="Silenciar microfone"><i class="fa fa-microphone"></i> <span>Microfone</span></button>
                            <button type="button" class="btn btn-default btn-sm" id="mensagens-chamada-comp-tela" title="Compartilhar tela e áudio"><i class="fa fa-desktop"></i> <span>Compartilhar tela</span></button>
                            <button type="button" class="btn btn-danger btn-sm" id="mensagens-chamada-sair" title="Desligar ligação"><i class="fa fa-phone"></i> Desligar</button>
                        </div>
                    </div>
                    <div class="mensagens-chamada-participantes" id="mensagens-chamada-participantes"></div>
                    <div class="mensagens-chamada-palco" id="mensagens-chamada-palco" hidden>
                        <div class="mensagens-chamada-palco-topo">
                            <span id="mensagens-chamada-palco-autor"><i class="fa fa-desktop"></i> Tela compartilhada</span>
                            <button type="button" class="btn btn-default btn-xs" id="mensagens-chamada-fullscreen" title="Alternar visualização da tela"><i class="fa fa-compress"></i> <span>Recolher</span></button>
                        </div>
                        <video id="mensagens-chamada-video" autoplay playsinline></video>
                    </div>
                    <div id="mensagens-chamada-audios" hidden></div>
                </div>

                <!-- Banner de mensagem fixada no topo da conversa -->
                <div class="mensagens-fixada-banner" id="mensagens-fixada-banner" hidden>
                    <button type="button" class="mensagens-fixada-ir" id="mensagens-fixada-ir">
                        <i class="fa fa-thumb-tack" aria-hidden="true"></i>
                        <span class="mensagens-fixada-texto-wrap">
                            <strong id="mensagens-fixada-titulo">Mensagem fixada</strong>
                            <span id="mensagens-fixada-previa"></span>
                        </span>
                    </button>
                    <button type="button" class="btn btn-link btn-xs" id="mensagens-fixada-todas">Ver todas</button>
                </div>

                <div class="mensagens-historico" id="mensagens-historico" aria-live="polite"></div>
                <div class="mensagens-soltar" aria-hidden="true"><i class="fa fa-cloud-upload"></i> Solte para anexar</div>

                <div class="mensagens-rodape">
                    <!-- Barra de resposta ou edição -->
                    <div class="mensagens-contexto" id="mensagens-contexto" hidden>
                        <div class="mensagens-contexto-corpo">
                            <strong id="mensagens-contexto-titulo"></strong>
                            <span id="mensagens-contexto-texto"></span>
                        </div>
                        <button type="button" class="mensagens-contexto-fechar" id="mensagens-contexto-fechar" aria-label="Cancelar">&times;</button>
                    </div>

                    <ul class="mensagens-pendentes" id="mensagens-pendentes" aria-label="Arquivos para enviar"></ul>
                    <div class="mensagens-emojis" id="mensagens-emojis" role="dialog" aria-label="Emojis" hidden></div>
                    <ul class="mensagens-mencoes-popup" id="mensagens-mencoes-popup" role="listbox" aria-label="Mencionar pessoa" hidden></ul>

                    <div class="mensagens-somente-leitura" id="mensagens-somente-leitura" hidden>
                        <i class="fa fa-lock"></i> <span id="mensagens-somente-leitura-txt">Apenas usuários autorizados podem enviar mensagens neste canal.</span>
                    </div>

                    <form class="mensagens-envio" id="mensagens-envio" autocomplete="off">
                        <button type="button" class="btn btn-default mensagens-acao" id="mensagens-emoji-btn" aria-label="Emojis" aria-expanded="false" title="Emojis"><i class="fa fa-smile-o"></i></button>
                        <button type="button" class="btn btn-default mensagens-acao" id="mensagens-anexo-btn" aria-label="Anexar arquivo" title="Anexar imagem, vídeo ou documento (até <?php echo (int) $tamanho_anexo_mb ?> MB)"><i class="fa fa-paperclip"></i></button>
                        <button type="button" class="btn btn-default mensagens-acao" id="mensagens-enquete-btn" aria-label="Criar enquete" title="Criar enquete no canal" hidden><i class="fa fa-bar-chart"></i></button>
                        <button type="button" class="btn btn-default mensagens-acao" id="mensagens-reuniao-btn" aria-label="Agendar reunião" title="Agendar reunião no canal" hidden><i class="fa fa-calendar-plus-o"></i></button>
                        <input type="file" id="mensagens-arquivo" multiple hidden accept="<?php echo html_escape($aceitos) ?>">
                        <textarea class="form-control" id="mensagens-texto" rows="1" maxlength="<?php echo (int) $tamanho_maximo ?>" placeholder="Escreva uma mensagem (use @ para mencionar)" aria-label="Mensagem"></textarea>
                        <button type="button" class="btn btn-default mensagens-acao" id="mensagens-audio-btn" aria-label="Gravar áudio" title="Gravar mensagem de áudio"><i class="fa fa-microphone"></i></button>
                        <button type="button" class="btn btn-default mensagens-acao" id="mensagens-agendar-btn" aria-label="Agendar mensagem" title="Agendar envio"><i class="fa fa-clock-o"></i></button>
                        <button type="submit" class="btn btn-primary mensagens-acao" id="mensagens-enviar" aria-label="Enviar"><i class="fa fa-paper-plane"></i></button>
                    </form>
                    <div class="mensagens-gravando" id="mensagens-gravando" hidden>
                        <button type="button" class="btn btn-default mensagens-acao" id="mensagens-audio-cancelar" aria-label="Cancelar gravação" title="Cancelar gravação"><i class="fa fa-trash-o"></i></button>
                        <div class="mensagens-gravando-status">
                            <span class="mensagens-gravando-ponto" aria-hidden="true"></span>
                            <span>Gravando áudio</span>
                            <strong id="mensagens-audio-tempo">00:00</strong>
                        </div>
                        <button type="button" class="btn btn-default mensagens-acao" id="mensagens-audio-parar" aria-label="Parar e ouvir" title="Parar para ouvir antes de enviar"><i class="fa fa-stop"></i></button>
                        <button type="button" class="btn btn-primary mensagens-acao" id="mensagens-audio-enviar" aria-label="Enviar áudio" title="Enviar áudio"><i class="fa fa-paper-plane"></i></button>
                    </div>
                    <p class="mensagens-erro" id="mensagens-erro" role="alert" hidden></p>
                    <p class="mensagens-info" id="mensagens-info" role="status" hidden></p>
                    <p class="mensagens-dica"><span>Enter envia · Shift+Enter quebra a linha · @ menciona · arraste ou cole arquivos</span><span id="mensagens-contador"></span></p>
                </div>
            </section>
        </div>
    </section>
</div>

<!-- Menu flutuante de ações da mensagem -->
<div class="mensagens-menu-msg" id="mensagens-menu-msg" role="menu" hidden>
    <button type="button" class="mensagens-menu-item" data-acao="reagir"><i class="fa fa-smile-o"></i> Reagir</button>
    <button type="button" class="mensagens-menu-item" data-acao="responder"><i class="fa fa-reply"></i> Responder</button>
    <button type="button" class="mensagens-menu-item" data-acao="encaminhar"><i class="fa fa-share"></i> Encaminhar</button>
    <button type="button" class="mensagens-menu-item" data-acao="favoritar"><i class="fa fa-star-o"></i> <span>Favoritar</span></button>
    <button type="button" class="mensagens-menu-item" data-acao="fixar"><i class="fa fa-thumb-tack"></i> <span>Fixar no topo</span></button>
    <button type="button" class="mensagens-menu-item" data-acao="lembrete"><i class="fa fa-calendar-plus-o"></i> Criar lembrete</button>
    <?php if (!empty($pode_criar_chamado)) { ?>
    <button type="button" class="mensagens-menu-item" data-acao="chamado"><i class="fa fa-ticket"></i> Criar chamado</button>
    <?php } ?>
    <button type="button" class="mensagens-menu-item" data-acao="visualizacoes"><i class="fa fa-eye"></i> Visualizações</button>
    <button type="button" class="mensagens-menu-item" data-acao="editar"><i class="fa fa-pencil"></i> Editar</button>
    <button type="button" class="mensagens-menu-item is-perigo" data-acao="excluir"><i class="fa fa-trash-o"></i> Excluir</button>
</div>

<!-- Participantes do grupo -->
<div class="modal fade mensagens-grupo-modal" id="mensagens-grupo-modal" tabindex="-1" role="dialog" aria-labelledby="mensagens-grupo-modal-titulo">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="mensagens-grupo-modal-titulo"></h4>
                <small id="mensagens-grupo-modal-sub"></small>
            </div>
            <div class="modal-body">
                <ul class="mensagens-membros" id="mensagens-membros"></ul>
                <div class="mensagens-adicionar" id="mensagens-adicionar" hidden>
                    <h5><i class="fa fa-user-plus"></i> Adicionar participantes</h5>
                    <input type="search" class="form-control input-sm" id="mensagens-busca-adicionar" placeholder="Buscar pessoa" autocomplete="off" aria-label="Buscar pessoa para adicionar">
                    <ul class="mensagens-lista mensagens-lista-modal" id="mensagens-adicionar-pessoas"></ul>
                    <button type="button" class="btn btn-primary btn-sm" id="mensagens-adicionar-btn" disabled><i class="fa fa-plus"></i> Adicionar</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger pull-left" id="mensagens-excluir-grupo" hidden><i class="fa fa-trash"></i> Excluir grupo</button>
                <button type="button" class="btn btn-default" id="mensagens-sair-grupo"><i class="fa fa-sign-out"></i> Sair do grupo</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Visualizações da mensagem -->
<div class="modal fade" id="mensagens-visualizacoes-modal" tabindex="-1" role="dialog" aria-labelledby="mensagens-vis-titulo">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="mensagens-vis-titulo"><i class="fa fa-check-circle-o"></i> Informações de leitura</h4>
            </div>
            <div class="modal-body" id="mensagens-vis-corpo"></div>
        </div>
    </div>
</div>

<!-- Modal: Mensagens fixadas -->
<div class="modal fade" id="mensagens-fixadas-modal" tabindex="-1" role="dialog" aria-labelledby="mensagens-fix-titulo">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="mensagens-fix-titulo"><i class="fa fa-thumb-tack"></i> Mensagens fixadas</h4>
            </div>
            <div class="modal-body" id="mensagens-fix-corpo"></div>
        </div>
    </div>
</div>

<!-- Modal: Mensagens favoritas -->
<div class="modal fade" id="mensagens-favoritas-modal" tabindex="-1" role="dialog" aria-labelledby="mensagens-fav-titulo">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="mensagens-fav-titulo"><i class="fa fa-star text-yellow"></i> Mensagens favoritas</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <select class="form-control input-sm" id="mensagens-fav-conversa"></select>
                </div>
                <div id="mensagens-fav-corpo"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Buscar mensagens -->
<div class="modal fade" id="mensagens-busca-modal" tabindex="-1" role="dialog" aria-labelledby="mensagens-busca-titulo">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="mensagens-busca-titulo"><i class="fa fa-search"></i> Buscar na conversa</h4>
                <small id="mensagens-busca-sub" class="text-muted"></small>
            </div>
            <div class="modal-body">
                <form id="mensagens-busca-form" class="mensagens-busca-filtros">
                    <div class="row">
                        <div class="col-sm-8 form-group">
                            <label for="mensagens-f-q">Texto ou nome do arquivo</label>
                            <input type="search" class="form-control input-sm" id="mensagens-f-q" placeholder="Palavra-chave...">
                        </div>
                        <div class="col-sm-4 form-group">
                            <label for="mensagens-f-usuario">Usuário</label>
                            <select class="form-control input-sm" id="mensagens-f-usuario"></select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3 form-group">
                            <label for="mensagens-f-de">De</label>
                            <input type="date" class="form-control input-sm" id="mensagens-f-de">
                        </div>
                        <div class="col-sm-3 form-group">
                            <label for="mensagens-f-ate">Até</label>
                            <input type="date" class="form-control input-sm" id="mensagens-f-ate">
                        </div>
                        <div class="col-sm-4 form-group">
                            <label for="mensagens-f-tipo">Tipo</label>
                            <select class="form-control input-sm" id="mensagens-f-tipo">
                                <option value="">Todos</option>
                                <option value="texto">Somente texto</option>
                                <option value="imagem">Imagens</option>
                                <option value="video">Vídeos</option>
                                <option value="audio">Áudios</option>
                                <option value="arquivo">Documentos</option>
                            </select>
                        </div>
                        <div class="col-sm-2 form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fa fa-search"></i> Filtrar</button>
                        </div>
                    </div>
                </form>
                <div id="mensagens-busca-resultados"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Encaminhar mensagem -->
<div class="modal fade" id="mensagens-encaminhar-modal" tabindex="-1" role="dialog" aria-labelledby="mensagens-enc-titulo">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="mensagens-enc-titulo"><i class="fa fa-share"></i> Encaminhar mensagem</h4>
            </div>
            <div class="modal-body">
                <div class="mensagens-enc-previa" id="mensagens-enc-previa"></div>
                <input type="search" class="form-control input-sm" id="mensagens-enc-busca" placeholder="Buscar conversa ou pessoa" autocomplete="off">
                <ul class="mensagens-lista mensagens-lista-modal" id="mensagens-enc-lista"></ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="mensagens-enc-enviar" disabled><i class="fa fa-share"></i> Encaminhar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Notificações personalizadas da conversa -->
<div class="modal fade" id="mensagens-notif-modal" tabindex="-1" role="dialog" aria-labelledby="mensagens-notif-titulo">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="mensagens-notif-titulo"><i class="fa fa-bell-o"></i> Notificações</h4>
                <small id="mensagens-notif-sub"></small>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="mensagens-notif-modo">Avisos desta conversa</label>
                    <select class="form-control input-sm" id="mensagens-notif-modo">
                        <option value="todas">Todas as mensagens</option>
                        <option value="mencoes">Somente quando me mencionarem (@)</option>
                        <option value="nenhuma">Nenhuma notificação</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="mensagens-notif-silenciar">Silenciar temporariamente</label>
                    <select class="form-control input-sm" id="mensagens-notif-silenciar">
                        <option value="">Manter configuração atual</option>
                        <option value="0">Reativar notificações</option>
                        <option value="60">Por 1 hora</option>
                        <option value="480">Por 8 horas</option>
                        <option value="1440">Por 24 horas</option>
                        <option value="10080">Por 7 dias</option>
                        <option value="-1">Silenciar sempre</option>
                    </select>
                    <p class="help-block" id="mensagens-notif-estado" style="margin-bottom:0;font-size:12px;"></p>
                </div>
                <hr style="margin:10px 0;">
                <div class="checkbox" style="margin:0 0 8px;">
                    <label><input type="checkbox" id="mensagens-notif-som"> Tocar som ao receber novas mensagens</label>
                </div>
                <button type="button" class="btn btn-default btn-xs btn-block" id="mensagens-notif-navegador">Ativar notificações do navegador</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary btn-sm" id="mensagens-notif-salvar">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Agendar mensagens -->
<div class="modal fade" id="mensagens-agendar-modal" tabindex="-1" role="dialog" aria-labelledby="mensagens-ag-titulo">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="mensagens-ag-titulo"><i class="fa fa-clock-o"></i> Mensagens agendadas</h4>
            </div>
            <div class="modal-body">
                <form id="mensagens-ag-form" style="margin-bottom:14px;">
                    <input type="hidden" id="mensagens-ag-id" value="">
                    <div class="form-group">
                        <label for="mensagens-ag-texto">Mensagem</label>
                        <textarea class="form-control input-sm" id="mensagens-ag-texto" rows="2" placeholder="Escreva a mensagem para agendar"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-sm-7 form-group">
                            <label for="mensagens-ag-data">Data e hora do envio</label>
                            <input type="datetime-local" class="form-control input-sm" id="mensagens-ag-data">
                        </div>
                        <div class="col-sm-5 form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-sm btn-block" id="mensagens-ag-salvar"><i class="fa fa-clock-o"></i> Agendar</button>
                        </div>
                    </div>
                </form>
                <h5>Agendamentos pendentes</h5>
                <div id="mensagens-ag-lista"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Etiquetas e categorias -->
<div class="modal fade" id="mensagens-etiquetas-modal" tabindex="-1" role="dialog" aria-labelledby="mensagens-et-titulo">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="mensagens-et-titulo"><i class="fa fa-tags"></i> Etiquetas</h4>
                <small id="mensagens-et-sub"></small>
            </div>
            <div class="modal-body">
                <div id="mensagens-et-lista" style="margin-bottom:12px;"></div>
                <form id="mensagens-et-form" class="form-inline" style="display:flex;gap:6px;">
                    <input type="text" class="form-control input-sm" id="mensagens-et-nome" maxlength="40" placeholder="Nova etiqueta" style="flex:1;">
                    <input type="color" id="mensagens-et-cor" value="#1E1D3F" style="width:36px;height:30px;padding:1px;border:1px solid #ccc;">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Criar lembrete / Lista de lembretes -->
<div class="modal fade" id="mensagens-lembretes-modal" tabindex="-1" role="dialog" aria-labelledby="mensagens-lem-titulo">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="mensagens-lem-titulo"><i class="fa fa-calendar-check-o"></i> Lembretes</h4>
            </div>
            <div class="modal-body">
                <form id="mensagens-lem-form" hidden style="margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid #e2e8f0;">
                    <input type="hidden" id="mensagens-lem-msg-id" value="">
                    <div class="form-group">
                        <label for="mensagens-lem-nota">Nota do lembrete</label>
                        <input type="text" class="form-control input-sm" id="mensagens-lem-nota" maxlength="255" placeholder="Ex.: Responder cliente sobre orçamento">
                    </div>
                    <div class="form-group">
                        <label>Atalhos rápidos:</label>
                        <div class="btn-group btn-group-xs">
                            <button type="button" class="btn btn-default js-lem-atalho" data-modo="1h">Em 1 hora</button>
                            <button type="button" class="btn btn-default js-lem-atalho" data-modo="hoje18">Hoje às 18h</button>
                            <button type="button" class="btn btn-default js-lem-atalho" data-modo="amanha9">Amanhã às 09h</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-7 form-group">
                            <input type="datetime-local" class="form-control input-sm" id="mensagens-lem-data">
                        </div>
                        <div class="col-sm-5 form-group">
                            <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fa fa-bell-o"></i> Salvar lembrete</button>
                        </div>
                    </div>
                </form>
                <div id="mensagens-lem-lista"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Notas privadas da conversa -->
<div class="modal fade" id="mensagens-notas-modal" tabindex="-1" role="dialog" aria-labelledby="mensagens-notas-titulo">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="mensagens-notas-titulo"><i class="fa fa-sticky-note-o"></i> Notas privadas</h4>
                <small id="mensagens-notas-sub">Visíveis somente para você.</small>
            </div>
            <div class="modal-body">
                <form id="mensagens-notas-form" style="margin-bottom:12px;">
                    <input type="hidden" id="mensagens-nota-id" value="">
                    <div class="form-group">
                        <textarea class="form-control input-sm" id="mensagens-nota-texto" rows="3" placeholder="Anote informações privadas sobre esta conversa..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Salvar nota</button>
                    <button type="button" class="btn btn-default btn-sm" id="mensagens-nota-limpar" hidden>Cancelar edição</button>
                </form>
                <div id="mensagens-notas-lista"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Transformar mensagem em chamado -->
<div class="modal fade" id="mensagens-chamado-modal" tabindex="-1" role="dialog" aria-labelledby="mensagens-ch-titulo">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="mensagens-ch-titulo"><i class="fa fa-ticket"></i> Transformar mensagem em chamado</h4>
            </div>
            <form id="mensagens-chamado-form">
                <div class="modal-body">
                    <input type="hidden" id="mensagens-ch-msg-id" value="">
                    <div class="form-group">
                        <label for="mensagens-ch-titulo-inp">Título *</label>
                        <input type="text" class="form-control input-sm" id="mensagens-ch-titulo-inp" maxlength="200" required>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label for="mensagens-ch-unidade">Unidade / Cliente *</label>
                            <select class="form-control input-sm" id="mensagens-ch-unidade" required>
                                <option value="">Selecione...</option>
                                <?php foreach ((array) $opcoes_chamado['unidades'] as $un) { ?>
                                    <option value="<?php echo (int) $un->id ?>"><?php echo html_escape($un->unidade) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label for="mensagens-ch-responsavel">Responsável</label>
                            <select class="form-control input-sm" id="mensagens-ch-responsavel"></select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 form-group">
                            <label for="mensagens-ch-status">Status</label>
                            <select class="form-control input-sm" id="mensagens-ch-status">
                                <?php foreach ((array) $opcoes_chamado['status'] as $st) { ?>
                                    <option value="<?php echo (int) $st->id ?>"><?php echo html_escape($st->descricao) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-sm-4 form-group">
                            <label for="mensagens-ch-prioridade">Prioridade</label>
                            <select class="form-control input-sm" id="mensagens-ch-prioridade">
                                <?php foreach ((array) $opcoes_chamado['prioridades'] as $pr) { ?>
                                    <option value="<?php echo (int) $pr->id ?>" <?php echo (int) $pr->id === 2 ? 'selected' : '' ?>><?php echo html_escape($pr->descricao) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-sm-4 form-group">
                            <label for="mensagens-ch-prazo">Prazo</label>
                            <input type="datetime-local" class="form-control input-sm" id="mensagens-ch-prazo">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="mensagens-ch-desc">Descrição</label>
                        <textarea class="form-control input-sm" id="mensagens-ch-desc" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="mensagens-ch-salvar"><i class="fa fa-ticket"></i> Criar chamado</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Status de disponibilidade -->
<div class="modal fade" id="mensagens-status-modal" tabindex="-1" role="dialog" aria-labelledby="mensagens-st-titulo">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="mensagens-st-titulo">Status de disponibilidade</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="mensagens-st-select">Status</label>
                    <select class="form-control input-sm" id="mensagens-st-select">
                        <option value="online">Online</option>
                        <option value="reuniao">Em reunião</option>
                        <option value="ocupado">Ocupado</option>
                        <option value="atendimento">Em atendimento</option>
                        <option value="ausente">Ausente</option>
                        <option value="offline">Offline</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="mensagens-st-texto">Recado curto (opcional)</label>
                    <input type="text" class="form-control input-sm" id="mensagens-st-texto" maxlength="80" placeholder="Ex.: Em call até 15h">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary btn-sm" id="mensagens-st-salvar">Atualizar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Caixa de mídias -->
<div class="modal fade" id="mensagens-midias-modal" tabindex="-1" role="dialog" aria-labelledby="mensagens-midias-titulo">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="mensagens-midias-titulo"><i class="fa fa-folder-open-o"></i> Caixa de mídias</h4>
                <small id="mensagens-midias-sub"></small>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="mensagens-midias-tabs" style="margin-bottom:12px;">
                    <li class="active"><a href="#" data-tipo="">Tudo</a></li>
                    <li><a href="#" data-tipo="imagem">Imagens</a></li>
                    <li><a href="#" data-tipo="video">Vídeos</a></li>
                    <li><a href="#" data-tipo="audio">Áudios</a></li>
                    <li><a href="#" data-tipo="arquivo">Documentos</a></li>
                </ul>
                <div id="mensagens-midias-corpo"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Criar / Editar Canal de mensagens -->
<div class="modal fade" id="mensagens-canal-modal" tabindex="-1" role="dialog" aria-labelledby="mensagens-canal-titulo">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="mensagens-canal-titulo"><i class="fa fa-bullhorn"></i> Novo canal</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="mensagens-canal-id" value="">
                <div class="form-group">
                    <label for="mensagens-canal-nome">Nome do canal *</label>
                    <input type="text" class="form-control input-sm" id="mensagens-canal-nome" maxlength="<?php echo (int) $tamanho_nome_grupo ?>" placeholder="Ex.: Avisos Gerais">
                </div>
                <div class="form-group">
                    <label for="mensagens-canal-desc">Descrição (opcional)</label>
                    <input type="text" class="form-control input-sm" id="mensagens-canal-desc" maxlength="255" placeholder="Objetivo do canal">
                </div>
                <div class="form-group">
                    <label>Grupos de usuários que podem ver este canal *</label>
                    <div class="mensagens-checklist" id="mensagens-canal-grupos">
                        <?php foreach ((array) $grupos_empresa as $gr) { ?>
                            <label class="checkbox-inline" style="margin-left:0;margin-right:12px;">
                                <input type="checkbox" value="<?php echo (int) $gr->id ?>"> <?php echo html_escape($gr->name) ?>
                            </label>
                        <?php } ?>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="mensagens-canal-busca-escritor">Usuários autorizados a enviar mensagens</label>
                    <p class="help-block" style="margin:0 0 6px;font-size:12px;">Use <code>@todos</code> no canal para notificar todos que o visualizam.</p>
                    <ul class="mensagens-escolhidos" id="mensagens-canal-escritores-tags"></ul>
                    <div class="input-group input-group-sm" style="margin-bottom:6px;">
                        <span class="input-group-addon"><i class="fa fa-search"></i></span>
                        <input type="search" class="form-control" id="mensagens-canal-busca-escritor" placeholder="Digite o nome do usuário para buscar..." autocomplete="off">
                    </div>
                    <div class="mensagens-checklist" id="mensagens-canal-escritores" style="max-height:160px;overflow-y:auto;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm pull-left" id="mensagens-canal-excluir" hidden><i class="fa fa-trash"></i> Excluir canal</button>
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" id="mensagens-canal-salvar"><i class="fa fa-check"></i> Salvar canal</button>
            </div>
        </div>
    </div>
</div>

<!-- Popup rápido de reações com emojis -->
<div class="mensagens-reacoes-popup" id="mensagens-reacoes-popup" role="dialog" aria-label="Reagir à mensagem" hidden></div>

<!-- Modal: Nova enquete no canal -->
<div class="modal fade" id="mensagens-enquete-modal" tabindex="-1" role="dialog" aria-labelledby="mensagens-eq-titulo">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="mensagens-enquete-form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="mensagens-eq-titulo"><i class="fa fa-bar-chart"></i> Nova enquete no canal</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="mensagens-eq-pergunta">Pergunta *</label>
                        <input type="text" class="form-control input-sm" id="mensagens-eq-pergunta" maxlength="255" placeholder="Ex.: Qual horário funciona melhor para a equipe?">
                    </div>
                    <div class="form-group">
                        <label>Alternativas *</label>
                        <div id="mensagens-eq-opcoes"></div>
                        <button type="button" class="btn btn-default btn-xs" id="mensagens-eq-add-op" style="margin-top:6px;"><i class="fa fa-plus"></i> Adicionar alternativa</button>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <div class="checkbox" style="margin:4px 0;">
                                <label><input type="checkbox" id="mensagens-eq-anonima"> Votação anônima</label>
                            </div>
                            <div class="checkbox" style="margin:4px 0;">
                                <label><input type="checkbox" id="mensagens-eq-multipla"> Permitir várias escolhas</label>
                            </div>
                        </div>
                        <div class="col-sm-6 form-group" style="margin-bottom:0;">
                            <label for="mensagens-eq-prazo">Prazo de votação (opcional)</label>
                            <input type="datetime-local" class="form-control input-sm" id="mensagens-eq-prazo">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="mensagens-eq-salvar"><i class="fa fa-check"></i> Publicar enquete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Novo evento / reunião no canal -->
<div class="modal fade" id="mensagens-reuniao-modal" tabindex="-1" role="dialog" aria-labelledby="mensagens-reu-modal-titulo">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="mensagens-reuniao-form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="mensagens-reu-modal-titulo"><i class="fa fa-calendar-plus-o"></i> Nova reunião no canal</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-7 form-group">
                            <label for="mensagens-reu-titulo-inp">Assunto da reunião *</label>
                            <input type="text" class="form-control input-sm" id="mensagens-reu-titulo-inp" maxlength="160" placeholder="Ex.: Alinhamento semanal de chamados">
                        </div>
                        <div class="col-sm-5 form-group">
                            <label for="mensagens-reu-inicio">Data e horário *</label>
                            <input type="datetime-local" class="form-control input-sm" id="mensagens-reu-inicio">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mensagens-reu-desc">Pauta / Descrição (opcional)</label>
                        <textarea class="form-control input-sm" id="mensagens-reu-desc" rows="2" placeholder="Detalhes do encontro..."></textarea>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Participantes convidados</label>
                        <div class="mensagens-checklist" id="mensagens-reu-participantes" style="max-height:150px;overflow-y:auto;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="mensagens-reu-salvar"><i class="fa fa-check"></i> Agendar reunião</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    window.addEventListener('load', function() {
        if (typeof $ === 'undefined') { return; }
        var api = <?php echo json_encode(site_url('mensagens_api'), $json) ?>;
        var urlTela = <?php echo json_encode(site_url('mensagens/index'), $json) ?>;
        var maximo = <?php echo (int) $tamanho_maximo ?>;
        var limiteArquivo = <?php echo (int) $tamanho_anexo_mb ?> * 1024 * 1024;
        var anexosAtivos = <?php echo $anexos_ativos ? 'true' : 'false' ?>;
        var minimoGrupo = <?php echo (int) $minimo_grupo ?>;
        var meuId = <?php echo (int) $this->session->userdata('user_id') ?>;
        var meuNome = <?php echo json_encode($meu_nome, $json) ?>;
        var conversas = <?php echo json_encode($conversas, $json) ?>;
        var pessoas = <?php echo json_encode($pessoas, $json) ?>;
        var atual = <?php echo json_encode($atual, $json) ?>;
        var conversaInicial = <?php echo json_encode($conversa, $json) ?>;
        var vistoAte = <?php echo (int) $visto_ate ?>;
        var entregueAte = <?php echo (int) $entregue_ate ?>;
        var fixadasAtuais = <?php echo json_encode($fixadas, $json) ?>;
        var configAtual = <?php echo json_encode($config_conversa, $json) ?>;
        var membrosAtual = <?php echo json_encode($membros_atual, $json) ?>;
        var etiquetasLista = <?php echo json_encode($etiquetas, $json) ?>;
        var lembretesLista = <?php echo json_encode($lembretes, $json) ?>;
        var agendadasLista = <?php echo json_encode($agendadas, $json) ?>;
        var meuStatus = <?php echo json_encode($meu_status, $json) ?>;
        var canaisAtivos = <?php echo $canais_ativos ? 'true' : 'false' ?>;
        var gruposEmpresa = <?php echo json_encode($grupos_empresa, $json) ?>;
        var emojis = [
            '😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇', '🙂', '😉', '😍', '🥰', '😘', '😋', '😜', '🤪', '🤔', '🤨', '😐', '🙄', '😏', '😬', '😌', '😴', '😷', '🤯', '🥳', '😎', '🤓', '😕', '🙁', '😮', '😳', '🥺', '😢', '😭', '😱', '😤', '😡',
            '👍', '👎', '👌', '✌️', '🤞', '🤝', '👏', '🙌', '🙏', '💪', '👋', '👉', '👈', '👆', '👇', '✋', '🤙', '✍️',
            '❤️', '💔', '⭐', '🔥', '✨', '🎉', '🎂', '🎁', '✅', '❌', '⚠️', '❗', '❓', '💯', '➡️', '⬅️', '🔴', '🟡', '🟢', '🔵', '⏳', '⏰',
            '☕', '🍕', '🍔', '🍺', '💻', '🖥️', '📱', '📞', '📧', '📎', '📌', '📅', '📈', '📉', '💰', '💡', '🔧', '🛠️', '🐞', '🚀', '🔒', '🔑'
        ];
        var ordem = { lista: 0, nova: 1, grupo: 2, conversa: 3 };
        var tela = 'lista';
        var ultimoId = 0;
        var primeiroId = 0;
        var mensagensMapa = {};
        var enviando = false;
        var pendentes = [];
        var empilhado = false;
        var escolhidosGrupo = [];
        var escolhidosAdicionar = [];
        var membrosGrupo = [];
        var telaAntesGrupo = 'lista';
        var gravacao = null;
        var modoContexto = null; // { tipo: 'responder'|'editar', msg: obj }
        var mencoesSelecionadas = {}; // { id: nome }
        var mencaoConsulta = null; // { inicio, fim, candidatos, indice }
        var digitandoLista = {};
        var digitandoAtual = [];
        var ultimoEnvioDigitando = 0;
        var timerPararDigitando = null;
        var timerRascunho = null;
        var filtroEtiqueta = '';
        var ultimoEstadoEtiquetas = '';
        var menuMsgId = 0;
        var encaminharMsgId = 0;
        var encaminharDestinos = [];
        var podeCarregarMais = true;
        var carregandoAnteriores = false;
        var chamadaAtiva = null;
        var chamadaConversaAtiva = null;
        var statusRotulos = {
            online: { rotulo: 'Online', cor: '#00a65a' },
            reuniao: { rotulo: 'Em reunião', cor: '#605ca8' },
            ocupado: { rotulo: 'Ocupado', cor: '#dd4b39' },
            atendimento: { rotulo: 'Em atendimento', cor: '#00c0ef' },
            ausente: { rotulo: 'Ausente', cor: '#f39c12' },
            offline: { rotulo: 'Offline', cor: '#94a3b8' }
        };

        var $app = $('#mensagens-app');
        var $historico = $('#mensagens-historico');
        var $texto = $('#mensagens-texto');
        var $modal = $('#mensagens-grupo-modal');
        var $menuMsg = $('#mensagens-menu-msg');

        /* ---------- Confirmações e utilitários ---------- */

        function confirmar(opcoes) {
            if (window.Swal) {
                return window.Swal.fire($.extend({
                    icon: 'warning', showCancelButton: true, reverseButtons: true, focusCancel: true,
                    confirmButtonText: 'Sim', cancelButtonText: 'Cancelar', confirmButtonColor: '#1E1D3F'
                }, opcoes)).then(function(r) { return r.isConfirmed; });
            }
            return Promise.resolve(window.confirm(opcoes.title + (opcoes.text ? '\n\n' + opcoes.text : '')));
        }

        function avisar(icone, titulo, texto) {
            if (window.Swal) {
                window.Swal.fire({ icon: icone, title: titulo, text: texto || '', confirmButtonColor: '#1E1D3F' });
            } else {
                window.alert(titulo + (texto ? '\n\n' + texto : ''));
            }
        }

        function erroDe(xhr) {
            return xhr && xhr.responseJSON && xhr.responseJSON.erro ? xhr.responseJSON.erro : 'Não foi possível falar com o servidor. Tente de novo.';
        }

        function semAcento(texto) {
            texto = String(texto || '').toLowerCase();
            return texto.normalize ? texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '') : texto;
        }

        function avatar(c, comStatus) {
            var $el;
            if (c && c.is_bot) {
                $el = $('<span class="mensagens-avatar mensagens-avatar-bot" aria-hidden="true"><i class="fa fa-android"></i></span>');
            } else if (c && c.tipo === 'canal') {
                $el = $('<span class="mensagens-avatar mensagens-avatar-canal" aria-hidden="true"><i class="fa fa-bullhorn"></i></span>');
            } else if (c && c.foto) {
                $el = $('<img class="img-circle mensagens-avatar" alt="">').attr('src', c.foto);
            } else {
                $el = $('<span class="mensagens-avatar mensagens-avatar-grupo" aria-hidden="true"><i class="fa fa-users"></i></span>');
            }
            if (comStatus && c && c.tipo !== 'grupo' && c.tipo !== 'canal' && !c.is_bot && c.status) {
                var st = c.status;
                var info = statusRotulos[st] || statusRotulos.offline;
                return $('<span class="mensagens-avatar-wrap">').append(
                    $el,
                    $('<span class="mensagens-status-dot">').addClass('st-' + st).attr('title', info.rotulo + (c.status_texto ? ' — ' + c.status_texto : ''))
                );
            }
            return $el;
        }

        function atualizarIndicadoresGerais() {
            var qtdLem = $.grep(lembretesLista || [], function(l) { return !l.concluido; }).length;
            $('#mensagens-lembretes-qtd').text(qtdLem).prop('hidden', !qtdLem);
            var qtdAg = (agendadasLista || []).length;
            $('#mensagens-agendadas-qtd').text(qtdAg).prop('hidden', !qtdAg);
            $('#mensagens-topo-alerta').prop('hidden', (qtdLem + qtdAg) === 0);
            var st = (meuStatus && meuStatus.status) || 'online';
            var info = statusRotulos[st] || statusRotulos.online;
            $('#mensagens-meu-status-dot').attr('class', 'mensagens-status-dot st-' + st);
            $('#mensagens-meu-status-rotulo').text(info.rotulo);
            $('#mensagens-meu-status-texto').text((meuStatus && meuStatus.texto) ? '· ' + meuStatus.texto : '');
        }

        function renderizarEtiquetasBar() {
            var $bar = $('#mensagens-etiquetas-bar');
            if (!$bar.length) { return; }
            if ($bar.find('.mensagens-etiqueta-dropdown.open').length) { return; }
            var estadoEtiquetas = JSON.stringify({ f: filtroEtiqueta, l: etiquetasLista || [] });
            if (estadoEtiquetas === ultimoEstadoEtiquetas && $bar.children().length) { return; }
            ultimoEstadoEtiquetas = estadoEtiquetas;
            $bar.empty();

            var ehTodas = filtroEtiqueta === '';
            var ehNaoLidas = filtroEtiqueta === '__nao_lidas__';
            var $rapidos = $('<div class="btn-group btn-group-xs mensagens-filtros-rapidos" role="group">').append(
                $('<button type="button" class="btn">')
                    .toggleClass('btn-primary is-ativa', ehTodas)
                    .toggleClass('btn-default', !ehTodas)
                    .attr('data-etiqueta', '')
                    .text('Todas'),
                $('<button type="button" class="btn">')
                    .toggleClass('btn-primary is-ativa', ehNaoLidas)
                    .toggleClass('btn-default', !ehNaoLidas)
                    .attr('data-etiqueta', '__nao_lidas__')
                    .text('Não lidas')
            );
            $bar.append($rapidos);

            if (etiquetasLista && etiquetasLista.length) {
                var ativaEt = $.grep(etiquetasLista, function(et) { return String(et.id) === String(filtroEtiqueta); })[0] || null;
                var $btnDrop = $('<button type="button" class="btn btn-default btn-xs dropdown-toggle mensagens-etiqueta-select-btn" data-toggle="dropdown" aria-expanded="false">');
                if (ativaEt) {
                    $btnDrop.addClass('is-filtrada').append(
                        $('<span class="mensagens-chip-cor">').css('background', ativaEt.cor),
                        $('<span>').text(ativaEt.nome),
                        ' <span class="caret"></span>'
                    );
                } else {
                    $btnDrop.html('<i class="fa fa-tag text-muted"></i> Categorias <span class="caret"></span>');
                }
                var $menu = $('<ul class="dropdown-menu dropdown-menu-right mensagens-etiqueta-menu">').append(
                    $('<li>').toggleClass('active', !ativaEt).append(
                        $('<a href="#" data-etiqueta="">').html('<i class="fa fa-th-list text-muted"></i> Todas as categorias')
                    )
                );
                $.each(etiquetasLista, function(i, et) {
                    var sel = String(filtroEtiqueta) === String(et.id);
                    $menu.append(
                        $('<li>').toggleClass('active', sel).append(
                            $('<a href="#">').attr('data-etiqueta', et.id).append(
                                $('<span class="mensagens-chip-cor">').css('background', et.cor),
                                $('<span>').text(et.nome),
                                sel ? ' <i class="fa fa-check pull-right text-primary"></i>' : null
                            )
                        )
                    );
                });
                $bar.append($('<div class="dropdown mensagens-etiqueta-dropdown">').append($btnDrop, $menu));
            }
            $bar.prop('hidden', false);
        }

        /* ---------- Telas ---------- */

        function irPara(nome) {
            tela = nome;
            $app.attr('data-tela', nome);
            $app.find('.mensagens-tela').each(function() {
                var posicao = ordem[$(this).attr('data-tela')] - ordem[nome];
                $(this).toggleClass('is-ativa', posicao === 0).toggleClass('is-esquerda', posicao < 0).toggleClass('is-direita', posicao > 0)
                    .attr('aria-hidden', posicao === 0 ? 'false' : 'true');
            });
            if (nome !== 'conversa') {
                window.mensagensConversaAberta = null;
                cancelarGravacao();
                enviarEstadoDigitando('');
                fecharEmojis();
                fecharMencoes();
                fecharMenuMensagem();
                if (typeof fecharPopupReacoes === 'function') { fecharPopupReacoes(); }
                renderizarListas();
            }
        }

        function voltarParaLista() {
            if (tela === 'conversa' && empilhado) {
                window.history.back();
                return;
            }
            irPara('lista');
            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, '', urlTela);
            }
        }

        /* ---------- Listas ---------- */

        function itemConversa(c) {
            var dig = digitandoLista[c.chave];
            var temRascunho = !dig && c.rascunho && $.trim(c.rascunho) !== '';
            var padraoSemMsg = c.is_bot ? 'Atualizações automáticas de chamados' : (c.tipo === 'canal' ? 'Canal de mensagens' : '');
            var previaTexto = dig ? dig.texto : (temRascunho ? 'Rascunho: ' + c.rascunho : ((c.ultima_minha ? 'Você: ' : (c.ultima_autor ? c.ultima_autor + ': ' : '')) + (c.ultima || padraoSemMsg)));
            var $ultima = $('<span class="mensagens-contato-ultima">')
                .toggleClass('is-digitando', !!dig)
                .toggleClass('is-rascunho', !!temRascunho)
                .text(previaTexto);
            var $nome = $('<strong class="mensagens-contato-nome">').text(c.nome);
            if (c.is_bot) {
                $nome.prepend('<span class="label label-info mensagens-bot-tag" title="Bot automático">BOT</span> ');
            } else if (c.tipo === 'canal') {
                $nome.prepend('<span class="label label-purple mensagens-canal-tag" title="Canal"><i class="fa fa-bullhorn"></i></span> ');
            }
            if (c.silenciada) {
                $nome.append(' <i class="fa fa-bell-slash-o text-muted" title="Silenciada"></i>');
            }
            var $linhaTop = $('<span class="mensagens-contato-linha">')
                .append($nome)
                .append($('<small class="mensagens-contato-quando">').text(c.quando || ''));
            var $linhaBot = $('<span class="mensagens-contato-linha">').append($ultima);
            if (c.mencoes > 0) {
                $linhaBot.append($('<span class="badge mensagens-contato-mencao" title="Mencionou você">@</span>'));
            }
            if (c.nao_lidas > 0) {
                $linhaBot.append($('<span class="badge mensagens-contato-badge">').text(c.nao_lidas));
            } else if (c.marcada_nao_lida) {
                $linhaBot.append($('<span class="badge mensagens-contato-badge is-manual" title="Marcada como não lida"><i class="fa fa-circle"></i></span>'));
            }
            var $corpo = $('<span class="mensagens-contato-corpo">').append($linhaTop, $linhaBot);
            if (c.etiquetas && c.etiquetas.length) {
                var $tags = $('<span class="mensagens-contato-tags">');
                $.each(c.etiquetas, function(i, et) {
                    $tags.append($('<span class="mensagens-mini-tag">').css('background', et.cor).text(et.nome));
                });
                $corpo.append($tags);
            }
            return $('<li>').append(
                $('<button type="button" class="mensagens-contato js-abrir">').attr('data-chave', c.chave)
                    .toggleClass('tem-nao-lidas', c.nao_lidas > 0 || !!c.marcada_nao_lida)
                    .toggleClass('is-ativo', !!atual && tela === 'conversa' && atual.chave === c.chave)
                    .append(avatar(c, true), $corpo)
            );
        }

        function itemPessoa(p, marcado) {
            var sub = marcado === undefined
                ? (p.status_texto ? p.status_texto : (p.status && statusRotulos[p.status] ? statusRotulos[p.status].rotulo : (p.ultima_id ? 'Já conversaram' : 'Começar conversa')))
                : (marcado ? 'Escolhida' : 'Toque para escolher');
            var $botao = $('<button type="button" class="mensagens-contato">').attr('data-id', p.id)
                .append(avatar(p, marcado === undefined), $('<span class="mensagens-contato-corpo">')
                    .append($('<span class="mensagens-contato-linha">').append($('<strong class="mensagens-contato-nome">').text(p.nome)))
                    .append($('<span class="mensagens-contato-linha">').append($('<span class="mensagens-contato-ultima">').text(sub))));
            if (marcado === undefined) {
                $botao.addClass('js-abrir').attr('data-chave', p.chave);
            } else {
                $botao.addClass('is-selecionavel').toggleClass('is-marcado', marcado).attr('aria-pressed', marcado ? 'true' : 'false')
                    .append($('<span class="mensagens-marca" aria-hidden="true">').append(marcado ? '<i class="fa fa-check"></i>' : null));
            }
            return $('<li>').append($botao);
        }

        function filtrar(lista, termo) {
            termo = semAcento(termo);
            return $.grep(lista, function(c) { return !termo || semAcento(c.nome).indexOf(termo) !== -1; });
        }

        function vazio(texto, comBotao) {
            return $('<li class="mensagens-lista-vazia">').append(
                comBotao ? $('<i class="fa fa-comments-o">') : null,
                $('<p>').text(texto),
                comBotao ? $('<button type="button" class="btn btn-primary btn-sm js-ir-nova"><i class="fa fa-plus"></i> Nova conversa</button>') : null
            );
        }

        function renderizarListas() {
            atualizarIndicadoresGerais();
            renderizarEtiquetasBar();
            var $lista = $('#mensagens-lista').empty();
            var visiveis = filtrar(conversas, $('#mensagens-busca').val());
            if (filtroEtiqueta === '__nao_lidas__') {
                visiveis = $.grep(visiveis, function(c) { return c.nao_lidas > 0 || !!c.marcada_nao_lida; });
            } else if (filtroEtiqueta !== '') {
                visiveis = $.grep(visiveis, function(c) {
                    return $.grep(c.etiquetas || [], function(et) { return String(et.id) === String(filtroEtiqueta); }).length > 0;
                });
            }
            $.each(visiveis, function(i, c) { $lista.append(itemConversa(c)); });
            if (!visiveis.length) {
                $lista.append(vazio(conversas.length ? 'Nenhuma conversa encontrada.' : 'Você ainda não tem conversas.', !conversas.length));
            }

            var $pessoas = $('#mensagens-pessoas').empty();
            var ordenadas = pessoas.slice().sort(function(a, b) { return a.nome.localeCompare(b.nome); });
            var achadas = filtrar(ordenadas, $('#mensagens-busca-pessoas').val());
            $.each(achadas, function(i, p) { $pessoas.append(itemPessoa(p)); });
            if (!achadas.length) {
                $pessoas.append(vazio(pessoas.length ? 'Ninguém encontrado.' : 'Nenhuma pessoa com acesso às mensagens.'));
            }
            renderizarEscolhaGrupo();
        }

        /* ---------- Novo grupo ---------- */

        function pessoaPorId(id) {
            return $.grep(pessoas, function(p) { return p.id === id; })[0];
        }

        function renderizarEscolhaGrupo() {
            var $lista = $('#mensagens-grupo-pessoas').empty();
            var ordenadas = $.grep(pessoas, function(p) { return !p.is_bot; }).sort(function(a, b) { return a.nome.localeCompare(b.nome); });
            var achadas = filtrar(ordenadas, $('#mensagens-busca-grupo').val());
            $.each(achadas, function(i, p) { $lista.append(itemPessoa(p, $.inArray(p.id, escolhidosGrupo) !== -1)); });
            if (!achadas.length) { $lista.append(vazio('Ninguém encontrado.')); }

            var $escolhidos = $('#mensagens-grupo-escolhidos').empty();
            $.each(escolhidosGrupo, function(i, id) {
                var p = pessoaPorId(id);
                if (!p) { return; }
                $escolhidos.append($('<li class="mensagens-escolhido">').append(
                    $('<span>').text(p.nome),
                    $('<button type="button" class="mensagens-escolhido-remover" aria-label="Tirar do grupo">&times;</button>').attr('data-id', id)
                ));
            });
            var faltam = Math.max(0, minimoGrupo - 1 - escolhidosGrupo.length);
            $('#mensagens-grupo-resumo').text(faltam
                ? 'Escolha mais ' + faltam + (faltam > 1 ? ' pessoas' : ' pessoa') + ' (o grupo precisa de ' + minimoGrupo + ' participantes ou mais, contando você).'
                : (escolhidosGrupo.length + 1) + ' participantes, contando você.');
            $('#mensagens-grupo-criar').prop('disabled', !!faltam || !$.trim($('#mensagens-grupo-nome').val()));
        }

        function alternar(lista, id) {
            var i = $.inArray(id, lista);
            if (i === -1) { lista.push(id); } else { lista.splice(i, 1); }
        }

        function criarGrupo() {
            var nome = $.trim($('#mensagens-grupo-nome').val());
            if (!nome || escolhidosGrupo.length < minimoGrupo - 1) { return; }
            confirmar({
                icon: 'question',
                title: 'Criar o grupo "' + nome + '"?',
                text: 'Ele vai ter ' + (escolhidosGrupo.length + 1) + ' participantes, contando você.',
                confirmButtonText: 'Sim, criar grupo'
            }).then(function(ok) {
                if (!ok) { return; }
                $('#mensagens-grupo-criar').prop('disabled', true);
                $.ajax({ url: api + '/grupo_criar', method: 'POST', dataType: 'json', data: { nome: nome, membros: escolhidosGrupo } }).done(function(res) {
                    if (!res || !res.ok) { return; }
                    escolhidosGrupo = [];
                    $('#mensagens-grupo-nome, #mensagens-busca-grupo').val('');
                    mostrarConversa(res);
                    empilharHistorico(res.conversa.chave);
                    atualizar();
                }).fail(function(xhr) {
                    avisar('error', 'Não foi possível criar o grupo', erroDe(xhr));
                }).always(renderizarEscolhaGrupo);
            });
        }

        /* ---------- Conversa e renderização de bolhas ---------- */

        var icones = { pdf: 'fa-file-pdf-o', doc: 'fa-file-word-o', docx: 'fa-file-word-o', odt: 'fa-file-word-o', rtf: 'fa-file-word-o', xls: 'fa-file-excel-o', xlsx: 'fa-file-excel-o', xlsm: 'fa-file-excel-o', ods: 'fa-file-excel-o', csv: 'fa-file-excel-o', ppt: 'fa-file-powerpoint-o', pptx: 'fa-file-powerpoint-o', pps: 'fa-file-powerpoint-o', ppsx: 'fa-file-powerpoint-o', odp: 'fa-file-powerpoint-o', zip: 'fa-file-archive-o', rar: 'fa-file-archive-o', txt: 'fa-file-text-o', xml: 'fa-file-code-o', json: 'fa-file-code-o' };

        function linkBaixar(a) {
            return $('<a class="mensagens-anexo-baixar-link">').attr({ href: a.baixar, download: a.nome || '', title: 'Baixar ' + a.nome })
                .append('<i class="fa fa-download"></i> ', $('<span>').text('Baixar · ' + a.tamanho));
        }

        function criarPlayerAudio(a) {
            var $wrap = $('<div class="mensagens-audio-wrap">');
            var $audio = $('<audio class="mensagens-anexo-audio" controls preload="metadata">').attr('src', a.url);
            $audio.on('error', function() {
                if (!$wrap.find('.mensagens-audio-erro').length) {
                    $wrap.append($('<small class="mensagens-audio-erro text-danger"><i class="fa fa-exclamation-circle"></i> Áudio indisponível neste servidor.</small>'));
                }
            });
            return $wrap.append($audio);
        }

        function conteudoAnexo(a, m) {
            if (a.tipo === 'imagem') {
                return $('<div class="mensagens-anexo-com-baixar">').append(
                    $('<a class="mensagens-anexo-imagem" target="_blank" rel="noopener">').attr({ href: a.url, title: a.nome })
                        .append($('<img loading="lazy">').attr({ src: a.url, alt: a.nome }).on('load', manterNoFim)),
                    linkBaixar(a));
            }
            if (a.tipo === 'video') {
                return $('<div class="mensagens-anexo-com-baixar">').append(
                    $('<video class="mensagens-anexo-midia" controls preload="metadata">').attr('src', a.url).on('loadedmetadata', manterNoFim),
                    linkBaixar(a));
            }
            if (a.tipo === 'audio') {
                if (m && m.transcricao) {
                    var $playerCol = $('<div class="mensagens-audio-player-col">').append(
                        criarPlayerAudio(a),
                        linkBaixar(a)
                    );
                    var $transCol = $('<div class="mensagens-audio-trans-col">').append(
                        $('<div class="mensagens-transcricao-label"><i class="fa fa-file-text-o"></i> Transcrição</div>'),
                        $('<div class="mensagens-transcricao-texto">').text(m.transcricao)
                    );
                    return $('<div class="mensagens-audio-lado">').append($playerCol, $transCol);
                }
                var $acoesAudio = $('<div class="mensagens-audio-acoes">').append(linkBaixar(a));
                if (m && m.id) {
                    $acoesAudio.append(
                        $('<span class="mensagens-audio-sep" aria-hidden="true">·</span>'),
                        $('<button type="button" class="mensagens-anexo-acao-link js-transcrever-audio">')
                            .attr('data-id', m.id)
                            .html('<i class="fa fa-file-text-o"></i> Transcrever')
                    );
                }
                return $('<div class="mensagens-anexo-com-baixar">').append(
                    criarPlayerAudio(a),
                    $acoesAudio
                );
            }
            return $('<a class="mensagens-anexo-arquivo">').attr({ href: a.baixar, download: a.nome || '', title: 'Baixar ' + a.nome }).append(
                $('<i class="fa">').addClass(icones[a.extensao] || 'fa-file-o'),
                $('<span class="mensagens-anexo-info">').append($('<strong>').text(a.nome), $('<small>').text(a.tamanho)),
                $('<i class="fa fa-download mensagens-anexo-baixar">')
            );
        }

        function participantesMencionaveis() {
            var lista = [];
            if (!atual) { return lista; }
            if (atual.tipo === 'canal') {
                lista.push({ id: 'todos', nome: 'todos', tipo: 'canal', foto: null });
            }
            if (atual.tipo === 'grupo' || atual.tipo === 'canal') {
                $.each(membrosAtual || [], function(i, mb) {
                    if (mb.id !== meuId) { lista.push(mb); }
                });
            } else {
                lista.push({ id: atual.id, nome: atual.nome, foto: atual.foto });
            }
            return lista;
        }

        function construirTextoComMencoes(texto) {
            var $span = $('<span class="mensagens-bolha-texto">');
            texto = String(texto || '');
            var nomes = [];
            if (meuNome) { nomes.push({ nome: meuNome, eu: true }); }
            if (atual && atual.tipo === 'canal') { nomes.push({ nome: 'todos', eu: true }); }
            $.each(participantesMencionaveis(), function(i, p) {
                if (p.nome && p.id !== 'todos') { nomes.push({ nome: p.nome, eu: false }); }
            });
            nomes.sort(function(a, b) { return b.nome.length - a.nome.length; });
            if (!nomes.length || texto.indexOf('@') === -1) {
                return $span.text(texto);
            }
            var esc = function(s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); };
            var padrao = new RegExp('(^|\\s)(@(?:' + $.map(nomes, function(n) { return esc(n.nome); }).join('|') + '))(?=$|\\s|[,.;:!?])', 'gi');
            var ultimo = 0;
            var match;
            while ((match = padrao.exec(texto)) !== null) {
                var inicioMencao = match.index + match[1].length;
                if (inicioMencao > ultimo) {
                    $span.append(document.createTextNode(texto.slice(ultimo, inicioMencao)));
                }
                var mencaoTxt = match[2];
                var corpoMencao = semAcento(mencaoTxt.slice(1));
                var ehEu = (meuNome && corpoMencao === semAcento(meuNome)) || corpoMencao === 'todos';
                $span.append($('<span class="mensagens-mencao-tag">').toggleClass('is-eu', !!ehEu).text(mencaoTxt));
                ultimo = inicioMencao + mencaoTxt.length;
            }
            if (ultimo < texto.length) {
                $span.append(document.createTextNode(texto.slice(ultimo)));
            }
            return $span;
        }

        function elementoTicks(m) {
            if (!m.minha || m.sistema || m.excluida) { return null; }
            var st = m.status || 'enviada';
            if (atual && atual.tipo === 'direta') {
                if (m.id <= vistoAte) { st = 'lida'; }
                else if (m.id <= entregueAte && st === 'enviada') { st = 'entregue'; }
            }
            var titulo = st === 'lida' ? 'Visualizada' : (st === 'entregue' ? 'Entregue' : 'Enviada');
            if (atual && (atual.tipo === 'grupo' || atual.tipo === 'canal') && m.total_grupo > 0) {
                titulo = 'Visualizada por ' + (m.lidas_grupo || 0) + ' de ' + m.total_grupo + ' (clique para detalhes)';
            }
            var $btn = $('<button type="button" class="mensagens-ticks js-ver-visualizacoes">')
                .addClass('is-' + st)
                .attr({ title: titulo, 'aria-label': titulo, 'data-id': m.id });
            if (st === 'enviada') {
                $btn.html('<i class="fa fa-check"></i>');
            } else {
                $btn.html('<i class="fa fa-check"></i><i class="fa fa-check"></i>');
            }
            return $btn;
        }

        function renderizarReacoes(m) {
            var $wrap = $('<div class="mensagens-reacoes">');
            if (m.excluida || m.sistema) { return $wrap; }
            $.each(m.reacoes || [], function(i, r) {
                var quem = (r.usuarios && r.usuarios.length) ? 'Reagiram: ' + r.usuarios.join(', ') : '';
                var $chip = $('<button type="button" class="mensagens-reacao-chip js-reagir-emoji">')
                    .toggleClass('is-minha', !!r.eu)
                    .attr({ 'data-id': m.id, 'data-emoji': r.emoji, title: quem })
                    .append($('<span>').text(r.emoji));
                if (r.quantidade > 1) {
                    $chip.append($('<b>').text(r.quantidade));
                }
                $wrap.append($chip);
            });
            return $wrap;
        }

        function renderizarEnqueteCard(eq) {
            if (!eq) { return null; }
            var $card = $('<div class="mensagens-enquete-card">').attr('data-enquete-id', eq.id);
            var subInfos = [eq.anonima ? 'Anônima' : 'Identificada'];
            if (eq.encerrada) { subInfos.push('Encerrada'); }
            else if (eq.encerrar_em_fmt) { subInfos.push('Até ' + eq.encerrar_em_fmt); }
            if (eq.multipla) { subInfos.push('Múltipla escolha'); }

            $card.append(
                $('<div class="mensagens-eq-cab">').append(
                    $('<div class="mensagens-eq-pergunta">').html('<i class="fa fa-bar-chart"></i> ').append($('<span>').text(eq.pergunta)),
                    $('<small class="mensagens-eq-meta">').text(subInfos.join(' · '))
                )
            );
            var $ops = $('<div class="mensagens-eq-lista">');
            $.each(eq.opcoes || [], function(i, op) {
                var $btn = $('<button type="button" class="mensagens-eq-opcao js-eq-votar">')
                    .toggleClass('is-votada', !!op.meu_voto)
                    .prop('disabled', !!eq.encerrada)
                    .attr({ 'data-enquete-id': eq.id, 'data-opcao-id': op.id });
                $btn.append($('<span class="mensagens-eq-barra">').css('width', (op.percentual || 0) + '%'));
                var $linha = $('<span class="mensagens-eq-linha">').append(
                    $('<span class="mensagens-eq-texto">').append(
                        op.meu_voto ? '<i class="fa fa-check-circle"></i> ' : '<i class="fa fa-circle-o"></i> ',
                        $('<span>').text(op.texto)
                    ),
                    $('<span class="mensagens-eq-nums">').text(op.votos + ' (' + op.percentual + '%)')
                );
                $btn.append($linha);
                if (!eq.anonima && op.votantes && op.votantes.length) {
                    $btn.append($('<span class="mensagens-eq-votantes">').text(op.votantes.join(', ')));
                }
                $ops.append($btn);
            });
            $card.append($ops);
            $card.append($('<div class="mensagens-eq-rodape">').text((eq.total_votos || 0) + (eq.total_votos === 1 ? ' voto' : ' votos')));
            return $card;
        }

        function renderizarReuniaoCard(reu) {
            if (!reu) { return null; }
            var $card = $('<div class="mensagens-reuniao-card">').attr('data-reuniao-id', reu.id);
            $card.append(
                $('<div class="mensagens-reu-topo">').append(
                    $('<span class="mensagens-reu-data"><i class="fa fa-calendar"></i> </span>').append($('<span>').text(reu.inicio_em_fmt)),
                    $('<strong class="mensagens-reu-titulo">').text(reu.titulo)
                )
            );
            if (reu.descricao) {
                $card.append($('<p class="mensagens-reu-desc">').text(reu.descricao));
            }
            var confirmadosNomes = $.map($.grep(reu.participantes || [], function(p) { return p.status === 'confirmado'; }), function(p) { return p.nome; });
            var resumoTxt = reu.confirmados + ' confirmado(s) · ' + reu.pendentes + ' pendente(s)';
            if (reu.recusados > 0) { resumoTxt += ' · ' + reu.recusados + ' recusado(s)'; }
            $card.append($('<div class="mensagens-reu-resumo">').text(resumoTxt));
            if (confirmadosNomes.length) {
                $card.append($('<div class="mensagens-reu-nomes">').text('Confirmados: ' + confirmadosNomes.join(', ')));
            }
            var $acoes = $('<div class="mensagens-reu-acoes">').append(
                $('<button type="button" class="btn btn-xs js-reu-responder">')
                    .toggleClass('btn-success', reu.meu_status === 'confirmado')
                    .toggleClass('btn-default', reu.meu_status !== 'confirmado')
                    .attr({ 'data-reuniao-id': reu.id, 'data-status': 'confirmado' })
                    .html('<i class="fa fa-check"></i> Confirmar presença'),
                $('<button type="button" class="btn btn-xs js-reu-responder">')
                    .toggleClass('btn-danger', reu.meu_status === 'recusado')
                    .toggleClass('btn-default', reu.meu_status !== 'recusado')
                    .attr({ 'data-reuniao-id': reu.id, 'data-status': 'recusado' })
                    .html('<i class="fa fa-times"></i> Não poderei ir'),
                $('<button type="button" class="btn btn-primary btn-xs js-reu-chamar">')
                    .attr('data-chave', reu.chave)
                    .html('<i class="fa fa-phone"></i> Entrar na chamada')
            );
            $card.append($acoes);
            return $card;
        }

        function bolha(m, mostrarAutor) {
            if (m.sistema) {
                return $('<div class="mensagens-sistema">').attr('data-id', m.id).append($('<span>').text(m.texto).attr('title', m.data));
            }
            mensagensMapa[m.id] = m;
            var $bolha = $('<div class="mensagens-bolha">')
                .toggleClass('tem-anexo', !!m.anexo)
                .toggleClass('tem-audio', !!(m.anexo && m.anexo.tipo === 'audio' && !m.excluida && !m.enquete && !m.reuniao))
                .toggleClass('is-excluida', !!m.excluida)
                .toggleClass('tem-card-especial', !!(m.enquete || m.reuniao));

            if (!m.excluida) {
                $bolha.append(
                    $('<button type="button" class="mensagens-msg-reagir-btn js-abrir-reacoes" aria-label="Reagir com emoji" title="Reagir"><i class="fa fa-smile-o"></i></button>').attr('data-id', m.id)
                );
            }
            $bolha.append(
                $('<button type="button" class="mensagens-msg-acoes-btn js-abrir-menu" aria-label="Opções da mensagem" title="Opções"><i class="fa fa-angle-down"></i></button>').attr('data-id', m.id)
            );

            if (mostrarAutor) { $bolha.append($('<span class="mensagens-bolha-autor">').css('color', m.cor).text(m.autor)); }
            if (m.encaminhada) {
                $bolha.append('<span class="mensagens-encaminhada"><i class="fa fa-share"></i> Encaminhada</span>');
            }
            if (m.resposta) {
                $bolha.append(
                    $('<button type="button" class="mensagens-citacao js-ir-msg">').attr('data-alvo-id', m.resposta.id).append(
                        $('<strong>').text(m.resposta.autor),
                        $('<span>').text(m.resposta.texto || 'Anexo')
                    )
                );
            }
            if (m.excluida) {
                $bolha.append($('<span class="mensagens-bolha-texto is-excluida">').html('<i class="fa fa-ban"></i> Mensagem excluída'));
            } else if (m.enquete) {
                $bolha.append(renderizarEnqueteCard(m.enquete));
            } else if (m.reuniao) {
                $bolha.append(renderizarReuniaoCard(m.reuniao));
            } else {
                if (m.anexo) { $bolha.append(conteudoAnexo(m.anexo, m)); }
                if (m.texto) { $bolha.append(construirTextoComMencoes(m.texto)); }
                if (m.chamado && m.chamado.id) {
                    $bolha.append(
                        $('<a class="mensagens-chamado-link" target="_blank" rel="noopener">')
                            .attr('href', m.chamado.url)
                            .append(
                                '<i class="fa fa-ticket"></i> ',
                                $('<strong>').text('Chamado #' + (m.chamado.protocolo || m.chamado.id)),
                                $('<span>').text(' — ' + m.chamado.titulo)
                            )
                    );
                }
            }
            var $reacoes = renderizarReacoes(m);
            if ($reacoes.children().length) { $bolha.append($reacoes); }

            var $rodape = $('<span class="mensagens-bolha-meta">');
            if (m.fixada) { $rodape.append('<i class="fa fa-thumb-tack mensagens-meta-icone" title="Fixada"></i>'); }
            if (m.favorita) { $rodape.append('<i class="fa fa-star mensagens-meta-icone is-fav" title="Favorita"></i>'); }
            if (m.editada) {
                $rodape.append($('<span class="mensagens-editada">').text('editada').attr('title', m.editada_em ? 'Editada em ' + m.editada_em : 'Editada'));
            }
            $rodape.append($('<span class="mensagens-bolha-hora">').attr('title', m.data).text(m.hora));
            var $ticks = elementoTicks(m);
            if ($ticks) { $rodape.append($ticks); }
            $bolha.append($rodape);

            return $('<div class="mensagens-msg">')
                .toggleClass('is-minha', m.minha)
                .toggleClass('tem-reacoes', $reacoes.children().length > 0)
                .attr({ 'data-id': m.id, 'data-de': m.de_id, 'data-dia': m.dia })
                .append($bolha);
        }

        var seguirFim = true;
        function pertoDoFim() {
            return $historico[0].scrollHeight - $historico.scrollTop() - $historico.innerHeight() < 80;
        }
        function manterNoFim() {
            if (seguirFim) { $historico.scrollTop($historico[0].scrollHeight); }
        }
        $historico.on('scroll', function() { seguirFim = pertoDoFim(); });

        function garantirBotaoAnteriores() {
            $historico.find('.mensagens-carregar-anteriores').remove();
            if (podeCarregarMais && primeiroId > 0) {
                $historico.prepend(
                    $('<div class="mensagens-carregar-anteriores">').append(
                        $('<button type="button" class="btn btn-default btn-xs" id="mensagens-carregar-btn"><i class="fa fa-history"></i> Carregar mensagens anteriores</button>')
                    )
                );
            }
        }

        function adicionar(lista) {
            var rolar = pertoDoFim() || lista.some(function(m) { return m.minha; });
            $.each(lista, function(i, m) {
                if ($historico.find('.mensagens-msg[data-id="' + m.id + '"], .mensagens-sistema[data-id="' + m.id + '"]').length) {
                    atualizarBolhaExistente(m);
                    return;
                }
                var mudouDia = $historico.find('.mensagens-dia').last().text() !== m.dia;
                if (mudouDia) {
                    $historico.append($('<div class="mensagens-dia">').attr('data-dia', m.dia).append($('<span>').text(m.dia)));
                }
                var $anterior = $historico.children().last();
                var mostrarAutor = atual && (atual.tipo === 'grupo' || atual.tipo === 'canal') && !m.minha && !m.sistema
                    && (mudouDia || !$anterior.is('.mensagens-msg') || parseInt($anterior.attr('data-de'), 10) !== m.de_id);
                $historico.append(bolha(m, mostrarAutor));
                ultimoId = Math.max(ultimoId, m.id);
                primeiroId = primeiroId ? Math.min(primeiroId, m.id) : m.id;
            });
            atualizarTicksDireta();
            if (rolar) {
                seguirFim = true;
                manterNoFim();
            }
        }

        function prefixarMensagens(lista) {
            if (!lista || !lista.length) {
                podeCarregarMais = false;
                garantirBotaoAnteriores();
                return;
            }
            var alturaAntes = $historico[0].scrollHeight;
            var scrollAntes = $historico.scrollTop();
            $historico.find('.mensagens-carregar-anteriores').remove();

            var $frag = $(document.createDocumentFragment());
            var diaAnterior = '';
            var autorAnterior = null;
            $.each(lista, function(i, m) {
                if ($historico.find('.mensagens-msg[data-id="' + m.id + '"], .mensagens-sistema[data-id="' + m.id + '"]').length) { return; }
                if (diaAnterior !== m.dia) {
                    $frag.append($('<div class="mensagens-dia">').attr('data-dia', m.dia).append($('<span>').text(m.dia)));
                    diaAnterior = m.dia;
                    autorAnterior = null;
                }
                var mostrarAutor = atual && (atual.tipo === 'grupo' || atual.tipo === 'canal') && !m.minha && !m.sistema && autorAnterior !== m.de_id;
                $frag.append(bolha(m, mostrarAutor));
                autorAnterior = m.sistema ? null : m.de_id;
                primeiroId = primeiroId ? Math.min(primeiroId, m.id) : m.id;
            });

            // Remove divisor de dia duplicado na junção com o primeiro item existente
            var $primeiroDiaExistente = $historico.find('.mensagens-dia').first();
            if ($primeiroDiaExistente.length && $primeiroDiaExistente.text() === diaAnterior) {
                $primeiroDiaExistente.remove();
            }
            $historico.prepend($frag);
            garantirBotaoAnteriores();
            $historico.scrollTop(scrollAntes + ($historico[0].scrollHeight - alturaAntes));
        }

        function atualizarBolhaExistente(m) {
            mensagensMapa[m.id] = m;
            var $el = $historico.find('.mensagens-msg[data-id="' + m.id + '"]');
            if (!$el.length) { return; }
            var tinhaAutor = $el.find('.mensagens-bolha-autor').length > 0;
            $el.replaceWith(bolha(m, tinhaAutor));
        }

        function atualizarTicksDireta() {
            if (!atual || atual.tipo !== 'direta') { return; }
            $historico.find('.mensagens-msg.is-minha').each(function() {
                var id = parseInt($(this).attr('data-id'), 10);
                var m = mensagensMapa[id];
                if (!m) { return; }
                var novoStatus = 'enviada';
                if (id <= vistoAte) { novoStatus = 'lida'; m.lida = true; m.pode_editar = false; }
                else if (id <= entregueAte) { novoStatus = 'entregue'; m.entregue = true; }
                if (m.status !== novoStatus) {
                    m.status = novoStatus;
                    var $novo = elementoTicks(m);
                    if ($novo) { $(this).find('.mensagens-ticks').replaceWith($novo); }
                }
            });
        }

        function renderizarFixadasBanner() {
            var $banner = $('#mensagens-fixada-banner');
            var qtd = fixadasAtuais ? fixadasAtuais.length : 0;
            $('#mensagens-fixadas-qtd').text(qtd).prop('hidden', !qtd);
            if (!qtd) {
                $banner.prop('hidden', true);
                return;
            }
            var topo = fixadasAtuais[0];
            $('#mensagens-fixada-titulo').text(qtd > 1 ? 'Mensagens fixadas (' + qtd + ')' : 'Mensagem fixada');
            $('#mensagens-fixada-previa').text(topo.texto || (topo.anexo ? topo.anexo.nome : 'Anexo'));
            $('#mensagens-fixada-ir').attr('data-alvo-id', topo.id);
            $banner.prop('hidden', false);
        }

        function atualizarTopo() {
            if (!atual) { return; }
            var grupo = atual.tipo === 'grupo';
            var canal = atual.tipo === 'canal';
            var ehBot = !!atual.is_bot;
            var podeEnviar = !ehBot && (!canal || atual.pode_enviar !== false);
            var pessoa = (!ehBot && !grupo && !canal) ? (pessoaPorId(atual.id) || atual) : null;
            if (pessoa) {
                atual.status = pessoa.status || 'offline';
                atual.status_texto = pessoa.status_texto || '';
            }
            $('#mensagens-atual-nome').text(atual.nome);
            $('#mensagens-atual-avatar').empty().append(avatar(atual, true));
            var temDigitando = digitandoAtual && digitandoAtual.length > 0;
            if (temDigitando) {
                var textoDig = (grupo || canal)
                    ? $.map(digitandoAtual, function(d) { return d.nome + (d.estado === 'gravando' ? ' está gravando áudio…' : ' está digitando…'); }).slice(0, 2).join(', ')
                    : (digitandoAtual[0].estado === 'gravando' ? 'gravando áudio…' : 'digitando…');
                $('#mensagens-atual-sub').prop('hidden', true);
                $('#mensagens-atual-status').text(textoDig).prop('hidden', false);
            } else {
                $('#mensagens-atual-status').prop('hidden', true);
                if (ehBot) {
                    $('#mensagens-atual-sub').prop('hidden', false).text('Bot automático · Atualizações de chamados');
                } else if (grupo) {
                    $('#mensagens-atual-sub').prop('hidden', false).text(atual.membros + ' participantes · ver todos');
                } else if (canal) {
                    var subCanal = 'Canal · ' + (atual.membros || 0) + ' membros' + (podeEnviar ? '' : ' · somente leitura');
                    $('#mensagens-atual-sub').prop('hidden', false).text(subCanal);
                } else {
                    var st = (pessoa && pessoa.status) || 'offline';
                    var infoSt = statusRotulos[st] || statusRotulos.offline;
                    var textoSt = infoSt.rotulo + (pessoa && pessoa.status_texto ? ' · ' + pessoa.status_texto : '');
                    $('#mensagens-atual-sub').prop('hidden', false).text(textoSt);
                }
            }
            $('#mensagens-grupo-btn').prop('hidden', !grupo);
            $('#mensagens-canal-config-btn').prop('hidden', !(canal && atual.sou_criador));
            $('#mensagens-ligar-btn').prop('hidden', ehBot);
            $('#mensagens-enquete-btn, #mensagens-reuniao-btn').prop('hidden', !(canal && podeEnviar));
            $('#mensagens-somente-leitura-txt').text(ehBot
                ? 'Bot de chamados automático — apenas recebimento de atualizações.'
                : 'Apenas usuários autorizados podem enviar mensagens neste canal.');
            $('#mensagens-somente-leitura').prop('hidden', podeEnviar);
            $('#mensagens-envio').prop('hidden', !podeEnviar);
            $('.mensagens-dica').prop('hidden', !podeEnviar);
            var silenciada = configAtual && configAtual.silenciada;
            $('#mensagens-notif-btn').find('i').attr('class', silenciada ? 'fa fa-bell-slash-o text-muted' : 'fa fa-bell-o');
            if (typeof renderizarChamadaBanner === 'function') { renderizarChamadaBanner(); }
        }

        function limparPendentes() {
            $.each(pendentes, function(i, p) {
                if (p.previa && window.URL) { window.URL.revokeObjectURL(p.previa); }
            });
            pendentes = [];
            renderizarPendentes();
        }

        function limparContexto() {
            modoContexto = null;
            $('#mensagens-contexto').prop('hidden', true);
            $('#mensagens-anexo-btn, #mensagens-audio-btn').prop('disabled', false);
        }

        function mostrarConversa(res) {
            cancelarGravacao();
            enviarEstadoDigitando('');
            limparContexto();
            mencoesSelecionadas = {};
            fecharMencoes();
            fecharMenuMensagem();

            atual = res.conversa;
            vistoAte = res.visto_ate || 0;
            entregueAte = res.entregue_ate || 0;
            fixadasAtuais = res.fixadas || [];
            configAtual = res.config || configAtual || { modo_notif: 'todas', silenciada: false };
            membrosAtual = res.membros || [];
            digitandoAtual = res.digitando || [];
            chamadaConversaAtiva = res.chamada_ativa || null;
            ultimoId = 0;
            primeiroId = 0;
            mensagensMapa = {};
            podeCarregarMais = (res.mensagens || []).length >= 100;
            limparPendentes();

            var cLista = $.grep(conversas, function(c) { return c.chave === atual.chave; })[0];
            var rasc = (atual && atual.rascunho !== undefined) ? atual.rascunho : (cLista && cLista.rascunho ? cLista.rascunho : '');
            $texto.val(rasc || '');
            ajustarAltura();

            $('#mensagens-erro, #mensagens-info').prop('hidden', true);
            window.mensagensConversaAberta = atual.chave;
            atualizarTopo();
            renderizarFixadasBanner();
            $historico.empty();
            if (!res.mensagens || !res.mensagens.length) {
                $historico.append($('<p class="mensagens-sem-historico">').text('Nenhuma mensagem com ' + atual.nome + ' ainda. Diga um oi!'));
            } else {
                adicionar(res.mensagens);
                garantirBotaoAnteriores();
            }
            seguirFim = true;
            manterNoFim();
            $.each(conversas, function(i, c) {
                if (c.chave === atual.chave) {
                    c.nao_lidas = 0;
                    c.mencoes = 0;
                    c.marcada_nao_lida = false;
                }
            });
            irPara('conversa');
            renderizarListas();
            manterNoFim();
            setTimeout(function() { manterNoFim(); $texto.trigger('focus'); }, 360);
        }

        function empilharHistorico(chave) {
            if (window.history && window.history.pushState) {
                window.history.pushState({ conversa: chave }, '', urlTela + '/' + chave);
                empilhado = true;
            }
        }

        function abrir(chave, semHistorico, aoAbrir) {
            $.ajax({ url: api + '/conversa/' + chave, dataType: 'json', cache: false }).done(function(res) {
                if (!res || !res.ok) { return; }
                mostrarConversa(res);
                if (!semHistorico) { empilharHistorico(chave); }
                if (typeof aoAbrir === 'function') { aoAbrir(); }
            }).fail(function(xhr) {
                avisar('error', 'Não foi possível abrir a conversa', erroDe(xhr));
            });
        }

        function destacarElemento($el) {
            $historico.find('.is-destacada').removeClass('is-destacada');
            $el[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            $el.addClass('is-destacada');
            setTimeout(function() { $el.removeClass('is-destacada'); }, 2200);
        }

        function irParaMensagem(id, chave) {
            id = parseInt(id, 10);
            if (!id) { return; }
            if (chave && (!atual || atual.chave !== chave || tela !== 'conversa')) {
                abrir(chave, false, function() { irParaMensagem(id); });
                return;
            }
            var $alvo = $historico.find('.mensagens-msg[data-id="' + id + '"]');
            if ($alvo.length) {
                destacarElemento($alvo);
                return;
            }
            if (!atual || carregandoAnteriores) { return; }
            carregandoAnteriores = true;
            $.getJSON(api + '/anteriores/' + atual.chave, { antes: primeiroId, ate: id }).done(function(res) {
                if (res && res.ok && res.mensagens) {
                    prefixarMensagens(res.mensagens);
                    var $novoAlvo = $historico.find('.mensagens-msg[data-id="' + id + '"]');
                    if ($novoAlvo.length) {
                        destacarElemento($novoAlvo);
                    } else {
                        avisar('info', 'Mensagem indisponível', 'Esta mensagem foi removida ou não está disponível.');
                    }
                }
            }).always(function() { carregandoAnteriores = false; });
        }

        function mostrarErro(xhr) {
            $('#mensagens-erro').text(typeof xhr === 'string' ? xhr : erroDe(xhr)).prop('hidden', false);
        }

        function sairDaConversa(chave) {
            conversas = $.grep(conversas, function(c) { return c.chave !== chave; });
            $modal.modal('hide');
            atual = null;
            empilhado = false;
            irPara('lista');
            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, '', urlTela);
            }
        }

        /* ---------- Menu de ações da mensagem (Responder, Editar, Excluir, Fixar, Favoritar, Encaminhar, Visualizações) ---------- */

        function fecharMenuMensagem() {
            $menuMsg.prop('hidden', true);
            menuMsgId = 0;
        }

        function abrirMenuMensagem($btn) {
            var id = parseInt($btn.attr('data-id'), 10);
            var m = mensagensMapa[id];
            if (!m) { return; }
            if (menuMsgId === id && !$menuMsg.prop('hidden')) {
                fecharMenuMensagem();
                return;
            }
            menuMsgId = id;
            var disponivel = !m.excluida;
            $menuMsg.find('[data-acao="reagir"]').prop('hidden', !disponivel);
            $menuMsg.find('[data-acao="responder"]').prop('hidden', !disponivel);
            $menuMsg.find('[data-acao="encaminhar"]').prop('hidden', !disponivel);
            $menuMsg.find('[data-acao="favoritar"]').prop('hidden', !disponivel)
                .find('span').text(m.favorita ? 'Remover dos favoritos' : 'Favoritar');
            $menuMsg.find('[data-acao="favoritar"] i').attr('class', m.favorita ? 'fa fa-star text-yellow' : 'fa fa-star-o');
            $menuMsg.find('[data-acao="fixar"]').prop('hidden', !disponivel)
                .find('span').text(m.fixada ? 'Desafixar' : 'Fixar no topo');
            $menuMsg.find('[data-acao="lembrete"]').prop('hidden', !disponivel);
            $menuMsg.find('[data-acao="chamado"]').prop('hidden', !disponivel);
            $menuMsg.find('[data-acao="visualizacoes"]').prop('hidden', m.excluida || (!m.minha && !(atual && (atual.tipo === 'grupo' || atual.tipo === 'canal'))));
            $menuMsg.find('[data-acao="editar"]').prop('hidden', !m.pode_editar);

            $menuMsg.prop('hidden', false);
            var rect = $btn[0].getBoundingClientRect();
            var w = $menuMsg.outerWidth();
            var h = $menuMsg.outerHeight();
            var left = Math.min(window.innerWidth - w - 10, Math.max(10, m.minha ? rect.right - w : rect.left));
            var top = (rect.bottom + h + 10 > window.innerHeight) ? Math.max(10, rect.top - h - 4) : rect.bottom + 4;
            $menuMsg.css({ left: left + 'px', top: top + 'px' });
        }

        function iniciarResposta(m) {
            limparContexto();
            modoContexto = { tipo: 'responder', msg: m };
            $('#mensagens-contexto-titulo').text('Respondendo a ' + (m.minha ? 'você' : m.autor));
            $('#mensagens-contexto-texto').text(m.texto || (m.anexo ? m.anexo.nome : 'Anexo'));
            $('#mensagens-contexto').prop('hidden', false);
            $texto.trigger('focus');
        }

        function iniciarEdicao(m) {
            if (!m.pode_editar) {
                avisar('info', 'Edição bloqueada', 'Esta mensagem já foi visualizada e não pode mais ser editada.');
                return;
            }
            limparContexto();
            limparPendentes();
            modoContexto = { tipo: 'editar', msg: m };
            $('#mensagens-contexto-titulo').text('Editando mensagem (antes da confirmação de leitura)');
            $('#mensagens-contexto-texto').text(m.texto || '');
            $('#mensagens-contexto').prop('hidden', false);
            $('#mensagens-anexo-btn, #mensagens-audio-btn').prop('disabled', true);
            $texto.val(m.texto || '').trigger('focus');
            ajustarAltura();
        }

        function solicitarExclusao(m) {
            if (m.minha && !m.excluida && window.Swal) {
                window.Swal.fire({
                    icon: 'warning',
                    title: 'Excluir mensagem?',
                    text: 'Escolha se deseja remover só para você ou para todos na conversa.',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonText: 'Excluir para todos',
                    denyButtonText: 'Excluir para mim',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dd4b39',
                    denyButtonColor: '#1E1D3F',
                    reverseButtons: true
                }).then(function(r) {
                    if (r.isConfirmed) { executarExclusao(m.id, 'para_todos'); }
                    else if (r.isDenied) { executarExclusao(m.id, 'para_mim'); }
                });
                return;
            }
            confirmar({
                title: 'Excluir esta mensagem para você?',
                text: 'Ela deixará de aparecer na sua tela.',
                confirmButtonText: 'Excluir para mim',
                confirmButtonColor: '#dd4b39'
            }).then(function(ok) {
                if (ok) { executarExclusao(m.id, 'para_mim'); }
            });
        }

        function executarExclusao(id, modo) {
            $.ajax({ url: api + '/excluir', method: 'POST', dataType: 'json', data: { id: id, modo: modo } }).done(function(res) {
                if (!res || !res.ok) { return; }
                if (modo === 'para_mim') {
                    $historico.find('.mensagens-msg[data-id="' + id + '"]').remove();
                    delete mensagensMapa[id];
                } else if (res.mensagem) {
                    atualizarBolhaExistente(res.mensagem);
                }
                fixadasAtuais = $.grep(fixadasAtuais || [], function(f) { return f.id !== id; });
                renderizarFixadasBanner();
                atualizar();
            }).fail(function(xhr) {
                avisar('error', 'Não foi possível excluir', erroDe(xhr));
            });
        }

        function abrirVisualizacoes(id) {
            $('#mensagens-vis-corpo').html('<p class="text-muted"><i class="fa fa-spinner fa-spin"></i> Carregando...</p>');
            $('#mensagens-visualizacoes-modal').modal('show');
            $.getJSON(api + '/visualizacoes/' + id).done(function(res) {
                if (!res || !res.ok) { return; }
                var $corpo = $('#mensagens-vis-corpo').empty();
                var secao = function(titulo, icone, lista) {
                    $corpo.append($('<h5 class="mensagens-vis-secao">').html('<i class="fa ' + icone + '"></i> ' + titulo + ' (' + lista.length + ')'));
                    if (!lista.length) {
                        $corpo.append('<p class="text-muted small">Ninguém ainda.</p>');
                        return;
                    }
                    var $ul = $('<ul class="mensagens-membros">');
                    $.each(lista, function(i, u) {
                        var hora = u.lida_em ? 'Visualizada em ' + u.lida_em : (u.entregue_em ? 'Entregue em ' + u.entregue_em : 'Aguardando entrega');
                        $ul.append($('<li class="mensagens-membro">').append(
                            avatar(u),
                            $('<span class="mensagens-membro-nome">').text(u.nome),
                            $('<small class="text-muted">').text(hora)
                        ));
                    });
                    $corpo.append($ul);
                };
                secao('Visualizada por', 'fa-check-circle text-aqua', res.lidas || []);
                secao('Entregue para', 'fa-check', res.entregues || []);
                if ((res.pendentes || []).length) {
                    secao('Enviada (aguardando entrega)', 'fa-clock-o', res.pendentes);
                }
            }).fail(function(xhr) {
                $('#mensagens-vis-corpo').html($('<p class="text-danger">').text(erroDe(xhr)));
            });
        }

        function abrirModalFixadas() {
            var $corpo = $('#mensagens-fix-corpo').empty();
            if (!fixadasAtuais || !fixadasAtuais.length) {
                $corpo.html('<p class="text-muted">Nenhuma mensagem fixada nesta conversa.</p>');
            } else {
                $.each(fixadasAtuais, function(i, m) {
                    $corpo.append($('<div class="mensagens-card-item">').append(
                        $('<div class="mensagens-card-topo">').append(
                            $('<strong>').text(m.minha ? 'Você' : (m.autor || atual.nome)),
                            $('<small class="text-muted">').text(m.data)
                        ),
                        $('<p class="mensagens-card-texto">').text(m.texto || (m.anexo ? m.anexo.nome : 'Anexo')),
                        $('<div class="mensagens-card-acoes">').append(
                            $('<button type="button" class="btn btn-default btn-xs">').text('Ir para mensagem').on('click', function() {
                                $('#mensagens-fixadas-modal').modal('hide');
                                irParaMensagem(m.id);
                            }),
                            $('<button type="button" class="btn btn-link btn-xs text-danger">').text('Desafixar').on('click', function() {
                                alternarFixar(m.id, false, abrirModalFixadas);
                            })
                        )
                    ));
                });
            }
            $('#mensagens-fixadas-modal').modal('show');
        }

        function alternarFixar(id, fixar, aoTerminar) {
            if (!atual) { return; }
            $.ajax({ url: api + '/fixar', method: 'POST', dataType: 'json', data: { id: id, para: atual.chave, fixar: fixar ? '1' : '0' } }).done(function(res) {
                if (!res || !res.ok) { return; }
                fixadasAtuais = res.fixadas || [];
                renderizarFixadasBanner();
                if (mensagensMapa[id]) {
                    mensagensMapa[id].fixada = fixar;
                    atualizarBolhaExistente(mensagensMapa[id]);
                }
                if (typeof aoTerminar === 'function') { aoTerminar(); }
            }).fail(function(xhr) {
                avisar('error', 'Não foi possível atualizar', erroDe(xhr));
            });
        }

        function alternarFavoritar(id) {
            $.ajax({ url: api + '/favoritar', method: 'POST', dataType: 'json', data: { id: id } }).done(function(res) {
                if (!res || !res.ok) { return; }
                if (mensagensMapa[id]) {
                    mensagensMapa[id].favorita = !!res.favorita;
                    atualizarBolhaExistente(mensagensMapa[id]);
                }
            }).fail(function(xhr) {
                avisar('error', 'Não foi possível favoritar', erroDe(xhr));
            });
        }

        function preencherSelectConversas($sel, textoTodas) {
            $sel.empty().append($('<option value="">').text(textoTodas));
            var vistas = {};
            $.each(conversas, function(i, c) {
                vistas[c.chave] = true;
                $sel.append($('<option>').val(c.chave).text(c.nome));
            });
            $.each(pessoas, function(i, p) {
                if (!vistas[p.chave] && p.ultima_id > 0) {
                    $sel.append($('<option>').val(p.chave).text(p.nome));
                }
            });
        }

        function renderizarListaCards($cont, itens, mostrarRemoverFav) {
            $cont.empty();
            if (!itens || !itens.length) {
                $cont.html('<p class="text-muted">Nenhuma mensagem encontrada.</p>');
                return;
            }
            $.each(itens, function(i, m) {
                var $acoes = $('<div class="mensagens-card-acoes">').append(
                    $('<button type="button" class="btn btn-primary btn-xs">').text('Ir para mensagem').on('click', function() {
                        $cont.closest('.modal').modal('hide');
                        irParaMensagem(m.id, m.conversa_chave);
                    })
                );
                if (mostrarRemoverFav) {
                    $acoes.append($('<button type="button" class="btn btn-link btn-xs text-danger">').text('Remover favorito').on('click', function() {
                        $.post(api + '/favoritar', { id: m.id }, null, 'json').done(function() {
                            if (mensagensMapa[m.id]) {
                                mensagensMapa[m.id].favorita = false;
                                atualizarBolhaExistente(mensagensMapa[m.id]);
                            }
                            carregarFavoritas();
                        });
                    }));
                }
                $cont.append($('<div class="mensagens-card-item">').append(
                    $('<div class="mensagens-card-topo">').append(
                        $('<strong>').text((m.conversa_nome || '') + ' · ' + (m.minha ? 'Você' : m.autor)),
                        $('<small class="text-muted">').text(m.data)
                    ),
                    $('<p class="mensagens-card-texto">').text((m.texto || '') + (m.anexo ? (m.texto ? ' · ' : '') + '[' + m.anexo.nome + ']' : '')),
                    $acoes
                ));
            });
        }

        function carregarFavoritas() {
            var conv = $('#mensagens-fav-conversa').val() || '';
            $('#mensagens-fav-corpo').html('<p class="text-muted"><i class="fa fa-spinner fa-spin"></i> Carregando...</p>');
            $.getJSON(api + '/favoritas', { conversa: conv }).done(function(res) {
                if (res && res.ok) { renderizarListaCards($('#mensagens-fav-corpo'), res.itens, true); }
            });
        }

        function abrirModalFavoritas() {
            preencherSelectConversas($('#mensagens-fav-conversa'), 'Todas as conversas');
            if (atual && tela === 'conversa') { $('#mensagens-fav-conversa').val(atual.chave); }
            $('#mensagens-favoritas-modal').modal('show');
            carregarFavoritas();
        }

        function abrirModalBusca() {
            if (!atual) { return; }
            $('#mensagens-busca-sub').text('Conversa: ' + atual.nome);
            $('#mensagens-f-q, #mensagens-f-de, #mensagens-f-ate').val('');
            $('#mensagens-f-tipo').val('');
            var $usr = $('#mensagens-f-usuario').empty().append('<option value="">Qualquer usuário</option>');
            $usr.append($('<option>').val(meuId).text(meuNome ? meuNome + ' (Você)' : 'Você'));
            if (atual.tipo === 'direta') {
                $usr.append($('<option>').val(atual.id).text(atual.nome));
            } else {
                var membrosOrdenados = (membrosAtual || []).slice().sort(function(a, b) { return a.nome.localeCompare(b.nome); });
                $.each(membrosOrdenados, function(i, p) {
                    if (p.id !== meuId) { $usr.append($('<option>').val(p.id).text(p.nome)); }
                });
            }
            $('#mensagens-busca-modal').modal('show');
            setTimeout(function() { $('#mensagens-f-q').trigger('focus'); }, 250);
            executarBusca();
        }

        function executarBusca() {
            if (!atual) { return; }
            $('#mensagens-busca-resultados').html('<p class="text-muted"><i class="fa fa-spinner fa-spin"></i> Pesquisando...</p>');
            $.getJSON(api + '/buscar', {
                q: $('#mensagens-f-q').val(),
                conversa: atual.chave,
                usuario: $('#mensagens-f-usuario').val(),
                de: $('#mensagens-f-de').val(),
                ate: $('#mensagens-f-ate').val(),
                tipo: $('#mensagens-f-tipo').val()
            }).done(function(res) {
                if (res && res.ok) { renderizarListaCards($('#mensagens-busca-resultados'), res.itens, false); }
            });
        }

        function abrirModalEncaminhar(m) {
            encaminharMsgId = m.id;
            encaminharDestinos = [];
            $('#mensagens-enc-busca').val('');
            $('#mensagens-enc-previa').text((m.texto || '') + (m.anexo ? (m.texto ? ' · ' : '') + 'Anexo: ' + m.anexo.nome : ''));
            renderizarListaEncaminhar();
            $('#mensagens-encaminhar-modal').modal('show');
        }

        function listaDestinosEncaminhar() {
            var mapa = {};
            var lista = [];
            $.each(conversas, function(i, c) {
                if (c.is_bot) { return; }
                mapa[c.chave] = true;
                lista.push({ chave: c.chave, nome: c.nome, foto: c.foto, tipo: c.tipo });
            });
            $.each(pessoas, function(i, p) {
                if (!p.is_bot && !mapa[p.chave]) {
                    lista.push({ chave: p.chave, nome: p.nome, foto: p.foto, tipo: 'direta' });
                }
            });
            return lista;
        }

        function renderizarListaEncaminhar() {
            var termo = $('#mensagens-enc-busca').val();
            var itens = filtrar(listaDestinosEncaminhar(), termo);
            var $ul = $('#mensagens-enc-lista').empty();
            $.each(itens, function(i, d) {
                var marcado = $.inArray(d.chave, encaminharDestinos) !== -1;
                $ul.append($('<li>').append(
                    $('<button type="button" class="mensagens-contato is-selecionavel">')
                        .toggleClass('is-marcado', marcado)
                        .attr('data-chave', d.chave)
                        .append(
                            avatar(d),
                            $('<span class="mensagens-contato-corpo">').append(
                                $('<span class="mensagens-contato-linha">').append($('<strong class="mensagens-contato-nome">').text(d.nome))
                            ),
                            $('<span class="mensagens-marca" aria-hidden="true">').append(marcado ? '<i class="fa fa-check"></i>' : null)
                        )
                ));
            });
            if (!itens.length) { $ul.append(vazio('Nenhuma conversa encontrada.')); }
            $('#mensagens-enc-enviar').prop('disabled', !encaminharDestinos.length)
                .html('<i class="fa fa-share"></i> Encaminhar' + (encaminharDestinos.length ? ' (' + encaminharDestinos.length + ')' : ''));
        }

        function abrirModalNotificacoes() {
            if (!atual) { return; }
            var cfg = configAtual || { modo_notif: 'todas', silenciada: false, silenciada_ate: null };
            $('#mensagens-notif-sub').text(atual.nome);
            $('#mensagens-notif-modo').val(cfg.modo_notif || 'todas');
            $('#mensagens-notif-silenciar').val('');
            $('#mensagens-notif-estado').text(cfg.silenciada
                ? (cfg.silenciada_ate && cfg.silenciada_ate.indexOf('2099') === 0 ? 'Conversa silenciada permanentemente.' : 'Silenciada até ' + cfg.silenciada_ate)
                : 'Notificações ativas.');
            $('#mensagens-notif-som').prop('checked', !window.localStorage || window.localStorage.getItem('sax_chat_som') !== '0');
            var perm = ('Notification' in window) ? window.Notification.permission : 'unsupported';
            $('#mensagens-notif-navegador')
                .prop('disabled', perm === 'granted' || perm === 'unsupported')
                .text(perm === 'granted' ? 'Notificações do navegador ativas' : (perm === 'unsupported' ? 'Navegador sem suporte a notificações' : 'Ativar notificações do navegador'));
            $('#mensagens-notif-modal').modal('show');
        }

        /* ---------- Participantes do grupo ---------- */

        function abrirDetalhes() {
            if (!atual || atual.tipo !== 'grupo') { return; }
            $.ajax({ url: api + '/grupo/' + atual.id, dataType: 'json', cache: false }).done(function(res) {
                if (!res || !res.ok) { return; }
                atual.membros = res.grupo.membros;
                atual.sou_criador = res.grupo.sou_criador;
                membrosAtual = res.membros;
                atualizarTopo();
                membrosGrupo = res.membros;
                escolhidosAdicionar = [];
                $('#mensagens-busca-adicionar').val('');
                renderizarDetalhes();
                $modal.modal('show');
            }).fail(function(xhr) {
                if (xhr.status === 404) {
                    avisar('info', 'Você não participa mais deste grupo', erroDe(xhr));
                    sairDaConversa(atual.chave);
                } else {
                    avisar('error', 'Não foi possível carregar o grupo', erroDe(xhr));
                }
            });
        }

        function renderizarDetalhes() {
            $('#mensagens-grupo-modal-titulo').text(atual.nome);
            $('#mensagens-grupo-modal-sub').text(membrosGrupo.length + ' participantes');
            var $lista = $('#mensagens-membros').empty();
            $.each(membrosGrupo, function(i, m) {
                var eu = m.id === meuId;
                $lista.append($('<li class="mensagens-membro">').append(
                    avatar(m),
                    $('<span class="mensagens-membro-nome">').text(m.nome).append(
                        eu ? ' <span class="label label-default">Você</span>' : null,
                        m.criador ? ' <span class="label label-primary">Criador</span>' : null),
                    atual.sou_criador && !eu
                        ? $('<button type="button" class="btn btn-default btn-xs mensagens-remover-membro"><i class="fa fa-user-times"></i> Remover</button>').attr('data-id', m.id).attr('data-nome', m.nome)
                        : null
                ));
            });

            $('#mensagens-adicionar').prop('hidden', !atual.sou_criador);
            $('#mensagens-excluir-grupo').prop('hidden', !atual.sou_criador);
            var noGrupo = $.map(membrosGrupo, function(m) { return m.id; });
            var disponiveis = $.grep(pessoas, function(p) { return $.inArray(p.id, noGrupo) === -1; });
            var $adicionar = $('#mensagens-adicionar-pessoas').empty();
            var achadas = filtrar(disponiveis.sort(function(a, b) { return a.nome.localeCompare(b.nome); }), $('#mensagens-busca-adicionar').val());
            $.each(achadas, function(i, p) { $adicionar.append(itemPessoa(p, $.inArray(p.id, escolhidosAdicionar) !== -1)); });
            if (!achadas.length) { $adicionar.append(vazio(disponiveis.length ? 'Ninguém encontrado.' : 'Todas as pessoas já estão no grupo.')); }
            $('#mensagens-adicionar-btn').prop('disabled', !escolhidosAdicionar.length)
                .html('<i class="fa fa-plus"></i> Adicionar' + (escolhidosAdicionar.length ? ' (' + escolhidosAdicionar.length + ')' : ''));
        }

        function acaoGrupo(url, dados, aoTerminar) {
            return $.ajax({ url: api + '/' + url, method: 'POST', dataType: 'json', data: $.extend({ grupo: atual.id }, dados) }).done(function(res) {
                if (res && res.ok) { aoTerminar(); }
            }).fail(function(xhr) {
                avisar('error', 'Não foi possível concluir', erroDe(xhr));
            });
        }

        function removerMembro(id, nome) {
            confirmar({
                title: 'Remover ' + nome + ' do grupo?',
                text: 'Ela deixa de ver as mensagens do grupo "' + atual.nome + '".',
                confirmButtonText: 'Sim, remover', confirmButtonColor: '#dd4b39'
            }).then(function(ok) {
                if (!ok) { return; }
                acaoGrupo('grupo_remover', { usuario: id }, function() { abrirDetalhes(); atualizar(); });
            });
        }

        function adicionarMembros() {
            if (!escolhidosAdicionar.length) { return; }
            var nomes = $.map(escolhidosAdicionar, function(id) { var p = pessoaPorId(id); return p ? p.nome : null; });
            confirmar({
                icon: 'question',
                title: 'Adicionar ' + (nomes.length > 1 ? nomes.length + ' pessoas' : nomes[0]) + ' ao grupo?',
                text: nomes.join(', ') + ' vai passar a ver as novas mensagens do grupo.',
                confirmButtonText: 'Sim, adicionar'
            }).then(function(ok) {
                if (!ok) { return; }
                acaoGrupo('grupo_adicionar', { membros: escolhidosAdicionar }, function() { abrirDetalhes(); atualizar(); });
            });
        }

        function sairDoGrupo() {
            var aviso = atual.sou_criador
                ? 'Você criou este grupo. Ao sair, a pessoa mais antiga do grupo passa a administrá-lo, e você deixa de ver as mensagens.'
                : 'Você deixa de ver as mensagens deste grupo. Para voltar, alguém precisa te adicionar de novo.';
            confirmar({ title: 'Sair do grupo "' + atual.nome + '"?', text: aviso, confirmButtonText: 'Sim, sair', confirmButtonColor: '#dd4b39' }).then(function(ok) {
                if (!ok) { return; }
                var chave = atual.chave;
                acaoGrupo('grupo_sair', {}, function() { sairDaConversa(chave); });
            });
        }

        function excluirGrupo() {
            confirmar({
                title: 'Excluir o grupo "' + atual.nome + '"?',
                text: 'O grupo some para todos os participantes e as mensagens deixam de ficar disponíveis. Não dá para desfazer.',
                confirmButtonText: 'Sim, excluir', confirmButtonColor: '#dd4b39'
            }).then(function(ok) {
                if (!ok) { return; }
                var chave = atual.chave;
                acaoGrupo('grupo_excluir', {}, function() { sairDaConversa(chave); });
            });
        }

        /* ---------- Anexos e gravação de áudio ---------- */

        function tamanhoLegivel(bytes) {
            return bytes >= 1048576 ? (bytes / 1048576).toFixed(1).replace('.', ',') + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
        }

        function adicionarArquivos(lista) {
            if (!atual || (modoContexto && modoContexto.tipo === 'editar')) { return; }
            if (!anexosAtivos) {
                mostrarErro('Os anexos ainda não estão disponíveis: aplique o script sql/2026-11-16_mensagens_anexos.sql no banco.');
                return;
            }
            $.each(lista, function(i, arquivo) {
                var imagemGrande = ehImagemComprimivel(arquivo) && arquivo.size > limiteCompressao;
                if (arquivo.size > limiteArquivo && !imagemGrande) {
                    mostrarErro(arquivo.name + ' passa do limite de ' + tamanhoLegivel(limiteArquivo) + '.');
                    return;
                }
                var p = { arquivo: arquivo, progresso: 0, comprimindo: imagemGrande };
                pendentes.push(p);
                if (!imagemGrande) { return; }
                comprimirImagem(arquivo).done(function(resultado) {
                    p.comprimindo = false;
                    if (resultado.size > limiteArquivo) {
                        pendentes.splice($.inArray(p, pendentes), 1);
                        mostrarErro(arquivo.name + ' passa do limite de ' + tamanhoLegivel(limiteArquivo) + ' mesmo comprimida.');
                    } else if (resultado !== arquivo) {
                        p.original = arquivo.size;
                        p.arquivo = resultado;
                        if (p.previa && window.URL) { window.URL.revokeObjectURL(p.previa); p.previa = null; }
                    }
                    renderizarPendentes();
                });
            });
            renderizarPendentes();
            $texto.trigger('focus');
        }

        var limiteCompressao = 5 * 1024 * 1024;
        function ehImagemComprimivel(arquivo) { return /^image\/(jpeg|png|webp|bmp)$/.test(arquivo.type || ''); }

        function comprimirImagem(arquivo) {
            var feito = $.Deferred();
            var canvasTeste = document.createElement('canvas');
            if (!window.URL || !canvasTeste.toBlob) { return feito.resolve(arquivo).promise(); }
            var url = window.URL.createObjectURL(arquivo);
            var img = new Image();
            var tentativas = [[2560, 0.82], [1920, 0.75], [1600, 0.7]];
            img.onload = function() {
                window.URL.revokeObjectURL(url);
                (function tentar(i) {
                    var escala = Math.min(1, tentativas[i][0] / Math.max(img.naturalWidth, img.naturalHeight));
                    var canvas = document.createElement('canvas');
                    canvas.width = Math.max(1, Math.round(img.naturalWidth * escala));
                    canvas.height = Math.max(1, Math.round(img.naturalHeight * escala));
                    var ctx = canvas.getContext('2d');
                    ctx.fillStyle = '#fff';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    canvas.toBlob(function(blob) {
                        if (!blob) { feito.resolve(arquivo); return; }
                        if (blob.size > limiteCompressao && i < tentativas.length - 1) { tentar(i + 1); return; }
                        if (blob.size >= arquivo.size) { feito.resolve(arquivo); return; }
                        var nome = arquivo.name.replace(/\.[^.]+$/, '') + '.jpg';
                        var novo;
                        try { novo = new File([blob], nome, { type: 'image/jpeg' }); }
                        catch (e) { novo = blob; novo.name = nome; }
                        feito.resolve(novo);
                    }, 'image/jpeg', tentativas[i][1]);
                })(0);
            };
            img.onerror = function() { window.URL.revokeObjectURL(url); feito.resolve(arquivo); };
            img.src = url;
            return feito.promise();
        }

        function renderizarPendentes() {
            var $lista = $('#mensagens-pendentes').empty();
            $.each(pendentes, function(i, p) {
                var $item = $('<li class="mensagens-pendente">').attr('data-i', i);
                var tipo = p.arquivo.type || '';
                if (tipo.indexOf('image/') === 0 && window.URL) {
                    if (!p.previa) { p.previa = window.URL.createObjectURL(p.arquivo); }
                    $item.append($('<img alt="">').attr('src', p.previa));
                } else if ((p.gravacaoAudio || tipo.indexOf('audio/') === 0) && window.URL) {
                    if (!p.previa) { p.previa = window.URL.createObjectURL(p.arquivo); }
                    $item.addClass('is-audio').append($('<audio class="mensagens-pendente-audio" controls preload="metadata">').attr('src', p.previa));
                } else {
                    $item.append('<i class="fa fa-file-o"></i>');
                }
                var situacao = p.comprimindo ? 'Comprimindo…'
                    : p.progresso ? p.progresso + '%'
                    : p.original ? tamanhoLegivel(p.original) + ' → ' + tamanhoLegivel(p.arquivo.size)
                    : tamanhoLegivel(p.arquivo.size);
                if (!$item.hasClass('is-audio')) {
                    $item.append($('<span class="mensagens-pendente-nome">').text(p.arquivo.name).attr('title', p.arquivo.name));
                }
                $item.append($('<small>').text(situacao))
                    .append(enviando ? null : $('<button type="button" class="mensagens-pendente-remover" aria-label="Remover arquivo">&times;</button>'));
                $lista.append($item);
            });
            $app.toggleClass('tem-pendentes', pendentes.length > 0);
            $('#mensagens-enviar').prop('disabled', enviando || $.grep(pendentes, function(p) { return p.comprimindo; }).length > 0);
        }

        function formatarTempo(segundos) {
            segundos = Math.max(0, Math.floor(segundos || 0));
            var m = Math.floor(segundos / 60);
            var s = segundos % 60;
            return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
        }

        function escolherMimeAudio() {
            if (!window.MediaRecorder || typeof window.MediaRecorder.isTypeSupported !== 'function') { return null; }
            var candidatos = [
                { mime: 'audio/webm;codecs=opus', ext: 'webm', type: 'audio/webm' },
                { mime: 'audio/webm', ext: 'webm', type: 'audio/webm' },
                { mime: 'audio/ogg;codecs=opus', ext: 'ogg', type: 'audio/ogg' },
                { mime: 'audio/ogg', ext: 'ogg', type: 'audio/ogg' },
                { mime: 'audio/mp4', ext: 'm4a', type: 'audio/mp4' }
            ];
            for (var i = 0; i < candidatos.length; i++) {
                if (window.MediaRecorder.isTypeSupported(candidatos[i].mime)) { return candidatos[i]; }
            }
            return null;
        }

        function extensaoDeMime(mime) {
            mime = String(mime || '').toLowerCase();
            if (mime.indexOf('ogg') !== -1) { return { ext: 'ogg', type: 'audio/ogg' }; }
            if (mime.indexOf('mp4') !== -1 || mime.indexOf('m4a') !== -1) { return { ext: 'm4a', type: 'audio/mp4' }; }
            if (mime.indexOf('wav') !== -1) { return { ext: 'wav', type: 'audio/wav' }; }
            return { ext: 'webm', type: 'audio/webm' };
        }

        function liberarMicrofone(stream) {
            if (!stream || typeof stream.getTracks !== 'function') { return; }
            $.each(stream.getTracks(), function(i, track) { try { track.stop(); } catch (e) {} });
        }

        function alternarBarraGravacao(ativa) {
            $('#mensagens-envio').prop('hidden', ativa);
            $('#mensagens-gravando').prop('hidden', !ativa);
        }

        function erroMicrofone(err) {
            var nome = err && err.name ? err.name : '';
            if (nome === 'NotAllowedError' || nome === 'PermissionDeniedError') {
                return 'Permissão de microfone negada. Libere o acesso ao microfone no navegador para gravar áudio.';
            }
            if (nome === 'NotFoundError' || nome === 'DevicesNotFoundError') {
                return 'Nenhum microfone encontrado neste dispositivo.';
            }
            if (nome === 'NotReadableError' || nome === 'TrackStartError') {
                return 'Não foi possível acessar o microfone. Verifique se outro programa está usando o dispositivo.';
            }
            return 'Não foi possível iniciar a gravação de áudio.';
        }

        function cancelarGravacao() {
            if (!gravacao) { return; }
            var g = gravacao;
            gravacao = null;
            clearInterval(g.timer);
            g.acao = 'cancelar';
            alternarBarraGravacao(false);
            enviarEstadoDigitando('');
            if (g.reconhecimento) {
                try { g.reconhecimento.stop(); } catch (e) {}
            }
            if (g.recorder && g.recorder.state !== 'inactive') {
                try { g.recorder.stop(); } catch (e) {}
            }
            liberarMicrofone(g.stream);
        }

        function finalizarGravacao(acao) {
            if (!gravacao) { return; }
            gravacao.acao = acao;
            clearInterval(gravacao.timer);
            enviarEstadoDigitando('');
            if (gravacao.reconhecimento) {
                try { gravacao.reconhecimento.stop(); } catch (e) {}
            }
            $('#mensagens-audio-parar, #mensagens-audio-enviar').prop('disabled', true);
            if (gravacao.recorder && gravacao.recorder.state !== 'inactive') {
                try { gravacao.recorder.stop(); } catch (e) { cancelarGravacao(); }
            } else {
                cancelarGravacao();
            }
        }

        function iniciarGravacao() {
            if (!atual || enviando || gravacao || (modoContexto && modoContexto.tipo === 'editar')) { return; }
            if (!anexosAtivos) {
                mostrarErro('Os anexos ainda não estão disponíveis: aplique o script sql/2026-11-16_mensagens_anexos.sql no banco.');
                return;
            }
            if (window.isSecureContext === false) {
                mostrarErro('A gravação de áudio exige conexão segura (HTTPS) ou acesso via localhost.');
                return;
            }
            if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function' || !window.MediaRecorder) {
                mostrarErro('Seu navegador não oferece suporte à gravação de áudio.');
                return;
            }
            $('#mensagens-erro, #mensagens-info').prop('hidden', true);
            fecharEmojis();

            navigator.mediaDevices.getUserMedia({ audio: true }).then(function(stream) {
                if (!atual || tela !== 'conversa') { liberarMicrofone(stream); return; }
                var escolha = escolherMimeAudio();
                var recorder;
                try {
                    recorder = escolha ? new window.MediaRecorder(stream, { mimeType: escolha.mime }) : new window.MediaRecorder(stream);
                } catch (e) {
                    try { recorder = new window.MediaRecorder(stream); }
                    catch (err) { liberarMicrofone(stream); mostrarErro('Não foi possível iniciar o gravador de áudio neste navegador.'); return; }
                }

                var partes = [];
                var info = escolha || extensaoDeMime(recorder.mimeType);
                var g = { recorder: recorder, stream: stream, partes: partes, inicio: Date.now(), timer: null, acao: 'parar', info: info, ultimoSinal: 0, transcricaoPartes: [], reconhecimento: null };
                gravacao = g;

                var Rec = window.SpeechRecognition || window.webkitSpeechRecognition;
                if (Rec) {
                    try {
                        var rec = new Rec();
                        rec.lang = 'pt-BR';
                        rec.continuous = true;
                        rec.interimResults = false;
                        rec.onresult = function(ev) {
                            for (var i = ev.resultIndex; i < ev.results.length; i++) {
                                if (ev.results[i].isFinal && ev.results[i][0]) {
                                    g.transcricaoPartes.push(ev.results[i][0].transcript);
                                }
                            }
                        };
                        rec.onerror = function() {};
                        rec.start();
                        g.reconhecimento = rec;
                    } catch (e) {}
                }

                recorder.ondataavailable = function(e) { if (e.data && e.data.size > 0) { partes.push(e.data); } };
                recorder.onerror = function() { cancelarGravacao(); mostrarErro('Ocorreu um erro durante a gravação do áudio.'); };
                recorder.onstop = function() {
                    clearInterval(g.timer);
                    liberarMicrofone(g.stream);
                    if (gravacao === g) { gravacao = null; }
                    alternarBarraGravacao(false);
                    $('#mensagens-audio-parar, #mensagens-audio-enviar').prop('disabled', false);
                    if (g.acao === 'cancelar') { return; }
                    var blob = new Blob(partes, { type: g.info.type });
                    if (!blob.size) { mostrarErro('O áudio gravado ficou vazio. Tente falar mais perto do microfone.'); return; }
                    if (blob.size > limiteArquivo) { mostrarErro('O áudio gravado passa do limite de ' + tamanhoLegivel(limiteArquivo) + '.'); return; }
                    var agora = new Date();
                    var pad = function(n) { return n < 10 ? '0' + n : String(n); };
                    var nome = 'audio-' + agora.getFullYear() + pad(agora.getMonth() + 1) + pad(agora.getDate())
                        + '-' + pad(agora.getHours()) + pad(agora.getMinutes()) + pad(agora.getSeconds()) + '.' + g.info.ext;
                    var arquivo;
                    try { arquivo = new File([blob], nome, { type: g.info.type }); }
                    catch (e) { arquivo = blob; arquivo.name = nome; }
                    var transc = $.trim((g.transcricaoPartes || []).join(' '));
                    pendentes.push({ arquivo: arquivo, progresso: 0, comprimindo: false, gravacaoAudio: true, transcricao: transc });
                    renderizarPendentes();
                    if (g.acao === 'enviar') { enviar(); }
                };

                $('#mensagens-audio-tempo').text('00:00');
                $('#mensagens-audio-parar, #mensagens-audio-enviar').prop('disabled', false);
                alternarBarraGravacao(true);
                enviarEstadoDigitando('gravando');
                g.timer = setInterval(function() {
                    $('#mensagens-audio-tempo').text(formatarTempo((Date.now() - g.inicio) / 1000));
                    if (Date.now() - g.ultimoSinal > 2500) {
                        g.ultimoSinal = Date.now();
                        enviarEstadoDigitando('gravando');
                    }
                }, 500);
                recorder.start();
            }).catch(function(err) {
                mostrarErro(erroMicrofone(err));
            });
        }

        /* ---------- Indicador de digitação e Menções (@) ---------- */

        function enviarEstadoDigitando(estado) {
            if (!atual && estado !== '') { return; }
            clearTimeout(timerPararDigitando);
            var chave = atual ? atual.chave : '';
            if (!chave) { return; }
            if (estado === 'digitando') {
                var agora = Date.now();
                timerPararDigitando = setTimeout(function() { enviarEstadoDigitando(''); }, 3500);
                if (agora - ultimoEnvioDigitando < 2000) { return; }
                ultimoEnvioDigitando = agora;
            } else {
                ultimoEnvioDigitando = 0;
            }
            $.ajax({ url: api + '/digitando', method: 'POST', dataType: 'json', global: false, data: { para: chave, estado: estado } });
        }

        function fecharMencoes() {
            mencaoConsulta = null;
            $('#mensagens-mencoes-popup').prop('hidden', true).empty();
        }

        function atualizarMencoesPopup() {
            var campo = $texto[0];
            var pos = campo.selectionStart || 0;
            var antes = campo.value.slice(0, pos);
            var match = /(?:^|\s)@([^\s@]{0,30})$/.exec(antes);
            if (!match) {
                fecharMencoes();
                return;
            }
            var termo = semAcento(match[1]);
            var candidatos = $.grep(participantesMencionaveis(), function(p) {
                return !termo || semAcento(p.nome).indexOf(termo) !== -1;
            });
            if (!candidatos.length) {
                fecharMencoes();
                return;
            }
            mencaoConsulta = {
                inicio: pos - match[1].length - 1,
                fim: pos,
                candidatos: candidatos.slice(0, 8),
                indice: 0
            };
            renderizarMencoesPopup();
        }

        function renderizarMencoesPopup() {
            if (!mencaoConsulta) { return; }
            var $popup = $('#mensagens-mencoes-popup').empty();
            $.each(mencaoConsulta.candidatos, function(i, p) {
                $popup.append($('<li>').append(
                    $('<button type="button" class="mensagens-mencao-item">')
                        .toggleClass('is-ativa', i === mencaoConsulta.indice)
                        .attr('data-i', i)
                        .append(avatar(p), $('<span>').text(p.nome))
                ));
            });
            $popup.prop('hidden', false);
        }

        function aplicarMencao(indice) {
            if (!mencaoConsulta || !mencaoConsulta.candidatos[indice]) { return; }
            var p = mencaoConsulta.candidatos[indice];
            var campo = $texto[0];
            var trecho = '@' + p.nome + ' ';
            campo.value = campo.value.slice(0, mencaoConsulta.inicio) + trecho + campo.value.slice(mencaoConsulta.fim);
            var novoCursor = mencaoConsulta.inicio + trecho.length;
            campo.selectionStart = campo.selectionEnd = novoCursor;
            mencoesSelecionadas[p.id] = p.nome;
            fecharMencoes();
            ajustarAltura();
            campo.focus();
        }

        function idsMencionadosNoTexto(texto) {
            var ids = [];
            $.each(mencoesSelecionadas, function(id, nome) {
                if (texto.indexOf('@' + nome) !== -1) {
                    ids.push(id === 'todos' ? 'todos' : parseInt(id, 10));
                }
            });
            if (atual && atual.tipo === 'canal' && /(?:^|\s)@todos(?=$|\s|[,.;:!?])/i.test(texto) && $.inArray('todos', ids) === -1) {
                ids.push('todos');
            }
            return ids;
        }

        /* ---------- Envio e Edição ---------- */

        function ajustarAltura() {
            $texto.css('height', 'auto').css('height', Math.min($texto[0].scrollHeight, 140) + 'px');
            var resta = maximo - $texto.val().length;
            $('#mensagens-contador').text(resta < 300 ? resta + ' caracteres restantes' : '');
        }

        function postar(dados, comArquivo, aoProgresso) {
            var opcoes = { url: api + '/enviar', method: 'POST', dataType: 'json', data: dados };
            if (comArquivo) {
                opcoes.processData = false;
                opcoes.contentType = false;
                opcoes.xhr = function() {
                    var xhr = $.ajaxSettings.xhr();
                    if (xhr.upload) {
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) { aoProgresso(Math.round(e.loaded / e.total * 100)); }
                        });
                    }
                    return xhr;
                };
            }
            return $.ajax(opcoes);
        }

        function registrarEnviada(m) {
            $historico.find('.mensagens-sem-historico').remove();
            adicionar([m]);
            var item = $.grep(conversas, function(c) { return c.chave === atual.chave; })[0];
            if (!item) {
                item = $.extend({}, atual, { nao_lidas: 0, mencoes: 0, ultima_autor: '' });
                conversas.push(item);
            }
            item.ultima = m.texto || (m.anexo ? ({ imagem: 'Foto', video: 'Vídeo', audio: 'Áudio' }[m.anexo.tipo] || 'Arquivo: ' + m.anexo.nome) : '');
            item.ultima_minha = true;
            item.ultima_autor = '';
            item.rascunho = '';
            item.marcada_nao_lida = false;
            item.quando = m.hora;
            item.ultima_id = m.id;
            conversas.sort(function(a, b) { return (b.ultima_id - a.ultima_id) || a.nome.localeCompare(b.nome); });
            renderizarListas();
        }

        function enviar() {
            var texto = $.trim($texto.val());
            if (!atual || enviando) { return; }
            if (atual.tipo === 'canal' && atual.pode_enviar === false) {
                mostrarErro('Você não tem permissão para enviar mensagens neste canal.');
                return;
            }

            // Modo edição de mensagem existente
            if (modoContexto && modoContexto.tipo === 'editar') {
                var editMsg = modoContexto.msg;
                if (!texto && !editMsg.anexo) {
                    mostrarErro('A mensagem não pode ficar vazia.');
                    return;
                }
                enviando = true;
                $('#mensagens-erro, #mensagens-info').prop('hidden', true);
                $('#mensagens-enviar').prop('disabled', true);
                $.ajax({
                    url: api + '/editar',
                    method: 'POST',
                    dataType: 'json',
                    data: { id: editMsg.id, texto: texto, mencoes: idsMencionadosNoTexto(texto) }
                }).done(function(res) {
                    if (res && res.ok && res.mensagem) {
                        atualizarBolhaExistente(res.mensagem);
                        $texto.val('');
                        ajustarAltura();
                        limparContexto();
                        mencoesSelecionadas = {};
                        atualizar();
                    }
                }).fail(mostrarErro).always(function() {
                    enviando = false;
                    $('#mensagens-enviar').prop('disabled', false);
                    $texto.trigger('focus');
                });
                return;
            }

            if (!texto && !pendentes.length) { return; }
            if ($.grep(pendentes, function(p) { return p.comprimindo; }).length) { return; }
            enviando = true;
            enviarEstadoDigitando('');
            clearTimeout(timerRascunho);
            fecharMencoes();
            $('#mensagens-erro, #mensagens-info').prop('hidden', true);
            $('#mensagens-enviar').prop('disabled', true);
            renderizarPendentes();

            var respostaId = (modoContexto && modoContexto.tipo === 'responder') ? modoContexto.msg.id : 0;
            var mencoes = idsMencionadosNoTexto(texto);

            function terminar() {
                enviando = false;
                $('#mensagens-enviar').prop('disabled', false);
                renderizarPendentes();
                $texto.trigger('focus');
            }

            function proximo(textoDaVez, respId) {
                if (!pendentes.length) {
                    if (!textoDaVez) { terminar(); return; }
                    postar({ para: atual.chave, texto: textoDaVez, resposta_id: respId || '', mencoes: mencoes }, false).done(function(res) {
                        if (res && res.ok) {
                            registrarEnviada(res.mensagem);
                            $texto.val('');
                            ajustarAltura();
                            limparContexto();
                            mencoesSelecionadas = {};
                        }
                    }).fail(mostrarErro).always(terminar);
                    return;
                }
                var p = pendentes[0];
                var dados = new FormData();
                dados.append('para', atual.chave);
                dados.append('texto', textoDaVez || '');
                dados.append('arquivo', p.arquivo, p.arquivo.name);
                if (p.gravacaoAudio) { dados.append('gravacao_audio', '1'); }
                if (p.transcricao) { dados.append('transcricao', p.transcricao); }
                if (respId) { dados.append('resposta_id', String(respId)); }
                if (textoDaVez && mencoes.length) {
                    $.each(mencoes, function(i, id) { dados.append('mencoes[]', String(id)); });
                }
                postar(dados, true, function(pct) {
                    p.progresso = pct;
                    var rotulo = pct >= 100 && p.arquivo.size > limiteCompressao ? 'Comprimindo no servidor…' : pct + '%';
                    $('#mensagens-pendentes .mensagens-pendente').first().find('small').text(rotulo);
                }).done(function(res) {
                    if (!res || !res.ok) { terminar(); return; }
                    if (textoDaVez) { $texto.val(''); ajustarAltura(); mencoesSelecionadas = {}; }
                    limparContexto();
                    var comprimido = res.comprimido ? res.comprimido.de + ' → ' + res.comprimido.para
                        : (p.original ? tamanhoLegivel(p.original) + ' → ' + tamanhoLegivel(p.arquivo.size) : '');
                    if (comprimido) { $('#mensagens-info').text('Arquivo comprimido antes do envio: ' + comprimido + '.').prop('hidden', false); }
                    if (p.previa && window.URL) { window.URL.revokeObjectURL(p.previa); }
                    pendentes.shift();
                    registrarEnviada(res.mensagem);
                    renderizarPendentes();
                    proximo('', 0);
                }).fail(function(xhr) {
                    p.progresso = 0;
                    mostrarErro(xhr);
                    terminar();
                });
            }
            proximo(texto, respostaId);
        }

        /* ---------- Emojis ---------- */

        function abrirEmojis() {
            var $painel = $('#mensagens-emojis');
            if (!$painel.children().length) {
                $.each(emojis, function(i, e) {
                    $painel.append($('<button type="button" class="mensagens-emoji">').text(e).attr('aria-label', e));
                });
            }
            $painel.prop('hidden', false);
            $('#mensagens-emoji-btn').attr('aria-expanded', 'true');
        }

        function fecharEmojis() {
            $('#mensagens-emojis').prop('hidden', true);
            $('#mensagens-emoji-btn').attr('aria-expanded', 'false');
        }

        function inserirNoCursor(trecho) {
            var campo = $texto[0];
            var inicio = campo.selectionStart || 0;
            var fim = campo.selectionEnd || 0;
            campo.value = campo.value.slice(0, inicio) + trecho + campo.value.slice(fim);
            campo.selectionStart = campo.selectionEnd = inicio + trecho.length;
            ajustarAltura();
            campo.focus();
        }

        /* ---------- Atualização periódica ---------- */

        function atualizar() {
            if (document.hidden) { return; }
            var dados = tela === 'conversa' && atual ? { com: atual.chave, depois: ultimoId } : {};
            if (typeof window.chatReacaoUltimoId === 'number') {
                dados.reacao_depois = window.chatReacaoUltimoId;
            }
            $.ajax({ url: api + '/atualizar', data: dados, dataType: 'json', cache: false, global: false }).done(function(res) {
                if (!res || !res.ok) { return; }
                conversas = res.conversas || conversas;
                pessoas = res.pessoas || pessoas;
                digitandoLista = res.digitando_lista || {};
                if (res.agendadas) { agendadasLista = res.agendadas; }
                if (res.lembretes) { lembretesLista = res.lembretes; }
                if (res.etiquetas) { etiquetasLista = res.etiquetas; }
                if (res.meu_status) { meuStatus = res.meu_status; }
                if (res.reacoes_novas && typeof window.receberReacoesNovas === 'function') {
                    window.receberReacoesNovas(res.reacoes_novas);
                }
                if (typeof window.receberChamadasRecebidas === 'function') {
                    window.receberChamadasRecebidas(res.chamadas_recebidas || []);
                }

                if (tela === 'conversa' && atual) {
                    if (res.removido) {
                        var nome = atual.nome;
                        sairDaConversa(atual.chave);
                        avisar('info', 'Conversa indisponível', 'Você não participa mais de "' + nome + '", ou o grupo/canal foi excluído.');
                        return;
                    }
                    if (res.conversa) {
                        atual.membros = res.conversa.membros;
                        atual.sou_criador = res.conversa.sou_criador;
                        if (typeof res.conversa.pode_enviar !== 'undefined') {
                            atual.pode_enviar = res.conversa.pode_enviar;
                        }
                        if (res.conversa.grupo_ids) { atual.grupo_ids = res.conversa.grupo_ids; }
                        if (res.conversa.escritor_ids) { atual.escritor_ids = res.conversa.escritor_ids; }
                        if (typeof res.conversa.descricao !== 'undefined') { atual.descricao = res.conversa.descricao; }
                    }
                    if (res.membros) { membrosAtual = res.membros; }
                    if (typeof res.chamada_ativa !== 'undefined') {
                        chamadaConversaAtiva = res.chamada_ativa;
                    }
                    digitandoAtual = res.digitando || [];
                    atualizarTopo();

                    if (res.fixadas) {
                        fixadasAtuais = res.fixadas;
                        renderizarFixadasBanner();
                    }
                    if (res.novas && res.novas.length) {
                        $historico.find('.mensagens-sem-historico').remove();
                        adicionar(res.novas);
                    }
                    if (res.sincronizadas && res.sincronizadas.length) {
                        $.each(res.sincronizadas, function(i, m) { atualizarBolhaExistente(m); });
                    }
                    if (res.reacoes) {
                        $.each(mensagensMapa, function(midStr, m) {
                            var mid = parseInt(midStr, 10);
                            var novasReacoes = res.reacoes[mid] || [];
                            if (JSON.stringify(m.reacoes || []) !== JSON.stringify(novasReacoes)) {
                                m.reacoes = novasReacoes;
                                aplicarReacoesNaBolha(mid, novasReacoes);
                            }
                        });
                    }
                    if (res.enquetes) {
                        $.each(res.enquetes, function(midStr, eq) {
                            var mid = parseInt(midStr, 10);
                            var m = mensagensMapa[mid];
                            if (m && JSON.stringify(m.enquete || null) !== JSON.stringify(eq)) {
                                m.enquete = eq;
                                $historico.find('.mensagens-msg[data-id="' + mid + '"] .mensagens-enquete-card').replaceWith(renderizarEnqueteCard(eq));
                            }
                        });
                    }
                    if (res.reunioes) {
                        $.each(res.reunioes, function(midStr, reu) {
                            var mid = parseInt(midStr, 10);
                            var m = mensagensMapa[mid];
                            if (m && JSON.stringify(m.reuniao || null) !== JSON.stringify(reu)) {
                                m.reuniao = reu;
                                $historico.find('.mensagens-msg[data-id="' + mid + '"] .mensagens-reuniao-card').replaceWith(renderizarReuniaoCard(reu));
                            }
                        });
                    }
                    if (typeof res.entregue_ate === 'number' && res.entregue_ate > entregueAte) {
                        entregueAte = res.entregue_ate;
                    }
                    if (typeof res.visto_ate === 'number' && res.visto_ate > vistoAte) {
                        vistoAte = res.visto_ate;
                    }
                    atualizarTicksDireta();

                    if (res.status_grupo) {
                        $.each(res.status_grupo, function(idStr, st) {
                            var mid = parseInt(idStr, 10);
                            var m = mensagensMapa[mid];
                            if (m && (m.status !== st.status || m.lidas_grupo !== st.lidas_grupo || m.pode_editar !== st.pode_editar)) {
                                m.status = st.status;
                                m.lidas_grupo = st.lidas_grupo;
                                m.total_grupo = st.total_grupo;
                                m.pode_editar = st.pode_editar;
                                var $novo = elementoTicks(m);
                                if ($novo) {
                                    $historico.find('.mensagens-msg[data-id="' + mid + '"] .mensagens-ticks').replaceWith($novo);
                                }
                            }
                        });
                    }
                }
                renderizarListas();
            });
        }

        /* ---------- Eventos ---------- */

        $app.on('click', '.js-ir-nova', function() {
            $('#mensagens-busca-pessoas').val('');
            renderizarListas();
            irPara('nova');
            setTimeout(function() { $('#mensagens-busca-pessoas').trigger('focus'); }, 360);
        });
        $app.on('click', '.js-ir-grupo', function(e) {
            e.preventDefault();
            var origem = $(this).closest('[data-tela]').attr('data-tela');
            telaAntesGrupo = (origem === 'lista' && tela === 'conversa') ? 'conversa' : (origem || 'lista');
            renderizarEscolhaGrupo();
            irPara('grupo');
            setTimeout(function() { $('#mensagens-grupo-nome').trigger('focus'); }, 360);
        });
        $app.on('click', '.js-voltar', voltarParaLista);
        $app.on('click', '.js-voltar-grupo', function() {
            if (telaAntesGrupo === 'conversa' && atual) {
                window.mensagensConversaAberta = atual.chave;
                irPara('conversa');
                renderizarListas();
                return;
            }
            irPara(telaAntesGrupo === 'nova' ? 'nova' : 'lista');
        });
        $app.on('click', '.js-abrir', function() { abrir($(this).attr('data-chave')); });
        $('#mensagens-busca, #mensagens-busca-pessoas').on('input', renderizarListas);

        $('#mensagens-busca-grupo, #mensagens-grupo-nome').on('input', renderizarEscolhaGrupo);
        $('#mensagens-grupo-pessoas').on('click', '.is-selecionavel', function() {
            alternar(escolhidosGrupo, parseInt($(this).attr('data-id'), 10));
            renderizarEscolhaGrupo();
        });
        $('#mensagens-grupo-escolhidos').on('click', '.mensagens-escolhido-remover', function() {
            alternar(escolhidosGrupo, parseInt($(this).attr('data-id'), 10));
            renderizarEscolhaGrupo();
        });
        $('#mensagens-grupo-criar').on('click', criarGrupo);
        $('#mensagens-grupo-nome').on('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); criarGrupo(); } });

        $app.on('click', '.js-detalhes-grupo', abrirDetalhes);
        $('#mensagens-membros').on('click', '.mensagens-remover-membro', function() { removerMembro(parseInt($(this).attr('data-id'), 10), $(this).attr('data-nome')); });
        $('#mensagens-busca-adicionar').on('input', renderizarDetalhes);
        $('#mensagens-adicionar-pessoas').on('click', '.is-selecionavel', function() {
            alternar(escolhidosAdicionar, parseInt($(this).attr('data-id'), 10));
            renderizarDetalhes();
        });
        $('#mensagens-adicionar-btn').on('click', adicionarMembros);
        $('#mensagens-sair-grupo').on('click', sairDoGrupo);
        $('#mensagens-excluir-grupo').on('click', excluirGrupo);

        // Eventos dos novos recursos
        $('#mensagens-busca-conversa-btn').on('click', abrirModalBusca);
        $('#mensagens-busca-form').on('submit', function(e) { e.preventDefault(); executarBusca(); });
        $('#mensagens-abrir-favoritas').on('click', function(e) { e.preventDefault(); abrirModalFavoritas(); });
        $('#mensagens-fav-conversa').on('change', carregarFavoritas);
        $('#mensagens-fixadas-btn, #mensagens-fixada-todas').on('click', abrirModalFixadas);
        $('#mensagens-fixada-ir').on('click', function() { irParaMensagem($(this).attr('data-alvo-id')); });
        $('#mensagens-notif-btn').on('click', abrirModalNotificacoes);
        $('#mensagens-notif-navegador').on('click', function() {
            if (!('Notification' in window)) { return; }
            window.Notification.requestPermission().then(function() { abrirModalNotificacoes(); });
        });
        $('#mensagens-notif-salvar').on('click', function() {
            if (!atual) { return; }
            if (window.localStorage) {
                window.localStorage.setItem('sax_chat_som', $('#mensagens-notif-som').is(':checked') ? '1' : '0');
            }
            $.ajax({
                url: api + '/notificacao_config',
                method: 'POST',
                dataType: 'json',
                data: { para: atual.chave, modo: $('#mensagens-notif-modo').val(), silenciar: $('#mensagens-notif-silenciar').val() }
            }).done(function(res) {
                if (res && res.ok) {
                    configAtual = res.config;
                    atualizarTopo();
                    atualizar();
                    $('#mensagens-notif-modal').modal('hide');
                }
            }).fail(function(xhr) { avisar('error', 'Erro ao salvar', erroDe(xhr)); });
        });

        $('#mensagens-enc-busca').on('input', renderizarListaEncaminhar);
        $('#mensagens-enc-lista').on('click', '.is-selecionavel', function() {
            var ch = $(this).attr('data-chave');
            var idx = $.inArray(ch, encaminharDestinos);
            if (idx === -1) { encaminharDestinos.push(ch); } else { encaminharDestinos.splice(idx, 1); }
            renderizarListaEncaminhar();
        });
        $('#mensagens-enc-enviar').on('click', function() {
            if (!encaminharMsgId || !encaminharDestinos.length) { return; }
            $('#mensagens-enc-enviar').prop('disabled', true);
            $.ajax({
                url: api + '/encaminhar',
                method: 'POST',
                dataType: 'json',
                data: { id: encaminharMsgId, destinos: encaminharDestinos }
            }).done(function(res) {
                if (res && res.ok) {
                    $('#mensagens-encaminhar-modal').modal('hide');
                    if (atual && res.enviadas && res.enviadas[atual.chave]) {
                        registrarEnviada(res.enviadas[atual.chave]);
                    }
                    atualizar();
                }
            }).fail(function(xhr) {
                avisar('error', 'Não foi possível encaminhar', erroDe(xhr));
            }).always(function() { $('#mensagens-enc-enviar').prop('disabled', false); });
        });

        $historico.on('click', '#mensagens-carregar-btn', function() {
            if (!atual || !primeiroId || carregandoAnteriores) { return; }
            carregandoAnteriores = true;
            $.getJSON(api + '/anteriores/' + atual.chave, { antes: primeiroId }).done(function(res) {
                if (res && res.ok) { prefixarMensagens(res.mensagens || []); }
            }).always(function() { carregandoAnteriores = false; });
        });
        $historico.on('click', '.js-ir-msg', function() { irParaMensagem($(this).attr('data-alvo-id')); });
        $historico.on('click', '.js-ver-visualizacoes', function() { abrirVisualizacoes($(this).attr('data-id')); });
        $historico.on('click', '.js-abrir-menu', function(e) {
            e.stopPropagation();
            abrirMenuMensagem($(this));
        });

        $menuMsg.on('click', '.mensagens-menu-item', function(e) {
            e.stopPropagation();
            var acao = $(this).attr('data-acao');
            var m = mensagensMapa[menuMsgId];
            fecharMenuMensagem();
            if (!m) { return; }
            if (acao === 'reagir') { abrirPopupReacoes($historico.find('.js-abrir-menu[data-id="' + m.id + '"]'), m.id); }
            else if (acao === 'responder') { iniciarResposta(m); }
            else if (acao === 'editar') { iniciarEdicao(m); }
            else if (acao === 'excluir') { solicitarExclusao(m); }
            else if (acao === 'fixar') { alternarFixar(m.id, !m.fixada); }
            else if (acao === 'favoritar') { alternarFavoritar(m.id); }
            else if (acao === 'encaminhar') { abrirModalEncaminhar(m); }
            else if (acao === 'lembrete') { abrirModalLembretes(m); }
            else if (acao === 'chamado') { abrirModalChamado(m); }
            else if (acao === 'visualizacoes') { abrirVisualizacoes(m.id); }
        });

        $historico.on('click', '.js-transcrever-audio', function() {
            var $btn = $(this);
            var id = parseInt($btn.attr('data-id'), 10);
            if (!id) { return; }
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Transcrevendo…');
            $.ajax({ url: api + '/transcrever', method: 'POST', dataType: 'json', data: { id: id } }).done(function(res) {
                if (res && res.ok) {
                    if (mensagensMapa[id]) {
                        mensagensMapa[id].transcricao = res.transcricao;
                        atualizarBolhaExistente(mensagensMapa[id]);
                    }
                    return;
                }
                if (res && res.precisa_cliente) {
                    solicitarTranscricaoManual(id, res.erro);
                } else {
                    avisar('info', 'Transcrição', (res && res.erro) || 'Não foi possível transcrever este áudio.');
                }
            }).fail(function(xhr) {
                var json = xhr && xhr.responseJSON;
                if (json && json.precisa_cliente) {
                    solicitarTranscricaoManual(id, json.erro);
                } else {
                    avisar('error', 'Transcrição', erroDe(xhr));
                }
            }).always(function() {
                $btn.prop('disabled', false).html('<i class="fa fa-file-text-o"></i> Transcrever');
            });
        });

        function solicitarTranscricaoManual(id, motivo) {
            var executarSalvar = function(textoTransc) {
                textoTransc = $.trim(textoTransc || '');
                if (!textoTransc) { return; }
                $.ajax({
                    url: api + '/transcrever',
                    method: 'POST',
                    dataType: 'json',
                    data: { id: id, texto: textoTransc }
                }).done(function(res) {
                    if (res && res.ok && mensagensMapa[id]) {
                        mensagensMapa[id].transcricao = res.transcricao;
                        atualizarBolhaExistente(mensagensMapa[id]);
                    }
                }).fail(function(xhr) { avisar('error', 'Erro ao salvar transcrição', erroDe(xhr)); });
            };
            if (window.Swal) {
                window.Swal.fire({
                    title: 'Transcrever áudio',
                    text: motivo || 'O servidor não possui motor automático de transcrição configurado. Digite ou dite a transcrição abaixo para salvá-la ao lado do áudio:',
                    input: 'textarea',
                    inputPlaceholder: 'Digite a transcrição do áudio...',
                    showCancelButton: true,
                    confirmButtonText: 'Salvar transcrição',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#1E1D3F'
                }).then(function(r) {
                    if (r.isConfirmed && r.value) { executarSalvar(r.value); }
                });
            } else {
                var digitado = window.prompt('Digite a transcrição do áudio:');
                if (digitado) { executarSalvar(digitado); }
            }
        }

        $('#mensagens-contexto-fechar').on('click', function() {
            if (modoContexto && modoContexto.tipo === 'editar') {
                $texto.val('');
                ajustarAltura();
            }
            limparContexto();
        });

        function salvarRascunho(chave, txt) {
            if (!chave) { return; }
            $.each(conversas, function(i, c) {
                if (c.chave === chave) { c.rascunho = txt; }
            });
            $.ajax({ url: api + '/rascunho', method: 'POST', dataType: 'json', global: false, data: { para: chave, texto: txt } });
        }

        function agendarSalvarRascunho() {
            if (!atual || (modoContexto && modoContexto.tipo === 'editar')) { return; }
            var ch = atual.chave;
            var txt = $texto.val();
            $.each(conversas, function(i, c) {
                if (c.chave === ch) { c.rascunho = txt; }
            });
            clearTimeout(timerRascunho);
            timerRascunho = setTimeout(function() { salvarRascunho(ch, txt); }, 700);
        }

        $('#mensagens-envio').on('submit', function(e) { e.preventDefault(); enviar(); });
        $texto.on('input', function() {
            ajustarAltura();
            atualizarMencoesPopup();
            agendarSalvarRascunho();
            if ($.trim($texto.val()) !== '') {
                enviarEstadoDigitando('digitando');
            } else {
                enviarEstadoDigitando('');
            }
        }).on('keydown', function(e) {
            if (mencaoConsulta) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    mencaoConsulta.indice = (mencaoConsulta.indice + 1) % mencaoConsulta.candidatos.length;
                    renderizarMencoesPopup();
                    return;
                }
                if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    mencaoConsulta.indice = (mencaoConsulta.indice - 1 + mencaoConsulta.candidatos.length) % mencaoConsulta.candidatos.length;
                    renderizarMencoesPopup();
                    return;
                }
                if (e.key === 'Enter' || e.key === 'Tab') {
                    e.preventDefault();
                    aplicarMencao(mencaoConsulta.indice);
                    return;
                }
                if (e.key === 'Escape') {
                    e.preventDefault();
                    fecharMencoes();
                    return;
                }
            }
            if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
                e.preventDefault();
                enviar();
            }
        }).on('click', atualizarMencoesPopup).on('paste', function(e) {
            var dados = e.originalEvent.clipboardData;
            if (dados && dados.files && dados.files.length) {
                e.preventDefault();
                adicionarArquivos(dados.files);
            }
        });

        $('#mensagens-mencoes-popup').on('click', '.mensagens-mencao-item', function(e) {
            e.stopPropagation();
            aplicarMencao(parseInt($(this).attr('data-i'), 10));
        });

        $('#mensagens-anexo-btn').on('click', function() { $('#mensagens-arquivo').trigger('click'); });
        $('#mensagens-audio-btn').on('click', iniciarGravacao);
        $('#mensagens-audio-cancelar').on('click', cancelarGravacao);
        $('#mensagens-audio-parar').on('click', function() { finalizarGravacao('parar'); });
        $('#mensagens-audio-enviar').on('click', function() { finalizarGravacao('enviar'); });
        $(window).on('beforeunload', function() {
            cancelarGravacao();
            enviarEstadoDigitando('');
            if (chamadaAtiva && chamadaAtiva.id && navigator.sendBeacon) {
                var fd = new FormData();
                fd.append('chamada_id', String(chamadaAtiva.id));
                navigator.sendBeacon(api + '/chamada_sair', fd);
            }
        });
        $('#mensagens-arquivo').on('change', function() {
            adicionarArquivos(this.files);
            this.value = '';
        });
        $('#mensagens-pendentes').on('click', '.mensagens-pendente-remover', function() {
            var i = parseInt($(this).closest('.mensagens-pendente').attr('data-i'), 10);
            if (pendentes[i] && pendentes[i].previa && window.URL) { window.URL.revokeObjectURL(pendentes[i].previa); }
            pendentes.splice(i, 1);
            renderizarPendentes();
        });

        var $telaConversa = $app.find('.mensagens-tela[data-tela="conversa"]');
        var arrastando = 0;
        $telaConversa.on('dragenter', function(e) {
            if (e.originalEvent.dataTransfer && $.inArray('Files', e.originalEvent.dataTransfer.types) !== -1) {
                arrastando++;
                $telaConversa.addClass('is-arrastando');
            }
        }).on('dragover', function(e) { e.preventDefault(); })
          .on('dragleave', function() { arrastando = Math.max(0, arrastando - 1); if (!arrastando) { $telaConversa.removeClass('is-arrastando'); } })
          .on('drop', function(e) {
              e.preventDefault();
              arrastando = 0;
              $telaConversa.removeClass('is-arrastando');
              if (e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files.length) {
                  adicionarArquivos(e.originalEvent.dataTransfer.files);
              }
          });

        $('#mensagens-emoji-btn').on('click', function(e) {
            e.stopPropagation();
            if ($('#mensagens-emojis').prop('hidden')) { abrirEmojis(); } else { fecharEmojis(); }
        });
        $('#mensagens-emojis').on('click', '.mensagens-emoji', function(e) {
            e.stopPropagation();
            inserirNoCursor($(this).text());
        });
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#mensagens-emojis').length) { fecharEmojis(); }
            if (!$(e.target).closest('#mensagens-menu-msg').length) { fecharMenuMensagem(); }
            if (!$(e.target).closest('#mensagens-reacoes-popup, .js-abrir-reacoes').length) { fecharPopupReacoes(); }
            if (!$(e.target).closest('#mensagens-mencoes-popup, #mensagens-texto').length) { fecharMencoes(); }
        }).on('keydown', function(e) {
            if (e.key === 'Escape') { fecharEmojis(); fecharMenuMensagem(); fecharPopupReacoes(); fecharMencoes(); }
        });

        window.addEventListener('popstate', function(e) {
            if (e.state && e.state.conversa) {
                abrir(e.state.conversa, true);
            } else {
                empilhado = false;
                irPara('lista');
            }
        });
        document.addEventListener('visibilitychange', function() { if (!document.hidden) { atualizar(); } });

        /* ---------- Recursos v2: Agendamento, Etiquetas, Lembretes, Notas, Chamados, Status, Mídias, Canais e WebRTC ---------- */

        function formatarDataInputLocal(dt) {
            var pad = function(n) { return n < 10 ? '0' + n : String(n); };
            return dt.getFullYear() + '-' + pad(dt.getMonth() + 1) + '-' + pad(dt.getDate())
                + 'T' + pad(dt.getHours()) + ':' + pad(dt.getMinutes());
        }

        function datetimeLocalParaSql(val) {
            if (!val) { return ''; }
            var s = String(val).replace('T', ' ');
            return s.length === 16 ? s + ':00' : s;
        }

        function sqlParaDatetimeLocal(sql) {
            if (!sql) { return ''; }
            return String(sql).replace(' ', 'T').slice(0, 16);
        }

        // 1. Agendamento de mensagens
        function renderizarAgendadas() {
            var $lista = $('#mensagens-ag-lista').empty();
            var itens = agendadasLista || [];
            if (!itens.length) {
                $lista.html('<p class="text-muted small">Nenhuma mensagem agendada.</p>');
                return;
            }
            $.each(itens, function(i, ag) {
                $lista.append($('<div class="mensagens-card-item">').append(
                    $('<div class="mensagens-card-topo">').append(
                        $('<strong>').text(ag.destino_nome || ag.chave),
                        $('<small class="text-muted">').text(ag.enviar_em_fmt || ag.enviar_em)
                    ),
                    $('<p class="mensagens-card-texto">').text(ag.texto),
                    $('<div class="mensagens-card-acoes">').append(
                        $('<button type="button" class="btn btn-default btn-xs">').html('<i class="fa fa-pencil"></i> Editar').on('click', function() {
                            $('#mensagens-ag-id').val(ag.id);
                            $('#mensagens-ag-texto').val(ag.texto);
                            $('#mensagens-ag-data').val(sqlParaDatetimeLocal(ag.enviar_em));
                            $('#mensagens-ag-salvar').html('<i class="fa fa-check"></i> Salvar edição');
                            $('#mensagens-ag-form').prop('hidden', false);
                        }),
                        $('<button type="button" class="btn btn-danger btn-xs">').html('<i class="fa fa-times"></i> Cancelar envio').on('click', function() {
                            $.ajax({ url: api + '/agendada_cancelar', method: 'POST', dataType: 'json', data: { id: ag.id } }).done(function(res) {
                                if (res && res.ok) {
                                    agendadasLista = $.grep(agendadasLista || [], function(x) { return x.id !== ag.id; });
                                    renderizarAgendadas();
                                    atualizarIndicadoresGerais();
                                }
                            }).fail(function(xhr) { avisar('error', 'Erro', erroDe(xhr)); });
                        })
                    )
                ));
            });
        }

        function abrirModalAgendar(preTexto) {
            $('#mensagens-ag-id').val('');
            $('#mensagens-ag-texto').val(preTexto !== undefined ? preTexto : (atual ? $.trim($texto.val()) : ''));
            var d = new Date(Date.now() + 30 * 60000);
            $('#mensagens-ag-data').val(formatarDataInputLocal(d));
            $('#mensagens-ag-salvar').html('<i class="fa fa-clock-o"></i> Agendar');
            $('#mensagens-ag-form').prop('hidden', !atual);
            $('#mensagens-agendar-modal').modal('show');
            renderizarAgendadas();
            $.getJSON(api + '/agendadas').done(function(res) {
                if (res && res.ok) {
                    agendadasLista = res.itens || [];
                    renderizarAgendadas();
                    atualizarIndicadoresGerais();
                }
            });
        }

        $('#mensagens-abrir-agendadas').on('click', function(e) { e.preventDefault(); abrirModalAgendar(''); });
        $('#mensagens-agendar-btn').on('click', function() {
            if (!atual) { return; }
            abrirModalAgendar($.trim($texto.val()));
        });
        $('#mensagens-ag-form').on('submit', function(e) {
            e.preventDefault();
            var id = parseInt($('#mensagens-ag-id').val() || '0', 10);
            var txt = $.trim($('#mensagens-ag-texto').val());
            var dataEnv = datetimeLocalParaSql($('#mensagens-ag-data').val());
            if (!txt || !dataEnv) {
                avisar('info', 'Campos obrigatórios', 'Informe o texto e a data/hora do envio.');
                return;
            }
            if (!id && !atual) {
                avisar('info', 'Selecione uma conversa', 'Abra uma conversa antes de agendar uma nova mensagem.');
                return;
            }
            var urlAg = id ? '/agendada_editar' : '/agendar';
            var payload = id
                ? { id: id, texto: txt, enviar_em: dataEnv }
                : { para: atual.chave, texto: txt, enviar_em: dataEnv, resposta_id: (modoContexto && modoContexto.tipo === 'responder') ? modoContexto.msg.id : '', mencoes: idsMencionadosNoTexto(txt) };
            $.ajax({ url: api + urlAg, method: 'POST', dataType: 'json', data: payload }).done(function(res) {
                if (!res || !res.ok) { return; }
                if (!id && $.trim($texto.val()) === txt) {
                    $texto.val('');
                    ajustarAltura();
                    salvarRascunho(atual.chave, '');
                    limparContexto();
                }
                $('#mensagens-ag-id').val('');
                $('#mensagens-ag-texto').val('');
                $('#mensagens-ag-salvar').html('<i class="fa fa-clock-o"></i> Agendar');
                if (res.agendadas) { agendadasLista = res.agendadas; }
                $.getJSON(api + '/agendadas').done(function(r2) {
                    if (r2 && r2.ok) { agendadasLista = r2.itens || []; }
                    renderizarAgendadas();
                    atualizarIndicadoresGerais();
                });
            }).fail(function(xhr) { avisar('error', 'Não foi possível agendar', erroDe(xhr)); });
        });

        // 2. Marcar conversa como não lida
        $('#mensagens-nao-lida-btn').on('click', function() {
            if (!atual) { return; }
            var chave = atual.chave;
            $.ajax({ url: api + '/marcar_nao_lida', method: 'POST', dataType: 'json', data: { para: chave, nao_lida: '1' } }).done(function(res) {
                if (res && res.ok) {
                    $.each(conversas, function(i, c) {
                        if (c.chave === chave) { c.marcada_nao_lida = true; }
                    });
                    atual = null;
                    window.mensagensConversaAberta = null;
                    voltarParaLista();
                    renderizarListas();
                }
            }).fail(function(xhr) { avisar('error', 'Erro', erroDe(xhr)); });
        });

        // 3. Categorias e etiquetas
        $('#mensagens-etiquetas-bar').on('click', '[data-etiqueta]', function(e) {
            e.preventDefault();
            filtroEtiqueta = $(this).attr('data-etiqueta') || '';
            $('#mensagens-etiquetas-bar .mensagens-etiqueta-dropdown').removeClass('open').find('.dropdown-toggle').attr('aria-expanded', 'false');
            renderizarListas();
        });

        function etiquetasDaConversaAtual() {
            if (!atual) { return []; }
            var item = $.grep(conversas, function(c) { return c.chave === atual.chave; })[0];
            return (item && item.etiquetas) ? item.etiquetas : [];
        }

        function renderizarModalEtiquetas() {
            var $lista = $('#mensagens-et-lista').empty();
            var ativas = $.map(etiquetasDaConversaAtual(), function(e) { return e.id; });
            if (!etiquetasLista || !etiquetasLista.length) {
                $lista.html('<p class="text-muted small">Nenhuma etiqueta cadastrada.</p>');
                return;
            }
            $.each(etiquetasLista, function(i, et) {
                var marcada = atual && $.inArray(et.id, ativas) !== -1;
                var $row = $('<div class="mensagens-et-row">');
                if (atual) {
                    $row.append(
                        $('<button type="button" class="btn btn-xs btn-block text-left mensagens-et-toggle">')
                            .toggleClass('is-ativa', marcada)
                            .append(
                                $('<span class="mensagens-chip-cor">').css('background', et.cor),
                                $('<span>').text(et.nome),
                                marcada ? '<i class="fa fa-check pull-right text-success"></i>' : null
                            )
                            .on('click', function() {
                                $.ajax({ url: api + '/etiqueta_conversa', method: 'POST', dataType: 'json', data: { para: atual.chave, etiqueta_id: et.id } }).done(function(res) {
                                    if (!res || !res.ok) { return; }
                                    $.each(conversas, function(j, c) {
                                        if (c.chave !== atual.chave) { return; }
                                        c.etiquetas = $.grep(c.etiquetas || [], function(x) { return x.id !== et.id; });
                                        if (res.ativa) { c.etiquetas.push(et); }
                                    });
                                    renderizarModalEtiquetas();
                                    renderizarListas();
                                }).fail(function(xhr) { avisar('error', 'Erro', erroDe(xhr)); });
                            })
                    );
                } else {
                    $row.append($('<span class="mensagens-et-static">').append($('<span class="mensagens-chip-cor">').css('background', et.cor), $('<span>').text(et.nome)));
                }
                if (et.propria) {
                    $row.append($('<button type="button" class="btn btn-xs btn-link text-danger" title="Excluir etiqueta"><i class="fa fa-trash-o"></i></button>').on('click', function() {
                        $.ajax({ url: api + '/etiqueta_excluir', method: 'POST', dataType: 'json', data: { id: et.id } }).done(function(res) {
                            if (res && res.ok) {
                                etiquetasLista = $.grep(etiquetasLista, function(x) { return x.id !== et.id; });
                                $.each(conversas, function(j, c) {
                                    if (c.etiquetas) { c.etiquetas = $.grep(c.etiquetas, function(x) { return x.id !== et.id; }); }
                                });
                                if (String(filtroEtiqueta) === String(et.id)) { filtroEtiqueta = ''; }
                                renderizarModalEtiquetas();
                                renderizarListas();
                            }
                        });
                    }));
                }
                $lista.append($row);
            });
        }

        $('#mensagens-etiquetas-btn').on('click', function() {
            if (!atual) { return; }
            $('#mensagens-et-sub').text('Conversa: ' + atual.nome);
            renderizarModalEtiquetas();
            $('#mensagens-etiquetas-modal').modal('show');
        });
        $('#mensagens-et-form').on('submit', function(e) {
            e.preventDefault();
            var nome = $.trim($('#mensagens-et-nome').val());
            var cor = $('#mensagens-et-cor').val() || '#1E1D3F';
            if (!nome) { return; }
            $.ajax({ url: api + '/etiqueta_criar', method: 'POST', dataType: 'json', data: { nome: nome, cor: cor } }).done(function(res) {
                if (res && res.ok && res.etiqueta) {
                    etiquetasLista.push(res.etiqueta);
                    $('#mensagens-et-nome').val('');
                    renderizarModalEtiquetas();
                    renderizarListas();
                }
            }).fail(function(xhr) { avisar('error', 'Erro', erroDe(xhr)); });
        });

        // 4. Lembretes vinculados a mensagens
        function renderizarLembretes() {
            var $lista = $('#mensagens-lem-lista').empty();
            var itens = lembretesLista || [];
            if (!itens.length) {
                $lista.html('<p class="text-muted small">Nenhum lembrete ativo.</p>');
                return;
            }
            $.each(itens, function(i, l) {
                $lista.append($('<div class="mensagens-card-item">').toggleClass('is-concluido', !!l.concluido).append(
                    $('<div class="mensagens-card-topo">').append(
                        $('<strong>').text(l.lembrar_em_fmt || l.lembrar_em),
                        $('<small class="text-muted">').text(l.concluido ? 'Concluído' : (l.autor || ''))
                    ),
                    l.nota ? $('<p class="mensagens-card-texto">').text(l.nota) : null,
                    l.mensagem_previa ? $('<p class="small text-muted" style="margin:0 0 6px;">').text('Mensagem: "' + l.mensagem_previa + '"') : null,
                    $('<div class="mensagens-card-acoes">').append(
                        l.chave ? $('<button type="button" class="btn btn-default btn-xs">').text('Ir para mensagem').on('click', function() {
                            $('#mensagens-lembretes-modal').modal('hide');
                            irParaMensagem(l.mensagem_id, l.chave);
                        }) : null,
                        !l.concluido ? $('<button type="button" class="btn btn-success btn-xs">').html('<i class="fa fa-check"></i> Concluir').on('click', function() {
                            $.ajax({ url: api + '/lembrete_concluir', method: 'POST', dataType: 'json', data: { id: l.id } }).done(function(res) {
                                if (res && res.ok) {
                                    lembretesLista = res.lembretes || [];
                                    renderizarLembretes();
                                    atualizarIndicadoresGerais();
                                }
                            });
                        }) : null,
                        $('<button type="button" class="btn btn-default btn-xs text-danger">').html('<i class="fa fa-trash-o"></i>').on('click', function() {
                            $.ajax({ url: api + '/lembrete_excluir', method: 'POST', dataType: 'json', data: { id: l.id } }).done(function(res) {
                                if (res && res.ok) {
                                    lembretesLista = res.lembretes || [];
                                    renderizarLembretes();
                                    atualizarIndicadoresGerais();
                                }
                            });
                        })
                    )
                ));
            });
        }

        function definirAtalhoLembrete(modo) {
            var d = new Date();
            if (modo === '1h') {
                d = new Date(d.getTime() + 60 * 60000);
            } else if (modo === 'hoje18') {
                d.setHours(18, 0, 0, 0);
                if (d.getTime() <= Date.now()) { d.setDate(d.getDate() + 1); }
            } else {
                d.setDate(d.getDate() + 1);
                d.setHours(9, 0, 0, 0);
            }
            $('#mensagens-lem-data').val(formatarDataInputLocal(d));
        }

        function abrirModalLembretes(m) {
            if (m && m.id) {
                $('#mensagens-lem-msg-id').val(m.id);
                $('#mensagens-lem-nota').val('Responder ' + (m.minha ? 'minha mensagem' : m.autor) + ': ' + (m.texto || (m.anexo ? m.anexo.nome : 'anexo')).slice(0, 80));
                definirAtalhoLembrete('amanha9');
                $('#mensagens-lem-form').prop('hidden', false);
            } else {
                $('#mensagens-lem-msg-id').val('0');
                $('#mensagens-lem-form').prop('hidden', true);
            }
            $('#mensagens-lembretes-modal').modal('show');
            renderizarLembretes();
            $.getJSON(api + '/lembretes').done(function(res) {
                if (res && res.ok) {
                    lembretesLista = res.itens || [];
                    renderizarLembretes();
                    atualizarIndicadoresGerais();
                }
            });
        }

        $('#mensagens-abrir-lembretes').on('click', function(e) { e.preventDefault(); abrirModalLembretes(null); });
        $('#mensagens-lembretes-modal').on('click', '.js-lem-atalho', function() {
            definirAtalhoLembrete($(this).attr('data-modo'));
        });
        $('#mensagens-lem-form').on('submit', function(e) {
            e.preventDefault();
            var mid = parseInt($('#mensagens-lem-msg-id').val() || '0', 10);
            var dt = datetimeLocalParaSql($('#mensagens-lem-data').val());
            var nota = $.trim($('#mensagens-lem-nota').val());
            if (!mid || !dt) {
                avisar('info', 'Informe data e hora', 'Defina quando deseja ser lembrado.');
                return;
            }
            $.ajax({ url: api + '/lembrete_criar', method: 'POST', dataType: 'json', data: { mensagem_id: mid, nota: nota, lembrar_em: dt } }).done(function(res) {
                if (res && res.ok) {
                    lembretesLista = res.lembretes || [];
                    $('#mensagens-lem-form').prop('hidden', true);
                    renderizarLembretes();
                    atualizarIndicadoresGerais();
                }
            }).fail(function(xhr) { avisar('error', 'Erro', erroDe(xhr)); });
        });

        // 5. Notas privadas por conversa
        function renderizarNotas(itens) {
            var $lista = $('#mensagens-notas-lista').empty();
            if (!itens || !itens.length) {
                $lista.html('<p class="text-muted small">Nenhuma anotação privada para esta conversa ainda.</p>');
                return;
            }
            $.each(itens, function(i, n) {
                $lista.append($('<div class="mensagens-card-item">').append(
                    $('<div class="mensagens-card-topo">').append(
                        $('<strong>').html('<i class="fa fa-lock text-muted"></i> Nota privada'),
                        $('<small class="text-muted">').text(n.data || '')
                    ),
                    $('<p class="mensagens-card-texto">').text(n.texto),
                    $('<div class="mensagens-card-acoes">').append(
                        $('<button type="button" class="btn btn-default btn-xs">').html('<i class="fa fa-pencil"></i> Editar').on('click', function() {
                            $('#mensagens-nota-id').val(n.id);
                            $('#mensagens-nota-texto').val(n.texto).trigger('focus');
                        }),
                        $('<button type="button" class="btn btn-default btn-xs text-danger">').html('<i class="fa fa-trash-o"></i> Excluir').on('click', function() {
                            $.ajax({ url: api + '/nota_excluir', method: 'POST', dataType: 'json', data: { id: n.id } }).done(function(res) {
                                if (res && res.ok) { carregarNotasModal(); }
                            });
                        })
                    )
                ));
            });
        }

        function carregarNotasModal() {
            if (!atual) { return; }
            $.getJSON(api + '/notas/' + atual.chave).done(function(res) {
                if (res && res.ok) { renderizarNotas(res.itens || []); }
            });
        }

        $('#mensagens-notas-btn').on('click', function() {
            if (!atual) { return; }
            $('#mensagens-notas-sub').text('Anotações sobre: ' + atual.nome);
            $('#mensagens-nota-id').val('');
            $('#mensagens-nota-texto').val('');
            $('#mensagens-notas-modal').modal('show');
            carregarNotasModal();
        });
        $('#mensagens-nota-limpar').on('click', function() {
            $('#mensagens-nota-id').val('');
            $('#mensagens-nota-texto').val('');
        });
        $('#mensagens-notas-form').on('submit', function(e) {
            e.preventDefault();
            if (!atual) { return; }
            var txt = $.trim($('#mensagens-nota-texto').val());
            if (!txt) { return; }
            $.ajax({
                url: api + '/nota_salvar',
                method: 'POST',
                dataType: 'json',
                data: { para: atual.chave, id: $('#mensagens-nota-id').val() || '', texto: txt }
            }).done(function(res) {
                if (res && res.ok) {
                    $('#mensagens-nota-id').val('');
                    $('#mensagens-nota-texto').val('');
                    renderizarNotas(res.itens || []);
                }
            }).fail(function(xhr) { avisar('error', 'Erro', erroDe(xhr)); });
        });

        // 6. Transformar mensagem em chamado
        function abrirModalChamado(m) {
            if (!m) { return; }
            $('#mensagens-ch-msg-id').val(m.id);
            var previa = $.trim(m.texto || (m.anexo ? m.anexo.nome : 'Mensagem do chat'));
            $('#mensagens-ch-titulo-inp').val(previa.slice(0, 120));
            $('#mensagens-ch-desc').val((m.autor ? m.autor + ' (' + (m.data || '') + '):\n' : '') + previa);
            $('#mensagens-ch-prazo').val('');
            $('#mensagens-chamado-modal').modal('show');
        }

        $('#mensagens-chamado-form').on('submit', function(e) {
            e.preventDefault();
            var mid = parseInt($('#mensagens-ch-msg-id').val() || '0', 10);
            var titulo = $.trim($('#mensagens-ch-titulo-inp').val());
            if (!mid || !titulo) {
                avisar('info', 'Título obrigatório', 'Informe o título do chamado.');
                return;
            }
            $('#mensagens-ch-salvar').prop('disabled', true);
            $.ajax({
                url: api + '/criar_chamado',
                method: 'POST',
                dataType: 'json',
                data: {
                    mensagem_id: mid,
                    titulo: titulo,
                    unidade_id: $('#mensagens-ch-unidade').val() || '',
                    responsavel_id: $('#mensagens-ch-responsavel').val() || '',
                    status_id: $('#mensagens-ch-status').val() || '',
                    prioridade_id: $('#mensagens-ch-prioridade').val() || '',
                    prazo: $('#mensagens-ch-prazo').val() || '',
                    descricao: $('#mensagens-ch-desc').val() || ''
                }
            }).done(function(res) {
                if (res && res.ok && res.chamado) {
                    $('#mensagens-chamado-modal').modal('hide');
                    if (mensagensMapa[mid]) {
                        mensagensMapa[mid].chamado = res.chamado;
                        atualizarBolhaExistente(mensagensMapa[mid]);
                    }
                    avisar('success', 'Chamado #' + (res.chamado.protocolo || res.chamado.id) + ' criado', res.chamado.titulo);
                }
            }).fail(function(xhr) {
                avisar('error', 'Não foi possível criar o chamado', erroDe(xhr));
            }).always(function() { $('#mensagens-ch-salvar').prop('disabled', false); });
        });

        // 7. Status de disponibilidade personalizado
        $('#mensagens-meu-status-btn').on('click', function() {
            $('#mensagens-st-select').val((meuStatus && meuStatus.status) || 'online');
            $('#mensagens-st-texto').val((meuStatus && meuStatus.texto) || '');
            $('#mensagens-status-modal').modal('show');
        });
        $('#mensagens-st-salvar').on('click', function() {
            var st = $('#mensagens-st-select').val() || 'online';
            var txt = $.trim($('#mensagens-st-texto').val());
            $.ajax({ url: api + '/status_usuario', method: 'POST', dataType: 'json', data: { status: st, texto: txt } }).done(function(res) {
                if (res && res.ok && res.status) {
                    meuStatus = res.status;
                    atualizarIndicadoresGerais();
                    $('#mensagens-status-modal').modal('hide');
                }
            }).fail(function(xhr) { avisar('error', 'Erro', erroDe(xhr)); });
        });

        // 8. Caixa de mídias
        var tipoMidiaAtual = '';
        function carregarMidias() {
            if (!atual) { return; }
            var $corpo = $('#mensagens-midias-corpo').html('<p class="text-muted"><i class="fa fa-spinner fa-spin"></i> Carregando mídias...</p>');
            $.getJSON(api + '/midias/' + atual.chave, { tipo: tipoMidiaAtual }).done(function(res) {
                $corpo.empty();
                var itens = (res && res.itens) || [];
                if (!itens.length) {
                    $corpo.html('<p class="text-muted">Nenhum arquivo ou mídia encontrado nesta categoria.</p>');
                    return;
                }
                var $grid = $('<div class="mensagens-midias-grid">');
                $.each(itens, function(i, item) {
                    // midias_conversa devolve os dados do anexo no próprio item (url, baixar, nome, tipo) e o id da mensagem em id.
                    var a = item.anexo || item;
                    var $card = $('<div class="mensagens-midia-card">');
                    if (a.tipo === 'imagem') {
                        $card.append($('<a target="_blank" rel="noopener">').attr('href', a.url).append($('<img>').attr({ src: a.url, alt: a.nome })));
                    } else if (a.tipo === 'video') {
                        $card.append($('<video controls preload="metadata">').attr('src', a.url));
                    } else if (a.tipo === 'audio') {
                        $card.addClass('is-audio').append($('<audio class="mensagens-midia-audio" controls preload="metadata">').attr('src', a.url));
                    } else {
                        $card.append($('<a class="mensagens-midia-doc" download>').attr('href', a.baixar).append('<i class="fa fa-file-o fa-2x"></i>', $('<span>').text(a.nome)));
                    }
                    $card.append(
                        $('<div class="mensagens-midia-meta">').append(
                            $('<strong>').attr('title', a.nome || '').text(a.nome || 'Arquivo'),
                            $('<small class="text-muted">').text((item.autor ? item.autor + ' · ' : '') + (item.data || '')),
                            $('<div class="mensagens-midia-acoes">').append(
                                $('<a class="btn btn-default btn-xs" download>').attr('href', a.baixar).html('<i class="fa fa-download"></i>'),
                                $('<button type="button" class="btn btn-default btn-xs">').text('Ir para msg').on('click', function() {
                                    $('#mensagens-midias-modal').modal('hide');
                                    irParaMensagem(item.mensagem_id || item.id);
                                })
                            )
                        )
                    );
                    $grid.append($card);
                });
                $corpo.append($grid);
            }).fail(function(xhr) {
                $corpo.html($('<p class="text-danger">').text(erroDe(xhr)));
            });
        }

        $('#mensagens-midias-btn').on('click', function() {
            if (!atual) { return; }
            tipoMidiaAtual = '';
            $('#mensagens-midias-tabs li').removeClass('active').first().addClass('active');
            $('#mensagens-midias-sub').text('Conversa: ' + atual.nome);
            $('#mensagens-midias-modal').modal('show');
            carregarMidias();
        });
        $('#mensagens-midias-tabs').on('click', 'a[data-tipo]', function(e) {
            e.preventDefault();
            tipoMidiaAtual = $(this).attr('data-tipo') || '';
            $('#mensagens-midias-tabs li').removeClass('active');
            $(this).closest('li').addClass('active');
            carregarMidias();
        });

        // 9. Criação e gestão de Canais de mensagens
        var escritoresCanal = [];

        function nomeEscritorCanal(id) {
            if (id === meuId) { return (meuNome || 'Você') + ' (Você)'; }
            var p = pessoaPorId(id);
            return p ? p.nome : ('Usuário #' + id);
        }

        function renderizarEscritoresCanal() {
            var $tags = $('#mensagens-canal-escritores-tags').empty();
            $.each(escritoresCanal, function(i, id) {
                $tags.append($('<li class="mensagens-escolhido">').append(
                    $('<span>').text(nomeEscritorCanal(id)),
                    $('<button type="button" class="mensagens-escolhido-remover js-remover-escritor-canal" aria-label="Remover autorização">&times;</button>').attr('data-id', id)
                ));
            });

            var termo = semAcento($.trim($('#mensagens-canal-busca-escritor').val()));
            var candidatos = [{ id: meuId, nome: (meuNome || 'Você') + ' (Você)', busca: meuNome || 'Você' }];
            $.each($.grep(pessoas, function(p) { return !p.is_bot; }).sort(function(a, b) { return a.nome.localeCompare(b.nome); }), function(i, p) {
                candidatos.push({ id: p.id, nome: p.nome, busca: p.nome });
            });
            var filtrados = $.grep(candidatos, function(c) {
                return !termo || semAcento(c.busca).indexOf(termo) !== -1;
            });

            var $eBox = $('#mensagens-canal-escritores').empty();
            if (!filtrados.length) {
                $eBox.html('<p class="text-muted small" style="margin:4px 0;">Nenhum usuário encontrado.</p>');
                return;
            }
            $.each(filtrados, function(i, u) {
                var chk = $.inArray(u.id, escritoresCanal) !== -1;
                $eBox.append(
                    $('<label class="mensagens-check-item">').append(
                        $('<input type="checkbox" class="js-canal-escritor">').val(u.id).prop('checked', chk),
                        $('<span>').text(u.nome)
                    )
                );
            });
        }

        function renderizarCheckboxesCanal(gruposMarcados, escritoresMarcados) {
            var $gBox = $('#mensagens-canal-grupos').empty();
            if (!gruposEmpresa || !gruposEmpresa.length) {
                $gBox.html('<p class="text-muted small">Nenhum grupo de usuário encontrado.</p>');
            } else {
                $.each(gruposEmpresa, function(i, g) {
                    var chk = $.inArray(g.id, gruposMarcados) !== -1;
                    $gBox.append(
                        $('<label class="mensagens-check-item">').append(
                            $('<input type="checkbox" class="js-canal-grupo">').val(g.id).prop('checked', chk),
                            $('<span>').text(g.description ? g.name + ' (' + g.description + ')' : g.name)
                        )
                    );
                });
            }
            escritoresCanal = $.map(escritoresMarcados && escritoresMarcados.length ? escritoresMarcados : [meuId], function(id) {
                return parseInt(id, 10);
            });
            $('#mensagens-canal-busca-escritor').val('');
            renderizarEscritoresCanal();
        }

        $('#mensagens-canal-busca-escritor').on('input', renderizarEscritoresCanal);
        $('#mensagens-canal-escritores').on('change', '.js-canal-escritor', function() {
            var id = parseInt($(this).val(), 10);
            var idx = $.inArray(id, escritoresCanal);
            if ($(this).is(':checked')) {
                if (idx === -1) { escritoresCanal.push(id); }
            } else if (idx !== -1) {
                escritoresCanal.splice(idx, 1);
            }
            renderizarEscritoresCanal();
        });
        $('#mensagens-canal-escritores-tags').on('click', '.js-remover-escritor-canal', function() {
            var id = parseInt($(this).attr('data-id'), 10);
            var idx = $.inArray(id, escritoresCanal);
            if (idx !== -1) { escritoresCanal.splice(idx, 1); }
            renderizarEscritoresCanal();
        });

        function abrirModalCanal(editar) {
            if (!canaisAtivos) {
                avisar('info', 'Canais indisponíveis', 'A estrutura de canais ainda não foi criada no banco.');
                return;
            }
            if (editar && atual && atual.tipo === 'canal') {
                $('#mensagens-canal-titulo').html('<i class="fa fa-bullhorn"></i> Configurar canal');
                $('#mensagens-canal-id').val(atual.id);
                $('#mensagens-canal-nome').val(atual.nome || '');
                $('#mensagens-canal-desc').val(atual.descricao || '');
                renderizarCheckboxesCanal(atual.grupo_ids || [], atual.escritor_ids || []);
                $('#mensagens-canal-excluir').prop('hidden', false);
            } else {
                $('#mensagens-canal-titulo').html('<i class="fa fa-bullhorn"></i> Novo canal de mensagens');
                $('#mensagens-canal-id').val('');
                $('#mensagens-canal-nome').val('');
                $('#mensagens-canal-desc').val('');
                renderizarCheckboxesCanal([], [meuId]);
                $('#mensagens-canal-excluir').prop('hidden', true);
            }
            $('#mensagens-canal-modal').modal('show');
        }

        $app.on('click', '.js-abrir-novo-canal', function(e) { e.preventDefault(); abrirModalCanal(false); });
        $('#mensagens-canal-config-btn').on('click', function() { abrirModalCanal(true); });

        $('#mensagens-canal-salvar').on('click', function() {
            var id = parseInt($('#mensagens-canal-id').val() || '0', 10);
            var nome = $.trim($('#mensagens-canal-nome').val());
            var desc = $.trim($('#mensagens-canal-desc').val());
            var grupos = $.map($('#mensagens-canal-grupos .js-canal-grupo:checked'), function(el) { return parseInt($(el).val(), 10); });
            var escritores = escritoresCanal.slice();
            if (!nome || !grupos.length) {
                avisar('info', 'Dados incompletos', 'Informe o nome do canal e selecione pelo menos um grupo com permissão para visualizar.');
                return;
            }
            var endpoint = id ? '/canal_atualizar' : '/canal_criar';
            var payload = { canal_id: id, nome: nome, descricao: desc, grupos: grupos, escritores: escritores };
            $('#mensagens-canal-salvar').prop('disabled', true);
            $.ajax({ url: api + endpoint, method: 'POST', dataType: 'json', data: payload }).done(function(res) {
                if (res && res.ok) {
                    $('#mensagens-canal-modal').modal('hide');
                    if (!id && res.conversa) {
                        mostrarConversa(res);
                        empilharHistorico(res.conversa.chave);
                    } else if (atual && atual.tipo === 'canal') {
                        atual.nome = nome;
                        atual.descricao = desc;
                        atual.grupo_ids = grupos;
                        atual.escritor_ids = escritores;
                        atual.pode_enviar = $.inArray(meuId, escritores) !== -1;
                        atualizarTopo();
                    }
                    atualizar();
                }
            }).fail(function(xhr) {
                avisar('error', 'Erro ao salvar canal', erroDe(xhr));
            }).always(function() { $('#mensagens-canal-salvar').prop('disabled', false); });
        });

        $('#mensagens-canal-excluir').on('click', function() {
            var id = parseInt($('#mensagens-canal-id').val() || '0', 10);
            if (!id) { return; }
            confirmar({
                title: 'Excluir este canal?',
                text: 'Todas as mensagens deste canal deixarão de estar disponíveis.',
                confirmButtonText: 'Sim, excluir canal',
                confirmButtonColor: '#dd4b39'
            }).then(function(ok) {
                if (!ok) { return; }
                $.ajax({ url: api + '/canal_excluir', method: 'POST', dataType: 'json', data: { canal_id: id } }).done(function(res) {
                    if (res && res.ok) {
                        $('#mensagens-canal-modal').modal('hide');
                        sairDaConversa('c' + id);
                        atualizar();
                    }
                }).fail(function(xhr) { avisar('error', 'Erro', erroDe(xhr)); });
            });
        });

        // 10. Chamada por voz e Compartilhamento de tela (WebRTC Mesh)
        function atualizarLayoutChamada() {
            var emChamada = !!chamadaAtiva;
            var palcoVisivel = emChamada && !$('#mensagens-chamada-palco').prop('hidden');
            $app.toggleClass('em-chamada', emChamada);
            $app.toggleClass('em-compartilhamento', palcoVisivel && !$app.hasClass('is-palco-compacto'));
            if (!palcoVisivel) {
                $app.removeClass('is-palco-compacto');
            }
            var compacto = $app.hasClass('is-palco-compacto');
            $('#mensagens-chamada-fullscreen')
                .attr('title', compacto ? 'Expandir tela ao lado das mensagens' : 'Recolher tela para modo compacto')
                .html(compacto ? '<i class="fa fa-expand"></i> <span>Expandir tela</span>' : '<i class="fa fa-compress"></i> <span>Recolher</span>');
            setTimeout(manterNoFim, 60);
        }

        function renderizarChamadaBanner() {
            var $b = $('#mensagens-chamada-banner');
            if (!$b.length) { return; }
            if (!atual || !chamadaConversaAtiva || (chamadaAtiva && (chamadaAtiva.conversaChave === atual.chave || chamadaAtiva.id === chamadaConversaAtiva.id))) {
                $b.prop('hidden', true);
                return;
            }
            var qtd = chamadaConversaAtiva.participantes ? chamadaConversaAtiva.participantes.length : 1;
            $('#mensagens-chamada-banner-txt').text('Chamada em andamento (' + qtd + ' conectado' + (qtd === 1 ? '' : 's') + ')');
            $b.prop('hidden', false);
        }

        function pararDetectorFala() {
            if (chamadaAtiva && chamadaAtiva.detectorTimer) { clearInterval(chamadaAtiva.detectorTimer); }
            if (chamadaAtiva && chamadaAtiva.audioCtx) {
                try { chamadaAtiva.audioCtx.close(); } catch (e) {}
            }
        }

        function iniciarDetectorFala(stream) {
            pararDetectorFala();
            var Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx || !stream || !stream.getAudioTracks().length) { return; }
            try {
                var ctx = new Ctx();
                var src = ctx.createMediaStreamSource(stream);
                var analyser = ctx.createAnalyser();
                analyser.fftSize = 256;
                src.connect(analyser);
                var data = new Uint8Array(analyser.frequencyBinCount);
                chamadaAtiva.audioCtx = ctx;
                chamadaAtiva.detectorTimer = setInterval(function() {
                    if (!chamadaAtiva || chamadaAtiva.mudo) {
                        if (chamadaAtiva && chamadaAtiva.falando) {
                            chamadaAtiva.falando = false;
                            enviarAtualizacaoChamada();
                        }
                        return;
                    }
                    analyser.getByteFrequencyData(data);
                    var soma = 0;
                    for (var i = 0; i < data.length; i++) { soma += data[i]; }
                    var media = soma / data.length;
                    var falandoAgora = media > 18;
                    if (falandoAgora !== !!chamadaAtiva.falando) {
                        chamadaAtiva.falando = falandoAgora;
                        enviarAtualizacaoChamada();
                    }
                }, 400);
            } catch (e) {}
        }

        function enviarAtualizacaoChamada() {
            if (!chamadaAtiva || !chamadaAtiva.id) { return; }
            $.ajax({
                url: api + '/chamada_atualizar',
                method: 'POST',
                dataType: 'json',
                global: false,
                data: {
                    chamada_id: chamadaAtiva.id,
                    mudo: chamadaAtiva.mudo ? '1' : '0',
                    tela: chamadaAtiva.compartilhandoTela ? '1' : '0',
                    falando: chamadaAtiva.falando ? '1' : '0'
                }
            });
        }

        function enviarSinalChamada(paraId, tipo, dados) {
            if (!chamadaAtiva || !chamadaAtiva.id) { return; }
            $.ajax({
                url: api + '/chamada_sinal',
                method: 'POST',
                dataType: 'json',
                global: false,
                data: { chamada_id: chamadaAtiva.id, para_id: paraId, tipo: tipo, dados: JSON.stringify(dados) }
            });
        }

        function anexarTracksLocaisAoPeer(pc) {
            var senders = pc.getSenders ? pc.getSenders() : [];
            var tracksExistentes = $.map(senders, function(s) { return s.track; });
            if (chamadaAtiva && chamadaAtiva.micStream) {
                $.each(chamadaAtiva.micStream.getTracks(), function(i, t) {
                    if ($.inArray(t, tracksExistentes) === -1) {
                        pc.addTrack(t, chamadaAtiva.micStream);
                    }
                });
            }
            if (chamadaAtiva && chamadaAtiva.screenStream) {
                $.each(chamadaAtiva.screenStream.getTracks(), function(i, t) {
                    if ($.inArray(t, tracksExistentes) === -1) {
                        pc.addTrack(t, chamadaAtiva.screenStream);
                    }
                });
            }
        }

        function obterOuCriarPeer(peerId, iniciarOferta) {
            if (!chamadaAtiva) { return null; }
            if (chamadaAtiva.peers[peerId]) { return chamadaAtiva.peers[peerId]; }
            var RTCPC = window.RTCPeerConnection || window.webkitRTCPeerConnection;
            if (!RTCPC) { return null; }
            var pc = new RTCPC({ iceServers: [{ urls: 'stun:stun.l.google.com:19302' }] });
            chamadaAtiva.peers[peerId] = pc;
            anexarTracksLocaisAoPeer(pc);

            pc.onicecandidate = function(ev) {
                if (ev.candidate) { enviarSinalChamada(peerId, 'ice', ev.candidate); }
            };
            pc.ontrack = function(ev) {
                var stream = (ev.streams && ev.streams[0]) ? ev.streams[0] : new window.MediaStream([ev.track]);
                if (ev.track.kind === 'video') {
                    var videoEl = document.getElementById('mensagens-chamada-video');
                    if (videoEl) {
                        videoEl.srcObject = stream;
                        if (typeof videoEl.play === 'function') {
                            videoEl.play().catch(function() {});
                        }
                        var part = $.grep(chamadaAtiva.participantes || [], function(p) { return p.user_id === peerId; })[0];
                        $('#mensagens-chamada-palco-autor').empty().append('<i class="fa fa-desktop"></i> ', document.createTextNode(part ? part.nome + ' está compartilhando a tela' : 'Tela compartilhada'));
                        $app.removeClass('is-palco-compacto');
                        $('#mensagens-chamada-palco').prop('hidden', false);
                        atualizarLayoutChamada();
                    }
                    ev.track.onended = function() {
                        var vEl = document.getElementById('mensagens-chamada-video');
                        if (vEl && vEl.srcObject === stream) {
                            vEl.srcObject = null;
                            $('#mensagens-chamada-palco').prop('hidden', true);
                            atualizarLayoutChamada();
                        }
                    };
                } else {
                    var $audios = $('#mensagens-chamada-audios');
                    var $audioEl = $audios.find('audio[data-peer="' + peerId + '"]');
                    if (!$audioEl.length) {
                        $audioEl = $('<audio>').prop('autoplay', true).attr('data-peer', peerId);
                        $audios.append($audioEl);
                    }
                    $audioEl[0].srcObject = stream;
                    if (typeof $audioEl[0].play === 'function') {
                        $audioEl[0].play().catch(function() {});
                    }
                }
            };

            if (iniciarOferta) {
                pc.createOffer().then(function(offer) {
                    return pc.setLocalDescription(offer);
                }).then(function() {
                    enviarSinalChamada(peerId, 'offer', pc.localDescription);
                }).catch(function() {});
            }
            return pc;
        }

        function renegociarTodosPeers() {
            if (!chamadaAtiva) { return; }
            $.each(chamadaAtiva.peers, function(peerIdStr, pc) {
                var peerId = parseInt(peerIdStr, 10);
                anexarTracksLocaisAoPeer(pc);
                pc.createOffer().then(function(offer) {
                    return pc.setLocalDescription(offer);
                }).then(function() {
                    enviarSinalChamada(peerId, 'offer', pc.localDescription);
                }).catch(function() {});
            });
        }

        function renderizarParticipantesChamada() {
            if (!chamadaAtiva) { return; }
            var $ul = $('#mensagens-chamada-participantes').empty();
            var algumVideo = false;
            $.each(chamadaAtiva.participantes || [], function(i, p) {
                if (p.tela) { algumVideo = true; }
                $ul.append(
                    $('<li class="mensagens-chamada-part">')
                        .toggleClass('is-falando', !!p.falando && !p.mudo)
                        .append(
                            avatar({ foto: p.foto }),
                            $('<span>').text(p.user_id === meuId ? 'Você' : p.nome),
                            p.mudo ? '<i class="fa fa-microphone-slash text-danger" title="Silenciado"></i>' : '<i class="fa fa-microphone text-success"></i>',
                            p.tela ? '<i class="fa fa-desktop text-aqua" title="Compartilhando tela"></i>' : null
                        )
                );
            });
            if (!algumVideo && !chamadaAtiva.compartilhandoTela) {
                $('#mensagens-chamada-palco').prop('hidden', true);
            }
            atualizarLayoutChamada();
        }

        function processarSinaisChamada(sinais) {
            if (!chamadaAtiva || !sinais || !sinais.length) { return; }
            // Só aceita sinais de quem está na chamada (a lista é atualizada antes, em sincronizarEstadoChamada).
            var participantesIds = $.map(chamadaAtiva.participantes || [], function(p) { return p.user_id; });
            $.each(sinais, function(i, s) {
                chamadaAtiva.ultimoSinalId = Math.max(chamadaAtiva.ultimoSinalId || 0, s.id);
                if (s.de_id === meuId || $.inArray(s.de_id, participantesIds) === -1) { return; }
                var pc = obterOuCriarPeer(s.de_id, false);
                if (!pc) { return; }
                var dados = s.dados;
                if (typeof dados === 'string') {
                    try { dados = JSON.parse(dados); } catch (e) { return; }
                }
                if (!dados || typeof dados !== 'object') { return; }
                if (s.tipo === 'offer') {
                    pc.setRemoteDescription(new window.RTCSessionDescription(dados)).then(function() {
                        return pc.createAnswer();
                    }).then(function(ans) {
                        return pc.setLocalDescription(ans);
                    }).then(function() {
                        enviarSinalChamada(s.de_id, 'answer', pc.localDescription);
                    }).catch(function() {});
                } else if (s.tipo === 'answer') {
                    pc.setRemoteDescription(new window.RTCSessionDescription(dados)).catch(function() {});
                } else if (s.tipo === 'ice') {
                    pc.addIceCandidate(new window.RTCIceCandidate(dados)).catch(function() {});
                }
            });
        }

        function atualizarTituloChamada() {
            if (!chamadaAtiva) { return; }
            var qtd = (chamadaAtiva.participantes || []).length;
            var nome = chamadaAtiva.conversaNome || 'conversa';
            $('#mensagens-chamada-titulo').text(qtd <= 1 ? ('Chamando ' + nome + '…') : ('Chamada com ' + nome));
            $('#mensagens-chamada-sub').text(qtd <= 1 ? 'Aguardando resposta…' : (qtd + ' participantes conectados'));
        }

        function sincronizarEstadoChamada() {
            if (!chamadaAtiva || !chamadaAtiva.id) { return; }
            $.getJSON(api + '/chamada_estado/' + chamadaAtiva.id, { depois: chamadaAtiva.ultimoSinalId || 0 }).done(function(res) {
                if (!chamadaAtiva || !res || !res.ok || !res.chamada) { return; }
                var listaPart = res.chamada.participantes || [];
                if (listaPart.length >= 2) {
                    chamadaAtiva.jaTeveOutro = true;
                }
                if (res.chamada.status !== 'ativa' || (chamadaAtiva.ehDireta && chamadaAtiva.jaTeveOutro && listaPart.length < 2)) {
                    var aguardandoResposta = !chamadaAtiva.jaTeveOutro;
                    encerrarChamadaLocal();
                    setTimeout(atualizar, 250);
                    if (aguardandoResposta) {
                        avisar('info', 'Chamada encerrada', 'A ligação foi recusada ou encerrada.');
                    } else {
                        avisar('info', 'Ligação encerrada', 'A chamada foi desligada.');
                    }
                    return;
                }
                chamadaAtiva.participantes = listaPart;
                atualizarTituloChamada();
                var ativosIds = $.map(chamadaAtiva.participantes, function(p) { return p.user_id; });
                $.each(chamadaAtiva.participantes, function(i, p) {
                    if (p.user_id === meuId) { return; }
                    if (!chamadaAtiva.peers[p.user_id]) {
                        obterOuCriarPeer(p.user_id, meuId < p.user_id);
                    }
                });
                $.each(chamadaAtiva.peers, function(pidStr, pc) {
                    var pid = parseInt(pidStr, 10);
                    if ($.inArray(pid, ativosIds) === -1) {
                        try { pc.close(); } catch (e) {}
                        delete chamadaAtiva.peers[pidStr];
                        $('#mensagens-chamada-audios audio[data-peer="' + pid + '"]').remove();
                    }
                });
                renderizarParticipantesChamada();
                processarSinaisChamada(res.chamada.sinais || []);
            });
        }

        function pararCompartilhamentoTelaLocal() {
            if (!chamadaAtiva) { return; }
            if (chamadaAtiva.screenStream) {
                $.each(chamadaAtiva.screenStream.getTracks(), function(i, t) {
                    try { t.stop(); } catch (e) {}
                    $.each(chamadaAtiva.peers, function(pid, pc) {
                        $.each(pc.getSenders ? pc.getSenders() : [], function(j, sender) {
                            if (sender.track === t) {
                                try { pc.removeTrack(sender); } catch (e2) {}
                            }
                        });
                    });
                });
                chamadaAtiva.screenStream = null;
            }
            chamadaAtiva.compartilhandoTela = false;
            var vEl = document.getElementById('mensagens-chamada-video');
            if (vEl) { vEl.srcObject = null; }
            $('#mensagens-chamada-comp-tela').removeClass('btn-info').addClass('btn-default').html('<i class="fa fa-desktop"></i> <span>Compartilhar tela</span>');
            $('#mensagens-chamada-palco').prop('hidden', true);
            atualizarLayoutChamada();
            enviarAtualizacaoChamada();
            renegociarTodosPeers();
        }

        function ativarCompartilhamentoTela() {
            if (!chamadaAtiva) { return; }
            if (!navigator.mediaDevices || typeof navigator.mediaDevices.getDisplayMedia !== 'function') {
                avisar('error', 'Sem suporte', 'Seu navegador não suporta compartilhamento de tela.');
                return;
            }
            navigator.mediaDevices.getDisplayMedia({ video: true, audio: true }).then(function(screenStream) {
                if (!chamadaAtiva) {
                    $.each(screenStream.getTracks(), function(i, t) { t.stop(); });
                    return;
                }
                chamadaAtiva.screenStream = screenStream;
                chamadaAtiva.compartilhandoTela = true;
                $('#mensagens-chamada-comp-tela').removeClass('btn-default').addClass('btn-info').html('<i class="fa fa-desktop"></i> <span>Parar tela</span>');
                var videoEl = document.getElementById('mensagens-chamada-video');
                if (videoEl) {
                    videoEl.srcObject = screenStream;
                    if (typeof videoEl.play === 'function') { videoEl.play().catch(function() {}); }
                    $('#mensagens-chamada-palco-autor').html('<i class="fa fa-desktop"></i> Você está compartilhando sua tela');
                    $app.removeClass('is-palco-compacto');
                    $('#mensagens-chamada-palco').prop('hidden', false);
                    atualizarLayoutChamada();
                }
                $.each(screenStream.getVideoTracks(), function(i, vt) {
                    vt.onended = function() { pararCompartilhamentoTelaLocal(); };
                });
                enviarAtualizacaoChamada();
                renegociarTodosPeers();
            }).catch(function(err) {
                if (err && err.name !== 'NotAllowedError' && err.name !== 'AbortError') {
                    avisar('error', 'Compartilhamento de tela', 'Não foi possível iniciar o compartilhamento de tela.');
                }
            });
        }

        function encerrarChamadaLocal() {
            pararDetectorFala();
            if (chamadaAtiva) {
                clearInterval(chamadaAtiva.pollTimer);
                if (chamadaAtiva.micStream) {
                    $.each(chamadaAtiva.micStream.getTracks(), function(i, t) { try { t.stop(); } catch (e) {} });
                }
                if (chamadaAtiva.screenStream) {
                    $.each(chamadaAtiva.screenStream.getTracks(), function(i, t) { try { t.stop(); } catch (e) {} });
                }
                $.each(chamadaAtiva.peers || {}, function(k, pc) { try { pc.close(); } catch (e) {} });
            }
            chamadaAtiva = null;
            var videoEl = document.getElementById('mensagens-chamada-video');
            if (videoEl) { videoEl.srcObject = null; }
            $('#mensagens-chamada-painel').prop('hidden', true);
            $('#mensagens-chamada-palco').prop('hidden', true);
            $('#mensagens-chamada-audios').empty();
            $('#mensagens-chamada-participantes').empty();
            $app.removeClass('em-chamada em-compartilhamento is-palco-compacto');
            renderizarChamadaBanner();
            setTimeout(manterNoFim, 60);
        }

        function iniciarOuEntrarChamada() {
            if (!atual || chamadaAtiva) { return; }
            if (window.isSecureContext === false) {
                avisar('error', 'Conexão segura exigida', 'Ligações de voz exigem HTTPS ou acesso via localhost.');
                return;
            }
            if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
                avisar('error', 'Navegador incompatível', 'Seu navegador não possui suporte a áudio WebRTC.');
                return;
            }
            navigator.mediaDevices.getUserMedia({ audio: true }).then(function(micStream) {
                $.ajax({
                    url: api + '/chamada_entrar',
                    method: 'POST',
                    dataType: 'json',
                    data: { para: atual.chave, tipo: 'voz', tela: '0' }
                }).done(function(res) {
                    if (!res || !res.ok || !res.chamada) {
                        $.each(micStream.getTracks(), function(i, t) { t.stop(); });
                        return;
                    }
                    chamadaAtiva = {
                        id: res.chamada.id,
                        chave: res.chamada.chave,
                        conversaChave: atual.chave,
                        conversaNome: atual.nome,
                        ehDireta: atual.tipo === 'direta',
                        jaTeveOutro: (res.chamada.participantes || []).length >= 2,
                        tipo: res.chamada.tipo,
                        micStream: micStream,
                        screenStream: null,
                        mudo: false,
                        compartilhandoTela: false,
                        falando: false,
                        peers: {},
                        participantes: res.chamada.participantes || [],
                        ultimoSinalId: 0,
                        pollTimer: null
                    };
                    atualizarTituloChamada();
                    $('#mensagens-chamada-mudo').html('<i class="fa fa-microphone"></i> <span>Microfone</span>');
                    $('#mensagens-chamada-comp-tela').removeClass('btn-info').addClass('btn-default').html('<i class="fa fa-desktop"></i> <span>Compartilhar tela</span>');
                    $('#mensagens-chamada-painel').prop('hidden', false);
                    atualizarLayoutChamada();
                    renderizarChamadaBanner();
                    renderizarParticipantesChamada();
                    iniciarDetectorFala(micStream);
                    $.each(chamadaAtiva.participantes, function(i, p) {
                        if (p.user_id !== meuId) {
                            obterOuCriarPeer(p.user_id, meuId < p.user_id);
                        }
                    });
                    processarSinaisChamada(res.chamada.sinais || []);
                    chamadaAtiva.pollTimer = setInterval(sincronizarEstadoChamada, 1500);
                }).fail(function(xhr) {
                    $.each(micStream.getTracks(), function(i, t) { t.stop(); });
                    avisar('error', 'Não foi possível iniciar a chamada', erroDe(xhr));
                });
            }).catch(function(err) {
                avisar('error', 'Microfone indisponível', erroMicrofone(err));
            });
        }

        $('#mensagens-ligar-btn, #mensagens-chamada-entrar').on('click', iniciarOuEntrarChamada);
        $('#mensagens-chamada-sair').on('click', function() {
            if (!chamadaAtiva) { return; }
            var id = chamadaAtiva.id;
            encerrarChamadaLocal();
            $.ajax({ url: api + '/chamada_sair', method: 'POST', dataType: 'json', global: false, data: { chamada_id: id } }).always(function() {
                setTimeout(atualizar, 200);
            });
        });
        $('#mensagens-chamada-mudo').on('click', function() {
            if (!chamadaAtiva || !chamadaAtiva.micStream) { return; }
            chamadaAtiva.mudo = !chamadaAtiva.mudo;
            $.each(chamadaAtiva.micStream.getAudioTracks(), function(i, t) { t.enabled = !chamadaAtiva.mudo; });
            $(this).html(chamadaAtiva.mudo ? '<i class="fa fa-microphone-slash text-danger"></i> <span>Silenciado</span>' : '<i class="fa fa-microphone"></i> <span>Microfone</span>');
            if (chamadaAtiva.mudo) { chamadaAtiva.falando = false; }
            enviarAtualizacaoChamada();
            renderizarParticipantesChamada();
        });
        $('#mensagens-chamada-comp-tela').on('click', function() {
            if (!chamadaAtiva) { return; }
            if (chamadaAtiva.compartilhandoTela) { pararCompartilhamentoTelaLocal(); }
            else { ativarCompartilhamentoTela(); }
        });
        $('#mensagens-chamada-fullscreen').on('click', function() {
            if (!chamadaAtiva || $('#mensagens-chamada-palco').prop('hidden')) { return; }
            $app.toggleClass('is-palco-compacto');
            atualizarLayoutChamada();
        });

        // 11. Reações com emojis, Enquetes nos canais e Eventos/Reuniões
        var emojisReacao = ['👍', '❤️', '😂', '😮', '😢', '👏', '🙏', '🔥', '✅', '🎉'];
        var reacaoMsgAlvoId = 0;

        function fecharPopupReacoes() {
            $('#mensagens-reacoes-popup').prop('hidden', true);
            reacaoMsgAlvoId = 0;
        }

        function aplicarReacoesNaBolha(msgId, reacoes) {
            var $bolha = $historico.find('.mensagens-msg[data-id="' + msgId + '"] .mensagens-bolha');
            if (!$bolha.length) { return; }
            $bolha.find('.mensagens-reacoes').remove();
            var m = mensagensMapa[msgId] || { id: msgId };
            m.reacoes = reacoes || [];
            var $nova = renderizarReacoes(m);
            var tem = $nova.children().length > 0;
            if (tem) {
                $bolha.find('.mensagens-bolha-meta').before($nova);
            }
            $bolha.closest('.mensagens-msg').toggleClass('tem-reacoes', tem);
        }

        function enviarReacao(msgId, emoji) {
            if (!msgId || !emoji) { return; }
            $.ajax({ url: api + '/reagir', method: 'POST', dataType: 'json', data: { mensagem_id: msgId, emoji: emoji } }).done(function(res) {
                if (res && res.ok) {
                    aplicarReacoesNaBolha(msgId, res.reacoes || []);
                }
            }).fail(function(xhr) { avisar('error', 'Reação', erroDe(xhr)); });
        }

        function abrirPopupReacoes($btn, msgId) {
            if (!msgId || !$btn.length) { return; }
            var $pop = $('#mensagens-reacoes-popup');
            if (reacaoMsgAlvoId === msgId && !$pop.prop('hidden')) {
                fecharPopupReacoes();
                return;
            }
            reacaoMsgAlvoId = msgId;
            if (!$pop.children().length) {
                $.each(emojisReacao, function(i, em) {
                    $pop.append($('<button type="button" class="mensagens-reacao-opt">').attr('data-emoji', em).text(em));
                });
            }
            $pop.prop('hidden', false);
            var rect = $btn[0].getBoundingClientRect();
            var w = $pop.outerWidth();
            var h = $pop.outerHeight();
            var left = Math.min(window.innerWidth - w - 10, Math.max(10, rect.left - w / 2 + 10));
            var top = (rect.top - h - 6 > 10) ? (rect.top - h - 6) : (rect.bottom + 6);
            $pop.css({ left: left + 'px', top: top + 'px' });
        }

        $historico.on('click', '.js-abrir-reacoes', function(e) {
            e.stopPropagation();
            abrirPopupReacoes($(this), parseInt($(this).attr('data-id'), 10));
        });
        $historico.on('click', '.js-reagir-emoji', function(e) {
            e.stopPropagation();
            enviarReacao(parseInt($(this).attr('data-id'), 10), $(this).attr('data-emoji'));
        });
        $('#mensagens-reacoes-popup').on('click', '.mensagens-reacao-opt', function(e) {
            e.stopPropagation();
            var id = reacaoMsgAlvoId;
            var em = $(this).attr('data-emoji');
            fecharPopupReacoes();
            enviarReacao(id, em);
        });

        // Enquetes nos canais
        function adicionarCampoOpcaoEnquete(valor, removivel) {
            var $wrap = $('<div class="input-group input-group-sm mensagens-eq-input-row">').append(
                $('<input type="text" class="form-control js-eq-opcao-inp" maxlength="150" placeholder="Alternativa">').val(valor || '')
            );
            if (removivel) {
                $wrap.append(
                    $('<span class="input-group-btn">').append(
                        $('<button type="button" class="btn btn-default" title="Remover"><i class="fa fa-times"></i></button>').on('click', function() { $wrap.remove(); })
                    )
                );
            }
            $('#mensagens-eq-opcoes').append($wrap);
        }

        $('#mensagens-enquete-btn').on('click', function() {
            if (!atual || atual.tipo !== 'canal') { return; }
            $('#mensagens-eq-pergunta').val('');
            $('#mensagens-eq-anonima, #mensagens-eq-multipla').prop('checked', false);
            $('#mensagens-eq-prazo').val('');
            $('#mensagens-eq-opcoes').empty();
            adicionarCampoOpcaoEnquete('', false);
            adicionarCampoOpcaoEnquete('', false);
            $('#mensagens-enquete-modal').modal('show');
            setTimeout(function() { $('#mensagens-eq-pergunta').trigger('focus'); }, 300);
        });

        $('#mensagens-eq-add-op').on('click', function() {
            if ($('#mensagens-eq-opcoes .js-eq-opcao-inp').length >= 10) {
                avisar('info', 'Limite de opções', 'A enquete pode ter no máximo 10 alternativas.');
                return;
            }
            adicionarCampoOpcaoEnquete('', true);
        });

        $('#mensagens-enquete-form').on('submit', function(e) {
            e.preventDefault();
            if (!atual || atual.tipo !== 'canal') { return; }
            var pergunta = $.trim($('#mensagens-eq-pergunta').val());
            var opcoes = [];
            $('#mensagens-eq-opcoes .js-eq-opcao-inp').each(function() {
                var v = $.trim($(this).val());
                if (v) { opcoes.push(v); }
            });
            if (!pergunta || opcoes.length < 2) {
                avisar('info', 'Dados incompletos', 'Informe a pergunta e pelo menos 2 alternativas.');
                return;
            }
            $('#mensagens-eq-salvar').prop('disabled', true);
            $.ajax({
                url: api + '/enquete_criar',
                method: 'POST',
                dataType: 'json',
                data: {
                    para: atual.chave,
                    pergunta: pergunta,
                    opcoes: opcoes,
                    anonima: $('#mensagens-eq-anonima').is(':checked') ? '1' : '0',
                    multipla: $('#mensagens-eq-multipla').is(':checked') ? '1' : '0',
                    encerrar_em: datetimeLocalParaSql($('#mensagens-eq-prazo').val())
                }
            }).done(function(res) {
                if (res && res.ok && res.mensagem) {
                    $('#mensagens-enquete-modal').modal('hide');
                    registrarEnviada(res.mensagem);
                }
            }).fail(function(xhr) {
                avisar('error', 'Não foi possível criar a enquete', erroDe(xhr));
            }).always(function() { $('#mensagens-eq-salvar').prop('disabled', false); });
        });

        $historico.on('click', '.js-eq-votar', function() {
            var eqId = parseInt($(this).attr('data-enquete-id'), 10);
            var opId = parseInt($(this).attr('data-opcao-id'), 10);
            if (!eqId || !opId) { return; }
            $.ajax({ url: api + '/enquete_votar', method: 'POST', dataType: 'json', data: { enquete_id: eqId, opcao_id: opId } }).done(function(res) {
                if (res && res.ok && res.enquete) {
                    var mid = res.enquete.mensagem_id;
                    if (mensagensMapa[mid]) { mensagensMapa[mid].enquete = res.enquete; }
                    $historico.find('.mensagens-msg[data-id="' + mid + '"] .mensagens-enquete-card').replaceWith(renderizarEnqueteCard(res.enquete));
                }
            }).fail(function(xhr) { avisar('info', 'Votação', erroDe(xhr)); });
        });

        // Reuniões e eventos nos canais
        $('#mensagens-reuniao-btn').on('click', function() {
            if (!atual || atual.tipo !== 'canal') { return; }
            $('#mensagens-reu-titulo-inp, #mensagens-reu-desc').val('');
            $('#mensagens-reu-inicio').val(formatarDataInputLocal(new Date(Date.now() + 60 * 60000)));
            var $box = $('#mensagens-reu-participantes').empty();
            var membros = (membrosAtual || []).slice().sort(function(a, b) { return a.nome.localeCompare(b.nome); });
            if (!membros.length) {
                $box.html('<p class="text-muted small">Todos os membros deste canal serão convidados.</p>');
            } else {
                $.each(membros, function(i, mb) {
                    if (mb.is_bot) { return; }
                    $box.append(
                        $('<label class="mensagens-check-item">').append(
                            $('<input type="checkbox" class="js-reu-part" checked>').val(mb.id),
                            $('<span>').text(mb.id === meuId ? mb.nome + ' (Você)' : mb.nome)
                        )
                    );
                });
            }
            $('#mensagens-reuniao-modal').modal('show');
            setTimeout(function() { $('#mensagens-reu-titulo-inp').trigger('focus'); }, 300);
        });

        $('#mensagens-reuniao-form').on('submit', function(e) {
            e.preventDefault();
            if (!atual || atual.tipo !== 'canal') { return; }
            var titulo = $.trim($('#mensagens-reu-titulo-inp').val());
            var inicio = datetimeLocalParaSql($('#mensagens-reu-inicio').val());
            if (!titulo || !inicio) {
                avisar('info', 'Campos obrigatórios', 'Informe o assunto e a data/horário da reunião.');
                return;
            }
            var parts = $.map($('#mensagens-reu-participantes .js-reu-part:checked'), function(el) { return parseInt($(el).val(), 10); });
            $('#mensagens-reu-salvar').prop('disabled', true);
            $.ajax({
                url: api + '/reuniao_criar',
                method: 'POST',
                dataType: 'json',
                data: {
                    para: atual.chave,
                    titulo: titulo,
                    descricao: $('#mensagens-reu-desc').val() || '',
                    inicio_em: inicio,
                    participantes: parts
                }
            }).done(function(res) {
                if (res && res.ok && res.mensagem) {
                    $('#mensagens-reuniao-modal').modal('hide');
                    registrarEnviada(res.mensagem);
                }
            }).fail(function(xhr) {
                avisar('error', 'Não foi possível agendar a reunião', erroDe(xhr));
            }).always(function() { $('#mensagens-reu-salvar').prop('disabled', false); });
        });

        $historico.on('click', '.js-reu-responder', function() {
            var reuId = parseInt($(this).attr('data-reuniao-id'), 10);
            var st = $(this).attr('data-status');
            if (!reuId || !st) { return; }
            $.ajax({ url: api + '/reuniao_responder', method: 'POST', dataType: 'json', data: { reuniao_id: reuId, status: st } }).done(function(res) {
                if (res && res.ok && res.reuniao) {
                    var mid = res.reuniao.mensagem_id;
                    if (mensagensMapa[mid]) { mensagensMapa[mid].reuniao = res.reuniao; }
                    $historico.find('.mensagens-msg[data-id="' + mid + '"] .mensagens-reuniao-card').replaceWith(renderizarReuniaoCard(res.reuniao));
                }
            }).fail(function(xhr) { avisar('error', 'Presença', erroDe(xhr)); });
        });

        $historico.on('click', '.js-reu-chamar', function() {
            var ch = $(this).attr('data-chave');
            if (!atual || atual.chave !== ch) {
                abrir(ch, false, function() { iniciarOuEntrarChamada(); });
            } else {
                iniciarOuEntrarChamada();
            }
        });

        function ajustarAlturaCaixa() {
            var rodape = $('.main-footer').outerHeight() || 0;
            var altura = window.innerHeight - $app.offset().top - rodape - 15;
            $app.css('height', Math.max(460, Math.floor(altura)) + 'px');
            if (tela === 'conversa') { manterNoFim(); }
        }
        ajustarAlturaCaixa();
        $(window).on('resize', ajustarAlturaCaixa);

        window.mensagensTelaAtiva = true;
        window.mensagensAtenderChamada = function(c) {
            if (!c || !c.chave) { return; }
            if (atual && atual.chave === c.chave && tela === 'conversa') {
                iniciarOuEntrarChamada();
            } else {
                abrir(c.chave, false, function() { iniciarOuEntrarChamada(); });
            }
        };

        var paramsIniciais = (window.URLSearchParams && window.location.search) ? new window.URLSearchParams(window.location.search) : null;
        var atenderChamadaInicial = paramsIniciais ? (parseInt(paramsIniciais.get('atender_chamada'), 10) || 0) : 0;

        renderizarListas();
        if (atual) {
            mostrarConversa({
                conversa: atual,
                mensagens: conversaInicial,
                visto_ate: vistoAte,
                entregue_ate: entregueAte,
                fixadas: fixadasAtuais,
                config: configAtual,
                membros: membrosAtual,
                digitando: []
            });
            if (window.history && window.history.replaceState) {
                window.history.replaceState({ conversa: atual.chave }, '', urlTela + '/' + atual.chave);
            }
            if (atenderChamadaInicial > 0) {
                setTimeout(iniciarOuEntrarChamada, 250);
            }
        } else {
            irPara('lista');
        }
        setTimeout(function() { $app.removeClass('sem-animacao'); }, 50);
        setTimeout(atualizar, 300);
        setInterval(atualizar, 2500);
    });
</script>
<style>
    .mensagens-app { position: relative; overflow: hidden; height: calc(100vh - 210px); min-height: 460px; margin-bottom: 0; }
    .mensagens-tela { position: absolute; top: 0; right: 0; bottom: 0; left: 0; display: flex; flex-direction: column; min-width: 0; background: #fff; visibility: hidden;
        transition: transform .35s cubic-bezier(.25, .8, .25, 1), opacity .35s ease, visibility 0s linear .35s; }
    .mensagens-tela.is-ativa { transform: none; opacity: 1; visibility: visible; transition: transform .35s cubic-bezier(.25, .8, .25, 1), opacity .35s ease, visibility 0s; z-index: 2; }
    .mensagens-tela.is-esquerda { transform: translateX(-30%); opacity: 0; }
    .mensagens-tela.is-direita { transform: translateX(100%); opacity: 1; z-index: 3; }
    .mensagens-app.sem-animacao .mensagens-tela { transition: none !important; }

    @media (min-width: 768px) {
        .mensagens-tela[data-tela="lista"] { transition: transform .35s cubic-bezier(.25, .8, .25, 1), opacity .35s ease, right .35s cubic-bezier(.25, .8, .25, 1), visibility 0s linear .35s; }
        .mensagens-app[data-tela="conversa"] .mensagens-tela[data-tela="lista"] { right: calc(100% - 340px); transform: none; opacity: 1; visibility: visible; z-index: 2; border-right: 1px solid #d2d6de;
            transition: transform .35s cubic-bezier(.25, .8, .25, 1), opacity .35s ease, right .35s cubic-bezier(.25, .8, .25, 1), visibility 0s; }
        .mensagens-tela[data-tela="conversa"] { left: 340px; }
        .mensagens-app[data-tela="conversa"] .mensagens-tela[data-tela="conversa"] .mensagens-voltar { display: none; }
    }
    .mensagens-contato.is-ativo { background: #ecebf3; box-shadow: inset 3px 0 0 #1E1D3F; }

    .mensagens-topo { display: flex; align-items: center; gap: 8px; min-height: 54px; padding: 8px 12px; }
    .mensagens-topo:before, .mensagens-topo:after { display: none !important; }
    .mensagens-topo .box-title { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .mensagens-topo-acoes { display: flex; align-items: center; gap: 6px; flex: 0 0 auto; }
    .mensagens-icon-btn { position: relative; width: 30px; height: 30px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; }
    .mensagens-ponto-alerta { position: absolute; top: 4px; right: 4px; width: 7px; height: 7px; border-radius: 50%; background: #dd4b39; border: 1px solid #fff; }
    .mensagens-ponto-alerta[hidden], .mensagens-topo-badge[hidden], .mensagens-fixadas-badge[hidden],
    #mensagens-app .mensagens-topo [hidden], #mensagens-app .mensagens-envio [hidden] { display: none !important; }
    .mensagens-topo-dropdown > li > a { display: flex; align-items: center; gap: 8px; padding: 6px 14px; font-size: 13px; }
    .mensagens-topo-dropdown .mensagens-topo-badge { margin-left: auto; background: #1E1D3F; }
    .mensagens-nova-btn { flex: 0 0 auto; border-radius: 4px; height: 30px; display: inline-flex; align-items: center; gap: 5px; }
    .mensagens-app[data-tela="conversa"] .mensagens-tela[data-tela="lista"] .mensagens-btn-texto { display: none; }
    @media (max-width: 520px) {
        .mensagens-tela[data-tela="lista"] .mensagens-btn-texto { display: none; }
    }
    .mensagens-voltar { padding: 0 6px; color: #555; font-size: 16px; }

    .mensagens-busca { position: relative; padding: 10px; border-bottom: 1px solid #d2d6de; }
    .mensagens-busca .fa { position: absolute; left: 21px; top: 50%; margin-top: -7px; color: #999; }
    .mensagens-busca .form-control { padding-left: 30px; border-radius: 4px; }
    .mensagens-lista { flex: 1; min-height: 0; margin: 0; padding: 0; overflow-y: auto; list-style: none; }
    .mensagens-lista-vazia { padding: 40px 20px; color: #999; text-align: center; }
    .mensagens-lista-vazia > .fa { font-size: 44px; color: #c9d3dd; }
    .mensagens-lista-vazia p { margin: 10px 0 14px; }
    .mensagens-contato { display: flex; align-items: center; gap: 12px; width: 100%; padding: 11px 16px; border: 0; border-bottom: 1px solid #f0f2f4; background: transparent; text-align: left; cursor: pointer; transition: background .15s; }
    .mensagens-contato:hover, .mensagens-contato:focus { background: #f5f8fa; outline: none; }
    .mensagens-avatar { flex: 0 0 40px; width: 40px; height: 40px; object-fit: cover; background: #c9d3dd; }
    .mensagens-contato-corpo { flex: 1; min-width: 0; }
    .mensagens-contato-linha { display: flex; align-items: center; justify-content: space-between; gap: 6px; }
    .mensagens-contato-nome { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 14px; color: #333; }
    .mensagens-contato-quando { flex: 0 0 auto; color: #999; font-size: 11px; }
    .mensagens-contato-ultima { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #888; font-size: 12px; }
    .mensagens-contato-ultima.is-digitando { color: #00a65a; font-style: italic; font-weight: 600; }
    .mensagens-contato.tem-nao-lidas .mensagens-contato-ultima { color: #333; font-weight: 600; }
    .mensagens-contato.tem-nao-lidas .mensagens-contato-quando { color: #00a65a; font-weight: 600; }
    .mensagens-contato-badge { flex: 0 0 auto; background: #00a65a; }
    .mensagens-contato-mencao { flex: 0 0 auto; background: #f39c12; }

    .mensagens-fixada-banner { display: flex; align-items: center; gap: 8px; padding: 6px 12px; border-bottom: 1px solid #d2d6de; background: #fffcf2; }
    .mensagens-fixada-banner[hidden] { display: none; }
    .mensagens-fixada-ir { flex: 1; min-width: 0; display: flex; align-items: center; gap: 8px; padding: 0; border: 0; background: none; text-align: left; cursor: pointer; }
    .mensagens-fixada-ir .fa { color: #f39c12; font-size: 14px; }
    .mensagens-fixada-texto-wrap { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .mensagens-fixada-texto-wrap strong { font-size: 11px; color: #b45309; }
    .mensagens-fixada-texto-wrap span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 12px; color: #444; }
    .mensagens-fixadas-badge { background: #f39c12; font-size: 10px; }
    .mensagens-fixadas-badge[hidden] { display: none; }

    .mensagens-historico { flex: 1; min-height: 0; overflow-y: auto; padding: 14px 16px; background: #f7f9fb; }
    .mensagens-carregar-anteriores { margin-bottom: 10px; text-align: center; }
    .mensagens-sem-historico { margin: 30px 0; color: #999; text-align: center; }
    .mensagens-dia { margin: 12px 0; text-align: center; }
    .mensagens-dia span { display: inline-block; padding: 2px 10px; border-radius: 10px; background: #e4e9ee; color: #667; font-size: 11px; font-weight: 600; }
    .mensagens-msg { display: flex; flex-direction: column; align-items: flex-start; margin-bottom: 6px; transition: background .3s; }
    .mensagens-msg.is-minha { align-items: flex-end; }
    .mensagens-msg.is-destacada .mensagens-bolha { box-shadow: 0 0 0 3px #f39c12; }
    .mensagens-bolha { position: relative; max-width: 75%; min-width: 82px; padding: 7px 26px 6px 10px; border-radius: 10px 10px 10px 2px; background: #fff; box-shadow: 0 1px 1px rgba(0, 0, 0, .08); color: #333; }
    .mensagens-msg.is-minha .mensagens-bolha { border-radius: 10px 10px 2px 10px; background: #1E1D3F; color: #fff; }
    .mensagens-bolha.tem-anexo { padding: 4px 4px 6px; }
    .mensagens-bolha.tem-anexo .mensagens-bolha-texto, .mensagens-bolha.tem-anexo .mensagens-bolha-meta { padding: 0 6px; }
    .mensagens-bolha.is-excluida { opacity: .8; }
    .mensagens-bolha-texto { display: block; white-space: pre-wrap; word-wrap: break-word; overflow-wrap: anywhere; font-size: 14px; line-height: 1.4; }
    .mensagens-bolha-texto.is-excluida { font-style: italic; opacity: .85; }
    .mensagens-bolha.tem-anexo .mensagens-bolha-texto { margin-top: 5px; }

    .mensagens-msg-acoes-btn { position: absolute; top: 3px; right: 4px; width: 20px; height: 20px; padding: 0; border: 0; border-radius: 50%; background: transparent; color: inherit; opacity: 0; cursor: pointer; transition: opacity .15s, background .15s; }
    .mensagens-bolha:hover .mensagens-msg-acoes-btn, .mensagens-msg-acoes-btn:focus { opacity: .85; background: rgba(0, 0, 0, .12); outline: none; }
    @media (hover: none) { .mensagens-msg-acoes-btn { opacity: .65; } }

    .mensagens-encaminhada { display: block; margin-bottom: 3px; font-size: 11px; font-style: italic; opacity: .8; }
    .mensagens-citacao { display: block; width: 100%; margin: 2px 0 6px; padding: 5px 8px; border: 0; border-left: 3px solid #1E1D3F; border-radius: 4px; background: rgba(0, 0, 0, .05); color: inherit; text-align: left; cursor: pointer; }
    .mensagens-msg.is-minha .mensagens-citacao { border-left-color: #fff; background: rgba(255, 255, 255, .16); }
    .mensagens-citacao strong { display: block; font-size: 11px; }
    .mensagens-citacao span { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 12px; opacity: .85; }
    .mensagens-mencao-tag { padding: 0 3px; border-radius: 3px; background: rgba(30, 29, 63, .15); font-weight: 600; }
    .mensagens-mencao-tag.is-eu { background: #fef3c7; color: #92400e; }
    .mensagens-msg.is-minha .mensagens-mencao-tag { background: rgba(255, 255, 255, .22); color: #fff; }

    .mensagens-bolha-meta { display: flex; align-items: center; justify-content: flex-end; gap: 5px; margin-top: 3px; font-size: 10px; opacity: .8; }
    .mensagens-meta-icone.is-fav { color: #f39c12; }
    .mensagens-editada { font-style: italic; }
    .mensagens-ticks { display: inline-flex; align-items: center; padding: 0; border: 0; background: none; color: inherit; font-size: 11px; cursor: pointer; line-height: 1; }
    .mensagens-ticks .fa + .fa { margin-left: -6px; }
    .mensagens-ticks.is-lida { color: #7dd3fc; opacity: 1; }
    .mensagens-msg:not(.is-minha) .mensagens-ticks.is-lida { color: #1E1D3F; }

    .mensagens-menu-msg { position: fixed; z-index: 1060; min-width: 175px; padding: 4px 0; border: 1px solid #d2d6de; border-radius: 6px; background: #fff; box-shadow: 0 6px 18px rgba(0, 0, 0, .16); }
    .mensagens-menu-msg[hidden] { display: none; }
    .mensagens-menu-item { display: flex; align-items: center; gap: 8px; width: 100%; padding: 7px 14px; border: 0; background: transparent; color: #333; font-size: 13px; text-align: left; cursor: pointer; }
    .mensagens-menu-item:hover, .mensagens-menu-item:focus { background: #f3f6f9; outline: none; }
    .mensagens-menu-item[hidden] { display: none; }
    .mensagens-menu-item.is-perigo { color: #dd4b39; }

    .mensagens-anexo-imagem { display: block; border-radius: 8px; overflow: hidden; background: rgba(0, 0, 0, .05); }
    .mensagens-anexo-imagem img { display: block; max-width: 100%; width: 280px; max-height: 280px; object-fit: cover; }
    .mensagens-anexo-midia { display: block; max-width: 100%; width: 320px; max-height: 300px; border-radius: 8px; background: #000; }
    /* Player de áudio nativo: largura definida no invólucro (a bolha não o espreme; ao abrir o volume a barra precisa caber). */
    .mensagens-audio-wrap { width: 300px; max-width: 100%; }
    .mensagens-anexo-audio { display: block; width: 100%; max-width: 100%; }
    .mensagens-anexo-audio:focus, .mensagens-pendente-audio:focus, .mensagens-midia-audio:focus { outline: none; }
    .mensagens-anexo-audio:focus-visible, .mensagens-pendente-audio:focus-visible, .mensagens-midia-audio:focus-visible { outline: 2px solid rgba(30, 29, 63, .6); outline-offset: 2px; }
    /* Calha à direita para os botões 😊/⌄ da bolha não ficarem por cima do volume e do menu do player. */
    .mensagens-bolha.tem-audio .mensagens-anexo-com-baixar > .mensagens-audio-wrap { max-width: calc(100% - 46px); margin-right: 46px; }
    .mensagens-bolha.tem-audio .mensagens-audio-lado { padding-right: 46px; }
    /* Texto: o início da 1ª linha contorna os botões 😊/⌄ em vez de ficar escondido atrás deles. */
    .mensagens-msg-acoes-btn + .mensagens-bolha-texto::before { content: ''; float: right; width: 40px; height: 16px; }
    .mensagens-anexo-arquivo { display: flex; align-items: center; gap: 10px; min-width: 220px; max-width: 320px; padding: 10px; border-radius: 8px; background: rgba(0, 0, 0, .05); color: inherit; text-decoration: none; }
    .mensagens-anexo-arquivo:hover, .mensagens-anexo-arquivo:focus { color: inherit; text-decoration: none; background: rgba(0, 0, 0, .09); }
    .mensagens-msg.is-minha .mensagens-anexo-arquivo { background: rgba(255, 255, 255, .15); }
    .mensagens-msg.is-minha .mensagens-anexo-arquivo:hover { background: rgba(255, 255, 255, .25); }
    .mensagens-anexo-arquivo > .fa:first-child { font-size: 28px; }
    .mensagens-anexo-info { flex: 1; min-width: 0; }
    .mensagens-anexo-info strong { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 13px; }
    .mensagens-anexo-info small { opacity: .75; }

    .mensagens-soltar { display: none; position: absolute; top: 54px; right: 0; bottom: 0; left: 0; z-index: 5; align-items: center; justify-content: center; gap: 10px; border: 3px dashed #1E1D3F; background: rgba(30, 29, 63, .12); color: #1E1D3F; font-size: 18px; font-weight: 600; pointer-events: none; }
    .mensagens-tela.is-arrastando .mensagens-soltar { display: flex; }

    .mensagens-rodape { position: relative; border-top: 1px solid #d2d6de; }
    .mensagens-contexto { display: flex; align-items: center; gap: 10px; padding: 7px 12px; border-bottom: 1px solid #e2e8f0; border-left: 4px solid #1E1D3F; background: #f8f8fb; }
    .mensagens-contexto[hidden] { display: none; }
    .mensagens-contexto-corpo { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .mensagens-contexto-corpo strong { font-size: 12px; color: #1E1D3F; }
    .mensagens-contexto-corpo span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 12px; color: #555; }
    .mensagens-contexto-fechar { padding: 0 6px; border: 0; background: none; color: #888; font-size: 20px; line-height: 1; cursor: pointer; }

    .mensagens-mencoes-popup { position: absolute; bottom: 100%; left: 12px; z-index: 7; min-width: 240px; max-width: 320px; max-height: 220px; overflow-y: auto; margin: 0 0 6px; padding: 4px 0; list-style: none; border: 1px solid #d2d6de; border-radius: 6px; background: #fff; box-shadow: 0 6px 18px rgba(0, 0, 0, .15); }
    .mensagens-mencoes-popup[hidden] { display: none; }
    .mensagens-mencao-item { display: flex; align-items: center; gap: 8px; width: 100%; padding: 6px 12px; border: 0; background: transparent; text-align: left; cursor: pointer; }
    .mensagens-mencao-item .mensagens-avatar, .mensagens-mencao-item .mensagens-avatar-grupo { flex-basis: 28px; width: 28px; height: 28px; font-size: 13px; }
    .mensagens-mencao-item.is-ativa, .mensagens-mencao-item:hover { background: #ecebf3; }

    .mensagens-pendentes { display: flex; flex-wrap: wrap; gap: 8px; margin: 0; padding: 10px 12px 0; list-style: none; }
    .mensagens-pendentes:empty { display: none; }
    .mensagens-pendente { position: relative; display: flex; align-items: center; gap: 8px; max-width: 240px; padding: 5px 28px 5px 6px; border: 1px solid #d2d6de; border-radius: 6px; background: #f7f9fb; font-size: 12px; }
    .mensagens-pendente.is-audio { max-width: 340px; padding-right: 30px; }
    .mensagens-pendente-audio { width: 260px; max-width: 100%; height: 40px; }
    .mensagens-pendente img { width: 34px; height: 34px; border-radius: 4px; object-fit: cover; }
    .mensagens-pendente > .fa { width: 34px; text-align: center; font-size: 20px; color: #777; }
    .mensagens-pendente-nome { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .mensagens-pendente small { flex: 0 0 auto; color: #999; }
    .mensagens-pendente-remover { position: absolute; top: 50%; right: 4px; margin-top: -11px; width: 22px; height: 22px; padding: 0; border: 0; border-radius: 50%; background: transparent; color: #999; font-size: 16px; line-height: 1; }
    .mensagens-pendente-remover:hover, .mensagens-pendente-remover:focus { background: rgba(0, 0, 0, .08); color: #333; outline: none; }

    .mensagens-envio { display: flex; align-items: flex-end; gap: 6px; padding: 10px 12px 4px; }
    .mensagens-envio[hidden], .mensagens-gravando[hidden] { display: none; }
    .mensagens-envio textarea { resize: none; min-height: 38px; max-height: 140px; border-radius: 4px; line-height: 1.4; }
    .mensagens-acao { flex: 0 0 38px; height: 38px; padding: 0; border-radius: 4px; font-size: 16px; }
    .mensagens-gravando { display: flex; align-items: center; gap: 6px; padding: 10px 12px 4px; }
    .mensagens-gravando-status { flex: 1; min-width: 0; display: flex; align-items: center; gap: 8px; height: 38px; padding: 0 12px; border: 1px solid #f5c6cb; border-radius: 4px; background: #fdf2f2; color: #b91c1c; font-size: 13px; }
    .mensagens-gravando-status strong { margin-left: auto; font-variant-numeric: tabular-nums; }
    .mensagens-gravando-ponto { flex: 0 0 10px; width: 10px; height: 10px; border-radius: 50%; background: #dd4b39; animation: mensagens-pulso 1.1s ease-in-out infinite; }
    @keyframes mensagens-pulso { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: .35; transform: scale(.8); } }

    .mensagens-erro { margin: 0; padding: 4px 12px 0; color: #dd4b39; font-size: 12px; }
    .mensagens-erro[hidden], .mensagens-info[hidden] { display: none; }
    .mensagens-info { margin: 0; padding: 4px 12px 0; color: #00a65a; font-size: 12px; }
    .mensagens-anexo-com-baixar { display: flex; flex-direction: column; align-items: flex-start; gap: 4px; }
    .mensagens-msg.is-minha .mensagens-anexo-com-baixar { align-items: flex-end; }
    .mensagens-anexo-baixar-link { display: inline-flex; align-items: center; gap: 5px; padding: 2px 6px; border-radius: 4px; color: inherit; font-size: 12px; opacity: .85; text-decoration: none; }
    .mensagens-anexo-baixar-link:hover, .mensagens-anexo-baixar-link:focus { color: inherit; opacity: 1; text-decoration: none; background: rgba(0, 0, 0, .06); }
    .mensagens-msg.is-minha .mensagens-anexo-baixar-link:hover { background: rgba(255, 255, 255, .15); }
    .mensagens-dica { display: flex; justify-content: space-between; gap: 10px; margin: 0; padding: 0 12px 8px; color: #999; font-size: 11px; }

    #mensagens-arquivo { display: none !important; }
    .mensagens-emojis { position: absolute; bottom: 100%; left: 12px; z-index: 6; display: grid; grid-template-columns: repeat(10, 34px); gap: 2px; width: 400px; max-width: calc(100% - 24px); max-height: 220px; overflow-x: hidden; overflow-y: auto; margin-bottom: 6px; padding: 8px; border: 1px solid #d2d6de; border-radius: 8px; background: #fff; box-shadow: 0 6px 18px rgba(0, 0, 0, .15); }
    .mensagens-emojis[hidden] { display: none; }
    .mensagens-emoji { width: 34px; height: 34px; padding: 0; border: 0; border-radius: 6px; background: transparent; font-size: 20px; line-height: 34px; cursor: pointer; }
    .mensagens-emoji:hover, .mensagens-emoji:focus { background: #ecebf3; outline: none; }

    .mensagens-card-item { padding: 10px 12px; margin-bottom: 8px; border: 1px solid #e2e8f0; border-radius: 6px; background: #f9fbfd; }
    .mensagens-card-topo { display: flex; justify-content: space-between; gap: 8px; margin-bottom: 4px; font-size: 12px; }
    .mensagens-card-texto { margin: 0 0 6px; white-space: pre-wrap; word-break: break-word; font-size: 13px; }
    .mensagens-card-acoes { display: flex; gap: 8px; }
    .mensagens-enc-previa { margin-bottom: 10px; padding: 8px 10px; border-left: 3px solid #1E1D3F; border-radius: 4px; background: #f7f9fb; font-size: 13px; }
    .mensagens-vis-secao { margin: 12px 0 6px; font-weight: 700; }

    @media (max-width: 767px) {
        .mensagens-app { height: calc(100vh - 170px); }
        .mensagens-bolha { max-width: 88%; }
        .mensagens-dica span:first-child { display: none; }
        .mensagens-emojis { grid-template-columns: repeat(auto-fill, 34px); right: 12px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .mensagens-tela, .mensagens-tela.is-ativa { transition: visibility 0s; }
        .mensagens-tela.is-esquerda, .mensagens-tela.is-direita { transform: none; opacity: 0; }
    }

    /* ---------- Grupos ---------- */
    .mensagens-avatar-grupo { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 40px; width: 40px; height: 40px; border-radius: 50%; background: #1E1D3F; color: #fff; font-size: 17px; }
    #mensagens-atual-avatar { display: inline-flex; flex: 0 0 auto; }
    .mensagens-topo-titulo { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .mensagens-topo-titulo .box-title { flex: none; }
    .mensagens-topo-sub { align-self: flex-start; padding: 0; border: 0; background: none; color: #1E1D3F; font-size: 12px; cursor: pointer; }
    .mensagens-topo-sub:hover, .mensagens-topo-sub:focus { text-decoration: underline; outline: none; }
    .mensagens-topo-sub[hidden], .mensagens-topo-status[hidden], #mensagens-grupo-btn[hidden] { display: none; }
    .mensagens-topo-status { color: #00a65a; font-size: 12px; font-style: italic; }

    .mensagens-novo-grupo { display: flex; align-items: center; gap: 12px; width: 100%; padding: 12px 16px; border: 0; border-bottom: 1px solid #d2d6de; background: #f8f8fb; text-align: left; cursor: pointer; }
    .mensagens-novo-grupo:hover, .mensagens-novo-grupo:focus { background: #ecebf3; outline: none; }
    .mensagens-novo-grupo strong { display: block; color: #1e293b; font-size: 14px; }
    .mensagens-novo-grupo small { display: block; color: #64748b; font-size: 12px; }

    .mensagens-grupo-form { padding: 12px 14px 4px; border-bottom: 1px solid #d2d6de; }
    .mensagens-grupo-form label { font-weight: 600; font-size: 12px; }
    .mensagens-grupo-resumo { margin: 8px 0 6px; color: #64748b; font-size: 12px; }
    .mensagens-escolhidos { display: flex; flex-wrap: wrap; gap: 6px; margin: 0 0 8px; padding: 0; list-style: none; }
    .mensagens-escolhidos:empty { display: none; }
    .mensagens-escolhido { display: inline-flex; align-items: center; gap: 4px; padding: 2px 4px 2px 9px; border-radius: 4px; background: #1E1D3F; color: #fff; font-size: 12px; }
    .mensagens-escolhido-remover { padding: 0 4px; border: 0; border-radius: 3px; background: transparent; color: #fff; font-size: 15px; line-height: 16px; opacity: .85; }
    .mensagens-escolhido-remover:hover, .mensagens-escolhido-remover:focus { opacity: 1; background: rgba(0, 0, 0, .18); outline: none; }
    .mensagens-grupo-acoes { padding: 10px 14px; border-top: 1px solid #d2d6de; text-align: right; }

    .mensagens-contato.is-selecionavel { position: relative; padding-right: 48px; }
    .mensagens-marca { position: absolute; top: 50%; right: 16px; width: 22px; height: 22px; margin-top: -11px; border: 2px solid #c9d3dd; border-radius: 50%; color: #fff; font-size: 11px; line-height: 18px; text-align: center; }
    .mensagens-contato.is-marcado { background: #f4f3f9; }
    .mensagens-contato.is-marcado .mensagens-marca { border-color: #1E1D3F; background: #1E1D3F; }

    .mensagens-sistema { margin: 8px 0; text-align: center; }
    .mensagens-sistema span { display: inline-block; max-width: 85%; padding: 3px 12px; border-radius: 10px; background: #fff8e6; color: #8a6d3b; font-size: 12px; }
    .mensagens-bolha-autor { display: block; margin-bottom: 2px; font-size: 12px; font-weight: 700; }
    .mensagens-bolha.tem-anexo .mensagens-bolha-autor { padding: 2px 6px 0; }

    .mensagens-membros { margin: 0 0 6px; padding: 0; list-style: none; }
    .mensagens-membro { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f0f2f4; }
    .mensagens-membro .mensagens-avatar, .mensagens-membro .mensagens-avatar-grupo { flex-basis: 34px; width: 34px; height: 34px; }
    .mensagens-membro-nome { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 600; }
    .mensagens-adicionar { margin-top: 14px; padding-top: 12px; border-top: 1px solid #e2e8f0; }
    .mensagens-adicionar[hidden], #mensagens-excluir-grupo[hidden] { display: none; }
    .mensagens-adicionar h5 { margin: 0 0 8px; font-weight: 700; }
    .mensagens-lista-modal { max-height: 220px; margin: 8px 0; border: 1px solid #e2e8f0; border-radius: 4px; }
    .mensagens-lista-modal .mensagens-contato { padding: 8px 12px; }

    /* ---------- Modo escuro ---------- */
    body.dark-mode .mensagens-tela { background: #1e1e1e !important; }
    body.dark-mode .mensagens-busca, body.dark-mode .mensagens-rodape { border-color: #2d2d2d !important; }
    body.dark-mode .mensagens-contato { border-bottom-color: #2d2d2d !important; }
    body.dark-mode .mensagens-contato:hover, body.dark-mode .mensagens-contato:focus { background: #252525 !important; }
    body.dark-mode .mensagens-contato-nome, body.dark-mode .mensagens-contato.tem-nao-lidas .mensagens-contato-ultima { color: #e6e6e6 !important; }
    body.dark-mode .mensagens-contato.is-ativo { background: #1f3533 !important; box-shadow: inset 3px 0 0 #9cd7d4; }
    body.dark-mode .mensagens-app[data-tela="conversa"] .mensagens-tela[data-tela="lista"] { border-right-color: #2d2d2d !important; }
    body.dark-mode .mensagens-voltar { color: #ccc; }
    body.dark-mode .mensagens-historico { background: #121212 !important; }
    body.dark-mode .mensagens-bolha { background: #252525 !important; color: #e6e6e6 !important; box-shadow: none; }
    body.dark-mode .mensagens-msg.is-minha .mensagens-bolha { background: #2d2b5a !important; color: #fff !important; }
    body.dark-mode .mensagens-anexo-arquivo { background: rgba(255, 255, 255, .06); }
    body.dark-mode .mensagens-dia span { background: #252525 !important; color: #aaa !important; }
    body.dark-mode .mensagens-lista-vazia > .fa { color: #444; }
    body.dark-mode .mensagens-pendente { background: #252525 !important; border-color: #2d2d2d !important; }
    body.dark-mode .mensagens-gravando-status { background: #3b2224 !important; border-color: #6b3035 !important; color: #fca5a5 !important; }
    body.dark-mode .mensagens-emojis, body.dark-mode .mensagens-menu-msg, body.dark-mode .mensagens-mencoes-popup { background: #1e1e1e !important; border-color: #2d2d2d !important; }
    body.dark-mode .mensagens-emoji:hover, body.dark-mode .mensagens-emoji:focus,
    body.dark-mode .mensagens-menu-item:hover, body.dark-mode .mensagens-mencao-item.is-ativa, body.dark-mode .mensagens-mencao-item:hover { background: #1f3533 !important; }
    body.dark-mode .mensagens-menu-item { color: #e6e6e6; }
    body.dark-mode .mensagens-fixada-banner { background: #2d291e !important; border-color: #2d2d2d !important; }
    body.dark-mode .mensagens-fixada-texto-wrap span, body.dark-mode .mensagens-contexto-corpo span { color: #ccc !important; }
    body.dark-mode .mensagens-contexto, body.dark-mode .mensagens-card-item, body.dark-mode .mensagens-enc-previa { background: #252525 !important; border-color: #2d2d2d !important; }
    body.dark-mode .mensagens-novo-grupo { background: #1f3533 !important; border-color: #2d2d2d !important; }
    body.dark-mode .mensagens-novo-grupo strong { color: #e6e6e6 !important; }
    body.dark-mode .mensagens-novo-grupo small, body.dark-mode .mensagens-grupo-resumo { color: #94a3b8 !important; }
    body.dark-mode .mensagens-grupo-form, body.dark-mode .mensagens-grupo-acoes { border-color: #2d2d2d !important; }
    body.dark-mode .mensagens-contato.is-marcado { background: #1f3533 !important; }
    body.dark-mode .mensagens-marca { border-color: #555; }
    body.dark-mode .mensagens-sistema span { background: #3a3220 !important; color: #e0c58a !important; }
    body.dark-mode .mensagens-membro { border-bottom-color: #2d2d2d !important; }
    body.dark-mode .mensagens-adicionar, body.dark-mode .mensagens-lista-modal { border-color: #2d2d2d !important; }
    body.dark-mode .mensagens-topo-sub { color: #9cd7d4; }

    /* ---------- Recursos v2 ---------- */
    .mensagens-meu-status-bar { padding: 6px 12px; border-bottom: 1px solid #d2d6de; background: #f8fafc; }
    .mensagens-meu-status-btn { display: flex; align-items: center; gap: 7px; width: 100%; padding: 4px 8px; border: 1px solid #e2e8f0; border-radius: 6px; background: #fff; text-align: left; font-size: 12px; }
    .mensagens-meu-status-btn:hover { background: #f1f5f9; }
    .mensagens-meu-status-btn small { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #64748b; }
    .mensagens-avatar-wrap { position: relative; display: inline-flex; flex-shrink: 0; }
    .mensagens-status-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; border: 2px solid #fff; background: #94a3b8; flex-shrink: 0; }
    .mensagens-avatar-wrap .mensagens-status-dot { position: absolute; right: 0; bottom: 0; width: 12px; height: 12px; }
    .mensagens-status-dot.st-online { background: #00a65a; }
    .mensagens-status-dot.st-reuniao { background: #605ca8; }
    .mensagens-status-dot.st-ocupado { background: #dd4b39; }
    .mensagens-status-dot.st-atendimento { background: #00c0ef; }
    .mensagens-status-dot.st-ausente { background: #f39c12; }
    .mensagens-status-dot.st-offline { background: #94a3b8; }
    .mensagens-avatar-canal { background: #605ca8 !important; color: #fff !important; }
    .mensagens-canal-tag { padding: 1px 5px; font-size: 10px; background: #605ca8; vertical-align: middle; }

    .mensagens-filtros-bar { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 7px 12px; border-bottom: 1px solid #d2d6de; background: #fff; }
    .mensagens-filtros-bar[hidden] { display: none; }
    .mensagens-etiqueta-dropdown { position: relative; flex: 0 0 auto; }
    .mensagens-etiqueta-select-btn { display: inline-flex; align-items: center; gap: 5px; max-width: 145px; }
    .mensagens-etiqueta-select-btn span:not(.caret):not(.mensagens-chip-cor) { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .mensagens-etiqueta-select-btn.is-filtrada { border-color: #1E1D3F; color: #1e293b; font-weight: 600; }
    .mensagens-etiqueta-menu { min-width: 185px; }
    .mensagens-etiqueta-menu > li > a { display: flex; align-items: center; gap: 8px; font-size: 12px; padding: 6px 12px; }
    .mensagens-etiqueta-menu > li > a .fa-check { margin-left: auto; }
    .mensagens-chip-cor { display: inline-block; width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }

    /* Correção de hover/active escuros do AdminLTE no modo claro dentro do chat */
    body:not(.dark-mode) #mensagens-app .dropdown-menu { background-color: #fff; border-color: #d2d6de; }
    body:not(.dark-mode) #mensagens-app .dropdown-menu > li > a { color: #334155; }
    body:not(.dark-mode) #mensagens-app .dropdown-menu > li > a:hover,
    body:not(.dark-mode) #mensagens-app .dropdown-menu > li > a:focus { background-color: #ecebf3; color: #1e293b; }
    body:not(.dark-mode) #mensagens-app .dropdown-menu > .active > a,
    body:not(.dark-mode) #mensagens-app .dropdown-menu > .active > a:hover,
    body:not(.dark-mode) #mensagens-app .dropdown-menu > .active > a:focus { background-color: #e9e8f2; color: #1E1D3F; font-weight: 600; }
    body:not(.dark-mode) #mensagens-app .btn-default { background-color: #fff; border-color: #d2d6de; color: #334155; }
    body:not(.dark-mode) #mensagens-app .btn-default:hover,
    body:not(.dark-mode) #mensagens-app .btn-default:focus { background-color: #f1f5f9; border-color: #cbd5e1; color: #1e293b; }
    body:not(.dark-mode) #mensagens-app .btn-default:active,
    body:not(.dark-mode) #mensagens-app .btn-default.active,
    body:not(.dark-mode) #mensagens-app .open > .dropdown-toggle.btn-default,
    body:not(.dark-mode) #mensagens-app .open > .dropdown-toggle.btn-default:hover,
    body:not(.dark-mode) #mensagens-app .open > .dropdown-toggle.btn-default:focus { background-color: #e2e8f0; border-color: #cbd5e1; color: #0f172a; box-shadow: none; }
    body:not(.dark-mode) #mensagens-app .mensagens-filtros-rapidos .btn-primary,
    body:not(.dark-mode) #mensagens-app .mensagens-filtros-rapidos .btn-primary:hover,
    body:not(.dark-mode) #mensagens-app .mensagens-filtros-rapidos .btn-primary:focus,
    body:not(.dark-mode) #mensagens-app .mensagens-filtros-rapidos .btn-primary:active { background-color: #9cd7d4; border-color: #15142D; color: #fff; box-shadow: none; }
    .mensagens-audio-acoes { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
    .mensagens-audio-sep { opacity: .55; font-size: 12px; }
    .mensagens-anexo-acao-link { display: inline-flex; align-items: center; gap: 4px; padding: 2px 6px; border: 0; border-radius: 4px; background: transparent; color: inherit; font-size: 12px; opacity: .85; cursor: pointer; }
    .mensagens-anexo-acao-link:hover, .mensagens-anexo-acao-link:focus { opacity: 1; background: rgba(0, 0, 0, .06); outline: none; }
    .mensagens-msg.is-minha .mensagens-anexo-acao-link:hover { background: rgba(255, 255, 255, .15); }
    .mensagens-anexo-acao-link:disabled { opacity: .6; cursor: wait; }
    .mensagens-contato-tags { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px; }
    .mensagens-mini-tag { display: inline-block; padding: 1px 6px; border-radius: 3px; color: #fff; font-size: 10px; font-weight: 600; line-height: 14px; }
    .mensagens-contato-ultima.is-rascunho { color: #d97706; font-style: italic; }
    .mensagens-contato-badge.is-manual { background: #00a65a; font-size: 8px; padding: 4px 5px; }
    .mensagens-et-row { display: flex; align-items: center; gap: 6px; margin-bottom: 6px; }
    .mensagens-et-toggle { display: flex; align-items: center; gap: 7px; border: 1px solid #e2e8f0; background: #fff; }
    .mensagens-et-toggle.is-ativa { border-color: #00a65a; background: #f0fdf4; font-weight: 600; }
    .mensagens-et-static { display: flex; align-items: center; gap: 7px; flex: 1; padding: 4px 6px; }

    .mensagens-somente-leitura { padding: 10px 14px; border-radius: 6px; background: #f8fafc; border: 1px dashed #cbd5e1; color: #475569; font-size: 13px; text-align: center; }
    .mensagens-somente-leitura[hidden] { display: none; }

    .mensagens-audio-lado { display: flex; flex-wrap: wrap; align-items: stretch; gap: 10px; }
    .mensagens-audio-player-col { flex: 1 1 260px; min-width: 240px; }
    .mensagens-audio-lado .mensagens-audio-wrap { width: 100%; }
    .mensagens-audio-trans-col { flex: 1 1 190px; min-width: 170px; padding: 8px 10px; border-radius: 6px; background: rgba(0, 0, 0, .05); font-size: 12px; display: flex; flex-direction: column; justify-content: center; }
    .mensagens-msg.is-minha .mensagens-audio-trans-col { background: rgba(255, 255, 255, .14); }
    .mensagens-transcricao-label { font-weight: 700; font-size: 11px; margin-bottom: 4px; opacity: .85; }
    .mensagens-transcricao-texto { white-space: pre-wrap; word-break: break-word; line-height: 1.4; }

    .mensagens-chamado-link { display: block; margin-top: 6px; padding: 6px 8px; border-radius: 5px; background: rgba(30, 29, 63, .12); color: #1E1D3F; font-size: 12px; text-decoration: none; }
    .mensagens-msg.is-minha .mensagens-chamado-link { background: rgba(255, 255, 255, .2); color: #fff; }
    .mensagens-chamado-link:hover { text-decoration: underline; }
    .mensagens-card-item.is-concluido { opacity: .6; }

    .mensagens-chamada-banner { display: flex; align-items: center; gap: 8px; padding: 7px 14px; background: #ecfdf5; border-bottom: 1px solid #a7f3d0; color: #065f46; font-size: 13px; }
    .mensagens-chamada-banner[hidden] { display: none; }
    .mensagens-chamada-painel { padding: 12px 16px; background: #1e293b; color: #fff; border-bottom: 1px solid #334155; }
    .mensagens-chamada-painel[hidden] { display: none; }
    .mensagens-chamada-topo { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
    .mensagens-chamada-info { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .mensagens-chamada-badge-ico { flex: 0 0 34px; width: 34px; height: 34px; border-radius: 50%; background: #00a65a; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 15px; box-shadow: 0 0 0 3px rgba(0, 166, 90, .24); }
    .mensagens-chamada-txt { display: flex; flex-direction: column; min-width: 0; }
    .mensagens-chamada-txt strong { font-size: 14px; line-height: 1.25; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .mensagens-chamada-txt small { font-size: 11.5px; color: #94a3b8; line-height: 1.3; }
    .mensagens-chamada-controles { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .mensagens-chamada-participantes { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255, 255, 255, .1); }
    .mensagens-chamada-part { display: inline-flex; align-items: center; gap: 8px; padding: 4px 10px 4px 4px; border-radius: 20px; background: rgba(255, 255, 255, .09); border: 2px solid transparent; font-size: 12.5px; }
    .mensagens-chamada-part .mensagens-avatar { width: 26px; height: 26px; flex-basis: 26px; }
    .mensagens-chamada-part.is-falando { border-color: #22c55e; box-shadow: 0 0 0 2px rgba(34, 197, 94, .3); }
    .mensagens-chamada-palco { margin-top: 12px; background: #0f172a; border: 1px solid #334155; border-radius: 6px; overflow: hidden; }
    .mensagens-chamada-palco[hidden] { display: none !important; }
    .mensagens-chamada-palco-topo { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 12px; font-size: 12px; color: #e2e8f0; background: #0b1220; border-bottom: 1px solid #1e293b; }
    #mensagens-chamada-video { display: block; width: 100%; max-height: 300px; background: #000; object-fit: contain; }
    @media (min-width: 768px) {
        .mensagens-app.em-chamada .mensagens-tela[data-tela="lista"] { opacity: 0; visibility: hidden; transform: translateX(-100%); pointer-events: none; }
        .mensagens-app.em-chamada .mensagens-tela[data-tela="conversa"] { left: 0; }
        .mensagens-app.em-chamada.em-compartilhamento .mensagens-tela[data-tela="conversa"] { padding-left: 58%; }
        .mensagens-app.em-chamada.em-compartilhamento .mensagens-chamada-palco { position: absolute; top: 0; bottom: 0; left: 0; width: 58%; margin: 0; border-radius: 0; border-right: 1px solid #334155; display: flex; flex-direction: column; z-index: 4; }
        .mensagens-app.em-chamada.em-compartilhamento #mensagens-chamada-video { flex: 1; min-height: 0; width: 100%; height: 100%; max-height: none; }
        .mensagens-app.em-chamada.em-compartilhamento .mensagens-topo { flex-wrap: wrap; gap: 6px; }
    }
    @media (min-width: 1120px) {
        .mensagens-app.em-chamada.em-compartilhamento .mensagens-tela[data-tela="conversa"] { padding-left: calc(100% - 430px); }
        .mensagens-app.em-chamada.em-compartilhamento .mensagens-chamada-palco { width: calc(100% - 430px); }
    }

    .mensagens-midias-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 10px; }
    /* Áudio ocupa a linha inteira da grade: num card de 170px o volume não cabe no player. */
    .mensagens-midia-card.is-audio { grid-column: 1 / -1; }
    .mensagens-midia-audio { display: block; width: 100%; margin: 8px 0 0; }
    .mensagens-midia-card { border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; background: #fff; display: flex; flex-direction: column; }
    .mensagens-midia-card img, .mensagens-midia-card video { width: 100%; height: 120px; object-fit: cover; background: #000; display: block; }
    .mensagens-midia-doc { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; height: 120px; padding: 10px; background: #f8fafc; color: #334155; text-align: center; }
    .mensagens-midia-doc span { font-size: 11px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%; }
    .mensagens-midia-meta { padding: 8px; display: flex; flex-direction: column; gap: 4px; font-size: 12px; }
    .mensagens-midia-meta strong { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .mensagens-midia-acoes { display: flex; gap: 6px; margin-top: 2px; }

    .mensagens-checklist { max-height: 150px; overflow-y: auto; border: 1px solid #d2d6de; border-radius: 4px; padding: 6px 10px; background: #fff; }
    .mensagens-check-item { display: flex; align-items: center; gap: 8px; margin: 4px 0; font-weight: 400; font-size: 13px; cursor: pointer; }

    body.dark-mode .mensagens-meu-status-bar, body.dark-mode .mensagens-filtros-bar { background: #1e1e1e !important; border-color: #2d2d2d !important; }
    body.dark-mode .mensagens-meu-status-btn, body.dark-mode .mensagens-etiqueta-select-btn, body.dark-mode .mensagens-et-toggle, body.dark-mode .mensagens-checklist { background: #252525 !important; border-color: #2d2d2d !important; color: #e6e6e6 !important; }
    body.dark-mode .mensagens-topo-dropdown, body.dark-mode .mensagens-etiqueta-menu { background: #1e1e1e !important; border-color: #2d2d2d !important; }
    body.dark-mode .mensagens-topo-dropdown > li > a, body.dark-mode .mensagens-etiqueta-menu > li > a { color: #e6e6e6 !important; }
    body.dark-mode .mensagens-topo-dropdown > li > a:hover, body.dark-mode .mensagens-etiqueta-menu > li > a:hover { background: #2d2d2d !important; }
    body.dark-mode .mensagens-somente-leitura { background: #1e1e1e !important; border-color: #444 !important; color: #ccc !important; }
    body.dark-mode .mensagens-midia-card { background: #252525 !important; border-color: #2d2d2d !important; }
    body.dark-mode .mensagens-midia-doc { background: #121212 !important; color: #e6e6e6 !important; }
    body.dark-mode .mensagens-chamada-banner { background: #1e3a2f !important; border-color: #2c5e4a !important; color: #a7f3d0 !important; }

    /* ---------- Reações, Enquetes, Reuniões e Bot ---------- */
    .mensagens-avatar-bot { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 40px; width: 40px; height: 40px; border-radius: 50%; background: #00a65a; color: #fff; font-size: 19px; }
    .mensagens-bot-tag { padding: 1px 5px; font-size: 10px; vertical-align: middle; }
    .mensagens-msg-reagir-btn { position: absolute; top: 3px; right: 25px; width: 20px; height: 20px; padding: 0; border: 0; border-radius: 50%; background: transparent; color: inherit; opacity: 0; cursor: pointer; transition: opacity .15s, background .15s; z-index: 2; }
    .mensagens-bolha:hover .mensagens-msg-reagir-btn, .mensagens-msg-reagir-btn:focus,
    .mensagens-bolha:hover .mensagens-msg-acoes-btn, .mensagens-msg-acoes-btn:focus { opacity: .92; background: rgba(255, 255, 255, .92); color: #334155; box-shadow: 0 1px 3px rgba(0, 0, 0, .14); outline: none; }
    .mensagens-msg.is-minha .mensagens-bolha:hover .mensagens-msg-reagir-btn, .mensagens-msg.is-minha .mensagens-msg-reagir-btn:focus,
    .mensagens-msg.is-minha .mensagens-bolha:hover .mensagens-msg-acoes-btn, .mensagens-msg.is-minha .mensagens-msg-acoes-btn:focus { background: rgba(15, 23, 42, .45); color: #fff; }
    .mensagens-bolha { padding: 7px 10px 5px 10px; }
    .mensagens-bolha.tem-anexo { padding: 4px 4px 6px; }
    .mensagens-bolha-meta { margin-right: -2px; }
    .mensagens-bolha.tem-card-especial { min-width: 260px; max-width: 390px; }
    .mensagens-msg.tem-reacoes { margin-bottom: 18px; }
    .mensagens-reacoes { position: absolute; bottom: -14px; right: 10px; z-index: 2; display: inline-flex; flex-wrap: nowrap; align-items: center; gap: 3px; }
    .mensagens-msg:not(.is-minha) .mensagens-reacoes { right: auto; left: 10px; }
    .mensagens-reacao-chip { display: inline-flex; align-items: center; gap: 4px; padding: 2px 7px; border: 1.5px solid #e2e8f0; border-radius: 999px; background: #fff; color: #334155; font-size: 13px; line-height: 1.25; box-shadow: 0 2px 5px rgba(0, 0, 0, .14); cursor: pointer; transition: transform .12s ease; }
    .mensagens-reacao-chip:hover { transform: scale(1.06); }
    .mensagens-reacao-chip.is-minha { border-color: #1E1D3F; background: #efeef6; color: #1E1D3F; font-weight: 600; }
    .mensagens-reacoes-popup { position: fixed; z-index: 1065; display: flex; align-items: center; gap: 3px; padding: 5px 8px; border: 1px solid #d2d6de; border-radius: 22px; background: #fff; box-shadow: 0 6px 18px rgba(0, 0, 0, .18); }
    .mensagens-reacoes-popup[hidden] { display: none; }
    .mensagens-reacao-opt { width: 30px; height: 30px; padding: 0; border: 0; border-radius: 50%; background: transparent; font-size: 18px; line-height: 30px; cursor: pointer; transition: transform .12s, background .12s; }
    .mensagens-reacao-opt:hover { transform: scale(1.2); background: #f1f5f9; }

    .mensagens-enquete-card, .mensagens-reuniao-card { margin-top: 2px; min-width: 240px; }
    .mensagens-eq-cab { margin-bottom: 8px; }
    .mensagens-eq-pergunta { font-weight: 700; font-size: 14px; line-height: 1.35; }
    .mensagens-eq-meta { display: block; margin-top: 2px; font-size: 11px; opacity: .8; }
    .mensagens-eq-lista { display: flex; flex-direction: column; gap: 6px; }
    .mensagens-eq-opcao { position: relative; overflow: hidden; display: block; width: 100%; padding: 7px 10px; border: 1px solid rgba(0, 0, 0, .14); border-radius: 6px; background: rgba(255, 255, 255, .85); color: #1e293b; text-align: left; cursor: pointer; }
    .mensagens-eq-opcao.is-votada { border-color: #1E1D3F; font-weight: 600; }
    .mensagens-eq-opcao:disabled { cursor: default; }
    .mensagens-eq-barra { position: absolute; top: 0; bottom: 0; left: 0; background: rgba(30, 29, 63, .18); pointer-events: none; transition: width .25s ease; }
    .mensagens-eq-opcao.is-votada .mensagens-eq-barra { background: rgba(30, 29, 63, .28); }
    .mensagens-eq-linha { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: 13px; }
    .mensagens-eq-nums { flex: 0 0 auto; font-size: 11px; opacity: .85; }
    .mensagens-eq-votantes { position: relative; z-index: 1; display: block; margin-top: 3px; font-size: 11px; opacity: .75; }
    .mensagens-eq-rodape { margin-top: 6px; font-size: 11px; opacity: .8; }
    .mensagens-eq-input-row { margin-bottom: 6px; }

    .mensagens-reu-topo { display: flex; flex-direction: column; gap: 2px; margin-bottom: 6px; }
    .mensagens-reu-data { font-size: 11px; font-weight: 700; opacity: .9; }
    .mensagens-reu-titulo { font-size: 14px; line-height: 1.3; }
    .mensagens-reu-desc { margin: 0 0 6px; font-size: 12px; opacity: .9; white-space: pre-wrap; }
    .mensagens-reu-resumo { font-size: 11px; font-weight: 600; opacity: .85; }
    .mensagens-reu-nomes { margin-top: 2px; font-size: 11px; opacity: .8; }
    .mensagens-reu-acoes { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 8px; }

    body.dark-mode .mensagens-reacoes-popup { background: #1e1e1e !important; border-color: #2d2d2d !important; }
    body.dark-mode .mensagens-reacao-opt:hover { background: #2d2d2d !important; }
    body.dark-mode .mensagens-reacao-chip { background: #262729 !important; border-color: #3a3b3e !important; color: #e6e6e6 !important; box-shadow: 0 2px 6px rgba(0, 0, 0, .45); }
    body.dark-mode .mensagens-reacao-chip.is-minha { background: #1f3533 !important; border-color: #9cd7d4 !important; color: #9cd7d4 !important; }
    body.dark-mode .mensagens-eq-opcao { background: #1e1e1e !important; border-color: #444 !important; color: #e6e6e6 !important; }
    body.dark-mode .mensagens-eq-opcao.is-votada { border-color: #9cd7d4 !important; }
</style>
