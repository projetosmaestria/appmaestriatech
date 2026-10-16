<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <div class="dataTables_wrapper">
            <div class="row">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <?php echo anchor('checklists/create', '<i class="fa fa-plus"></i> ' . lang('checklists_new'), array('class' => 'btn btn-block btn-primary btn-flat')); ?>
                            </h3>
                            <div class="box-tools pull-right">
                                <form id="form-checklists-q" action="" method="get">
                                    <div class="input-group input-group-sm" style="width: 250px;">
                                        <input type="text" name="q" class="form-control pull-right" placeholder="<?php echo lang('checklists_search'); ?>" value="<?php echo isset($q) ? $q : ''; ?>">
                                        <div class="input-group-btn">
                                            <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="box-body">
                            <?php if(isset($message)): ?>
                                <div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <h4><i class="icon fa fa-check"></i> Sucesso!</h4>
                                    <?php echo $message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if(isset($error)): ?>
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <h4><i class="icon fa fa-ban"></i> Erro!</h4>
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (empty($checklists)): ?>
                                <div class="alert alert-info">
                                    <h4><i class="icon fa fa-info"></i> Informação</h4>
                                    <?php echo lang('checklists_no_records'); ?>
                                </div>
                            <?php else: ?>
                                <table id="checklists-datatable" class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th width="50">ID</th>
                                            <th><?php echo lang('checklists_name'); ?></th>
                                            <th><?php echo lang('checklists_description'); ?></th>
                                            <th width="100" class="text-center">Etapas</th>
                                            <th width="150"><?php echo lang('checklists_created_at'); ?></th>
                                            <th width="150"><?php echo lang('checklists_actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($checklists as $checklist): ?>
                                            <tr>
                                                <td><?php echo $checklist->id; ?></td>
                                                <td><?php echo htmlspecialchars($checklist->nome, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars(substr($checklist->descricao, 0, 100), ENT_QUOTES, 'UTF-8') . (strlen($checklist->descricao) > 100 ? '...' : ''); ?></td>
                                                
                                                <!-- NOVA COLUNA: Contagem de etapas -->
                                                <td class="text-center">
                                                    <?php 
                                                    // Carrega o model de etapas se não estiver carregado
                                                    if (!isset($this->checklists_etapas_model)) {
                                                        $this->load->model('checklists_etapas_model');
                                                    }
                                                    
                                                    // Conta as etapas ativas deste checklist
                                                    $total_etapas = $this->checklists_etapas_model->count_by_checklist($checklist->id);
                                                    
                                                    if ($total_etapas > 0):
                                                    ?>
                                                        <span class="badge bg-green">
                                                            <?php echo $total_etapas; ?> etapa(s)
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-gray">
                                                            0 etapas
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <td><?php echo date('d/m/Y H:i', strtotime($checklist->data_criacao)); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-xs">
                                                        <a href="<?php echo site_url('checklists/view/' . $checklist->id); ?>" class="btn btn-info" title="<?php echo lang('checklists_view'); ?>">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                        <a href="<?php echo site_url('checklists/edit/' . $checklist->id); ?>" class="btn btn-primary" title="<?php echo lang('checklists_edit'); ?>">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                        <a href="<?php echo site_url('checklists/delete/' . $checklist->id); ?>" class="btn btn-danger" onclick="return confirm('<?php echo lang('checklists_delete_confirm'); ?>')" title="<?php echo lang('checklists_delete'); ?>">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (isset($pagination) && $pagination): ?>
                        <div class="box-footer clearfix">
                            <div class="row">
                                <div class="col-sm-12">
                                    <?php echo $pagination; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.clickable-row:hover {
    background-color: #f5f5f5;
}
.btn-group-xs > .btn {
    padding: 3px 8px;
    font-size: 12px;
    line-height: 1.5;
}

/* Estilos para os badges de etapas */
.badge.bg-green {
    background-color: #00a65a;
    color: white;
    padding: 5px 10px;
    font-size: 12px;
    font-weight: normal;
    border-radius: 3px;
}

.badge.bg-gray {
    background-color: #d2d6de;
    color: #333;
    padding: 5px 10px;
    font-size: 12px;
    font-weight: normal;
    border-radius: 3px;
}

/* Centralizar conteúdo da coluna */
.text-center {
    text-align: center;
    vertical-align: middle;
}
</style>