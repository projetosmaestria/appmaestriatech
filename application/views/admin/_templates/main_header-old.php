<?php
defined('BASEPATH') or exit('No direct script access allowed');

?>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!-- OU Font Awesome 5 (mais moderno) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<header class="main-header">
    <a href="<?php echo site_url('dashboard'); ?>" class="logo">
        <img src="<?php echo base_url('assets/images/login/page-login-logo.png?v=' . time()) ?>" class="logo-image" alt="Logo">
    </a>

    <nav class="navbar navbar-static-top modern-navbar" role="navigation">
        <!-- Sidebar toggle button -->
        <div class="navbar-left">
            <a href="#" class="sidebar-toggle modern-toggle" data-toggle="offcanvas" role="button"></a>
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
                                    <small class="modern-user-since"><?php echo lang('header_member_since'); ?> <?php echo date('d-m-Y', $user_login['created_on']); ?></small>
                                </p>
                            </li>
                            <li class="user-body modern-user-body">
                                <div class="row">
                                    <div class="col-xs-4 text-center">
                                        <a href="#" class="modern-user-stat"><?php echo lang('header_followers'); ?></a>
                                    </div>
                                    <div class="col-xs-4 text-center">
                                        <a href="#" class="modern-user-stat"><?php echo lang('header_sales'); ?></a>
                                    </div>
                                    <div class="col-xs-4 text-center">
                                        <a href="#" class="modern-user-stat"><?php echo lang('header_friends'); ?></a>
                                    </div>
                                </div>
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
    .main-header {
        background: #ffffff !important;
        border-bottom: 1px solid rgba(30, 29, 63, 0.08) !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
    }

    .logo {
        background: #ffffff !important;
        border-right: 1px solid rgba(30, 29, 63, 0.06) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 8px 15px !important;
        height: 60px !important;
        width: 260px !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        position: relative;
        overflow: hidden;
    }

    .logo::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg,
                transparent,
                rgba(156, 215, 212, 0.1),
                rgba(30, 29, 63, 0.05),
                transparent);
        transition: left 0.8s ease;
    }

    .logo:hover {
        background: #fcfcfc !important;
        box-shadow: inset 0 0 0 1px rgba(156, 215, 212, 0.3) !important;
    }

    .logo:hover::before {
        left: 100%;
    }

    .logo-image {
        max-height: 40px !important;
        max-width: 180px !important;
        width: auto !important;
        height: auto !important;
        object-fit: contain !important;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.02));
    }

    .logo:hover .logo-image {
        transform: scale(1.08) translateY(-1px);
        filter: drop-shadow(0 8px 12px rgba(30, 29, 63, 0.1));
    }

    .modern-navbar {
        background: transparent !important;
        min-height: 50px !important;
    }

    .navbar-left {
        display: flex;
        align-items: center;
        height: 50px;
    }

    .modern-toggle {
        padding: 15px !important;
        margin: 0 !important;
        background: transparent !important;
        border: none !important;
        transition: all 0.3s ease;
        position: relative;
        display: flex;
        flex-direction: column;
        width: 50px;
        height: 50px;
        justify-content: center;
        align-items: center;
        color: #1E1D3F !important;
    }

    .modern-toggle .toggle-bar {
        display: block;
        width: 20px;
        height: 2px;
        background: #1E1D3F !important;
        margin: 3px 0;
        transition: all 0.3s ease;
        border-radius: 1px;
    }

    .modern-toggle.active .toggle-bar:nth-child(1) {
        transform: rotate(45deg) translate(5px, 5px);
    }

    .modern-toggle.active .toggle-bar:nth-child(2) {
        opacity: 0;
    }

    .modern-toggle.active .toggle-bar:nth-child(3) {
        transform: rotate(-45deg) translate(5px, -5px);
    }

    .modern-toggle:hover .toggle-bar {
        background: #9CD7D4 !important;
    }

    .modern-toggle:hover {
        transform: none !important;
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

    .modern-logout-btn:hover {
        background: #1E1D3F !important;
        color: white !important;
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
</style>