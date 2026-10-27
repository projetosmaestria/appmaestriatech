<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Filtro no navegador para listagens pequenas: #lista-q busca em data-busca, #lista-status e #lista-filtro-* comparam
// com data-status / data-<nome> das linhas de #lista-tabela. Links .js-confirmar pedem confirmação.
?>
<script type="text/javascript">
    window.addEventListener('load', function() {
        var linhas = $('#lista-tabela tbody tr');

        function filtrar() {
            var termo = $.trim($('#lista-q').val() || '').toLowerCase();
            var filtros = [];
            if ($('#lista-status').length) { filtros.push(['status', $('#lista-status').val()]); }
            $('[id^="lista-filtro-"]').each(function() { filtros.push([this.id.replace('lista-filtro-', ''), $(this).val()]); });
            var visiveis = 0;
            linhas.each(function() {
                var linha = $(this);
                var mostra = !termo || String(linha.data('busca')).indexOf(termo) > -1;
                $.each(filtros, function(i, filtro) {
                    if (mostra && filtro[1] !== '' && filtro[1] !== null) {
                        mostra = (' ' + String(linha.attr('data-' + filtro[0])) + ' ').indexOf(' ' + filtro[1] + ' ') > -1;
                    }
                });
                linha.toggle(mostra);
                if (mostra) { visiveis++; }
            });
            $('#lista-vazio').toggle(visiveis === 0);
        }

        $('#lista-q').on('input', filtrar);
        $('#lista-status, [id^="lista-filtro-"]').on('change', filtrar);
        $('.js-status-kpi').on('click', function(event) {
            event.preventDefault();
            $('#lista-status').val(String($(this).data('status')));
            filtrar();
        });
        $('#lista-tabela').on('click', '.js-confirmar', function(event) {
            if (!window.confirm($(this).data('confirmar'))) { event.preventDefault(); }
        });
        filtrar();
    });
</script>
