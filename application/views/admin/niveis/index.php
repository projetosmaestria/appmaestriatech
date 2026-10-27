<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Mesma regra do planejamento da equipe e do cronograma: o número do nível define as horas previstas.
$horas_nivel = array(1 => 2, 2 => 4, 3 => 8, 4 => 16);
$total = count($niveis);
$ativos = count(array_filter($niveis, function ($nivel) { return (int) $nivel->actived === 1; }));
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if (isset($message)) { echo $message; } ?>
        <div class="box">
            <div class="box-header with-border niveis-toolbar">
                <?php if ($can_create) { ?>
                    <?php echo anchor('niveis/create', '<i class="fa fa-plus"></i> ' . lang('niveis_create'), array('class' => 'btn btn-primary')); ?>
                <?php } ?>
                <div class="input-group niveis-search">
                    <input type="search" id="niveis-busca" class="form-control" placeholder="Buscar nível...">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                </div>
                <select id="niveis-status" class="form-control niveis-filter" title="Status">
                    <option value="1">Ativos</option>
                    <option value="0">Inativos</option>
                    <option value="">Todos os status</option>
                </select>
                <span class="text-muted niveis-count"><?php echo (int) $ativos ?> ativo<?php echo $ativos === 1 ? '' : 's' ?> de <?php echo (int) $total ?></span>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover niveis-table">
                    <thead>
                        <tr>
                            <th><?php echo lang('descricao'); ?></th>
                            <th title="Horas previstas usadas no planejamento da equipe e no cronograma">Estimativa no planejamento</th>
                            <th class="text-right">Chamados</th>
                            <th><?php echo lang('status'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($niveis as $values) {
                            $ativo = (int) $values->actived === 1;
                            $numero = (int) preg_replace('/\D/', '', (string) $values->descricao);
                            $horas = isset($horas_nivel[$numero]) ? $horas_nivel[$numero] : null;
                            $uso = isset($uso_niveis[(int) $values->id]) ? $uso_niveis[(int) $values->id] : 0;
                            $cor = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $values->cor) ? $values->cor : '#9aa5b1';
                        ?>
                            <tr data-status="<?php echo $ativo ? 1 : 0 ?>" data-busca="<?php echo html_escape(mb_strtolower($values->descricao, 'UTF-8')) ?>" style="border-left-color: <?php echo $cor ?>;">
                                <td>
                                    <span class="niveis-swatch" style="background: <?php echo $cor ?>;"></span>
                                    <span class="niveis-title"><?php echo html_escape($values->descricao); ?></span>
                                </td>
                                <td><?php echo $horas !== null ? $horas . ' h' : '<span class="text-muted" title="Sem número de 1 a 4 na descrição: o planejamento estima 0 h">—</span>'; ?></td>
                                <td class="text-right"><?php echo $uso ? number_format($uso, 0, ',', '.') : '<span class="text-muted">0</span>'; ?></td>
                                <td>
                                    <?php if ($ativo) { ?>
                                        <?php echo anchor('niveis/deactivate/' . $values->id, '<span class="label label-success">' . lang('actions_ativo') . '</span>', array('title' => 'Clique para desativar', 'class' => 'js-confirm-status', 'data-acao' => 'desativar', 'data-uso' => $uso)); ?>
                                    <?php } else { ?>
                                        <?php echo anchor('niveis/activate/' . $values->id, '<span class="label label-default">' . lang('actions_inativo') . '</span>', array('title' => 'Clique para ativar', 'class' => 'js-confirm-status', 'data-acao' => 'ativar', 'data-uso' => 0)); ?>
                                    <?php } ?>
                                </td>
                                <td class="text-right">
                                    <?php if ($can_edit) { ?>
                                        <?php echo anchor('niveis/edit/' . $values->id, '<i class="fa fa-edit"></i> Editar', array('class' => 'btn btn-default btn-sm')); ?>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr class="niveis-empty"<?php echo $total ? ' style="display:none;"' : '' ?>>
                            <td colspan="5" class="text-center text-muted">Nenhum nível encontrado com os filtros atuais.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="box-footer text-muted small">
                A estimativa usa o número da descrição: Nível 1 = 2 h, Nível 2 = 4 h, Nível 3 = 8 h e Nível 4 = 16 h.
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    (function() {
        var search = document.getElementById('niveis-busca');
        var status = document.getElementById('niveis-status');
        var rows = document.querySelectorAll('.niveis-table tbody tr[data-status]');
        var empty = document.querySelector('.niveis-empty');

        function apply() {
            var term = search.value.trim().toLowerCase();
            var visible = 0;
            rows.forEach(function(row) {
                var show = (!term || row.getAttribute('data-busca').indexOf(term) !== -1) &&
                    (status.value === '' || row.getAttribute('data-status') === status.value);
                row.style.display = show ? '' : 'none';
                visible += show ? 1 : 0;
            });
            empty.style.display = visible ? 'none' : '';
        }

        search.addEventListener('input', apply);
        status.addEventListener('change', apply);
        document.querySelectorAll('.js-confirm-status').forEach(function(link) {
            link.addEventListener('click', function(event) {
                var uso = parseInt(link.getAttribute('data-uso'), 10) || 0;
                var aviso = 'Deseja ' + link.getAttribute('data-acao') + ' este nível?';
                if (uso > 0) {
                    aviso += '\n' + uso + ' chamado(s) usam este nível e continuarão com ele.';
                }
                if (!window.confirm(aviso)) {
                    event.preventDefault();
                }
            });
        });
        apply();
    })();
</script>
<style type="text/css">
    .niveis-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .niveis-search { flex: 1 1 260px; max-width: 420px; }
    .niveis-filter { width: auto; min-width: 170px; }
    .niveis-count { margin-left: auto; font-size: 13px; }
    .niveis-table { border-collapse: separate; border-spacing: 0; }
    .niveis-table > thead > tr > th { border-bottom: 2px solid #d2d6de; background: #f4f6f8; }
    .niveis-table > tbody > tr > td { vertical-align: middle; padding-top: 12px; padding-bottom: 12px; border-top: 0; border-bottom: 1px solid #d2d6de; }
    .niveis-table > tbody > tr > td:first-child { border-left: 5px solid; border-left-color: inherit; }
    .niveis-table > tbody > tr:nth-child(even) > td { background-color: #f7f9fb; }
    .niveis-table > tbody > tr:hover > td { background-color: #eaf2f8; }
    .niveis-swatch { display: inline-block; width: 14px; height: 14px; border-radius: 3px; margin-right: 8px; vertical-align: -2px; border: 1px solid rgba(0, 0, 0, .1); }
    .niveis-title { font-weight: 600; font-size: 14px; }
    .niveis-empty td { padding: 30px !important; }
</style>
