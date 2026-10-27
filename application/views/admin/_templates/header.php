<?php
defined('BASEPATH') or exit('No direct script access allowed');

?>
<!doctype html>
<html lang="<?php echo $lang; ?>">

<head>
        <meta charset="<?php echo $charset; ?>">
        <title><?php echo $title; ?></title>
        <?php if ($mobile === FALSE): ?>
                <!--[if IE 8]>
            <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <![endif]-->
        <?php else: ?>
                <meta name="HandheldFriendly" content="true">
        <?php endif; ?>
        <?php if ($mobile == TRUE && $mobile_ie == TRUE): ?>
                <meta http-equiv="cleartype" content="on">
        <?php endif; ?>
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <meta name="google" content="notranslate">
        <meta name="robots" content="noindex, nofollow">
        <?php if ($mobile == TRUE && $ios == TRUE): ?>
                <meta name="apple-mobile-web-app-capable" content="yes">
                <meta name="apple-mobile-web-app-status-bar-style" content="black">
                <meta name="apple-mobile-web-app-title" content="<?php echo $title; ?>">
        <?php endif; ?>
        <?php if ($mobile == TRUE && $android == TRUE): ?>
                <meta name="mobile-web-app-capable" content="yes">
        <?php endif; ?>
        <link rel="icon" href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAqElEQVRYR+2WYQ6AIAiF8W7cq7oXd6v5I2eYAw2nbfivYq+vtwcUgB1EPPNbRBR4Tby2qivErYRvaEnPAdyB5AAi7gCwvSUeAA4iis/TkcKl1csBHu3HQXg7KgBUegVA7UW9AJKeA6znQKULoDcDkt46bahdHtZ1Por/54B2xmuz0uwA3wFfd0Y3gDTjhzvgANMdkGb8yAyY/ro1d4H2y7R1DuAOTHfgAn2CtjCe07uwAAAAAElFTkSuQmCC">
        <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,700italic">
        <link rel="stylesheet" href="<?php echo base_url('assets/xkey/reset.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url($frameworks_dir . '/bootstrap/css/bootstrap.min.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url($frameworks_dir . '/font-awesome/css/font-awesome.min.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url($frameworks_dir . '/ionicons/css/ionicons.min.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url($frameworks_dir . '/adminlte/css/adminlte.min.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url($frameworks_dir . '/adminlte/css/skins/skin-blue.min.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url($plugins_dir . '/datatables.net-bs/css/dataTables.bootstrap.min.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url($plugins_dir . '/treeview/treeview.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url($plugins_dir . '/treejs/treejs.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url($plugins_dir . '/bootstrap-toggle/bootstrap-toggle.min.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url($plugins_dir . '/star-rating/star-rating.min.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url($plugins_dir . '/datatables.net/css/select.dataTables.min.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url($plugins_dir . '/fastselect/fastselect.min.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url($frameworks_dir . '/morris.js/morris.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url($plugins_dir . '/audioplayer/audioplayer.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url($plugins_dir . '/icheck/css/red.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url($plugins_dir . '/icheck/css/green.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url($plugins_dir . '/icheck/css/yellow.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url($plugins_dir . '/icheck/css/orange.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url($plugins_dir . '/bootstrap-treeview/bootstrap-treeview.css'); ?>">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/v4-shims.min.css">
        <?php if ($this->router->fetch_class() == 'agenda' && ($this->router->fetch_method() == 'index')): ?>
                <link rel="stylesheet" href="<?php echo base_url($frameworks_dir . '/fullcalendar/dist/fullcalendar.min.css'); ?>">
                <link rel="stylesheet" media="print" href="<?php echo base_url($frameworks_dir . '/fullcalendar/dist/fullcalendar.print.min.css'); ?>">
        <?php endif; ?>
        <?php if (in_array(strtolower($this->router->fetch_class()), array('treinamentos', 'treinamentos_admin'))): ?>
                <link rel="stylesheet" href="<?php echo base_url('assets/xkey/treinamentos.css?v=2.0'); ?>">
        <?php endif; ?>
        <?php if ($mobile === FALSE && $admin_prefs['transition_page'] == TRUE): ?>
                <link rel="stylesheet" href="<?php echo base_url($plugins_dir . '/animsition/animsition.min.css'); ?>">
        <?php endif; ?>
        <?php if (($this->router->fetch_class() == 'groups' && ($this->router->fetch_method() == 'create' or $this->router->fetch_method() == 'edit')) || ($this->router->fetch_class() == 'niveis' && ($this->router->fetch_method() == 'create' or $this->router->fetch_method() == 'edit')) || ($this->router->fetch_class() == 'app_livros' && ($this->router->fetch_method() == 'create' or $this->router->fetch_method() == 'edit'))): ?>
                <link rel="stylesheet" href="<?php echo base_url($plugins_dir . '/colorpickersliders/colorpickersliders.min.css'); ?>">
        <?php endif; ?>
        <link rel="stylesheet" href="<?php echo base_url($frameworks_dir . '/domprojects/css/dp.min.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url('assets/xkey/estilo.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url('assets/xkey/modern-theme.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url('assets/xkey/dark-theme.css'); ?>">
        <script src="<?php echo base_url('assets/xkey/theme-toggle.js'); ?>"></script>
        <link href="<?php echo base_url($plugins_dir . '/datetimepicker/build/css/bootstrap-datetimepicker.min.css'); ?>" rel="stylesheet">

        <?php if ($this->router->fetch_class() == 'dashboard'): ?>
                <link rel="stylesheet" type="text/css" href="<?php echo base_url($frameworks_dir . '/jquery.countdown/css/jquery.countdown.css'); ?>">
                <link rel="stylesheet" type="text/css" href="<?php echo base_url($plugins_dir . '/daterangepicker/daterangepicker.css'); ?>">
        <?php endif; ?>

        <?php if ($this->router->fetch_class() == 'iugu' && $this->router->fetch_method() == 'cartao_recorrencia'): ?>
                <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/xkey/iugu.css'); ?>">
        <?php endif; ?>

        <?php if ($this->router->fetch_class() == 'cad_prod' && ($this->router->fetch_method() == 'create' || $this->router->fetch_method() == 'edit')): ?>
                <link rel="stylesheet" type="text/css" href="<?php echo base_url($plugins_dir . '/fileinput/fileinput.min.css'); ?>">
        <?php endif; ?>
        <?php if ($this->router->fetch_class() == 'oportunidades'): ?>
                <link rel="stylesheet" type="text/css" href="<?php echo base_url($plugins_dir . '/ekko-lightbox/ekko-lightbox.css'); ?>">
        <?php endif; ?>

        <?php if (
                $this->router->fetch_class() == 'oportunidades' ||
                $this->router->fetch_class() == 'cad_prod'
        ): ?>
                <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/xkey/estilo_multiselect.css'); ?>">
        <?php endif; ?>

        <?php if (
                $this->router->fetch_class() == 'kanban'
        ): ?>
                <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/xkey/kanban.css'); ?>">
                <link rel="stylesheet" type="text/css" href="<?php echo base_url($plugins_dir . '/fileinput/fileinput.min.css'); ?>">
        <?php endif; ?>

        <?php if ($mobile === FALSE): ?>
                <!--[if lt IE 9]>
            <script src="<?php echo base_url($plugins_dir . '/html5shiv/html5shiv.min.js'); ?>"></script>
            <script src="<?php echo base_url($plugins_dir . '/respond/respond.min.js'); ?>"></script>
        <![endif]-->
        <?php endif; ?>
        
</head>

<body class="hold-transition skin-blue fixed sidebar-mini">
        <?php if ($mobile === FALSE && $admin_prefs['transition_page'] == TRUE): ?>
                <div class="wrapper animsition">
                <?php else: ?>
                        <div class="wrapper">
                        <?php endif; ?>