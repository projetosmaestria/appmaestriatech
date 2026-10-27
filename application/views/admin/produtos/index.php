<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$ativos = 0;
$clientes = 0;
$chamados = 0;
$aplicacoes = array();
foreach ($produtos as $produto) {
    if ($produto->status === 'S') { $ativos++; }
    $clientes += (int) $produto->clientes;
    $chamados += (int) $produto->chamados_90;
    if ($produto->aplicacao !== '') { $aplicacoes[$produto->aplicacao] = true; }
}
ksort($aplicacoes);
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content lista-page">
        <?php if (isset($message)) { echo $message; } ?>

        <div class="row">
            <div class="col-md-3 col-sm-6"><a href="#" class="lista-kpi js-status-kpi" data-status=""><span>Produtos</span><strong><?php echo count($produtos) ?></strong><small>cadastrados</small></a></div>
            <div class="col-md-3 col-sm-6"><a href="#" class="lista-kpi js-status-kpi" data-status="S"><span>Ativos</span><strong class="text-green"><?php echo $ativos ?></strong><small>disponíveis para habilitar e abrir chamados</small></a></div>
            <div class="col-md-3 col-sm-6"><div class="lista-kpi"><span>Habilitações vigentes</span><strong><?php echo number_format($clientes, 0, ',', '.') ?></strong><small>clientes usando, somando os produtos</small></div></div>
            <div class="col-md-3 col-sm-6"><div class="lista-kpi"><span>Chamados</span><strong><?php echo number_format($chamados, 0, ',', '.') ?></strong><small>abertos nos últimos 90 dias</small></div></div>
        </div>

        <div class="box">
            <div class="box-header with-border lista-toolbar">
                <?php if ($can_create) { echo anchor('produtos/create', '<i class="fa fa-plus"></i> ' . lang('produtos_create'), array('class' => 'btn btn-primary')); } ?>
                <div class="input-group lista-busca">
                    <input type="search" id="lista-q" class="form-control" placeholder="Buscar produto...">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                </div>
                <select id="lista-status" class="form-control lista-filtro">
                    <option value="">Todos os status</option>
                    <option value="S">Ativos</option>
                    <option value="N">Inativos</option>
                </select>
                <?php if (count($aplicacoes) > 1) { ?>
                    <select id="lista-filtro-aplicacao" class="form-control lista-filtro">
                        <option value="">Todas as aplicações</option>
                        <?php foreach (array_keys($aplicacoes) as $aplicacao) { ?>
                            <option value="<?php echo html_escape(md5($aplicacao)) ?>"><?php echo html_escape($aplicacao) ?></option>
                        <?php } ?>
                    </select>
                <?php } ?>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table lista-tabela" id="lista-tabela">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Versão</th>
                            <th>Módulos</th>
                            <th>Clientes</th>
                            <th>Chamados (90 dias)</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $produto) { $ativo = $produto->status === 'S'; ?>
                            <tr class="<?php echo $ativo ? 'is-ativo' : 'is-inativo' ?>" data-status="<?php echo $produto->status ?>" data-aplicacao="<?php echo html_escape(md5($produto->aplicacao)) ?>" data-busca="<?php echo html_escape(mb_strtolower($produto->nome . ' ' . $produto->aplicacao . ' ' . $produto->descricao, 'UTF-8')) ?>">
                                <td>
                                    <div class="lista-nome"><?php echo html_escape($produto->nome) ?></div>
                                    <div class="lista-detalhe"><?php echo html_escape($produto->aplicacao) ?></div>
                                </td>
                                <td><?php echo $produto->versao !== null && $produto->versao !== '' ? html_escape($produto->versao) : '<span class="text-muted">—</span>' ?></td>
                                <td>
                                    <?php if ($can_modulos) { ?>
                                        <a href="<?php echo base_url('produtos_modulos?produto=' . (int) $produto->id) ?>"><?php echo (int) $produto->modulos ?></a>
                                    <?php } else { echo (int) $produto->modulos; } ?>
                                </td>
                                <td><?php echo number_format((int) $produto->clientes, 0, ',', '.') ?></td>
                                <td><?php echo number_format((int) $produto->chamados_90, 0, ',', '.') ?></td>
                                <td><?php echo $ativo ? '<span class="label label-success">Ativo</span>' : '<span class="label label-default">Inativo</span>' ?></td>
                                <td class="lista-acoes">
                                    <?php if ($can_edit) { echo anchor('produtos/edit/' . (int) $produto->id, '<i class="fa fa-pencil"></i> Editar', array('class' => 'btn btn-default btn-sm')); } ?>
                                    <?php if ($can_status) { ?>
                                        <?php if ($ativo) { ?>
                                            <a href="<?php echo base_url('produtos/deactivate/' . (int) $produto->id) ?>" class="btn btn-default btn-sm js-confirmar" data-confirmar="Inativar o produto &quot;<?php echo html_escape($produto->nome) ?>&quot;? Ele deixa de aparecer para novas habilitações e chamados." title="Inativar"><i class="fa fa-ban"></i></a>
                                        <?php } else { ?>
                                            <a href="<?php echo base_url('produtos/activate/' . (int) $produto->id) ?>" class="btn btn-default btn-sm" title="Ativar"><i class="fa fa-check"></i></a>
                                        <?php } ?>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="lista-vazio" id="lista-vazio" style="display:none;">Nenhum produto encontrado.</div>
            </div>
        </div>
    </section>
</div>

<?php $this->load->view('admin/_partials/lista_filtro_local'); ?>
<?php $this->load->view('admin/_partials/lista_estilos'); ?>
