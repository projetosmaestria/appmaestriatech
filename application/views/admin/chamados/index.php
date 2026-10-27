<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$advanced_filters = 0;
foreach (array($cliente, $usuario_limitado ? '' : $dono, $dev, $plano_acao, $inicio_abertura, $fim_abertura, $inicio_encerramento, $fim_encerramento, $avaliacao_de, $avaliacao_ate) as $value) {
    if ($value !== '' && $value !== null && $value !== 0) {
        $advanced_filters++;
    }
}
if (@$mostrar_resolvidos == 1) {
    $advanced_filters++;
}
$status_colors = array(
    'aberto'                          => '#28a745',
    'fechado'                         => '#212529',
    'avaliado'                        => '#6b1d2f',
    'aguardando retorno do cliente'   => '#9c27b0',
    'retorno'                         => '#9c27b0',
    'suspenso'                        => '#6c757d',
    'aguardando testes com o cliente' => '#e84393',
    'sugestão de desenvolvimento'     => '#605ca8',
    'sugestao de desenvolvimento'     => '#605ca8',
    'implantação'                     => '#00c0ef',
    'implantacao'                     => '#00c0ef',
    'fazendo'                         => '#007bff',
    'aguardando atualização sistema'  => '#795548',
    'aguardando atualizacao sistema'  => '#795548',
    'aguardando aprovação'            => '#ffc107',
    'aguardando aprovacao'            => '#ffc107',
    'aguardando testes sanclá'        => '#ec8f24',
    'aguardando testes sancla'        => '#ec8f24',
);
$get_status_color = function ($status_name) use ($status_colors) {
    $raw = mb_strtolower(trim((string) $status_name), 'UTF-8');
    if (isset($status_colors[$raw])) {
        return $status_colors[$raw];
    }
    $ascii = str_replace(
        array('ã', 'á', 'à', 'é', 'ê', 'í', 'ó', 'ô', 'ú', 'ç'),
        array('a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'u', 'c'),
        $raw
    );
    if (isset($status_colors[$ascii])) {
        return $status_colors[$ascii];
    }
    return '#5f5f5f';
};
$get_status_text_color = function ($hex) {
    return ($hex === '#ffc107') ? '#212529' : '#ffffff';
};
$format_date = function ($date, $format) {
    return $date ? date($format, strtotime($date)) : '';
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
            <form id="form-chamados-q" action="" method="get">
                <input type="hidden" id="mostrar_resolvidos" name="mostrar_resolvidos" value="<?php echo (@$mostrar_resolvidos == 1) ? 1 : 0 ?>">
                <input type="hidden" id="mostrar_responsaveis" name="mostrar_responsaveis" value="<?php echo (@$mostrar_responsaveis == 1) ? 1 : 0 ?>">
                <div class="box-header with-border chamados-toolbar">
                    <?php echo anchor('chamados/create', '<i class="fa fa-plus"></i> ' . lang('chamados_create'), array('class' => 'btn btn-primary')); ?>
                    <div class="input-group chamados-search">
                        <input name="q" class="form-control" placeholder="Buscar por número, título, cliente, responsável..." type="text" value="<?php echo html_escape($q) ?>">
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-default" title="Buscar"><i class="fa fa-search"></i></button>
                        </span>
                    </div>
                    <select name="filtro_status" id="filtro_status" class="form-control chamados-quick-filter" title="Status">
                        <option value="">Todos os status</option>
                        <?php foreach ($status as $_status) { ?>
                            <option value="<?php echo (int) $_status->id ?>" <?php echo ($st == $_status->id) ? 'selected' : '' ?>><?php echo html_escape($_status->descricao) ?></option>
                        <?php } ?>
                    </select>
                    <select name="filtro_prior" id="filtro_prior" class="form-control chamados-quick-filter" title="Prioridade">
                        <option value="">Todas as prioridades</option>
                        <?php foreach ($prioridades as $prior) { ?>
                            <option value="<?php echo (int) $prior->id ?>" <?php echo ($pr == $prior->id) ? 'selected' : '' ?>><?php echo html_escape($prior->descricao) ?></option>
                        <?php } ?>
                    </select>
                    <label class="checkbox-inline chamados-mine">
                        <input type="checkbox" id="mostrar_responsaveis_toggle" value="1" <?php echo (@$mostrar_responsaveis == 1) ? 'checked' : '' ?> <?php echo $usuario_limitado ? 'disabled' : '' ?>>
                        Só os meus
                    </label>
                    <button type="button" class="btn btn-default" data-toggle="collapse" data-target="#chamadosMaisFiltros" aria-expanded="<?php echo $advanced_filters ? 'true' : 'false' ?>">
                        <i class="fa fa-sliders"></i> Mais filtros
                        <?php if ($advanced_filters) { ?><span class="badge"><?php echo $advanced_filters ?></span><?php } ?>
                    </button>
                    <button type="submit" id="search-btn" class="btn btn-primary"><i class="fa fa-search"></i> Filtrar</button>
                    <?php if ($limpar == 1) { ?>
                        <a href="<?php echo base_url('chamados/index') ?>" id="clear-btn" class="btn btn-link"><i class="fa fa-times"></i> Limpar filtros</a>
                    <?php } ?>
                </div>

                <div id="chamadosMaisFiltros" class="collapse <?php echo $advanced_filters ? 'in' : '' ?>">
                    <div class="box-body chamados-advanced">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="filtro_unidade">Cliente</label>
                                <select name="filtro_unidade" id="filtro_unidade" class="singleInputDynamic form-control">
                                    <option value="">Todos</option>
                                    <?php foreach ($unidade_filtro as $k_unid => $unid) { ?>
                                        <option value="<?php echo (int) $k_unid ?>" <?php echo ($cliente == $k_unid) ? 'selected' : '' ?>><?php echo html_escape($unid) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="filtro_dono">Dono (solicitante)</label>
                                <select name="filtro_dono" id="filtro_dono" class="<?php echo $usuario_limitado ? '' : 'singleInputDynamic' ?> form-control" <?php echo $usuario_limitado ? 'disabled' : '' ?>>
                                    <option value="">Todos</option>
                                    <?php foreach ($usuarios as $usu) { ?>
                                        <option value="<?php echo (int) $usu->id ?>" <?php echo ($dono == $usu->id) ? 'selected' : '' ?>><?php echo html_escape($usu->nome . ' - ' . $usu->email) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="filtro_dev">Desenvolvedor</label>
                                <select name="filtro_dev" id="filtro_dev" class="<?php echo $usuario_limitado ? '' : 'singleInputDynamic' ?> form-control" <?php echo $usuario_limitado ? 'disabled' : '' ?>>
                                    <option value="">Todos</option>
                                    <?php foreach ($desenvolvedores as $_dev) { ?>
                                        <option value="<?php echo (int) $_dev->id ?>" <?php echo ($dev == $_dev->id) ? 'selected' : '' ?>><?php echo html_escape($_dev->nome) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="filtro_plano_acao">Projeto</label>
                                <select name="filtro_plano_acao" id="filtro_plano_acao" class="form-control">
                                    <option value="">Todos</option>
                                    <?php foreach ($planos_acao as $plano) { ?>
                                        <option value="<?php echo (int) $plano->id ?>" <?php echo ($plano_acao == $plano->id) ? 'selected' : '' ?>><?php echo html_escape($plano->desc) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="datetimepickerInicioAbertura">Abertura</label>
                                <div class="input-group">
                                    <input type="text" id="datetimepickerInicioAbertura" name="data_filtro_inicio_abertura" class="form-control date_range_filter date" placeholder="De">
                                    <span class="input-group-addon">até</span>
                                    <input type="text" id="datetimepickerFimAbertura" name="data_filtro_fim_abertura" class="form-control date_range_filter date" placeholder="Até">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="datetimepickerInicioEncerramento">Encerramento</label>
                                <div class="input-group">
                                    <input type="text" id="datetimepickerInicioEncerramento" name="data_filtro_inicio_encerramento" class="form-control date_range_filter date" placeholder="De">
                                    <span class="input-group-addon">até</span>
                                    <input type="text" id="datetimepickerFimEncerramento" name="data_filtro_fim_encerramento" class="form-control date_range_filter date" placeholder="Até">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 form-group">
                                <label>&nbsp;</label>
                                <div class="checkbox chamados-checkbox">
                                    <label><input type="checkbox" id="mostrar_resolvidos_toggle" value="1" <?php echo (@$mostrar_resolvidos == 1) ? 'checked' : '' ?>> Incluir chamados avaliados</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 form-group avaliacao_de_ate" <?php echo ($st == 5) ? '' : 'style="display:none;"' ?>>
                                <label for="filtro_avaliacao_de">Nota da avaliação</label>
                                <div class="input-group">
                                    <select name="filtro_avaliacao_de" id="filtro_avaliacao_de" class="form-control">
                                        <option value="">De</option>
                                        <?php for ($i = 1; $i <= 5; $i++) { ?><option value="<?php echo $i ?>" <?php echo ($avaliacao_de == $i) ? 'selected' : '' ?>><?php echo $i ?></option><?php } ?>
                                    </select>
                                    <span class="input-group-addon">até</span>
                                    <select name="filtro_avaliacao_ate" id="filtro_avaliacao_ate" class="form-control">
                                        <option value="">Até</option>
                                        <?php for ($i = 1; $i <= 5; $i++) { ?><option value="<?php echo $i ?>" <?php echo ($avaliacao_ate == $i) ? 'selected' : '' ?>><?php echo $i ?></option><?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="box-body table-responsive no-padding">
                <table id="chamado-datatable" class="table table-hover chamados-table">
                    <thead>
                        <tr>
                            <th><?php echo lang('chamados_id'); ?></th>
                            <th>Chamado</th>
                            <th><?php echo lang('chamados_status'); ?></th>
                            <th><?php echo lang('chamados_prioridade'); ?></th>
                            <th><?php echo lang('chamados_responsavel'); ?></th>
                            <th>Abertura</th>
                            <th>Previsão</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$chamados) { ?>
                            <tr><td colspan="7" class="text-center text-muted chamados-empty">Nenhum chamado encontrado com os filtros atuais.</td></tr>
                        <?php } ?>
                        <?php foreach ($chamados as $chamado) {
                            $status_color = $get_status_color($chamado->status);
                            $status_text_color = $get_status_text_color($status_color);
                            $without_owner = ($chamado->responsavel_id == "" || $chamado->responsavel_id == null);
                            $is_open = (isset($chamado->status_id) && (int) $chamado->status_id === 1) || (mb_strtolower(trim((string) $chamado->status), 'UTF-8') === 'aberto');
                            $is_overdue = ($chamado->data_prazo && !$chamado->data_finalizacao && $chamado->data_prazo < date('Y-m-d H:i:s'));
                            $row_color = ($is_open && $is_overdue) ? '#dd4b39' : $status_color;
                            $is_high_priority = mb_strtolower(trim((string) $chamado->prioridade), 'UTF-8') === 'alta';
                        ?>
                            <tr class="clickable-row" data-href="<?php echo base_url('chamados/details/' . $chamado->id) ?>" style="border-left-color: <?php echo $row_color ?>;">
                                <td class="chamados-id" style="border-left: 6px solid <?php echo $row_color ?> !important;">#<?php echo (int) $chamado->id; ?></td>
                                <td>
                                    <a href="<?php echo base_url('chamados/details/' . $chamado->id) ?>" class="chamados-title"><?php echo html_escape($chamado->titulo); ?></a>
                                    <div class="chamados-sub text-muted">
                                        <?php echo html_escape($chamado->cliente); ?>
                                        <?php if ($chamado->catalogo_desc) { ?> · <?php echo html_escape($chamado->catalogo_desc); ?><?php } ?>
                                        · Dono: <?php echo html_escape(trim($chamado->dono_first_name . ' ' . $chamado->dono_last_name)); ?>
                                    </div>
                                </td>
                                <td><span class="label" style="background-color: <?php echo $status_color ?> !important; color: <?php echo $status_text_color ?> !important; font-weight: 600; font-size: 11.5px; padding: 4px 8px; border-radius: 4px; display: inline-block;"><?php echo html_escape($chamado->status); ?></span></td>
                                <td><span class="label <?php echo $is_high_priority ? 'label-danger' : 'label-default'; ?>"><?php echo html_escape($chamado->prioridade); ?></span></td>
                                <td>
                                    <?php if ($without_owner) { ?>
                                        <span class="label label-warning">Sem responsável</span>
                                    <?php } else { ?>
                                        <?php echo html_escape(trim($chamado->first_name . ' ' . $chamado->last_name)); ?>
                                    <?php } ?>
                                </td>
                                <td class="chamados-date" title="<?php echo $format_date($chamado->created, 'd/m/Y H:i:s') ?>"><?php echo $format_date($chamado->created, 'd/m/Y H:i') ?></td>
                                <td class="chamados-date <?php echo $is_overdue ? 'text-red' : '' ?>">
                                    <?php if (@$acesso_reagendar == true) { ?>
                                        <a href="javascript:void(0)" id="<?php echo (int) $chamado->id ?>" class="reagendarChamado <?php echo $is_overdue ? 'text-red' : '' ?>" title="Reagendar previsão">
                                    <?php } ?>
                                    <?php echo $chamado->data_prazo ? $format_date($chamado->data_prazo, 'd/m/Y H:i') : 'A definir'; ?>
                                    <?php if (@$acesso_reagendar == true) { ?>
                                        <i class="fa fa-pencil"></i></a>
                                    <?php } ?>
                                    <?php if ($is_overdue) { ?><br><small><strong>Atrasado</strong></small><?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="box-footer chamados-pagination">
                <div class="row">
                    <?php if ($pagination) { ?>
                        <?php echo $pagination; ?>
                    <?php } else { ?>
                        <div class="col-sm-12 text-muted"><?php echo (int) $total_chamados ?> chamado<?php echo (int) $total_chamados === 1 ? '' : 's' ?></div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal -->
<div id="reagendarModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"></h4>
      </div>
      <div class="modal-body">
        <?php echo form_open(base_url('chamados/reagendar'), array('class' => 'form-horizontal', 'id' => 'form-reagendar_chamado')); ?>
            <input type='hidden' id='chamado_id' name='chamado_id' value=''>
            <div class="form-group">
                <?php echo lang('chamados_data', 'data_reagendar', array('class' => 'col-sm-4 control-label'), true); ?>
                <div class="col-sm-6 input-group date" id='datetimehourpicker'>
                    <?php echo form_input(array('id' => 'data_reagendar', 'name' => 'data_reagendar', 'value' => '', 'class' => 'form-control')); ?>
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-10 text-right">
                    <div class="btn-group">
                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit_chamado'))); ?>
                    </div>
                </div>
            </div>
        <?php echo form_close(); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
    window.onload = function() {
        var dateOptions = {locale: 'pt-br', format: 'DD/MM/YYYY', showClose: true, icons: {close: 'closeText'}};
        $('#datetimepickerInicioAbertura').datetimepicker($.extend({}, dateOptions, {defaultDate: "<?php echo html_escape($inicio_abertura) ?>"}));
        $('#datetimepickerFimAbertura').datetimepicker($.extend({}, dateOptions, {defaultDate: "<?php echo html_escape($fim_abertura) ?>"}));
        $('#datetimepickerInicioEncerramento').datetimepicker($.extend({}, dateOptions, {defaultDate: "<?php echo html_escape($inicio_encerramento) ?>"}));
        $('#datetimepickerFimEncerramento').datetimepicker($.extend({}, dateOptions, {defaultDate: "<?php echo html_escape($fim_encerramento) ?>"}));

        // Status e prioridade aplicam o filtro na hora; "Só os meus" também.
        $('#filtro_status, #filtro_prior').on('change', function() {
            if (!$('#chamadosMaisFiltros').hasClass('in')) {
                $('#form-chamados-q').submit();
            }
        });
        $('#mostrar_responsaveis_toggle').on('change', function() {
            $('#mostrar_responsaveis').val(this.checked ? 1 : 0);
            $('#form-chamados-q').submit();
        });
        $('.chamados-table .clickable-row a').on('click', function(event) {
            event.stopPropagation();
        });
    };
</script>
<style type="text/css">
    .chamados-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }
    .chamados-search {
        flex: 1 1 260px;
        max-width: 420px;
        display: flex;
        align-items: stretch;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        overflow: hidden;
        transition: all 0.2s ease;
    }
    .chamados-search:focus-within {
        border-color: #9cd7d4;
        box-shadow: 0 0 0 3px rgba(156, 215, 212, 0.1);
    }
    .chamados-toolbar .chamados-search .form-control {
        flex: 1 1 auto;
        width: auto;
        margin: 0;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        background: transparent !important;
    }
    .chamados-toolbar .chamados-search .input-group-btn {
        display: flex;
        width: auto;
        margin: 0;
    }
    .chamados-toolbar .chamados-search .input-group-btn .btn {
        margin: 0;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        color: #64748b !important;
        box-shadow: none !important;
        transform: none !important;
    }
    .chamados-toolbar .chamados-search .input-group-btn .btn:hover {
        background: #f8fafc !important;
        color: #1e1d3f !important;
    }
    .chamados-quick-filter {
        width: auto;
        min-width: 160px;
    }
    .chamados-mine {
        padding-top: 0;
        margin: 0 4px;
    }
    .chamados-advanced {
        background: #f9fafc;
        border-bottom: 1px solid #f4f4f4;
    }
    .chamados-advanced .fstElement {
        width: 100%;
    }
    .chamados-checkbox {
        margin-top: 6px;
    }
    .chamados-table {
        border-collapse: separate;
        border-spacing: 0;
    }
    .chamados-table > thead > tr > th {
        border-bottom: 2px solid #d2d6de;
        background: #f4f6f8;
    }
    .chamados-table > tbody > tr > td {
        vertical-align: middle;
        padding-top: 12px;
        padding-bottom: 12px;
        border-top: 0;
        border-bottom: 1px solid #d2d6de;
    }
    .chamados-table > tbody > tr > td:first-child {
        border-left: 5px solid;
        border-left-color: inherit;
    }
    .chamados-table > tbody > tr:nth-child(even) > td {
        background-color: #f7f9fb;
    }
    .chamados-table > tbody > tr:hover > td {
        background-color: #eaf2f8;
    }
    .chamados-id {
        color: #777;
        white-space: nowrap;
    }
    .chamados-title {
        font-weight: 600;
        font-size: 14px;
    }
    .chamados-sub {
        font-size: 12px;
        margin-top: 2px;
    }
    .chamados-date {
        white-space: nowrap;
    }
    .chamados-empty {
        padding: 30px !important;
    }
    .chamados-pagination .pagination {
        margin: 0;
    }
</style>
