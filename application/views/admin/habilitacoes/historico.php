<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$situacoes = array(
    'vigente' => array('Vigente', 'label-success'),
    'vencendo' => array('Vencendo', 'label-warning'),
    'vencida' => array('Vencida', 'label-danger'),
    'desabilitada' => array('Desabilitada', 'label-default'),
);
$data_br = function ($data) {
    return ($data && $data !== '0000-00-00' && $data !== '0000-00-00 00:00:00') ? date('d/m/Y', strtotime($data)) : '—';
};
$documento = $cliente->cnpj != '' ? $cliente->cnpj : $cliente->cpf;
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content habil-page">
        <?php if (isset($message)) { echo $message; } ?>

        <div class="habil-cliente-cabecalho">
            <div>
                <div class="habil-cliente-titulo">
                    <?php echo html_escape($cliente->unidade) ?>
                    <?php if ($cliente->actived != 's') { ?><span class="label label-default">Cliente inativo</span><?php } ?>
                </div>
                <div class="text-muted habil-cliente-dados">
                    <?php
                    $dados = array();
                    if ($cliente->razao_social != '' && $cliente->razao_social !== $cliente->unidade) { $dados[] = html_escape($cliente->razao_social); }
                    if ($documento != '') { $dados[] = html_escape($documento); }
                    if ($cliente->gestor != '') { $dados[] = 'Gestor: ' . html_escape($cliente->gestor); }
                    if ($cliente->telefone1 != '') { $dados[] = html_escape($cliente->telefone1); }
                    if ($cliente->email != '') { $dados[] = html_escape($cliente->email); }
                    echo implode(' · ', $dados);
                    ?>
                </div>
            </div>
            <div class="habil-cliente-botoes">
                <a href="<?php echo base_url('habilitacoes') ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Voltar</a>
                <?php if ($pode_gerenciar) { ?>
                    <button type="button" class="btn btn-primary js-gerenciar-produtos" data-cliente="<?php echo (int) $cliente->id ?>"><i class="fa fa-th"></i> Gerenciar produtos</button>
                <?php } ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-6"><div class="habil-kpi"><span>Vigentes</span><strong class="text-green"><?php echo (int) $resumo['vigente'] + (int) $resumo['vencendo'] ?></strong><small>produtos em uso</small></div></div>
            <div class="col-md-3 col-sm-6"><div class="habil-kpi"><span>Vencendo</span><strong class="text-yellow"><?php echo (int) $resumo['vencendo'] ?></strong><small>nos próximos <?php echo (int) $dias_alerta ?> dias</small></div></div>
            <div class="col-md-3 col-sm-6"><div class="habil-kpi"><span>Vencidas</span><strong class="text-red"><?php echo (int) $resumo['vencida'] ?></strong><small>ativas com período encerrado</small></div></div>
            <div class="col-md-3 col-sm-6"><div class="habil-kpi"><span>Desabilitadas</span><strong class="text-muted"><?php echo (int) $resumo['desabilitada'] ?></strong><small>com justificativa no histórico</small></div></div>
        </div>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Produtos habilitados</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <?php if (empty($habilitacoes)) { ?>
                    <p class="text-muted habil-vazio-pagina">Nenhum produto habilitado para este cliente.<?php echo $pode_gerenciar ? ' Use "Gerenciar produtos" para habilitar.' : '' ?></p>
                <?php } else { ?>
                    <table class="table habil-tabela habil-tabela-historico">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Situação</th>
                                <th>Período</th>
                                <th>Módulos</th>
                                <th>Contrato MaestriaTech</th>
                                <th>Registro</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($habilitacoes as $habilitacao) {
                                $liberados = 0; $consulta = 0; $usuarios = 0;
                                foreach ($habilitacao->modulos as $modulo) {
                                    if ($modulo['status'] === 'L') { $liberados++; }
                                    if ($modulo['status'] === 'C') { $consulta++; }
                                    if ($modulo['status'] !== 'B') { $usuarios += (int) $modulo['usuarios']; }
                                }
                                $dias = (int) $habilitacao->dias_restantes;
                            ?>
                                <tr class="situacao-<?php echo $habilitacao->situacao ?>">
                                    <td>
                                        <div class="habil-cliente-nome"><?php echo html_escape($habilitacao->produto) ?></div>
                                        <?php if ($habilitacao->aplicacao != '') { ?><div class="habil-cliente-detalhe text-muted"><?php echo html_escape($habilitacao->aplicacao) ?></div><?php } ?>
                                    </td>
                                    <td>
                                        <span class="label <?php echo $situacoes[$habilitacao->situacao][1] ?>"><?php echo $situacoes[$habilitacao->situacao][0] ?></span>
                                        <?php if ($habilitacao->demonstracao === 'S') { ?><span class="label label-info">Demonstração</span><?php } ?>
                                        <?php if ($habilitacao->situacao === 'vencendo') { ?>
                                            <div class="habil-cliente-detalhe text-yellow"><?php echo $dias === 0 ? 'vence hoje' : 'vence em ' . $dias . ($dias === 1 ? ' dia' : ' dias') ?></div>
                                        <?php } elseif ($habilitacao->situacao === 'vencida') { ?>
                                            <div class="habil-cliente-detalhe text-red">há <?php echo abs($dias) . (abs($dias) === 1 ? ' dia' : ' dias') ?></div>
                                        <?php } ?>
                                    </td>
                                    <td class="text-nowrap"><?php echo $data_br($habilitacao->periodo_inicio) ?> a <?php echo $data_br($habilitacao->periodo_fim) ?></td>
                                    <td>
                                        <?php if (empty($habilitacao->modulos)) { ?>
                                            <span class="text-muted">Sem módulos</span>
                                        <?php } else { ?>
                                            <?php echo $liberados ?> de <?php echo count($habilitacao->modulos) ?> liberados<?php echo $consulta ? ', ' . $consulta . ' em consulta' : '' ?>
                                            <?php if ($usuarios) { ?><div class="habil-cliente-detalhe text-muted"><?php echo $usuarios ?> usuário<?php echo $usuarios === 1 ? '' : 's' ?></div><?php } ?>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo $habilitacao->contrato_sax ? (int) $habilitacao->contrato_sax : '<span class="text-muted">—</span>' ?></td>
                                    <td class="habil-registro">
                                        Criada em <?php echo $data_br($habilitacao->data_cadastro) ?><?php echo trim($habilitacao->cadastrado_por_nome) !== '' ? ' por ' . html_escape($habilitacao->cadastrado_por_nome) : '' ?>
                                        <?php if ($habilitacao->data_atualizacao) { ?>
                                            <div class="text-muted">Alterada em <?php echo $data_br($habilitacao->data_atualizacao) ?><?php echo trim($habilitacao->atualizado_por_nome) !== '' ? ' por ' . html_escape($habilitacao->atualizado_por_nome) : '' ?></div>
                                        <?php } ?>
                                    </td>
                                    <td class="text-right">
                                        <?php if ($habilitacao->historico) { ?>
                                            <button type="button" class="btn btn-default btn-xs js-ver-historico" data-alvo="historico-<?php echo (int) $habilitacao->id ?>"><i class="fa fa-history"></i> <?php echo count($habilitacao->historico) ?></button>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php if ($habilitacao->historico) { ?>
                                    <tr class="habil-historico-linha" id="historico-<?php echo (int) $habilitacao->id ?>" style="display:none;">
                                        <td colspan="7">
                                            <ul class="habil-historico">
                                                <?php foreach ($habilitacao->historico as $registro) { ?>
                                                    <li>
                                                        <strong><?php echo $registro->acao === 'D' ? 'Desabilitou' : 'Reabilitou' ?></strong>
                                                        <span class="text-muted"><?php echo html_escape(trim($registro->usuario) !== '' ? $registro->usuario : 'Usuário removido') ?> · <?php echo date('d/m/Y H:i', strtotime($registro->data_cadastro)) ?></span>
                                                        <div><?php echo nl2br(html_escape($registro->observacao)) ?></div>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            </div>
        </div>
    </section>
</div>

<?php if ($pode_gerenciar) { $this->load->view('admin/habilitacoes/_modal_produtos'); } ?>

<script type="text/javascript">
    window.addEventListener('load', function() {
        // Nesta página o conteúdo vem pronto do servidor: recarrega para refletir o que mudou no modal.
        var alterou = false;
        window.habilAoAlterar = function() { alterou = true; };
        $('#habilModal').on('hidden.bs.modal', function() {
            if (alterou) { window.location.reload(); }
        });
        $('.js-ver-historico').on('click', function() {
            $('#' + $(this).data('alvo')).toggle();
        });
    });
</script>
<style type="text/css">
    .habil-cliente-cabecalho { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 15px; }
    .habil-cliente-titulo { font-size: 20px; font-weight: 600; }
    .habil-cliente-dados { font-size: 13px; }
    .habil-cliente-botoes { display: flex; gap: 6px; }
    .habil-kpi { display: block; background: #fff; border-radius: 6px; box-shadow: 0 1px 2px rgba(0, 0, 0, .1); padding: 14px 16px; margin-bottom: 15px; color: #333; }
    .habil-kpi span { display: block; color: #777; font-size: 12px; text-transform: uppercase; letter-spacing: .4px; }
    .habil-kpi strong { display: block; font-size: 26px; line-height: 1.2; }
    .habil-kpi small { color: #999; }
    .habil-tabela { border-collapse: separate; border-spacing: 0; margin: 0 !important; }
    .habil-tabela > thead > tr > th { border-bottom: 2px solid #d2d6de; background: #f4f6f8; }
    .habil-tabela > tbody > tr > td { vertical-align: middle; padding-top: 10px; padding-bottom: 10px; border-top: 0; border-bottom: 1px solid #d2d6de; }
    .habil-tabela-historico > tbody > tr > td:first-child { border-left: 4px solid #00a65a; }
    .habil-tabela-historico > tbody > tr.situacao-vencendo > td:first-child { border-left-color: #f39c12; }
    .habil-tabela-historico > tbody > tr.situacao-vencida > td:first-child { border-left-color: #dd4b39; }
    .habil-tabela-historico > tbody > tr.situacao-desabilitada > td { background: #fafafa; color: #777; }
    .habil-tabela-historico > tbody > tr.situacao-desabilitada > td:first-child { border-left-color: #b5bbc8; }
    .habil-tabela-historico > tbody > tr.habil-historico-linha > td { background: #f7f9fb; border-left: 4px solid #d2d6de; }
    .habil-cliente-nome { font-weight: 600; font-size: 14px; }
    .habil-cliente-detalhe { font-size: 12px; }
    .habil-registro { font-size: 12px; }
    .habil-vazio-pagina { padding: 20px; margin: 0; }
    .habil-historico { list-style: none; margin: 0; padding: 0; font-size: 12px; }
    .habil-historico li { padding: 4px 0; border-bottom: 1px dashed #e3e3e3; }
    .habil-historico li:last-child { border-bottom: 0; }
</style>
