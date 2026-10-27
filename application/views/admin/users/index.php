<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content usuarios-page">
        <?php if (isset($message)) { echo $message; } ?>

        <div class="row">
            <div class="col-md-3 col-sm-6"><a href="#" class="usuarios-kpi js-status-kpi" data-status=""><span>Total</span><strong><?php echo number_format($totais['todos'], 0, ',', '.') ?></strong><small>usuários nas suas unidades</small></a></div>
            <div class="col-md-3 col-sm-6"><a href="#" class="usuarios-kpi js-status-kpi" data-status="ativo"><span>Ativos</span><strong class="text-green"><?php echo number_format($totais['ativo'], 0, ',', '.') ?></strong><small>podem entrar no sistema</small></a></div>
            <div class="col-md-3 col-sm-6"><a href="#" class="usuarios-kpi js-status-kpi" data-status="pendente"><span>Pendentes</span><strong class="text-yellow"><?php echo number_format($totais['pendente'], 0, ',', '.') ?></strong><small>aguardando ativação por e-mail</small></a></div>
            <div class="col-md-3 col-sm-6"><a href="#" class="usuarios-kpi js-status-kpi" data-status="inativo"><span>Inativos</span><strong class="text-muted"><?php echo number_format($totais['inativo'], 0, ',', '.') ?></strong><small>sem acesso</small></a></div>
        </div>

        <div class="box">
            <div class="box-header with-border usuarios-toolbar">
                <?php if ($can_create) { ?>
                    <?php echo anchor('users/create', '<i class="fa fa-plus"></i> ' . lang('users_create_user'), array('class' => 'btn btn-primary')); ?>
                <?php } ?>
                <div class="input-group usuarios-busca">
                    <input type="search" id="usuarios-q" class="form-control" placeholder="Buscar por nome, e-mail ou número...">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                </div>
                <select id="usuarios-status" class="form-control usuarios-filtro" title="Status">
                    <option value="ativo">Ativos</option>
                    <option value="pendente">Pendentes</option>
                    <option value="inativo">Inativos</option>
                    <option value="">Todos os status</option>
                </select>
                <select id="usuarios-grupo" class="form-control usuarios-filtro" title="Grupo">
                    <option value="">Todos os grupos</option>
                    <?php foreach ($grupos_filtro as $grupo) { ?>
                        <option value="<?php echo (int) $grupo->id ?>"><?php echo html_escape($grupo->name) ?></option>
                    <?php } ?>
                </select>
                <select id="usuarios-unidade" class="form-control usuarios-filtro" title="Unidade">
                    <option value="">Todas as unidades</option>
                    <?php foreach ($unidades_filtro as $id => $nome) { ?>
                        <option value="<?php echo (int) $id ?>"><?php echo html_escape($nome) ?></option>
                    <?php } ?>
                </select>
                <button type="button" class="btn btn-link" id="usuarios-limpar"><i class="fa fa-times"></i> Limpar</button>
            </div>
            <div class="box-body table-responsive no-padding">
                <table id="datatable_server_side" class="table table-hover usuarios-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Usuário</th>
                            <th><?php echo lang('users_groups'); ?></th>
                            <th>Último acesso</th>
                            <th><?php echo lang('users_status'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    window.addEventListener('load', function() {
        var tabela = $('#datatable_server_side').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?php echo base_url('users/server_side') ?>',
                type: 'post',
                dataType: 'json',
                data: function(d) {
                    d.q = $('#usuarios-q').val();
                    d.status = $('#usuarios-status').val();
                    d.grupo = $('#usuarios-grupo').val();
                    d.unidade = $('#usuarios-unidade').val();
                }
            },
            dom: 'rtip',
            pageLength: 25,
            autoWidth: false,
            order: [[1, 'asc']],
            columns: [
                {orderable: false, width: '44px'},
                null,
                {orderable: false},
                null,
                null,
                {orderable: false, className: 'text-right', width: '60px'}
            ],
            oLanguage: {
                sProcessing: '<i class="fa fa-spinner fa-spin"></i> Carregando...',
                sZeroRecords: 'Nenhum usuário encontrado com os filtros atuais.',
                sInfo: 'Mostrando _START_ a _END_ de _TOTAL_ usuários',
                sInfoEmpty: 'Nenhum usuário',
                sInfoFiltered: '(de _MAX_ nas suas unidades)',
                oPaginate: {sFirst: 'Primeiro', sPrevious: 'Anterior', sNext: 'Próximo', sLast: 'Último'}
            }
        });

        var espera;
        $('#usuarios-q').on('input', function() {
            clearTimeout(espera);
            espera = setTimeout(function() { tabela.ajax.reload(); }, 350);
        });
        $('.usuarios-filtro').on('change', function() { tabela.ajax.reload(); });
        $('#usuarios-limpar').on('click', function() {
            $('#usuarios-q').val('');
            $('#usuarios-status').val('ativo');
            $('#usuarios-grupo, #usuarios-unidade').val('');
            tabela.ajax.reload();
        });
        $('.js-status-kpi').on('click', function(event) {
            event.preventDefault();
            $('#usuarios-status').val($(this).data('status'));
            tabela.ajax.reload();
        });
        // Ativar um usuário é imediato: pede confirmação antes.
        $('#datatable_server_side').on('click', '.js-confirmar', function(event) {
            if (!window.confirm($(this).data('confirmar'))) {
                event.preventDefault();
            }
        });
    });
</script>
<style type="text/css">
    .usuarios-kpi { display: block; background: #fff; border-radius: 6px; box-shadow: 0 1px 2px rgba(0, 0, 0, .1); padding: 14px 16px; margin-bottom: 15px; color: #333; transition: box-shadow .15s; }
    .usuarios-kpi:hover { color: #333; box-shadow: 0 3px 10px rgba(0, 0, 0, .15); }
    .usuarios-kpi span { display: block; color: #777; font-size: 12px; text-transform: uppercase; letter-spacing: .4px; }
    .usuarios-kpi strong { display: block; font-size: 26px; line-height: 1.2; }
    .usuarios-kpi small { color: #999; }
    .usuarios-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .usuarios-busca { flex: 1 1 260px; max-width: 420px; }
    .usuarios-filtro { width: auto; min-width: 160px; max-width: 240px; }
    .usuarios-table { border-collapse: separate; border-spacing: 0; margin: 0 !important; }
    .usuarios-table > thead > tr > th { border-bottom: 2px solid #d2d6de; background: #f4f6f8; }
    .usuarios-table > tbody > tr > td { vertical-align: middle; padding-top: 10px; padding-bottom: 10px; border-top: 0; border-bottom: 1px solid #d2d6de; }
    .usuarios-table > tbody > tr:nth-child(even) > td { background-color: #f7f9fb; }
    .usuarios-table > tbody > tr:hover > td { background-color: #eaf2f8; }
    .usuarios-avatar { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 50%; background: #3c8dbc; color: #fff; font-weight: 700; font-size: 13px; }
    .usuarios-nome { font-weight: 600; font-size: 14px; }
    .usuarios-email { font-size: 12px; }
    .usuarios-table .label { display: inline-block; margin: 1px 0; }
    .usuarios-page .dataTables_info, .usuarios-page .dataTables_paginate { padding: 8px 12px; }
    .usuarios-page .dataTables_processing { top: 60px; }
</style>
