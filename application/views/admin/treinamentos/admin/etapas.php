<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-tasks text-primary"></i> Etapas do Treinamento
            <small><?php echo htmlspecialchars($treinamento->titulo); ?></small>
        </h1>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if (!empty($message)) echo $message; ?>

        <div style="background: #fff; border-radius: 10px; padding: 18px 24px; margin-bottom: 22px; border: 1px solid #eef2f5; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
            <div>
                <h4 style="margin: 0 0 4px 0; font-weight: 700; color: #2c3b41;">
                    <?php echo htmlspecialchars($treinamento->titulo); ?>
                </h4>
                <p style="margin: 0; color: #718096; font-size: 13px;">
                    Módulo: <strong><?php echo htmlspecialchars($treinamento->modulo_nome ?? 'Sistema'); ?></strong> &bull;
                    Carga Horária: <strong><?php echo $treinamento->carga_horaria_minutos; ?> min</strong> &bull;
                    Total: <strong><?php echo count($etapas); ?> etapa(s)</strong>
                </p>
            </div>

            <div style="margin-top: 8px;">
                <a href="<?php echo site_url('treinamentos_admin/etapa_create/' . $treinamento->id); ?>" class="btn btn-primary" style="border-radius: 6px; font-weight: 600;">
                    <i class="fa fa-plus"></i> Adicionar Nova Etapa
                </a>
                <a href="<?php echo site_url('treinamentos_admin'); ?>" class="btn btn-default" style="border-radius: 6px; margin-left: 6px;">
                    <i class="fa fa-arrow-left"></i> Voltar aos Treinamentos
                </a>
            </div>
        </div>

        <style>
            .btn-orders-all .dropdown-menu {
                min-width: 165px;
                border-radius: 6px;
                box-shadow: 0 6px 18px rgba(0,0,0,0.15);
                border: 1px solid #e2e8f0;
                z-index: 9999;
            }
            .btn-orders-all .dropdown-menu > li > a {
                padding: 8px 16px;
                font-size: 13px;
                color: #4a5568;
            }
            .btn-orders-all .dropdown-menu > li > a:hover {
                background-color: #f7fafc;
                color: #2b6cb0;
            }
            .card-full-height {
                min-height: calc(100vh - 250px);
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.04);
                display: flex;
                flex-direction: column;
            }
            .table-responsive-overflow {
                overflow: visible !important;
                min-height: 400px;
                padding-bottom: 90px;
                flex: 1;
            }
        </style>

        <div class="box box-primary card-full-height">
            <div class="box-header with-border" style="padding: 14px 20px;">
                <h3 class="box-title"><i class="fa fa-list-ol text-primary"></i> Sequência Ordenada das Etapas</h3>
            </div>
            <div class="box-body table-responsive table-responsive-overflow">
                <table class="table table-hover table-striped" style="vertical-align: middle;">
                    <thead>
                        <tr style="background: #f8fafc; font-size: 13px; text-transform: uppercase; color: #4a5568;">
                            <th style="width: 70px;" class="text-center">Ordem</th>
                            <th>Título da Etapa</th>
                            <th>Vídeo</th>
                            <th>Materiais de Apoio</th>
                            <th>Atividades Práticas</th>
                            <th>Situação</th>
                            <th class="text-right" style="width: 80px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($etapas)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted" style="padding: 40px;">
                                    <i class="fa fa-info-circle fa-2x" style="margin-bottom: 10px;"></i>
                                    <h4>Nenhuma etapa cadastrada ainda para este treinamento.</h4>
                                    <p>Clique no botão acima para adicionar a primeira etapa.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($etapas as $ep): ?>
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-navy" style="font-size: 13px; padding: 5px 10px; border-radius: 50%;">
                                            <?php echo $ep->ordem; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: #2d3748; font-size: 14px;">
                                            <?php echo htmlspecialchars($ep->titulo); ?>
                                        </div>
                                        <?php if (!empty($ep->descricao_curta)): ?>
                                            <small class="text-muted"><?php echo htmlspecialchars($ep->descricao_curta); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($ep->video_tipo !== 'nenhum' && !empty($ep->video_url)): ?>
                                            <span class="label label-primary"><i class="fa fa-play-circle"></i> <?php echo ucfirst($ep->video_tipo); ?></span>
                                            <?php if ($ep->video_obrigatorio == 1): ?>
                                                <span class="label label-warning" title="Vídeo obrigatório para conclusão"><i class="fa fa-lock"></i> Obrigatório</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fa fa-minus"></i> Sem vídeo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-gray">
                                            <i class="fa fa-paperclip"></i> <?php echo $ep->total_arquivos ?? 0; ?> arquivo(s)
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-gray">
                                            <i class="fa fa-check-square-o"></i> <?php echo $ep->total_atividades ?? 0; ?> tarefa(s)
                                        </span>
                                        <?php if (($ep->total_atividades_obrigatorias ?? 0) > 0): ?>
                                            <span class="label label-danger"><?php echo $ep->total_atividades_obrigatorias; ?> obrigatória(s)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($ep->ativo == 1): ?>
                                            <span class="label label-success">Ativa</span>
                                        <?php else: ?>
                                            <span class="label label-danger">Inativa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <!-- Dropdown de 3 pontinhos idêntico ao padrão Users -->
                                        <div class="btn-group btn-orders-all">
                                            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="padding: 4px 10px; border-radius: 4px;" title="Opções">
                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                <li>
                                                    <a href="<?php echo site_url('treinamentos_admin/etapa_edit/' . $ep->id); ?>">
                                                        <i class="fa fa-pencil text-primary"></i> Editar Etapa
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo site_url('treinamentos/etapa/' . $treinamento->id . '/' . $ep->id); ?>" target="_blank">
                                                        <i class="fa fa-eye text-info"></i> Visualizar Aluno
                                                    </a>
                                                </li>
                                                <li class="divider"></li>
                                                <li>
                                                    <a href="<?php echo site_url('treinamentos_admin/etapa_delete/' . $ep->id); ?>" onclick="return confirm('Deseja realmente excluir esta etapa?');" style="color: #d9534f;">
                                                        <i class="fa fa-trash"></i> Excluir Etapa
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
