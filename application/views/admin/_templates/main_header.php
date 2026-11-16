<?php
defined('BASEPATH') or exit('No direct script access allowed');

?>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!-- OU Font Awesome 5 (mais moderno) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<header class="main-header">
    <a href="<?php echo site_url('dashboard'); ?>" class="logo" title="MaestriaTech">
        <span class="logo-lg">
            <img src="<?php echo base_url('assets/images/login/page-login-logo.png?v=' . time()) ?>" class="logo-image" alt="MaestriaTech">
        </span>
        <span class="logo-mini">
            <img src="<?php echo base_url('assets/images/login/logo.png?v=' . time()) ?>" class="logo-image-mini" alt="MT">
        </span>
    </a>

    <nav class="navbar navbar-static-top modern-navbar" role="navigation">
        <!-- Sidebar toggle button -->
        <div class="navbar-left">
            <a href="#" class="sidebar-toggle modern-toggle" data-toggle="offcanvas" role="button" title="Minimizar / Expandir barra lateral">
                <span class="sr-only">Toggle navigation</span>
                <span class="modern-toggle-box">
                    <i class="fa fa-times toggle-icon-close" aria-hidden="true"></i>
                    <i class="fa fa-bars toggle-icon-bars" aria-hidden="true"></i>
                </span>
            </a>
        </div>

        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav modern-nav">
                <?php
                // Envelope: conversas (grupos com acesso às mensagens) e os contatos internos antigos (preferência messages_menu).
                $chat_acesso = !empty($chat_acesso);
                $chat = isset($chat) ? $chat : array('total' => 0, 'remetentes' => array());
                $mostrar_mensagens = $admin_prefs['messages_menu'] == TRUE || $chat_acesso;
                ?>
                <?php if ($mostrar_mensagens): ?>
                    <!-- Messages -->
                    <li class="dropdown messages-menu modern-dropdown" id="mensagens-menu">
                        <a href="#" class="modern-dropdown-toggle" data-drawer="mensagens" aria-controls="header-drawer" aria-expanded="false" aria-label="Mensagens">
                            <div class="nav-icon-wrapper">
                                <i class="fa fa-envelope-o"></i>
                                <span class="label label-success modern-badge" id="mensagens-badge"<?php echo $messages_count > 0 ? '' : ' style="display:none;"' ?>><?php echo (int) $messages_count ?></span>
                            </div>
                        </a>
                    </li>
                <?php endif; ?>

                <?php echo $header_alert_file_install; ?>

                <?php
                $novidades_lidas = isset($novidades_lidas) ? $novidades_lidas : array();
                $novidades_pendentes = isset($novidades_pendentes) ? $novidades_pendentes : array();
                $apontamento_aberto = isset($apontamento_aberto) ? $apontamento_aberto : null;
                $notificacoes_chamado = isset($notificacoes_chamado) ? $notificacoes_chamado : array();
                $notificacoes_chamado_nao_lidas = isset($notificacoes_chamado_nao_lidas) ? (int) $notificacoes_chamado_nao_lidas : 0;
                // O sino aparece sempre: as notificações de chamado (e o alerta de apontamento) valem para todos os usuários,
                // mesmo com o menu de notificações desligado nas preferências.
                ?>
                    <!-- Notifications -->
                    <li class="dropdown notifications-menu modern-dropdown" id="notificacoes-menu">
                        <a href="#" class="modern-dropdown-toggle" data-drawer="notificacoes" aria-controls="header-drawer" aria-expanded="false" aria-label="Notificações">
                            <div class="nav-icon-wrapper">
                                <i class="fa fa-bell-o"></i>
                                <span class="label label-warning modern-badge" id="notificacoes-badge"<?php echo $notifications_count > 0 ? '' : ' style="display:none;"' ?>><?php echo (int) $notifications_count ?></span>
                            </div>
                        </a>
                    </li>

                <?php if ($admin_prefs['tasks_menu'] == TRUE): ?>
                    <!-- Tasks -->
                    <li class="dropdown tasks-menu modern-dropdown">
                        <a href="#" class="modern-dropdown-toggle" data-drawer="tarefas" aria-controls="header-drawer" aria-expanded="false" aria-label="Tarefas">
                            <div class="nav-icon-wrapper">
                                <i class="fa fa-flag-o"></i>
                                <span class="label label-danger modern-badge">0</span>
                            </div>
                        </a>
                    </li>
                <?php endif; ?>

                    <!-- Dark Mode Toggle -->
                    <li class="dropdown theme-toggle-menu modern-dropdown">
                        <a href="javascript:void(0)" id="theme-toggle" class="modern-dropdown-toggle">
                            <div class="nav-icon-wrapper">
                                <i id="theme-icon" class="fa fa-sun-o"></i>
                            </div>
                        </a>
                    </li>

                    <!-- Chamados Pendentes -->
                    <li class="dropdown tasks-menu modern-dropdown chamados-pendentes-menu" id="menu-chamados-pendentes">
                        <a href="#" class="modern-dropdown-toggle" data-drawer="chamados" aria-controls="header-drawer" aria-expanded="false" aria-label="Chamados pendentes" title="Chamados Pendentes">
                            <div class="nav-icon-wrapper">
                                <i class="fa fa-ticket"></i>
                                <span class="label label-warning modern-badge" id="chamados-pendentes-badge" style="<?php echo (isset($chamados_pendentes_count) && $chamados_pendentes_count > 0) ? '' : 'display: none;'; ?>">
                                    <?php echo isset($chamados_pendentes_count) ? $chamados_pendentes_count : 0; ?>
                                </span>
                            </div>
                        </a>
                    </li>

                <?php if ($admin_prefs['user_menu'] == TRUE): ?>
                    <!-- User Account -->
                    <li class="dropdown user user-menu modern-dropdown">
                        <a href="#" class="dropdown-toggle modern-dropdown-toggle user-toggle" data-toggle="dropdown">
                            <div class="user-info-wrapper">
                                <img src="<?php echo !empty($user_login['foto_url']) ? html_escape($user_login['foto_url']) : base_url($avatar_dir . '/m_001.png'); ?>" class="user-image modern-user-image" alt="User Image">
                                <span class="hidden-xs modern-username"><?php echo $user_login['firstname'] . " " . $user_login['lastname']; ?></span>
                                <i class="fa fa-chevron-down user-chevron"></i>
                            </div>
                        </a>
                        <ul class="dropdown-menu modern-dropdown-menu user-dropdown-menu">
                            <li class="user-header modern-user-header">
                                <img src="<?php echo !empty($user_login['foto_url']) ? html_escape($user_login['foto_url']) : base_url($avatar_dir . '/m_001.png'); ?>" class="img-circle modern-user-avatar" alt="User Image">
                                <p class="modern-user-info">
                                    <span class="modern-user-name"><?php echo $user_login['firstname'] . " " . $user_login['lastname']; ?></span>
                                    <small class="modern-user-since"><?php echo lang('header_member_since'); ?> <?php echo date('d/m/Y', $user_login['created_on']); ?></small>
                                </p>
                            </li>
                            <li class="user-footer modern-user-footer">
                                <div class="modern-footer-buttons">
                                    <a href="<?php echo site_url('users/profile/' . $user_login['id']); ?>" class="btn btn-default btn-flat modern-profile-btn">
                                        <i class="fa fa-user"></i> <?php echo lang('header_profile'); ?>
                                    </a>
                                    <a href="<?php echo site_url('auth/logout/admin'); ?>" class="btn btn-default btn-flat modern-logout-btn">
                                        <i class="fa fa-sign-out"></i> <?php echo lang('header_sign_out'); ?>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</header>
<!-- Gaveta lateral do cabeçalho: mensagens, notificações e chamados pendentes (e tarefas, se ativo) abrem aqui, puxando da direita. O perfil continua em dropdown. -->
<div class="header-drawer-fundo" id="header-drawer-fundo" hidden></div>
<aside class="header-drawer" id="header-drawer" role="dialog" aria-label="Painel do cabeçalho" aria-hidden="true" tabindex="-1">
    <div class="header-drawer-topo">
        <div class="header-drawer-abas" role="tablist">
<?php if ($mostrar_mensagens): ?>
            <button type="button" class="header-drawer-aba" role="tab" id="header-drawer-aba-mensagens" data-drawer="mensagens" aria-controls="header-drawer-painel-mensagens" aria-selected="false"><i class="fa fa-envelope-o"></i><span>Mensagens</span></button>
<?php endif; ?>
            <button type="button" class="header-drawer-aba" role="tab" id="header-drawer-aba-notificacoes" data-drawer="notificacoes" aria-controls="header-drawer-painel-notificacoes" aria-selected="false"><i class="fa fa-bell-o"></i><span>Notificações</span></button>
<?php if ($admin_prefs['tasks_menu'] == TRUE): ?>
            <button type="button" class="header-drawer-aba" role="tab" id="header-drawer-aba-tarefas" data-drawer="tarefas" aria-controls="header-drawer-painel-tarefas" aria-selected="false"><i class="fa fa-flag-o"></i><span>Tarefas</span></button>
<?php endif; ?>
            <button type="button" class="header-drawer-aba" role="tab" id="header-drawer-aba-chamados" data-drawer="chamados" aria-controls="header-drawer-painel-chamados" aria-selected="false"><i class="fa fa-ticket"></i><span>Chamados</span></button>
        </div>
        <button type="button" class="header-drawer-fechar" id="header-drawer-fechar" aria-label="Fechar painel">&times;</button>
    </div>
    <div class="header-drawer-corpo">
<?php if ($mostrar_mensagens): ?>
        <section class="header-drawer-painel" role="tabpanel" id="header-drawer-painel-mensagens" data-painel="mensagens" aria-labelledby="header-drawer-aba-mensagens" hidden>
            <ul class="modern-dropdown-menu header-drawer-conteudo">
                <li class="header modern-dropdown-header"><?php echo lang('header_you_have'); ?> <span id="mensagens-total"><?php echo (int) $messages_count ?></span> <?php echo lang('header_message'); ?></li>
                <li>
                    <ul class="menu modern-menu-list" id="mensagens-lista-header">
                        <?php foreach ($chat['remetentes'] as $remetente) { ?>
                            <li class="notif-chamado is-nao-lida chat-remetente" data-de="<?php echo html_escape($remetente['chave']) ?>">
                                <a href="<?php echo html_escape($remetente['url']) ?>">
                                    <?php if (!empty($remetente['foto'])) { ?><img src="<?php echo html_escape($remetente['foto']) ?>" alt="" class="notif-chamado-icone chat-avatar"><?php } else { ?><span class="notif-chamado-icone" style="background: #1E1D3F;"><i class="fa fa-users"></i></span><?php } ?>
                                    <span class="notif-chamado-corpo">
                                        <strong class="notif-chamado-titulo"><?php echo html_escape($remetente['nome']) ?> <span class="badge chat-qtd"><?php echo (int) $remetente['quantidade'] ?></span></strong>
                                        <span class="notif-chamado-texto"><?php echo html_escape($remetente['ultima']) ?></span>
                                        <small class="notif-chamado-meta"><i class="fa fa-clock-o"></i> <?php echo html_escape($remetente['quando']) ?></small>
                                    </span>
                                </a>
                            </li>
                        <?php } ?>
                        <?php
                        foreach ($messages as $value) :
                            echo $value;
                        endforeach;
                        ?>
                        <li class="notificacoes-vazio">Nenhuma mensagem nova.</li>
                    </ul>
                </li>
                <li class="footer modern-dropdown-footer">
                    <?php if ($chat_acesso) { ?>
                        <a href="<?php echo site_url('mensagens') ?>" class="modern-dropdown-link"><i class="fa fa-comments-o"></i> Abrir mensagens</a>
                    <?php } else { ?>
                        <a href="<?php echo base_url('contatos_interno/index') ?>" class="modern-dropdown-link"><?php echo lang('header_view_all'); ?></a>
                    <?php } ?>
                </li>
            </ul>
        </section>
<?php endif; ?>
        <section class="header-drawer-painel" role="tabpanel" id="header-drawer-painel-notificacoes" data-painel="notificacoes" aria-labelledby="header-drawer-aba-notificacoes" hidden>
            <ul class="modern-dropdown-menu header-drawer-conteudo">
                <li class="header modern-dropdown-header"><?php echo lang('header_you_have'); ?> <span id="notificacoes-total"><?php echo (int) $notifications_count ?></span> <?php echo lang('header_notification'); ?></li>
                <li>
                    <ul class="menu modern-menu-list" id="notificacoes-lista">
                        <?php foreach ($chat['remetentes'] as $remetente) { ?>
                            <li class="notif-chamado is-nao-lida chat-remetente" data-de="<?php echo html_escape($remetente['chave']) ?>">
                                <a href="<?php echo html_escape($remetente['url']) ?>">
                                    <span class="notif-chamado-icone" style="background: #00a65a;"><i class="fa fa-envelope"></i></span>
                                    <span class="notif-chamado-corpo">
                                        <strong class="notif-chamado-titulo"><?php echo (int) $remetente['quantidade'] > 1 ? (int) $remetente['quantidade'] . ' mensagens novas' : 'Nova mensagem' ?> <?php echo !empty($remetente['grupo']) ? 'em' : 'de' ?> <?php echo html_escape($remetente['nome']) ?></strong>
                                        <span class="notif-chamado-texto"><?php echo html_escape($remetente['ultima']) ?></span>
                                        <small class="notif-chamado-meta"><i class="fa fa-clock-o"></i> <?php echo html_escape($remetente['quando']) ?></small>
                                    </span>
                                </a>
                            </li>
                        <?php } ?>
                        <?php foreach ($notificacoes_chamado as $notif) { ?>
                            <li class="notif-chamado<?php echo $notif['lida'] ? '' : ' is-nao-lida' ?>" data-id="<?php echo (int) $notif['id'] ?>">
                                <a href="<?php echo html_escape($notif['url']) ?>" title="<?php echo html_escape($notif['titulo'] . ($notif['chamado_titulo'] !== '' ? ' - ' . $notif['chamado_titulo'] : '')) ?>" style="border-left-color: <?php echo html_escape($notif['cor']) ?>;">
                                    <span class="notif-chamado-icone" style="background: <?php echo html_escape($notif['cor']) ?>;"><i class="fa <?php echo html_escape($notif['icone']) ?>"></i></span>
                                    <span class="notif-chamado-corpo">
                                        <strong class="notif-chamado-titulo"><?php echo html_escape($notif['titulo']) ?></strong>
                                        <span class="notif-chamado-texto"><?php echo html_escape($notif['texto']) ?></span>
                                        <small class="notif-chamado-meta"><i class="fa fa-user-o"></i> <?php echo html_escape($notif['autor']) ?> · <span title="<?php echo html_escape($notif['data']) ?>"><?php echo html_escape($notif['quando']) ?></span></small>
                                    </span>
                                </a>
                            </li>
                        <?php } ?>
                        <?php foreach ($novidades_lidas as $novidade_lida) { ?>
                            <li class="notif-chamado notif-novidade">
                                <a href="#" class="js-novidade-abrir" data-id="<?php echo (int) $novidade_lida->id ?>" title="<?php echo html_escape($novidade_lida->titulo) ?>" style="border-left-color: #00c0ef;">
                                    <span class="notif-chamado-icone" style="background: #00c0ef;"><i class="fa fa-bullhorn"></i></span>
                                    <span class="notif-chamado-corpo">
                                        <strong class="notif-chamado-titulo">Novidade no sistema</strong>
                                        <span class="notif-chamado-texto"><?php echo html_escape($novidade_lida->titulo) ?></span>
                                        <small class="notif-chamado-meta"><i class="fa fa-eye"></i> Clique para ver de novo</small>
                                    </span>
                                </a>
                            </li>
                        <?php } ?>
                        <?php
                        foreach ($notifications as $value) :
                            echo $value;
                        endforeach;
                        ?>
                        <li class="notificacoes-vazio">Nenhuma notificação por enquanto.</li>
                    </ul>
                </li>
                <li class="footer modern-dropdown-footer" id="notificacoes-marcar-todas-li"<?php echo $notificacoes_chamado_nao_lidas > 0 ? '' : ' style="display:none;"' ?>>
                    <a href="#" class="modern-dropdown-link" id="notificacoes-marcar-todas"><i class="fa fa-check"></i> Marcar todas como lidas</a>
                </li>
            </ul>
        </section>
<?php if ($admin_prefs['tasks_menu'] == TRUE): ?>
        <section class="header-drawer-painel" role="tabpanel" id="header-drawer-painel-tarefas" data-painel="tarefas" aria-labelledby="header-drawer-aba-tarefas" hidden>
            <ul class="modern-dropdown-menu header-drawer-conteudo">
                <li class="header modern-dropdown-header"><?php echo lang('header_you_have'); ?> 0 <?php echo lang('header_task'); ?></li>
                <li>
                    <ul class="menu modern-menu-list">
                        <li>
                            <a href="#" class="modern-task-item">
                                <h3>Design some buttons<small class="pull-right">20%</small></h3>
                                <div class="progress xs modern-progress">
                                    <div class="progress-bar progress-bar-aqua modern-progress-bar" style="width: 20%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                                        <span class="sr-only">20% <?php echo lang('header_complete'); ?></span>
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="footer modern-dropdown-footer">
                    <a href="#" class="modern-dropdown-link"><?php echo lang('header_view_all'); ?></a>
                </li>
            </ul>
        </section>
<?php endif; ?>
        <section class="header-drawer-painel" role="tabpanel" id="header-drawer-painel-chamados" data-painel="chamados" aria-labelledby="header-drawer-aba-chamados" hidden>
            <ul class="modern-dropdown-menu header-drawer-conteudo chamados-dropdown-menu">
                <li class="header modern-dropdown-header chamados-dropdown-header">
                    <div class="chamados-header-content">
                        <span><i class="fa fa-ticket"></i> Chamados Pendentes (<b id="chamados-count-text"><?php echo isset($chamados_pendentes_count) ? $chamados_pendentes_count : 0; ?></b>)</span>
                        <button type="button" class="btn-refresh-chamados" id="btn-refresh-chamados" title="Atualizar chamados">
                            <i class="fa fa-refresh" id="icon-refresh-chamados"></i>
                        </button>
                    </div>
                </li>
                <li>
                    <div class="chamados-cards-scroll" id="chamados-cards-container">
                        <?php if (isset($chamados_pendentes_user) && !empty($chamados_pendentes_user)): ?>
                            <?php foreach ($chamados_pendentes_user as $c): ?>
                                <?php 
                                    $desc_limpa = trim(strip_tags($c->descricao));
                                    if (mb_strlen($desc_limpa, 'UTF-8') > 105) {
                                        $desc_resumo = mb_substr($desc_limpa, 0, 105, 'UTF-8') . '...';
                                    } else {
                                        $desc_resumo = $desc_limpa ?: ($c->titulo ?: 'Sem descrição informada');
                                    }

                                    $pri_class = 'prioridade-normal';
                                    if (!empty($c->prioridade_desc)) {
                                        $pri_lower = mb_strtolower($c->prioridade_desc, 'UTF-8');
                                        if (strpos($pri_lower, 'alta') !== false) $pri_class = 'prioridade-alta';
                                        elseif (strpos($pri_lower, 'baixa') !== false) $pri_class = 'prioridade-baixa';
                                        elseif (strpos($pri_lower, 'urg') !== false) $pri_class = 'prioridade-urgente';
                                    }
                                ?>
                                <a href="<?php echo site_url('chamados/details/' . $c->id); ?>" class="chamado-card-item" data-id="<?php echo $c->id; ?>">
                                    <div class="chamado-card-top">
                                        <span class="chamado-code">#<?php echo $c->id; ?></span>
                                        <?php if (!empty($c->prioridade_desc)): ?>
                                            <span class="chamado-badge-prioridade <?php echo $pri_class; ?>">
                                                <?php echo htmlspecialchars($c->prioridade_desc, ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($c->cliente)): ?>
                                            <span class="chamado-cliente" title="<?php echo htmlspecialchars($c->cliente, ENT_QUOTES, 'UTF-8'); ?>">
                                                <i class="fa fa-building-o"></i> <?php echo htmlspecialchars(mb_strimwidth($c->cliente, 0, 20, '...'), ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="chamado-card-title">
                                        <?php echo htmlspecialchars($c->titulo, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                    <div class="chamado-card-desc">
                                        <?php echo htmlspecialchars($desc_resumo, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                    <div class="chamado-card-footer">
                                        <span class="chamado-card-date">
                                            <i class="fa fa-calendar-o"></i> <?php echo date('d/m/Y H:i', strtotime($c->created)); ?>
                                        </span>
                                        <?php if (!empty($c->data_prazo)): ?>
                                            <span class="chamado-card-prazo <?php echo (strtotime($c->data_prazo) < time()) ? 'text-danger' : ''; ?>">
                                                <i class="fa fa-clock-o"></i> Prazo: <?php echo date('d/m/Y H:i', strtotime($c->data_prazo)); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="chamado-empty-state" id="chamados-empty-state">
                                <i class="fa fa-check-circle-o"></i>
                                <p>Nenhum chamado pendente</p>
                                <span>Você não possui chamados em aberto no momento.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </li>
                <li class="footer modern-dropdown-footer">
                    <a href="<?php echo site_url('chamados'); ?>" class="modern-dropdown-link">
                        Ver todos os chamados <i class="fa fa-arrow-right"></i>
                    </a>
                </li>
            </ul>
        </section>
    </div>
</aside>


<style>
    /* Transições suaves sincronizadas com a barra lateral */
    .main-sidebar,
    .main-header .logo,
    .main-header .navbar.modern-navbar,
    .content-wrapper,
    .main-footer,
    .main-footer.modern-main-footer {
        transition: width 0.38s cubic-bezier(0.25, 0.8, 0.25, 1),
                    margin-left 0.38s cubic-bezier(0.25, 0.8, 0.25, 1),
                    transform 0.38s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
        will-change: width, margin-left;
    }

    .main-header {
        background: #ffffff !important;
        border-bottom: 1px solid rgba(30, 29, 63, 0.08) !important;
        box-shadow: 0 2px 10px rgba(30, 29, 63, 0.03) !important;
        position: fixed !important;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1030 !important;
        height: 50px !important;
    }

    /* Logo Styles - Comportamento Expandido e Miniatura */
    .main-header .logo {
        background: #ffffff !important;
        border-right: 1px solid rgba(30, 29, 63, 0.08) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 16px !important;
        height: 50px !important;
        width: 230px !important;
        float: left !important;
        overflow: hidden !important;
        box-shadow: none !important;
        text-decoration: none !important;
        position: relative;
        transition: width 0.38s cubic-bezier(0.25, 0.8, 0.25, 1), background-color 0.3s ease !important;
    }

    .main-header .logo:hover {
        background: #fcfcfc !important;
    }

    .main-header .logo .logo-lg {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        height: 100% !important;
        transition: opacity 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
    }

    .main-header .logo .logo-mini {
        display: none !important;
        transition: opacity 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
    }

    .main-header .logo-image {
        max-height: 34px !important;
        max-width: 185px !important;
        width: auto !important;
        height: auto !important;
        object-fit: contain !important;
        display: block !important;
        margin: 0 auto !important;
        transition: transform 0.2s ease;
    }

    .main-header .logo:hover .logo-image {
        transform: scale(1.03);
    }

    .main-header .logo-image-mini {
        max-height: 32px !important;
        max-width: 32px !important;
        width: auto !important;
        height: auto !important;
        object-fit: contain !important;
        display: block !important;
        margin: 0 auto !important;
        transition: transform 0.2s ease;
    }

    .main-header .logo:hover .logo-image-mini {
        transform: scale(1.1);
    }

    /* Comportamento Colapsado da Barra Lateral (Desktop) */
    body.sidebar-collapse .main-header .logo,
    body.sidebar-mini.sidebar-collapse .main-header .logo {
        width: 50px !important;
        padding: 0 !important;
    }

    body.sidebar-collapse .main-header .logo .logo-lg,
    body.sidebar-mini.sidebar-collapse .main-header .logo .logo-lg {
        display: none !important;
    }

    body.sidebar-collapse .main-header .logo .logo-mini,
    body.sidebar-mini.sidebar-collapse .main-header .logo .logo-mini {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 50px !important;
        height: 50px !important;
    }

    body.sidebar-collapse .main-header .navbar.modern-navbar,
    body.sidebar-mini.sidebar-collapse .main-header .navbar.modern-navbar {
        margin-left: 50px !important;
    }

    body.sidebar-collapse .content-wrapper,
    body.sidebar-mini.sidebar-collapse .content-wrapper,
    body.sidebar-collapse .main-footer,
    body.sidebar-mini.sidebar-collapse .main-footer {
        margin-left: 50px !important;
    }

    /* Expandido no Hover quando colapsado */
    body.sidebar-expanded-on-hover .main-header .logo,
    body.sidebar-mini.sidebar-collapse.sidebar-expanded-on-hover .main-header .logo {
        width: 230px !important;
        padding: 0 16px !important;
    }

    body.sidebar-expanded-on-hover .main-header .logo .logo-lg,
    body.sidebar-mini.sidebar-collapse.sidebar-expanded-on-hover .main-header .logo .logo-lg {
        display: flex !important;
    }

    body.sidebar-expanded-on-hover .main-header .logo .logo-mini,
    body.sidebar-mini.sidebar-collapse.sidebar-expanded-on-hover .main-header .logo .logo-mini {
        display: none !important;
    }

    body.sidebar-expanded-on-hover .main-header .navbar.modern-navbar,
    body.sidebar-mini.sidebar-collapse.sidebar-expanded-on-hover .main-header .navbar.modern-navbar {
        margin-left: 230px !important;
    }

    /* Modern Navbar */
    .modern-navbar {
        background: transparent !important;
        min-height: 50px !important;
        height: 50px !important;
        margin-left: 230px !important;
        padding: 0 !important;
        border: none !important;
        transition: margin-left 0.38s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
    }

    .navbar-left {
        float: left !important;
        display: flex !important;
        align-items: center !important;
        height: 50px !important;
    }

    /* Modern Toggle Button & Morphing */
    .main-header .sidebar-toggle.modern-toggle {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 50px !important;
        height: 50px !important;
        padding: 0 !important;
        margin: 0 !important;
        background: transparent !important;
        border: none !important;
        color: #1E1D3F !important;
        line-height: 50px !important;
        text-align: center !important;
        cursor: pointer !important;
        outline: none !important;
        position: relative !important;
        transition: background-color 0.2s ease, color 0.2s ease !important;
    }

    .main-header .sidebar-toggle.modern-toggle:hover {
        background: rgba(30, 29, 63, 0.05) !important;
        color: #9CD7D4 !important;
    }

    .main-header .sidebar-toggle.modern-toggle:focus,
    .main-header .sidebar-toggle.modern-toggle:active {
        background: rgba(30, 29, 63, 0.08) !important;
        color: #1E1D3F !important;
    }

    .main-header .sidebar-toggle.modern-toggle:before {
        display: none !important;
        content: "" !important;
    }

    .modern-toggle-box {
        position: relative;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }

    .modern-toggle-box i {
        position: absolute;
        top: 50%;
        left: 50%;
        font-size: 16px;
        line-height: 1;
        transform-origin: center center;
        transition: opacity 0.35s cubic-bezier(0.25, 0.8, 0.25, 1),
                    transform 0.35s cubic-bezier(0.25, 0.8, 0.25, 1),
                    color 0.2s ease !important;
        color: #1E1D3F !important;
    }

    .main-header .sidebar-toggle.modern-toggle:hover .modern-toggle-box i {
        color: #9CD7D4 !important;
    }

    /* 1. BARRA LATERAL ABERTA (Desktop padrão: body sem .sidebar-collapse) -> EXIBE O 'X' */
    body:not(.sidebar-collapse) .toggle-icon-close {
        opacity: 1 !important;
        visibility: visible !important;
        transform: translate(-50%, -50%) rotate(0deg) scale(1) !important;
        color: #1E1D3F !important;
    }
    body:not(.sidebar-collapse) .toggle-icon-bars {
        opacity: 0 !important;
        visibility: hidden !important;
        transform: translate(-50%, -50%) rotate(-90deg) scale(0.5) !important;
    }

    /* 2. BARRA LATERAL FECHADA (Desktop colapsado: body com .sidebar-collapse) -> EXIBE O 'HAMBÚRGUER' */
    body.sidebar-collapse .toggle-icon-close,
    body.sidebar-mini.sidebar-collapse .toggle-icon-close {
        opacity: 0 !important;
        visibility: hidden !important;
        transform: translate(-50%, -50%) rotate(90deg) scale(0.5) !important;
    }
    body.sidebar-collapse .toggle-icon-bars,
    body.sidebar-mini.sidebar-collapse .toggle-icon-bars {
        opacity: 1 !important;
        visibility: visible !important;
        transform: translate(-50%, -50%) rotate(0deg) scale(1) !important;
        color: #1E1D3F !important;
    }

    /* Mobile */
    @media (max-width: 991px) {
        body:not(.sidebar-open) .toggle-icon-bars {
            opacity: 1 !important;
            visibility: visible !important;
            transform: translate(-50%, -50%) rotate(0deg) scale(1) !important;
        }
        body:not(.sidebar-open) .toggle-icon-close {
            opacity: 0 !important;
            visibility: hidden !important;
            transform: translate(-50%, -50%) rotate(90deg) scale(0.5) !important;
        }
        body.sidebar-open .toggle-icon-close {
            opacity: 1 !important;
            visibility: visible !important;
            transform: translate(-50%, -50%) rotate(0deg) scale(1) !important;
            color: #1E1D3F !important;
        }
        body.sidebar-open .toggle-icon-bars {
            opacity: 0 !important;
            visibility: hidden !important;
            transform: translate(-50%, -50%) rotate(-90deg) scale(0.5) !important;
        }
    }

    .modern-nav {
        margin-right: 10px !important;
    }

    .modern-nav>li>a {
        padding: 15px 12px !important;
        background: transparent !important;
        transition: all 0.3s ease;
    }

    .modern-dropdown-toggle {
        background: transparent !important;
        border: none !important;
        padding: 12px 8px !important;
        transition: all 0.3s ease;
        border-radius: 8px !important;
        margin: 0 2px !important;
    }

    .modern-dropdown-toggle:hover {
        background: rgba(30, 29, 63, 0.05) !important;
        transform: translateY(-1px);
    }

    .nav-icon-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
    }

    .modern-dropdown-toggle i {
        color: #1E1D3F !important;
        font-size: 16px !important;
        transition: all 0.3s ease;
    }

    .modern-dropdown-toggle:hover i {
        color: #9CD7D4 !important;
        transform: scale(1.1);
    }

    .modern-badge {
        position: absolute !important;
        top: -5px !important;
        right: -5px !important;
        min-width: 18px !important;
        height: 18px !important;
        border-radius: 9px !important;
        padding: 0 4px !important;
        font-size: 10px !important;
        line-height: 18px !important;
        border: 2px solid #ffffff;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(30, 29, 63, 0.2);
    }

    .label-success.modern-badge {
        background: #9CD7D4 !important;
        color: #1E1D3F !important;
    }

    .label-warning.modern-badge {
        background: #1E1D3F !important;
        color: #ffffff !important;
    }

    .label-danger.modern-badge {
        background: #9CD7D4 !important;
        color: #1E1D3F !important;
    }

    .modern-dropdown-menu {
        border: none !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 30px rgba(30, 29, 63, 0.15) !important;
        margin-top: 8px !important;
        background: white !important;
        min-width: 280px !important;
    }

    /* Abertura e fechamento suaves dos menus do cabeçalho: o menu fica montado, mas invisível, e o .open do Bootstrap só troca opacidade e posição. */
    .modern-nav > .modern-dropdown > .dropdown-menu {
        display: block;
        visibility: hidden;
        opacity: 0;
        pointer-events: none;
        transform: translateY(-8px) scale(0.98);
        transform-origin: top right;
        transition: opacity 0.2s ease, transform 0.22s cubic-bezier(0.25, 0.8, 0.25, 1), visibility 0s linear 0.22s;
    }

    .modern-nav > .modern-dropdown.open > .dropdown-menu {
        visibility: visible;
        opacity: 1;
        pointer-events: auto;
        transform: none;
        transition: opacity 0.2s ease, transform 0.22s cubic-bezier(0.25, 0.8, 0.25, 1), visibility 0s;
    }

    @media (prefers-reduced-motion: reduce) {
        .modern-nav > .modern-dropdown > .dropdown-menu,
        .modern-nav > .modern-dropdown.open > .dropdown-menu {
            transform: none;
            transition: opacity 0.01s, visibility 0s;
        }
    }

    .modern-dropdown-header {
        background: linear-gradient(135deg, #1E1D3F 0%, #2a2952 100%) !important;
        color: white !important;
        padding: 15px 20px !important;
        font-weight: 600;
        border-radius: 12px 12px 0 0 !important;
        text-align: center;
    }

    .modern-menu-list {
        padding: 10px 0 !important;
    }

    .modern-menu-list a {
        padding: 12px 20px !important;
        border-bottom: 1px solid #f8f9fa;
        color: #1E1D3F !important;
        transition: all 0.3s ease;
        display: block;
    }

    .modern-menu-list a:hover {
        background: rgba(156, 215, 212, 0.1) !important;
        color: #1E1D3F !important;
        padding-left: 25px !important;
        text-decoration: none;
    }

    .modern-dropdown-footer {
        background: #f8f9fa !important;
        border-radius: 0 0 12px 12px !important;
        padding: 0 !important;
    }

    .modern-dropdown-link {
        display: block !important;
        padding: 12px 20px !important;
        text-align: center;
        color: #1E1D3F !important;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .modern-dropdown-link:hover {
        background: #9CD7D4 !important;
        color: #1E1D3F !important;
        text-decoration: none;
    }

    .user-toggle {
        padding: 8px 12px !important;
    }

    .user-info-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .modern-user-image {
        width: 32px !important;
        height: 32px !important;
        border: 2px solid rgba(30, 29, 63, 0.2);
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .modern-username {
        color: #1E1D3F !important;
        font-weight: 500;
        font-size: 14px;
    }

    .user-chevron {
        color: #1E1D3F !important;
        font-size: 12px;
        transition: all 0.3s ease;
    }

    .modern-dropdown-toggle:hover .modern-user-image {
        border-color: #9CD7D4;
        transform: scale(1.05);
    }

    .modern-dropdown-toggle:hover .user-chevron {
        color: #9CD7D4 !important;
        transform: translateY(1px);
    }

    .user-dropdown-menu {
        width: 280px !important;
        padding: 0 !important;
        overflow: hidden;
    }

    .modern-user-header {
        background: linear-gradient(135deg, #1E1D3F 0%, #2a2952 100%) !important;
        padding: 20px !important;
        text-align: center;
        border-radius: 12px 12px 0 0 !important;
    }

    .modern-user-avatar {
        width: 80px !important;
        height: 80px !important;
        border: 4px solid rgba(255, 255, 255, 0.2);
        margin-bottom: 15px;
        color: #1E1D3F !important;
    }

    .modern-user-info {
        margin: 0 !important;
    }

    .modern-user-name {
        display: block;
        color: white;
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .modern-user-since {
        color: rgba(255, 255, 255, 0.8) !important;
        font-size: 12px;
    }

    .modern-user-body {
        padding: 15px 20px !important;
        border-bottom: 1px solid #f8f9fa;
    }

    .modern-user-stat {
        color: #1E1D3F !important;
        font-weight: 600;
        font-size: 12px;
        transition: all 0.3s ease;
    }

    .modern-user-stat:hover {
        color: #9CD7D4 !important;
        text-decoration: none;
    }

    .modern-user-footer {
        padding: 15px !important;
        border-radius: 0 0 12px 12px !important;
    }

    .modern-footer-buttons {
        display: flex;
        gap: 10px;
        justify-content: space-between;
    }

    .modern-profile-btn,
    .modern-logout-btn {
        flex: 1;
        background: #f8f9fa !important;
        border: 1px solid #e9ecef !important;
        border-radius: 8px !important;
        color: #1E1D3F !important;
        font-weight: 500;
        transition: all 0.3s ease;
        padding: 8px 12px !important;
        font-size: 13px;
    }

    .modern-profile-btn:hover {
        background: #9CD7D4 !important;
        color: #1E1D3F !important;
        border-color: #9CD7D4 !important;
        transform: translateY(-1px);
    }

    .modern-logout-btn {
        color: #dc3545 !important;
        border-color: rgba(220, 53, 69, 0.2) !important;
    }

    .modern-logout-btn:hover {
        background: rgba(220, 53, 69, 0.1) !important;
        color: #dc3545 !important;
        border-color: rgba(220, 53, 69, 0.3) !important;
        transform: translateY(-1px);
    }

    .modern-control-toggle {
        padding: 12px 15px !important;
        color: #1E1D3F !important;
        transition: all 0.3s ease;
        border-radius: 8px !important;
    }

    .modern-control-toggle:hover {
        color: #9CD7D4 !important;
        background: rgba(30, 29, 63, 0.05) !important;
        transform: rotate(90deg);
    }

    .modern-progress {
        height: 6px !important;
        border-radius: 3px;
        background: #f1f2f6;
        overflow: hidden;
    }

    .modern-progress-bar {
        border-radius: 3px;
        background: linear-gradient(135deg, #9CD7D4 0%, #7fb9b6 100%) !important;
    }

    .modern-task-item h3 {
        font-size: 14px !important;
        margin: 0 0 8px 0 !important;
        color: #1E1D3F;
        font-weight: 600;
    }

    .modern-task-item small {
        color: #6c757d;
        font-weight: 500;
    }

    @media (max-width: 991px) {
        .main-header {
            position: fixed !important;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1050;
            display: flex;
            flex-direction: column;
            background: #ffffff !important;
            padding: 0 !important;
        }

        .main-header .logo {
            width: 100% !important;
            border-right: none !important;
            justify-content: center !important;
            height: 55px !important;
        }

        .main-header .navbar {
            width: 100% !important;
            background: #f8f9fa !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 0 10px !important;
            height: 55px !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .navbar-left {
            order: 1;
        }

        .navbar-custom-menu {
            order: 2;
            flex-grow: 1;
            display: flex !important;
            justify-content: flex-end !important;
        }

        .modern-nav>li>a {
            padding: 10px !important;
        }

        .modern-dropdown-toggle i {
            font-size: 18px !important;
            color: #1E1D3F !important;
        }

        .modern-dropdown-menu {
            right: 0 !important;
            left: auto !important;
            width: 240px !important;
        }

        .modern-username {
            display: none !important;
        }

        .modern-user-image {
            width: 28px !important;
            height: 28px !important;
        }

        body {
            padding-top: 110px !important;
        }
    }

    @media (max-width: 576px) {
        .modern-dropdown-menu {
            width: 200px !important;
        }

        .modern-dropdown-toggle {
            padding: 8px !important;
        }

        .navbar-custom-menu {
            gap: 4px !important;
        }
    }

    /* Dark Toggle Specific Styles */
    #theme-toggle:hover i {
        color: #f59e0b !important; /* Amber for sun */
    }

    body.dark-mode #theme-toggle:hover i {
        color: #94a3b8 !important; /* Slate for moon */
    }


    /* Chamados Pendentes Dropdown - MaestriaTech Identity */
    .chamados-dropdown-menu {
        width: 370px !important;
        max-width: 92vw !important;
        border-radius: 12px !important;
        padding: 0 !important;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(30, 29, 63, 0.15) !important;
        border: 1px solid rgba(30, 29, 63, 0.08) !important;
        background: #ffffff !important;
    }

    .chamados-dropdown-header {
        background: linear-gradient(135deg, #1E1D3F 0%, #2a2952 100%) !important;
        color: #ffffff !important;
        padding: 14px 18px !important;
        border-radius: 12px 12px 0 0 !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
    }

    .chamados-header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 13.5px;
        font-weight: 600;
        color: #ffffff;
    }

    .chamados-header-content i.fa-ticket {
        margin-right: 6px;
        color: #9CD7D4;
    }

    .btn-refresh-chamados {
        background: rgba(156, 215, 212, 0.2);
        border: 1px solid rgba(156, 215, 212, 0.3);
        color: #ffffff;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        transition: all 0.25s ease;
        outline: none;
    }

    .btn-refresh-chamados:hover {
        background: #9CD7D4;
        color: #1E1D3F;
        transform: rotate(180deg);
    }

    .chamados-cards-scroll {
        max-height: 380px;
        overflow-y: auto;
        padding: 10px 8px;
        background: #f8fafc;
    }

    .chamados-cards-scroll::-webkit-scrollbar {
        width: 5px;
    }
    .chamados-cards-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
    .chamados-cards-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .chamado-card-item {
        display: block;
        background: #ffffff;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        border-left: 3.5px solid #1E1D3F;
        padding: 10px 12px;
        margin-bottom: 8px;
        text-decoration: none !important;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(30, 29, 63, 0.04);
    }

    .chamado-card-item:hover {
        background: #ffffff;
        border-left-color: #9CD7D4;
        transform: translateY(-1.5px);
        box-shadow: 0 4px 12px rgba(30, 29, 63, 0.08);
    }

    .chamado-card-top {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 5px;
    }

    .chamado-code {
        font-size: 11px;
        font-weight: 700;
        color: #1E1D3F;
        background: rgba(30, 29, 63, 0.08);
        padding: 2px 6px;
        border-radius: 4px;
    }

    .chamado-badge-prioridade {
        font-size: 10px;
        font-weight: 600;
        padding: 1px 6px;
        border-radius: 4px;
        text-transform: uppercase;
    }

    .prioridade-alta, .prioridade-urgente {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    .prioridade-normal, .prioridade-media {
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
    }

    .prioridade-baixa {
        background: #f0fdf4;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }

    .chamado-cliente {
        font-size: 11px;
        color: #64748b;
        margin-left: auto;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 140px;
    }

    .chamado-card-title {
        font-size: 13px;
        font-weight: 600;
        color: #1E1D3F;
        line-height: 1.35;
        margin-bottom: 4px;
    }

    .chamado-card-desc {
        font-size: 11.5px;
        color: #64748b;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 6px;
    }

    .chamado-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 11px;
        color: #94a3b8;
        padding-top: 4px;
        border-top: 1px dashed #f1f5f9;
    }

    .chamado-card-prazo.text-danger {
        color: #ef4444 !important;
        font-weight: 600;
    }

    .chamado-empty-state {
        padding: 30px 20px;
        text-align: center;
        color: #94a3b8;
    }

    .chamado-empty-state i {
        font-size: 32px;
        color: #9CD7D4;
        margin-bottom: 8px;
        display: block;
    }

    .chamado-empty-state p {
        font-size: 13.5px;
        font-weight: 600;
        color: #1E1D3F;
        margin: 0 0 4px 0;
    }

    .chamado-empty-state span {
        font-size: 12px;
        color: #64748b;
    }
</style>

<script>
(function() {
    var POLL_INTERVAL = 25000;
    var refreshUrl = '<?php echo site_url("chamados/ajax_pendentes"); ?>';
    var isPolling = false;

    function escapeHtml(text) {
        if (!text) return '';
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function renderChamados(data) {
        var badge = document.getElementById('chamados-pendentes-badge');
        var countText = document.getElementById('chamados-count-text');
        var container = document.getElementById('chamados-cards-container');
        if (!container) return;

        var count = data.count || 0;
        var chamados = data.chamados || [];

        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline-block' : 'none';
        }
        if (countText) {
            countText.textContent = count;
        }

        if (count === 0) {
            container.innerHTML = '<div class="chamado-empty-state" id="chamados-empty-state">' +
                '<i class="fa fa-check-circle-o"></i>' +
                '<p>Nenhum chamado pendente</p>' +
                '<span>Você não possui chamados em aberto no momento.</span>' +
            '</div>';
            return;
        }

        var html = '';
        chamados.forEach(function(c) {
            var priClass = 'prioridade-normal';
            if (c.prioridade) {
                var priLower = c.prioridade.toLowerCase();
                if (priLower.indexOf('alta') !== -1) priClass = 'prioridade-alta';
                else if (priLower.indexOf('baixa') !== -1) priClass = 'prioridade-baixa';
                else if (priLower.indexOf('urg') !== -1) priClass = 'prioridade-urgente';
            }

            var clienteHtml = '';
            if (c.cliente) {
                var cliShort = c.cliente.length > 20 ? c.cliente.substring(0, 20) + '...' : c.cliente;
                clienteHtml = '<span class="chamado-cliente" title="' + escapeHtml(c.cliente) + '">' +
                    '<i class="fa fa-building-o"></i> ' + escapeHtml(cliShort) +
                '</span>';
            }

            var prazoHtml = '';
            if (c.prazo) {
                var prazoClass = c.atrasado ? 'text-danger' : '';
                prazoHtml = '<span class="chamado-card-prazo ' + prazoClass + '">' +
                    '<i class="fa fa-clock-o"></i> Prazo: ' + escapeHtml(c.prazo) +
                '</span>';
            }

            html += '<a href="' + escapeHtml(c.url) + '" class="chamado-card-item" data-id="' + c.id + '">' +
                '<div class="chamado-card-top">' +
                    '<span class="chamado-code">#' + c.id + '</span>' +
                    '<span class="chamado-badge-prioridade ' + priClass + '">' + escapeHtml(c.prioridade || 'Normal') + '</span>' +
                    clienteHtml +
                '</div>' +
                '<div class="chamado-card-title">' + escapeHtml(c.titulo) + '</div>' +
                '<div class="chamado-card-desc">' + escapeHtml(c.descricao) + '</div>' +
                '<div class="chamado-card-footer">' +
                    '<span class="chamado-card-date"><i class="fa fa-calendar-o"></i> ' + escapeHtml(c.created) + '</span>' +
                    prazoHtml +
                '</div>' +
            '</a>';
        });

        container.innerHTML = html;
    }

    function fetchChamados(isManual) {
        if (isPolling && !isManual) return;
        isPolling = true;

        var refreshIcon = document.getElementById('icon-refresh-chamados');
        if (refreshIcon && isManual) {
            refreshIcon.classList.add('fa-spin');
        }

        fetch(refreshUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(function(data) {
            if (data && data.status === 'success') {
                renderChamados(data);
            }
        })
        .catch(function(err) {
            console.warn('Erro ao atualizar chamados pendentes:', err);
        })
        .finally(function() {
            isPolling = false;
            if (refreshIcon && isManual) {
                setTimeout(function() {
                    refreshIcon.classList.remove('fa-spin');
                }, 300);
            }
        });
    }

    setInterval(function() {
        fetchChamados(false);
    }, POLL_INTERVAL);

    document.addEventListener('DOMContentLoaded', function() {
        var menuBtn = document.querySelector('#menu-chamados-pendentes > a');
        if (menuBtn) {
            menuBtn.addEventListener('click', function() {
                fetchChamados(true);
            });
        }

        var refreshBtn = document.getElementById('btn-refresh-chamados');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                fetchChamados(true);
            });
        }
    });
})();
</script>
<!-- Novidades: janela mostrada uma vez por usuário durante o período (cadastro em novidades/). -->
<div class="modal fade novidade-modal" id="novidade-modal" tabindex="-1" role="dialog" aria-labelledby="novidade-modal-titulo">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <span class="novidade-modal-selo"><i class="fa fa-bullhorn"></i> Novidade</span>
                <h4 class="modal-title" id="novidade-modal-titulo"></h4>
                <small class="novidade-modal-periodo" id="novidade-modal-periodo"></small>
            </div>
            <div class="modal-body">
                <div class="novidade-modal-texto" id="novidade-modal-texto"></div>
                <div class="novidade-modal-imagens" id="novidade-modal-imagens"></div>
            </div>
            <div class="modal-footer">
                <span class="novidade-modal-contador" id="novidade-modal-contador"></span>
                <button type="button" class="btn btn-primary" data-dismiss="modal"><i class="fa fa-check"></i> OK, entendi</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    window.addEventListener('load', function() {
        if (typeof $ === 'undefined' || !$.fn.modal) { return; }
        var baseUrl = <?php echo json_encode(base_url()) ?>;
        var pendentes = <?php echo json_encode(array_values($novidades_pendentes), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        var totalPendentes = pendentes.length;
        var $modal = $('#novidade-modal');
        var atual = null;

        // Também usada pela pré-visualização do cadastro (novidades/form).
        window.mostrarNovidade = function(dados, opcoes) {
            atual = { dados: dados, opcoes: opcoes || {} };
            $('#novidade-modal-titulo').text(dados.titulo);
            $('#novidade-modal-periodo').text(dados.periodo || '');
            $('#novidade-modal-texto').html(dados.conteudo_html || '');
            var $imagens = $('#novidade-modal-imagens').empty().toggleClass('is-unica', (dados.imagens || []).length === 1);
            $.each(dados.imagens || [], function(i, imagem) {
                $('<a target="_blank" rel="noopener" title="Abrir em tamanho original">').attr('href', imagem.original || imagem.url)
                    .append($('<img alt="">').attr('src', imagem.url))
                    .appendTo($imagens);
            });
            var contador = '';
            if (atual.opcoes.pendente && totalPendentes > 1) {
                contador = (totalPendentes - pendentes.length) + ' de ' + totalPendentes;
            } else if (atual.opcoes.previa) {
                contador = 'Pré-visualização: nada é registrado.';
            }
            $('#novidade-modal-contador').text(contador);
            $modal.modal('show');
        };

        function adicionarNoSino(dados) {
            var $lista = $('#notificacoes-lista');
            if (!$lista.length || $lista.find('.js-novidade-abrir[data-id="' + dados.id + '"]').length) { return; }
            $('<li class="notif-chamado notif-novidade">').append(
                $('<a href="#" class="js-novidade-abrir">').attr({ 'data-id': dados.id, title: dados.titulo }).css('border-left-color', '#00c0ef')
                    .append('<span class="notif-chamado-icone" style="background: #00c0ef;"><i class="fa fa-bullhorn"></i></span>')
                    .append($('<span class="notif-chamado-corpo">')
                        .append('<strong class="notif-chamado-titulo">Novidade no sistema</strong>')
                        .append($('<span class="notif-chamado-texto">').text(dados.titulo))
                        .append('<small class="notif-chamado-meta"><i class="fa fa-eye"></i> Clique para ver de novo</small>'))
            ).prependTo($lista);
        }

        $modal.on('hidden.bs.modal', function() {
            var fechada = atual;
            atual = null;
            if (!fechada || !fechada.opcoes.pendente) { return; }
            // Fechar (OK, X, Esc ou clique fora) conta como visto: não aparece mais na janela, só no sino.
            $.post(baseUrl + 'novidades/marcar_lida', { id: fechada.dados.id });
            adicionarNoSino(fechada.dados);
            if (pendentes.length) {
                setTimeout(function() { window.mostrarNovidade(pendentes.shift(), { pendente: true }); }, 250);
            }
        });

        $(document).on('click', '.js-novidade-abrir', function(e) {
            e.preventDefault();
            $.getJSON(baseUrl + 'novidades/detalhe/' + parseInt($(this).data('id'), 10)).done(function(res) {
                if (res && res.ok) { window.mostrarNovidade(res.novidade, {}); }
            }).fail(function() {
                window.alert('Esta novidade não está mais disponível.');
            });
        });

        if (pendentes.length) {
            window.mostrarNovidade(pendentes.shift(), { pendente: true });
        }
    });
</script>
<style>
    .novidade-modal .modal-content { border-radius: 8px; overflow: hidden; }
    .novidade-modal .modal-header { padding: 18px 20px 14px; border-bottom: 1px solid #eef1f4; }
    .novidade-modal-selo { display: inline-block; margin-bottom: 6px; padding: 2px 10px; border-radius: 12px; background: #eaf2f8; color: #3c8dbc; font-size: 12px; font-weight: 600; }
    .novidade-modal .modal-title { font-size: 20px; font-weight: 600; }
    .novidade-modal-periodo { color: #888; }
    .novidade-modal .modal-body { padding: 20px; max-height: 65vh; overflow-y: auto; }
    .novidade-modal-texto { font-size: 15px; line-height: 1.6; word-wrap: break-word; }
    .novidade-modal-imagens { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 16px; }
    .novidade-modal-imagens:empty { display: none; }
    .novidade-modal-imagens.is-unica { grid-template-columns: 1fr; }
    .novidade-modal-imagens a { display: block; border: 1px solid #e5e8eb; border-radius: 6px; overflow: hidden; background: #f7f9fb; }
    .novidade-modal-imagens img { display: block; width: 100%; max-height: 420px; object-fit: contain; }
    .novidade-modal .modal-footer { display: flex; align-items: center; justify-content: space-between; }
    .novidade-modal .modal-footer::before, .novidade-modal .modal-footer::after { display: none; }
    .novidade-modal-contador { color: #888; font-size: 12px; }
    body.dark-mode .novidade-modal .modal-header { border-color: #2d2d2d !important; }
    body.dark-mode .novidade-modal-selo { background: #1f2b2b !important; color: #9cd7d4 !important; }
    body.dark-mode .novidade-modal-imagens a { border-color: #2d2d2d !important; background: #181818 !important; }
</style>
<!-- Apontamento esquecido: depois de 4 horas seguidas avisa e deixa o alerta no sino até o apontamento ser parado. -->
<script type="text/javascript">
    window.addEventListener('load', function() {
        if (typeof $ === 'undefined') { return; }
        var baseUrl = <?php echo json_encode(base_url()) ?>;
        var limite = <?php echo (int) (isset($apontamento_alerta_segundos) ? $apontamento_alerta_segundos : 4 * 3600) ?>;
        var lembrarACada = 30 * 60 * 1000;
        var atual = null;
        var timer = null;
        var lembrete = null;

        function decorrido() {
            return atual ? atual.segundos + Math.floor((Date.now() - atual.recebido) / 1000) : 0;
        }

        function duracao(segundos) {
            var h = Math.floor(segundos / 3600);
            var m = Math.floor((segundos % 3600) / 60);
            return h + 'h' + (m < 10 ? '0' : '') + m + 'min';
        }

        function nomeChamado() {
            return 'chamado #' + atual.chamado_id + (atual.titulo ? ' - ' + atual.titulo : '');
        }

        function urlChamado() {
            return atual.url || (baseUrl + 'chamados/details/' + atual.chamado_id);
        }

        // Na própria tela do chamado o botão só rola até o cronômetro.
        function estaNoChamado() {
            return String($('#chamado_id').val() || '') === String(atual.chamado_id) && $('.chamado-timer').length > 0;
        }

        function somarNotificacoes(delta) {
            var total = Math.max(0, (parseInt($('#notificacoes-total').text(), 10) || 0) + delta);
            $('#notificacoes-total').text(total);
            $('#notificacoes-badge').text(total).toggle(total > 0);
        }

        function textoSino() {
            return 'Rodando há ' + duracao(decorrido()) + ' no ' + nomeChamado();
        }

        function adicionarNoSino() {
            var $lista = $('#notificacoes-lista');
            if (!$lista.length) { return; }
            var $item = $lista.find('.js-apontamento-alerta');
            if (!$item.length) {
                $item = $('<li class="notif-chamado is-nao-lida js-apontamento-alerta apontamento-alerta-item">').append(
                    $('<a>').css('border-left-color', '#f39c12')
                        .append('<span class="notif-chamado-icone" style="background: #f39c12;"><i class="fa fa-clock-o"></i></span>')
                        .append($('<span class="notif-chamado-corpo">')
                            .append('<strong class="notif-chamado-titulo">Apontamento ainda rodando</strong>')
                            .append($('<span class="notif-chamado-texto apontamento-alerta-item-texto">'))
                            .append('<small class="notif-chamado-meta"><i class="fa fa-stop-circle-o"></i> Abra o chamado e clique em Parar</small>'))
                ).prependTo($lista);
                somarNotificacoes(1);
            }
            $item.find('a').attr({ href: urlChamado(), title: textoSino() });
            $item.find('.apontamento-alerta-item-texto').text(textoSino());
            $('#notificacoes-menu').addClass('has-apontamento-alerta');
        }

        function removerDoSino() {
            var $item = $('#notificacoes-lista .js-apontamento-alerta');
            if ($item.length) {
                $item.remove();
                somarNotificacoes(-1);
            }
            $('#notificacoes-menu').removeClass('has-apontamento-alerta');
        }

        function fecharAviso() {
            $('#apontamento-alerta').remove();
        }

        function chaveLembrete() {
            return 'apontamento_alerta_' + atual.id;
        }

        // Entre abas e recarregamentos o aviso flutuante aparece no máximo a cada 30 minutos; o sino fica sempre.
        function podeLembrar() {
            try {
                var ultimo = parseInt(window.localStorage.getItem(chaveLembrete()), 10) || 0;
                return Date.now() - ultimo >= lembrarACada - 5000;
            } catch (e) {
                return true;
            }
        }

        function registrarLembrete() {
            try { window.localStorage.setItem(chaveLembrete(), String(Date.now())); } catch (e) {}
        }

        function mostrarAviso() {
            fecharAviso();
            registrarLembrete();
            var noChamado = estaNoChamado();
            var $botao = $('<a class="btn btn-warning btn-sm apontamento-alerta-acao">')
                .attr('href', noChamado ? '#' : urlChamado())
                .append(noChamado ? '<i class="fa fa-stop"></i> Ver o apontamento' : '<i class="fa fa-external-link"></i> Ir para o chamado');
            if (noChamado) {
                $botao.on('click', function(e) {
                    e.preventDefault();
                    fecharAviso();
                    $('html, body').animate({ scrollTop: Math.max(0, $('.chamado-timer').offset().top - 80) }, 300);
                });
            }
            $('<div id="apontamento-alerta" class="apontamento-alerta" role="alert" aria-live="assertive">')
                .append('<span class="apontamento-alerta-icone"><i class="fa fa-clock-o"></i></span>')
                .append($('<div class="apontamento-alerta-corpo">')
                    .append($('<strong class="apontamento-alerta-titulo">').text('Apontamento rodando há ' + duracao(decorrido())))
                    .append($('<p>').text('Você iniciou um apontamento no ' + nomeChamado() + (atual.inicio ? ' em ' + atual.inicio : '') + ' e ele ainda não foi parado. Se já terminou, pare o apontamento para conseguir iniciar em outro chamado.'))
                    .append($botao))
                .append($('<button type="button" class="apontamento-alerta-fechar" aria-label="Fechar aviso">&times;</button>').on('click', fecharAviso))
                .appendTo('body');

            // Notificação do navegador só quando o usuário já tiver autorizado o site e estiver em outra aba.
            if (!document.hasFocus() && 'Notification' in window && window.Notification.permission === 'granted') {
                try {
                    var nativa = new window.Notification('Apontamento rodando há ' + duracao(decorrido()), { body: 'Pare o apontamento do ' + nomeChamado() + ' se já terminou.', tag: chaveLembrete() });
                    nativa.onclick = function() { window.focus(); window.location.href = urlChamado(); };
                } catch (e) {}
            }
        }

        function disparar(forcarAviso) {
            if (!atual) { return; }
            adicionarNoSino();
            if (forcarAviso || podeLembrar()) { mostrarAviso(); }
            clearInterval(lembrete);
            // A cada minuto atualiza o tempo no sino e, a cada 30 minutos, repete o aviso.
            lembrete = setInterval(function() {
                adicionarNoSino();
                if (podeLembrar()) { mostrarAviso(); }
            }, 60 * 1000);
        }

        function parar() {
            clearTimeout(timer);
            clearInterval(lembrete);
            if (atual) {
                try { window.localStorage.removeItem(chaveLembrete()); } catch (e) {}
            }
            atual = null;
            removerDoSino();
            fecharAviso();
        }

        function acompanhar(dados) {
            parar();
            if (!dados || !dados.chamado_id) { return; }
            atual = $.extend({ segundos: 0 }, dados, { recebido: Date.now() });
            var restante = limite - atual.segundos;
            if (restante <= 0) {
                disparar(false);
            } else {
                timer = setTimeout(function() { disparar(true); }, restante * 1000);
            }
        }

        // Disparados pelo cronômetro da tela do chamado (startApontamento / stopApontamento em funcoes.js).
        $(document).on('apontamento:iniciado', function(e, dados) { acompanhar(dados); });
        $(document).on('apontamento:parado', parar);

        acompanhar(<?php echo json_encode(isset($apontamento_aberto) ? $apontamento_aberto : null, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
    });
</script>
<style>
    .apontamento-alerta { position: fixed; right: 20px; bottom: 20px; z-index: 1060; display: flex; align-items: flex-start; gap: 12px; width: 380px; max-width: calc(100vw - 32px); padding: 14px 36px 14px 14px; background: #fff; border-left: 4px solid #f39c12; border-radius: 6px; box-shadow: 0 6px 24px rgba(0, 0, 0, .18); animation: apontamento-alerta-entrar .25s ease-out; }
    .apontamento-alerta-icone { flex: 0 0 36px; height: 36px; border-radius: 50%; background: #fff4e0; color: #f39c12; font-size: 20px; line-height: 36px; text-align: center; }
    .apontamento-alerta-corpo { flex: 1; min-width: 0; }
    .apontamento-alerta-titulo { display: block; margin-bottom: 4px; font-size: 15px; color: #333; }
    .apontamento-alerta-corpo p { margin: 0 0 10px; font-size: 13px; line-height: 1.45; color: #555; word-wrap: break-word; }
    .apontamento-alerta-fechar { position: absolute; top: 6px; right: 8px; padding: 0 4px; border: 0; background: transparent; color: #999; font-size: 22px; line-height: 1; cursor: pointer; }
    .apontamento-alerta-fechar:hover, .apontamento-alerta-fechar:focus { color: #333; }
    .apontamento-alerta-item a { white-space: normal !important; }
    #notificacoes-menu.has-apontamento-alerta .fa-bell-o { color: #f39c12; animation: apontamento-alerta-sino 1.6s ease-in-out infinite; transform-origin: 50% 0; }
    @keyframes apontamento-alerta-entrar { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: none; } }
    @keyframes apontamento-alerta-sino { 0%, 70%, 100% { transform: rotate(0); } 75% { transform: rotate(14deg); } 85% { transform: rotate(-12deg); } 95% { transform: rotate(6deg); } }
    @media (prefers-reduced-motion: reduce) { .apontamento-alerta, #notificacoes-menu.has-apontamento-alerta .fa-bell-o { animation: none; } }
    @media (max-width: 480px) { .apontamento-alerta { right: 16px; bottom: 16px; } }
    body.dark-mode .apontamento-alerta { background: #1e1e1e !important; box-shadow: 0 6px 24px rgba(0, 0, 0, .5); }
    body.dark-mode .apontamento-alerta-icone { background: #2d2716 !important; }
    body.dark-mode .apontamento-alerta-titulo { color: #f1f5f9 !important; }
    body.dark-mode .apontamento-alerta-corpo p { color: #cbd5e1 !important; }
    body.dark-mode .apontamento-alerta-fechar:hover, body.dark-mode .apontamento-alerta-fechar:focus { color: #f1f5f9; }
</style>
<!-- Notificações de chamado: busca as novas a cada minuto, mostra o aviso na tela e mantém no sino. -->
<div class="notif-avisos" id="notif-avisos" aria-live="polite"></div>
<script type="text/javascript">
    window.addEventListener('load', function() {
        if (typeof $ === 'undefined') { return; }
        var baseUrl = <?php echo json_encode(base_url()) ?>;
        var naoLidas = <?php echo (int) (isset($notificacoes_chamado_nao_lidas) ? $notificacoes_chamado_nao_lidas : 0) ?>;
        // Mensagens internas: o envelope soma as não lidas; o sino tem um item por remetente.
        var chatAcesso = <?php echo !empty($chat_acesso) ? 'true' : 'false' ?>;
        var chatUltimoId = <?php echo (int) (isset($chat_ultimo_id) ? $chat_ultimo_id : 0) ?>;
        window.chatReacaoUltimoId = <?php echo (int) (isset($chat_reacao_ultimo_id) ? $chat_reacao_ultimo_id : 0) ?>;
        var sinoChatVistoId = <?php echo (int) (isset($sino_chat_visto_id) ? $sino_chat_visto_id : 0) ?>;
        var chatTotal = <?php echo (int) (isset($chat['total']) ? $chat['total'] : 0) ?>;
        var chatRemetentes = <?php echo isset($chat['remetentes']) ? count($chat['remetentes']) : 0 ?>;
        var mensagensLegado = <?php echo max(0, (int) $messages_count - (int) (isset($chat['total']) ? $chat['total'] : 0)) ?>;
        var intervalo = 30 * 1000;
        var duracaoAviso = 9000;
        var ultimaBusca = 0;
        var $lista = $('#notificacoes-lista');
        var $avisos = $('#notif-avisos');

        function somarNotificacoes(delta) {
            var total = Math.max(0, (parseInt($('#notificacoes-total').text(), 10) || 0) + delta);
            $('#notificacoes-total').text(total);
            $('#notificacoes-badge').text(total).toggle(total > 0);
        }

        function sinoEstaAberto() {
            return $('#header-drawer').hasClass('is-aberto') && !$('#header-drawer-painel-notificacoes').prop('hidden');
        }

        function zerarContadorSino(sincronizarServidor) {
            var tinhaPendencia = naoLidas > 0 || (parseInt($('#notificacoes-total').text(), 10) || 0) > 0 || chatUltimoId > sinoChatVistoId;
            $('#notificacoes-total').text(0);
            $('#notificacoes-badge').text(0).hide();
            naoLidas = 0;
            sinoChatVistoId = Math.max(sinoChatVistoId, chatUltimoId);
            $lista.find('.notif-chamado.is-nao-lida').not('.chat-remetente').removeClass('is-nao-lida');
            atualizarRodape();
            if (sincronizarServidor && tinhaPendencia) {
                $.post(baseUrl + 'notificacoes/marcar_todas', { chat_ultimo_id: chatUltimoId });
            }
        }

        function atualizarRodape() {
            $('#notificacoes-marcar-todas-li').toggle(naoLidas > 0);
        }

        function icone(n) {
            return $('<span class="notif-chamado-icone">').css('background', n.cor).append($('<i class="fa">').addClass(n.icone));
        }

        function corpo(n) {
            return $('<span class="notif-chamado-corpo">')
                .append($('<strong class="notif-chamado-titulo">').text(n.titulo))
                .append($('<span class="notif-chamado-texto">').text(n.texto))
                .append($('<small class="notif-chamado-meta">').append('<i class="fa fa-user-o"></i> ', document.createTextNode(n.autor + ' · ' + n.quando)));
        }

        function adicionarNoSino(n) {
            if (!$lista.length || $lista.find('.notif-chamado[data-id="' + n.id + '"]').length) { return false; }
            $('<li class="notif-chamado">').toggleClass('is-nao-lida', !n.lida).attr('data-id', n.id)
                .append($('<a>').attr({ href: n.url, title: n.titulo + (n.chamado_titulo ? ' - ' + n.chamado_titulo : '') }).css('border-left-color', n.cor).append(icone(n), corpo(n)))
                .prependTo($lista);
            return true;
        }

        function fecharAviso($aviso) {
            clearTimeout($aviso.data('timer'));
            $aviso.addClass('is-saindo');
            setTimeout(function() { $aviso.remove(); }, 250);
        }

        // O aviso some sozinho, mas espera enquanto o mouse estiver em cima.
        function agendarSaida($aviso) {
            clearTimeout($aviso.data('timer'));
            $aviso.data('timer', setTimeout(function() { fecharAviso($aviso); }, duracaoAviso));
        }

        function mostrarAviso(n, resumo) {
            var $aviso = $('<div class="notif-aviso" role="status">');
            if (resumo) {
                $aviso.append($('<a class="notif-aviso-link" href="#">').append(
                    $('<span class="notif-chamado-icone">').css('background', '#3c8dbc').append('<i class="fa fa-bell"></i>'),
                    $('<span class="notif-chamado-corpo">').append($('<strong class="notif-chamado-titulo">').text(resumo)).append($('<span class="notif-chamado-texto">').text('Veja todas no sino de notificações.'))
                ).on('click', function(e) {
                    e.preventDefault();
                    fecharAviso($aviso);
                    if (window.headerDrawer) { window.headerDrawer.abrir('notificacoes'); }
                }));
            } else {
                $aviso.css('border-left-color', n.cor).append($('<a class="notif-aviso-link">').attr('href', n.url).append(icone(n), corpo(n)));
            }
            $aviso.append($('<button type="button" class="notif-aviso-fechar" aria-label="Fechar aviso">&times;</button>').on('click', function() { fecharAviso($aviso); }));
            $aviso.on('mouseenter', function() { clearTimeout($aviso.data('timer')); }).on('mouseleave', function() { agendarSaida($aviso); });
            $aviso.appendTo($avisos);
            agendarSaida($aviso);
        }

        function tocarSomChat() {
            if (window.localStorage && window.localStorage.getItem('sax_chat_som') === '0') { return; }
            var Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) { return; }
            try {
                var ctx = new Ctx();
                var osc = ctx.createOscillator();
                var ganho = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(784, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(1046, ctx.currentTime + 0.08);
                ganho.gain.setValueAtTime(0.06, ctx.currentTime);
                ganho.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.2);
                osc.connect(ganho);
                ganho.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.22);
            } catch (e) {}
        }

        function itemChat(r, noSino) {
            var titulo = noSino ? (r.quantidade > 1 ? r.quantidade + ' mensagens novas' : 'Nova mensagem') + (r.grupo ? ' em ' : ' de ') + r.nome : r.nome;
            var $titulo = $('<strong class="notif-chamado-titulo">').text(titulo);
            if (r.silenciada) { $titulo.append(' ', $('<i class="fa fa-bell-slash-o text-muted" title="Silenciada"></i>')); }
            if (r.mencoes > 0) { $titulo.append(' ', $('<span class="badge bg-orange" title="Mencionou você">@</span>')); }
            if (!noSino) { $titulo.append(' ', $('<span class="badge chat-qtd">').text(r.quantidade)); }
            return $('<li class="notif-chamado is-nao-lida chat-remetente">').attr('data-de', r.chave).append(
                $('<a>').attr('href', r.url).append(
                    noSino ? icone({ cor: '#00a65a', icone: 'fa-envelope' }) : (r.foto ? $('<img alt="" class="notif-chamado-icone chat-avatar">').attr('src', r.foto) : icone({ cor: '#1E1D3F', icone: 'fa-users' })),
                    $('<span class="notif-chamado-corpo">').append($titulo)
                        .append($('<span class="notif-chamado-texto">').text(r.ultima))
                        .append($('<small class="notif-chamado-meta">').append('<i class="fa fa-clock-o"></i> ', document.createTextNode(r.quando)))
                )
            );
        }

        function aplicarChat(dados) {
            var remetentes = dados.remetentes || [];
            chatTotal = dados.total || 0;
            var totalEnvelope = mensagensLegado + chatTotal;
            $('#mensagens-badge').text(totalEnvelope).toggle(totalEnvelope > 0);
            $('#mensagens-total').text(totalEnvelope);
            var novoUltimoId = dados.ultimo_id || 0;
            chatRemetentes = remetentes.length;
            $('#notificacoes-lista .chat-remetente, #mensagens-lista-header .chat-remetente').remove();
            for (var i = remetentes.length - 1; i >= 0; i--) {
                itemChat(remetentes[i], true).prependTo($lista);
                itemChat(remetentes[i], false).prependTo('#mensagens-lista-header');
            }
            chatUltimoId = Math.max(chatUltimoId, novoUltimoId);

            // Aviso na tela para cada mensagem nova, menos a da conversa que já está aberta na tela de mensagens.
            var novas = $.grep(dados.novas || [], function(m) { return m.chave !== window.mensagensConversaAberta; });
            if (novas.length && novoUltimoId > sinoChatVistoId) {
                if (sinoEstaAberto()) {
                    sinoChatVistoId = chatUltimoId;
                    $.post(baseUrl + 'notificacoes/marcar_todas', { chat_ultimo_id: chatUltimoId });
                } else {
                    somarNotificacoes(novas.length);
                    sinoChatVistoId = chatUltimoId;
                }
            }
            if (novas.length) { tocarSomChat(); }
            $.each(novas.slice(0, 3), function(i, m) {
                mostrarAviso({ cor: m.mencionado ? '#f39c12' : '#00a65a', icone: m.mencionado ? 'fa-at' : 'fa-envelope', titulo: m.mencionado ? 'Você foi mencionado' : 'Nova mensagem', texto: m.texto, autor: m.nome, quando: 'agora', url: m.url });
            });
            $.each(dados.lembretes || [], function(i, l) {
                mostrarAviso({ cor: '#605ca8', icone: 'fa-bell', titulo: 'Lembrete de mensagem', texto: l.nota, autor: 'Lembrete', quando: 'agora', url: l.url });
            });
            if (novas.length > 3) {
                mostrarAviso({ cor: '#00a65a', icone: 'fa-envelope', titulo: 'Mais ' + (novas.length - 3) + ' mensagens novas', texto: 'Abra o envelope para ver todas.', autor: 'Mensagens', quando: 'agora', url: baseUrl + 'mensagens' });
            }
            if (novas.length && document.hidden && 'Notification' in window && window.Notification.permission === 'granted') {
                try {
                    var nativa = new window.Notification('Nova mensagem de ' + novas[0].nome, { body: novas[0].texto, tag: 'chat-' + novas[0].chave });
                    nativa.onclick = function() { window.focus(); window.location.href = novas[0].url; };
                } catch (e) {}
            }
        }

        function receberReacoesNovas(lista) {
            if (!chatAcesso || !$.isArray(lista) || !lista.length) { return; }
            var avisaveis = [];
            $.each(lista, function(i, r) {
                if (!r || !r.id) { return; }
                window.chatReacaoUltimoId = Math.max(window.chatReacaoUltimoId || 0, r.id);
                if (r.chave !== window.mensagensConversaAberta || document.hidden) {
                    avisaveis.push(r);
                }
            });
            if (!avisaveis.length) { return; }
            tocarSomChat();
            $.each(avisaveis.slice(0, 3), function(i, r) {
                mostrarAviso({
                    cor: '#00a65a',
                    icone: 'fa-smile-o',
                    titulo: r.texto,
                    texto: r.previa ? ('"' + r.previa + '"') : 'Toque para abrir a conversa',
                    autor: r.autor,
                    quando: 'agora',
                    url: r.url
                });
            });
            if (document.hidden && 'Notification' in window && window.Notification.permission === 'granted') {
                try {
                    var primeira = avisaveis[0];
                    var nativa = new window.Notification(primeira.texto, {
                        body: primeira.previa ? ('"' + primeira.previa + '"') : 'Nova reação no chat',
                        tag: 'reacao-' + primeira.id
                    });
                    nativa.onclick = function() { window.focus(); window.location.href = primeira.url; };
                } catch (e) {}
            }
        }
        window.receberReacoesNovas = receberReacoesNovas;

        function receber(res) {
            if (!res || !res.ok) { return; }
            if (res.mensagens) { aplicarChat(res.mensagens); }
            if (res.reacoes_novas) { receberReacoesNovas(res.reacoes_novas); }
            if (res.chamadas) { processarChamadasRecebidas(res.chamadas); }
            var itens = res.itens || [];
            var novos = 0;
            var aberto = sinoEstaAberto();
            // Do mais antigo para o mais novo, para o mais recente ficar no topo do sino.
            for (var i = itens.length - 1; i >= 0; i--) {
                if (adicionarNoSino(itens[i]) && !itens[i].lida) { novos++; }
            }
            if (novos) {
                if (aberto) {
                    $lista.find('.notif-chamado.is-nao-lida').not('.chat-remetente').removeClass('is-nao-lida');
                    $.post(baseUrl + 'notificacoes/marcar_todas', { chat_ultimo_id: chatUltimoId });
                } else {
                    somarNotificacoes(novos);
                    naoLidas += novos;
                }
            }
            if (typeof res.nao_lidas === 'number' && res.nao_lidas < naoLidas) {
                // Lidas em outra aba: corrige a contagem.
                somarNotificacoes(res.nao_lidas - naoLidas);
                naoLidas = res.nao_lidas;
            }
            atualizarRodape();
            if (!itens.length) { return; }

            // No máximo três avisos de uma vez; o resto fica resumido.
            var mostrar = itens.length > 3 ? itens.slice(0, 2) : itens;
            $.each(mostrar, function(i, n) { mostrarAviso(n); });
            if (itens.length > 3) {
                mostrarAviso(null, 'Mais ' + (itens.length - 2) + ' notificações de chamados');
            }
            // Em outra aba, usa a notificação do navegador se o usuário já tiver autorizado.
            if (document.hidden && 'Notification' in window && window.Notification.permission === 'granted') {
                try {
                    var primeiro = itens[0];
                    var nativa = new window.Notification(itens.length > 1 ? itens.length + ' novas notificações de chamados' : primeiro.titulo, { body: itens.length > 1 ? primeiro.titulo : primeiro.texto, tag: 'notif-chamado' });
                    nativa.onclick = function() { window.focus(); window.location.href = primeiro.url; };
                } catch (e) {}
            }
        }

        function buscar() {
            if (document.hidden) { return; }
            ultimaBusca = Date.now();
            $.ajax({ url: baseUrl + 'notificacoes/novas', data: chatAcesso ? { msg_depois: chatUltimoId, reacao_depois: window.chatReacaoUltimoId || 0 } : {}, dataType: 'json', cache: false, global: false }).done(receber);
        }

        document.addEventListener('headerDrawer:aberto', function(e) {
            if (e && e.detail && e.detail.nome === 'notificacoes') {
                zerarContadorSino(true);
            }
        });

        $(document).on('click', '#notificacoes-marcar-todas', function(e) {
            e.preventDefault();
            e.stopPropagation();
            zerarContadorSino(true);
        });

        var chamadaExibidaId = 0;
        var chamadasRecusadas = {};
        var toqueChamadaTimer = null;
        var toqueAudioCtx = null;

        function tocarPulsoChamada() {
            if (window.localStorage && window.localStorage.getItem('sax_chat_som') === '0') { return; }
            var Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) { return; }
            try {
                if (!toqueAudioCtx || toqueAudioCtx.state === 'closed') {
                    toqueAudioCtx = new Ctx();
                }
                if (toqueAudioCtx.state === 'suspended' && typeof toqueAudioCtx.resume === 'function') {
                    toqueAudioCtx.resume().catch(function() {});
                }
                var agora = toqueAudioCtx.currentTime;
                var emitir = function(inicio, freq) {
                    var osc = toqueAudioCtx.createOscillator();
                    var ganho = toqueAudioCtx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, inicio);
                    ganho.gain.setValueAtTime(0.001, inicio);
                    ganho.gain.exponentialRampToValueAtTime(0.08, inicio + 0.03);
                    ganho.gain.exponentialRampToValueAtTime(0.001, inicio + 0.35);
                    osc.connect(ganho);
                    ganho.connect(toqueAudioCtx.destination);
                    osc.start(inicio);
                    osc.stop(inicio + 0.38);
                };
                emitir(agora, 660);
                emitir(agora + 0.42, 880);
            } catch (e) {}
        }

        function pararToqueChamada() {
            clearInterval(toqueChamadaTimer);
            toqueChamadaTimer = null;
            $('#chamada-recebida-alerta').remove();
            chamadaExibidaId = 0;
        }

        function iniciarToqueChamada(c) {
            if (!c || !c.chamada_id) { return; }
            if (chamadaExibidaId === c.chamada_id && $('#chamada-recebida-alerta').length) { return; }
            pararToqueChamada();
            chamadaExibidaId = c.chamada_id;

            var subtitulo = c.eh_direta ? 'Ligação de voz' : ('Chamada em ' + (c.titulo || 'conversa'));
            var $card = $('<div id="chamada-recebida-alerta" class="chamada-recebida-alerta" role="alertdialog" aria-live="assertive">').append(
                $('<div class="chamada-recebida-cab">').append(
                    $('<span class="chamada-recebida-pulso"><i class="fa fa-phone"></i></span>'),
                    $('<img alt="" class="chamada-recebida-foto">').attr('src', c.iniciador_foto || (baseUrl + 'upload/avatar/m_001.png')),
                    $('<div class="chamada-recebida-meta">').append(
                        $('<strong>').text(c.iniciador_nome || 'Usuário'),
                        $('<span>').text(subtitulo)
                    )
                ),
                $('<div class="chamada-recebida-acoes">').append(
                    $('<button type="button" class="btn btn-success btn-sm"><i class="fa fa-phone"></i> Atender</button>').on('click', function() {
                        var alvo = c;
                        pararToqueChamada();
                        if (typeof window.mensagensAtenderChamada === 'function') {
                            window.mensagensAtenderChamada(alvo);
                        } else {
                            window.location.href = alvo.url;
                        }
                    }),
                    $('<button type="button" class="btn btn-danger btn-sm"><i class="fa fa-times"></i> Recusar</button>').on('click', function() {
                        chamadasRecusadas[c.chamada_id] = true;
                        pararToqueChamada();
                        $.post(baseUrl + 'mensagens_api/chamada_recusar', { chamada_id: c.chamada_id });
                    })
                )
            );
            $('body').append($card);
            tocarPulsoChamada();
            toqueChamadaTimer = setInterval(function() {
                if (chamadaExibidaId !== c.chamada_id) { clearInterval(toqueChamadaTimer); return; }
                tocarPulsoChamada();
            }, 2200);

            if ('Notification' in window && window.Notification.permission === 'granted') {
                try {
                    var nativa = new window.Notification(subtitulo + ' — ' + (c.iniciador_nome || 'Usuário'), {
                        body: 'Clique para atender a ligação.',
                        icon: c.iniciador_foto || undefined,
                        tag: 'chamada-' + c.chamada_id,
                        requireInteraction: true
                    });
                    nativa.onclick = function() {
                        window.focus();
                        if (typeof window.mensagensAtenderChamada === 'function') {
                            pararToqueChamada();
                            window.mensagensAtenderChamada(c);
                        } else {
                            window.location.href = c.url;
                        }
                    };
                } catch (e) {}
            }
        }

        function processarChamadasRecebidas(lista) {
            if (!chatAcesso) { return; }
            lista = $.isArray(lista) ? lista : [];
            var candidatas = $.grep(lista, function(c) {
                return c && c.chamada_id && !chamadasRecusadas[c.chamada_id];
            });
            if (!candidatas.length) {
                if (chamadaExibidaId) { pararToqueChamada(); }
                return;
            }
            var atualChamada = candidatas[0];
            if (chamadaExibidaId && chamadaExibidaId !== atualChamada.chamada_id) {
                pararToqueChamada();
            }
            iniciarToqueChamada(atualChamada);
        }

        window.receberChamadasRecebidas = processarChamadasRecebidas;

        function buscarChamadas() {
            if (!chatAcesso || document.hidden || window.mensagensTelaAtiva) { return; }
            $.ajax({ url: baseUrl + 'notificacoes/chamadas', dataType: 'json', cache: false, global: false }).done(function(res) {
                if (res && res.ok) { processarChamadasRecebidas(res.chamadas || []); }
            });
        }

        document.addEventListener('visibilitychange', function() {
            if (!document.hidden && Date.now() - ultimaBusca > 30000) { buscar(); }
            if (!document.hidden && chatAcesso && !window.mensagensTelaAtiva) { buscarChamadas(); }
        });

        $(document).on('click', '[data-drawer="mensagens"], a[href*="/mensagens"]', function(e) {
            if (!('Notification' in window) || window.Notification.permission !== 'default') { return; }
            var $el = $(this);
            var href = $el.is('a') ? ($el.attr('href') || '') : '';
            // Só os links que abrem a tela de mensagens: anexos (mensagens_api/arquivo), downloads e nova aba seguem normais.
            if ($el.is('a') && (href.indexOf('mensagens_api') !== -1 || $el.is('[download]') || ($el.attr('target') || '') === '_blank')) { return; }
            var ehLink = href !== '' && href.charAt(0) !== '#' && !$el.is('[data-drawer]');
            if (ehLink && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
                e.preventDefault();
                var navegou = false;
                var ir = function() {
                    if (navegou) { return; }
                    navegou = true;
                    window.location.href = href;
                };
                try {
                    var prom = window.Notification.requestPermission(ir);
                    if (prom && typeof prom.then === 'function') {
                        prom.then(ir, ir);
                    }
                } catch (err) {
                    ir();
                }
            } else {
                try { window.Notification.requestPermission(); } catch (err) {}
            }
        });

        setInterval(buscar, intervalo);
        buscar();
        if (chatAcesso) {
            setInterval(buscarChamadas, 4000);
            buscarChamadas();
        }
    });
</script>
<style>
    /* Sino e envelope: cada item é um card, no mesmo padrão do painel de chamados pendentes. */
    #notificacoes-lista, #mensagens-lista-header { padding: 10px 8px !important; background: #f8fafc; }
    #notificacoes-lista > .notificacoes-vazio, #mensagens-lista-header > .notificacoes-vazio { padding: 30px 20px; color: #94a3b8; font-size: 13px; text-align: center; }
    #notificacoes-lista > .notificacoes-vazio:not(:only-child), #mensagens-lista-header > .notificacoes-vazio:not(:only-child) { display: none; }
    #notificacoes-lista > li > a, #mensagens-lista-header > li > a {
        position: relative; display: flex !important; align-items: flex-start; gap: 12px; margin-bottom: 8px;
        padding: 12px 28px 10px 12px !important; white-space: normal !important;
        background: #fff !important; border-top: 1px solid #e2e8f0 !important; border-right: 1px solid #e2e8f0 !important; border-bottom: 1px solid #e2e8f0 !important;
        border-left-width: 3.5px !important; border-left-style: solid !important; border-left-color: #cbd5e1; border-radius: 8px;
        box-shadow: 0 1px 3px rgba(30, 29, 63, .04); color: #1E1D3F !important; font-size: 13px; line-height: 1.4; text-decoration: none !important;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    #notificacoes-lista > li > a:hover, #notificacoes-lista > li > a:focus, #mensagens-lista-header > li > a:hover, #mensagens-lista-header > li > a:focus {
        padding-left: 12px !important; background: #fff !important; color: #1E1D3F !important; transform: translateY(-1.5px); box-shadow: 0 4px 12px rgba(30, 29, 63, .1); outline: none;
    }
    /* A cor da borda vem do tipo (style do link); as notificações já lidas ficam com a borda neutra. */
    #notificacoes-lista > .chat-remetente > a, #mensagens-lista-header > .chat-remetente > a { border-left-color: #00a65a !important; }
    #notificacoes-lista > .notif-chamado:not(.is-nao-lida):not(.notif-novidade) > a { border-left-color: #cbd5e1 !important; opacity: .85; }
    /* Ponto de "não lida" no canto do card. */
    #notificacoes-lista > .is-nao-lida > a::after, #mensagens-lista-header > .is-nao-lida > a::after { content: ""; position: absolute; top: 14px; right: 12px; width: 8px; height: 8px; border-radius: 50%; background: #1E1D3F; }
    #notificacoes-lista > .chat-remetente.is-nao-lida > a::after, #mensagens-lista-header > .chat-remetente.is-nao-lida > a::after { background: #00a65a; }
    #notificacoes-lista > .js-apontamento-alerta > a::after { background: #f39c12; }
    #notificacoes-lista .notif-chamado-icone, #mensagens-lista-header .notif-chamado-icone { flex-basis: 38px; width: 38px; height: 38px; font-size: 16px; line-height: 38px; }
    #notificacoes-lista .notif-chamado-titulo, #mensagens-lista-header .notif-chamado-titulo { font-size: 13.5px; font-weight: 700; color: #1E1D3F; white-space: normal; line-height: 1.35; }
    #notificacoes-lista .notif-chamado:not(.is-nao-lida):not(.notif-novidade) .notif-chamado-titulo { font-weight: 600; color: #475569; }
    #notificacoes-lista .notif-chamado-texto, #mensagens-lista-header .notif-chamado-texto { -webkit-line-clamp: 3; margin-top: 3px; font-size: 12.5px; color: #64748b; }
    #notificacoes-lista .notif-chamado-meta, #mensagens-lista-header .notif-chamado-meta { margin-top: 7px; padding-top: 5px; border-top: 1px dashed #e2e8f0; font-size: 11.5px; color: #94a3b8; }
    #notificacoes-lista .notif-chamado-meta .fa, #mensagens-lista-header .notif-chamado-meta .fa { margin-right: 2px; }
    /* Itens antigos que chegam prontos em HTML (alunos EAD, cartão recorrente): ícone em círculo. */
    #notificacoes-lista > li:not(.notif-chamado) > a { display: flow-root !important; min-height: 64px; padding-top: 22px !important; }
    #notificacoes-lista > li:not(.notif-chamado) > a > .fa:first-child { float: left; width: 38px; height: 38px; margin: -10px 12px 0 0; border-radius: 50%; background: #eef2f7; font-size: 16px; line-height: 38px; text-align: center; }
    .chat-avatar { padding: 0; object-fit: cover; background: #e4e9ee; }
    .chat-qtd { margin-left: 4px; background: #00a65a; font-size: 10px; vertical-align: 1px; }
    .notif-chamado-icone { flex: 0 0 30px; width: 30px; height: 30px; border-radius: 50%; color: #fff; font-size: 13px; line-height: 30px; text-align: center; }
    .notif-chamado-corpo { flex: 1; min-width: 0; display: block; }
    .notif-chamado-titulo { display: block; font-size: 13px; color: #1E1D3F; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .notif-chamado:not(.is-nao-lida) .notif-chamado-titulo { font-weight: 600; color: #555; }
    .notif-chamado-texto { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-top: 2px; font-size: 12px; line-height: 1.4; color: #666; word-break: break-word; }
    .notif-chamado-meta { display: block; margin-top: 3px; font-size: 11px; color: #999; }
    .notif-avisos { position: fixed; top: 62px; right: 16px; z-index: 1060; display: flex; flex-direction: column; gap: 10px; width: 360px; max-width: calc(100vw - 32px); pointer-events: none; }
    .notif-aviso { position: relative; pointer-events: auto; background: #fff; border-left: 4px solid #3c8dbc; border-radius: 6px; box-shadow: 0 6px 24px rgba(0, 0, 0, .16); animation: notif-aviso-entrar .25s ease-out; transition: opacity .25s, transform .25s; }
    .notif-aviso.is-saindo { opacity: 0; transform: translateX(16px); }
    .notif-aviso-link { display: flex; align-items: flex-start; gap: 10px; padding: 12px 34px 12px 12px; color: inherit; text-decoration: none; }
    .notif-aviso-link:hover, .notif-aviso-link:focus { color: inherit; text-decoration: none; }
    .notif-aviso-link:hover .notif-chamado-titulo { color: #2a2952; text-decoration: underline; }
    .notif-aviso .notif-chamado-titulo { white-space: normal; }
    .notif-aviso-fechar { position: absolute; top: 4px; right: 6px; padding: 0 4px; border: 0; background: transparent; color: #999; font-size: 20px; line-height: 1; cursor: pointer; }
    .notif-aviso-fechar:hover, .notif-aviso-fechar:focus { color: #333; }
    @keyframes notif-aviso-entrar { from { opacity: 0; transform: translateX(16px); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) { .notif-aviso { animation: none; transition: opacity .01s; } .notif-aviso.is-saindo { transform: none; } }
    body.dark-mode #notificacoes-lista, body.dark-mode #mensagens-lista-header { background: #121212 !important; }
    body.dark-mode #notificacoes-lista > .notificacoes-vazio, body.dark-mode #mensagens-lista-header > .notificacoes-vazio { color: #888; }
    body.dark-mode #notificacoes-lista > li > a, body.dark-mode #mensagens-lista-header > li > a { background: #1e1e1e !important; border-top-color: #2d2d2d !important; border-right-color: #2d2d2d !important; border-bottom-color: #2d2d2d !important; color: #f1f5f9 !important; }
    body.dark-mode #notificacoes-lista > li > a:hover, body.dark-mode #mensagens-lista-header > li > a:hover { background: #252525 !important; }
    body.dark-mode #notificacoes-lista > .notif-chamado:not(.is-nao-lida):not(.notif-novidade) > a { border-left-color: #444 !important; }
    body.dark-mode #notificacoes-lista > .is-nao-lida > a::after { background: #9cd7d4; }
    body.dark-mode #notificacoes-lista .notif-chamado-titulo, body.dark-mode #mensagens-lista-header .notif-chamado-titulo { color: #f1f5f9 !important; }
    body.dark-mode #notificacoes-lista .notif-chamado:not(.is-nao-lida):not(.notif-novidade) .notif-chamado-titulo { color: #cbd5e1 !important; }
    body.dark-mode #notificacoes-lista .notif-chamado-texto, body.dark-mode #mensagens-lista-header .notif-chamado-texto { color: #94a3b8 !important; }
    body.dark-mode #notificacoes-lista .notif-chamado-meta, body.dark-mode #mensagens-lista-header .notif-chamado-meta { color: #64748b !important; border-top-color: #2d2d2d !important; }
    body.dark-mode #notificacoes-lista > li:not(.notif-chamado) > a > .fa:first-child { background: #2d2d2d; }
    body.dark-mode .notif-chamado-titulo { color: #f1f5f9 !important; }
    body.dark-mode .notif-chamado:not(.is-nao-lida) .notif-chamado-titulo { color: #cbd5e1 !important; }
    body.dark-mode .notif-chamado-texto { color: #94a3b8 !important; }
    body.dark-mode .notif-chamado-meta { color: #888 !important; }
    body.dark-mode .notif-aviso { background: #1e1e1e !important; box-shadow: 0 6px 24px rgba(0, 0, 0, .5); }
    body.dark-mode .notif-aviso-link:hover .notif-chamado-titulo { color: #9cd7d4 !important; }
    body.dark-mode .notif-aviso-fechar:hover, body.dark-mode .notif-aviso-fechar:focus { color: #f1f5f9; }
    .chamada-recebida-alerta { position: fixed; right: 20px; bottom: 20px; z-index: 1080; width: 320px; max-width: calc(100vw - 32px); padding: 14px; border-radius: 10px; border-left: 4px solid #00a65a; background: #fff; box-shadow: 0 10px 30px rgba(0, 0, 0, .26); animation: apontamento-alerta-entrar .25s ease-out; }
    .chamada-recebida-cab { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
    .chamada-recebida-pulso { flex: 0 0 34px; width: 34px; height: 34px; border-radius: 50%; background: #00a65a; color: #fff; font-size: 16px; line-height: 34px; text-align: center; animation: apontamento-alerta-sino 1.2s ease-in-out infinite; }
    .chamada-recebida-foto { flex: 0 0 38px; width: 38px; height: 38px; border-radius: 50%; object-fit: cover; background: #e4e9ee; }
    .chamada-recebida-meta { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .chamada-recebida-meta strong { font-size: 14px; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .chamada-recebida-meta span { font-size: 12px; color: #64748b; }
    .chamada-recebida-acoes { display: flex; gap: 8px; }
    .chamada-recebida-acoes .btn { flex: 1; }
    body.dark-mode .chamada-recebida-alerta { background: #1e1e1e !important; color: #e6e6e6 !important; box-shadow: 0 10px 30px rgba(0, 0, 0, .55); }
    body.dark-mode .chamada-recebida-meta strong { color: #f1f5f9 !important; }
    body.dark-mode .chamada-recebida-meta span { color: #94a3b8 !important; }
</style>
<!-- Gaveta lateral: os botões do cabeçalho (menos o de tema e o de perfil) abrem o painel correspondente, e as abas trocam entre eles. -->
<script type="text/javascript">
    (function() {
        var gaveta = document.getElementById('header-drawer');
        var fundo = document.getElementById('header-drawer-fundo');
        if (!gaveta || !fundo) { return; }
        var atual = null;
        var origem = null;
        var timerFundo = null;

        function todos(seletor) {
            return Array.prototype.slice.call(document.querySelectorAll(seletor));
        }

        function marcar(nome) {
            var achou = false;
            todos('#header-drawer .header-drawer-painel').forEach(function(painel) {
                var ativo = painel.getAttribute('data-painel') === nome;
                painel.hidden = !ativo;
                achou = achou || ativo;
            });
            todos('#header-drawer .header-drawer-aba').forEach(function(aba) {
                var ativo = aba.getAttribute('data-drawer') === nome;
                aba.setAttribute('aria-selected', ativo ? 'true' : 'false');
                aba.tabIndex = ativo ? 0 : -1;
            });
            return achou;
        }

        // Destaca no cabeçalho o botão do painel aberto (null: nenhum).
        function marcarBotoes(nome) {
            todos('.modern-nav [data-drawer]').forEach(function(botao) {
                var ativo = botao.getAttribute('data-drawer') === nome;
                botao.setAttribute('aria-expanded', ativo ? 'true' : 'false');
                botao.parentNode.classList.toggle('is-drawer-ativo', ativo);
            });
        }

        function abrir(nome) {
            if (!marcar(nome)) { return; }
            marcarBotoes(nome);
            if (!atual) { origem = document.activeElement; }
            atual = nome;
            clearTimeout(timerFundo);
            fundo.hidden = false;
            void fundo.offsetWidth; // aplica o estado inicial antes da transição
            fundo.classList.add('is-visivel');
            gaveta.classList.add('is-aberto');
            gaveta.setAttribute('aria-hidden', 'false');
            document.body.classList.add('header-drawer-aberto');
            try { gaveta.focus({ preventScroll: true }); } catch (e) { gaveta.focus(); }
            try {
                var ev = typeof CustomEvent === 'function' ? new CustomEvent('headerDrawer:aberto', { detail: { nome: nome } }) : null;
                if (ev) { document.dispatchEvent(ev); }
            } catch (e) {}
        }

        function fechar() {
            if (!atual) { return; }
            atual = null;
            marcarBotoes(null); // o painel continua visível enquanto a gaveta desliza para fora
            gaveta.classList.remove('is-aberto');
            gaveta.setAttribute('aria-hidden', 'true');
            fundo.classList.remove('is-visivel');
            timerFundo = setTimeout(function() { fundo.hidden = true; }, 300);
            document.body.classList.remove('header-drawer-aberto');
            if (origem && origem.focus) { origem.focus(); }
        }

        document.addEventListener('click', function(e) {
            var alvo = e.target.closest ? e.target.closest('[data-drawer]') : null;
            if (!alvo) { return; }
            var naGaveta = gaveta.contains(alvo);
            if (!naGaveta && !alvo.closest('.modern-nav')) { return; }
            e.preventDefault();
            var nome = alvo.getAttribute('data-drawer');
            // Botão do cabeçalho do painel que já está aberto: fecha.
            if (!naGaveta && atual === nome) {
                fechar();
                return;
            }
            abrir(nome);
            // A aba de chamados atualiza a lista, como o botão do cabeçalho já faz.
            if (naGaveta && nome === 'chamados') {
                var atualizar = document.getElementById('btn-refresh-chamados');
                if (atualizar) { atualizar.click(); }
            }
        });

        fundo.addEventListener('click', fechar);
        document.getElementById('header-drawer-fechar').addEventListener('click', fechar);
        document.addEventListener('keydown', function(e) {
            // Com a janela de novidade aberta por cima, o Esc fecha só a janela.
            if (atual && (e.key === 'Escape' || e.keyCode === 27) && !document.querySelector('.modal.in')) {
                fechar();
            }
        });

        window.headerDrawer = { abrir: abrir, fechar: fechar };
    })();
</script>
<style>
    .header-drawer-fundo { position: fixed; top: 0; right: 0; bottom: 0; left: 0; z-index: 1037; background: rgba(30, 29, 63, .4); opacity: 0; transition: opacity .3s ease; }
    .header-drawer-fundo.is-visivel { opacity: 1; }
    .header-drawer { position: fixed; top: 0; right: 0; bottom: 0; z-index: 1038; display: flex; flex-direction: column; width: 420px; max-width: 100vw; background: #fff; box-shadow: -8px 0 30px rgba(0, 0, 0, .2); outline: none; visibility: hidden; transform: translateX(100%); transition: transform .3s cubic-bezier(.25, .8, .25, 1), visibility 0s linear .3s; }
    .header-drawer.is-aberto { visibility: visible; transform: none; transition: transform .3s cubic-bezier(.25, .8, .25, 1), visibility 0s; }
    body.header-drawer-aberto { overflow: hidden; }

    .header-drawer-topo { display: flex; align-items: flex-end; gap: 6px; min-height: 50px; padding: 6px 8px 0 10px; background: #1E1D3F; }
    .header-drawer-abas { display: flex; flex: 1; min-width: 0; gap: 2px; overflow-x: auto; }
    .header-drawer-aba { flex: 1 1 0; min-width: 0; display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 7px 2px 8px; border: 0; border-bottom: 3px solid transparent; border-radius: 6px 6px 0 0; background: transparent; color: rgba(255, 255, 255, .7); font-size: 10.5px; font-weight: 600; line-height: 1.2; cursor: pointer; transition: color .2s, background .2s, border-color .2s; }
    .header-drawer-aba span { max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .header-drawer-aba .fa { font-size: 17px; }
    .header-drawer-aba:hover, .header-drawer-aba:focus { color: #fff; background: rgba(255, 255, 255, .06); outline: none; }
    .header-drawer-aba[aria-selected="true"] { color: #fff; background: rgba(255, 255, 255, .1); border-bottom-color: #9CD7D4; }
    .header-drawer-fechar { flex: 0 0 36px; height: 36px; align-self: center; margin-bottom: 3px; border: 0; border-radius: 50%; background: transparent; color: rgba(255, 255, 255, .8); font-size: 26px; line-height: 1; cursor: pointer; transition: background .2s, color .2s; }
    .header-drawer-fechar:hover, .header-drawer-fechar:focus { color: #fff; background: rgba(255, 255, 255, .12); outline: none; }

    .header-drawer-corpo { position: relative; flex: 1; min-height: 0; }
    .header-drawer-painel { position: absolute; top: 0; right: 0; bottom: 0; left: 0; overflow-y: auto; overscroll-behavior: contain; }
    .header-drawer-painel[hidden] { display: none; }

    /* O conteúdo que era dropdown passa a ocupar a gaveta inteira. */
    .header-drawer .header-drawer-painel > .header-drawer-conteudo { position: static !important; display: block !important; float: none !important; width: auto !important; min-width: 0 !important; max-width: none !important; margin: 0 !important; padding: 0 !important; border: 0 !important; border-radius: 0 !important; box-shadow: none !important; overflow: visible !important; background: transparent !important; list-style: none; }
    .header-drawer .header-drawer-conteudo > .modern-dropdown-header { position: sticky; top: 0; z-index: 2; border-radius: 0 !important; }
    .header-drawer .header-drawer-conteudo > .modern-dropdown-footer { position: sticky; bottom: 0; z-index: 2; border-radius: 0 !important; border-top: 1px solid #eef1f4; }
    .header-drawer .header-drawer-conteudo .menu { max-height: none !important; overflow: visible !important; margin: 0; padding: 0 !important; list-style: none; }
    .header-drawer .header-drawer-conteudo .menu > li > a { white-space: normal; }
    .header-drawer .chamados-cards-scroll { max-height: none !important; overflow: visible !important; }

    /* Mensagens: o AdminLTE só estiliza esses itens dentro do dropdown. */
    .header-drawer [data-painel="mensagens"] .menu > li > a { display: block; padding: 10px 16px !important; }
    .header-drawer [data-painel="mensagens"] .menu > li > a::after { content: ""; display: table; clear: both; }
    .header-drawer [data-painel="mensagens"] .menu > li > a > div > img { width: 40px; height: 40px; margin: auto 10px auto auto; }
    .header-drawer [data-painel="mensagens"] .menu > li > a > h4 { position: relative; margin: 0 0 0 50px; padding: 0; font-size: 14px; color: #444; }
    .header-drawer [data-painel="mensagens"] .menu > li > a > h4 > small { position: absolute; top: 0; right: 0; font-size: 10px; color: #999; }
    .header-drawer [data-painel="mensagens"] .menu > li > a > p { margin: 2px 0 0 50px; font-size: 12px; color: #888; }

    /* Botão do cabeçalho cujo painel está aberto. */
    .modern-nav > li.is-drawer-ativo > .modern-dropdown-toggle { background: rgba(30, 29, 63, .08) !important; }
    body.dark-mode .modern-nav > li.is-drawer-ativo > .modern-dropdown-toggle { background: rgba(255, 255, 255, .08) !important; }
    .modern-nav .user-toggle .user-chevron { transition: transform .25s ease; }
    .modern-nav .user-toggle[aria-expanded="true"] .user-chevron { transform: rotate(-90deg); }

    @media (max-width: 480px) {
        .header-drawer { width: 100vw; }
        .header-drawer-aba span { display: none; }
        .header-drawer-aba .fa { font-size: 19px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .header-drawer, .header-drawer.is-aberto { transform: none; transition: visibility 0s; }
        .header-drawer-fundo { transition: none; }
    }

    body.dark-mode .header-drawer { background: #1e1e1e !important; box-shadow: -8px 0 30px rgba(0, 0, 0, .55); }
    body.dark-mode .header-drawer-topo { background: #181818 !important; }
    body.dark-mode .header-drawer-aba[aria-selected="true"] { border-bottom-color: #9cd7d4; }
    body.dark-mode .header-drawer-fundo { background: rgba(0, 0, 0, .6); }
    body.dark-mode .header-drawer .header-drawer-conteudo > .modern-dropdown-footer { border-top-color: #2d2d2d !important; }
    body.dark-mode .header-drawer [data-painel="mensagens"] .menu > li > a > h4 { color: #f1f5f9; }
    body.dark-mode .header-drawer [data-painel="mensagens"] .menu > li > a > p { color: #94a3b8; }
</style>
