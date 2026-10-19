<?php 
defined('BASEPATH') OR exit('No direct script access allowed'); 

$groupIdsAtuais = !empty($groupIdsAtuais) ? (array)$groupIdsAtuais : (!empty($grupos_selecionados) ? (array)$grupos_selecionados : array());
$userIdsAtuais  = !empty($userIdsAtuais)  ? (array)$userIdsAtuais  : (!empty($usuarios_selecionados) ? (array)$usuarios_selecionados : array());
?>

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
                <h3 class="box-title"><i class="fa fa-lock text-primary"></i> Modalidade e Regras de Acesso</h3>
                <div class="box-tools pull-right">
                    <a href="<?php echo site_url('treinamentos_admin'); ?>" class="btn btn-default btn-sm" style="border-radius: 6px;">
                        <i class="fa fa-arrow-left"></i> Voltar à Listagem
                    </a>
                </div>
            </div>

            <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'formPermissoesTreinamento')); ?>
            <div class="box-body" style="padding: 25px 30px;">

                <!-- Modalidade de Acesso Principal -->
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="selectTipoAcesso">Modalidade de Acesso *</label>
                    <div class="col-sm-8">
                        <select name="tipo_acesso" id="selectTipoAcesso" class="form-control" onchange="var b = document.getElementById('blocoAcessoRestrito'); if (b) { b.style.display = (this.value === 'restrito') ? 'block' : 'none'; }" style="width: 100%; height: 42px; font-size: 14px; border-radius: 6px; background-color: #fff; cursor: pointer; font-weight: 600;">
                            <option value="livre" <?php echo ($treinamento->tipo_acesso === 'livre') ? 'selected' : ''; ?>>
                                Acesso Livre (Todos os colaboradores da empresa podem visualizar e cursar)
                            </option>
                            <option value="restrito" <?php echo ($treinamento->tipo_acesso === 'restrito') ? 'selected' : ''; ?>>
                                Acesso Restrito (Apenas grupos e/ou usuários específicos autorizados abaixo)
                            </option>
                        </select>
                        <p class="help-block" style="margin-top: 8px; font-size: 13px; color: #64748b;">
                            Selecione <strong>Acesso Restrito</strong> para habilitar as caixas de seleção de Grupos e Usuários abaixo.
                        </p>
                    </div>
                </div>

                <!-- Bloco Condicional de Acesso Restrito -->
                <div id="blocoAcessoRestrito" style="<?php echo ($treinamento->tipo_acesso === 'restrito') ? 'display: block;' : 'display: none;'; ?> margin-top: 25px; padding-top: 20px; border-top: 1px dashed #cbd5e1;">
                    <div class="row">
                        
                        <!-- 1. GRUPOS DE USUÁRIOS -->
                        <div class="col-md-6" style="margin-bottom: 20px;">
                            <div class="panel panel-default" style="border-radius: 8px; box-shadow: none; border-color: #cbd5e1;">
                                <div class="panel-heading" style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1; padding: 12px 15px; display: flex; align-items: center; justify-content: space-between;">
                                    <strong style="color: #1e293b; font-size: 14px;">
                                        <i class="fa fa-users text-primary"></i> Grupos Autorizados (<?php echo count($grupos ?? []); ?>)
                                    </strong>
                                    <div>
                                        <button type="button" class="btn btn-xs btn-primary" id="btnMarcarTodosGrupos">Marcar Todos</button>
                                        <button type="button" class="btn btn-xs btn-default" id="btnDesmarcarTodosGrupos">Desmarcar</button>
                                    </div>
                                </div>
                                <div class="panel-body" style="padding: 15px;">
                                    <div style="margin-bottom: 12px;">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                            <input type="text" id="filtroBuscaGrupos" class="form-control" placeholder="Filtrar grupos por nome...">
                                        </div>
                                    </div>
                                    <div id="listaContainerGrupos" style="max-height: 280px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; background: #fafafa;">
                                        <?php if (!empty($grupos)): ?>
                                            <?php foreach ($grupos as $g): ?>
                                                <?php $checado = in_array((int)$g->id, (array)$groupIdsAtuais); ?>
                                                <div class="item-checkbox-grupo" style="margin-bottom: 8px; padding: 6px 10px; border-radius: 4px; background: #ffffff; border: 1px solid #e2e8f0;">
                                                    <label style="cursor: pointer; width: 100%; margin: 0; display: flex; align-items: flex-start; gap: 8px;">
                                                        <input type="checkbox" name="grupos[]" value="<?php echo $g->id; ?>" class="chk-grupo" <?php echo $checado ? 'checked' : ''; ?> style="width: 17px; height: 17px; margin-top: 2px; cursor: pointer;">
                                                        <div style="flex: 1;">
                                                            <div class="nome-grupo" style="color: #1e293b; font-weight: 700; font-size: 13.5px;">
                                                                <?php echo htmlspecialchars($g->name); ?>
                                                            </div>
                                                            <?php if (!empty($g->description)): ?>
                                                                <small class="text-muted" style="font-size: 11.5px; display: block; line-height: 1.3;">
                                                                    <?php echo htmlspecialchars($g->description); ?>
                                                                </small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-muted text-center" style="margin: 15px 0;">Nenhum grupo cadastrado.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. USUÁRIOS ESPECÍFICOS -->
                        <div class="col-md-6" style="margin-bottom: 20px;">
                            <div class="panel panel-default" style="border-radius: 8px; box-shadow: none; border-color: #cbd5e1;">
                                <div class="panel-heading" style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1; padding: 12px 15px; display: flex; align-items: center; justify-content: space-between;">
                                    <strong style="color: #1e293b; font-size: 14px;">
                                        <i class="fa fa-user text-primary"></i> Usuários Específicos (<?php echo count($usuarios ?? []); ?>)
                                    </strong>
                                    <div>
                                        <button type="button" class="btn btn-xs btn-primary" id="btnMarcarTodosUsuarios">Marcar Todos</button>
                                        <button type="button" class="btn btn-xs btn-default" id="btnDesmarcarTodosUsuarios">Desmarcar</button>
                                    </div>
                                </div>
                                <div class="panel-body" style="padding: 15px;">
                                    <div style="margin-bottom: 12px;">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                            <input type="text" id="filtroBuscaUsuarios" class="form-control" placeholder="Filtrar por nome ou e-mail...">
                                        </div>
                                    </div>
                                    <div id="listaContainerUsuarios" style="max-height: 280px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; background: #fafafa;">
                                        <?php if (!empty($usuarios)): ?>
                                            <?php foreach ($usuarios as $u): ?>
                                                <?php 
                                                    $checadoU = in_array((int)$u->id, (array)$userIdsAtuais); 
                                                    $nomeCompleto = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
                                                    if (empty($nomeCompleto)) $nomeCompleto = $u->username ?? 'Usuário #' . $u->id;
                                                ?>
                                                <div class="item-checkbox-usuario" style="margin-bottom: 8px; padding: 6px 10px; border-radius: 4px; background: #ffffff; border: 1px solid #e2e8f0;">
                                                    <label style="cursor: pointer; width: 100%; margin: 0; display: flex; align-items: flex-start; gap: 8px;">
                                                        <input type="checkbox" name="usuarios[]" value="<?php echo $u->id; ?>" class="chk-usuario" <?php echo $checadoU ? 'checked' : ''; ?> style="width: 17px; height: 17px; margin-top: 2px; cursor: pointer;">
                                                        <div style="flex: 1;">
                                                            <div class="nome-usuario" style="color: #1e293b; font-weight: 700; font-size: 13.5px;">
                                                                <?php echo htmlspecialchars($nomeCompleto); ?>
                                                            </div>
                                                            <small class="text-muted email-usuario" style="font-size: 11.5px; display: block; line-height: 1.3;">
                                                                <?php echo htmlspecialchars($u->email); ?>
                                                            </small>
                                                        </div>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-muted text-center" style="margin: 15px 0;">Nenhum usuário ativo encontrado.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="alert alert-info" style="border-radius: 6px; font-size: 13px; margin-bottom: 0;">
                        <i class="fa fa-info-circle"></i> <strong>Regra de Acesso:</strong> 
                        Usuários pertencentes a qualquer um dos <em>grupos marcados</em> <strong>OU</strong> os <em>usuários específicos selecionados</em> terão acesso liberado a este treinamento.
                    </div>
                </div>

            </div>

            <div class="box-footer" style="padding: 16px 30px; background: #f8fafc; border-top: 1px solid #e2e8f0;">
                <div class="row">
                    <div class="col-sm-8 col-sm-offset-3">
                        <button type="submit" class="btn btn-primary" style="font-weight: 700; border-radius: 6px; padding: 9px 26px;">
                            <i class="fa fa-check"></i> Salvar Permissões de Acesso
                        </button>
                        <a href="<?php echo site_url('treinamentos_admin'); ?>" class="btn btn-default" style="border-radius: 6px; margin-left: 8px; padding: 9px 20px;">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </section>
</div>
