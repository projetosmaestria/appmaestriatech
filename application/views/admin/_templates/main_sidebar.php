<?php
defined('BASEPATH') or exit('No direct script access allowed');

?>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <section class="sidebar">
        <!-- Search Form -->
        <?php if ($admin_prefs['sidebar_form'] == TRUE): ?>
            <div class="px-3 pb-3">
                <form action="#" method="get" class="sidebar-form">
                    <div class="input-group input-group-sm">
                        <input type="text" name="q" class="form-control form-control-sm modern-input" placeholder="<?php echo lang('menu_search'); ?>...">
                        <div class="input-group-append">
                            <button type="submit" name="search" id="search-btn" class="btn btn-sm btn-light">
                                <i class="fa fa-search text-muted"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Navigation Menu -->
        <nav class="mt-2">
            <?php echo $menu_sidebar; ?>
        </nav>
    </section>
</aside>

<style>
    .main-sidebar {
        background: #ffffff !important;
        box-shadow: 2px 0 15px rgba(30, 29, 63, 0.03);
        border-right: 1px solid rgba(30, 29, 63, 0.06);
    }

    .brand-link {
        background: rgba(255, 255, 255, 0.05) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        backdrop-filter: blur(10px);
    }

    .user-panel {
        border-bottom: 1px solid rgba(30, 29, 63, 0.1);
        padding-bottom: 1rem !important;
    }

    .user-panel .image img {
        width: 45px;
        height: 45px;
        border: 2px solid rgba(30, 29, 63, 0.2);
        transition: all 0.3s ease;
    }

    .user-panel:hover .image img {
        border-color: #9CD7D4;
        transform: scale(1.05);
    }

    .user-panel .info {
        color: #1E1D3F !important;
    }

    .user-panel .info a {
        color: #1E1D3F !important;
    }

    .user-panel .info a:hover {
        color: #9CD7D4 !important;
    }

    .modern-select-inverted {
        background: rgba(30, 29, 63, 0.05) !important;
        border: none !important;
        border-radius: 8px !important;
        color: #1E1D3F !important;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(30, 29, 63, 0.05);
    }

    .modern-select-inverted:hover {
        background: rgba(30, 29, 63, 0.1) !important;
        box-shadow: 0 4px 10px rgba(30, 29, 63, 0.1);
        transform: translateY(-1px);
    }

    .modern-select-inverted:focus {
        background: white !important;
        border: none !important;
        box-shadow: 0 0 0 2px rgba(156, 215, 212, 0.5) !important;
        color: #1E1D3F !important;
    }

    .modern-select-inverted option {
        background: white;
        color: #1E1D3F;
        padding: 10px;
        border-bottom: 1px solid #ecf0f1;
    }

    .modern-select-inverted option:checked {
        background: white !important;
        color: #1E1D3F !important;
    }

    .modern-select-inverted option:hover {
        background: #f8f9fa !important;
        color: #1E1D3F !important;
    }

    .modern-input {
        background: rgba(30, 29, 63, 0.05) !important;
        border: 1px solid rgba(30, 29, 63, 0.1) !important;
        border-radius: 8px !important;
        color: #1E1D3F !important;
        transition: all 0.3s ease;
    }

    .modern-input::placeholder {
        color: rgba(30, 29, 63, 0.4) !important;
    }

    .modern-input:focus {
        background: rgba(30, 29, 63, 0.08) !important;
        border-color: #9CD7D4 !important;
        box-shadow: 0 0 0 0.2rem rgba(156, 215, 212, 0.25) !important;
    }

    .sidebar-menu {
        padding: 10px 10px !important;
    }

    .sidebar-menu li.header {
        color: rgba(30, 29, 63, 0.6) !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        padding: 15px 15px 5px !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: transparent !important;
        border-bottom: 1px solid rgba(30, 29, 63, 0.08);
        margin-bottom: 10px;
    }

    .sidebar-menu>li>a {
        border-radius: 8px !important;
        margin: 2px 0 !important;
        padding: 12px 15px !important;
        color: #1E1D3F !important;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        font-weight: 500;
    }

    .sidebar-menu>li>a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 3px;
        background: #9CD7D4;
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }

    .sidebar-form {
        border: none !important;
    }

    .sidebar-menu>li>a:hover {
        background: rgba(156, 215, 212, 0.1) !important;
        color: #1E1D3F !important;
        transform: translateX(5px);
    }

    .sidebar-menu>li>a:hover::before {
        transform: scaleY(1);
    }

    .sidebar-menu>li.active>a {
        background: rgba(156, 215, 212, 0.2) !important;
        color: #1E1D3F !important;
        box-shadow: 0 4px 10px rgba(156, 215, 212, 0.2);
        font-weight: 600;
    }

    .sidebar-menu>li.active>a::before {
        transform: scaleY(1);
        background: #1E1D3F;
    }

    .sidebar-menu li>a .fa-angle-left,
    .sidebar-menu li>a .fa-angle-down {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        color: #1E1D3F !important;
    }

    .sidebar-menu li>a:hover .fa-angle-left,
    .sidebar-menu li>a:hover .fa-angle-down {
        transform: rotate(-90deg) scale(1.1);
        color: #9CD7D4 !important;
    }

    .sidebar-menu li.active>a .fa-angle-left {
        transform: rotate(-90deg) !important;
        color: #1E1D3F !important;
    }

    .sidebar-menu li>a>.fa,
    .sidebar-menu li>a>.fas,
    .sidebar-menu li>a>.far {
        width: 20px;
        margin-right: 10px;
        text-align: center;
        transition: transform 0.3s ease;
        color: #1E1D3F !important;
    }

    .sidebar-menu li>a:hover>.fa,
    .sidebar-menu li>a:hover>.fas,
    .sidebar-menu li>a:hover>.far {
        transform: scale(1.1);
        color: #9CD7D4 !important;
    }

    .treeview-menu {
        background: rgba(30, 29, 63, 0.02) !important;
        border-radius: 0 0 8px 8px;
        margin: 0 10px 5px 10px !important;
        border-left: 2px solid rgba(156, 215, 212, 0.3);
    }

    .treeview-menu>li>a {
        padding: 10px 15px 10px 35px !important;
        color: #1E1D3F !important;
        border-radius: 6px !important;
        margin: 1px 0 !important;
        transition: all 0.3s ease;
        font-size: 14px;
    }

    .treeview-menu>li>a:hover {
        background: rgba(156, 215, 212, 0.1) !important;
        color: #1E1D3F !important;
        padding-left: 40px !important;
    }

    .treeview-menu>li.active>a {
        color: #1E1D3F !important;
        background: rgba(156, 215, 212, 0.2) !important;
        font-weight: 600;
        border-right: 3px solid #9CD7D4;
    }

    .pull-right-container {
        background: rgba(30, 29, 63, 0.05);
        border-radius: 12px;
        padding: 2px 8px;
        transition: all 0.3s ease;
        color: #1E1D3F !important;
    }

    .sidebar-menu li>a:hover .pull-right-container {
        background: rgba(156, 215, 212, 0.2);
    }

    .label.pull-right {
        background: #9CD7D4 !important;
        color: #1E1D3F !important;
    }

    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: rgba(30, 29, 63, 0.05);
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(30, 29, 63, 0.2);
        border-radius: 3px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: #9CD7D4;
    }

    .sidebar-menu .badge {
        background: #9CD7D4 !important;
        color: #1E1D3F !important;
    }

    .user-panel .info p {
        color: #1E1D3F !important;
    }

    .sidebar-mini.sidebar-collapse .main-sidebar {
        width: 65px !important;
    }

    .sidebar-mini.sidebar-collapse .sidebar {
        overflow-x: hidden !important;
    }

    .sidebar-mini.sidebar-collapse .sidebar-menu>li>a {
        padding: 12px 0 !important;
        text-align: center !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 2px 8px !important;
        width: auto !important;
    }

    .sidebar-mini.sidebar-collapse .sidebar-menu>li>a>.fa,
    .sidebar-mini.sidebar-collapse .sidebar-menu>li>a>.fas,
    .sidebar-mini.sidebar-collapse .sidebar-menu>li>a>.far,
    .sidebar-mini.sidebar-collapse .sidebar-menu>li>a>.ion {
        margin-right: 0 !important;
        width: 28px !important;
        height: 28px !important;
        font-size: 18px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .sidebar-mini.sidebar-collapse .sidebar-menu>li>a>span {
        display: none !important;
    }

    .sidebar-mini.sidebar-collapse .sidebar-menu>li>a .pull-right-container {
        display: none !important;
    }

    .sidebar-mini.sidebar-collapse .sidebar-menu li.header {
        text-align: center !important;
        padding: 15px 0 5px !important;
        font-size: 10px !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        cursor: pointer !important;
    }

    .sidebar-mini.sidebar-collapse .sidebar-menu>li>a {
        position: relative !important;
    }

    .sidebar-mini.sidebar-collapse .sidebar-menu>li>a:hover::after {
        content: attr(title);
        position: absolute;
        left: 70px;
        top: 50%;
        transform: translateY(-50%);
        background: #1E1D3F;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 13px;
        white-space: nowrap;
        z-index: 9999;
        box-shadow: 0 4px 15px rgba(30, 29, 63, 0.2);
        pointer-events: none;
        font-weight: 500;
    }

    .sidebar-mini.sidebar-collapse .sidebar-menu>li>a:hover::before {
        content: '';
        position: absolute;
        left: 62px;
        top: 50%;
        transform: translateY(-50%);
        border-width: 6px 6px 6px 0;
        border-style: solid;
        border-color: transparent #1E1D3F transparent transparent;
        z-index: 9999;
        pointer-events: none;
    }

    .sidebar-mini.sidebar-collapse .sidebar-form {
        display: none !important;
    }

    .sidebar-mini.sidebar-collapse .user-panel {
        display: flex !important;
        justify-content: center !important;
        padding: 15px 0 !important;
        margin: 0 !important;
    }

    .sidebar-mini.sidebar-collapse .user-panel .image {
        margin: 0 !important;
        padding: 0 !important;
    }

    .sidebar-mini.sidebar-collapse .user-panel .image img {
        width: 40px !important;
        height: 40px !important;
        margin: 0 auto !important;
    }

    .sidebar-mini.sidebar-collapse .user-panel .info {
        display: none !important;
    }

    .sidebar-mini.sidebar-collapse .treeview-menu {
        display: none !important;
    }

    .sidebar-mini.sidebar-collapse .sidebar-menu>li>a:hover {
        background: rgba(156, 215, 212, 0.15) !important;
        transform: scale(1.08) !important;
        border-radius: 12px !important;
    }

    .sidebar-mini.sidebar-collapse .sidebar-menu>li.active>a {
        background: rgba(156, 215, 212, 0.25) !important;
        box-shadow: 0 2px 8px rgba(156, 215, 212, 0.3) !important;
    }

    .sidebar-mini.sidebar-collapse .main-sidebar,
    .sidebar-mini.sidebar-collapse .sidebar-menu>li>a,
    .sidebar-mini.sidebar-collapse .sidebar-menu>li>a>i {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .sidebar-mini.sidebar-collapse .sidebar-menu {
        padding: 10px 5px !important;
    }

    .sidebar-mini.sidebar-collapse .sidebar-menu>li>a::before {
        width: 2px !important;
        left: 0 !important;
        background: #9CD7D4 !important;
    }

    .sidebar-mini.sidebar-collapse .brand-link {
        display: flex !important;
        justify-content: center !important;
        padding: 12px 0 !important;
    }

    .sidebar-mini.sidebar-collapse .brand-link .brand-image {
        margin: 0 auto !important;
    }

    .sidebar-mini.sidebar-collapse .brand-text {
        display: none !important;
    }

    .sidebar-mini.sidebar-collapse .sidebar-menu .treeview-menu {
        display: none !important;
    }
</style>

<script>
    $(document).ready(function() {
        if (!$('body').hasClass('sidebar-mini')) {
            $('body').addClass('sidebar-mini');
        }

        // Para garantir que os tooltips funcionem com títulos
        $('.sidebar-mini.sidebar-collapse .sidebar-menu > li > a').each(function() {
            var $this = $(this);
            var text = $this.find('span').first().text().trim();
            if (text && !$this.attr('title')) {
                $this.attr('title', text);
            }
        });

        $(document).ajaxComplete(function() {
            $('.sidebar-mini.sidebar-collapse .sidebar-menu > li > a').each(function() {
                var $this = $(this);
                var text = $this.find('span').first().text().trim();
                if (text && !$this.attr('title')) {
                    $this.attr('title', text);
                }
            });
        });
    });
</script>