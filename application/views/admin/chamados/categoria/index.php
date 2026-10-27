<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$total = count($categorias);
$ativas = count(array_filter($categorias, function ($categoria) { return (int) $categoria->status === 1; }));
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if (isset($message)) { echo $message; } ?>
        <div class="box">
            <div class="box-header with-border categorias-toolbar">
                <?php if ($can_create) { ?>
                    <?php echo anchor('chamados_categoria/create', '<i class="fa fa-plus"></i> ' . lang('chamados_categoria_create'), array('class' => 'btn btn-primary')); ?>
                <?php } ?>
                <div class="input-group categorias-search">
                    <input type="search" id="categorias-busca" class="form-control" placeholder="Buscar por descrição ou setor...">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                </div>
                <select id="categorias-status" class="form-control categorias-filter" title="Status">
                    <option value="1">Ativas</option>
                    <option value="0">Inativas</option>
                    <option value="">Todos os status</option>
                </select>
                <select id="categorias-setor" class="form-control categorias-filter" title="Setor">
                    <option value="">Todos os setores</option>
                    <?php foreach ($setores_filtro as $setor) { ?>
                        <option value="<?php echo html_escape($setor->descricao) ?>"><?php echo html_escape($setor->descricao) ?></option>
                    <?php } ?>
                </select>
                <span class="text-muted categorias-count"><?php echo (int) $ativas ?> ativa<?php echo $ativas === 1 ? '' : 's' ?> de <?php echo (int) $total ?></span>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover categorias-table">
                    <thead>
                        <tr>
                            <th><?php echo lang('chamados_desc'); ?></th>
                            <th><?php echo lang('chamados_setor'); ?></th>
                            <th><?php echo lang('chamados_status'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categorias as $values) {
                            $ativa = (int) $values->status === 1;
                        ?>
                            <tr data-status="<?php echo $ativa ? 1 : 0 ?>" data-setor="<?php echo html_escape($values->setor) ?>" data-busca="<?php echo html_escape(mb_strtolower($values->descricao . ' ' . $values->setor, 'UTF-8')) ?>" style="border-left-color: <?php echo $ativa ? '#00a65a' : '#9aa5b1' ?>;">
                                <td>
                                    <span class="categorias-title"><?php echo html_escape($values->descricao); ?></span>
                                    <span class="categorias-id text-muted">#<?php echo (int) $values->id ?></span>
                                </td>
                                <td><?php echo html_escape($values->setor); ?></td>
                                <td>
                                    <?php if ($ativa) { ?>
                                        <?php echo anchor('chamados_categoria/deactivate/' . $values->id, '<span class="label label-success">' . lang('actions_ativo') . '</span>', array('title' => 'Clique para desativar', 'class' => 'js-confirm-status', 'data-acao' => 'desativar')); ?>
                                    <?php } else { ?>
                                        <?php echo anchor('chamados_categoria/activate/' . $values->id, '<span class="label label-default">' . lang('actions_inativo') . '</span>', array('title' => 'Clique para ativar', 'class' => 'js-confirm-status', 'data-acao' => 'ativar')); ?>
                                    <?php } ?>
                                </td>
                                <td class="text-right">
                                    <?php if ($can_edit) { ?>
                                        <?php echo anchor('chamados_categoria/edit/' . $values->id, '<i class="fa fa-edit"></i> Editar', array('class' => 'btn btn-default btn-sm')); ?>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr class="categorias-empty"<?php echo $total ? ' style="display:none;"' : '' ?>>
                            <td colspan="4" class="text-center text-muted">Nenhuma categoria encontrada com os filtros atuais.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    (function() {
        var search = document.getElementById('categorias-busca');
        var status = document.getElementById('categorias-status');
        var sector = document.getElementById('categorias-setor');
        var rows = document.querySelectorAll('.categorias-table tbody tr[data-status]');
        var empty = document.querySelector('.categorias-empty');

        function apply() {
            var term = search.value.trim().toLowerCase();
            var visible = 0;
            rows.forEach(function(row) {
                var show = (!term || row.getAttribute('data-busca').indexOf(term) !== -1) &&
                    (status.value === '' || row.getAttribute('data-status') === status.value) &&
                    (sector.value === '' || row.getAttribute('data-setor') === sector.value);
                row.style.display = show ? '' : 'none';
                visible += show ? 1 : 0;
            });
            empty.style.display = visible ? 'none' : '';
        }

        [search, status, sector].forEach(function(field) {
            field.addEventListener('input', apply);
            field.addEventListener('change', apply);
        });
        document.querySelectorAll('.js-confirm-status').forEach(function(link) {
            link.addEventListener('click', function(event) {
                if (!window.confirm('Deseja ' + link.getAttribute('data-acao') + ' esta categoria?')) {
                    event.preventDefault();
                }
            });
        });
        apply();
    })();
</script>
<style type="text/css">
    .categorias-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }
    .categorias-search {
        flex: 1 1 260px;
        max-width: 420px;
    }
    .categorias-filter {
        width: auto;
        min-width: 170px;
    }
    .categorias-count {
        margin-left: auto;
        font-size: 13px;
    }
    .categorias-table {
        border-collapse: separate;
        border-spacing: 0;
    }
    .categorias-table > thead > tr > th {
        border-bottom: 2px solid #d2d6de;
        background: #f4f6f8;
    }
    .categorias-table > tbody > tr > td {
        vertical-align: middle;
        padding-top: 12px;
        padding-bottom: 12px;
        border-top: 0;
        border-bottom: 1px solid #d2d6de;
    }
    .categorias-table > tbody > tr > td:first-child {
        border-left: 5px solid;
        border-left-color: inherit;
    }
    .categorias-table > tbody > tr:nth-child(even) > td {
        background-color: #f7f9fb;
    }
    .categorias-table > tbody > tr:hover > td {
        background-color: #eaf2f8;
    }
    .categorias-title {
        font-weight: 600;
        font-size: 14px;
    }
    .categorias-id {
        margin-left: 6px;
        font-size: 12px;
    }
    .categorias-empty td {
        padding: 30px !important;
    }
</style>
