<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$total_usuarios = 0;
$sem_permissao = 0;
foreach ($groups as $grupo) {
    $total_usuarios += (int) $grupo->usuarios_ativos;
    if (!$grupo->telas) { $sem_permissao++; }
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
            <div class="col-md-4 col-sm-6"><div class="lista-kpi"><span>Grupos</span><strong><?php echo count($groups) ?></strong><small>nesta empresa</small></div></div>
            <div class="col-md-4 col-sm-6"><div class="lista-kpi"><span>Usuários ativos</span><strong class="text-green"><?php echo $total_usuarios ?></strong><small>somando os membros de cada grupo</small></div></div>
            <div class="col-md-4 col-sm-6"><div class="lista-kpi"><span>Sem permissão</span><strong class="<?php echo $sem_permissao ? 'text-yellow' : 'text-muted' ?>"><?php echo $sem_permissao ?></strong><small>grupos que não acessam nenhuma tela</small></div></div>
        </div>

        <div class="box">
            <div class="box-header with-border lista-toolbar">
                <?php if ($can_create) { ?>
                    <?php echo anchor('groups/create', '<i class="fa fa-plus"></i> ' . lang('groups_create'), array('class' => 'btn btn-primary')); ?>
                <?php } ?>
                <div class="input-group lista-busca">
                    <input type="search" id="lista-q" class="form-control" placeholder="Buscar grupo...">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table lista-tabela" id="lista-tabela">
                    <thead>
                        <tr>
                            <th>Grupo</th>
                            <th>Membros ativos</th>
                            <th>Permissões</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groups as $grupo) { ?>
                            <tr class="<?php echo $grupo->telas ? 'is-ativo' : 'is-inativo' ?>" data-busca="<?php echo html_escape(mb_strtolower($grupo->name . ' ' . $grupo->description, 'UTF-8')) ?>">
                                <td>
                                    <span class="label" style="background: <?php echo html_escape($grupo->bgcolor) ?>;"><?php echo html_escape($grupo->name) ?></span>
                                    <?php if ($grupo->papel) { ?><span class="lista-chip"><?php echo $grupo->papel ?></span><?php } ?>
                                    <?php if ($grupo->limited) { ?><span class="lista-chip" title="Os membros veem apenas os registros ligados a eles"><i class="fa fa-lock"></i> Limitado</span><?php } ?>
                                    <?php if ($grupo->description !== '') { ?><div class="lista-detalhe"><?php echo html_escape($grupo->description) ?></div><?php } ?>
                                </td>
                                <td>
                                    <?php echo (int) $grupo->usuarios_ativos ?>
                                    <?php if ($grupo->usuarios > $grupo->usuarios_ativos) { ?><span class="lista-detalhe">+ <?php echo (int) ($grupo->usuarios - $grupo->usuarios_ativos) ?> inativos</span><?php } ?>
                                </td>
                                <td>
                                    <?php if ($grupo->telas) { ?>
                                        <?php echo (int) $grupo->telas ?> tela<?php echo $grupo->telas == 1 ? '' : 's' ?>
                                        <span class="lista-detalhe">· <?php echo (int) $grupo->acoes ?> aç<?php echo $grupo->acoes == 1 ? 'ão' : 'ões' ?></span>
                                    <?php } else { ?>
                                        <span class="text-yellow">Nenhuma</span>
                                    <?php } ?>
                                </td>
                                <td class="lista-acoes">
                                    <?php if ($can_edit) { ?>
                                        <?php echo anchor('groups/edit/' . (int) $grupo->id, '<i class="fa fa-pencil"></i> Editar', array('class' => 'btn btn-default btn-sm')); ?>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="lista-vazio" id="lista-vazio" style="display:none;">Nenhum grupo encontrado.</div>
            </div>
        </div>
    </section>
</div>

<?php $this->load->view('admin/_partials/lista_filtro_local'); ?>
<?php $this->load->view('admin/_partials/lista_estilos'); ?>
