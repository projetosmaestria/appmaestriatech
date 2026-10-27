<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$numero = function ($valor) {
    return number_format((int) $valor, 0, ',', '.');
};
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content habil-page">
        <?php if (isset($message)) { echo $message; } ?>

        <div class="row">
            <div class="col-md-3 col-sm-6"><a href="#" class="habil-kpi js-situacao-kpi" data-situacao="vigente"><span>Clientes atendidos</span><strong class="text-green"><?php echo $numero($indicadores->clientes_vigentes) ?></strong><small><?php echo $numero($indicadores->vigentes) ?> habilitações vigentes</small></a></div>
            <div class="col-md-3 col-sm-6"><a href="#" class="habil-kpi js-situacao-kpi" data-situacao="vencendo" data-ordem="vencimento"><span>Vencendo</span><strong class="text-yellow"><?php echo $numero($indicadores->vencendo) ?></strong><small>nos próximos <?php echo (int) $dias_alerta ?> dias</small></a></div>
            <div class="col-md-3 col-sm-6"><a href="#" class="habil-kpi js-situacao-kpi" data-situacao="vencida"><span>Vencidas</span><strong class="text-red"><?php echo $numero($indicadores->vencidas) ?></strong><small>ativas com período encerrado</small></a></div>
            <div class="col-md-3 col-sm-6"><div class="habil-kpi"><span>Demonstrações</span><strong class="text-light-blue"><?php echo $numero($indicadores->demonstracoes) ?></strong><small>em andamento</small></div></div>
        </div>

        <div class="box">
            <div class="box-header with-border habil-toolbar">
                <div class="input-group habil-busca">
                    <input type="search" id="habil-q" class="form-control" placeholder="Buscar por nome, razão social ou CNPJ/CPF...">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                </div>
                <select id="habil-situacao" class="form-control habil-filtro" title="Situação">
                    <option value="">Todos os clientes</option>
                    <option value="vigente">Com produto vigente</option>
                    <option value="vencendo">Vencendo em <?php echo (int) $dias_alerta ?> dias</option>
                    <option value="vencida">Com habilitação vencida</option>
                    <option value="sem">Sem produto vigente</option>
                </select>
                <select id="habil-produto" class="form-control habil-filtro" title="Produto vigente">
                    <option value="">Todos os produtos</option>
                    <?php foreach ($produtos_filtro as $produto) { ?>
                        <option value="<?php echo (int) $produto->id ?>"><?php echo html_escape($produto->nome) ?></option>
                    <?php } ?>
                </select>
                <button type="button" class="btn btn-link" id="habil-limpar"><i class="fa fa-times"></i> Limpar</button>
            </div>
            <div class="box-body table-responsive no-padding">
                <table id="habil-tabela" class="table table-hover habil-tabela" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Produtos vigentes</th>
                            <th>Próximo vencimento</th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <p class="text-muted habil-legenda">
            <span class="habil-chip">Produto</span> vigente
            <span class="habil-chip is-vencendo">Produto</span> vence em até <?php echo (int) $dias_alerta ?> dias
            · passe o mouse sobre o produto para ver a data de término.
        </p>
    </section>
</div>

<?php $this->load->view('admin/habilitacoes/_modal_produtos'); ?>

<script type="text/javascript">
    window.addEventListener('load', function() {
        var tabela = $('#habil-tabela').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?php echo base_url('habilitacoes/server_side') ?>',
                type: 'post',
                dataType: 'json',
                data: function(d) {
                    d.q = $('#habil-q').val();
                    d.situacao = $('#habil-situacao').val();
                    d.produto = $('#habil-produto').val();
                }
            },
            dom: 'rtip',
            pageLength: 25,
            autoWidth: false,
            order: [[0, 'asc']],
            columns: [
                null,
                {orderable: false},
                {width: '150px'},
                {orderable: false, className: 'text-right', width: '130px'}
            ],
            oLanguage: {
                sProcessing: '<i class="fa fa-spinner fa-spin"></i> Carregando...',
                sZeroRecords: 'Nenhum cliente encontrado com os filtros atuais.',
                sInfo: 'Mostrando _START_ a _END_ de _TOTAL_ clientes',
                sInfoEmpty: 'Nenhum cliente',
                sInfoFiltered: '(de _MAX_ clientes ativos)',
                oPaginate: {sFirst: 'Primeiro', sPrevious: 'Anterior', sNext: 'Próximo', sLast: 'Último'}
            }
        });

        // Depois de habilitar, alterar ou desabilitar no modal, a linha do cliente é atualizada.
        window.habilAoAlterar = function() {
            tabela.ajax.reload(null, false);
        };

        var espera;
        $('#habil-q').on('input', function() {
            clearTimeout(espera);
            espera = setTimeout(function() { tabela.ajax.reload(); }, 350);
        });
        $('.habil-filtro').on('change', function() { tabela.ajax.reload(); });
        $('#habil-limpar').on('click', function() {
            $('#habil-q, #habil-situacao, #habil-produto').val('');
            tabela.order([0, 'asc']).ajax.reload();
        });
        $('.js-situacao-kpi').on('click', function(event) {
            event.preventDefault();
            $('#habil-situacao').val($(this).data('situacao'));
            if ($(this).data('ordem') === 'vencimento') {
                tabela.order([2, 'asc']);
            }
            tabela.ajax.reload();
        });
    });
</script>
<style type="text/css">
    .habil-kpi { display: block; background: #fff; border-radius: 6px; box-shadow: 0 1px 2px rgba(0, 0, 0, .1); padding: 14px 16px; margin-bottom: 15px; color: #333; transition: box-shadow .15s; }
    .habil-kpi:hover { color: #333; box-shadow: 0 3px 10px rgba(0, 0, 0, .15); }
    .habil-kpi span { display: block; color: #777; font-size: 12px; text-transform: uppercase; letter-spacing: .4px; }
    .habil-kpi strong { display: block; font-size: 26px; line-height: 1.2; }
    .habil-kpi small { color: #999; }
    .habil-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .habil-busca { flex: 1 1 260px; max-width: 420px; }
    .habil-filtro { width: auto; min-width: 170px; max-width: 240px; }
    .habil-tabela { border-collapse: separate; border-spacing: 0; margin: 0 !important; }
    .habil-tabela > thead > tr > th { border-bottom: 2px solid #d2d6de; background: #f4f6f8; }
    .habil-tabela > tbody > tr > td { vertical-align: middle; padding-top: 10px; padding-bottom: 10px; border-top: 0; border-bottom: 1px solid #d2d6de; }
    .habil-tabela > tbody > tr:nth-child(even) > td { background-color: #f7f9fb; }
    .habil-tabela > tbody > tr:hover > td { background-color: #eaf2f8; }
    .habil-cliente-nome { font-weight: 600; font-size: 14px; }
    .habil-cliente-detalhe { font-size: 12px; }
    .habil-chip { display: inline-block; margin: 2px 4px 2px 0; padding: 2px 8px; border-radius: 10px; background: #e8f6ee; border: 1px solid #b7e3c8; color: #1d6b40; font-size: 12px; white-space: nowrap; }
    .habil-chip small { color: #3c8dbc; font-weight: 600; text-transform: uppercase; font-size: 10px; }
    .habil-chip.is-vencendo { background: #fff4e0; border-color: #f5cf8e; color: #8a5a00; }
    .habil-extras { font-size: 12px; margin-top: 2px; }
    .habil-vencimento { font-weight: 600; }
    .habil-acoes .btn { float: none; }
    .habil-legenda { font-size: 12px; }
    .habil-legenda .habil-chip { margin-left: 8px; }
    .habil-page .dataTables_info, .habil-page .dataTables_paginate { padding: 8px 12px; }
    .habil-page .dataTables_processing { top: 60px; }
</style>
