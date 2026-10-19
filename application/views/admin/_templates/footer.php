<?php
defined('BASEPATH') OR exit('No direct script access allowed');

?>

            <footer class="main-footer">
                <div class="pull-right hidden-xs">
                    <b><?php echo lang('footer_version'); ?></b> 1.0.0
                </div>
            </footer>
        </div>

        <script src="<?php echo base_url($frameworks_dir . '/jquery/jquery.min.js'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/jquery.mask/jquery.mask.min.js'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/jquery.mask/jquery.mask2.min.js'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/jquery.mask/jquery.maskmoney.min.js'); ?>"></script>
        <script src="<?php echo base_url('assets/xkey/datatable_funcoes.js?v=0.2'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/treejs/treejs.js'); ?>"></script>
        <script src="<?php echo base_url('assets/xkey/moment.min.js'); ?>"></script>
        <script src="<?php echo base_url('assets/xkey/pt-br.js'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/datetimepicker/build/js/bootstrap-datetimepicker.min.js'); ?>"></script>
        <script src="<?php echo base_url('assets/xkey/funcoes.js?v=3.7'); ?>"></script>
        <script src="<?php echo base_url($frameworks_dir . '/jquery-ui/jquery-ui.min.js'); ?>"></script>
        <script src="<?php echo base_url($frameworks_dir . '/bootstrap/js/bootstrap.min.js'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/slimscroll/slimscroll.min.js'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/datatables.net/js/jquery.dataTables.min.js'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/datatables.net-bs/js/dataTables.bootstrap.min.js'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/treeview/treeview.js'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/bootstrap-toggle/bootstrap-toggle.min.js'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/star-rating/star-rating.min.js'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/datatables.net/js/jquery.dataTables.responsive.min.js'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/datatables.net-bs/js/dataTables.responsive.min.js'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/datatables.net/js/dataTables.select.min.js'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/fastselect/fastselect_standalone.min.js'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/jquery.number/jquery.number.js'); ?>"></script>
        <script src="<?php echo base_url($frameworks_dir . '/raphael/raphael.min.js'); ?>"></script>
        <script src="<?php echo base_url($frameworks_dir . '/morris.js/morris.min.js'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/audioplayer/audioplayer.js'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/icheck/js/icheck.min.js'); ?>"></script>
        <script src="<?php echo base_url($frameworks_dir . '/chart.js/Chart.js'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/bootstrap-treeview/bootstrap-treeview.js'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/jquery.serialize/serializejson.js'); ?>"></script>

<?php if ($mobile == TRUE): ?>
        <script src="<?php echo base_url($plugins_dir . '/fastclick/fastclick.min.js'); ?>"></script>
<?php endif; ?>
<?php if ($admin_prefs['transition_page'] == TRUE): ?>
        <script src="<?php echo base_url($plugins_dir . '/animsition/animsition.min.js'); ?>"></script>
<?php endif; ?>
<?php if ($this->router->fetch_class() == 'users' && ($this->router->fetch_method() == 'create' OR $this->router->fetch_method() == 'edit' OR $this->router->fetch_method() == 'profile')): ?>
        <script src="<?php echo base_url($plugins_dir . '/pwstrength/pwstrength.min.js'); ?>"></script>
<?php endif; ?>
<?php if (($this->router->fetch_class() == 'config_email' && ($this->router->fetch_method() == 'edit')) || ($this->router->fetch_class() == 'app_unidades_livro' && ($this->router->fetch_method() == 'edit' || $this->router->fetch_method() == 'create')) || ($this->router->fetch_class() == 'app_exercicios' && ($this->router->fetch_method() == 'edit' || $this->router->fetch_method() == 'create'))): ?>
        <script src="<?php echo base_url($plugins_dir . '/ckeditor/ckeditor.js'); ?>"></script>
<?php endif; ?>
<?php if (($this->router->fetch_class() == 'groups' && ($this->router->fetch_method() == 'create' OR $this->router->fetch_method() == 'edit')) || ($this->router->fetch_class() == 'niveis' && ($this->router->fetch_method() == 'create' OR $this->router->fetch_method() == 'edit')) || ($this->router->fetch_class() == 'app_livros' && ($this->router->fetch_method() == 'create' OR $this->router->fetch_method() == 'edit'))): ?>
        <script src="<?php echo base_url($plugins_dir . '/tinycolor/tinycolor.min.js'); ?>"></script>
        <script src="<?php echo base_url($plugins_dir . '/colorpickersliders/colorpickersliders.min.js'); ?>"></script>
<?php endif; ?>
<?php if ($this->router->fetch_class() == 'dashboard' || $this->router->fetch_class() == 'kanban'): ?>
        <script type="text/javascript">
            $('.connectedSortable').sortable({
                placeholder         : 'sort-highlight',
                connectWith         : '.connectedSortable',
                handle              : '.box-header, .nav-tabs',
                forcePlaceholderSize: true,
                zIndex              : 999999
            });
            $('.connectedSortable .box-header, .connectedSortable .nav-tabs-custom').css('cursor', 'move');
        </script>
        <script src="<?php echo base_url($frameworks_dir . '/jquery-knob/js/jquery.knob.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url($frameworks_dir . '/jquery.countdown/js/jquery.plugin.min.js'); ?>"></script> 
        <script type="text/javascript" src="<?php echo base_url($frameworks_dir . '/jquery.countdown/js/jquery.countdown.min.js'); ?>"></script> 
        <script type="text/javascript" src="<?php echo base_url($frameworks_dir . '/jquery.countdown/js/jquery.countdown-pt-BR.js'); ?>"></script> 
        <script type="text/javascript" src="<?php echo base_url($plugins_dir . '/daterangepicker/daterangepicker.min.js'); ?>"></script> 
        <script src="<?php echo base_url('assets/xkey/graficos.js?v=0.1'); ?>"></script>
<?php endif; ?>

<?php if ($this->router->fetch_class() == 'kanban'): ?>
    <script src="<?php echo base_url('assets/xkey/kanban.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url($plugins_dir . '/fileinput/fileinput.min.js'); ?>"></script> 
    <script type="text/javascript" src="<?php echo base_url($plugins_dir . '/fileinput/pt-BR.js'); ?>"></script> 
    <script type="text/javascript" src="<?php echo base_url($plugins_dir . '/fileinput/sortable.min.js'); ?>"></script> 
    <script type="text/javascript" src="<?php echo base_url($frameworks_dir . '/jquery.countup/ez.countimer.min.js'); ?>"></script> 

        <?php if($this->router->fetch_method() == 'metricas'): ?>
            <script type="module" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js" integrity="sha512-ZwR1/gSZM3ai6vCdI+LVF1zSq/5HznD3ZSTk7kajkaj4D292NLuduDCO1c/NT8Id+jE58KYLKT7hXnbtryGmMg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
            <script type="module" src="<?php echo base_url('assets/xkey/kanban_metricas.js'); ?>"></script>
        <?php endif; ?>
<?php endif; ?>

<?php if ($this->router->fetch_class() == 'agenda' && ($this->router->fetch_method() == 'index')): ?>
        <!-- <script src="<?php echo base_url($frameworks_dir . '/jquery-ui/jquery-ui.min.js'); ?>"></script> -->
        <script src="<?php echo base_url($frameworks_dir . '/moment/moment.js'); ?>"></script>
        <script src="<?php echo base_url($frameworks_dir . '/fullcalendar/dist/fullcalendar.min.js'); ?>"></script>
        <script src="<?php echo base_url($frameworks_dir . '/fullcalendar/dist/locale/pt-br.js'); ?>"></script>
<?php endif; ?>

<?php if ($this->router->fetch_class() == 'iugu' && $this->router->fetch_method() == 'cartao_recorrencia'): ?>
        <script type="text/javascript" src="<?php echo base_url('assets/xkey/iugu.v2.js'); ?>"></script> 
        <script type="text/javascript" src="<?php echo base_url('assets/xkey/formatter.min.js'); ?>"></script> 
<?php endif; ?>

<?php if ($this->router->fetch_class() == 'chamados' && $this->router->fetch_method() == 'details'): ?>
        <script type="text/javascript" src="<?php echo base_url($frameworks_dir . '/jquery.countup/ez.countimer.min.js'); ?>"></script> 
<?php endif; ?>

<?php if ($this->router->fetch_class() == 'cad_prod' && ($this->router->fetch_method() == 'create' || $this->router->fetch_method() == 'edit')): ?>
        <script type="text/javascript" src="<?php echo base_url($plugins_dir . '/fileinput/fileinput.min.js'); ?>"></script> 
        <script type="text/javascript" src="<?php echo base_url($plugins_dir . '/fileinput/pt-BR.js'); ?>"></script> 
        <script type="text/javascript" src="<?php echo base_url($plugins_dir . '/fileinput/sortable.min.js'); ?>"></script> 
<?php endif; ?>

<?php if ($this->router->fetch_class() == 'oportunidades'): ?>
        <script type="text/javascript" src="<?php echo base_url($plugins_dir . '/ekko-lightbox/ekko-lightbox.min.js'); ?>"></script>
<?php endif; ?>

<?php if ($this->router->fetch_class() == 'oportunidades' && $this->router->fetch_method() == 'metricas'): ?>
        <script type="text/javascript" src="<?php echo base_url('assets/xkey/oportunidades_metricas.js'); ?>"></script>
<?php endif; ?>
<?php if (
    ($this->router->fetch_class() == 'oportunidades' && ($this->router->fetch_method() != 'analise_rentabilidade' && $this->router->fetch_method() != 'gerar_contrato')) || 
    $this->router->fetch_class() == 'cad_prod' ||
    $this->router->fetch_class() == 'comissoes' ||
    $this->router->fetch_class() == 'plano_acao' && $this->router->fetch_method() == 'index'
    ): ?>
        <script type="text/javascript" src="<?php echo base_url('assets/xkey/multiselect.js'); ?>"></script>
<?php endif; ?>

        <script src="<?php echo base_url($frameworks_dir . '/adminlte/js/adminlte.min.js'); ?>"></script>
        <script src="<?php echo base_url($frameworks_dir . '/domprojects/js/dp.min.js'); ?>"></script>

<?php if (in_array(strtolower($this->router->fetch_class()), array('treinamentos', 'treinamentos_admin'))): ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="<?php echo base_url('assets/xkey/treinamentos.js?v=' . (file_exists(FCPATH . 'assets/xkey/treinamentos.js') ? filemtime(FCPATH . 'assets/xkey/treinamentos.js') : time())); ?>"></script>
<?php endif; ?>
        <?php if ($this->router->fetch_class() == 'chamados' && $this->router->fetch_method() == 'apontamentos'): ?>
                <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.4.0/js/dataTables.buttons.min.js"></script>
                <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.4.0/js/buttons.flash.min.js"></script>
                <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.4.0/js/buttons.html5.min.js"></script>
                <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.4.0/js/buttons.print.min.js"></script>
                <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.4.0/css/buttons.dataTables.min.css"></script>
                <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
                <script type="text/javascript" src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.27/build/pdfmake.min.js"></script>
                <script type="text/javascript" src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.27/build/vfs_fonts.js"></script>
        <?php endif; ?>
    </body>
</html>