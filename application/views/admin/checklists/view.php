<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Visualizar Checklist</h3>
                    </div>
                    
                    <div class="box-body">
                        <?php if(isset($message) && $message): ?>
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <h4><i class="icon fa fa-check"></i> Sucesso!</h4>
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Dados do Checklist -->
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">Informações do Checklist</h3>
                            </div>
                            <div class="box-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <th style="width: 200px;">ID</th>
                                        <td><?php echo $checklist->id; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Nome</th>
                                        <td><?php echo htmlspecialchars($checklist->nome); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Descrição</th>
                                        <td><?php echo nl2br(htmlspecialchars($checklist->descricao)); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <?php if($checklist->status == 1): ?>
                                                <span class="label label-success">Ativo</span>
                                            <?php else: ?>
                                                <span class="label label-danger">Inativo</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Data de Criação</th>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($checklist->data_criacao)); ?></td>
                                    </tr>
                                    <?php if($checklist->data_atualizacao): ?>
                                    <tr>
                                        <th>Data de Atualização</th>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($checklist->data_atualizacao)); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>

                        <!-- Etapas do Checklist -->
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Etapas do Checklist</h3>
                                
                                <!--  Total de dias do checklist -->
                                <?php if(isset($etapas) && !empty($etapas)): 
                                    $total_dias = 0;
                                    foreach($etapas as $etapa) {
                                        if(isset($etapa->prazo_padrao) && $etapa->prazo_padrao > 0) {
                                            $total_dias += $etapa->prazo_padrao;
                                        }
                                    }
                                ?>
                                    <?php if($total_dias > 0): ?>
                                        <div class="box-tools box-title pull-right">
                                            <span class="label bg-green" style="font-size: 14px; padding: 5px 10px;">
                                                <i class="fa fa-calendar"></i> Total: <?php echo $total_dias; ?> dia(s)
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div class="box-body">
                                <?php if(isset($etapas) && !empty($etapas)): ?>
                                    <div class="row">
                                        <?php foreach($etapas as $index => $etapa): ?>
                                            <div class="col-md-6">
                                                <div class="box box-default">
                                                    <div class="box-header with-border">
                                                        <h4 class="box-title">Etapa <?php echo $index + 1; ?></h4>
                                                    </div>
                                                    <div class="box-body">
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <th style="width: 150px;">ID</th>
                                                                <td><?php echo $etapa->id; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Nome</th>
                                                                <td><?php echo htmlspecialchars($etapa->nome); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Descrição</th>
                                                                <td><?php echo nl2br(htmlspecialchars($etapa->descricao)); ?></td>
                                                            </tr>
                                                            
                                                            <!--  Linha do Prazo Padrão -->
                                                            <tr>
                                                                <th>Prazo Padrão</th>
                                                                <td style="font-size: 14px;">
                                                                    <?php if(isset($etapa->prazo_padrao) && $etapa->prazo_padrao !== null && $etapa->prazo_padrao !== ''): ?>
                                                                        <?php if($etapa->prazo_padrao > 0): ?>
                                                                            <span class="label label-primary">
                                                                                <i class="fa fa-calendar-check-o"></i> <?php echo $etapa->prazo_padrao; ?> dia(s)
                                                                            </span>
                                                                        <?php else: ?>
                                                                            <span class="label label-default">
                                                                                <i class="fa fa-calendar"></i> 0 dias (imediato)
                                                                            </span>
                                                                        <?php endif; ?>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">Não definido</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <th>Status</th>
                                                                <td>
                                                                    <?php if($etapa->status == 1): ?>
                                                                        <span class="label label-success">Ativo</span>
                                                                    <?php else: ?>
                                                                        <span class="label label-danger">Inativo</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Ordem</th>
                                                                <td><?php echo $etapa->ordem + 1; ?></td>
                                                            </tr>
                                                            
                                                            <?php if(isset($etapa->data_criacao) && $etapa->data_criacao): ?>
                                                            <tr>
                                                                <th>Data de Criação</th>
                                                                <td><?php echo date('d/m/Y H:i:s', strtotime($etapa->data_criacao)); ?></td>
                                                            </tr>
                                                            <?php endif; ?>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <!--  Resumo dos prazos -->
                                    <?php 
                                    $etapas_com_prazo = array_filter($etapas, function($e) {
                                        return isset($e->prazo_prazao) && $e->prazo_padrao > 0;
                                    });
                                    
                                    if(count($etapas_com_prazo) > 0): 
                                    ?>
                                    <div class="box box-info">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">Resumo de Prazos</h3>
                                        </div>
                                        <div class="box-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="small-box bg-green">
                                                        <div class="inner">
                                                            <h3><?php echo count($etapas_com_prazo); ?></h3>
                                                            <p>Etapas com prazo</p>
                                                        </div>
                                                        <div class="icon">
                                                            <i class="fa fa-tasks"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="small-box bg-blue">
                                                        <div class="inner">
                                                            <h3><?php echo $total_dias; ?></h3>
                                                            <p>Total de dias</p>
                                                        </div>
                                                        <div class="icon">
                                                            <i class="fa fa-calendar"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="small-box bg-yellow">
                                                        <div class="inner">
                                                            <h3><?php echo count($etapas); ?></h3>
                                                            <p>Total de etapas</p>
                                                        </div>
                                                        <div class="icon">
                                                            <i class="fa fa-list-ol"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="icon fa fa-info"></i> Este checklist não possui etapas cadastradas.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <a href="<?php echo site_url('checklists/edit/' . $checklist->id); ?>" class="btn btn-primary">
                            <i class="fa fa-edit"></i> Editar
                        </a>
                        <a href="<?php echo site_url('checklists'); ?>" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.table-bordered th {
    background-color: #f5f5f5;
}
.box-default {
    margin-bottom: 20px;
}
.small-box {
    border-radius: 3px;
    position: relative;
    display: block;
    margin-bottom: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
}
.small-box > .inner {
    padding: 10px;
}
.small-box h3 {
    font-size: 38px;
    font-weight: bold;
    margin: 0 0 10px 0;
    white-space: nowrap;
    padding: 0;
}
.small-box p {
    font-size: 15px;
}
.small-box .icon {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 0;
    font-size: 70px;
    color: rgba(0,0,0,0.15);
}
.bg-green {
    background-color: #00a65a !important;
    color: #fff !important;
}
.bg-blue {
    background-color: #0073b7 !important;
    color: #fff !important;
}
.bg-yellow {
    background-color: #f39c12 !important;
    color: #fff !important;
}
.label-primary {
    background-color: #337ab7;
}
.label-default {
    background-color: #777;
}
.badge.bg-blue {
    background-color: #0073b7;
    color: white;
    padding: 3px 7px;
    border-radius: 3px;
}
</style>