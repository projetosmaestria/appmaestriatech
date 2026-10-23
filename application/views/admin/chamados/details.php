<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if ($this->session->flashdata('message') != "") {
            echo $this->session->flashdata('message');
        } ?>
        <div class="dataTables_wrapper">
            <input type="hidden" name="acesso_interno" id="acesso_interno" value="<?php echo @$acesso_interno ?>">
            <?php if (@$acesso_apontar_automatico == true) { ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h2 class="box-title"><?php echo lang('chamados_apontar') ?></h2>
                            </div>
                            <div class="box-body">
                                <input type="hidden" name="apontamento_id" id="apontamento_id" value="<?php echo ($apontamento_ativo->num_rows() > 0) ? $apontamento_ativo->row()->id : '' ?>">
                                <div class="col-sm-12">
                                    <div class="row">
                                        <div class="left-myday">
                                            <?php if ($apontamento_ativo->num_rows() > 0) { ?>
                                                <?php
                                                $hora_inicio = new DateTime($apontamento_ativo->row()->data . " " . $apontamento_ativo->row()->hora_inicio);
                                                $agora = new DateTime(date("Y-m-d H:i:s"));
                                                $diff = $hora_inicio->diff($agora);
                                                $horas = ($diff->days * 24) + $diff->format('%h');
                                                $minutos = $diff->format('%i');
                                                $segundos = $diff->format('%s');
                                                ?>
                                                <a class="btn btn-app" onclick="stopApontamento()">
                                                    <i class="fa fa-stop"></i> Parar
                                                </a>
                                                <div class="duration-clock"><?php echo $horas . ":" . $minutos . ":" . $segundos ?></div>
                                            <?php } else { ?>
                                                <a class="btn btn-app" onclick="startApontamento()">
                                                    <i class="fa fa-play"></i> Iniciar
                                                </a>
                                                <div class="duration-clock">00:00:00</div>
                                            <?php } ?>
                                        </div>
                                        <div class="informations-apontamento">
                                            <input type="text" class="form-control" name="obs_apontamento" id="obs_apontamento" value="" placeholder="Adicionar observação para apontamento" <?php echo (@$apontamento_ativo->num_rows() > 0) ? '' : 'readonly="readonly"' ?>>
                                            <div class="progress progress-xs active">
                                                <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                                </div>
                                            </div>
                                            <?php if (@$apontamento_ativo->num_rows() > 0) { ?>
                                                <div class="inicio-apontamento" style="display:none;">Início <span class="inicio-apontamento-span"><?php echo $apontamento_ativo->row()->hora_inicio ?></span></div>
                                            <?php } else { ?>
                                                <div class="inicio-apontamento" style="display:none;">Início <span class="inicio-apontamento-span"></span></div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php if (@$has_chamados_vinculados == true) { ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h2 class="box-title"><?php echo lang('chamados_vinculados') ?></h2>
                            </div>
                            <div class="box-body">
                                <?php
                                foreach ($chamados_vinculados->result() as $vinc) {
                                    echo "<p><a target='_blank' href='" . base_url('chamados/details/' . $vinc->id) . "'>Chamado " . $vinc->id . " - " . $vinc->titulo . "</a> - Estado: " . $vinc->status . "</p>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-header with-border row">
                            <h2 class="box-title col-md-4 col-xs-12 col-sm-12"><?php echo lang('chamados_info') ?></h2>
                            <div class="col-md-8 col-xs-12 col-sm-12">
                                <?php if (@$acesso_status == true) { ?>
                                    <a href="javascript:void(0)" id="<?php echo $chamado->id ?>" class="btn btn-success pull-right statusChamado"><i class="fa fa-refresh" aria-hidden="true"></i> <?php echo lang('chamados_att_status') ?></a>
                                <?php } ?>
                                <?php if (@$acesso_escalar == true) { ?>
                                    <a href="javascript:void(0)" id="<?php echo $chamado->id ?>" class="btn btn-warning pull-right btn-space escalarChamado"><i class="fa fa-random" aria-hidden="true"></i> <?php echo lang('chamados_escalar') ?></a>
                                <?php } ?>
                                <?php if (@$acesso_imprimir == true) { ?>
                                    <a href="<?php echo base_url('chamados/imprimir/' . $chamado->id) ?>" class="btn btn-info pull-right btn-space"><i class="fa fa-print" aria-hidden="true"></i> <?php echo lang('chamados_imprimir') ?></a>
                                <?php } ?>
                                <?php if (@$acesso_edit == true && $this->ion_auth->in_group(array(1, 3))) { ?>
                                    <a href="<?php echo base_url('chamados/edit/' . $chamado->id) ?>" class="btn btn-warning pull-right btn-space"><i class="fa fa-edit" aria-hidden="true"></i> <?php echo lang('chamados_edit') ?></a>
                                <?php } ?>
                                <?php if (@$acesso_apontar_manual == true) { ?>
                                    <a href="javascript:void(0)" id="<?php echo $chamado->id ?>" class="btn btn-info pull-right btn-space apontarChamado"><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo lang('chamados_apontar') ?></a>
                                <?php } ?>
                                <?php if (@$acesso_associar_checklist == true) { ?>
                                    <a href="#" id="<?php echo $chamado->id ?>" class="btn btn-primary pull-right btn-space associarChecklist" data-toggle="modal" data-target="#associarChecklistModal"><i class="fa fa-check-square-o" aria-hidden="true"></i> Associar Checklist</a>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="box-body">
                            <input type="hidden" id="id_chamado" name="id_chamado" value="<?php echo $chamado->id ?>">
                            <div class="col-sm-12 border-cima">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h2><?php echo $chamado->titulo ?></h2>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h4>
                                            <?php
                                            if (@$chamado->produto_desc != "") {
                                                echo "<u>" . $chamado->produto_desc . "</u><b> > </b>";
                                            }
                                            if (@$chamado->modulo_desc != "") {
                                                echo "<u> " . $chamado->modulo_desc . "</u><b> > </b>";
                                            }
                                            if (@$chamado->mcategoria_desc != "") {
                                                echo "<u> " . $chamado->mcategoria_desc . "</u><b> > </b>";
                                            }
                                            if (@$chamado->catalogo_desc != "") {
                                                echo "<u> " . $chamado->catalogo_desc . "</u>";
                                            }
                                            ?></h4>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-2">
                                        <p><?php echo lang('chamados_created') . ": " . date("d/m/Y H:i:s", strtotime($chamado->created)) ?></p>
                                    </div>
                                    <div class="col-sm-2">
                                        <p>
                                            <?php
                                            echo lang('chamados_prazo') . ": ";
                                            echo ($chamado->data_prazo != "") ? date("d/m/Y H:i:s", strtotime($chamado->data_prazo)) : 'não definido';
                                            ?>
                                        </p>
                                    </div>
                                    <div class="col-sm-2">
                                        <p>
                                            <?php
                                            echo lang('chamados_inicio') . ": ";
                                            echo ($chamado->data_inicio != "") ? date("d/m/Y H:i:s", strtotime($chamado->data_inicio)) : 'não definido';
                                            ?>
                                        </p>
                                    </div>
                                    <div class="col-sm-2">
                                        <p>
                                            <?php
                                            echo lang('chamados_finalizacao') . ": ";
                                            echo ($chamado->data_finalizacao != "") ? date("d/m/Y H:i:s", strtotime($chamado->data_finalizacao)) : 'não definido';
                                            ?>
                                        </p>
                                    </div>
                                    <div class="col-sm-2">
                                        <p>
                                            <?php
                                            echo lang('chamados_atualizado_por') . ": ";
                                            echo $chamado->atualizado_por_nome;
                                            ?>
                                        </p>
                                    </div>
                                    <div class="col-sm-2">
                                        <p>
                                            <?php
                                            echo lang('chamados_nivel') . ": ";
                                            echo $chamado->nivel_desc;
                                            ?>
                                        </p>
                                    </div>
                                </div>
                                <?php if (count($avaliacoes) > 0): ?>
                                    <?php if ($show_avaliacao) : ?>
                                        <div class="row">
                                            <?php foreach ($avaliacoes as $value) { ?>
                                                <div class="col-sm-2">
                                                    <label for="star-<?php echo $value["id"] ?>" class="control-label"><?php echo $value['descricao'] ?>: <input id="star-<?php echo $value["id"] ?>" class="rating rating-loading" value="<?php echo $value["resposta"] ?>" data-min="0" data-max="5" data-step="1" data-size="xs" data-show-caption="false"></label>
                                                    <?php if ($value["resposta"] != "") {
                                                        $rating_id_respondido[] = $value["id"];
                                                    } ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif ?>
                            </div>
                            <?php
                            $s_limpo = mb_strtolower(trim($chamado->status), 'UTF-8');
                            if ($s_limpo == "aberto") {
                                if ($chamado->data_prazo && $chamado->data_prazo < date('Y-m-d H:i:s'))
                                    $class_tr_baixo = "border-baixo-vencido";
                                else
                                    $class_tr_baixo = "border-baixo-aberto";
                            } elseif ($s_limpo == "aguardando aprovação" || $s_limpo == utf8_decode("aguardando aprovação")) {
                                $class_tr_baixo = "border-baixo-aguardando-aprovacao";
                            } elseif ($s_limpo == "fazendo") {
                                $class_tr_baixo = "border-baixo-fazendo";
                            } elseif ($s_limpo == "aguardando retorno do cliente" || $s_limpo == "retorno") {
                                $class_tr_baixo = "border-baixo-retorno-cliente";
                            } elseif ($s_limpo == "aguardando testes sanclá" || $s_limpo == utf8_decode("aguardando testes sanclá")) {
                                $class_tr_baixo = "border-baixo-aguardando-testes-sancla";
                            } elseif ($s_limpo == "aguardando testes com o cliente") {
                                $class_tr_baixo = "border-baixo-testes-cliente";
                            } elseif ($s_limpo == "sugestão de desenvolvimento" || $s_limpo == utf8_decode("sugestão de desenvolvimento")) {
                                $class_tr_baixo = "border-baixo-sugestao";
                            } elseif ($s_limpo == "implantação" || $s_limpo == utf8_decode("implantação")) {
                                $class_tr_baixo = "border-baixo-implantacao";
                            } elseif ($s_limpo == "suspenso") {
                                $class_tr_baixo = "border-baixo-suspenso";
                            } elseif ($s_limpo == "fechado") {
                                $class_tr_baixo = "border-baixo-fechado";
                            } elseif ($s_limpo == "avaliado") {
                                $class_tr_baixo = "border-baixo-avaliado";
                            } elseif ($s_limpo == "aguardando atualização sistema" || $s_limpo == utf8_decode("aguardando atualização sistema")) {
                                $class_tr_baixo = "border-baixo-aguardando-atualizacao";
                            } else {
                                $class_tr_baixo = "border-baixo";
                            }
                            ?>
                            <div class="col-sm-12 <?php echo $class_tr_baixo ?>">
                                <div class="row">
                                    <div class="col-md-2 col-sm-4 separated-border"><?php echo mb_strtoupper(lang('chamados_dono')) . "<br><label>" . $chamado->first_name_dono . " " . $chamado->last_name_dono . "</label>" ?></div>
                                    <div class="col-md-2 col-sm-4 separated-border"><?php echo mb_strtoupper(lang('chamados_responsavel')) . "<br><label>" . $chamado->first_name . " " . $chamado->last_name . "</label>" ?></div>
                                    <div class="col-md-2 col-sm-4 separated-border"><?php echo mb_strtoupper(lang('chamados_desenvolvedor')) . "<br><label>" . $chamado->first_name_dev . " " . $chamado->last_name_dev . "</label>" ?></div>
                                    <div class="col-md-2 col-sm-4 separated-border"><?php echo mb_strtoupper(lang('chamados_cliente')) . "<br><label><a href='" . base_url('unidades/edit/' . $chamado->unidade_id) . "'>" . $chamado->cliente . "</a></label>" ?></div>
                                    <div class="col-md-2 col-sm-4 separated-border"><?php echo mb_strtoupper(lang('chamados_status')) . "<br><label>" . $chamado->status . "</label>" ?></div>
                                    <div class="col-md-2 col-sm-4 separated-border">
                                        <?php
                                        echo mb_strtoupper(lang('chamados_prioridade'));
                                        echo "<br>";
                                        if ($has_prioridade)
                                            echo form_dropdown($prioridade);
                                        else
                                            echo "<label>" . $chamado->prioridade . "</label>";
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-header with-border">
                            <h2 class="box-title"><?php echo lang('chamados_desc') ?></h2>
                        </div>
                        <div class="box-body">
                            <div class="col-sm-12">
                                <div class="row">
                                    <?php echo nl2br($chamado->descricao); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Abas -->
            <div class="row">
                <div class="col-md-12">
                    <div class="box">
                        <?php if ($chamado->status_id != 3 && $chamado->status_id != 5): ?>
                            <div class="box-header">
                                <div class="col-md-12">
                                    <!-- Abas -->
                                    <ul class="nav nav-tabs" role="tablist">
                                        <li role="presentation" class="active">
                                            <a href="#dialogo" aria-controls="dialogo" role="tab" data-toggle="tab">
                                                <i class="fa fa-comments"></i> Diálogo
                                            </a>
                                        </li>
                                        <li role="presentation">
                                            <a href="#checklist" aria-controls="checklist" role="tab" data-toggle="tab">
                                                <i class="fa fa-check-square-o"></i> Checklist
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        <?php endif ?>

                        <div class="box-body">
                            <div class="col-md-12">
                                <!-- Conteúdo das Abas -->
                                <div class="tab-content">
                                    <!-- AbaDiálogo -->
                                    <div role="tabpanel" class="tab-pane active" id="dialogo">
                                        <div class="row" style="margin-bottom: 20px;">
                                            <div class="col-md-12">
                                                <div class="btn-group">
                                                    <a class="btn btn-primary" href="javascript:void(0);" onclick="add_resposta(1,'<?php echo $chamado->id ?>');">
                                                        <i class="fa fa-plus-circle"></i> Diálogo
                                                    </a>
                                                </div>
                                                &nbsp;
                                                <div class="btn-group">
                                                    <a class="btn btn-primary" href="javascript:void(0);" onclick="add_resposta(2,'<?php echo $chamado->id ?>');">
                                                        <i class="fa fa-plus-circle"></i> Anexo
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="col-md-12 dialogo-anexo"></div>
                                        </div>

                                        <!-- Timeline (mantida dentro da abaDiálogo) -->
                                        <?php if (count($timeline) > 0): ?>
                                            <ul class="timeline">
                                                <?php
                                                $last_time_dialog = "";
                                                foreach ($timeline as $dialog):
                                                    $created_dialog = explode(" ", $dialog['created']);
                                                    $created_hr_dialog = explode(":", $created_dialog[1]);
                                                    if ($last_time_dialog != $created_dialog[0]) {
                                                        echo '
                                                                <li class="time-label">
                                                                      <span class="bg-blue">
                                                                        ' . date("d M Y", strtotime($created_dialog[0])) . '
                                                                      </span>
                                                                </li>
                                                            ';
                                                        $last_time_dialog = $created_dialog[0];
                                                    }
                                                ?>
                                                    <li>
                                                        <?php if (@$dialog['anexo']): ?>
                                                            <i class="fa fa-file-text bg-purple"></i>
                                                        <?php else: ?>
                                                            <i class="fa fa-comments bg-aqua"></i>
                                                        <?php endif ?>

                                                        <div class="timeline-item">
                                                            <span class="time"><i class="fa fa-clock-o"></i> <?php echo $created_hr_dialog[0] . ":" . $created_hr_dialog[1] ?></span>

                                                            <h3 class="timeline-header">
                                                                <?php
                                                                if (isset($dialog['interno']) && $dialog['interno'] == 1)
                                                                    echo '<i class="fa fa-lock" aria-hidden="true"></i>&nbsp;';
                                                                ?>
                                                                <a href="#"><?php echo $dialog['first_name'] . " " . $dialog['last_name'] ?></a> respondeu
                                                            </h3>

                                                            <div class="timeline-body"><?php echo nl2br(htmlentities($dialog['descricao'], ENT_QUOTES, 'UTF-8')); ?></div>

                                                            <?php if (@$dialog['anexo']): ?>
                                                                <div class="timeline-footer">
                                                                    <a href="<?php echo base_url("upload/anexo/" . $dialog['anexo']) ?>" target="_blank" class="btn btn-primary btn-xs" rel="tooltip" data-placement="top" title="Download do anexo" download><i class="fa fa-floppy-o"></i> Download</a>
                                                                </div>
                                                            <?php endif ?>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                                <li>
                                                    <i class="fa fa-clock-o bg-gray"></i>
                                                </li>
                                            </ul>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Aba Checklist -->
                                    <div role="tabpanel" class="tab-pane" id="checklist">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <?php if (isset($checklists_associados) && !empty($checklists_associados)): ?>
                                                    <!-- Barra de Título do Checklist -->
                                                    <div class="row" style="margin-bottom: 15px;">
                                                        <div class="col-xs-12">
                                                            <h4 style="margin: 5px 0 0 0; font-weight: bold; color: #333;">
                                                                <i class="fa fa-list-alt"></i> Checklists Associados
                                                            </h4>
                                                        </div>
                                                    </div>
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
                                                                                <th width="4%">#</th>
                                                                                <th width="24%">Etapa</th>
                                                                                <th width="16%">Data Início</th>
                                                                                <th width="16%">Data Prazo</th>
                                                                                <th width="21%">Responsável</th>
                                                                                <th width="10%">Status</th>
                                                                                <th width="9%">Ações</th>
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
                                                                                        <div class="input-group date datetimepicker-checklist-inline" style="width: 155px;">
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
                                                                                        <div class="input-group date datetimepicker-checklist-inline" style="width: 155px;">
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
                                                                    <div class="row" style="margin-top: 10px; margin-bottom: 5px;">
                                                                        <div class="col-xs-12 text-right">
                                                                            <button type="button" class="btn btn-sm btn-primary" onclick="salvarEtapasChecklist(<?php echo $checklist->id ?>)">
                                                                                <i class="fa fa-save"></i> Salvar Etapas
                                                                            </button>
                                                                        </div>
                                                                    </div>
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
                    </div>
                </div>
            </div>
        </div>
</div>
</section>
</div>

<!-- Modal -->
<div id="escalarModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <?php echo form_open(base_url('chamados/escalar'), array('class' => 'form-horizontal', 'id' => 'form-escalar_chamado', 'enctype' => 'multipart/form-data')); ?>
                <input type='hidden' id='chamado_id' name='chamado_id' value='<?php echo $chamado->id ?>'>
                <input type="hidden" name="base_url" id="base_url" value="<?php echo base_url() ?>">
                <div class="form-group">
                    <?php echo lang('chamados_unidade', 'unidade', array('class' => 'col-sm-4 control-label'), true); ?>
                    <div class="col-sm-6">
                        <?php echo form_dropdown($unidade); ?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo lang('chamados_responsavel', 'responsavel', array('class' => 'col-sm-4 control-label'), false); ?>
                    <div class="col-sm-6">
                        <?php echo form_dropdown($responsavel); ?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo lang('chamados_desenvolvedor', 'desenvolvedor', array('class' => 'col-sm-4 control-label'), false); ?>
                    <div class="col-sm-6">
                        <?php echo form_dropdown($desenvolvedor); ?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo lang('chamados_produto', 'produto', array('class' => 'col-sm-4 control-label'), true); ?>
                    <div class="col-sm-6">
                        <?php echo form_dropdown($produto); ?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo lang('chamados_modulo', 'modulo', array('class' => 'col-sm-4 control-label'), true); ?>
                    <div class="col-sm-6">
                        <?php echo form_dropdown($modulo); ?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo lang('chamados_categoria', 'categoria', array('class' => 'col-sm-4 control-label'), true); ?>
                    <div class="col-sm-6">
                        <?php echo form_dropdown($categoria); ?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo lang('chamados_tipo_servico', 'tipo_servico', array('class' => 'col-sm-4 control-label'), true); ?>
                    <div class="col-sm-6">
                        <?php echo form_dropdown($tipo_servico); ?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo lang('chamados_nivel', 'nivel', array('class' => 'col-sm-4 control-label'), true); ?>
                    <div class="col-sm-6">
                        <?php echo form_dropdown($nivel); ?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo lang('chamados_prazo', 'chamados_prazo', array('class' => 'col-sm-4 control-label'), false); ?>
                    <div class="col-sm-4">
                        <?php if (@$acesso_reagendar == true) { ?>
                            <div class="input-group date" id='datetimehourpicker'>
                                <?php echo form_input(array('id' => 'data_reagendar', 'name' => 'data_reagendar', 'value' => ($chamado->data_prazo) ? date("d/m/Y H:i:s", strtotime($chamado->data_prazo)) : '', 'class' => 'form-control')); ?>
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                            </div>
                        <?php } else { ?>
                            <div class="input-group date" id='datetimehourpicker'>
                                <?php echo form_input(array('id' => 'data_reagendar', 'name' => 'data_reagendar', 'value' => '', 'class' => 'form-control disabled', 'disabled' => 'disabled')); ?>
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class='form-group'>
                    <?php echo lang('chamados_horas_prevista', 'horas_prevista', array('class' => 'col-sm-4 control-label'), false); ?>
                    <div class="col-sm-2">
                        <?php echo form_input(array('id' => 'horas_prevista', 'name' => 'horas_prevista', 'value' => $horas_prevista, 'class' => 'form-control required', 'maxlength' => "5")); ?>
                        <em> (ex: 1,5h) </em>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo lang('chamados_status', 'status', array('class' => 'col-sm-4 control-label'), true); ?>
                    <div class="col-sm-6">
                        <?php echo form_dropdown($status); ?>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='dialogo' class="col-sm-4 control-label">Di&aacute;logo</label>
                    <div class="col-sm-6">
                        <textarea id='dialogo' name='dialogo' class='form-control' rows='3' placeholder='Digite aqui...'></textarea>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='anexo' class="col-sm-4 control-label">Anexo</label>
                    <div class="col-sm-6">
                        <label class='btn btn-primary' for='anexo-escalar'><input type='file' id='anexo-escalar' name='anexo' style='display:none' onchange="informarArquivos(this, 'upload-file-info-escalar')"> Selecionar Anexo... </label> <span class='label label-info' id='upload-file-info-escalar'></span>
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

<?php if (@$acesso_apontar_manual == true) { ?>
    <!-- Modal -->
    <div id="apontarModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"></h4>
                    <?php if ($chamado->horas_prevista != "") { ?>
                        <p class="modal-title-horas-prevista">Horas prevista: <?php echo str_replace(".", ",", $chamado->horas_prevista) ?>h</p>
                    <?php } ?>
                </div>
                <div class="modal-body">
                    <?php echo form_open(base_url('chamados/apontamento_manual_criar'), array('class' => 'form-horizontal', 'id' => 'form-apontar_chamado')); ?>
                    <input type='hidden' id='chamado_id' name='chamado_id' value='<?php echo $chamado->id ?>'>
                    <input type='hidden' id='id' name='id' value=''>
                    <div class="form-group">
                        <?php echo lang('chamados_data', 'chamados_data', array('class' => 'col-sm-4 control-label'), true); ?>
                        <div class="col-sm-3">
                            <div class="input-group date" id='datetimepicker'>
                                <?php echo form_input(array('id' => 'data', 'name' => 'data', 'value' => date('d/m/Y'), 'class' => 'form-control')); ?>
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo lang('chamados_hora_inicio', 'chamados_hora_inicio', array('class' => 'col-sm-4 control-label'), false); ?>
                        <div class="col-sm-2">
                            <div class="input-group date" id='datetimehouronlypicker1'>
                                <?php echo form_input(array('id' => 'hora_inicio', 'name' => 'hora_inicio', 'value' => '', 'class' => 'form-control')); ?>
                                <span class="input-group-addon">
                                    <i class="fa fa-clock-o"></i>
                                </span>
                            </div>
                            <em> (opcional) </em>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='obs' class="col-sm-4 control-label">Esforço <em>*</em></label>
                        <div class="col-sm-2">
                            <?php echo form_input(array('id' => 'esforco', 'name' => 'esforco', 'value' => '00:00', 'class' => 'form-control required', 'onkeypress' => "formatTime(this)", 'maxlength' => "5")); ?>
                            <em> (ex: 01:30) </em>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo lang('chamados_hora_termino', 'chamados_hora_termino', array('class' => 'col-sm-4 control-label'), false); ?>
                        <div class="col-sm-2">
                            <div class="input-group date" id='datetimehouronlypicker3'>
                                <?php echo form_input(array('id' => 'hora_termino', 'name' => 'hora_termino', 'value' => '', 'class' => 'form-control')); ?>
                                <span class="input-group-addon">
                                    <i class="fa fa-clock-o"></i>
                                </span>
                            </div>
                            <em> (opcional) </em>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='tipo' class="col-sm-4 control-label">Tipo <em>*</em></label>
                        <div class="col-sm-3">
                            <select id="tipo" name="tipo" class="form-control required">
                                <option value="">Selecione</option>
                                <?php foreach ($tipos_apontamento as $tipo_ap) { ?>
                                    <option value="<?php echo $tipo_ap->id; ?>"><?php echo $tipo_ap->descricao; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='obs' class="col-sm-4 control-label">Observação <em>*</em></label>
                        <div class="col-sm-6">
                            <textarea id='observacao' name='observacao' class='form-control required' rows='3' placeholder='Digite aqui...'></textarea>
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
                    <hr>
                    <h4>Histórico</h4>
                    <p>Apontamentos nessa atividade:</p>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Esforço</th>
                                <th>Hora de início</th>
                                <th>Hora de fim</th>
                                <th>Resp.</th>
                                <th>Tipo</th>
                                <th>Observação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($apontamentos->num_rows() > 0) { ?>
                                <?php foreach ($apontamentos->result() as $apontamento) { ?>
                                    <tr>
                                        <td><?php echo date("d/m/Y", strtotime($apontamento->data)) ?></td>
                                        <td><?php echo date("H:i", strtotime($apontamento->esforco)) ?></td>
                                        <td><?php echo date("H:i", strtotime($apontamento->hora_inicio)) ?></td>
                                        <td><?php echo date("H:i", strtotime($apontamento->hora_termino)) ?></td>
                                        <td><?php echo $apontamento->nome_usuario ?></td>
                                        <td><?php echo $apontamento->tipo_descricao ?></td>
                                        <td><?php echo $apontamento->observacao ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Total:</td>
                                <td colspan="5"><?= $esforco ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                </div>
            </div>

        </div>
    </div>
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
                                                <small class="text-muted">Data de Iní­cio</small>
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

    /* Estilo unificado para inputs de data dos checklists (Input e Calendário como componente único) */
    .datetimepicker-checklist-inline {
        width: 155px !important;
        position: relative !important;
        display: flex !important;
        align-items: center !important;
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        overflow: visible !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .datetimepicker-checklist-inline:focus-within,
    .datetimepicker-checklist-inline.active {
        border-color: #9cd7d4 !important;
        box-shadow: 0 0 0 3px rgba(156, 215, 212, 0.25) !important;
    }

    .datetimepicker-checklist-inline input.form-control,
    .datetimepicker-checklist-inline input {
        flex: 1 1 auto !important;
        width: auto !important;
        min-width: 0 !important;
        border: none !important;
        border-radius: 8px 0 0 8px !important;
        box-shadow: none !important;
        background: transparent !important;
        background-color: transparent !important;
        height: 34px !important;
        line-height: 34px !important;
        padding: 6px 4px 6px 10px !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        color: #1e293b !important;
        outline: none !important;
        cursor: text !important;
    }

    .datetimepicker-checklist-inline input.form-control:focus,
    .datetimepicker-checklist-inline input:focus {
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
    }

    .datetimepicker-checklist-inline .input-group-addon {
        flex: 0 0 34px !important;
        width: 34px !important;
        min-width: 34px !important;
        height: 34px !important;
        line-height: 34px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        border: none !important;
        border-left: 1px solid #f1f5f9 !important;
        border-radius: 0 8px 8px 0 !important;
        background: transparent !important;
        background-color: transparent !important;
        padding: 0 !important;
        margin: 0 !important;
        color: #64748b !important;
        cursor: pointer !important;
        transition: color 0.2s ease, background-color 0.2s ease !important;
    }

    .datetimepicker-checklist-inline .input-group-addon:hover {
        color: #1e1d3f !important;
        background-color: #f8fafc !important;
    }

    .datetimepicker-checklist-inline .input-group-addon i {
        font-size: 14px !important;
    }

    #checklist table td {
        overflow: visible !important;
    }

    #checklist table {
        overflow: visible !important;
    }

    #checklist .box-body {
        overflow: visible !important;
    }

    .bootstrap-datetimepicker-widget,
    .datetimepicker {
        z-index: 99999 !important;
    }

    /* Posicionamento exato sob o botão do calendário */
    .datetimepicker-checklist-inline .bootstrap-datetimepicker-widget.dropdown-menu,
    .datetimepicker-checklist-inline .bootstrap-datetimepicker-widget.dropdown-menu.bottom {
        position: absolute !important;
        top: 36px !important;
        bottom: auto !important;
        left: 120px !important;
        right: auto !important;
        width: 250px !important;
        min-width: 250px !important;
        max-width: 250px !important;
        margin: 0 !important;
        padding: 8px !important;
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15) !important;
        z-index: 99999 !important;
    }

    /* Quando abrir para cima (rodapé da página) */
    .datetimepicker-checklist-inline .bootstrap-datetimepicker-widget.dropdown-menu.top {
        top: auto !important;
        bottom: 36px !important;
        left: 120px !important;
        right: auto !important;
    }

    /* Ajuste para borda direita da tela se necessário */
    .datetimepicker-checklist-inline .bootstrap-datetimepicker-widget.dropdown-menu.widget-edge-right {
        left: auto !important;
        right: 0px !important;
    }

    /* Seta do calendário perfeitamente alinhada com o botão clicado */
    .datetimepicker-checklist-inline .bootstrap-datetimepicker-widget.dropdown-menu:before {
        left: 10px !important;
        right: auto !important;
    }

    .datetimepicker-checklist-inline .bootstrap-datetimepicker-widget.dropdown-menu:after {
        left: 11px !important;
        right: auto !important;
    }

    .datetimepicker-checklist-inline .bootstrap-datetimepicker-widget.dropdown-menu.widget-edge-right:before {
        left: auto !important;
        right: 10px !important;
    }

    .datetimepicker-checklist-inline .bootstrap-datetimepicker-widget.dropdown-menu.widget-edge-right:after {
        left: auto !important;
        right: 11px !important;
    }

    .datetimepicker-checklist-inline .bootstrap-datetimepicker-widget table {
        width: 100% !important;
        margin: 0 !important;
    }

    .datetimepicker-checklist-inline .bootstrap-datetimepicker-widget td,
    .datetimepicker-checklist-inline .bootstrap-datetimepicker-widget th {
        text-align: center !important;
        padding: 4px 2px !important;
        border-radius: 4px !important;
        height: 28px !important;
        line-height: 28px !important;
    }

    .datetimepicker-checklist-inline .bootstrap-datetimepicker-widget td.day {
        cursor: pointer !important;
    }

    .datetimepicker-checklist-inline .bootstrap-datetimepicker-widget td.active,
    .datetimepicker-checklist-inline .bootstrap-datetimepicker-widget td.active:hover {
        background-color: #3c8dbc !important;
        color: #ffffff !important;
        text-shadow: none !important;
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

    /* Posicionamento inteligente para dropdown de responsável no Checklist (DropUp vs DropDown) */
    #checklist .fstElement.fstDropUp .fstControls {
        top: auto !important;
        bottom: 100% !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        border-bottom: 0 !important;
        border-radius: 0 !important;
        box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.08) !important;
        z-index: 1051 !important;
        overflow: visible !important;
    }

    #checklist .fstElement.fstDropUp .fstResults {
        top: auto !important;
        bottom: 100% !important;
        margin-top: 0 !important;
        margin-bottom: -1px !important;
        border: 1px solid #9cd7d4 !important;
        border-bottom: 1px solid #f1f5f9 !important;
        border-radius: 8px 8px 0 0 !important;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.12) !important;
        max-height: 240px !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        z-index: 1052 !important;
    }

    #checklist .fstElement.fstActive {
        z-index: 1050 !important;
    }

    #checklist tr.fst-row-active {
        position: relative;
        z-index: 1050 !important;
    }

    #checklist .fstResults {
        max-height: 250px !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }

    .btn-salvar-geral {
        transition: all 0.2s ease-in-out;
    }

    .btn-salvar-geral:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2) !important;
    }
</style>
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/datetimepicker/build/css/bootstrap-datetimepicker.min.css') ?>">
<script type="text/javascript" src="<?php echo base_url('assets/xkey/moment.min.js') ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/xkey/pt-br.js') ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/plugins/datetimepicker/build/js/bootstrap-datetimepicker.min.js') ?>"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    var base_url = '<?php echo base_url(); ?>';
</script>
<script type="text/javascript" src="<?php echo base_url('assets/xkey/chamados_checklists.js'); ?>"></script>
<script>
window.addEventListener("load", function() {
    $("a").on("click", function(e) {
        var href = $(this).attr("href");
        var temApontamento = $(".btn-app").length > 0 && $(".btn-app").attr("onclick") === "stopApontamento()";
        
        if (temApontamento && href && href !== "#" && href.indexOf("javascript") === -1 && href.indexOf("#") !== 0) {
            e.preventDefault();
            Swal.fire({
                title: "Apontamento Ativo!",
                text: "Você tem um apontamento em andamento. Deseja pará-lo antes de sair?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sim, parar e sair",
                cancelButtonText: "Sair sem parar"
            }).then((result) => {
                if (result.isConfirmed) {
                    stopApontamento();
                    setTimeout(function() {
                        window.location.href = href;
                    }, 1000);
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    window.location.href = href;
                }
            });
        }
    });

    $(document).on("submit", "#form-apontar_chamado", function(e) {
        var tipo = $("#tipo").val();
        var observacao = $("#observacao").val();
        if(observacao !== undefined && observacao !== null) {
            observacao = observacao.trim();
        } else {
            observacao = "";
        }
        
        if (!tipo && !observacao) {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Erro!",
                text: "Os campos Tipo e Observação são obrigatórios."
            });
            return false;
        } else if (!tipo) {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Erro!",
                text: "O campo Tipo é obrigatório."
            });
            return false;
        } else if (!observacao) {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Erro!",
                text: "O campo Observação é obrigatório."
            });
            return false;
        }
    });
});
</script>

