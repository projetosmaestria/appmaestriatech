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
                <?php if ($admin_prefs['messages_menu'] == TRUE): ?>
                    <!-- Messages -->
                    <li class="dropdown messages-menu modern-dropdown">
                        <a href="#" class="dropdown-toggle modern-dropdown-toggle" data-toggle="dropdown">
                            <div class="nav-icon-wrapper">
                                <i class="fa fa-envelope-o"></i>
                                <?php if ($messages_count > 0): ?>
                                    <span class="label label-success modern-badge"><?php echo $messages_count ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                        <ul class="dropdown-menu modern-dropdown-menu">
                            <li class="header modern-dropdown-header"><?php echo lang('header_you_have'); ?> <?php echo $messages_count ?> <?php echo lang('header_message'); ?></li>
                            <?php if ($messages_count > 0): ?>
                                <li>
                                    <ul class="menu modern-menu-list">
                                        <?php
                                        foreach ($messages as $value) :
                                            echo $value;
                                        endforeach;
                                        ?>
                                    </ul>
                                </li>
                            <?php endif ?>
                            <li class="footer modern-dropdown-footer">
                                <a href="<?php echo base_url('contatos_interno/index') ?>" class="modern-dropdown-link">
                                    <?php echo lang('header_view_all'); ?>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php echo $header_alert_file_install; ?>

                <?php if ($admin_prefs['notifications_menu'] == TRUE): ?>
                    <!-- Notifications -->
                    <li class="dropdown notifications-menu modern-dropdown">
                        <a href="#" class="dropdown-toggle modern-dropdown-toggle" data-toggle="dropdown">
                            <div class="nav-icon-wrapper">
                                <i class="fa fa-bell-o"></i>
                                <?php if ($notifications_count > 0): ?>
                                    <span class="label label-warning modern-badge"><?php echo $notifications_count ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                        <ul class="dropdown-menu modern-dropdown-menu">
                            <li class="header modern-dropdown-header"><?php echo lang('header_you_have'); ?> <?php echo $notifications_count ?> <?php echo lang('header_notification'); ?></li>
                            <?php if ($notifications_count > 0): ?>
                                <li>
                                    <ul class="menu modern-menu-list">
                                        <?php
                                        foreach ($notifications as $value) :
                                            echo $value;
                                        endforeach;
                                        ?>
                                    </ul>
                                </li>
                            <?php endif ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($admin_prefs['tasks_menu'] == TRUE): ?>
                    <!-- Tasks -->
                    <li class="dropdown tasks-menu modern-dropdown">
                        <a href="#" class="dropdown-toggle modern-dropdown-toggle" data-toggle="dropdown">
                            <div class="nav-icon-wrapper">
                                <i class="fa fa-flag-o"></i>
                                <span class="label label-danger modern-badge">0</span>
                            </div>
                        </a>
                        <ul class="dropdown-menu modern-dropdown-menu">
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
                        <a href="#" class="dropdown-toggle modern-dropdown-toggle" data-toggle="dropdown" title="Chamados Pendentes">
                            <div class="nav-icon-wrapper">
                                <i class="fa fa-ticket"></i>
                                <span class="label label-warning modern-badge" id="chamados-pendentes-badge" style="<?php echo (isset($chamados_pendentes_count) && $chamados_pendentes_count > 0) ? '' : 'display: none;'; ?>">
                                    <?php echo isset($chamados_pendentes_count) ? $chamados_pendentes_count : 0; ?>
                                </span>
                            </div>
                        </a>
                        <ul class="dropdown-menu modern-dropdown-menu chamados-dropdown-menu">
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
                    </li>

                <?php if ($admin_prefs['user_menu'] == TRUE): ?>
                    <!-- User Account -->
                    <li class="dropdown user user-menu modern-dropdown">
                        <a href="#" class="dropdown-toggle modern-dropdown-toggle user-toggle" data-toggle="dropdown">
                            <div class="user-info-wrapper">
                                <img src="<?php echo base_url($avatar_dir . '/m_001.png'); ?>" class="user-image modern-user-image" alt="User Image">
                                <span class="hidden-xs modern-username"><?php echo $user_login['firstname'] . " " . $user_login['lastname']; ?></span>
                                <i class="fa fa-chevron-down user-chevron"></i>
                            </div>
                        </a>
                        <ul class="dropdown-menu modern-dropdown-menu user-dropdown-menu">
                            <li class="user-header modern-user-header">
                                <img src="<?php echo base_url($avatar_dir . '/m_001.png'); ?>" class="img-circle modern-user-avatar" alt="User Image">
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