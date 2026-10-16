<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if (isset($message)) {
            echo $message;
        } ?>
        <div class="dataTables_wrapper">
            <div class="row">
                <div class="col-md-12">
                    <div class="box">
                        <form id="form-chamados-q" action="" method="get">
                            <input type="hidden" id="mostrar_resolvidos" name="mostrar_resolvidos" value="<?php echo @($mostrar_resolvidos == 1) ? 1 : 0 ?>">
                            <input type="hidden" id="mostrar_responsaveis" name="mostrar_responsaveis" value="<?php echo @($mostrar_responsaveis == 1) ? 1 : 0 ?>">
                            <div class="box-header with-border">
                                <h3 class="box-title"><?php echo anchor('chamados/create', '<i class="fa fa-plus"></i> ' . lang('chamados_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                                <div style="float:right;">
                                    <input name="q" class="form-control" placeholder="Buscar..." type="text" value="<?php echo $q ?>">
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="box box-default collapsed-box">
                                            <div class="box-header with-border" style="cursor:default !important;">
                                                <h3 class="box-title">Filtros Adicionais</h3>

                                                <div class="box-tools pull-right">
                                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                                <!-- /.box-tools -->
                                            </div>
                                            <!-- /.box-header -->
                                            <div class="box-body">
                                                <div class="row">
                                                    <div class="col-md-2 col-sm-6 form-group">
                                                        <select name="filtro_unidade" id="filtro_unidade" class="singleInputDynamic form-control">
                                                            <option value="">Selecione o Cliente</option>
                                                            <?php
                                                            foreach ($unidade_filtro as $k_unid => $unid) {
                                                                echo "<option value='" . $k_unid . "'";
                                                                if ($cliente == $k_unid)
                                                                    echo " selected";
                                                                echo ">" . $unid . "</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 col-sm-6 form-group">
                                                        <select name="filtro_dono" id="filtro_dono" class="<?php echo ($usuario_limitado) ? '' : 'singleInputDynamic' ?> form-control" <?php echo ($usuario_limitado) ? 'disabled="disabled"' : '' ?>>
                                                            <option value="">Selecione o Dono</option>
                                                            <?php
                                                            foreach ($usuarios as $usu) {
                                                                echo "<option value='" . $usu->id . "'";
                                                                if ($dono == $usu->id)
                                                                    echo " selected";
                                                                echo ">" . $usu->nome . " - " . $usu->email . "</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 col-sm-6 form-group">
                                                        <select name="filtro_dev" id="filtro_dev" class="<?php echo ($usuario_limitado) ? '' : 'singleInputDynamic' ?> form-control" <?php echo ($usuario_limitado) ? 'disabled="disabled"' : '' ?>>
                                                            <option value="">Selecione o Desenvolvedor</option>
                                                            <?php
                                                            foreach ($desenvolvedores as $dev) {
                                                                echo "<option value='" . $dev->id . "'";
                                                                if (@$_GET['filtro_dev'] == $dev->id)
                                                                    echo " selected";
                                                                echo ">" . $dev->nome . " - " . $dev->email . "</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 col-sm-6 form-group">
                                                        <select name="filtro_status" id="filtro_status" class="form-control">
                                                            <option value="">Selecione um Status</option>
                                                            <option value="todos_menos_fechados_suspensos_avaliados"<?php if ($st === 'todos_menos_fechados_suspensos_avaliados') echo " selected"; ?>>Todos menos Fechados, Suspensos e Avaliados</option>
                                                            <?php
                                                            foreach ($status as $_status) {
                                                                echo "<option value='" . $_status->id . "'";
                                                                if ((string)$st === (string)$_status->id)
                                                                    echo " selected";
                                                                echo ">" . $_status->descricao . "</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 col-sm-6 form-group">
                                                        <select name="filtro_prior" id="filtro_prior" class="form-control">
                                                            <option value="">Selecione uma Prioridade</option>
                                                            <?php
                                                            foreach ($prioridades as $prior) {
                                                                echo "<option value='" . $prior->id . "'";
                                                                if ($pr == $prior->id)
                                                                    echo " selected";
                                                                echo ">" . $prior->descricao . "</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 col-sm-6 form-group">
                                                        <select name="filtro_plano_acao" id="filtro_plano_acao" class="form-control">
                                                            <option value="">Selecione um Plano de Ação</option>
                                                            <?php
                                                            foreach ($planos_acao as $plano) {
                                                                echo "<option value='" . $plano->id . "'";
                                                                if ($plano_acao == $plano->id)
                                                                    echo " selected";
                                                                echo ">" . $plano->desc . "</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-2 col-sm-6 form-group">
                                                        <label for="mostrar_resolvidos" class="checkbox-inline"><input name="mostrar_resolvidos_toggle" class="form-control" id="mostrar_resolvidos_toggle" data-toggle="toggle" data-on="Sim" data-off="Não" type="checkbox" value="1"
                                                                <?php
                                                                echo (@$mostrar_resolvidos == 1) ? ' checked ' : '';
                                                                ?>>Mostrar Chamados Avaliados</label>
                                                    </div>
                                                    <div class="col-md-2 col-sm-6 form-group">
                                                        <label for="mostrar_responsaveis" class="checkbox-inline"><input name="mostrar_responsaveis_toggle" class="form-control" id="mostrar_responsaveis_toggle" data-toggle="toggle" data-on="Sim" data-off="Não" type="checkbox" value="1"
                                                                <?php
                                                                echo (@$mostrar_responsaveis == 1) ? ' checked ' : '';
                                                                ?>
                                                                <?php echo ($usuario_limitado) ? 'disabled="disabled"' : '' ?>>Mostrar apenas Meus Chamados</label>
                                                    </div>
                                                    <div class="avaliacao_de_ate" <?php echo ((string)$st === '5') ? '' : 'style="display:none;"'; ?>>
                                                        <div class="col-md-2 col-sm-6 form-group">
                                                            <select name="filtro_avaliacao_de" id="filtro_avaliacao_de" class="form-control">
                                                                <option value="">De:</option>
                                                                <option value="1" <?php echo (@$avaliacao_de == 1) ? 'selected' : '' ?>>1</option>
                                                                <option value="2" <?php echo (@$avaliacao_de == 2) ? 'selected' : '' ?>>2</option>
                                                                <option value="3" <?php echo (@$avaliacao_de == 3) ? 'selected' : '' ?>>3</option>
                                                                <option value="4" <?php echo (@$avaliacao_de == 4) ? 'selected' : '' ?>>4</option>
                                                                <option value="5" <?php echo (@$avaliacao_de == 5) ? 'selected' : '' ?>>5</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2 col-sm-6 form-group">
                                                            <select name="filtro_avaliacao_ate" id="filtro_avaliacao_ate" class="form-control">
                                                                <option value="">Até:</option>
                                                                <option value="1" <?php echo (@$avaliacao_ate == 1) ? 'selected' : '' ?>>1</option>
                                                                <option value="2" <?php echo (@$avaliacao_ate == 2) ? 'selected' : '' ?>>2</option>
                                                                <option value="3" <?php echo (@$avaliacao_ate == 3) ? 'selected' : '' ?>>3</option>
                                                                <option value="4" <?php echo (@$avaliacao_ate == 4) ? 'selected' : '' ?>>4</option>
                                                                <option value="5" <?php echo (@$avaliacao_ate == 5) ? 'selected' : '' ?>>5</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row col-sm-12">
                                                        <div class="col-sm-2">
                                                            <div class="input-group date form-group" id='datetimepickerInicio'>
                                                                <?php
                                                                if (@$inicio_abertura)
                                                                    echo form_input(array('id' => 'datetimepickerInicioAbertura', 'name' => 'data_filtro_inicio_abertura', 'value' => '', 'class' => 'form-control date_range_filter date'));
                                                                else
                                                                    echo form_input(array('placeholder' => 'Início Abertura', 'id' => 'datetimepickerInicioAbertura', 'name' => 'data_filtro_inicio_abertura', 'value' => '', 'class' => 'form-control date_range_filter date'));
                                                                ?>
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-calendar"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <div class="input-group date form-group" id='datetimepickerFim'>
                                                                <?php
                                                                if (@$fim_abertura)
                                                                    echo form_input(array('id' => 'datetimepickerFimAbertura', 'name' => 'data_filtro_fim_abertura', 'value' => '', 'class' => 'form-control date_range_filter date'));
                                                                else
                                                                    echo form_input(array('placeholder' => 'Fim Abertura', 'id' => 'datetimepickerFimAbertura', 'name' => 'data_filtro_fim_abertura', 'value' => '', 'class' => 'form-control date_range_filter date'));
                                                                ?>
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-calendar"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row col-sm-12">
                                                        <div class="col-sm-2">
                                                            <div class="input-group date form-group" id='datetimepickerInicio'>
                                                                <?php
                                                                if (@$inicio_encerramento)
                                                                    echo form_input(array('id' => 'datetimepickerInicioEncerramento', 'name' => 'data_filtro_inicio_encerramento', 'value' => '', 'class' => 'form-control date_range_filter date'));
                                                                else
                                                                    echo form_input(array('placeholder' => 'Início Encerramento', 'id' => 'datetimepickerInicioEncerramento', 'name' => 'data_filtro_inicio_encerramento', 'value' => '', 'class' => 'form-control date_range_filter date'));
                                                                ?>
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-calendar"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <div class="input-group date form-group" id='datetimepickerFim'>
                                                                <?php
                                                                if (@$fim_encerramento)
                                                                    echo form_input(array('id' => 'datetimepickerFimEncerramento', 'name' => 'data_filtro_fim_encerramento', 'value' => '', 'class' => 'form-control date_range_filter date'));
                                                                else
                                                                    echo form_input(array('placeholder' => 'Fim Encerramento', 'id' => 'datetimepickerFimEncerramento', 'name' => 'data_filtro_fim_encerramento', 'value' => '', 'class' => 'form-control date_range_filter date'));
                                                                ?>
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-calendar"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <button type="submit" id="search-btn" class="btn btn-default btn-flat" style="height: 35px;"><i class="fa fa-search"></i> Buscar</button>
                                                        <?php if ($limpar == 1): ?>
                                                            &nbsp;<a href="<?php echo base_url('chamados/index') ?>" id="clear-btn" class="btn btn-default btn-flat" style="height: 35px;"><i class="fa fa-times"></i> Limpar</a>
                                                        <?php endif ?>
                                                    </div>
                                                </div>
                                                <!-- /.box-body -->
                                            </div>
                                            <!-- /.box -->
                                        </div>
                                    </div>
                                </div>
                        </form>
                        <table id="chamado-datatable" class="table">
                            <thead>
                                <tr>
                                    <th><?php echo lang('chamados_id'); ?></th>
                                    <th><?php echo lang('chamados_data_hora'); ?></th>
                                    <th><?php echo lang('chamados_responsavel'); ?></th>
                                    <th><?php echo lang('chamados_desenvolvedor'); ?></th>
                                    <th><?php echo lang('chamados_servico'); ?></th>
                                    <th><?php echo lang('chamados_cliente'); ?></th>
                                    <th><?php echo lang('chamados_status'); ?></th>
                                    <th><?php echo lang('chamados_prioridade'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($chamados as $chamado): ?>
                                    <tr class="border-cima clickable-row" data-href="<?php echo base_url('chamados/details/' . $chamado->id) ?>">
                                        <td colspan="8">
                                            <h4><a href="<?php echo base_url('chamados/details/' . $chamado->id) ?>" style="color: inherit; text-decoration: none;"><?php echo $chamado->titulo; ?></a></h4>Previsão Resolução:
                                            <?php if (@$acesso_reagendar == true) { ?>
                                                <a href="javascript:void(0)" id="<?php echo $chamado->id ?>" class="reagendarChamado">
                                                <?php } ?>
                                                <?php echo ($chamado->data_prazo) ? date("d/m/Y H:i", strtotime($chamado->data_prazo)) : 'à definir'; ?>
                                                <?php if (@$acesso_reagendar == true) { ?>
                                                </a>
                                            <?php } ?>
                                            <p><a href="<?php echo base_url('chamados/details/' . $chamado->id) ?>" style="color: inherit; text-decoration: none;">Dono: <?php echo $chamado->dono_first_name . " " . $chamado->dono_last_name ?></a></p>
                                        </td>
                                    </tr>
                                    <?php
                                    if ($chamado->responsavel_id == "") {
                                        $class_tr_baixo = "border-baixo-sem-responsavel";
                                    } else {
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
                                    }
                                    ?>
                                    <tr class="<?php echo $class_tr_baixo; ?> clickable-row" data-href="<?php echo base_url('chamados/details/' . $chamado->id) ?>">
                                        <td><a href="<?php echo base_url('chamados/details/' . $chamado->id) ?>" style="display: block; color: inherit; text-decoration: none;"><?php echo $chamado->id; ?></a></td>
                                        <td><a href="<?php echo base_url('chamados/details/' . $chamado->id) ?>" style="display: block; color: inherit; text-decoration: none;"><?php echo date("d/m/Y H:i:s", strtotime($chamado->created)); ?></a></td>
                                        <td><a href="<?php echo base_url('chamados/details/' . $chamado->id) ?>" style="display: block; color: inherit; text-decoration: none;"><?php echo htmlspecialchars($chamado->first_name . " " . $chamado->last_name, ENT_QUOTES, 'UTF-8'); ?></a></td>
                                        <td><a href="<?php echo base_url('chamados/details/' . $chamado->id) ?>" style="display: block; color: inherit; text-decoration: none;"><?php echo ($chamado->dev_first_name) ? htmlspecialchars($chamado->dev_first_name . " " . $chamado->dev_last_name, ENT_QUOTES, 'UTF-8') : 'Não Atribuído a um Desenvolvedor'; ?></a></td>
                                        <td><a href="<?php echo base_url('chamados/details/' . $chamado->id) ?>" style="display: block; color: inherit; text-decoration: none;"><?php echo htmlspecialchars($chamado->catalogo_desc, ENT_QUOTES, 'UTF-8'); ?></a></td>
                                        <td><a href="<?php echo base_url('chamados/details/' . $chamado->id) ?>" style="display: block; color: inherit; text-decoration: none;"><?php echo htmlspecialchars($chamado->cliente, ENT_QUOTES, 'UTF-8'); ?></a></td>
                                        <td><a href="<?php echo base_url('chamados/details/' . $chamado->id) ?>" style="display: block; color: inherit; text-decoration: none;"><?php echo htmlspecialchars($chamado->status, ENT_QUOTES, 'UTF-8'); ?></a></td>
                                        <td><a href="<?php echo base_url('chamados/details/' . $chamado->id) ?>" style="display: block; color: inherit; text-decoration: none;"><?php echo htmlspecialchars($chamado->prioridade, ENT_QUOTES, 'UTF-8'); ?></a></td>
                                    </tr>
                                    <tr class="spacer">
                                        <td colspan="8"></td>
                                    </tr>

                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <?php echo $pagination; ?>
        </div>
</div>
</section>
</div>

<!-- Modal -->
<div id="reagendarModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
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
        $('#datetimepickerInicioAbertura').datetimepicker({
            defaultDate: "<?php echo @$inicio_abertura ?>",
            locale: 'pt-br',
            format: 'DD/MM/YYYY',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });
        $('#datetimepickerFimAbertura').datetimepicker({
            defaultDate: "<?php echo @$fim_abertura ?>",
            locale: 'pt-br',
            format: 'DD/MM/YYYY',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });
        $('#datetimepickerInicioEncerramento').datetimepicker({
            defaultDate: "<?php echo @$inicio_encerramento ?>",
            locale: 'pt-br',
            format: 'DD/MM/YYYY',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });
        $('#datetimepickerFimEncerramento').datetimepicker({
            defaultDate: "<?php echo @$fim_encerramento ?>",
            locale: 'pt-br',
            format: 'DD/MM/YYYY',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });
    };
</script>