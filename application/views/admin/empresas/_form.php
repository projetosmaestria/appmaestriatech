<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Formulário compartilhado entre criação ($modo = 'create') e edição ($modo = 'edit') de empresa.
$editando = $modo === 'edit';
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content cadastro-form">
        <?php if (isset($message)) { echo $message; } ?>
        <?php echo form_open(current_url(), array('id' => 'form-empresa')); ?>
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">1</span>Empresa</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group<?php echo form_error('empresa_name') !== '' ? ' has-error' : '' ?>" style="margin-bottom:0;">
                            <label for="empresa_name">Nome <span class="text-red">*</span></label>
                            <input type="text" id="empresa_name" name="empresa_name" class="form-control" maxlength="250" value="<?php echo html_escape($nome) ?>">
                        </div>
                    </div>
                </div>
                <?php if (!$editando) { ?>
                    <div class="box box-default">
                        <div class="box-header with-border">
                            <h3 class="box-title"><span class="step-number">2</span>O que é criado junto</h3>
                        </div>
                        <div class="box-body">
                            <ul class="empresa-criacao">
                                <li><i class="fa fa-users text-blue"></i> Grupo <b>admin</b> com todas as permissões, e você como membro.</li>
                                <li><i class="fa fa-sliders text-blue"></i> Preferências e configuração de e-mail em branco.</li>
                                <li><i class="fa fa-envelope-o text-blue"></i> Modelos padrão de e-mail (cadastro de usuário, senha, chamados, agenda e contratos).</li>
                            </ul>
                            <p class="cadastro-dica"><i class="fa fa-info-circle"></i> Depois de criar, habilite as telas da empresa em Menus e cadastre as filiais.</p>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="col-md-4">
                <div class="sticky-column">
                    <div class="box box-default cadastro-resumo">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-list-alt"></i> Resumo</h3>
                        </div>
                        <div class="box-body">
                            <dl>
                                <dt>Empresa</dt><dd id="resumo-nome"></dd>
                                <?php if ($editando && $numeros) { ?>
                                    <dt>Filiais ativas</dt><dd><?php echo (int) $numeros->filiais ?></dd>
                                    <dt>Usuários ativos</dt><dd><?php echo (int) $numeros->usuarios ?></dd>
                                    <dt>Grupos</dt><dd><?php echo (int) $numeros->grupos ?></dd>
                                    <dt>Telas habilitadas</dt><dd><?php echo (int) $numeros->menus ?></dd>
                                <?php } ?>
                            </dl>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-check"></i> <?php echo $editando ? 'Salvar alterações' : 'Criar empresa' ?></button>
                            <?php echo anchor('empresas', lang('actions_cancel'), array('class' => 'btn btn-default btn-block')); ?>
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
        $('#empresa_name').on('input', function() {
            var valor = $.trim($(this).val());
            $('#resumo-nome').text(valor || 'Não informado').toggleClass('is-empty', !valor);
        }).trigger('input');
    });
</script>
<?php $this->load->view('admin/_partials/cadastro_estilos'); ?>
<style type="text/css">
    .empresa-criacao { list-style: none; padding: 0; margin: 0 0 10px; }
    .empresa-criacao li { padding: 4px 0; }
    .empresa-criacao .fa { width: 18px; }
</style>
