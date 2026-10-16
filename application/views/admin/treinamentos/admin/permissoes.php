<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-users text-primary"></i> Liberação de Acesso ao Treinamento
            <small><?php echo htmlspecialchars($treinamento->titulo); ?></small>
        </h1>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if (!empty($message)) echo $message; ?>

        <div class="box box-primary" style="border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.04);">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-lock text-primary"></i> Modalidade de Acesso</h3>
            </div>

            <?php echo form_open(current_url(), array('class' => 'form-horizontal')); ?>
            <div class="box-body" style="padding: 30px;">

                <!-- Modalidade de Acesso -->
                <div class="form-group">
                    <label class="col-sm-3 control-label">Modalidade de Acesso *</label>
                    <div class="col-sm-7">
                        <select name="tipo_acesso" id="selectTipoAcesso" class="form-control" style="width: 100%; height: 38px; font-size: 14px; border-radius: 6px; background-color: #fff; cursor: pointer;">
                            <option value="livre" <?php echo ($treinamento->tipo_acesso === 'livre') ? 'selected' : ''; ?>>
                                Acesso Livre (Todos os usuários autenticados da empresa podem visualizar)
                            </option>
                            <option value="restrito" <?php echo ($treinamento->tipo_acesso === 'restrito') ? 'selected' : ''; ?>>
                                Acesso Restrito (Apenas usuários autorizados)
                            </option>
                        </select>
                        <p class="help-block" style="margin-top: 10px; font-size: 13px; color: #64748b;">
                            Selecione se o treinamento deve estar disponível publicamente para todos os colaboradores ou apenas de forma restrita.
                        </p>
                    </div>
                </div>

            </div>

            <div class="box-footer" style="padding: 15px 30px; background: #f8fafc;">
                <div class="row">
                    <div class="col-sm-7 col-sm-offset-3">
                        <button type="submit" class="btn btn-primary" style="font-weight: 700; border-radius: 6px; padding: 8px 24px;">
                            <i class="fa fa-check"></i> Salvar Modalidade de Acesso
                        </button>
                        <a href="<?php echo site_url('treinamentos_admin'); ?>" class="btn btn-default" style="border-radius: 6px; margin-left: 8px;">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </section>
</div>
