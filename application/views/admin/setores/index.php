<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$ativos = 0;
$usuarios = 0;
foreach ($setores as $setor) {
    if ($setor->actived == 1) { $ativos++; }
    $usuarios += (int) $setor->usuarios;
}
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content lista-page">
        <?php if (isset($message)) { echo $message; } ?>

        <div class="row">
            <div class="col-md-4 col-sm-6"><a href="#" class="lista-kpi js-status-kpi" data-status=""><span>Setores</span><strong><?php echo count($setores) ?></strong><small>nesta empresa</small></a></div>
            <div class="col-md-4 col-sm-6"><a href="#" class="lista-kpi js-status-kpi" data-status="1"><span>Ativos</span><strong class="text-green"><?php echo $ativos ?></strong><small>disponíveis no cadastro de usuários</small></a></div>
            <div class="col-md-4 col-sm-6"><div class="lista-kpi"><span>Usuários vinculados</span><strong><?php echo $usuarios ?></strong><small>usuários ativos com setor</small></div></div>
        </div>

        <div class="box">
            <div class="box-header with-border lista-toolbar">
                <?php if ($can_create) { ?>
                    <?php echo anchor('setores/create', '<i class="fa fa-plus"></i> ' . lang('setores_create'), array('class' => 'btn btn-primary')); ?>
                <?php } ?>
                <div class="input-group lista-busca">
                    <input type="search" id="lista-q" class="form-control" placeholder="Buscar setor...">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                </div>
                <select id="lista-status" class="form-control lista-filtro">
                    <option value="">Todos os status</option>
                    <option value="1">Ativos</option>
                    <option value="0">Inativos</option>
                </select>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table lista-tabela" id="lista-tabela">
                    <thead>
                        <tr>
                            <th>Setor</th>
                            <th>Usuários ativos</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($setores as $setor) { $ativo = $setor->actived == 1; ?>
                            <tr class="<?php echo $ativo ? 'is-ativo' : 'is-inativo' ?>" data-status="<?php echo $ativo ? 1 : 0 ?>" data-busca="<?php echo html_escape(mb_strtolower($setor->descricao, 'UTF-8')) ?>">
                                <td><span class="lista-nome"><?php echo html_escape($setor->descricao) ?></span></td>
                                <td><?php echo (int) $setor->usuarios ?></td>
                                <td><?php echo $ativo ? '<span class="label label-success">Ativo</span>' : '<span class="label label-default">Inativo</span>' ?></td>
                                <td class="lista-acoes">
                                    <?php if ($can_edit) { echo anchor('setores/edit/' . (int) $setor->id, '<i class="fa fa-pencil"></i> Editar', array('class' => 'btn btn-default btn-sm')); } ?>
                                    <?php if ($can_status) { ?>
                                        <?php if ($ativo) { ?>
                                            <a href="<?php echo base_url('setores/deactivate/' . (int) $setor->id) ?>" class="btn btn-default btn-sm js-confirmar" data-confirmar="Desativar o setor &quot;<?php echo html_escape($setor->descricao) ?>&quot;? Ele deixa de aparecer no cadastro de usuários." title="Desativar"><i class="fa fa-ban"></i></a>
                                        <?php } else { ?>
                                            <a href="<?php echo base_url('setores/activate/' . (int) $setor->id) ?>" class="btn btn-default btn-sm" title="Ativar"><i class="fa fa-check"></i></a>
                                        <?php } ?>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="lista-vazio" id="lista-vazio" style="<?php echo $setores ? 'display:none;' : '' ?>">Nenhum setor encontrado.</div>
            </div>
        </div>
    </section>
</div>

<?php $this->load->view('admin/_partials/lista_filtro_local'); ?>
<?php $this->load->view('admin/_partials/lista_estilos'); ?>
