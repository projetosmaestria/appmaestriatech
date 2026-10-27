<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$status_labels = array(
    'abertos' => 'A iniciar e em andamento',
    'AI' => 'A iniciar',
    'EA' => 'Em andamento',
    'FI' => 'Finalizados',
    'todos' => 'Todos os status',
);
$status_colors = array('AI' => '#9aa5b1', 'EA' => '#3c8dbc', 'FI' => '#00a65a');
$status_names = array('AI' => 'A iniciar', 'EA' => 'Em andamento', 'FI' => 'Finalizado');
$tipos = array('I' => 'Implantação', 'D' => 'Desenvolvimento');
$advanced_filters = 0;
foreach (array('unidade', 'tipo', 'inicio_de', 'inicio_ate', 'termino_de', 'termino_ate', 'atrasados') as $key) {
    $advanced_filters += !empty($filters[$key]) ? 1 : 0;
}
$advanced_filters += (!$only_mine && !empty($filters['responsavel'])) ? 1 : 0;
$format_date = function ($date) {
    return $date ? date('d/m/Y', strtotime($date)) : '—';
};
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if (isset($message)) { echo $message; } ?>
        <div class="box">
            <form id="form-projetos" action="<?php echo base_url('plano_acao/index') ?>" method="get">
                <input type="hidden" id="filtro_meus" name="meus" value="<?php echo $only_mine ? 1 : 0 ?>">
                <div class="box-header with-border projetos-toolbar">
                    <?php if ($can_create) { ?>
                        <?php echo anchor('plano_acao/create', '<i class="fa fa-plus"></i> ' . lang('plano_acao_create'), array('class' => 'btn btn-primary')); ?>
                    <?php } ?>
                    <div class="input-group projetos-search">
                        <input name="q" class="form-control" placeholder="Buscar por número, nome, cliente ou responsável..." type="text" value="<?php echo html_escape($filters['q']) ?>">
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-default" title="Buscar"><i class="fa fa-search"></i></button>
                        </span>
                    </div>
                    <select name="filtro_status" id="filtro_status" class="form-control projetos-quick-filter" title="Status">
                        <?php foreach ($status_labels as $value => $label) { ?>
                            <option value="<?php echo $value ?>" <?php echo $status_filter === $value ? 'selected' : '' ?>><?php echo $label ?></option>
                        <?php } ?>
                    </select>
                    <label class="checkbox-inline projetos-mine">
                        <input type="checkbox" id="filtro_meus_toggle" value="1" <?php echo $only_mine ? 'checked' : '' ?>> Só os meus
                    </label>
                    <button type="button" class="btn btn-default" data-toggle="collapse" data-target="#projetosMaisFiltros" aria-expanded="<?php echo $advanced_filters ? 'true' : 'false' ?>">
                        <i class="fa fa-sliders"></i> Mais filtros
                        <?php if ($advanced_filters) { ?><span class="badge"><?php echo $advanced_filters ?></span><?php } ?>
                    </button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Filtrar</button>
                    <?php if ($limpar == 1) { ?>
                        <a href="<?php echo base_url('plano_acao/index') ?>" class="btn btn-link"><i class="fa fa-times"></i> Limpar filtros</a>
                    <?php } ?>
                    <div class="projetos-toolbar-right">
                        <a href="<?php echo base_url('plano_acao/gantt') ?>" class="btn btn-default"><i class="fa fa-tasks"></i> Cronograma (Gantt)</a>
                        <?php if ($can_exportar_pdf) { ?>
                            <button type="button" class="btn btn-default" id="projetos-exportar" disabled><i class="fa fa-file-pdf-o"></i> Exportar PDF <span class="badge" id="projetos-selecionados">0</span></button>
                        <?php } ?>
                    </div>
                </div>

                <div id="projetosMaisFiltros" class="collapse <?php echo $advanced_filters ? 'in' : '' ?>">
                    <div class="box-body projetos-advanced">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="filtro_responsavel">Responsável</label>
                                <select name="filtro_responsavel" id="filtro_responsavel" class="singleInputDynamic form-control" <?php echo $only_mine ? 'disabled' : '' ?>>
                                    <option value="">Todos</option>
                                    <?php foreach ($usuarios_filtro as $usuario) { ?>
                                        <option value="<?php echo (int) $usuario->id ?>" <?php echo (!$only_mine && $filters['responsavel'] == $usuario->id) ? 'selected' : '' ?>><?php echo html_escape(trim($usuario->first_name . ' ' . $usuario->last_name)) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="filtro_unidade">Cliente</label>
                                <select name="filtro_unidade" id="filtro_unidade" class="singleInputDynamic form-control">
                                    <option value="">Todos</option>
                                    <?php foreach ($unidades_filtro as $unidade) { ?>
                                        <option value="<?php echo (int) $unidade->id ?>" <?php echo $filters['unidade'] == $unidade->id ? 'selected' : '' ?>><?php echo html_escape($unidade->unidade) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="filtro_tipo">Tipo</label>
                                <select name="filtro_tipo" id="filtro_tipo" class="form-control">
                                    <option value="">Todos</option>
                                    <?php foreach ($tipos as $value => $label) { ?>
                                        <option value="<?php echo $value ?>" <?php echo $filters['tipo'] === $value ? 'selected' : '' ?>><?php echo $label ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6 form-group">
                                <label>&nbsp;</label>
                                <div class="checkbox projetos-checkbox">
                                    <label><input type="checkbox" name="atrasados" value="1" <?php echo $filters['atrasados'] ? 'checked' : '' ?>> Somente atrasados</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="inicio_de">Início estimado</label>
                                <div class="input-group">
                                    <input type="text" id="inicio_de" name="inicio_de" class="form-control js-date" placeholder="De" value="<?php echo html_escape($filter_dates['inicio_de']) ?>">
                                    <span class="input-group-addon">até</span>
                                    <input type="text" id="inicio_ate" name="inicio_ate" class="form-control js-date" placeholder="Até" value="<?php echo html_escape($filter_dates['inicio_ate']) ?>">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="termino_de">Término estimado</label>
                                <div class="input-group">
                                    <input type="text" id="termino_de" name="termino_de" class="form-control js-date" placeholder="De" value="<?php echo html_escape($filter_dates['termino_de']) ?>">
                                    <span class="input-group-addon">até</span>
                                    <input type="text" id="termino_ate" name="termino_ate" class="form-control js-date" placeholder="Até" value="<?php echo html_escape($filter_dates['termino_ate']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="box-body table-responsive no-padding">
                <table class="table table-hover projetos-table">
                    <thead>
                        <tr>
                            <?php if ($can_exportar_pdf) { ?><th class="projetos-check"><input type="checkbox" id="projetos-todos" title="Selecionar todos"></th><?php } ?>
                            <th><?php echo lang('codigo') ?></th>
                            <th>Projeto</th>
                            <th><?php echo lang('status') ?></th>
                            <th><?php echo lang('responsavel') ?></th>
                            <th>Período estimado</th>
                            <th>Chamados</th>
                            <th>Horas</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$planos) { ?>
                            <tr><td colspan="9" class="text-center text-muted projetos-empty">Nenhum projeto encontrado com os filtros atuais.</td></tr>
                        <?php } ?>
                        <?php foreach ($planos as $plano) {
                            $codigo = $plano->status_codigo;
                            $late = $codigo !== 'FI' && $plano->termino_estimado && $plano->termino_estimado < date('Y-m-d H:i:s');
                            $total = $plano->resumo ? (int) $plano->resumo->total : 0;
                            $done = $plano->resumo ? (int) $plano->resumo->concluidos : 0;
                            $late_tickets = $plano->resumo ? (int) $plano->resumo->atrasados : 0;
                            $progress = $total ? round($done / $total * 100) : 0;
                            $row_color = $late ? '#dd4b39' : $status_colors[$codigo];
                        ?>
                            <tr class="js-projeto-linha" data-href="<?php echo base_url('plano_acao/gantt/' . $plano->id) ?>" style="border-left-color: <?php echo $row_color ?>;">
                                <?php if ($can_exportar_pdf) { ?><td class="projetos-check"><input type="checkbox" class="js-projeto-check" value="<?php echo (int) $plano->id ?>"></td><?php } ?>
                                <td class="projetos-id">#<?php echo (int) $plano->id ?></td>
                                <td>
                                    <a href="<?php echo base_url('plano_acao/gantt/' . $plano->id) ?>" class="projetos-title"><?php echo html_escape($plano->desc) ?></a>
                                    <div class="projetos-sub text-muted">
                                        <?php echo html_escape($plano->unidade ?: 'Todos os clientes') ?>
                                        <?php if (isset($tipos[$plano->tipo])) { ?> · <?php echo $tipos[$plano->tipo] ?><?php } ?>
                                    </div>
                                </td>
                                <td><span class="label" style="background-color: <?php echo $status_colors[$codigo] ?>;"><?php echo $status_names[$codigo] ?></span></td>
                                <td><?php echo html_escape($plano->nome_resp ?: '—') ?></td>
                                <td class="projetos-nowrap <?php echo $late ? 'text-red' : '' ?>">
                                    <?php echo $format_date($plano->inicio_estimado) ?> a <?php echo $format_date($plano->termino_estimado) ?>
                                    <?php if ($late) { ?><br><small><strong>Atrasado</strong></small><?php } ?>
                                </td>
                                <td class="projetos-progress">
                                    <div class="progress progress-xs"><div class="progress-bar progress-bar-success" style="width: <?php echo $progress ?>%"></div></div>
                                    <small><?php echo $done ?>/<?php echo $total ?> concluídos</small>
                                    <?php if ($late_tickets) { ?><span class="label label-danger"><?php echo $late_tickets ?> atrasado<?php echo $late_tickets > 1 ? 's' : '' ?></span><?php } ?>
                                </td>
                                <td class="projetos-nowrap"><?php echo html_escape($plano->horas_utilizadas) ?> <small class="text-muted">/ <?php echo $plano->horas_prevista !== null && $plano->horas_prevista !== '' ? html_escape($plano->horas_prevista) . 'h' : '—' ?></small></td>
                                <td class="text-right">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false" title="Ações"><i class="fa fa-ellipsis-v"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                            <li><?php echo anchor('plano_acao/gantt/' . $plano->id, '<i class="fa fa-tasks"></i> Cronograma'); ?></li>
                                            <li><?php echo anchor('plano_acao/view/' . $plano->id, '<i class="fa fa-eye"></i> ' . lang('actions_see')); ?></li>
                                            <?php if ($can_edit) { ?><li><?php echo anchor('plano_acao/edit/' . $plano->id, '<i class="fa fa-edit"></i> ' . lang('actions_edit')); ?></li><?php } ?>
                                            <li><?php echo anchor('chamados/index?filtro_plano_acao=' . $plano->id . '&mostrar_resolvidos=1', '<i class="fa fa-headphones"></i> ' . lang('chamados_vinculados')); ?></li>
                                            <li><?php echo anchor('chamados/create/planoacao/' . $plano->id, '<i class="fa fa-plus"></i> ' . lang('novo_chamado')); ?></li>
                                            <?php if ($codigo !== 'FI') { ?>
                                                <li class="divider"></li>
                                                <li><?php echo anchor('plano_acao/finalizar/' . $plano->id, '<i class="fa fa-check"></i> ' . lang('actions_finalizar')); ?></li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="box-footer projetos-pagination">
                <div class="row">
                    <?php if ($pagination) { ?>
                        <?php echo $pagination; ?>
                    <?php } else { ?>
                        <div class="col-sm-12 text-muted"><?php echo (int) $total_planos ?> projeto<?php echo (int) $total_planos === 1 ? '' : 's' ?></div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    window.addEventListener('load', function() {
        $('.js-date').datetimepicker({locale: 'pt-br', format: 'DD/MM/YYYY', showClose: true, useCurrent: false, icons: {close: 'closeText'}});

        // Status e "Só os meus" aplicam o filtro na hora, como na lista de chamados.
        $('#filtro_status').on('change', function() {
            if (!$('#projetosMaisFiltros').hasClass('in')) {
                $('#form-projetos').submit();
            }
        });
        $('#filtro_meus_toggle').on('change', function() {
            $('#filtro_meus').val(this.checked ? 1 : 0);
            $('#form-projetos').submit();
        });

        // A linha abre o cronograma, exceto quando o clique é em link, botão, caixa de seleção ou no menu de ações.
        // Não usa stopPropagation: o menu (dropdown do Bootstrap) só abre se o clique chegar ao documento.
        $('.projetos-table').on('click', '.js-projeto-linha', function(event) {
            if ($(event.target).closest('a, button, input, label, .dropdown-menu, .projetos-check').length) {
                return;
            }
            window.location = $(this).data('href');
        });

        function updateSelection() {
            var count = $('.js-projeto-check:checked').length;
            $('#projetos-selecionados').text(count);
            $('#projetos-exportar').prop('disabled', count === 0);
        }
        $('#projetos-todos').on('change', function() {
            $('.js-projeto-check').prop('checked', this.checked);
            updateSelection();
        });
        $('.js-projeto-check').on('change', updateSelection);
        $('#projetos-exportar').on('click', function() {
            var ids = $('.js-projeto-check:checked').map(function() { return this.value; }).get().join(',');
            var form = $('<form>', {action: '<?php echo base_url('plano_acao/exportar_pdf') ?>', method: 'post'});
            form.append($('<input>', {type: 'hidden', name: 'ids', value: ids}));
            <?php if ($this->config->item('csrf_protection')) { ?>
            form.append($('<input>', {type: 'hidden', name: '<?php echo $this->security->get_csrf_token_name() ?>', value: '<?php echo $this->security->get_csrf_hash() ?>'}));
            <?php } ?>
            form.appendTo('body').submit();
        });
    });
</script>
<style type="text/css">
    .projetos-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }
    .projetos-toolbar-right {
        margin-left: auto;
    }
    .projetos-search {
        flex: 1 1 260px;
        max-width: 420px;
    }
    .projetos-quick-filter {
        width: auto;
        min-width: 190px;
    }
    .projetos-mine {
        padding-top: 0;
        margin: 0 4px;
    }
    .projetos-advanced {
        background: #f9fafc;
        border-bottom: 1px solid #f4f4f4;
    }
    .projetos-advanced .fstElement {
        width: 100%;
    }
    .projetos-checkbox {
        margin-top: 6px;
    }
    .projetos-table {
        border-collapse: separate;
        border-spacing: 0;
    }
    .projetos-table > thead > tr > th {
        border-bottom: 2px solid #d2d6de;
        background: #f4f6f8;
    }
    .projetos-table > tbody > tr > td {
        vertical-align: middle;
        padding-top: 12px;
        padding-bottom: 12px;
        border-top: 0;
        border-bottom: 1px solid #d2d6de;
    }
    .projetos-table > tbody > tr > td:first-child {
        border-left: 5px solid;
        border-left-color: inherit;
    }
    .projetos-table > tbody > tr:nth-child(even) > td {
        background-color: #f7f9fb;
    }
    .projetos-table > tbody > tr:hover > td {
        background-color: #eaf2f8;
    }
    .js-projeto-linha {
        cursor: pointer;
    }
    .projetos-check {
        width: 36px;
    }
    .projetos-id {
        color: #777;
        white-space: nowrap;
    }
    .projetos-title {
        font-weight: 600;
        font-size: 14px;
    }
    .projetos-sub {
        font-size: 12px;
        margin-top: 2px;
    }
    .projetos-nowrap {
        white-space: nowrap;
    }
    .projetos-progress {
        min-width: 140px;
    }
    .projetos-progress .progress {
        margin-bottom: 2px;
    }
    .projetos-empty {
        padding: 30px !important;
    }
    .projetos-pagination .pagination {
        margin: 0;
    }
</style>
