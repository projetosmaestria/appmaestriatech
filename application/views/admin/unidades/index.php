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

    <section class="content lista-page">
        <?php if (isset($message)) { echo $message; } ?>

        <div class="row">
            <div class="col-md-3 col-sm-6"><a href="#" class="lista-kpi js-un-status" data-status=""><span>Filiais</span><strong><?php echo $numero($indicadores->total) ?></strong><small>cadastradas</small></a></div>
            <div class="col-md-3 col-sm-6"><a href="#" class="lista-kpi js-un-status" data-status="s"><span>Ativas</span><strong class="text-green"><?php echo $numero($indicadores->ativas) ?></strong><small>em atendimento</small></a></div>
            <div class="col-md-3 col-sm-6"><a href="#" class="lista-kpi js-un-status" data-status="n"><span>Inativas</span><strong class="text-muted"><?php echo $numero($indicadores->inativas) ?></strong><small>sem acesso</small></a></div>
            <div class="col-md-3 col-sm-6"><div class="lista-kpi"><span>Com produto vigente</span><strong><?php echo $numero($indicadores->com_produto) ?></strong><small>filiais ativas habilitadas</small></div></div>
        </div>

        <div class="box">
            <div class="box-header with-border lista-toolbar">
                <?php if ($can_create) { echo anchor('unidades/create', '<i class="fa fa-plus"></i> ' . lang('unidades_create'), array('class' => 'btn btn-primary')); } ?>
                <div class="input-group lista-busca">
                    <input type="search" id="un-q" class="form-control" placeholder="Buscar por nome, CNPJ/CPF, gestor, e-mail ou cidade...">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                </div>
                <select id="un-status" class="form-control lista-filtro un-filtro">
                    <option value="s">Ativas</option>
                    <option value="n">Inativas</option>
                    <option value="">Todos os status</option>
                </select>
                <select id="un-tipo" class="form-control lista-filtro un-filtro">
                    <option value="">Pessoa jurídica e física</option>
                    <option value="J">Pessoa jurídica</option>
                    <option value="F">Pessoa física</option>
                </select>
                <?php if (count($ufs) > 1) { ?>
                    <select id="un-uf" class="form-control lista-filtro un-filtro" style="min-width:110px;">
                        <option value="">Todas as UFs</option>
                        <?php foreach ($ufs as $uf) { ?><option value="<?php echo html_escape($uf->uf) ?>"><?php echo html_escape($uf->uf) ?></option><?php } ?>
                    </select>
                <?php } ?>
                <?php if ($caracteristicas) { ?>
                    <select id="un-caracteristica" class="form-control lista-filtro un-filtro">
                        <option value="">Todas as características</option>
                        <?php foreach ($caracteristicas as $caracteristica) { ?><option value="<?php echo (int) $caracteristica->id ?>"><?php echo html_escape($caracteristica->descricao) ?></option><?php } ?>
                    </select>
                <?php } ?>
                <button type="button" class="btn btn-link" id="un-limpar"><i class="fa fa-times"></i> Limpar</button>
            </div>
            <div class="box-body table-responsive no-padding">
                <table id="un-tabela" class="table lista-tabela" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Filial</th>
                            <th>Contato</th>
                            <th>Cidade</th>
                            <th>Produtos</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    window.addEventListener('load', function() {
        var tabela = $('#un-tabela').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?php echo base_url('unidades/server_side') ?>',
                type: 'post',
                dataType: 'json',
                data: function(d) {
                    d.q = $('#un-q').val();
                    d.status = $('#un-status').val();
                    d.tipo = $('#un-tipo').val();
                    d.uf = $('#un-uf').val() || '';
                    d.caracteristica = $('#un-caracteristica').val() || '';
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
                {orderable: false, width: '100px'},
                {orderable: false, width: '80px'},
                {orderable: false, className: 'lista-acoes', width: '130px'}
            ],
            oLanguage: {
                sProcessing: '<i class="fa fa-spinner fa-spin"></i> Carregando...',
                sZeroRecords: 'Nenhuma filial encontrada com os filtros atuais.',
                sInfo: 'Mostrando _START_ a _END_ de _TOTAL_ filiais',
                sInfoEmpty: 'Nenhuma filial',
                sInfoFiltered: '(de _MAX_ cadastradas)',
                oPaginate: {sFirst: 'Primeiro', sPrevious: 'Anterior', sNext: 'Próximo', sLast: 'Último'}
            }
        });

        var espera;
        $('#un-q').on('input', function() {
            clearTimeout(espera);
            espera = setTimeout(function() { tabela.ajax.reload(); }, 350);
        });
        $('.un-filtro').on('change', function() { tabela.ajax.reload(); });
        $('#un-limpar').on('click', function() {
            $('#un-q, #un-tipo, #un-uf, #un-caracteristica').val('');
            $('#un-status').val('s');
            tabela.ajax.reload();
        });
        $('.js-un-status').on('click', function(event) {
            event.preventDefault();
            $('#un-status').val(String($(this).data('status')));
            tabela.ajax.reload();
        });
        $('#un-tabela').on('click', '.js-confirmar', function(event) {
            if (!window.confirm($(this).data('confirmar'))) { event.preventDefault(); }
        });
    });
</script>
<?php $this->load->view('admin/_partials/lista_estilos'); ?>
