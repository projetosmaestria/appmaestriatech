<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$acoes_por_tela = array();
foreach ($telas as $controlador => $tela) {
    $acoes_por_tela[$controlador] = $tela['acoes'];
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
            <div class="col-md-4 col-sm-6"><div class="lista-kpi"><span>Registros</span><strong id="kpi-registros">—</strong><small>no período e filtros atuais</small></div></div>
            <div class="col-md-4 col-sm-6"><div class="lista-kpi"><span>Usuários</span><strong id="kpi-usuarios">—</strong><small>que fizeram alterações</small></div></div>
            <div class="col-md-4 col-sm-6"><div class="lista-kpi"><span>Telas</span><strong id="kpi-telas">—</strong><small>com alterações registradas</small></div></div>
        </div>

        <div class="box">
            <div class="box-header with-border lista-toolbar">
                <div class="auditoria-periodo">
                    <input type="date" id="aud-inicio" class="form-control" value="<?php echo $inicio_padrao ?>" title="De">
                    <span>até</span>
                    <input type="date" id="aud-fim" class="form-control" value="<?php echo $fim_padrao ?>" title="Até">
                </div>
                <select id="aud-usuario" class="form-control lista-filtro aud-filtro">
                    <option value="">Todos os usuários</option>
                    <?php foreach ($usuarios as $usuario) { ?><option value="<?php echo (int) $usuario->id ?>"><?php echo html_escape(trim($usuario->nome) !== '' ? $usuario->nome : 'Usuário #' . (int) $usuario->id) ?></option><?php } ?>
                </select>
                <select id="aud-controlador" class="form-control lista-filtro aud-filtro">
                    <option value="">Todas as telas</option>
                    <?php foreach ($telas as $controlador => $tela) { ?><option value="<?php echo html_escape($controlador) ?>"><?php echo html_escape($tela['nome']) ?></option><?php } ?>
                </select>
                <select id="aud-metodo" class="form-control lista-filtro aud-filtro" disabled>
                    <option value="">Todas as ações</option>
                </select>
                <div class="input-group lista-busca">
                    <input type="search" id="aud-q" class="form-control" placeholder="Buscar nos detalhes...">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                </div>
                <button type="button" class="btn btn-link" id="aud-limpar"><i class="fa fa-times"></i> Limpar</button>
            </div>
            <div class="box-body table-responsive no-padding">
                <table id="aud-tabela" class="table lista-tabela" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Quando</th>
                            <th>Usuário</th>
                            <th>Tela / ação</th>
                            <th>Detalhes</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="aud-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Detalhes do registro</h4>
            </div>
            <div class="modal-body"><pre class="auditoria-obs-completa" id="aud-modal-texto"></pre></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    window.addEventListener('load', function() {
        var acoes = <?php echo json_encode($acoes_por_tela) ?>;
        var numero = function(valor) { return (parseInt(valor, 10) || 0).toLocaleString('pt-BR'); };

        var tabela = $('#aud-tabela').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?php echo base_url('auditorias/server_side') ?>',
                type: 'post',
                dataType: 'json',
                data: function(d) {
                    d.inicio = $('#aud-inicio').val();
                    d.fim = $('#aud-fim').val();
                    d.usuario = $('#aud-usuario').val();
                    d.controlador = $('#aud-controlador').val();
                    d.metodo = $('#aud-metodo').val();
                    d.q = $('#aud-q').val();
                },
                dataSrc: function(json) {
                    if (json.resumo) {
                        $('#kpi-registros').text(numero(json.resumo.registros));
                        $('#kpi-usuarios').text(numero(json.resumo.usuarios));
                        $('#kpi-telas').text(numero(json.resumo.telas));
                    }
                    return json.data;
                }
            },
            dom: 'rtip',
            pageLength: 25,
            autoWidth: false,
            order: [[0, 'desc']],
            columns: [
                {width: '110px'},
                {orderable: false, width: '200px'},
                {orderable: false, width: '200px'},
                {orderable: false}
            ],
            oLanguage: {
                sProcessing: '<i class="fa fa-spinner fa-spin"></i> Carregando...',
                sZeroRecords: 'Nenhum registro encontrado com os filtros atuais.',
                sInfo: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                sInfoEmpty: 'Nenhum registro',
                sInfoFiltered: '(de _MAX_ no total)',
                oPaginate: {sFirst: 'Primeiro', sPrevious: 'Anterior', sNext: 'Próximo', sLast: 'Último'}
            }
        });

        function recarregar() { tabela.ajax.reload(); }

        $('#aud-controlador').on('change', function() {
            var select = $('#aud-metodo').empty().append('<option value="">Todas as ações</option>');
            var lista = acoes[$(this).val()] || {};
            $.each(lista, function(metodo, rotulo) {
                select.append($('<option></option>').val(metodo).text(rotulo));
            });
            select.prop('disabled', !$(this).val());
        });
        $('.aud-filtro, #aud-inicio, #aud-fim').on('change', recarregar);
        var espera;
        $('#aud-q').on('input', function() {
            clearTimeout(espera);
            espera = setTimeout(recarregar, 350);
        });
        $('#aud-limpar').on('click', function() {
            $('#aud-inicio').val('<?php echo $inicio_padrao ?>');
            $('#aud-fim').val('<?php echo $fim_padrao ?>');
            $('#aud-usuario, #aud-controlador, #aud-q').val('');
            $('#aud-controlador').trigger('change');
            recarregar();
        });
        $('#aud-tabela').on('click', '.js-obs-completa', function(event) {
            event.preventDefault();
            $('#aud-modal-texto').text($(this).data('obs'));
            $('#aud-modal').modal('show');
        });
    });
</script>
<?php $this->load->view('admin/_partials/lista_estilos'); ?>
<style type="text/css">
    .auditoria-periodo { display: flex; align-items: center; gap: 6px; }
    .auditoria-periodo .form-control { width: 150px; }
    .auditoria-periodo span { color: #777; }
    .auditoria-obs { font-size: 12px; color: #555; word-break: break-word; }
    .auditoria-obs-completa { white-space: pre-wrap; word-break: break-word; max-height: 60vh; overflow: auto; font-size: 12px; }
</style>
