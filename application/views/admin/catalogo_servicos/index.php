<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$horas = function ($valor) {
    $valor = (float) $valor;
    return rtrim(rtrim(number_format($valor, 2, ',', '.'), '0'), ',') . ' h';
};
$ativos = 0;
$usados = 0;
$chamados = 0;
$produtos = array();
foreach ($servicos as $servico) {
    if ($servico->actived == 1) { $ativos++; }
    if ($servico->chamados_90 > 0) { $usados++; }
    $chamados += (int) $servico->chamados_90;
    $produtos[$servico->produto_id] = $servico->produto;
}
asort($produtos);
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content lista-page">
        <?php if (isset($message)) { echo $message; } ?>

        <div class="row">
            <div class="col-md-3 col-sm-6"><a href="#" class="lista-kpi js-status-kpi" data-status=""><span>Serviços</span><strong><?php echo count($servicos) ?></strong><small>no catálogo</small></a></div>
            <div class="col-md-3 col-sm-6"><a href="#" class="lista-kpi js-status-kpi" data-status="1"><span>Ativos</span><strong class="text-green"><?php echo $ativos ?></strong><small>disponíveis na abertura de chamados</small></a></div>
            <div class="col-md-3 col-sm-6"><div class="lista-kpi"><span>Usados</span><strong><?php echo $usados ?></strong><small>com chamados nos últimos 90 dias</small></div></div>
            <div class="col-md-3 col-sm-6"><div class="lista-kpi"><span>Chamados</span><strong><?php echo number_format($chamados, 0, ',', '.') ?></strong><small>classificados no catálogo em 90 dias</small></div></div>
        </div>

        <div class="box">
            <div class="box-header with-border lista-toolbar">
                <?php if ($can_create) { echo anchor('catalogo_servicos/create', '<i class="fa fa-plus"></i> Criar serviço', array('class' => 'btn btn-primary')); } ?>
                <div class="input-group lista-busca">
                    <input type="search" id="lista-q" class="form-control" placeholder="Buscar serviço, módulo ou categoria...">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                </div>
                <select id="lista-filtro-produto" class="form-control lista-filtro">
                    <option value="">Todos os produtos</option>
                    <?php foreach ($produtos as $id => $nome) { ?><option value="<?php echo (int) $id ?>"><?php echo html_escape($nome) ?></option><?php } ?>
                </select>
                <select id="lista-filtro-nivel" class="form-control lista-filtro">
                    <option value="">Todos os níveis</option>
                    <?php foreach ($niveis as $nivel) { ?><option value="<?php echo (int) $nivel->id ?>"><?php echo html_escape($nivel->descricao) ?></option><?php } ?>
                </select>
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
                            <th>Serviço</th>
                            <th>Nível</th>
                            <th class="text-right">Tempo estimado</th>
                            <th class="text-right">Prazo</th>
                            <th class="text-right">Chamados (90 dias)</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servicos as $servico) { $ativo = $servico->actived == 1; ?>
                            <tr class="<?php echo $ativo ? 'is-ativo' : 'is-inativo' ?>" data-status="<?php echo $ativo ? 1 : 0 ?>" data-produto="<?php echo (int) $servico->produto_id ?>" data-nivel="<?php echo (int) $servico->nivel_id ?>" data-busca="<?php echo html_escape(mb_strtolower($servico->descricao . ' ' . $servico->produto . ' ' . $servico->modulo . ' ' . $servico->categoria, 'UTF-8')) ?>">
                                <td>
                                    <div class="lista-nome"><?php echo html_escape($servico->descricao) ?></div>
                                    <div class="lista-detalhe"><?php echo html_escape($servico->produto) ?> <i class="fa fa-angle-right"></i> <?php echo html_escape($servico->modulo) ?> <i class="fa fa-angle-right"></i> <?php echo html_escape($servico->categoria) ?></div>
                                </td>
                                <td><?php if ($servico->nivel) { ?><span class="label" style="background: <?php echo html_escape($servico->nivel_cor ? $servico->nivel_cor : '#777') ?>;"><?php echo html_escape($servico->nivel) ?></span><?php } else { ?><span class="text-muted">—</span><?php } ?></td>
                                <td class="text-right text-nowrap"><?php echo $horas($servico->tempo_padrao) ?></td>
                                <td class="text-right text-nowrap"><?php echo (int) $servico->prazo_padrao ?> h úteis</td>
                                <td class="text-right"><?php echo (int) $servico->chamados_90 ?: '<span class="text-muted">0</span>' ?></td>
                                <td><?php echo $ativo ? '<span class="label label-success">Ativo</span>' : '<span class="label label-default">Inativo</span>' ?></td>
                                <td class="lista-acoes">
                                    <?php if ($can_edit) { echo anchor('catalogo_servicos/edit/' . (int) $servico->id, '<i class="fa fa-pencil"></i> Editar', array('class' => 'btn btn-default btn-sm')); } ?>
                                    <?php if ($can_status) { ?>
                                        <?php if ($ativo) { ?>
                                            <a href="<?php echo base_url('catalogo_servicos/deactivate/' . (int) $servico->id) ?>" class="btn btn-default btn-sm js-confirmar" data-confirmar="Inativar o serviço &quot;<?php echo html_escape($servico->descricao) ?>&quot;? Ele deixa de aparecer na abertura de chamados." title="Inativar"><i class="fa fa-ban"></i></a>
                                        <?php } else { ?>
                                            <a href="<?php echo base_url('catalogo_servicos/activate/' . (int) $servico->id) ?>" class="btn btn-default btn-sm" title="Ativar"><i class="fa fa-check"></i></a>
                                        <?php } ?>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="lista-vazio" id="lista-vazio" style="display:none;">Nenhum serviço encontrado.</div>
            </div>
        </div>
        <p class="text-muted small">O tempo estimado vira as horas previstas do chamado; o prazo, em horas úteis, define a previsão de entrega.</p>
    </section>
</div>

<?php $this->load->view('admin/_partials/lista_filtro_local'); ?>
<?php $this->load->view('admin/_partials/lista_estilos'); ?>
