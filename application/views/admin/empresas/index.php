<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$filiais = 0;
$usuarios = 0;
foreach ($empresas as $empresa) {
    $filiais += (int) $empresa->filiais;
    $usuarios += (int) $empresa->usuarios;
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
            <div class="col-md-4 col-sm-6"><div class="lista-kpi"><span>Empresas</span><strong><?php echo count($empresas) ?></strong><small>cadastradas no sistema</small></div></div>
            <div class="col-md-4 col-sm-6"><div class="lista-kpi"><span>Filiais ativas</span><strong class="text-green"><?php echo number_format($filiais, 0, ',', '.') ?></strong><small>somando todas as empresas</small></div></div>
            <div class="col-md-4 col-sm-6"><div class="lista-kpi"><span>Usuários ativos</span><strong><?php echo number_format($usuarios, 0, ',', '.') ?></strong><small>vínculos usuário × empresa</small></div></div>
        </div>

        <div class="box">
            <div class="box-header with-border lista-toolbar">
                <?php if ($can_create) { echo anchor('empresas/create', '<i class="fa fa-plus"></i> ' . lang('empresas_create'), array('class' => 'btn btn-primary')); } ?>
                <div class="input-group lista-busca">
                    <input type="search" id="lista-q" class="form-control" placeholder="Buscar empresa...">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table lista-tabela" id="lista-tabela">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th class="text-right">Filiais ativas</th>
                            <th class="text-right">Usuários ativos</th>
                            <th class="text-right">Grupos</th>
                            <th class="text-right">Telas habilitadas</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($empresas as $empresa) { ?>
                            <tr class="<?php echo $empresa->menus > 0 ? 'is-ativo' : 'is-inativo' ?>" data-busca="<?php echo html_escape(mb_strtolower($empresa->nome, 'UTF-8')) ?>">
                                <td>
                                    <span class="lista-nome"><?php echo html_escape($empresa->nome) ?></span>
                                    <?php if ((int) $empresa->id === $empresa_atual) { ?><span class="label label-primary">Selecionada</span><?php } ?>
                                    <div class="lista-detalhe">#<?php echo (int) $empresa->id ?><?php echo $empresa->create_on ? ' · desde ' . date('m/Y', (int) $empresa->create_on) : '' ?></div>
                                </td>
                                <td class="text-right"><?php echo number_format((int) $empresa->filiais, 0, ',', '.') ?></td>
                                <td class="text-right"><?php echo number_format((int) $empresa->usuarios, 0, ',', '.') ?></td>
                                <td class="text-right"><?php echo (int) $empresa->grupos ?></td>
                                <td class="text-right"><?php echo $empresa->menus > 0 ? (int) $empresa->menus : '<span class="text-yellow">Nenhuma</span>' ?></td>
                                <td class="lista-acoes">
                                    <?php if ($can_edit) { echo anchor('empresas/edit/' . (int) $empresa->id, '<i class="fa fa-pencil"></i> Editar', array('class' => 'btn btn-default btn-sm')); } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="lista-vazio" id="lista-vazio" style="display:none;">Nenhuma empresa encontrada.</div>
            </div>
        </div>
    </section>
</div>

<?php $this->load->view('admin/_partials/lista_filtro_local'); ?>
<?php $this->load->view('admin/_partials/lista_estilos'); ?>
