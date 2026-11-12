<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$field_error_class = function ($field) {
    return form_error($field) !== '' ? ' has-error' : '';
};
$required_mark = ' <span class="text-red">*</span>';
$anexo_config = get_upload_config();
$anexo_tipos = explode('|', $anexo_config['allowed_types']);
$anexo_max_kb = (int) $anexo_config['max_size'];
?>
<style>
    .chamado-create .box { border-top-width: 3px; }
    .chamado-create .box-header .box-title { font-size: 16px; }
    .chamado-create .step-number {
        display: inline-block; width: 24px; height: 24px; line-height: 24px; margin-right: 6px;
        border-radius: 50%; background: #3c8dbc; color: #fff; text-align: center; font-size: 13px; font-weight: 600;
    }
    .chamado-create .box-header .box-subtitle { display: block; margin: 4px 0 0 30px; color: #777; font-size: 12px; }
    .chamado-create .form-group label { font-weight: 600; }
    .chamado-create .help-block { margin-bottom: 0; font-size: 12px; }
    .chamado-create #desc { min-height: 220px; resize: vertical; }
    .chamado-create #titulo { font-size: 15px; }
    .chamado-create .fstElement { width: 100%; }
    .chamado-summary dl { margin-bottom: 0; }
    .chamado-summary dt { color: #777; font-weight: 400; font-size: 12px; }
    .chamado-summary dd { margin-bottom: 8px; font-weight: 600; word-break: break-word; }
    .chamado-summary dd.is-empty { color: #bbb; font-weight: 400; font-style: italic; }
    .chamado-summary .progress { height: 8px; margin-bottom: 6px; }
    .chamado-summary .pending-list { margin: 8px 0 0; padding-left: 18px; font-size: 12px; color: #a94442; }
    .anexo-dropzone {
        display: block; padding: 22px 16px; border: 2px dashed #c9d3dd; border-radius: 6px;
        background: #f9fbfc; color: #667; text-align: center; cursor: pointer; font-weight: 400 !important;
        transition: border-color .15s, background .15s;
    }
    .anexo-dropzone:hover, .anexo-dropzone.is-dragover { border-color: #3c8dbc; background: #eef5fa; }
    .anexo-dropzone .fa { display: block; margin-bottom: 6px; font-size: 26px; color: #3c8dbc; }
    .anexo-dropzone strong { color: #3c8dbc; }
    .anexo-list { margin: 10px 0 0; padding: 0; list-style: none; }
    .anexo-list li {
        display: flex; align-items: center; gap: 8px; padding: 6px 8px; border: 1px solid #eee;
        border-radius: 4px; margin-bottom: 4px;
    }
    .anexo-list li.is-invalid { border-color: #f2c6c6; background: #fdf3f3; }
    .anexo-list .anexo-nome { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .anexo-list .anexo-info { color: #888; font-size: 12px; white-space: nowrap; }
    .anexo-list li.is-invalid .anexo-info { color: #a94442; }
    .swal-novo-solicitante { text-align: left; margin-top: 10px; }
    .swal-novo-solicitante .form-group { margin-bottom: 12px; }
    .swal-novo-solicitante label { display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px; }
    @media (min-width: 992px) {
        .chamado-create .sticky-column { position: sticky; top: 60px; }
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content chamado-create">
        <?php if (isset($message)) { echo $message; } ?>

        <?php echo form_open_multipart(current_url(), array('id' => 'form-create_chamados')); ?>
        <input type="hidden" name="base_url" id="base_url" value="<?php echo base_url() ?>">

        <?php if ($is_full_create == true) { ?>
            <div style="display: none;">
                <?php echo form_dropdown($quadro); ?>
            </div>
        <?php } ?>

        <div class="row">
            <div class="col-md-8">

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">1</span>Cliente e produto</h3>
                        <span class="box-subtitle">Para quem é o chamado e em qual parte do sistema está a demanda.</span>
                    </div>
                    <div class="box-body">
                        <div class="form-group<?php echo $field_error_class('unidade'); ?>">
                            <label for="unidade">Cliente (unidade)<?php echo $required_mark; ?></label>
                            <?php echo form_dropdown($unidade); ?>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group<?php echo $field_error_class('produto'); ?>">
                                    <label for="produto"><?php echo lang('chamados_produto') . $required_mark; ?></label>
                                    <?php echo form_dropdown($produto); ?>
                                    <p class="help-block">Lista os produtos habilitados para o cliente.</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group<?php echo $field_error_class('modulo'); ?>">
                                    <label for="modulo"><?php echo lang('chamados_modulo') . $required_mark; ?></label>
                                    <?php echo form_dropdown($modulo); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">2</span>Classificação</h3>
                        <span class="box-subtitle">Define o tipo de atendimento e o esforço esperado.</span>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group<?php echo $field_error_class('categoria'); ?>">
                                    <label for="categoria"><?php echo lang('chamados_categoria') . $required_mark; ?></label>
                                    <?php echo form_dropdown($categoria); ?>
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <div class="form-group<?php echo $field_error_class('tipo_servico'); ?>">
                                    <label for="tipo_servico"><?php echo lang('chamados_tipo_servico') . $required_mark; ?></label>
                                    <?php echo form_dropdown($tipo_servico); ?>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group<?php echo $field_error_class('nivel'); ?>">
                                    <label for="nivel"><?php echo lang('chamados_nivel') . $required_mark; ?></label>
                                    <?php echo form_dropdown($nivel); ?>
                                    <p class="help-block" id="nivel-estimativa">Sugerido pelo tipo de serviço.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">3</span>Descrição da demanda</h3>
                        <span class="box-subtitle">Quanto mais claro, mais rápido o atendimento.</span>
                    </div>
                    <div class="box-body">
                        <div class="form-group<?php echo $field_error_class('titulo'); ?>">
                            <label for="titulo"><?php echo lang('chamados_titulo') . $required_mark; ?></label>
                            <?php echo form_input(array_merge($titulo, array(
                                'placeholder' => 'Resumo em uma frase. Ex.: Erro ao emitir nota fiscal de serviço',
                                'autocomplete' => 'off',
                            ))); ?>
                        </div>
                        <div class="form-group<?php echo $field_error_class('desc'); ?>">
                            <label for="desc"><?php echo lang('chamados_desc') . $required_mark; ?></label>
                            <?php echo form_textarea(array_merge($desc, array(
                                'rows' => 10,
                                'placeholder' => "O que aconteceu ou o que precisa ser feito?\nOnde acontece (tela, rotina, relatório)?\nComo reproduzir e qual o resultado esperado?",
                            ))); ?>
                            <p class="help-block">Ao escolher a categoria, o modelo de descrição dela é preenchido aqui.</p>
                        </div>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">4</span>Anexos <small class="text-muted">(opcional)</small></h3>
                        <span class="box-subtitle">Prints da tela, planilhas ou documentos que ajudem a entender a demanda.</span>
                    </div>
                    <div class="box-body">
                        <label class="anexo-dropzone" for="anexo" id="anexo-dropzone">
                            <i class="fa fa-cloud-upload"></i>
                            Arraste os arquivos aqui ou <strong>clique para selecionar</strong>
                            <input type="file" name="anexo[]" id="anexo" multiple class="hidden"
                                   accept="<?php echo html_escape('.' . implode(',.', $anexo_tipos)); ?>">
                        </label>
                        <p class="help-block">
                            Até <?php echo $anexo_max_kb / 1024; ?> MB por arquivo. Formatos: <?php echo html_escape(implode(', ', $anexo_tipos)); ?>.
                        </p>
                        <ul class="anexo-list" id="anexo-list"></ul>
                        <?php if (!empty($pode_anexo_interno)) { ?>
                            <div class="checkbox" style="margin-bottom:0;">
                                <label for="anexos_internos">
                                    <input type="checkbox" name="anexos_internos" id="anexos_internos" value="1"<?php echo set_checkbox('anexos_internos', '1'); ?>>
                                    <i class="fa fa-lock"></i> Anexos internos
                                </label>
                                <p class="help-block">O solicitante não vê esses arquivos na conversa nem no e-mail de abertura.</p>
                            </div>
                        <?php } ?>
                    </div>
                </div>

            </div>

            <div class="col-md-4">
                <div class="sticky-column">

                    <?php if ($is_full_create == true) { ?>
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-users"></i> Atribuição</h3>
                            </div>
                            <div class="box-body">
                                <div class="form-group<?php echo $field_error_class('prioridade'); ?>">
                                    <label for="prioridade"><?php echo lang('chamados_prioridade') . $required_mark; ?></label>
                                    <?php echo form_dropdown($prioridade); ?>
                                </div>
                                <?php if (@$dono): ?>
                                    <div class="form-group<?php echo $field_error_class('dono'); ?>">
                                        <label for="dono">Solicitante</label>
                                        <div class="input-group">
                                            <?php echo form_dropdown($dono); ?>
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-default btn-flat" id="btn-novo-solicitante" title="Cadastrar novo solicitante">
                                                    <i class="fa fa-user-plus"></i>
                                                </button>
                                            </span>
                                        </div>
                                        <p class="help-block">Usuário do cliente que pediu o chamado. Em branco, fica você. Não está na lista? Cadastre pelo <i class="fa fa-user-plus"></i>.</p>
                                    </div>
                                <?php endif ?>
                                <div class="form-group<?php echo $field_error_class('responsavel'); ?>">
                                    <label for="responsavel"><?php echo lang('chamados_responsavel') . $required_mark; ?></label>
                                    <?php echo form_dropdown($responsavel); ?>
                                </div>
                                <div class="form-group">
                                    <label for="desenvolvedor">Programador</label>
                                    <?php echo form_dropdown($desenvolvedor); ?>
                                    <p class="help-block">Com programador, o chamado aberto ou em andamento aparece na lista dele no planejamento.</p>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="box box-default">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-link"></i> Acompanhamento</h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="form-group">
                                <label for="interessados"><?php echo lang('chamados_interessados'); ?></label>
                                <?php echo form_dropdown($interessados); ?>
                                <p class="help-block">Recebem os e-mails do chamado. Segure Ctrl (ou ⌘) para marcar vários.</p>
                            </div>
                            <div class="form-group">
                                <label for="referencia">Chamado relacionado</label>
                                <?php echo form_dropdown($referencia); ?>
                                <p class="help-block">Use quando a demanda continua ou depende de outro chamado aberto.</p>
                            </div>
                            <div class="form-group">
                                <label for="plano_acao"><?php echo lang('plano_acao'); ?></label>
                                <?php echo form_dropdown($plano_acao); ?>
                            </div>
                        </div>
                    </div>

                    <div class="box box-default chamado-summary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-list-alt"></i> Resumo</h3>
                        </div>
                        <div class="box-body">
                            <div class="progress"><div class="progress-bar progress-bar-success" id="chamado-progress" style="width: 0%"></div></div>
                            <small class="text-muted" id="chamado-progress-label"></small>
                            <ul class="pending-list" id="chamado-pending"></ul>
                            <hr>
                            <dl>
                                <dt>Cliente</dt><dd data-summary="unidade"></dd>
                                <dt>Produto / módulo</dt><dd data-summary="produto,modulo"></dd>
                                <dt>Categoria / serviço</dt><dd data-summary="categoria,tipo_servico"></dd>
                                <dt>Nível</dt><dd data-summary="nivel"></dd>
                                <dt>Anexos</dt><dd id="summary-anexos"></dd>
                                <?php if ($is_full_create == true) { ?>
                                    <dt>Prioridade</dt><dd data-summary="prioridade"></dd>
                                    <dt>Responsável / programador</dt><dd data-summary="responsavel,desenvolvedor"></dd>
                                <?php } ?>
                            </dl>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary btn-flat btn-block" id="btn-salvar-chamado">
                                <i class="fa fa-check"></i> Abrir chamado
                            </button>
                            <?php echo anchor('chamados', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat btn-block')); ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
    </section>
</div>
<script type="text/javascript">
    window.onload = function() {
        $('.multipleSelect').fastselect();

        var $form = $('#form-create_chamados');
        var required = <?php echo json_encode(array_merge(
            array(
                'unidade' => 'Cliente',
                'produto' => 'Produto',
                'modulo' => 'Módulo',
                'categoria' => 'Categoria',
                'tipo_servico' => 'Tipo de serviço',
                'nivel' => 'Nível',
                'titulo' => 'Título',
                'desc' => 'Descrição',
            ),
            $is_full_create == true ? array('prioridade' => 'Prioridade', 'responsavel' => 'Responsável') : array()
        ), JSON_UNESCAPED_UNICODE); ?>;
        var levelHours = {'nível 1': 2, 'nível 2': 4, 'nível 3': 8, 'nível 4': 16};

        function selectedText(id) {
            var $field = $('#' + id);
            if (!$field.length || !$field.val()) {
                return '';
            }
            return $.trim($field.find('option:selected').text());
        }

        function updateSummary() {
            $('[data-summary]').each(function() {
                var parts = $.map($(this).data('summary').split(','), function(id) {
                    return selectedText(id) || null;
                });
                $(this).text(parts.length ? parts.join(' / ') : 'Não informado').toggleClass('is-empty', !parts.length);
            });

            var pending = [];
            $.each(required, function(id, label) {
                if (!$.trim($('#' + id).val() || '')) {
                    pending.push(label);
                }
            });
            var total = Object.keys(required).length;
            var done = total - pending.length;
            $('#chamado-progress').css('width', Math.round(done / total * 100) + '%');
            $('#chamado-progress-label').text(pending.length
                ? done + ' de ' + total + ' campos obrigatórios preenchidos'
                : 'Tudo pronto para abrir o chamado.');
            $('#chamado-pending').html($.map(pending, function(label) {
                return $('<li>').text(label).prop('outerHTML');
            }).join(''));

            var hours = levelHours[selectedText('nivel').toLowerCase()];
            $('#nivel-estimativa').text(hours ? 'Estimativa: ' + hours + 'h de esforço.' : 'Sugerido pelo tipo de serviço.');
        }

        $form.on('change keyup', 'select, input, textarea', updateSummary);
        // Os selects são recarregados via AJAX ao trocar o campo anterior da cascata.
        $(document).ajaxComplete(updateSummary);
        updateSummary();

        var anexoInput = document.getElementById('anexo');
        var anexoTipos = <?php echo json_encode($anexo_tipos); ?>;
        var anexoMaxBytes = <?php echo $anexo_max_kb * 1024; ?>;
        var anexoFiles = [];

        function formatSize(bytes) {
            return bytes >= 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
        }

        function anexoProblem(file) {
            var ext = file.name.split('.').pop().toLowerCase();
            if (file.name.indexOf('.') === -1 || anexoTipos.indexOf(ext) === -1) {
                return 'Formato não permitido';
            }
            if (file.size > anexoMaxBytes) {
                return 'Maior que ' + formatSize(anexoMaxBytes);
            }
            return '';
        }

        function syncAnexos() {
            var transfer = new DataTransfer();
            var $list = $('#anexo-list').empty();
            var validCount = 0;
            $.each(anexoFiles, function(index, file) {
                var problem = anexoProblem(file);
                if (!problem) {
                    transfer.items.add(file);
                    validCount++;
                }
                $('<li>').toggleClass('is-invalid', !!problem)
                    .append($('<i class="fa">').addClass(problem ? 'fa-exclamation-triangle text-red' : 'fa-file-o text-muted'))
                    .append($('<span class="anexo-nome">').text(file.name).attr('title', file.name))
                    .append($('<span class="anexo-info">').text(problem || formatSize(file.size)))
                    .append($('<button type="button" class="btn btn-box-tool" title="Remover">')
                        .html('<i class="fa fa-times"></i>')
                        .on('click', function() {
                            anexoFiles.splice(index, 1);
                            syncAnexos();
                        }))
                    .appendTo($list);
            });
            // Só os arquivos válidos vão no envio; os inválidos ficam listados para o usuário ver o motivo.
            anexoInput.files = transfer.files;
            var internos = validCount && $('#anexos_internos').is(':checked') ? (validCount === 1 ? ' (interno)' : ' (internos)') : '';
            $('#summary-anexos').text(validCount ? validCount + (validCount === 1 ? ' arquivo' : ' arquivos') + internos : 'Nenhum')
                .toggleClass('is-empty', !validCount);
        }
        $('#anexos_internos').on('change', function() {
            syncAnexos();
        });

        function addAnexos(fileList) {
            $.each(fileList, function(i, file) {
                var duplicated = anexoFiles.some(function(current) {
                    return current.name === file.name && current.size === file.size;
                });
                if (!duplicated) {
                    anexoFiles.push(file);
                }
            });
            syncAnexos();
        }

        $(anexoInput).on('change', function() {
            // Cada nova seleção substitui this.files; somamos à lista e syncAnexos regrava o input.
            addAnexos(Array.prototype.slice.call(this.files));
        });
        $('#anexo-dropzone')
            .on('dragenter dragover', function(e) {
                e.preventDefault();
                $(this).addClass('is-dragover');
            })
            .on('dragleave drop', function(e) {
                e.preventDefault();
                $(this).removeClass('is-dragover');
                if (e.type === 'drop' && e.originalEvent.dataTransfer.files.length) {
                    addAnexos(e.originalEvent.dataTransfer.files);
                }
            });
        syncAnexos();

        var $dono = $('#dono');

        function aplicarMascaraFone($el) {
            if ($.fn.inputmask) {
                $el.inputmask({
                    mask: ['(99) 9999-9999', '(99) 99999-9999'],
                    keepStatic: true
                });
                return;
            }
            $el.on('input', function() {
                var d = this.value.replace(/\D/g, '').slice(0, 11);
                if (d.length > 10) {
                    this.value = '(' + d.slice(0, 2) + ') ' + d.slice(2, 7) + '-' + d.slice(7);
                } else if (d.length > 6) {
                    this.value = '(' + d.slice(0, 2) + ') ' + d.slice(2, 6) + '-' + d.slice(6);
                } else if (d.length > 2) {
                    this.value = '(' + d.slice(0, 2) + ') ' + d.slice(2);
                } else if (d.length > 0) {
                    this.value = '(' + d;
                } else {
                    this.value = '';
                }
            });
        }

        function abrirNovoSolicitante() {
            var unidadeId = $.trim($('#unidade').val() || '');
            var baseUrl = $('#base_url').val() || '';

            if (!unidadeId) {
                Swal.fire({
                    icon: 'info',
                    title: 'Selecione o cliente',
                    text: 'Escolha o cliente (unidade) do chamado antes de cadastrar o solicitante.',
                    confirmButtonColor: '#3c8dbc'
                });
                return;
            }

            var campo = function(id, label, type, placeholder, extra) {
                return '<div class="form-group">' +
                    '<label for="' + id + '">' + label + ' <span class="text-red">*</span></label>' +
                    '<input type="' + type + '" id="' + id + '" class="form-control" placeholder="' + placeholder + '" autocomplete="off"' + (extra || '') + '>' +
                '</div>';
            };
            var unidadeNome = $('<span>').text(selectedText('unidade')).html();
            var htmlModal =
                '<p style="margin:0 0 12px;font-size:14px;">O usuário será criado já ativo no cliente <strong>' + unidadeNome + '</strong> e no grupo Clientes, e receberá um e-mail para cadastrar a senha.</p>' +
                '<div class="swal-novo-solicitante">' +
                    '<div class="row">' +
                        '<div class="col-sm-6">' + campo('swal-novo-nome', 'Nome', 'text', 'Nome', ' maxlength="50"') + '</div>' +
                        '<div class="col-sm-6">' + campo('swal-novo-sobrenome', 'Sobrenome', 'text', 'Sobrenome', ' maxlength="50"') + '</div>' +
                    '</div>' +
                    campo('swal-novo-email', 'E-mail', 'email', 'nome@empresa.com.br', ' maxlength="254"') +
                    campo('swal-novo-celular', 'Celular / WhatsApp', 'tel', '(00) 00000-0000', ' maxlength="15"') +
                '</div>';

            Swal.fire({
                title: 'Novo solicitante',
                html: htmlModal,
                width: 560,
                showCancelButton: true,
                confirmButtonText: 'Cadastrar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#3c8dbc',
                reverseButtons: true,
                focusConfirm: false,
                showLoaderOnConfirm: true,
                allowOutsideClick: function() {
                    return !Swal.isLoading();
                },
                didOpen: function() {
                    aplicarMascaraFone($('#swal-novo-celular'));
                    $('.swal-novo-solicitante input').on('keydown', function(ev) {
                        if (ev.which === 13) {
                            ev.preventDefault();
                            Swal.clickConfirm();
                        }
                    });
                    $('#swal-novo-nome').trigger('focus');
                },
                preConfirm: function() {
                    var dados = {
                        unidade_id: unidadeId,
                        first_name: $.trim($('#swal-novo-nome').val() || ''),
                        last_name: $.trim($('#swal-novo-sobrenome').val() || ''),
                        email: $.trim($('#swal-novo-email').val() || ''),
                        celular: $.trim($('#swal-novo-celular').val() || '')
                    };
                    var celDigits = dados.celular.replace(/\D/g, '');

                    if (!dados.first_name || !dados.last_name) {
                        Swal.showValidationMessage('Informe o nome e o sobrenome.');
                        return false;
                    }
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(dados.email)) {
                        Swal.showValidationMessage('Informe um e-mail válido.');
                        return false;
                    }
                    if (celDigits.length < 10 || celDigits.length > 11) {
                        Swal.showValidationMessage('Informe um celular/WhatsApp válido com DDD.');
                        return false;
                    }

                    return new Promise(function(resolve) {
                        $.ajax({
                            url: baseUrl + 'chamados/criar_solicitante',
                            type: 'POST',
                            dataType: 'json',
                            data: dados
                        }).done(function(res) {
                            if (!res || !res.ok) {
                                Swal.showValidationMessage((res && res.message) ? res.message : 'Não foi possível cadastrar o solicitante.');
                                resolve(false);
                                return;
                            }
                            resolve(res);
                        }).fail(function(xhr) {
                            var msg = (xhr && xhr.responseJSON && xhr.responseJSON.message)
                                ? xhr.responseJSON.message
                                : 'Erro ao cadastrar o solicitante.';
                            Swal.showValidationMessage(msg);
                            resolve(false);
                        });
                    });
                }
            }).then(function(result) {
                if (!result || !result.isConfirmed || !result.value) {
                    return;
                }
                var novo = result.value;
                // Se o cliente foi trocado enquanto o modal estava aberto, as listas já são de outra unidade.
                if (String($.trim($('#unidade').val() || '')) === String(unidadeId)) {
                    // Unidade sem usuários vem só com a mensagem de lista vazia no lugar do "Selecione".
                    $dono.find('option[value=""]').first().text('Selecione');
                    $('#interessados').find('option[value=""]').remove();
                    $('<option>').val(novo.id).text(novo.nome + ' (' + novo.email + ')').appendTo('#interessados');
                    $('<option>').val(novo.id).text(novo.nome).appendTo($dono);
                    $dono.val(String(novo.id)).trigger('change');
                }
                if (novo.email_enviado) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Solicitante cadastrado!',
                        text: 'Enviamos o e-mail para ele cadastrar a senha.',
                        showConfirmButton: false,
                        timer: 3500
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Solicitante cadastrado',
                        text: 'Não foi possível enviar o e-mail para cadastrar a senha. O solicitante pode usar o "Esqueci minha senha" na tela de login.',
                        confirmButtonColor: '#3c8dbc'
                    });
                }
            });
        }

        $('#btn-novo-solicitante').on('click', abrirNovoSolicitante);

        $form.on('submit', function(e) {
            var pending = $.grep(Object.keys(required), function(id) {
                return !$.trim($('#' + id).val() || '');
            });
            if (pending.length && anexoFiles.length) {
                // Se o servidor recusar o formulário, o navegador descarta os arquivos escolhidos.
                e.preventDefault();
                $('#chamado-pending').closest('.box')[0].scrollIntoView({behavior: 'smooth'});
                $('#chamado-progress-label').html('<span class="text-red">Preencha os campos obrigatórios antes de enviar os anexos.</span>');
                return;
            }
            $('#btn-salvar-chamado').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Salvando...');
        });
    };
</script>
