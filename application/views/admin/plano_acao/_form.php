<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$field_error_class = function ($field) {
    return form_error($field) !== '' ? ' has-error' : '';
};
$required_mark = ' <span class="text-red">*</span>';
// Formulário compartilhado entre criação ($modo = 'create') e edição ($modo = 'edit') de projeto.
$editando = $modo === 'edit';
$status_nomes = array('AI' => array('A iniciar', '#9aa5b1'), 'EA' => array('Em andamento', '#3c8dbc'), 'FI' => array('Finalizado', '#00a65a'));
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content projeto-form">
        <?php if (isset($message)) { echo $message; } ?>
        <?php if ($editando) { ?>
            <div class="projeto-edit-heading">
                <span class="projeto-edit-id">#<?php echo (int) $plano->id ?></span>
                <span class="projeto-edit-nome"><?php echo html_escape($plano->desc) ?></span>
                <span class="label" style="background-color: <?php echo $status_nomes[$plano->status][1] ?>;"><?php echo $status_nomes[$plano->status][0] ?></span>
                <a href="<?php echo base_url('plano_acao/gantt/' . (int) $plano->id) ?>" class="btn btn-default btn-sm"><i class="fa fa-tasks"></i> Ver cronograma</a>
            </div>
        <?php } ?>
        <?php echo form_open(current_url(), array('id' => 'form-projeto')); ?>
        <div class="row">
            <div class="col-md-8">

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">1</span>Identificação</h3>
                        <span class="box-subtitle">O que é o projeto e para quem.</span>
                    </div>
                    <div class="box-body">
                        <div class="form-group<?php echo $field_error_class('desc'); ?>">
                            <label for="desc">Nome do projeto<?php echo $required_mark; ?></label>
                            <input type="text" id="desc" name="desc" class="form-control projeto-nome" maxlength="150" autocomplete="off" placeholder="Ex.: Implantação do módulo fiscal - Loja Centro" value="<?php echo html_escape($valores['desc']) ?>">
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group<?php echo $field_error_class('tipo'); ?>">
                                <label>Tipo<?php echo $required_mark; ?></label>
                                <div>
                                    <div class="btn-group projeto-tipo" data-toggle="buttons">
                                        <?php foreach ($tipos as $codigo => $nome) { ?>
                                            <label class="btn btn-default <?php echo $valores['tipo'] === $codigo ? 'active' : '' ?>">
                                                <input type="radio" name="tipo" value="<?php echo $codigo ?>" autocomplete="off" <?php echo $valores['tipo'] === $codigo ? 'checked' : '' ?>>
                                                <i class="fa <?php echo $codigo === 'I' ? 'fa-rocket' : 'fa-code' ?>"></i> <?php echo $nome ?>
                                            </label>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 form-group<?php echo $field_error_class('filial'); ?>">
                                <label for="filial">Cliente</label>
                                <select id="filial" name="filial" class="singleInputDynamic form-control">
                                    <?php foreach ($unidades_options as $id => $nome) { ?>
                                        <option value="<?php echo html_escape($id) ?>" <?php echo (string) $valores['filial'] === (string) $id ? 'selected' : '' ?>><?php echo html_escape($nome) ?></option>
                                    <?php } ?>
                                </select>
                                <p class="help-block">Deixe "Todos os clientes" quando o projeto não for de um cliente específico.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">2</span>Prazo e esforço</h3>
                        <span class="box-subtitle">Usados no cronograma (Gantt) e para indicar atraso.</span>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-4 form-group<?php echo $field_error_class('inicio_estimado'); ?>">
                                <label for="inicio_estimado">Início estimado<?php echo $required_mark; ?></label>
                                <div class="input-group date" id="datetimepicker_inicio_estimado">
                                    <input type="text" id="inicio_estimado" name="inicio_estimado" class="form-control" autocomplete="off" placeholder="dd/mm/aaaa" value="<?php echo html_escape($valores['inicio_estimado']) ?>">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                            <div class="col-sm-4 form-group<?php echo $field_error_class('termino_estimado'); ?>">
                                <label for="termino_estimado">Término estimado<?php echo $required_mark; ?></label>
                                <div class="input-group date" id="datetimepicker_termino_estimado">
                                    <input type="text" id="termino_estimado" name="termino_estimado" class="form-control" autocomplete="off" placeholder="dd/mm/aaaa" value="<?php echo html_escape($valores['termino_estimado']) ?>">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                            <div class="col-sm-4 form-group<?php echo $field_error_class('horas_prevista'); ?>">
                                <label for="horas_prevista">Horas previstas<?php echo $required_mark; ?></label>
                                <div class="input-group">
                                    <input type="text" id="horas_prevista" name="horas_prevista" class="form-control" maxlength="7" inputmode="decimal" placeholder="Ex.: 40" value="<?php echo html_escape($valores['horas_prevista']) ?>">
                                    <span class="input-group-addon">h</span>
                                </div>
                            </div>
                        </div>
                        <p class="help-block" id="projeto-duracao">Informe as datas para ver a duração do projeto.</p>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">3</span>Objetivo e escopo <small class="text-muted">(opcional)</small></h3>
                        <span class="box-subtitle">O que precisa ser entregue e o que fica de fora.</span>
                    </div>
                    <div class="box-body">
                        <textarea id="txt" name="txt" class="form-control" rows="6" placeholder="Ex.: Implantar emissão de NF-e e NFC-e, treinar a equipe da loja e acompanhar o primeiro fechamento."><?php echo html_escape($valores['txt']) ?></textarea>
                    </div>
                </div>

                <?php if ($editando) { ?>
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><span class="step-number">4</span>Execução</h3>
                            <span class="box-subtitle">Datas reais do projeto. O início real é registrado quando o primeiro chamado é aberto.</span>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-sm-4 form-group<?php echo $field_error_class('inicio_real'); ?>">
                                    <label for="inicio_real">Início real</label>
                                    <div class="input-group date" id="datetimepicker_inicio_real">
                                        <input type="text" id="inicio_real" name="inicio_real" class="form-control js-data" autocomplete="off" placeholder="dd/mm/aaaa" value="<?php echo html_escape($valores['inicio_real']) ?>">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                                <div class="col-sm-4 form-group<?php echo $field_error_class('termino_real'); ?>">
                                    <label for="termino_real">Término real</label>
                                    <div class="input-group date" id="datetimepicker_termino_real">
                                        <input type="text" id="termino_real" name="termino_real" class="form-control js-data" autocomplete="off" placeholder="dd/mm/aaaa" value="<?php echo html_escape($valores['termino_real']) ?>">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                                <div class="col-sm-4 form-group">
                                    <label>Chamados do projeto</label>
                                    <div class="projeto-chamados">
                                        <strong><?php echo (int) $execucao['total'] - (int) $execucao['pendentes'] ?></strong> de <?php echo (int) $execucao['total'] ?> concluídos
                                        <?php if ($execucao['pendentes'] > 0) { ?><br><span class="text-yellow"><?php echo (int) $execucao['pendentes'] ?> pendente<?php echo $execucao['pendentes'] == 1 ? '' : 's' ?></span><?php } ?>
                                    </div>
                                </div>
                            </div>
                            <?php if ($plano->status === 'FI') { ?>
                                <p class="help-block"><i class="fa fa-info-circle"></i> Projeto finalizado. Apagar o término real reabre o projeto.</p>
                            <?php } elseif ($execucao['pendentes'] > 0) { ?>
                                <p class="help-block text-yellow"><i class="fa fa-exclamation-triangle"></i> Para finalizar (preencher o término real), todos os chamados precisam estar "Em avaliação" ou "Avaliado".</p>
                            <?php } else { ?>
                                <p class="help-block"><i class="fa fa-info-circle"></i> Preencha o término real para finalizar o projeto.</p>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>

            </div>

            <div class="col-md-4">
                <div class="sticky-column">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-user"></i> Responsável</h3>
                        </div>
                        <div class="box-body">
                            <div class="form-group<?php echo $field_error_class('responsavel'); ?>" style="margin-bottom:0;">
                                <label for="responsavel">Quem acompanha o projeto<?php echo $required_mark; ?></label>
                                <select id="responsavel" name="responsavel" class="singleInputDynamic form-control">
                                    <?php foreach ($usuarios_options as $id => $nome) { ?>
                                        <option value="<?php echo html_escape($id) ?>" <?php echo (string) $valores['responsavel'] === (string) $id ? 'selected' : '' ?>><?php echo html_escape($nome) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="box box-default projeto-resumo">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-list-alt"></i> Resumo</h3>
                        </div>
                        <div class="box-body">
                            <dl>
                                <dt>Projeto</dt><dd id="resumo-nome" class="is-empty">Não informado</dd>
                                <dt>Tipo</dt><dd id="resumo-tipo"></dd>
                                <dt>Cliente</dt><dd id="resumo-cliente"></dd>
                                <dt>Período</dt><dd id="resumo-periodo" class="is-empty">Não informado</dd>
                                <dt>Horas previstas</dt><dd id="resumo-horas" class="is-empty">Não informado</dd>
                            </dl>
                            <?php if (!$editando) { ?>
                                <p class="projeto-dica"><i class="fa fa-info-circle"></i> O projeto começa como <b>A iniciar</b> e passa para <b>Em andamento</b> quando o primeiro chamado for aberto nele. O término real é registrado ao finalizar.</p>
                            <?php } ?>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary btn-block" id="btn-salvar-projeto"><i class="fa fa-check"></i> <?php echo $editando ? 'Salvar alterações' : 'Criar projeto' ?></button>
                            <?php echo anchor($editando ? 'plano_acao/gantt/' . (int) $plano->id : 'plano_acao', lang('actions_cancel'), array('class' => 'btn btn-default btn-block')); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
    </section>
</div>

<script type="text/javascript">
    window.addEventListener('load', function() {
        // Os campos .singleInputDynamic já são ativados pelo funcoes.js.
        $('#datetimepicker_inicio_estimado, #datetimepicker_termino_estimado, #datetimepicker_inicio_real, #datetimepicker_termino_real').datetimepicker({
            locale: 'pt-br', format: 'DD/MM/YYYY', showClose: true, useCurrent: false, icons: {close: 'closeText'}
        });
        $('#datetimepicker_inicio_estimado, #datetimepicker_termino_estimado').on('dp.change', atualizar);
        $('#form-projeto').on('change keyup', 'input, select, textarea', atualizar);
        $('#form-projeto').on('submit', function() {
            $('#btn-salvar-projeto').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Salvando...');
        });

        function lerData(valor) {
            var partes = String(valor || '').split('/');
            if (partes.length !== 3) { return null; }
            var data = new Date(parseInt(partes[2], 10), parseInt(partes[1], 10) - 1, parseInt(partes[0], 10));
            return isNaN(data.getTime()) ? null : data;
        }
        function diasUteis(inicio, fim) {
            var dias = 0, atual = new Date(inicio);
            while (atual <= fim) {
                if (atual.getDay() !== 0 && atual.getDay() !== 6) { dias++; }
                atual.setDate(atual.getDate() + 1);
            }
            return dias;
        }
        function texto(id, valor) {
            $(id).text(valor || 'Não informado').toggleClass('is-empty', !valor);
        }

        function atualizar() {
            texto('#resumo-nome', $.trim($('#desc').val()));
            texto('#resumo-tipo', $.trim($('input[name="tipo"]:checked').parent().text()));
            texto('#resumo-cliente', $('#filial option:selected').text());

            var inicio = lerData($('#inicio_estimado').val()), fim = lerData($('#termino_estimado').val());
            var horas = parseFloat(String($('#horas_prevista').val()).replace(',', '.'));
            var duracao = $('#projeto-duracao').removeClass('text-red');
            if (inicio && fim && fim < inicio) {
                duracao.addClass('text-red').text('O término estimado precisa ser igual ou posterior ao início.');
                texto('#resumo-periodo', '');
            } else if (inicio && fim) {
                var uteis = diasUteis(inicio, fim);
                var frase = 'Duração: ' + uteis + ' dia' + (uteis === 1 ? '' : 's') + ' úte' + (uteis === 1 ? 'il' : 'is');
                if (horas > 0 && uteis > 0) {
                    frase += ' · média de ' + (horas / uteis).toFixed(1).replace('.', ',') + ' h por dia útil';
                }
                duracao.text(frase + '.');
                texto('#resumo-periodo', $('#inicio_estimado').val() + ' a ' + $('#termino_estimado').val() + ' (' + uteis + ' dias úteis)');
            } else {
                duracao.text('Informe as datas para ver a duração do projeto.');
                texto('#resumo-periodo', '');
            }
            texto('#resumo-horas', horas > 0 ? String(horas).replace('.', ',') + ' h' : '');
        }
        atualizar();
    });
</script>
<style type="text/css">
    .projeto-form .box { border-top-width: 3px; }
    .projeto-form .box-header .box-title { font-size: 16px; }
    .projeto-form .step-number { display: inline-block; width: 24px; height: 24px; line-height: 24px; margin-right: 6px; border-radius: 50%; background: #3c8dbc; color: #fff; text-align: center; font-size: 13px; font-weight: 600; }
    .projeto-form .box-header .box-subtitle { display: block; margin: 4px 0 0 30px; color: #777; font-size: 12px; }
    .projeto-form .form-group label { font-weight: 600; }
    .projeto-form .help-block { margin-bottom: 0; font-size: 12px; }
    .projeto-form .projeto-nome { font-size: 15px; }
    .projeto-form .fstElement { width: 100%; }
    .projeto-form .projeto-tipo .btn { min-width: 150px; }
    .projeto-form .projeto-tipo .btn.active { background: #eaf2f8; border-color: #3c8dbc; box-shadow: none; }
    .projeto-resumo dl { margin-bottom: 10px; }
    .projeto-resumo dt { color: #777; font-weight: 400; font-size: 12px; }
    .projeto-resumo dd { margin-bottom: 8px; font-weight: 600; word-break: break-word; }
    .projeto-resumo dd.is-empty { color: #bbb; font-weight: 400; font-style: italic; }
    .projeto-edit-heading { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 15px; }
    .projeto-edit-id { font-size: 18px; font-weight: 700; color: #777; }
    .projeto-edit-nome { font-size: 18px; font-weight: 600; }
    .projeto-chamados { padding-top: 6px; }
    .projeto-dica { margin: 0; padding: 10px; background: #f7f9fb; border-radius: 4px; font-size: 12px; color: #666; }
    @media (min-width: 992px) {
        .projeto-form .sticky-column { position: sticky; top: 60px; }
    }
</style>
