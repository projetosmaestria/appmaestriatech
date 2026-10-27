<?php
defined('BASEPATH') or exit('No direct script access allowed');
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
$status_color = $get_status_color($chamado->status);
$status_text_color = ($status_color === '#ffc107') ? '#212529' : '#ffffff';
$is_overdue = $chamado->data_prazo && !$chamado->data_finalizacao && $chamado->data_prazo < date('Y-m-d H:i:s');
$is_high_priority = mb_strtolower(trim((string) $chamado->prioridade), 'UTF-8') === 'alta';
$full_name = function ($first, $last) {
    $name = trim($first . ' ' . $last);
    return $name !== '' ? $name : '—';
};
$initials = function ($first, $last) {
    $letters = mb_substr(trim((string) $first), 0, 1, 'UTF-8') . mb_substr(trim((string) $last), 0, 1, 'UTF-8');
    return $letters !== '' ? mb_strtoupper($letters, 'UTF-8') : '?';
};
$format_datetime = function ($date) {
    return $date ? date('d/m/Y H:i', strtotime($date)) : 'Não definido';
};
// Texto puro com quebras de linha e links clicáveis.
$format_text = function ($text) {
    $escaped = nl2br(htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8'));
    return preg_replace('~(https?://[^\s<]+)~i', '<a href="$1" target="_blank" rel="noopener">$1</a>', $escaped);
};
$day_label = function ($date) {
    $day = date('Y-m-d', strtotime($date));
    if ($day === date('Y-m-d')) {
        return 'Hoje';
    }
    if ($day === date('Y-m-d', strtotime('-1 day'))) {
        return 'Ontem';
    }
    return date('d/m/Y', strtotime($date));
};
$path = array();
foreach (array('produto_desc', 'modulo_desc', 'mcategoria_desc', 'catalogo_desc') as $field) {
    if (!empty($chamado->$field)) {
        $path[] = html_escape($chamado->$field);
    }
}
$total_seconds = 0;
$time_entries = (isset($apontamentos) && $apontamentos) ? $apontamentos->result() : array();
foreach ($time_entries as $entry) {
    $parts = array_map('intval', explode(':', (string) $entry->esforco));
    $total_seconds += (isset($parts[0]) ? $parts[0] : 0) * 3600 + (isset($parts[1]) ? $parts[1] : 0) * 60 + (isset($parts[2]) ? $parts[2] : 0);
}
$total_effort = sprintf('%d:%02d', floor($total_seconds / 3600), floor(($total_seconds % 3600) / 60));
$is_closed = $chamado->status_id == 3 || $chamado->status_id == 5;
$checklist_count = isset($checklists_associados) ? count($checklists_associados) : 0;
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content chamado-detalhe">
        <?php if ($this->session->flashdata('message') != "") {
            echo $this->session->flashdata('message');
        } ?>
        <input type="hidden" name="acesso_interno" id="acesso_interno" value="<?php echo @$acesso_interno ?>">
        <input type="hidden" id="id_chamado" name="id_chamado" value="<?php echo $chamado->id ?>">

        <div class="row">
            <div class="col-lg-8">
                <div class="box chamado-hero" style="border-top-color: <?php echo $status_color ?>;">
                    <div class="box-body">
                        <div class="chamado-badges">
                            <span class="chamado-number">#<?php echo (int) $chamado->id ?></span>
                            <span class="label" style="background-color: <?php echo $status_color ?>; color: <?php echo $status_text_color ?>; font-weight: 600; font-size: 12px; padding: 4px 8px; border-radius: 4px;"><?php echo html_escape($chamado->status) ?></span>
                            <span class="label <?php echo $is_high_priority ? 'label-danger' : 'label-default' ?>">Prioridade <?php echo html_escape($chamado->prioridade) ?></span>
                            <?php if ($chamado->nivel_desc) { ?><span class="label label-default"><?php echo html_escape($chamado->nivel_desc) ?></span><?php } ?>
                            <?php if ($is_overdue) { ?><span class="label label-danger"><i class="fa fa-clock-o"></i> Atrasado</span><?php } ?>
                        </div>
                        <h2 class="chamado-title"><?php echo html_escape($chamado->titulo) ?></h2>
                        <?php if ($path) { ?>
                            <div class="chamado-path"><?php echo implode(' <i class="fa fa-angle-right"></i> ', $path) ?></div>
                        <?php } ?>
                        <div class="chamado-opened text-muted">
                            Aberto em <?php echo $format_datetime($chamado->created) ?> por <strong><?php echo html_escape($full_name($chamado->first_name_dono, $chamado->last_name_dono)) ?></strong>
                            para <a href="<?php echo base_url('unidades/edit/' . $chamado->unidade_id) ?>"><?php echo html_escape($chamado->cliente) ?></a>
                            <?php if ($chamado->referencia_id) { ?>
                                · Referência: <a target="_blank" href="<?php echo base_url('chamados/details/' . $chamado->chamado_referencia_id) ?>">#<?php echo (int) $chamado->chamado_referencia_id ?> <?php echo html_escape($chamado->chamado_referencia_titulo) ?></a>
                            <?php } ?>
                        </div>
                        <?php if (count($avaliacoes) > 0 && $show_avaliacao) { ?>
                            <div class="chamado-rating">
                                <?php foreach ($avaliacoes as $value) { ?>
                                    <label for="star-<?php echo $value["id"] ?>" class="control-label"><?php echo html_escape($value['descricao']) ?>
                                        <input id="star-<?php echo $value["id"] ?>" class="rating rating-loading" value="<?php echo $value["resposta"] ?>" data-min="0" data-max="5" data-step="1" data-size="xs" data-show-caption="false">
                                    </label>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-align-left"></i> <?php echo lang('chamados_desc') ?></h3>
                    </div>
                    <div class="box-body chamado-description"><?php echo $format_text($chamado->descricao); ?></div>
                </div>

                <?php if (@$has_chamados_vinculados == true) { ?>
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-link"></i> <?php echo lang('chamados_vinculados') ?></h3>
                        </div>
                        <div class="box-body no-padding">
                            <ul class="nav nav-stacked">
                                <?php foreach ($chamados_vinculados->result() as $vinc) { ?>
                                    <li><a target="_blank" href="<?php echo base_url('chamados/details/' . $vinc->id) ?>">#<?php echo (int) $vinc->id ?> — <?php echo html_escape($vinc->titulo) ?> <span class="pull-right text-muted"><?php echo html_escape($vinc->status) ?></span></a></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                <?php } ?>

                <div class="nav-tabs-custom chamado-tabs">
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#dialogo" aria-controls="dialogo" role="tab" data-toggle="tab">
                                <i class="fa fa-comments"></i> Conversa <span class="badge"><?php echo count($timeline) ?></span>
                            </a>
                        </li>
                        <?php if (!$is_closed) { ?>
                            <li role="presentation">
                                <a href="#checklist" aria-controls="checklist" role="tab" data-toggle="tab">
                                    <i class="fa fa-check-square-o"></i> Checklist <span class="badge"><?php echo (int) $checklist_count ?></span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="dialogo">
                            <div class="chamado-reply-actions">
                                <a class="btn btn-primary" href="javascript:void(0);" onclick="add_resposta(1,'<?php echo $chamado->id ?>');">
                                    <i class="fa fa-reply"></i> Responder
                                </a>
                                <a class="btn btn-default" href="javascript:void(0);" onclick="add_resposta(2,'<?php echo $chamado->id ?>');">
                                    <i class="fa fa-paperclip"></i> Anexar arquivo
                                </a>
                            </div>
                            <div class="dialogo-anexo"></div>

                            <?php if (!$timeline) { ?>
                                <p class="text-muted chamado-empty">Nenhuma interação ainda. Use <strong>Responder</strong> para iniciar a conversa.</p>
                            <?php } else { ?>
                                <p class="text-muted small">Mais recentes primeiro.</p>
                                <div class="chamado-conversation">
                                    <?php
                                    $last_day = '';
                                    foreach ($timeline as $dialog) {
                                        $day = date('Y-m-d', strtotime($dialog['created']));
                                        if ($day !== $last_day) {
                                            echo '<div class="chamado-day"><span>' . $day_label($dialog['created']) . '</span></div>';
                                            $last_day = $day;
                                        }
                                        $is_attachment = !empty($dialog['anexo']);
                                        $is_internal = isset($dialog['interno']) && $dialog['interno'] == 1;
                                        $is_requester = isset($dialog['responsavel_id']) && (int) $dialog['responsavel_id'] === (int) $chamado->dono_id;
                                    ?>
                                        <div class="chamado-message <?php echo $is_requester ? 'from-requester' : 'from-team' ?> <?php echo $is_internal ? 'is-internal' : '' ?>">
                                            <div class="chamado-avatar" title="<?php echo html_escape($full_name($dialog['first_name'], $dialog['last_name'])) ?>"><?php echo html_escape($initials($dialog['first_name'], $dialog['last_name'])) ?></div>
                                            <div class="chamado-bubble">
                                                <div class="chamado-bubble-header">
                                                    <strong><?php echo html_escape($full_name($dialog['first_name'], $dialog['last_name'])) ?></strong>
                                                    <span class="chamado-role"><?php echo $is_requester ? 'Solicitante' : 'Equipe' ?></span>
                                                    <?php if ($is_internal) { ?><span class="label label-warning"><i class="fa fa-lock"></i> Interno</span><?php } ?>
                                                    <?php if ($is_attachment) { ?><span class="label label-default"><i class="fa fa-paperclip"></i> Anexo</span><?php } ?>
                                                    <span class="chamado-time"><?php echo date('H:i', strtotime($dialog['created'])) ?></span>
                                                </div>
                                                <?php if (trim((string) $dialog['descricao']) !== '') { ?>
                                                    <div class="chamado-bubble-body"><?php echo $format_text($dialog['descricao']); ?></div>
                                                <?php } ?>
                                                <?php if ($is_attachment) { ?>
                                                    <a href="<?php echo base_url("upload/anexo/" . $dialog['anexo']) ?>" target="_blank" class="chamado-file" download>
                                                        <i class="fa fa-file-o"></i> <?php echo html_escape($dialog['anexo']) ?> <i class="fa fa-download"></i>
                                                    </a>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>

                        <div role="tabpanel" class="tab-pane" id="checklist">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <?php if (isset($checklists_associados) && !empty($checklists_associados)): ?>
                                                    <?php foreach ($checklists_associados as $checklist): ?>
                                                        <div class="box box-solid" style="margin-bottom: 20px; border: 1px solid #d2d6de;" data-checklist-id="<?php echo $checklist->id ?>">
                                                            <div class="box-header with-border" style="background-color: #f4f4f4;">
                                                                <h3 class="box-title">
                                                                    <i class="fa fa-list-alt"></i> <?php echo $checklist->nome ?>
                                                                </h3>
                                                                <div class="box-tools pull-right">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-success"
                                                                        onclick="abrirModalAdicionarEtapaExistente(<?php echo $checklist->id ?>, '<?php echo addslashes($checklist->nome) ?>')"
                                                                        style="margin-right: 5px;">
                                                                        <i class="fa fa-plus-circle"></i> Adicionar Etapa
                                                                    </button>
                                                                    <span class="badge bg-blue"><?php echo count($checklist->etapas) ?> etapa(s)</span>
                                                                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                                                        <i class="fa fa-minus"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <div class="box-body" style="padding: 15px;">
                                                                <?php if (!empty($checklist->descricao)): ?>
                                                                    <p><strong>Descrição:</strong> <?php echo $checklist->descricao ?></p>
                                                                <?php endif; ?>

                                                                <?php if ($checklist->data_inicio): ?>
                                                                    <p><strong>Data de início:</strong> <?php echo date('d/m/Y', strtotime($checklist->data_inicio)) ?></p>
                                                                <?php endif; ?>

                                                                <?php if (isset($checklist->etapas) && !empty($checklist->etapas)): ?>
                                                                    <table class="table table-bordered table-striped table-hover" style="margin-top: 15px;">
                                                                        <thead>
                                                                            <tr>
                                                                                <th width="5%">#</th>
                                                                                <th width="25%">Etapa</th>
                                                                                <th width="15%">Data Início</th>
                                                                                <th width="15%">Data Prazo</th>
                                                                                <th width="20%">Responsável</th>
                                                                                <th width="10%">Status</th>
                                                                                <th width="10%">Ações</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php foreach ($checklist->etapas as $index => $etapa): ?>
                                                                                <tr id="etapa-row-<?php echo $etapa->id ?>" class="<?php echo ($etapa->concluida == 1) ? 'success' : (($etapa->data_prazo && $etapa->data_prazo < date('Y-m-d') && $etapa->concluida == 0) ? 'danger' : '') ?>">
                                                                                    <td class="text-center"><?php echo $index + 1 ?></td>
                                                                                    <td>
                                                                                        <strong><?php echo $etapa->nome ?></strong>
                                                                                        <?php if (!empty($etapa->descricao)): ?>
                                                                                            <br><small class="text-muted"><?php echo $etapa->descricao ?></small>
                                                                                        <?php endif; ?>
                                                                                    </td>
                                                                                    <td>
                                                                                        <div class="input-group date datetimepicker-checklist-inline" style="width: 130px;">
                                                                                            <input type="text"
                                                                                                class="form-control input-sm data-inicio-etapa"
                                                                                                id="data_inicio_etapa_<?php echo $etapa->id ?>"
                                                                                                value="<?php echo ($etapa->data_inicio) ? date('d/m/Y', strtotime($etapa->data_inicio)) : '' ?>"
                                                                                                data-etapa-id="<?php echo $etapa->id ?>"
                                                                                                placeholder="__/__/____">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-calendar"></i>
                                                                                            </span>
                                                                                        </div>
                                                                                    </td>
                                                                                    <td>
                                                                                        <div class="input-group date datetimepicker-checklist-inline" style="width: 130px;">
                                                                                            <input type="text"
                                                                                                class="form-control input-sm data-prazo-etapa"
                                                                                                id="data_prazo_etapa_<?php echo $etapa->id ?>"
                                                                                                value="<?php echo ($etapa->data_prazo) ? date('d/m/Y', strtotime($etapa->data_prazo)) : '' ?>"
                                                                                                data-etapa-id="<?php echo $etapa->id ?>"
                                                                                                placeholder="__/__/____">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-calendar"></i>
                                                                                            </span>
                                                                                        </div>
                                                                                    </td>
                                                                                    <td>
                                                                                        <div class="form-group">
                                                                                            <div class="col-sm-12">
                                                                                                <?php
                                                                                                $responsavel_etapa = $responsavel;
                                                                                                $responsavel_etapa['id'] = 'responsavel_etapa_' . $etapa->id;
                                                                                                $responsavel_etapa['selected'] = $etapa->responsavel;
                                                                                                $responsavel_etapa['data-responsavel'] = $etapa->responsavel;
                                                                                                echo form_dropdown($responsavel_etapa);
                                                                                                ?>
                                                                                            </div>
                                                                                        </div>
                                                                                    </td>
                                                                                    <td class="text-center">
                                                                                        <div class="checkbox" style="margin: 0;">
                                                                                            <label>
                                                                                                <input type="checkbox"
                                                                                                    class="etapa-concluida"
                                                                                                    id="etapa_concluida_<?php echo $etapa->id ?>"
                                                                                                    data-etapa-id="<?php echo $etapa->id ?>"
                                                                                                    <?php echo ($etapa->concluida == 1) ? 'checked' : '' ?>>
                                                                                                <span class="label <?php echo ($etapa->concluida == 1) ? 'label-success' : (($etapa->data_prazo && $etapa->data_prazo < date('Y-m-d') && $etapa->concluida == 0) ? 'label-danger' : 'label-default') ?>">
                                                                                                    <?php echo ($etapa->concluida == 1) ? 'Concluído' : (($etapa->data_prazo && $etapa->data_prazo < date('Y-m-d') && $etapa->concluida == 0) ? 'Atrasado' : 'Pendente') ?>
                                                                                                </span>
                                                                                            </label>
                                                                                        </div>
                                                                                    </td>
                                                                                    <td class="text-center">
                                                                                        <button type="button"
                                                                                            class="btn btn-xs btn-primary salvar-etapa"
                                                                                            data-etapa-id="<?php echo $etapa->id ?>"
                                                                                            title="Salvar alterações">
                                                                                            <i class="fa fa-save"></i>
                                                                                        </button>
                                                                                        <?php if ($etapa->etapa_original_id == 0): ?>
                                                                                            <button type="button"
                                                                                                class="btn btn-xs btn-danger excluir-etapa-personalizada"
                                                                                                data-etapa-id="<?php echo $etapa->id ?>"
                                                                                                title="Excluir etapa personalizada">
                                                                                                <i class="fa fa-trash"></i>
                                                                                            </button>
                                                                                        <?php endif; ?>
                                                                                    </td>
                                                                                </tr>
                                                                            <?php endforeach; ?>
                                                                        </tbody>
                                                                    </table>
                                                                <?php else: ?>
                                                                    <div class="alert alert-info" style="margin-bottom: 0;">
                                                                        <i class="fa fa-info-circle"></i> Nenhuma etapa associada a este checklist.
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <div class="alert alert-warning">
                                                        <i class="fa fa-exclamation-triangle"></i> Nenhum checklist associado a este chamado.
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="box chamado-actions">
                    <div class="box-body">
                        <?php if (@$acesso_status == true) { ?>
                            <a href="javascript:void(0)" id="<?php echo $chamado->id ?>" class="btn btn-success btn-block statusChamado"><i class="fa fa-refresh" aria-hidden="true"></i> <?php echo lang('chamados_att_status') ?></a>
                        <?php } ?>
                        <div class="chamado-actions-grid">
                            <?php if (@$acesso_escalar == true) { ?>
                                <a href="javascript:void(0)" id="<?php echo $chamado->id ?>" class="btn btn-default escalarChamado"><i class="fa fa-random" aria-hidden="true"></i> <?php echo lang('chamados_escalar') ?></a>
                            <?php } ?>
                            <?php if (@$acesso_apontar_manual == true) { ?>
                                <a href="javascript:void(0)" id="<?php echo $chamado->id ?>" class="btn btn-default apontarChamado"><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo lang('chamados_apontar') ?></a>
                            <?php } ?>
                            <?php if (@$acesso_edit == true) { ?>
                                <a href="<?php echo base_url('chamados/edit/' . $chamado->id) ?>" class="btn btn-default"><i class="fa fa-edit" aria-hidden="true"></i> <?php echo lang('chamados_edit') ?></a>
                            <?php } ?>
                            <?php if (@$acesso_imprimir == true) { ?>
                                <a href="<?php echo base_url('chamados/imprimir/' . $chamado->id) ?>" class="btn btn-default"><i class="fa fa-print" aria-hidden="true"></i> <?php echo lang('chamados_imprimir') ?></a>
                            <?php } ?>
                            <?php if (@$acesso_associar_checklist == true) { ?>
                                <a href="#" id="<?php echo $chamado->id ?>" class="btn btn-default associarChecklist" data-toggle="modal" data-target="#associarChecklistModal"><i class="fa fa-check-square-o" aria-hidden="true"></i> Checklist</a>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <?php if (@$acesso_apontar_automatico == true) { ?>
                    <div class="box chamado-timer">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-hourglass-half"></i> <?php echo lang('chamados_apontar') ?></h3>
                        </div>
                        <div class="box-body">
                            <input type="hidden" name="apontamento_id" id="apontamento_id" value="<?php echo ($apontamento_ativo->num_rows() > 0) ? $apontamento_ativo->row()->id : '' ?>">
                            <div class="chamado-timer-row left-myday">
                                <?php if ($apontamento_ativo->num_rows() > 0) {
                                    $hora_inicio = new DateTime($apontamento_ativo->row()->data . " " . $apontamento_ativo->row()->hora_inicio);
                                    $diff = $hora_inicio->diff(new DateTime(date("Y-m-d H:i:s")));
                                    $horas = ($diff->days * 24) + $diff->format('%h');
                                ?>
                                    <a class="btn btn-app" onclick="stopApontamento()"><i class="fa fa-stop"></i> Parar</a>
                                    <div class="duration-clock"><?php echo $horas . ":" . $diff->format('%i') . ":" . $diff->format('%s') ?></div>
                                <?php } else { ?>
                                    <a class="btn btn-app" onclick="startApontamento()"><i class="fa fa-play"></i> Iniciar</a>
                                    <div class="duration-clock">00:00:00</div>
                                <?php } ?>
                            </div>
                            <div class="informations-apontamento">
                                <input type="text" class="form-control" name="obs_apontamento" id="obs_apontamento" value="" placeholder="Observação do apontamento (opcional)" <?php echo ($apontamento_ativo->num_rows() > 0) ? '' : 'readonly="readonly"' ?>>
                                <div class="progress progress-xxs active">
                                    <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
                                </div>
                                <div class="inicio-apontamento text-muted small" style="display:none;">Início <span class="inicio-apontamento-span"><?php echo ($apontamento_ativo->num_rows() > 0) ? $apontamento_ativo->row()->hora_inicio : '' ?></span></div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <div class="box">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-users"></i> Pessoas</h3></div>
                    <div class="box-body">
                        <dl class="chamado-facts">
                            <dt><?php echo lang('chamados_dono') ?></dt>
                            <dd><?php echo html_escape($full_name($chamado->first_name_dono, $chamado->last_name_dono)) ?></dd>
                            <dt><?php echo lang('chamados_responsavel') ?></dt>
                            <dd><?php echo $chamado->responsavel_id ? html_escape($full_name($chamado->first_name, $chamado->last_name)) : '<span class="label label-warning">Sem responsável</span>' ?></dd>
                            <dt><?php echo lang('chamados_desenvolvedor') ?></dt>
                            <dd><?php echo html_escape($full_name($chamado->first_name_dev, $chamado->last_name_dev)) ?></dd>
                            <dt><?php echo lang('chamados_cliente') ?></dt>
                            <dd><a href="<?php echo base_url('unidades/edit/' . $chamado->unidade_id) ?>"><?php echo html_escape($chamado->cliente) ?></a></dd>
                        </dl>
                    </div>
                </div>

                <div class="box">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-calendar"></i> Datas</h3></div>
                    <div class="box-body">
                        <dl class="chamado-facts">
                            <dt><?php echo lang('chamados_created') ?></dt>
                            <dd><?php echo $format_datetime($chamado->created) ?></dd>
                            <dt><?php echo lang('chamados_prazo') ?></dt>
                            <dd class="<?php echo $is_overdue ? 'text-red' : '' ?>"><?php echo $format_datetime($chamado->data_prazo) ?><?php echo $is_overdue ? ' <strong>(atrasado)</strong>' : '' ?></dd>
                            <dt><?php echo lang('chamados_inicio') ?></dt>
                            <dd><?php echo $format_datetime($chamado->data_inicio) ?></dd>
                            <dt><?php echo lang('chamados_finalizacao') ?></dt>
                            <dd><?php echo $format_datetime($chamado->data_finalizacao) ?></dd>
                            <dt><?php echo lang('chamados_atualizado_por') ?></dt>
                            <dd><?php echo html_escape($chamado->atualizado_por_nome ?: '—') ?></dd>
                        </dl>
                    </div>
                </div>

                <div class="box">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-tags"></i> Classificação</h3></div>
                    <div class="box-body">
                        <dl class="chamado-facts">
                            <dt><?php echo lang('chamados_status') ?></dt>
                            <dd><span class="label" style="background-color: <?php echo $status_color ?>; color: <?php echo $status_text_color ?>; font-weight: 600; font-size: 12px; padding: 4px 8px; border-radius: 4px;"><?php echo html_escape($chamado->status) ?></span></dd>
                            <dt><?php echo lang('chamados_prioridade') ?></dt>
                            <dd><?php echo $has_prioridade ? form_dropdown($prioridade) : html_escape($chamado->prioridade) ?></dd>
                            <dt><?php echo lang('chamados_nivel') ?></dt>
                            <dd><?php echo html_escape($chamado->nivel_desc ?: '—') ?></dd>
                        </dl>
                    </div>
                </div>

                <div class="box <?php echo $time_entries ? '' : 'collapsed-box' ?>">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-clock-o"></i> Horas apontadas <small><?php echo $total_effort ?> h</small></h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa <?php echo $time_entries ? 'fa-minus' : 'fa-plus' ?>"></i></button>
                        </div>
                    </div>
                    <div class="box-body no-padding">
                        <?php if (!$time_entries) { ?>
                            <p class="text-muted chamado-empty">Nenhum apontamento no último ano.</p>
                        <?php } else { ?>
                            <ul class="chamado-entries">
                                <?php foreach ($time_entries as $entry) { ?>
                                    <li>
                                        <div><strong><?php echo html_escape($entry->nome_usuario) ?></strong> <span class="pull-right"><?php echo html_escape(substr((string) $entry->esforco, 0, 5)) ?> h</span></div>
                                        <div class="text-muted small">
                                            <?php echo date('d/m/Y', strtotime($entry->data)) ?>
                                            <?php if ($entry->hora_inicio) { ?> · <?php echo html_escape(substr((string) $entry->hora_inicio, 0, 5)) ?><?php echo $entry->hora_termino ? ' – ' . html_escape(substr((string) $entry->hora_termino, 0, 5)) : '' ?><?php } ?>
                                        </div>
                                        <?php if (!empty($entry->observacao)) { ?><div class="small"><?php echo html_escape($entry->observacao) ?></div><?php } ?>
                                    </li>
                                <?php } ?>
                            </ul>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>


<!-- Modal Escalar -->
<?php
$escalar_responsavel_atual = $chamado->responsavel_id ? $full_name($chamado->first_name, $chamado->last_name) : 'Sem responsável';
$escalar_dev_atual = $chamado->desenvolvedor_id ? $full_name($chamado->first_name_dev, $chamado->last_name_dev) : 'Nenhum';
$escalar_horas_previstas = ($chamado->horas_prevista !== null && $chamado->horas_prevista !== '') ? number_format((float) $chamado->horas_prevista, 1, ',', '.') . ' h' : 'Não definidas';
?>
<div id="escalarModal" class="modal fade" role="dialog" aria-labelledby="escalarModalTitulo">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content escalar-modal">
            <?php echo form_open(base_url('chamados/escalar'), array('id' => 'form-escalar_chamado', 'enctype' => 'multipart/form-data')); ?>
            <input type='hidden' id='chamado_id' name='chamado_id' value='<?php echo $chamado->id ?>'>
            <input type="hidden" name="base_url" id="base_url" value="<?php echo base_url() ?>">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">&times;</button>
                <h4 class="modal-title" id="escalarModalTitulo">Escalar chamado #<?php echo (int) $chamado->id ?></h4>
                <div class="escalar-subtitle"><?php echo html_escape($chamado->titulo) ?></div>
            </div>

            <div class="modal-body">
                <div class="escalar-current">
                    <div class="escalar-current-title"><i class="fa fa-info-circle"></i> Situação atual</div>
                    <div class="escalar-current-grid">
                        <div><span>Status</span><strong><span class="label" style="background-color: <?php echo $status_color ?>; color: <?php echo $status_text_color ?>; font-weight: 600; font-size: 12px; padding: 4px 8px; border-radius: 4px;"><?php echo html_escape($chamado->status) ?></span></strong></div>
                        <div><span>Responsável</span><strong><?php echo html_escape($escalar_responsavel_atual) ?></strong></div>
                        <div><span>Programador</span><strong><?php echo html_escape($escalar_dev_atual) ?></strong></div>
                        <div><span>Prioridade</span><strong><?php echo html_escape($chamado->prioridade) ?></strong></div>
                        <div><span>Previsão</span><strong class="<?php echo $is_overdue ? 'text-red' : '' ?>"><?php echo $format_datetime($chamado->data_prazo) ?><?php echo $is_overdue ? ' (atrasado)' : '' ?></strong></div>
                        <div><span>Horas previstas / apontadas</span><strong><?php echo $escalar_horas_previstas ?> / <?php echo $total_effort ?> h</strong></div>
                    </div>
                    <?php if ($path) { ?>
                        <div class="escalar-current-path"><?php echo html_escape($chamado->cliente) ?> · <?php echo implode(' <i class="fa fa-angle-right"></i> ', $path) ?><?php echo $chamado->nivel_desc ? ' · ' . html_escape($chamado->nivel_desc) : '' ?></div>
                    <?php } ?>
                </div>

                <div class="escalar-section">
                    <h5><span class="escalar-step">1</span> Para quem vai o chamado</h5>
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label for="responsavel"><?php echo lang('chamados_responsavel') ?></label>
                            <?php echo form_dropdown($responsavel); ?>
                            <p class="help-block">Em branco, continua com <strong><?php echo html_escape($escalar_responsavel_atual) ?></strong>.</p>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label for="desenvolvedor">Programador</label>
                            <?php echo form_dropdown($desenvolvedor); ?>
                            <p class="help-block">Em branco, continua com <strong><?php echo html_escape($escalar_dev_atual) ?></strong>.</p>
                        </div>
                    </div>
                </div>

                <div class="escalar-section">
                    <h5><span class="escalar-step">2</span> Status e prazo<?php echo !empty($acesso_planejar) ? ' e planejamento' : '' ?></h5>
                    <div class="row">
                        <div class="col-sm-4 form-group">
                            <label for="status"><?php echo lang('chamados_status') ?> <span class="text-red">*</span></label>
                            <?php echo form_dropdown($status); ?>
                        </div>
                        <div class="col-sm-5 form-group">
                            <label for="data_reagendar">Nova previsão</label>
                            <div class="input-group date" id='datetimehourpicker'>
                                <?php if (@$acesso_reagendar == true) { ?>
                                    <?php echo form_input(array('id' => 'data_reagendar', 'name' => 'data_reagendar', 'value' => ($chamado->data_prazo) ? date("d/m/Y H:i", strtotime($chamado->data_prazo)) : '', 'class' => 'form-control')); ?>
                                <?php } else { ?>
                                    <?php echo form_input(array('id' => 'data_reagendar', 'name' => 'data_reagendar', 'value' => '', 'class' => 'form-control disabled', 'disabled' => 'disabled', 'placeholder' => 'Calculada pelo tipo de serviço')); ?>
                                <?php } ?>
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            </div>
                            <p class="help-block">
                                <?php echo (@$acesso_reagendar == true)
                                    ? 'Ao trocar o tipo de serviço, a previsão e as horas são recalculadas; ajuste se precisar.'
                                    : 'Você não pode reagendar: a previsão é calculada pelo prazo padrão do tipo de serviço.'; ?>
                            </p>
                        </div>
                        <div class="col-sm-3 form-group">
                            <label for="horas_prevista">Horas previstas</label>
                            <div class="input-group">
                                <?php echo form_input(array('id' => 'horas_prevista', 'name' => 'horas_prevista', 'value' => $horas_prevista, 'class' => 'form-control', 'maxlength' => "5", 'placeholder' => 'Ex.: 1,5')); ?>
                                <span class="input-group-addon">h</span>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($acesso_planejar)) { ?>
                        <div class="row escalar-planejamento">
                            <div class="col-sm-4 form-group">
                                <label for="data_planejada">Data planejada <small class="text-muted">(opcional)</small></label>
                                <div class="input-group date" id="datetimepicker_planejada">
                                    <input type="text" id="data_planejada" name="data_planejada" class="form-control" autocomplete="off" placeholder="dd/mm/aaaa" value="<?php echo $data_planejada ? date('d/m/Y', strtotime($data_planejada)) : '' ?>">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                            <div class="col-sm-8">
                                <p class="help-block escalar-planejamento-ajuda">
                                    <i class="fa fa-calendar-check-o"></i>
                                    Dia em que o chamado entra no planejamento da equipe, na agenda de <strong id="escalar-planejado-para"><?php echo html_escape($chamado->desenvolvedor_id ? $escalar_dev_atual : $escalar_responsavel_atual) ?></strong>.
                                    <?php if ($data_planejada) { ?>
                                        Hoje está planejado para <strong><?php echo date('d/m/Y', strtotime($data_planejada)) ?></strong>.
                                    <?php } else { ?>
                                        Ainda não está planejado.
                                    <?php } ?>
                                </p>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div class="escalar-section">
                    <h5>
                        <span class="escalar-step">3</span> Classificação
                        <a href="#escalarClassificacao" class="escalar-toggle" data-toggle="collapse" aria-expanded="false">alterar cliente, produto ou serviço <i class="fa fa-angle-down"></i></a>
                    </h5>
                    <p class="text-muted small escalar-hint">Já vem preenchida com a classificação atual. Abra só se o chamado estiver no lugar errado.</p>
                    <div id="escalarClassificacao" class="collapse">
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label for="unidade">Cliente <span class="text-red">*</span></label>
                                <?php echo form_dropdown($unidade); ?>
                            </div>
                            <div class="col-sm-6 form-group">
                                <label for="produto_modulo_categoria_edit"><?php echo lang('chamados_produto') ?> <span class="text-red">*</span></label>
                                <?php echo form_dropdown($produto); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 form-group">
                                <label for="modulo_edit"><?php echo lang('chamados_modulo') ?> <span class="text-red">*</span></label>
                                <?php echo form_dropdown($modulo); ?>
                            </div>
                            <div class="col-sm-4 form-group">
                                <label for="categoria_edit"><?php echo lang('chamados_categoria') ?> <span class="text-red">*</span></label>
                                <?php echo form_dropdown($categoria); ?>
                            </div>
                            <div class="col-sm-4 form-group">
                                <label for="tipo_servico"><?php echo lang('chamados_tipo_servico') ?> <span class="text-red">*</span></label>
                                <?php echo form_dropdown($tipo_servico); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 form-group">
                                <label for="nivel"><?php echo lang('chamados_nivel') ?> <span class="text-red">*</span></label>
                                <?php echo form_dropdown($nivel); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="escalar-section">
                    <h5><span class="escalar-step">4</span> Mensagem para quem vai assumir <small class="text-muted">(recomendado)</small></h5>
                    <textarea id='dialogo' name='dialogo' class='form-control' rows='4' placeholder='O que já foi feito, o que falta e o motivo de escalar. Ex.: "Erro reproduzido no ambiente do cliente; precisa de ajuste na rotina de faturamento."'></textarea>
                    <div class="escalar-attach">
                        <label class='btn btn-default btn-sm' for='anexo-escalar'>
                            <input type='file' id='anexo-escalar' name='anexo' style='display:none' onchange="$('#upload-file-info-escalar').text(this.files.length ? this.files[0].name : '')">
                            <i class="fa fa-paperclip"></i> Anexar arquivo
                        </label>
                        <span class='text-muted small' id='upload-file-info-escalar'></span>
                    </div>
                    <p class="help-block">A mensagem e as mudanças de responsável, programador, serviço e prazo ficam registradas na conversa do chamado.</p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning" id="btn-escalar"><i class="fa fa-random"></i> Escalar chamado</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="statusModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <?php echo form_open(base_url('chamados/status'), array('class' => 'form-horizontal', 'id' => 'form-status_chamado')); ?>
                <input type='hidden' id='chamado_id' name='chamado_id' value='<?php echo $chamado->id ?>'>
                <div class="form-group">
                    <?php echo lang('chamados_transferir', 'responsavel', array('class' => 'col-sm-4 control-label'), false); ?>
                    <div class="col-sm-6">
                        <?php echo form_dropdown($responsavel); ?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo lang('chamados_status', 'status', array('class' => 'col-sm-4 control-label'), true); ?>
                    <div class="col-sm-6">
                        <?php echo form_dropdown($status); ?>
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

<div id="apontamentoExistenteModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>

    </div>
</div>

<?php if (@$acesso_apontar_manual == true) {
    $apontar_previsto_seg = ($chamado->horas_prevista !== null && $chamado->horas_prevista !== '') ? (int) round((float) $chamado->horas_prevista * 3600) : null;
    $apontar_saldo_seg = $apontar_previsto_seg !== null ? $apontar_previsto_seg - $total_seconds : null;
    $apontar_fmt = function ($seconds) {
        $seconds = abs((int) $seconds);
        return sprintf('%d:%02d', floor($seconds / 3600), floor(($seconds % 3600) / 60));
    };
    $apontar_hoje_seg = isset($apontado_hoje_segundos) ? (int) $apontado_hoje_segundos : 0;
    $apontar_meta_seg = isset($meta_diaria_horas) && $meta_diaria_horas !== null ? (int) round($meta_diaria_horas * 3600) : null;
    $apontar_meta_pct = $apontar_meta_seg ? min(100, round($apontar_hoje_seg / $apontar_meta_seg * 100)) : 0;
    $apontar_meus_seg = 0;
    foreach ($time_entries as $entry) {
        if ((int) $entry->user_id === (int) $this->session->userdata('user_id')) {
            $parts = array_map('intval', explode(':', (string) $entry->esforco));
            $apontar_meus_seg += (isset($parts[0]) ? $parts[0] : 0) * 3600 + (isset($parts[1]) ? $parts[1] : 0) * 60;
        }
    }
    $apontar_entries = $time_entries;
    usort($apontar_entries, function ($a, $b) {
        return strcmp($b->data . ' ' . $b->hora_inicio, $a->data . ' ' . $a->hora_inicio);
    });
?>
    <!-- Modal Apontamento -->
    <div id="apontarModal" class="modal fade" role="dialog" aria-labelledby="apontarModalTitulo">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content apontar-modal">
                <?php echo form_open(base_url('chamados/apontamento_manual_criar'), array('id' => 'form-apontar_chamado')); ?>
                <input type='hidden' id='chamado_id' name='chamado_id' value='<?php echo $chamado->id ?>'>
                <input type='hidden' id='id' name='id' value=''>

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">&times;</button>
                    <h4 class="modal-title" id="apontarModalTitulo">Apontar horas no chamado #<?php echo (int) $chamado->id ?></h4>
                    <div class="apontar-subtitle"><?php echo html_escape($chamado->titulo) ?></div>
                </div>

                <div class="modal-body">
                    <div class="apontar-stats">
                        <div class="apontar-stat">
                            <span>Previsto no chamado</span>
                            <strong><?php echo $apontar_previsto_seg !== null ? $apontar_fmt($apontar_previsto_seg) . ' h' : '—' ?></strong>
                        </div>
                        <div class="apontar-stat">
                            <span>Já apontado</span>
                            <strong><?php echo $total_effort ?> h</strong>
                            <small>Você: <?php echo $apontar_fmt($apontar_meus_seg) ?> h</small>
                        </div>
                        <div class="apontar-stat <?php echo ($apontar_saldo_seg !== null && $apontar_saldo_seg < 0) ? 'is-over' : '' ?>">
                            <span>Saldo</span>
                            <?php if ($apontar_saldo_seg === null) { ?>
                                <strong>—</strong><small>Sem horas previstas</small>
                            <?php } elseif ($apontar_saldo_seg >= 0) { ?>
                                <strong><?php echo $apontar_fmt($apontar_saldo_seg) ?> h</strong><small>Restante</small>
                            <?php } else { ?>
                                <strong>+<?php echo $apontar_fmt($apontar_saldo_seg) ?> h</strong><small>Acima do previsto</small>
                            <?php } ?>
                        </div>
                        <div class="apontar-stat">
                            <span>Seu dia (todos os chamados)</span>
                            <strong><?php echo $apontar_fmt($apontar_hoje_seg) ?> h<?php echo $apontar_meta_seg ? ' <em>de ' . $apontar_fmt($apontar_meta_seg) . ' h</em>' : '' ?></strong>
                            <?php if ($apontar_meta_seg) { ?>
                                <div class="progress progress-xxs"><div class="progress-bar <?php echo $apontar_hoje_seg >= $apontar_meta_seg ? 'progress-bar-success' : 'progress-bar-warning' ?>" style="width: <?php echo $apontar_meta_pct ?>%"></div></div>
                                <small><?php echo $apontar_hoje_seg >= $apontar_meta_seg ? 'Meta diária atingida' : 'Faltam ' . $apontar_fmt($apontar_meta_seg - $apontar_hoje_seg) . ' h para a meta' ?></small>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="apontar-form">
                        <div class="row">
                            <div class="col-sm-3 form-group">
                                <label for="data"><?php echo lang('chamados_data') ?> <span class="text-red">*</span></label>
                                <div class="input-group date" id='datetimepicker'>
                                    <?php echo form_input(array('id' => 'data', 'name' => 'data', 'value' => date('d/m/Y'), 'class' => 'form-control')); ?>
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                            <div class="col-sm-3 form-group">
                                <label for="hora_inicio"><?php echo lang('chamados_hora_inicio') ?></label>
                                <div class="input-group date" id='datetimehouronlypicker1'>
                                    <?php echo form_input(array('id' => 'hora_inicio', 'name' => 'hora_inicio', 'value' => '', 'class' => 'form-control', 'placeholder' => 'Opcional')); ?>
                                    <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
                                </div>
                            </div>
                            <div class="col-sm-3 form-group">
                                <label for="hora_termino"><?php echo lang('chamados_hora_termino') ?></label>
                                <div class="input-group date" id='datetimehouronlypicker3'>
                                    <?php echo form_input(array('id' => 'hora_termino', 'name' => 'hora_termino', 'value' => '', 'class' => 'form-control', 'placeholder' => 'Opcional')); ?>
                                    <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
                                </div>
                            </div>
                            <div class="col-sm-3 form-group">
                                <label for="esforco">Esforço <span class="text-red">*</span></label>
                                <?php echo form_input(array('id' => 'esforco', 'name' => 'esforco', 'value' => '00:00', 'class' => 'form-control required apontar-esforco', 'onkeypress' => "formatTime(this)", 'maxlength' => "5", 'placeholder' => 'hh:mm')); ?>
                            </div>
                        </div>
                        <div class="apontar-quick">
                            <span class="text-muted small">Esforço rápido:</span>
                            <?php foreach (array('00:15', '00:30', '01:00', '02:00', '04:00') as $quick) { ?>
                                <button type="button" class="btn btn-default btn-xs js-apontar-rapido" data-esforco="<?php echo $quick ?>"><?php echo ltrim(substr($quick, 0, 2), '0') === '' ? (int) substr($quick, 3) . ' min' : (int) substr($quick, 0, 2) . ' h' ?></button>
                            <?php } ?>
                        </div>
                        <p class="help-block">Informe o esforço (hh:mm) ou o início e o término: o outro campo é calculado automaticamente.</p>
                        <div class="form-group">
                            <label for="observacao">O que foi feito</label>
                            <textarea id='observacao' name='observacao' class='form-control' rows='3' placeholder='Ex.: Análise do erro na emissão da nota e ajuste na regra de impostos.'></textarea>
                        </div>
                        <p class="text-danger small apontar-erro" id="apontar-erro" style="display:none;"></p>
                    </div>

                    <div class="apontar-history">
                        <div class="apontar-history-title">
                            <strong>Apontamentos neste chamado</strong>
                            <span class="text-muted small"><?php echo count($apontar_entries) ?> registro<?php echo count($apontar_entries) === 1 ? '' : 's' ?> · total <?php echo $total_effort ?> h · último ano</span>
                        </div>
                        <?php if (!$apontar_entries) { ?>
                            <p class="text-muted small apontar-empty">Ainda não há apontamentos neste chamado.</p>
                        <?php } else { ?>
                            <div class="apontar-history-list">
                                <table class="table table-condensed">
                                    <thead><tr><th>Data</th><th>Quem</th><th>Período</th><th class="text-right">Esforço</th><th>Observação</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($apontar_entries as $entry) {
                                            $mine = (int) $entry->user_id === (int) $this->session->userdata('user_id');
                                            $running = substr((string) $entry->esforco, 0, 5) === '00:00' && substr((string) $entry->hora_termino, 0, 5) === '00:00';
                                        ?>
                                            <tr class="<?php echo $mine ? 'is-mine' : '' ?>">
                                                <td><?php echo date('d/m/Y', strtotime($entry->data)) ?></td>
                                                <td><?php echo html_escape($entry->nome_usuario) ?><?php echo $mine ? ' <span class="label label-primary">você</span>' : '' ?></td>
                                                <td><?php echo $entry->hora_inicio ? html_escape(substr((string) $entry->hora_inicio, 0, 5)) . ($running ? ' – em andamento' : ($entry->hora_termino ? ' – ' . html_escape(substr((string) $entry->hora_termino, 0, 5)) : '')) : '—' ?></td>
                                                <td class="text-right"><strong><?php echo $running ? '—' : html_escape(substr((string) $entry->esforco, 0, 5)) ?></strong></td>
                                                <td><?php echo html_escape($entry->observacao) ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btn-apontar"><i class="fa fa-check"></i> Salvar apontamento</button>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        // Ouvintes nativos registrados na hora: não dependem do evento "load" da janela, que atrasa
        // enquanto imagens e scripts externos da página carregam, nem do jQuery (carregado no rodapé).
        document.addEventListener('click', function(event) {
            var button = event.target.closest ? event.target.closest('.js-apontar-rapido') : null;
            if (!button) {
                return;
            }
            var field = document.getElementById('esforco');
            field.value = button.getAttribute('data-esforco');
            document.getElementById('apontar-erro').style.display = 'none';
            // Dispara o blur para o funcoes.js recalcular o término a partir do início.
            if (window.jQuery) {
                window.jQuery(field).trigger('blur');
            }
        });
        document.addEventListener('submit', function(event) {
            if (event.target.id !== 'form-apontar_chamado') {
                return;
            }
            var effort = document.getElementById('esforco').value.trim();
            var message = document.getElementById('apontar-erro');
            if (!/^\d{1,2}:\d{2}$/.test(effort) || /^0{1,2}:00$/.test(effort)) {
                event.preventDefault();
                message.textContent = 'Informe o esforço em horas e minutos (ex.: 01:30) ou o início e o término.';
                message.style.display = 'block';
                document.getElementById('esforco').focus();
                return;
            }
            var submit = document.getElementById('btn-apontar');
            submit.disabled = true;
            submit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Salvando...';
        });
    </script>
<?php } ?>

<!-- Modal Associar Checklist  -->
<div id="associarChecklistModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Associar Checklists</h4>
            </div>
            <div class="modal-body">
                <?php if (isset($checklists) && $checklists->num_rows() > 0): ?>

                    <!-- CAMPO DE BUSCA -->
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-12">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                <input type="text" id="search-checklist" class="form-control" placeholder="Buscar checklist...">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" onclick="document.getElementById('search-checklist').value = ''; filtrarChecklists();">
                                        <i class="fa fa-times"></i> Limpar
                                    </button>
                                </span>
                            </div>
                            <small class="text-muted" id="search-results-count">Mostrando todos os checklists</small>
                        </div>
                    </div>

                    <form id="form-associar-checklist-server">
                        <!-- Botão para selecionar/desmarcar todos -->
                        <div class="row" style="margin-bottom: 15px;">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-sm btn-default" onclick="selecionarTodosChecklists()" style="margin-right: 5px;">
                                    <i class="fa fa-check-square-o"></i> Selecionar Todos
                                </button>
                                <button type="button" class="btn btn-sm btn-default" onclick="desmarcarTodosChecklists()">
                                    <i class="fa fa-square-o"></i> Desmarcar Todos
                                </button>
                            </div>
                        </div>

                        <!-- CONTAINER COM BARRA DE ROLAGEM -->
                        <div id="checklists-container" style="max-height: 500px; overflow-y: auto; padding-right: 10px;">
                            <?php foreach ($checklists->result() as $cl): ?>
                                <?php $etapas = isset($checklist_etapas[$cl->id]) ? $checklist_etapas[$cl->id] : array(); ?>
                                <div class="card mb-3 shadow-sm checklist-item" data-checklist-id="<?php echo $cl->id ?>" data-checklist-nome="<?php echo strtolower($cl->nome) ?>">
                                    <!-- Cabeçalho do Checklist -->
                                    <div class="card-header clearfix" style="background-color: #f4f4f4; padding: 12px; border: 1px solid #ddd; border-radius: 4px 4px 0 0;">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="checkbox" style="margin:0;">
                                                    <label style="font-weight:bold; font-size:15px;">
                                                        <input type="checkbox" name="checklists[]" value="<?php echo $cl->id ?>" class="checklist-checkbox">
                                                        <i class="fa fa-list-alt"></i> <?php echo $cl->nome ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="input-group date datetimepicker-checklist" style="width: 200px;">
                                                    <?php echo form_input(array(
                                                        'id' => 'data_inicio_' . $cl->id,
                                                        'name' => 'data_inicio[' . $cl->id . ']',
                                                        'value' => date('d/m/Y'),
                                                        'class' => 'form-control input-sm data-inicio-checklist',
                                                        'placeholder' => 'Data de início',
                                                        'data-checklist-id' => $cl->id
                                                    )); ?>
                                                    <span class="input-group-addon">
                                                        <i class="fa fa-calendar"></i>
                                                    </span>
                                                </div>
                                                <small class="text-muted">Data de início</small>
                                            </div>
                                            <div class="col-md-2 text-right">
                                                <span class="badge" style="margin-right: 10px; background-color: #337ab7;"><?php echo count($etapas) ?> etapas</span>
                                                <button class="btn btn-link btn-sm toggle-etapas" type="button" data-toggle="collapse" data-target="#etapas-<?php echo $cl->id ?>" aria-expanded="false">
                                                    <i class="fa fa-chevron-down"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Corpo do Checklist (etapas) -->
                                    <div id="etapas-<?php echo $cl->id ?>" class="collapse">
                                        <div class="panel-body" style="border: 1px solid #ddd; border-top: none; padding: 15px; background-color: #fafafa;">

                                            <!-- Barra de ferramentas das etapas -->
                                            <div class="row" style="margin-bottom: 15px;">
                                                <div class="col-xs-12 col-sm-8">
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-default" onclick="selecionarTodasEtapas(<?php echo $cl->id ?>)">
                                                            <i class="fa fa-check-square-o"></i> Selecionar todas
                                                        </button>
                                                        <button type="button" class="btn btn-default" onclick="desmarcarTodasEtapas(<?php echo $cl->id ?>)">
                                                            <i class="fa fa-square-o"></i> Desmarcar todas
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-4 text-right">
                                                    <button type="button" class="btn btn-sm btn-success" onclick="abrirModalAdicionarEtapa(<?php echo $cl->id ?>, document.getElementById('data_inicio_<?php echo $cl->id ?>').value)">
                                                        <i class="fa fa-plus-circle"></i> Adicionar Etapa
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Lista de etapas existentes com barra de rolagem APENAS VERTICAL -->
                                            <div class="etapas-container" id="etapas-container-<?php echo $cl->id ?>" style="max-height: 300px; overflow-y: auto; overflow-x: hidden; padding-right: 5px;">
                                                <?php if (count($etapas) > 0): ?>
                                                    <?php foreach ($etapas as $index => $et): ?>
                                                        <div class="etapa-item" data-etapa-id="<?php echo $et->id ?>" data-checklist-id="<?php echo $cl->id ?>" data-prazo-padrao="<?php echo isset($et->prazo_padrao) ? (int)$et->prazo_padrao : 0 ?>" style="margin-bottom: 8px; padding: 8px; border: 1px solid #eee; border-radius: 4px; background-color: #fff; width: 100%;">
                                                            <div class="row" style="margin: 0;">
                                                                <div class="col-xs-1 text-center" style="padding: 0; width: 30px;">
                                                                    <span class="badge" style="background-color: #337ab7;"><?php echo $index + 1 ?></span>
                                                                </div>
                                                                <div class="col-xs-1 text-center" style="padding: 0; width: 30px;">
                                                                    <input type="checkbox"
                                                                        name="etapas[<?php echo $cl->id ?>][]"
                                                                        value="<?php echo $et->id ?>"
                                                                        class="etapa-checkbox etapa-checklist-<?php echo $cl->id ?>"
                                                                        id="etapa_<?php echo $cl->id ?>_<?php echo $et->id ?>"
                                                                        style="margin-top: 5px;">
                                                                </div>
                                                                <div class="col-xs-10" style="padding-left: 5px; width: calc(100% - 70px);">
                                                                    <label for="etapa_<?php echo $cl->id ?>_<?php echo $et->id ?>" style="font-weight: normal; cursor: pointer; width: 100%; margin-bottom: 0; word-wrap: break-word;">
                                                                        <strong><?php echo $et->nome ?></strong>
                                                                        <?php if (isset($et->descricao) && $et->descricao != ''): ?>
                                                                            <br><small class="text-muted"><?php echo $et->descricao ?></small>
                                                                        <?php endif; ?>
                                                                        <?php if (isset($et->prazo_padrao) && $et->prazo_padrao > 0): ?>
                                                                            <br><small class="text-info">
                                                                                <i class="fa fa-clock-o"></i> Prazo: <?php echo $et->prazo_padrao ?> dia(s)
                                                                            </small>
                                                                        <?php endif; ?>
                                                                        <span class="etapa-datas-calc" style="display:none;">
                                                                            <br><small class="text-success etapa-data-inicio-calc"><i class="fa fa-play-circle-o"></i> Início: <strong></strong></small>
                                                                            <br><small class="text-danger etapa-data-prazo-calc"><i class="fa fa-flag-checkered"></i> Prazo: <strong></strong></small>
                                                                        </span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <div class="alert alert-info" style="margin: 10px 0;">
                                                        <i class="fa fa-info-circle"></i> Nenhuma etapa cadastrada para este checklist.
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Container para etapas personalizadas adicionadas dinamicamente -->
                                            <div id="etapas-personalizadas-<?php echo $cl->id ?>" class="etapas-personalizadas" style="margin-top: 10px; max-height: 300px; overflow-y: auto; overflow-x: hidden; padding-right: 5px;"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i> Nenhum checklist disponível.
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <div class="pull-left">
                    <span id="checklist-counter" class="text-muted">Selecione um checklist com pelo menos uma etapa</span>
                </div>
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="btnSaveChecklist" onclick="salvarChecklist()" disabled>Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Adicionar Etapa Personalizada -->
<div id="adicionarEtapaModal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" onclick="fecharModalAdicionarEtapa()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Adicionar Nova Etapa Personalizada</h4>
            </div>
            <div class="modal-body">
                <form id="form-adicionar-etapa">
                    <input type="hidden" id="etapa_checklist_id" name="checklist_id" value="">

                    <div class="form-group">
                        <label for="etapa_nome">Nome da Etapa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="etapa_nome" name="nome" required maxlength="255" placeholder="Digite o nome da etapa">
                    </div>

                    <div class="form-group">
                        <label for="etapa_descricao">Descrição</label>
                        <textarea class="form-control" id="etapa_descricao" name="descricao" rows="3" placeholder="Digite uma descrição para a etapa (opcional)"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="etapa_responsavel">Responsável <span class="text-danger">*</span></label>
                        <div class="col-sm-12" style="padding-left: 0;">
                            <?php
                            $responsavel_etapa_modal = $responsavel;
                            $responsavel_etapa_modal['id'] = 'etapa_responsavel';
                            $responsavel_etapa_modal['name'] = 'responsavel';
                            $responsavel_etapa_modal['class'] = 'form-control singleInputDynamic';
                            $responsavel_etapa_modal['selected'] = '';
                            echo form_dropdown($responsavel_etapa_modal);
                            ?>
                        </div>
                        <small class="text-muted" style="display: block; margin-top: 5px;">Selecione o responsável por esta etapa</small>
                    </div>

                    <div class="form-group">
                        <label for="etapa_prazo">Prazo Padrão (dias)</label>
                        <input type="number" class="form-control" id="etapa_prazo" name="prazo_padrao" min="0" value="0" placeholder="0 = sem prazo">
                        <small class="text-muted">Número de dias para conclusão da etapa (considera apenas dias úteis)</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="etapa_data_inicio">Data Início <span class="text-danger">*</span></label>
                                <div class="input-group date datetimepicker-modal-inline">
                                    <input type="text"
                                        class="form-control"
                                        id="etapa_data_inicio"
                                        name="data_inicio"
                                        value=""
                                        placeholder="__/__/____"
                                        readonly>
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="etapa_data_prazo">Data Prazo</label>
                                <div class="input-group date datetimepicker-modal-inline">
                                    <input type="text"
                                        class="form-control"
                                        id="etapa_data_prazo"
                                        name="data_prazo"
                                        value=""
                                        placeholder="__/__/____"
                                        readonly>
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>
                                <small class="text-muted">Será calculada automaticamente com base no prazo padrão</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" onclick="fecharModalAdicionarEtapa()">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarEtapaPersonalizada()">Adicionar Etapa</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Adicionar Etapa em Checklist Existente -->
<div id="adicionarEtapaExistenteModal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Adicionar Nova Etapa ao Checklist</h4>
            </div>
            <div class="modal-body">
                <form id="form-adicionar-etapa-existente">
                    <input type="hidden" id="etapa_existente_checklist_id" name="chamado_checklist_id" value="">
                    <input type="hidden" id="chamado_id_hidden" value="<?php echo $chamado->id ?>">

                    <div class="form-group">
                        <label>Checklist</label>
                        <input type="text" id="etapa_existente_checklist_nome" class="form-control" readonly disabled>
                    </div>

                    <div class="form-group">
                        <label for="etapa_existente_nome">Nome da Etapa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="etapa_existente_nome" name="nome" required maxlength="255" placeholder="Digite o nome da etapa">
                    </div>

                    <div class="form-group">
                        <label for="etapa_existente_descricao">Descrição</label>
                        <textarea class="form-control" id="etapa_existente_descricao" name="descricao" rows="3" placeholder="Digite uma descrição para a etapa (opcional)"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="etapa_existente_prazo">Prazo Padrão (dias) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="etapa_existente_prazo" name="prazo_padrao" min="0" value="0" placeholder="0 = sem prazo">
                        <small class="text-muted">Número de dias para conclusão da etapa a partir da data de início</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="etapa_existente_data_inicio">Data Início <span class="text-danger">*</span></label>
                                <div class="input-group date datetimepicker-modal-inline">
                                    <input type="text"
                                        class="form-control"
                                        id="etapa_existente_data_inicio"
                                        name="data_inicio"
                                        value=""
                                        placeholder="__/__/____"
                                        readonly>
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="etapa_existente_data_prazo">Data Prazo</label>
                                <div class="input-group date datetimepicker-modal-inline">
                                    <input type="text"
                                        class="form-control"
                                        id="etapa_existente_data_prazo"
                                        name="data_prazo"
                                        value=""
                                        placeholder="__/__/____"
                                        readonly>
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>
                                <small class="text-muted">Será calculada automaticamente com base no prazo padrão</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" id="ultima_etapa_info" style="display: none;">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            <span id="ultima_etapa_texto"></span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarEtapaExistente()">Adicionar Etapa</button>
            </div>
        </div>
    </div>
</div>


<style>
    .chamado-hero {
        border-top-width: 4px;
    }
    .chamado-badges {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
    }
    .chamado-badges .label {
        font-size: 12px;
        padding: 4px 8px;
    }
    .chamado-number {
        font-size: 15px;
        font-weight: bold;
        color: #777;
        margin-right: 4px;
    }
    .chamado-title {
        margin: 10px 0 6px;
        font-size: 24px;
        font-weight: 600;
        line-height: 1.3;
    }
    .chamado-path {
        color: #3c8dbc;
        margin-bottom: 6px;
    }
    .chamado-path .fa {
        margin: 0 4px;
        color: #aaa;
    }
    .chamado-rating {
        display: flex;
        flex-wrap: wrap;
        gap: 4px 20px;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #f4f4f4;
    }
    .chamado-description {
        font-size: 14px;
        line-height: 1.6;
        white-space: normal;
        word-wrap: break-word;
    }
    .chamado-reply-actions {
        margin-bottom: 10px;
    }
    .chamado-reply-actions .btn {
        margin-right: 6px;
    }
    .chamado-empty {
        padding: 12px 10px;
        margin: 0;
    }
    .chamado-day {
        text-align: center;
        margin: 18px 0 10px;
        border-bottom: 1px solid #e5e8eb;
        line-height: 0;
    }
    .chamado-day span {
        background: #fff;
        padding: 0 10px;
        color: #777;
        font-size: 12px;
        font-weight: 600;
    }
    .chamado-message {
        display: flex;
        gap: 10px;
        margin-bottom: 12px;
    }
    .chamado-avatar {
        flex: 0 0 36px;
        height: 36px;
        border-radius: 50%;
        background: #3c8dbc;
        color: #fff;
        font-weight: bold;
        font-size: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .chamado-message.from-requester .chamado-avatar {
        background: #00a65a;
    }
    .chamado-bubble {
        flex: 1;
        min-width: 0;
        border: 1px solid #e5e8eb;
        border-radius: 6px;
        background: #fff;
        padding: 10px 12px;
    }
    .chamado-message.from-requester .chamado-bubble {
        background: #f3faf6;
        border-color: #cfe9da;
    }
    .chamado-message.is-internal .chamado-bubble {
        background: #fff8e6;
        border-color: #f5d68a;
    }
    .chamado-bubble-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        margin-bottom: 4px;
    }
    .chamado-role {
        color: #777;
        font-size: 12px;
    }
    .chamado-time {
        margin-left: auto;
        color: #999;
        font-size: 12px;
    }
    .chamado-bubble-body {
        line-height: 1.5;
        word-wrap: break-word;
    }
    .chamado-file {
        display: inline-block;
        margin-top: 6px;
        padding: 4px 10px;
        border: 1px solid #d2d6de;
        border-radius: 4px;
        background: #f9fafc;
        font-size: 12px;
    }
    .chamado-actions-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
        margin-top: 6px;
    }
    .chamado-actions-grid .btn {
        white-space: normal;
    }
    .chamado-timer-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
    }
    .chamado-timer-row .btn-app {
        margin: 0;
    }
    .chamado-timer-row .duration-clock {
        font-size: 26px;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }
    .chamado-timer .progress {
        margin: 6px 0 4px;
    }
    .chamado-facts {
        margin: 0;
    }
    .chamado-facts dt {
        color: #777;
        font-weight: normal;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .3px;
    }
    .chamado-facts dd {
        margin-bottom: 10px;
        font-weight: 600;
    }
    .chamado-facts dd:last-child {
        margin-bottom: 0;
    }
    .chamado-entries {
        list-style: none;
        margin: 0;
        padding: 0;
        max-height: 320px;
        overflow-y: auto;
    }
    .chamado-entries li {
        padding: 8px 10px;
        border-bottom: 1px solid #f4f4f4;
    }
    .escalar-modal .escalar-subtitle {
        color: #777;
        margin-top: 2px;
    }
    .escalar-current {
        background: #f7f9fb;
        border: 1px solid #e5e8eb;
        border-radius: 6px;
        padding: 12px 14px;
        margin-bottom: 16px;
    }
    .escalar-current-title {
        font-weight: 600;
        margin-bottom: 8px;
        color: #555;
    }
    .escalar-current-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px 16px;
    }
    .escalar-current-grid span {
        display: block;
        color: #888;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .3px;
    }
    .escalar-current-grid strong {
        font-weight: 600;
    }
    .escalar-current-path {
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid #e5e8eb;
        color: #3c8dbc;
        font-size: 12px;
    }
    .escalar-current-path .fa {
        color: #aaa;
        margin: 0 3px;
    }
    .escalar-section {
        padding: 12px 0;
        border-top: 1px solid #f0f2f4;
    }
    .escalar-section:first-of-type {
        border-top: 0;
    }
    .escalar-section h5 {
        margin: 0 0 10px;
        font-weight: 600;
        font-size: 14px;
    }
    .escalar-step {
        display: inline-block;
        width: 22px;
        height: 22px;
        line-height: 22px;
        margin-right: 4px;
        border-radius: 50%;
        background: #f39c12;
        color: #fff;
        text-align: center;
        font-size: 12px;
    }
    .escalar-toggle {
        margin-left: 8px;
        font-weight: normal;
        font-size: 12px;
    }
    .escalar-hint {
        margin: -4px 0 8px;
    }
    .escalar-modal .form-group label {
        font-weight: 600;
    }
    .escalar-modal .help-block {
        font-size: 12px;
        margin-bottom: 0;
    }
    .escalar-modal .fstElement {
        width: 100%;
    }
    .escalar-attach {
        margin-top: 8px;
    }
    .escalar-planejamento-ajuda {
        margin-top: 28px !important;
    }
    @media (max-width: 767px) {
        .escalar-planejamento-ajuda {
            margin-top: 0 !important;
        }
    }
    .apontar-modal .apontar-subtitle {
        color: #777;
        margin-top: 2px;
    }
    .apontar-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }
    .apontar-stat {
        background: #f7f9fb;
        border: 1px solid #e5e8eb;
        border-radius: 6px;
        padding: 10px 12px;
    }
    .apontar-stat span {
        display: block;
        color: #888;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .3px;
    }
    .apontar-stat strong {
        display: block;
        font-size: 20px;
        line-height: 1.3;
    }
    .apontar-stat strong em {
        font-size: 13px;
        font-style: normal;
        color: #888;
        font-weight: normal;
    }
    .apontar-stat small {
        color: #888;
    }
    .apontar-stat.is-over {
        background: #fdf3f3;
        border-color: #f2c6c6;
    }
    .apontar-stat.is-over strong {
        color: #dd4b39;
    }
    .apontar-stat .progress {
        margin: 4px 0 2px;
    }
    .apontar-modal .form-group label {
        font-weight: 600;
    }
    .apontar-esforco {
        font-size: 16px;
        font-weight: 600;
        text-align: center;
    }
    .apontar-quick {
        margin-top: -6px;
    }
    .apontar-quick .btn {
        margin-left: 4px;
    }
    .apontar-modal .help-block {
        font-size: 12px;
    }
    .apontar-history {
        border-top: 1px solid #f0f2f4;
        padding-top: 12px;
    }
    .apontar-history-title {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 6px;
        margin-bottom: 6px;
    }
    .apontar-history-list {
        max-height: 220px;
        overflow-y: auto;
    }
    .apontar-history-list tr.is-mine > td {
        background: #f3f8fc;
    }
    .apontar-empty {
        margin: 0;
    }
    @media (max-width: 767px) {
        .apontar-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 767px) {
        .escalar-current-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }


    /* Estilo para os datetimepickers do modal */
    .datetimepicker-modal-inline .input-group-addon {
        cursor: pointer;
        background-color: #f4f4f4;
    }

    .datetimepicker-modal-inline input {
        cursor: pointer;
        background-color: white !important;
    }

    .datetimepicker-modal-inline input:read-only {
        background-color: white !important;
    }

    .border-danger {
        border: 2px solid #dc3545 !important;
        box-shadow: 0 0 5px rgba(220, 53, 69, 0.5);
        transition: all 0.3s ease;
    }

    .datetimepicker {
        z-index: 9999 !important;
    }

    .datetimepicker-checklist-inline .input-group-addon {
        cursor: pointer;
    }

    .datetimepicker-checklist-inline input {
        background-color: white !important;
        cursor: text;
    }

    .btn-xs.salvar-etapa {
        margin: 0 2px;
    }

    .responsavel-etapa-select {
        width: 100%;
        min-width: 150px;
    }

    .danger {
        background-color: #f2dede !important;
    }

    .danger td {
        background-color: #f2dede !important;
    }

    .success {
        background-color: #dff0d8 !important;
    }

    .success td {
        background-color: #dff0d8 !important;
    }

    .datetimepicker-checklist-inline .input-group-addon {
        padding: 3px 8px;
    }

    .datetimepicker-checklist-inline input {
        height: 28px;
    }

    .btn-xs.salvar-etapa,
    .btn-xs.excluir-etapa-personalizada {
        margin: 0 2px;
    }

    .etapas-container,
    .etapas-personalizadas {
        width: 100%;
        overflow-x: hidden !important;
    }

    .etapa-item {
        width: 100%;
        box-sizing: border-box;
    }

    .etapa-item .row {
        margin-left: 0;
        margin-right: 0;
        width: 100%;
    }

    .etapa-item [class*="col-"] {
        padding-left: 2px;
        padding-right: 2px;
    }

    .btn-sm.btn-success:hover,
    .btn-sm.btn-success:focus {
        background-color: #008d4c !important;
        border-color: #398439 !important;
        color: #ffffff !important;
        transition: all 0.3s ease !important;
    }

    @media (max-width: 768px) {
        .etapa-item .col-xs-1 {
            width: 25px !important;
        }

        .etapa-item .col-xs-9,
        .etapa-item .col-xs-10 {
            width: calc(100% - 80px) !important;
        }
    }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    var base_url = '<?php echo base_url(); ?>';
</script>
<?php if (!empty($acesso_planejar)) { ?>
<script type="text/javascript">
    window.addEventListener('load', function() {
        $('#datetimepicker_planejada').datetimepicker({
            locale: 'pt-br', format: 'DD/MM/YYYY', showClose: true, useCurrent: false, icons: {close: 'closeText'}
        });
        // Na agenda da equipe o chamado conta para o programador; sem programador, para o responsável.
        var padrao = $('#escalar-planejado-para').text();
        function nomeSelecionado(id) {
            var campo = $('#' + id);
            return campo.val() ? $.trim(campo.find('option:selected').text()) : '';
        }
        $('#responsavel, #desenvolvedor').on('change', function() {
            $('#escalar-planejado-para').text(nomeSelecionado('desenvolvedor') || nomeSelecionado('responsavel') || padrao);
        });
    });
</script>
<?php } ?>
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/datetimepicker/build/css/bootstrap-datetimepicker.min.css') ?>">
<script type="text/javascript" src="<?php echo base_url('assets/xkey/moment.min.js') ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/xkey/pt-br.js') ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/plugins/datetimepicker/build/js/bootstrap-datetimepicker.min.js') ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/xkey/chamados_checklists.js'); ?>"></script>