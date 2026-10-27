<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$hhmm = function ($segundos) {
    $segundos = max(0, (int) $segundos);
    return sprintf('%d:%02d', floor($segundos / 3600), floor(($segundos % 3600) / 60));
};
// Apontamentos sem horário ficam gravados como 00:00 – 00:00: nesse caso o período fica em branco.
$horario = function ($item) {
    $inicio = substr((string) $item->hora_inicio, 0, 5);
    $fim = substr((string) $item->hora_termino, 0, 5);
    if (($inicio === '' || $inicio === '00:00') && ($fim === '' || $fim === '00:00')) {
        return array('', '');
    }
    return array($inicio, $fim === '00:00' ? '' : $fim);
};
$decimal = function ($segundos) {
    return number_format($segundos / 3600, 2, ',', '.');
};
$periodo_texto = date('d/m/Y', strtotime($filtros['inicio'])) . ' a ' . date('d/m/Y', strtotime($filtros['fim']));
$filtros_texto = array();
if ($filtros['usuarios']) {
    $filtros_texto[] = 'Usuários: ' . implode(', ', array_intersect_key($usuarios_options, array_flip($filtros['usuarios'])));
}
if ($filtros['clientes']) {
    $filtros_texto[] = 'Clientes: ' . implode(', ', array_intersect_key($unidade_options, array_flip($filtros['clientes'])));
}
if ($filtros['chamado']) {
    $filtros_texto[] = 'Chamado #' . (int) $filtros['chamado'];
}
$maior_usuario = $resumo['usuarios'] ? max(1, $resumo['usuarios'][0]['segundos']) : 1;
$maior_cliente = $resumo['clientes'] ? max(1, $resumo['clientes'][0]['segundos']) : 1;

// Dados usados pelas exportações (PDF, Excel e CSV), já filtrados no servidor.
$exportacao = array(
    'titulo' => 'Relatório de apontamentos',
    'periodo' => $periodo_texto,
    'filtros' => $filtros_texto ? implode(' · ', $filtros_texto) : 'Todos os usuários e clientes',
    'gerado_em' => date('d/m/Y H:i'),
    'arquivo' => 'apontamentos_' . $filtros['inicio'] . '_a_' . $filtros['fim'],
    'total_segundos' => $resumo['segundos'],
    'linhas' => array(),
    'usuarios' => array(),
);
foreach ($apontamentos as $item) {
    list($hora_inicio, $hora_fim) = $horario($item);
    $exportacao['linhas'][] = array(
        'data' => date('d/m/Y', strtotime($item->data)),
        'usuario' => $item->nome_usuario,
        'cliente' => $item->cliente,
        'chamado' => (int) $item->chamado_id,
        'titulo' => $item->chamado_titulo,
        'inicio' => $hora_inicio,
        'fim' => $hora_fim,
        'segundos' => (int) $item->segundos,
        'observacao' => (string) $item->observacao,
    );
}
foreach ($resumo['usuarios'] as $usuario) {
    $exportacao['usuarios'][] = array('nome' => $usuario['nome'], 'segundos' => $usuario['segundos'], 'registros' => $usuario['registros'], 'chamados' => count($usuario['chamados']));
}
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content apontamentos-page">
        <?php if (isset($message)) { echo $message; } ?>

        <div class="box">
            <?php echo form_open('chamados/apontamentos', array('method' => 'get', 'id' => 'form-apontamentos_filtro')); ?>
            <div class="box-header with-border apontamentos-toolbar">
                <div class="form-group">
                    <label for="filtro-inicio">Período</label>
                    <div class="input-group apontamentos-periodo">
                        <input type="date" id="filtro-inicio" name="inicio" class="form-control" value="<?php echo html_escape($filtros['inicio']) ?>" required>
                        <span class="input-group-addon">até</span>
                        <input type="date" id="filtro-fim" name="fim" class="form-control" value="<?php echo html_escape($filtros['fim']) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="hidden-xs">&nbsp;</label>
                    <div class="btn-group" role="group" aria-label="Atalhos de período">
                        <button type="button" class="btn btn-default js-periodo" data-periodo="hoje">Hoje</button>
                        <button type="button" class="btn btn-default js-periodo" data-periodo="semana">Semana</button>
                        <button type="button" class="btn btn-default js-periodo" data-periodo="mes">Mês</button>
                        <button type="button" class="btn btn-default js-periodo" data-periodo="mes_passado">Mês passado</button>
                    </div>
                </div>
                <div class="form-group apontamentos-multi">
                    <label for="filtro-usuarios">Usuários</label>
                    <select id="filtro-usuarios" name="usuarios[]" class="multipleSelect form-control" multiple>
                        <?php foreach ($usuarios_options as $id => $nome) { ?>
                            <option value="<?php echo (int) $id ?>" <?php echo in_array($id, $filtros['usuarios'], true) ? 'selected' : '' ?>><?php echo html_escape($nome) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group apontamentos-multi">
                    <label for="filtro-clientes">Clientes</label>
                    <select id="filtro-clientes" name="clientes[]" class="multipleSelect form-control" multiple>
                        <?php foreach ($unidade_options as $id => $nome) { ?>
                            <option value="<?php echo (int) $id ?>" <?php echo in_array($id, $filtros['clientes'], true) ? 'selected' : '' ?>><?php echo html_escape($nome) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group apontamentos-chamado">
                    <label for="filtro-chamado">Chamado</label>
                    <input type="number" id="filtro-chamado" name="chamado" class="form-control" min="1" placeholder="Nº" value="<?php echo $filtros['chamado'] ? (int) $filtros['chamado'] : '' ?>">
                </div>
                <div class="form-group">
                    <label class="hidden-xs">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Filtrar</button>
                        <a href="<?php echo base_url('chamados/apontamentos') ?>" class="btn btn-link">Limpar</a>
                    </div>
                </div>
                <div class="form-group apontamentos-export">
                    <label class="hidden-xs">&nbsp;</label>
                    <div class="btn-group">
                        <button type="button" class="btn btn-default js-exportar" data-formato="pdf" <?php echo $apontamentos ? '' : 'disabled' ?>><i class="fa fa-file-pdf-o text-red"></i> PDF</button>
                        <button type="button" class="btn btn-default js-exportar" data-formato="xlsx" <?php echo $apontamentos ? '' : 'disabled' ?>><i class="fa fa-file-excel-o text-green"></i> Excel</button>
                        <button type="button" class="btn btn-default js-exportar" data-formato="csv" <?php echo $apontamentos ? '' : 'disabled' ?>><i class="fa fa-file-text-o"></i> CSV</button>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
            <div class="box-body apontamentos-resumo-filtro text-muted small">
                <i class="fa fa-filter"></i> <?php echo $periodo_texto ?> · <?php echo html_escape($exportacao['filtros']) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-6"><div class="apontamentos-kpi"><span>Total de horas</span><strong><?php echo $hhmm($resumo['segundos']) ?> h</strong><small><?php echo $decimal($resumo['segundos']) ?> h decimais</small></div></div>
            <div class="col-md-3 col-sm-6"><div class="apontamentos-kpi"><span>Apontamentos</span><strong><?php echo number_format($resumo['registros'], 0, ',', '.') ?></strong><small>registros no período</small></div></div>
            <div class="col-md-3 col-sm-6"><div class="apontamentos-kpi"><span>Chamados</span><strong><?php echo number_format(count($resumo['chamados']), 0, ',', '.') ?></strong><small>com horas apontadas</small></div></div>
            <div class="col-md-3 col-sm-6"><div class="apontamentos-kpi"><span>Usuários</span><strong><?php echo count($resumo['usuarios']) ?></strong><small>apontaram no período</small></div></div>
        </div>

        <?php if ($apontamentos) { ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="box">
                        <div class="box-header with-border"><h3 class="box-title">Horas por usuário</h3></div>
                        <div class="box-body no-padding">
                            <table class="table apontamentos-ranking">
                                <?php foreach ($resumo['usuarios'] as $usuario) { ?>
                                    <tr>
                                        <td class="apontamentos-ranking-nome"><?php echo html_escape($usuario['nome']) ?><small><?php echo $usuario['registros'] ?> apont. · <?php echo count($usuario['chamados']) ?> cham.</small></td>
                                        <td class="apontamentos-ranking-barra"><div class="progress progress-xs"><div class="progress-bar progress-bar-primary" style="width: <?php echo round($usuario['segundos'] / $maior_usuario * 100) ?>%"></div></div></td>
                                        <td class="text-right apontamentos-ranking-horas"><?php echo $hhmm($usuario['segundos']) ?> h</td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="box">
                        <div class="box-header with-border"><h3 class="box-title">Horas por cliente</h3><?php if (count($resumo['clientes']) > 8) { ?><small class="text-muted"> · 8 maiores de <?php echo count($resumo['clientes']) ?></small><?php } ?></div>
                        <div class="box-body no-padding">
                            <table class="table apontamentos-ranking">
                                <?php foreach (array_slice($resumo['clientes'], 0, 8) as $cliente) { ?>
                                    <tr>
                                        <td class="apontamentos-ranking-nome"><?php echo html_escape($cliente['nome']) ?><small><?php echo count($cliente['chamados']) ?> cham.</small></td>
                                        <td class="apontamentos-ranking-barra"><div class="progress progress-xs"><div class="progress-bar progress-bar-success" style="width: <?php echo round($cliente['segundos'] / $maior_cliente * 100) ?>%"></div></div></td>
                                        <td class="text-right apontamentos-ranking-horas"><?php echo $hhmm($cliente['segundos']) ?> h</td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Apontamentos</h3>
                <span class="text-muted small"> · mais recentes primeiro</span>
            </div>
            <div class="box-body">
                <?php if (!$apontamentos) { ?>
                    <p class="text-muted text-center apontamentos-vazio">Nenhum apontamento encontrado no período e filtros selecionados.</p>
                <?php } else { ?>
                    <table id="apont_datatable" class="table table-hover apontamentos-table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Usuário</th>
                                <th>Cliente</th>
                                <th>Chamado</th>
                                <th>Período</th>
                                <th class="text-right">Esforço</th>
                                <th>Observação</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($apontamentos as $values) {
                                list($hora_inicio, $hora_fim) = $horario($values);
                            ?>
                                <tr>
                                    <td data-order="<?php echo strtotime($values->data) ?>" class="apontamentos-nowrap"><?php echo date('d/m/Y', strtotime($values->data)) ?></td>
                                    <td><?php echo html_escape($values->nome_usuario) ?></td>
                                    <td><?php echo html_escape($values->cliente) ?></td>
                                    <td><a href="<?php echo base_url('chamados/details/' . (int) $values->chamado_id) ?>" target="_blank">#<?php echo (int) $values->chamado_id ?></a> <span class="apontamentos-titulo"><?php echo html_escape($values->chamado_titulo) ?></span></td>
                                    <td class="apontamentos-nowrap text-muted"><?php echo $hora_inicio !== '' ? $hora_inicio . ($hora_fim !== '' ? ' – ' . $hora_fim : '') : '—' ?></td>
                                    <td class="text-right apontamentos-nowrap" data-order="<?php echo (int) $values->segundos ?>"><strong><?php echo $hhmm($values->segundos) ?></strong></td>
                                    <td class="apontamentos-obs" title="<?php echo html_escape($values->observacao) ?>"><?php echo html_escape($values->observacao) ?></td>
                                    <td class="text-right apontamentos-nowrap">
                                        <input type="hidden" id="data-<?php echo (int) $values->id ?>" value="<?php echo date('d/m/Y', strtotime($values->data)) ?>">
                                        <input type="hidden" id="esforco-<?php echo (int) $values->id ?>" value="<?php echo substr((string) $values->esforco, 0, 5) ?>">
                                        <input type="hidden" id="hora_inicio-<?php echo (int) $values->id ?>" value="<?php echo substr((string) $values->hora_inicio, 0, 5) ?>">
                                        <input type="hidden" id="hora_termino-<?php echo (int) $values->id ?>" value="<?php echo substr((string) $values->hora_termino, 0, 5) ?>">
                                        <input type="hidden" id="observacao-<?php echo (int) $values->id ?>" value="<?php echo html_escape($values->observacao) ?>">
                                        <?php if ($can_apontamento_editar) { ?>
                                            <a href="javascript:void(0);" id="<?php echo (int) $values->id ?>" class="btn btn-default btn-xs btn_apontamento_editar" title="Editar"><i class="fa fa-edit"></i></a>
                                        <?php } ?>
                                        <?php if ($can_apontamento_excluir) { ?>
                                            <a href="javascript:void(0);" id="<?php echo (int) $values->id ?>" class="btn btn-default btn-xs btn_apontamento_excluir" title="Excluir"><i class="fa fa-trash text-red"></i></a>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-right">Total do período</th>
                                <th class="text-right"><?php echo $hhmm($resumo['segundos']) ?></th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                <?php } ?>
            </div>
        </div>
    </section>
</div>

<?php if ($can_apontamento_excluir) { ?>
<div id="apontamentoExcluirModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="apontamentos-modal-titulo">Excluir apontamento</h4>
      </div>
      <div class="modal-body">Deseja realmente excluir este apontamento? Essa ação não pode ser desfeita.</div>
      <div class="modal-footer">
        <?php echo form_open(base_url('chamados/apontamento_excluir'), array('id' => 'form-apontamento_excluir')); ?>
          <input type="hidden" id="apontamento_id_excluir" name="apontamento_id_excluir" value="">
          <input type="hidden" name="voltar_query" value="<?php echo html_escape($query_atual) ?>">
          <button type="button" data-dismiss="modal" class="btn btn-default">Cancelar</button>
          <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> Excluir</button>
        <?php echo form_close(); ?>
      </div>
    </div>
  </div>
</div>
<?php } ?>

<?php if ($can_apontamento_editar) { ?>
<div id="apontarModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <?php echo form_open(base_url('chamados/apontamento_editar'), array('id' => 'form-apontar_chamado')); ?>
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="apontamentos-modal-titulo">Editar apontamento</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="apontamento_id" name="apontamento_id" value="">
        <input type="hidden" name="voltar_query" value="<?php echo html_escape($query_atual) ?>">
        <div class="row">
          <div class="col-sm-6 form-group">
            <label for="data">Data <span class="text-red">*</span></label>
            <div class="input-group date" id="datetimepicker">
              <input type="text" id="data" name="data" class="form-control" value="<?php echo date('d/m/Y') ?>">
              <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            </div>
          </div>
          <div class="col-sm-6 form-group">
            <label for="esforco">Esforço <span class="text-red">*</span></label>
            <input type="text" id="esforco" name="esforco" class="form-control required" value="00:00" onkeypress="formatTime(this)" maxlength="5" placeholder="hh:mm">
          </div>
        </div>
        <div class="row">
          <div class="col-sm-6 form-group">
            <label for="hora_inicio">Início <small class="text-muted">(opcional)</small></label>
            <div class="input-group date" id="datetimehouronlypicker1">
              <input type="text" id="hora_inicio" name="hora_inicio" class="form-control">
              <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
            </div>
          </div>
          <div class="col-sm-6 form-group">
            <label for="hora_termino">Término <small class="text-muted">(opcional)</small></label>
            <div class="input-group date" id="datetimehouronlypicker3">
              <input type="text" id="hora_termino" name="hora_termino" class="form-control">
              <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label for="observacao">O que foi feito</label>
          <textarea id="observacao" name="observacao" class="form-control" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Salvar</button>
      </div>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>
<?php } ?>

<script type="text/javascript">
    window.APONTAMENTOS_EXPORTACAO = <?php echo json_encode($exportacao, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    (function() {
        function pad(n) { return (n < 10 ? '0' : '') + n; }
        function iso(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }

        // Atalhos de período preenchem as datas e aplicam o filtro.
        document.querySelectorAll('.js-periodo').forEach(function(button) {
            button.addEventListener('click', function() {
                var hoje = new Date(), inicio = new Date(hoje), fim = new Date(hoje);
                var periodo = button.getAttribute('data-periodo');
                if (periodo === 'semana') {
                    inicio.setDate(hoje.getDate() - ((hoje.getDay() + 6) % 7));
                } else if (periodo === 'mes') {
                    inicio = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
                } else if (periodo === 'mes_passado') {
                    inicio = new Date(hoje.getFullYear(), hoje.getMonth() - 1, 1);
                    fim = new Date(hoje.getFullYear(), hoje.getMonth(), 0);
                }
                document.getElementById('filtro-inicio').value = iso(inicio);
                document.getElementById('filtro-fim').value = iso(fim);
                document.getElementById('form-apontamentos_filtro').submit();
            });
        });

        var dados = window.APONTAMENTOS_EXPORTACAO;
        function hhmm(segundos) { return Math.floor(segundos / 3600) + ':' + pad(Math.floor((segundos % 3600) / 60)); }
        function horasDecimais(segundos) { return Math.round(segundos / 36) / 100; }
        function carregar(src) {
            return new Promise(function(ok, falha) {
                if (document.querySelector('script[src="' + src + '"]')) { return ok(); }
                var s = document.createElement('script');
                s.src = src; s.onload = ok; s.onerror = falha;
                document.head.appendChild(s);
            });
        }
        function baixar(conteudo, nome, tipo) {
            var link = document.createElement('a');
            link.href = URL.createObjectURL(new Blob([conteudo], {type: tipo}));
            link.download = nome;
            document.body.appendChild(link);
            link.click();
            setTimeout(function() { URL.revokeObjectURL(link.href); link.remove(); }, 1000);
        }

        var exportar = {
            csv: function() {
                var celula = function(v) { v = String(v === null || v === undefined ? '' : v); return /[";\n\r]/.test(v) ? '"' + v.replace(/"/g, '""') + '"' : v; };
                var linhas = [['Data', 'Usuário', 'Cliente', 'Chamado', 'Título', 'Início', 'Término', 'Esforço (hh:mm)', 'Horas (decimal)', 'Observação']];
                dados.linhas.forEach(function(l) {
                    linhas.push([l.data, l.usuario, l.cliente, l.chamado, l.titulo, l.inicio, l.fim, hhmm(l.segundos), String(horasDecimais(l.segundos)).replace('.', ','), l.observacao]);
                });
                linhas.push(['Total', '', '', '', '', '', '', hhmm(dados.total_segundos), String(horasDecimais(dados.total_segundos)).replace('.', ','), '']);
                // BOM e ";" para o Excel em português abrir com acentos e colunas corretas.
                baixar('﻿' + linhas.map(function(l) { return l.map(celula).join(';'); }).join('\r\n'), dados.arquivo + '.csv', 'text/csv;charset=utf-8');
                return Promise.resolve();
            },
            xlsx: function() {
                return carregar('https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js').then(function() {
                    var detalhes = [['Data', 'Usuário', 'Cliente', 'Chamado', 'Título', 'Início', 'Término', 'Esforço (hh:mm)', 'Horas', 'Observação']];
                    dados.linhas.forEach(function(l) {
                        detalhes.push([l.data, l.usuario, l.cliente, l.chamado, l.titulo, l.inicio, l.fim, hhmm(l.segundos), horasDecimais(l.segundos), l.observacao]);
                    });
                    detalhes.push(['Total', '', '', '', '', '', '', hhmm(dados.total_segundos), horasDecimais(dados.total_segundos), '']);
                    var resumo = [[dados.titulo], ['Período', dados.periodo], ['Filtros', dados.filtros], ['Gerado em', dados.gerado_em], [], ['Usuário', 'Apontamentos', 'Chamados', 'Horas (hh:mm)', 'Horas']];
                    dados.usuarios.forEach(function(u) { resumo.push([u.nome, u.registros, u.chamados, hhmm(u.segundos), horasDecimais(u.segundos)]); });
                    resumo.push(['Total', dados.linhas.length, '', hhmm(dados.total_segundos), horasDecimais(dados.total_segundos)]);

                    var livro = XLSX.utils.book_new();
                    var abaResumo = XLSX.utils.aoa_to_sheet(resumo);
                    abaResumo['!cols'] = [{wch: 32}, {wch: 14}, {wch: 12}, {wch: 14}, {wch: 10}];
                    var abaDetalhes = XLSX.utils.aoa_to_sheet(detalhes);
                    abaDetalhes['!cols'] = [{wch: 11}, {wch: 24}, {wch: 28}, {wch: 9}, {wch: 45}, {wch: 7}, {wch: 8}, {wch: 14}, {wch: 8}, {wch: 50}];
                    abaDetalhes['!autofilter'] = {ref: 'A1:J' + (detalhes.length - 1)};
                    XLSX.utils.book_append_sheet(livro, abaResumo, 'Resumo');
                    XLSX.utils.book_append_sheet(livro, abaDetalhes, 'Apontamentos');
                    XLSX.writeFile(livro, dados.arquivo + '.xlsx');
                });
            },
            pdf: function() {
                return carregar('https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js')
                    .then(function() { return carregar('https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js'); })
                    .then(function() {
                        var cab = function(t) { return {text: t, style: 'cab'}; };
                        var usuarios = [[cab('Usuário'), cab('Apontamentos'), cab('Chamados'), cab('Horas')]];
                        dados.usuarios.forEach(function(u) { usuarios.push([u.nome, {text: String(u.registros), alignment: 'right'}, {text: String(u.chamados), alignment: 'right'}, {text: hhmm(u.segundos), alignment: 'right', bold: true}]); });
                        usuarios.push([{text: 'Total', bold: true}, {text: String(dados.linhas.length), alignment: 'right', bold: true}, '', {text: hhmm(dados.total_segundos), alignment: 'right', bold: true}]);

                        var detalhes = [[cab('Data'), cab('Usuário'), cab('Cliente'), cab('Chamado'), cab('Período'), cab('Esforço'), cab('Observação')]];
                        dados.linhas.forEach(function(l) {
                            detalhes.push([l.data, l.usuario, l.cliente, '#' + l.chamado + ' ' + l.titulo, l.inicio ? l.inicio + (l.fim ? ' – ' + l.fim : '') : '—', {text: hhmm(l.segundos), alignment: 'right'}, l.observacao]);
                        });
                        detalhes.push([{text: 'Total do período', colSpan: 5, alignment: 'right', bold: true}, '', '', '', '', {text: hhmm(dados.total_segundos), alignment: 'right', bold: true}, '']);

                        var linhasZebradas = {
                            fillColor: function(i) { return i === 0 ? '#2f4a5c' : (i % 2 === 0 ? '#f4f6f8' : null); },
                            hLineColor: '#d2d6de', vLineColor: '#d2d6de', hLineWidth: function() { return 0.5; }, vLineWidth: function() { return 0; }
                        };
                        pdfMake.createPdf({
                            pageSize: 'A4', pageOrientation: 'landscape', pageMargins: [28, 32, 28, 36],
                            defaultStyle: {fontSize: 8, color: '#333333'},
                            styles: {titulo: {fontSize: 16, bold: true, color: '#2f4a5c'}, sub: {fontSize: 9, color: '#777777'}, secao: {fontSize: 11, bold: true, color: '#3c8dbc', margin: [0, 14, 0, 6]}, cab: {bold: true, color: '#ffffff', fontSize: 8}},
                            footer: function(pagina, total) { return {columns: [{text: dados.titulo + ' · ' + dados.periodo, margin: [28, 0, 0, 0]}, {text: 'Página ' + pagina + ' de ' + total, alignment: 'right', margin: [0, 0, 28, 0]}], fontSize: 7, color: '#999999'}; },
                            content: [
                                {text: dados.titulo, style: 'titulo'},
                                {text: 'Período: ' + dados.periodo + '   ·   ' + dados.filtros + '   ·   Gerado em ' + dados.gerado_em, style: 'sub', margin: [0, 2, 0, 0]},
                                {columns: [
                                    {width: 'auto', stack: [{text: 'TOTAL DE HORAS', style: 'sub'}, {text: hhmm(dados.total_segundos) + ' h', fontSize: 18, bold: true}]},
                                    {width: 'auto', stack: [{text: 'APONTAMENTOS', style: 'sub'}, {text: String(dados.linhas.length), fontSize: 18, bold: true}]},
                                    {width: 'auto', stack: [{text: 'USUÁRIOS', style: 'sub'}, {text: String(dados.usuarios.length), fontSize: 18, bold: true}]}
                                ], columnGap: 40, margin: [0, 12, 0, 0]},
                                {text: 'Horas por usuário', style: 'secao'},
                                {table: {headerRows: 1, widths: ['*', 70, 60, 60], body: usuarios}, layout: linhasZebradas},
                                {text: 'Apontamentos', style: 'secao'},
                                {table: {headerRows: 1, widths: [44, 80, 90, '*', 52, 38, 150], body: detalhes}, layout: linhasZebradas}
                            ]
                        }).download(dados.arquivo + '.pdf');
                    });
            }
        };

        document.querySelectorAll('.js-exportar').forEach(function(button) {
            button.addEventListener('click', function() {
                var formato = button.getAttribute('data-formato');
                var texto = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Gerando...';
                exportar[formato]().catch(function() {
                    alert('Não foi possível gerar o arquivo. Verifique a conexão com a internet e tente novamente.');
                }).then(function() {
                    button.disabled = false;
                    button.innerHTML = texto;
                });
            });
        });
    })();

    window.addEventListener('load', function() {
        $('.multipleSelect').fastselect({placeholder: 'Todos'});
        if ($('#apont_datatable').length) {
            $('#apont_datatable').DataTable({
                paging: true, pageLength: 25, lengthChange: false, searching: true, ordering: true, info: true, autoWidth: false,
                order: [[0, 'desc']],
                columnDefs: [{orderable: false, targets: -1}],
                oLanguage: {
                    sZeroRecords: 'Nenhum apontamento encontrado', sInfo: 'Mostrando _START_ a _END_ de _TOTAL_ apontamentos',
                    sInfoEmpty: 'Nenhum apontamento', sInfoFiltered: '(filtrado de _MAX_)', sSearch: 'Buscar na tabela:',
                    oPaginate: {sFirst: 'Primeiro', sPrevious: 'Anterior', sNext: 'Próximo', sLast: 'Último'}
                }
            });
        }
    });
</script>
<style type="text/css">
    .apontamentos-toolbar { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 4px 12px; }
    .apontamentos-toolbar .form-group { margin-bottom: 4px; }
    .apontamentos-toolbar label { display: block; font-weight: 600; font-size: 12px; }
    .apontamentos-periodo .form-control { width: 150px; }
    .apontamentos-multi { min-width: 200px; flex: 1 1 200px; max-width: 280px; }
    .apontamentos-multi .fstElement { width: 100%; }
    .apontamentos-chamado .form-control { width: 90px; }
    .apontamentos-export { margin-left: auto; }
    .apontamentos-resumo-filtro { padding-top: 6px; padding-bottom: 8px; }
    .apontamentos-kpi { background: #fff; border-radius: 6px; box-shadow: 0 1px 2px rgba(0, 0, 0, .1); padding: 14px 16px; margin-bottom: 15px; }
    .apontamentos-kpi span { display: block; color: #777; font-size: 12px; text-transform: uppercase; letter-spacing: .4px; }
    .apontamentos-kpi strong { display: block; font-size: 26px; line-height: 1.2; }
    .apontamentos-kpi small { color: #999; }
    .apontamentos-ranking td { vertical-align: middle !important; border-top: 1px solid #f0f2f4 !important; }
    .apontamentos-ranking-nome small { display: block; color: #999; font-size: 11px; }
    .apontamentos-ranking-barra { width: 40%; }
    .apontamentos-ranking-barra .progress { margin: 0; }
    .apontamentos-ranking-horas { white-space: nowrap; font-weight: 600; width: 80px; }
    .apontamentos-table { border-collapse: separate; border-spacing: 0; }
    .apontamentos-table > thead > tr > th { border-bottom: 2px solid #d2d6de; background: #f4f6f8; }
    .apontamentos-table > tbody > tr > td { vertical-align: middle; border-top: 0; border-bottom: 1px solid #e3e6ea; }
    .apontamentos-table > tbody > tr:nth-child(even) > td { background-color: #f7f9fb; }
    .apontamentos-table > tbody > tr:hover > td { background-color: #eaf2f8; }
    .apontamentos-nowrap { white-space: nowrap; }
    .apontamentos-titulo { color: #555; }
    .apontamentos-obs { max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #666; }
    .apontamentos-modal-titulo { margin: 0; font-size: 18px; }
    .apontamentos-vazio { padding: 30px; margin: 0; }
</style>
