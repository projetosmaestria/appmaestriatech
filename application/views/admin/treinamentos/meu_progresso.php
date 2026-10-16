<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-user-circle text-primary"></i> Meus Treinamentos
            <small>Acompanhamento de Aprendizado e Conclusões</small>
        </h1>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if (!empty($message)) echo $message; ?>

        <!-- Cards de Resumo -->
        <div class="row">
            <div class="col-md-4 col-sm-6">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-check-circle-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Treinamentos Concluídos</span>
                        <span class="info-box-number" style="font-size: 26px;"><?php echo $resumo['concluidos']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-hourglass-half"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Em Andamento</span>
                        <span class="info-box-number" style="font-size: 26px;"><?php echo $resumo['em_andamento']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-pie-chart"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Média Geral de Conclusão</span>
                        <span class="info-box-number" style="font-size: 26px;"><?php echo $resumo['percentual_geral']; ?>%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Histórico -->
        <div class="box box-primary" style="border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.04);">
            <div class="box-header with-border" style="padding: 16px 20px;">
                <div class="pull-left" style="padding-top: 4px;">
                    <h3 class="box-title" style="margin: 0; font-weight: 700; font-size: 17px; color: #2c3b41;">
                        <i class="fa fa-history text-primary" style="margin-right: 6px;"></i> Histórico Detalhado de Cursos
                    </h3>
                </div>
                <div class="pull-right">
                    <a href="<?php echo site_url('treinamentos'); ?>" class="btn btn-sm btn-primary" style="border-radius: 6px; font-weight: 600; padding: 7px 18px;">
                        <i class="fa fa-search" style="margin-right: 5px;"></i> Explorar Novos Treinamentos
                    </a>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="box-body table-responsive">
                <?php if (empty($historico)): ?>
                    <div class="text-center" style="padding: 40px;">
                        <i class="fa fa-folder-open-o fa-3x text-muted" style="margin-bottom: 12px;"></i>
                        <h4 style="color: #666;">Você ainda não iniciou nenhum treinamento.</h4>
                        <p style="color: #888;">Navegue pelo catálogo e comece sua jornada de capacitação!</p>
                        <a href="<?php echo site_url('treinamentos'); ?>" class="btn btn-primary" style="border-radius: 20px; font-weight: 600; margin-top: 10px;">
                            Ir para o Catálogo de Treinamentos
                        </a>
                    </div>
                <?php else: ?>
                    <table class="table table-hover table-striped" style="vertical-align: middle;">
                        <thead>
                            <tr style="background: #f8fafc; font-size: 13px; text-transform: uppercase; color: #4a5568;">
                                <th>Treinamento</th>
                                <th>Módulo</th>
                                <th>Nível</th>
                                <th>Progresso</th>
                                <th>Situação</th>
                                <th>Início</th>
                                <th>Conclusão</th>
                                <th class="text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historico as $h): ?>
                                <tr>
                                    <td style="font-weight: 700; color: #2d3748;">
                                        <?php echo htmlspecialchars($h->titulo); ?>
                                    </td>
                                    <td>
                                        <span class="label label-default" style="font-size: 11px;">
                                            <?php echo htmlspecialchars($h->modulo_nome ?? 'Geral'); ?>
                                        </span>
                                    </td>
                                    <td><span class="text-capitalize"><?php echo $h->nivel; ?></span></td>
                                    <td style="min-width: 160px;">
                                        <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 3px;">
                                            <span><?php echo $h->etapas_concluidas; ?>/<?php echo $h->total_etapas; ?> etapas</span>
                                            <strong><?php echo (float)$h->percentual; ?>%</strong>
                                        </div>
                                        <div class="progress" style="height: 6px; margin: 0; background: #eee;">
                                            <div class="progress-bar <?php echo $h->status === 'concluido' ? 'progress-bar-success' : 'progress-bar-primary'; ?>" style="width: <?php echo (float)$h->percentual; ?>%;"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($h->status === 'concluido'): ?>
                                            <span class="label label-success"><i class="fa fa-check"></i> Concluído</span>
                                        <?php else: ?>
                                            <span class="label label-primary"><i class="fa fa-spinner fa-spin"></i> Em Andamento</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo !empty($h->data_inicio) ? date('d/m/Y H:i', strtotime($h->data_inicio)) : '-'; ?></td>
                                    <td><?php echo !empty($h->data_conclusao) ? date('d/m/Y H:i', strtotime($h->data_conclusao)) : '-'; ?></td>
                                    <td class="text-right">
                                        <a href="<?php echo site_url('treinamentos/ver/' . $h->treinamento_id); ?>" class="btn btn-xs btn-default" style="border-radius: 4px;">
                                            <?php echo $h->status === 'concluido' ? 'Rever Curso' : 'Continuar Curso'; ?> <i class="fa fa-arrow-right"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>
