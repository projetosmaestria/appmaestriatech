<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-book text-primary"></i> Gerenciar Treinamentos
            <small>Catálogo e Administração de Treinamentos</small>
        </h1>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if (!empty($message)) echo $message; ?>

        <style>
            .card-full-height {
                min-height: calc(100vh - 210px);
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.04);
                display: flex;
                flex-direction: column;
            }
            .table-responsive-overflow {
                overflow: visible !important;
                min-height: 420px;
                padding-bottom: 100px;
                flex: 1;
            }
        </style>

        <div class="box box-primary card-full-height">
            <div class="box-header with-border" style="padding: 14px 18px;">
                <div style="display: flex; align-items: center; justify-content: flex-start; gap: 15px; flex-wrap: wrap;">
                    <!-- Botao Novo Treinamento na extrema esquerda -->
                    <a href="<?php echo site_url('treinamentos_admin/create'); ?>" class="btn btn-primary" style="border-radius: 6px; font-weight: 600; padding: 7px 18px;">
                        <i class="fa fa-plus"></i> Novo Treinamento
                    </a>

                    <!-- Filtro por Modulo ao lado do botao -->
                    <form id="form_filtro_modulo" method="get" action="<?php echo site_url('treinamentos_admin'); ?>" style="display: inline-flex; align-items: center; margin: 0;">
                        <span style="margin-right: 8px; font-weight: 600; color: #555; font-size: 13px;">Módulo:</span>
                        <select name="modulo_id" id="select_modulo_admin" class="form-control" onchange="this.form.submit()" style="width: 280px; height: 36px; border-radius: 6px; font-size: 14px; background-color: #fff; cursor: pointer;">
                            <option value="">Todos os Módulos</option>
                            <?php foreach ($modulos as $mod): ?>
                                <option value="<?php echo $mod->id; ?>" <?php echo ($modulo_selecionado == $mod->id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($mod->nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($modulo_selecionado)): ?>
                            <a href="<?php echo site_url('treinamentos_admin'); ?>" class="btn btn-sm btn-default" style="margin-left: 8px; border-radius: 4px; height: 36px; display: inline-flex; align-items: center;" title="Limpar Filtro">
                                <i class="fa fa-times text-danger"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="box-body table-responsive table-responsive-overflow">
                <table class="table table-hover table-striped" style="vertical-align: middle;">
                    <thead>
                        <tr style="background: #f8fafc; font-size: 13px; text-transform: uppercase; color: #4a5568;">
                            <th style="width: 50px;">Capa</th>
                            <th>Título</th>
                            <th>Módulo</th>
                            <th>Etapas</th>
                            <th>Alunos Ativos</th>
                            <th>Concluídos</th>
                            <th>Avaliação Média</th>
                            <th>Acesso</th>
                            <th>Situação</th>
                            <th class="text-right" style="width: 80px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($treinamentos)): ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted" style="padding: 35px;">
                                    <i class="fa fa-info-circle fa-2x" style="margin-bottom: 8px; display: block; color: #a0aec0;"></i>
                                    Nenhum treinamento cadastrado<?php echo !empty($modulo_selecionado) ? ' para este módulo' : ''; ?>.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($treinamentos as $t): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo base_url($t->imagem_capa); ?>" alt="" style="width: 44px; height: 32px; object-fit: cover; border-radius: 4px; border: 1px solid #edf2f7;">
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: #2d3748;"><?php echo htmlspecialchars($t->titulo); ?></div>
                                        <small class="text-muted"><i class="fa fa-clock-o"></i> <?php echo $t->carga_horaria_minutos; ?> min &bull; Nível <?php echo ucfirst($t->nivel); ?></small>
                                    </td>
                                    <td>
                                        <span class="label label-default" style="font-size: 11px;">
                                            <?php echo htmlspecialchars($t->modulo_nome ?? 'Geral'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo site_url('treinamentos_admin/etapas/' . $t->id); ?>" class="badge bg-blue" title="Clique para gerenciar as etapas">
                                            <?php echo $t->total_etapas; ?> etapa(s)
                                        </a>
                                    </td>
                                    <td><span class="label label-primary"><?php echo $t->total_em_andamento ?? 0; ?></span></td>
                                    <td><span class="label label-success"><?php echo $t->total_concluidos ?? 0; ?></span></td>
                                    <td>
                                        <i class="fa fa-star text-yellow"></i> <strong><?php echo number_format($t->media_avaliacao, 1); ?></strong>
                                        <small class="text-muted">(<?php echo $t->total_avaliacoes; ?>)</small>
                                    </td>
                                    <td>
                                        <span class="label <?php echo $t->tipo_acesso === 'livre' ? 'label-info' : 'label-warning'; ?>">
                                            <?php echo ucfirst($t->tipo_acesso); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($t->ativo == 1): ?>
                                            <span class="label label-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="label label-danger">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <!-- Botao de 3 pontinhos com Dropdown idêntico ao padrão Users -->
                                        <div class="btn-group btn-orders-all">
                                            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="padding: 4px 10px; border-radius: 4px;" title="Opções">
                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                <li>
                                                    <a href="<?php echo site_url('treinamentos_admin/etapas/' . $t->id); ?>">
                                                        <i class="fa fa-tasks text-blue"></i> Gerenciar Etapas
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo site_url('treinamentos_admin/permissoes/' . $t->id); ?>">
                                                        <i class="fa fa-users text-orange"></i> Liberar Acessos
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo site_url('treinamentos_admin/edit/' . $t->id); ?>">
                                                        <i class="fa fa-edit text-green"></i> Editar Treinamento
                                                    </a>
                                                </li>
                                                <li class="divider"></li>
                                                <li>
                                                    <a href="<?php echo site_url('treinamentos_admin/delete/' . $t->id); ?>" onclick="return confirm('Deseja realmente excluir/desativar este treinamento?');" style="color: #d9534f;">
                                                        <i class="fa fa-trash"></i> Excluir Treinamento
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
