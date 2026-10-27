<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$moeda = function ($valor) {
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
};
$ativos = 0;
$liberados = 0;
foreach ($modulos as $modulo) {
    if ($modulo->status === 'S') { $ativos++; }
    $liberados += (int) $modulo->clientes;
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
            <div class="col-md-4 col-sm-6"><a href="#" class="lista-kpi js-status-kpi" data-status=""><span>Módulos</span><strong><?php echo count($modulos) ?></strong><small>em <?php echo count($produtos_filtro) ?> produtos</small></a></div>
            <div class="col-md-4 col-sm-6"><a href="#" class="lista-kpi js-status-kpi" data-status="S"><span>Ativos</span><strong class="text-green"><?php echo $ativos ?></strong><small>disponíveis nas habilitações</small></a></div>
            <div class="col-md-4 col-sm-6"><div class="lista-kpi"><span>Liberações vigentes</span><strong><?php echo number_format($liberados, 0, ',', '.') ?></strong><small>clientes com acesso, somando os módulos</small></div></div>
        </div>

        <div class="box">
            <div class="box-header with-border lista-toolbar">
                <?php if ($can_create) { ?>
                    <a href="<?php echo base_url('produtos_modulos/create' . ($produto_selecionado ? '?produto=' . $produto_selecionado : '')) ?>" class="btn btn-primary" id="btn-novo-modulo"><i class="fa fa-plus"></i> Criar módulo</a>
                <?php } ?>
                <div class="input-group lista-busca">
                    <input type="search" id="lista-q" class="form-control" placeholder="Buscar módulo...">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                </div>
                <select id="lista-filtro-produto" class="form-control lista-filtro">
                    <option value="">Todos os produtos</option>
                    <?php foreach ($produtos_filtro as $produto) { ?>
                        <option value="<?php echo (int) $produto->id ?>" <?php echo $produto_selecionado === (int) $produto->id ? 'selected' : '' ?>><?php echo html_escape($produto->nome) . ($produto->status === 'S' ? '' : ' (inativo)') ?></option>
                    <?php } ?>
                </select>
                <select id="lista-status" class="form-control lista-filtro">
                    <option value="">Todos os status</option>
                    <option value="S">Ativos</option>
                    <option value="N">Inativos</option>
                </select>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table lista-tabela" id="lista-tabela">
                    <thead>
                        <tr>
                            <th>Módulo</th>
                            <th class="text-right">Licença</th>
                            <th class="text-right">Manutenção</th>
                            <th class="text-right">Implantação</th>
                            <th>Clientes</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($modulos as $modulo) { $ativo = $modulo->status === 'S'; ?>
                            <tr class="<?php echo $ativo ? 'is-ativo' : 'is-inativo' ?>" data-status="<?php echo $modulo->status ?>" data-produto="<?php echo (int) $modulo->id_produto ?>" data-busca="<?php echo html_escape(mb_strtolower($modulo->nome . ' ' . $modulo->produto . ' ' . $modulo->descricao, 'UTF-8')) ?>">
                                <td>
                                    <div class="lista-nome"><?php echo html_escape($modulo->nome) ?></div>
                                    <div class="lista-detalhe"><?php echo html_escape($modulo->produto) ?></div>
                                </td>
                                <td class="text-right text-nowrap"><?php echo $moeda($modulo->valor_licenca) ?></td>
                                <td class="text-right text-nowrap"><?php echo $moeda($modulo->valor_manutencao) ?></td>
                                <td class="text-right text-nowrap"><?php echo $moeda($modulo->valor_implantacao) ?></td>
                                <td><?php echo (int) $modulo->clientes ?></td>
                                <td><?php echo $ativo ? '<span class="label label-success">Ativo</span>' : '<span class="label label-default">Inativo</span>' ?></td>
                                <td class="lista-acoes">
                                    <?php if ($can_edit) { echo anchor('produtos_modulos/edit/' . (int) $modulo->id, '<i class="fa fa-pencil"></i> Editar', array('class' => 'btn btn-default btn-sm')); } ?>
                                    <?php if ($can_status) { ?>
                                        <?php if ($ativo) { ?>
                                            <a href="<?php echo base_url('produtos_modulos/deactivate/' . (int) $modulo->id) ?>" class="btn btn-default btn-sm js-confirmar" data-confirmar="Inativar o módulo &quot;<?php echo html_escape($modulo->nome) ?>&quot;? Ele deixa de aparecer nas habilitações." title="Inativar"><i class="fa fa-ban"></i></a>
                                        <?php } else { ?>
                                            <a href="<?php echo base_url('produtos_modulos/activate/' . (int) $modulo->id) ?>" class="btn btn-default btn-sm" title="Ativar"><i class="fa fa-check"></i></a>
                                        <?php } ?>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="lista-vazio" id="lista-vazio" style="display:none;">Nenhum módulo encontrado.</div>
            </div>
        </div>
        <p class="text-muted small">"Clientes" conta os clientes com o módulo liberado ou em consulta numa habilitação vigente.</p>
    </section>
</div>

<script type="text/javascript">
    window.addEventListener('load', function() {
        // O botão "Criar módulo" já sugere o produto filtrado.
        $('#lista-filtro-produto').on('change', function() {
            var url = '<?php echo base_url('produtos_modulos/create') ?>';
            $('#btn-novo-modulo').attr('href', $(this).val() ? url + '?produto=' + $(this).val() : url);
        });
    });
</script>
<?php $this->load->view('admin/_partials/lista_filtro_local'); ?>
<?php $this->load->view('admin/_partials/lista_estilos'); ?>
