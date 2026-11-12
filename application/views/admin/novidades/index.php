<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$rotulos = array(
    'vigente' => array('Vigente', 'label-success', 'is-ativo'),
    'agendada' => array('Agendada', 'label-info', 'is-ativo'),
    'encerrada' => array('Encerrada', 'label-default', 'is-inativo'),
    'inativa' => array('Inativa', 'label-default', 'is-inativo'),
);
$kpis = array(
    'vigente' => array('Vigentes', 'text-green', 'aparecendo para os usuários agora'),
    'agendada' => array('Agendadas', 'text-aqua', 'começam mais à frente'),
    'encerrada' => array('Encerradas', '', 'período já terminou'),
    'inativa' => array('Inativas', 'text-muted', 'desligadas manualmente'),
);
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content lista-page">
        <?php if (isset($message)) { echo $message; } ?>

        <div class="row">
            <?php foreach ($kpis as $chave => $kpi) { ?>
                <div class="col-md-3 col-sm-6">
                    <a class="lista-kpi" href="<?php echo base_url('novidades?situacao=' . $chave) ?>">
                        <span><?php echo $kpi[0] ?></span>
                        <strong class="<?php echo $kpi[1] ?>"><?php echo (int) $totais[$chave] ?></strong>
                        <small><?php echo $kpi[2] ?></small>
                    </a>
                </div>
            <?php } ?>
        </div>

        <div class="box">
            <form class="box-header with-border lista-toolbar" method="get" action="<?php echo base_url('novidades') ?>">
                <?php if ($pode_criar) { echo anchor('novidades/create', '<i class="fa fa-plus"></i> Nova novidade', array('class' => 'btn btn-primary')); } ?>
                <div class="input-group lista-busca">
                    <input type="search" name="q" class="form-control" placeholder="Buscar no título ou no texto..." value="<?php echo html_escape($filtros['q']) ?>">
                    <span class="input-group-btn"><button type="submit" class="btn btn-default" title="Buscar"><i class="fa fa-search"></i></button></span>
                </div>
                <select name="situacao" class="form-control lista-filtro" onchange="this.form.submit()" title="Situação">
                    <option value="">Todas as situações</option>
                    <?php foreach ($rotulos as $chave => $rotulo) { ?>
                        <option value="<?php echo $chave ?>" <?php echo $filtros['situacao'] === $chave ? 'selected' : '' ?>><?php echo $rotulo[0] ?></option>
                    <?php } ?>
                </select>
                <?php if ($filtros['q'] !== '' || $filtros['situacao'] !== '') { ?>
                    <a href="<?php echo base_url('novidades') ?>" class="btn btn-link">Limpar filtros</a>
                <?php } ?>
            </form>
            <div class="box-body table-responsive no-padding">
                <table class="table lista-tabela">
                    <thead>
                        <tr>
                            <th>Novidade</th>
                            <th>Período</th>
                            <th>Situação</th>
                            <th class="text-right">Imagens</th>
                            <th class="text-right" title="Usuários que já viram e fecharam o aviso">Visualizações</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($novidades as $novidade) {
                            $rotulo = $rotulos[$novidade->situacao];
                            $resumo = trim(preg_replace('/\s+/', ' ', $novidade->conteudo));
                        ?>
                            <tr class="<?php echo $rotulo[2] ?>">
                                <td>
                                    <span class="lista-nome"><?php echo html_escape($novidade->titulo) ?></span>
                                    <div class="lista-detalhe"><?php echo html_escape(mb_strlen($resumo, 'UTF-8') > 110 ? mb_substr($resumo, 0, 110, 'UTF-8') . '…' : $resumo) ?></div>
                                </td>
                                <td class="novidades-periodo">
                                    <?php echo date('d/m/Y H:i', strtotime($novidade->data_inicio)) ?><br>
                                    <span class="text-muted">até <?php echo date('d/m/Y H:i', strtotime($novidade->data_fim)) ?></span>
                                </td>
                                <td><span class="label <?php echo $rotulo[1] ?>"><?php echo $rotulo[0] ?></span></td>
                                <td class="text-right"><?php echo (int) $novidade->total_imagens ? '<i class="fa fa-picture-o text-muted"></i> ' . (int) $novidade->total_imagens : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-right"><?php echo number_format((int) $novidade->total_leituras, 0, ',', '.') ?></td>
                                <td class="lista-acoes">
                                    <?php if ($pode_editar) { echo anchor('novidades/edit/' . (int) $novidade->id, '<i class="fa fa-pencil"></i> Editar', array('class' => 'btn btn-default btn-sm')); } ?>
                                    <?php if ($pode_excluir) { ?>
                                        <?php echo form_open('novidades/delete/' . (int) $novidade->id, array('class' => 'novidades-excluir', 'style' => 'display:inline;')); ?>
                                            <button type="submit" class="btn btn-default btn-sm" title="Excluir"><i class="fa fa-trash-o text-red"></i></button>
                                        <?php echo form_close(); ?>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php if (!$novidades) { ?>
                    <div class="lista-vazio">
                        <?php echo ($filtros['q'] !== '' || $filtros['situacao'] !== '') ? 'Nenhuma novidade encontrada com os filtros atuais.' : 'Nenhuma novidade cadastrada ainda.' ?>
                    </div>
                <?php } ?>
            </div>
            <div class="box-footer text-muted small">
                <i class="fa fa-info-circle"></i> Durante o período, cada usuário vê a novidade uma vez em uma janela ao entrar no sistema. Depois de fechar, ela fica disponível no sino de notificações.
            </div>
        </div>
    </section>
</div>
<script type="text/javascript">
    window.addEventListener('load', function() {
        $('.novidades-excluir').on('submit', function(e) {
            if (!window.confirm('Excluir esta novidade? As imagens e o registro de quem já viu também serão apagados.')) {
                e.preventDefault();
            }
        });
    });
</script>
<style type="text/css">
    .novidades-periodo { white-space: nowrap; font-size: 13px; }
</style>
<?php $this->load->view('admin/_partials/lista_estilos'); ?>
