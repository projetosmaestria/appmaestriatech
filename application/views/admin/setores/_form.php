<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Formulário compartilhado entre criação ($modo = 'create') e edição ($modo = 'edit') de setor.
$editando = $modo === 'edit';
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content cadastro-form">
        <?php if (isset($message)) { echo $message; } ?>
        <?php echo form_open(current_url(), array('id' => 'form-setor')); ?>
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">1</span>Setor</h3>
                        <span class="box-subtitle">Os setores aparecem no cadastro de usuários para organizar a equipe.</span>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-7 form-group<?php echo form_error('descricao') !== '' ? ' has-error' : '' ?>">
                                <label for="descricao">Nome do setor <span class="text-red">*</span></label>
                                <input type="text" id="descricao" name="descricao" class="form-control" maxlength="255" placeholder="Ex.: Suporte, Comercial, Desenvolvimento" value="<?php echo html_escape($valores['descricao']) ?>">
                            </div>
                            <div class="col-sm-5 form-group">
                                <label>Status <span class="text-red">*</span></label>
                                <div>
                                    <div class="btn-group cadastro-segmentos" data-toggle="buttons">
                                        <label class="btn btn-default <?php echo $valores['status'] === '1' ? 'active' : '' ?>"><input type="radio" name="status" value="1" autocomplete="off" <?php echo $valores['status'] === '1' ? 'checked' : '' ?>><i class="fa fa-check text-green"></i> Ativo</label>
                                        <label class="btn btn-default <?php echo $valores['status'] === '0' ? 'active' : '' ?>"><input type="radio" name="status" value="0" autocomplete="off" <?php echo $valores['status'] === '0' ? 'checked' : '' ?>><i class="fa fa-ban text-muted"></i> Inativo</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="sticky-column">
                    <div class="box box-default cadastro-resumo">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-list-alt"></i> Resumo</h3>
                        </div>
                        <div class="box-body">
                            <dl>
                                <dt>Setor</dt><dd id="resumo-nome"><?php echo html_escape($valores['descricao']) ?></dd>
                                <?php if ($editando) { ?><dt>Usuários ativos no setor</dt><dd><?php echo (int) $usuarios ?></dd><?php } ?>
                            </dl>
                            <?php if ($editando && $usuarios > 0) { ?>
                                <p class="cadastro-dica"><i class="fa fa-info-circle"></i> Inativar o setor não remove os usuários dele; só deixa de oferecê-lo em novos cadastros.</p>
                            <?php } ?>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-check"></i> <?php echo $editando ? 'Salvar alterações' : 'Criar setor' ?></button>
                            <?php echo anchor('setores', lang('actions_cancel'), array('class' => 'btn btn-default btn-block')); ?>
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
        $('#descricao').on('input', function() {
            $('#resumo-nome').text($.trim($(this).val()) || 'Não informado').toggleClass('is-empty', !$.trim($(this).val()));
        }).trigger('input');
    });
</script>
<?php $this->load->view('admin/_partials/cadastro_estilos'); ?>
