<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$editando = (int) $novidade->id > 0;
$campo_data = function ($valor) {
    $tempo = strtotime((string) $valor);
    return $tempo ? date('Y-m-d\TH:i', $tempo) : '';
};
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content cadastro-form novidade-form">
        <?php if (isset($message)) { echo $message; } ?>
        <?php echo form_open_multipart(current_url(), array('id' => 'form-novidade')); ?>
        <div class="row">
            <div class="col-md-8">

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">1</span>O que será informado</h3>
                        <span class="box-subtitle">Aparece para cada usuário em uma janela ao entrar no sistema.</span>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label for="titulo">Título <span class="text-red">*</span></label>
                            <input type="text" id="titulo" name="titulo" class="form-control" maxlength="150" placeholder="Ex.: Nova tela de apontamentos" value="<?php echo html_escape($novidade->titulo) ?>">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label for="conteudo">Texto <span class="text-red">*</span></label>
                            <textarea id="conteudo" name="conteudo" class="form-control" rows="9" maxlength="20000" placeholder="Explique a novidade: o que mudou, onde encontrar e como usar."><?php echo html_escape($novidade->conteudo) ?></textarea>
                            <p class="help-block">As quebras de linha são mantidas e endereços começando com http(s):// viram links.</p>
                        </div>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">2</span>Período</h3>
                        <span class="box-subtitle">A janela só aparece entre o início e o fim.</span>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label for="data_inicio">Início <span class="text-red">*</span></label>
                                <input type="datetime-local" id="data_inicio" name="data_inicio" class="form-control" value="<?php echo $campo_data($novidade->data_inicio) ?>">
                            </div>
                            <div class="col-sm-6 form-group">
                                <label for="data_fim">Fim <span class="text-red">*</span></label>
                                <input type="datetime-local" id="data_fim" name="data_fim" class="form-control" value="<?php echo $campo_data($novidade->data_fim) ?>">
                            </div>
                        </div>
                        <div class="novidade-atalhos">
                            <span class="text-muted small">Duração a partir do início:</span>
                            <button type="button" class="btn btn-default btn-xs" data-dias="1">1 dia</button>
                            <button type="button" class="btn btn-default btn-xs" data-dias="7">7 dias</button>
                            <button type="button" class="btn btn-default btn-xs" data-dias="15">15 dias</button>
                            <button type="button" class="btn btn-default btn-xs" data-dias="30">30 dias</button>
                        </div>
                        <label class="cadastro-opcao" style="margin-top:12px;">
                            <input type="checkbox" name="ativo" id="ativo" value="1" <?php echo (int) $novidade->ativo === 1 ? 'checked' : '' ?>>
                            <span><b>Ativa</b><br><span class="text-muted small">Desmarque para tirar do ar antes do fim do período, sem excluir.</span></span>
                        </label>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">3</span>Imagens <small class="text-muted">(opcional)</small></h3>
                        <span class="box-subtitle">Prints ou ilustrações da novidade. Até <?php echo (int) $max_imagens ?> imagens; acima de 5 MB são compactadas automaticamente.</span>
                    </div>
                    <div class="box-body">
                        <?php if ($imagens) { ?>
                            <div class="novidade-imagens">
                                <?php foreach ($imagens as $imagem) { ?>
                                    <label class="novidade-imagem" title="Marque para remover">
                                        <?php if ($imagem->url) { ?>
                                            <img src="<?php echo html_escape($imagem->url) ?>" alt="">
                                        <?php } else { ?>
                                            <span class="novidade-imagem-faltando"><i class="fa fa-chain-broken"></i> Arquivo não encontrado</span>
                                        <?php } ?>
                                        <span class="novidade-imagem-remover"><input type="checkbox" name="remover_imagens[]" value="<?php echo (int) $imagem->id ?>" class="js-remover-imagem"> Remover</span>
                                    </label>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <label class="novidade-drop" for="imagens" id="novidade-drop">
                            <i class="fa fa-cloud-upload"></i>
                            Arraste as imagens aqui ou <strong>clique para escolher</strong>
                            <input type="file" name="imagens[]" id="imagens" multiple accept="image/jpeg,image/png,image/gif,image/webp" class="hidden">
                        </label>
                        <ul class="novidade-novas" id="novidade-novas"></ul>
                    </div>
                </div>

            </div>

            <div class="col-md-4">
                <div class="sticky-column">
                    <div class="box box-default cadastro-resumo">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-list-alt"></i> Resumo</h3>
                        </div>
                        <div class="box-body">
                            <dl>
                                <dt>Situação no período</dt><dd id="resumo-situacao">-</dd>
                                <dt>Fica no ar</dt><dd id="resumo-duracao">-</dd>
                                <dt>Imagens</dt><dd id="resumo-imagens">-</dd>
                            </dl>
                            <?php if ($editando) { ?>
                                <label class="cadastro-opcao">
                                    <input type="checkbox" name="reexibir" value="1">
                                    <span><b>Mostrar de novo para todos</b><br><span class="text-muted small">Quem já fechou a janela vê a novidade outra vez (use ao mudar algo importante).</span></span>
                                </label>
                            <?php } ?>
                            <button type="button" class="btn btn-default btn-block" id="btn-previa" style="margin-top:10px;"><i class="fa fa-eye"></i> Pré-visualizar janela</button>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary btn-block" id="btn-salvar-novidade"><i class="fa fa-check"></i> <?php echo $editando ? 'Salvar alterações' : 'Cadastrar novidade' ?></button>
                            <?php echo anchor('novidades', lang('actions_cancel'), array('class' => 'btn btn-default btn-block')); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
    </section>
</div>
<script type="text/javascript">
    window.addEventListener('load', function() {
        var LIMITE = 5 * 1024 * 1024;
        var MAX = <?php echo (int) $max_imagens ?>;
        var existentes = <?php echo count($imagens) ?>;
        var input = document.getElementById('imagens');
        var novas = [];
        var podeAcumular = typeof DataTransfer !== 'undefined';

        function mb(bytes) {
            return (bytes / 1048576).toFixed(1).replace('.', ',') + ' MB';
        }

        function lerData(id) {
            var v = $('#' + id).val();
            return v ? new Date(v) : null;
        }

        function formatar(data) {
            var pad = function(n) { return ('0' + n).slice(-2); };
            return data.getFullYear() + '-' + pad(data.getMonth() + 1) + '-' + pad(data.getDate()) + 'T' + pad(data.getHours()) + ':' + pad(data.getMinutes());
        }

        function atualizarResumo() {
            var inicio = lerData('data_inicio'), fim = lerData('data_fim'), agora = new Date();
            var situacao = '-', duracao = '-';
            if (!$('#ativo').is(':checked')) {
                situacao = 'Inativa (não aparece)';
            } else if (inicio && fim) {
                if (fim <= inicio) { situacao = 'Fim antes do início'; }
                else if (inicio > agora) { situacao = 'Agendada'; }
                else if (fim < agora) { situacao = 'Encerrada'; }
                else { situacao = 'Vigente: aparece ao salvar'; }
            }
            if (inicio && fim && fim > inicio) {
                var dias = Math.round((fim - inicio) / 86400000);
                duracao = dias >= 1 ? dias + (dias === 1 ? ' dia' : ' dias') : Math.round((fim - inicio) / 3600000) + ' h';
            }
            var removidas = $('.js-remover-imagem:checked').length;
            var total = existentes - removidas + novas.length;
            $('#resumo-situacao').text(situacao).toggleClass('text-red', situacao === 'Fim antes do início');
            $('#resumo-duracao').text(duracao);
            $('#resumo-imagens').text(total ? total + (total === 1 ? ' imagem' : ' imagens') : 'Nenhuma').toggleClass('text-red', total > MAX);
        }

        $('.novidade-atalhos [data-dias]').on('click', function() {
            var inicio = lerData('data_inicio') || new Date();
            var fim = new Date(inicio.getTime() + parseInt($(this).data('dias'), 10) * 86400000);
            $('#data_fim').val(formatar(fim));
            atualizarResumo();
        });
        $('#form-novidade').on('change input', 'input, textarea', atualizarResumo);
        $('.js-remover-imagem').on('change', function() {
            $(this).closest('.novidade-imagem').toggleClass('is-removendo', this.checked);
        });

        // Imagens acima de 5 MB são reduzidas no navegador (o servidor também compacta, se precisar).
        function compactar(arquivo, pronto) {
            var img = new Image();
            img.onload = function() {
                var escala = Math.min(1, 3000 / Math.max(img.naturalWidth, img.naturalHeight)), qualidade = 0.85;
                var tentar = function() {
                    var canvas = document.createElement('canvas');
                    canvas.width = Math.max(1, Math.round(img.naturalWidth * escala));
                    canvas.height = Math.max(1, Math.round(img.naturalHeight * escala));
                    var ctx = canvas.getContext('2d');
                    ctx.fillStyle = '#fff';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    canvas.toBlob(function(blob) {
                        if (blob && blob.size <= LIMITE) {
                            pronto(new File([blob], arquivo.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' }));
                            return;
                        }
                        if (qualidade > 0.65) { qualidade -= 0.1; } else { escala *= 0.75; qualidade = 0.85; }
                        if (escala < 0.05) { pronto(arquivo); return; }
                        tentar();
                    }, 'image/jpeg', qualidade);
                };
                tentar();
            };
            img.onerror = function() { pronto(arquivo); };
            img.src = URL.createObjectURL(arquivo);
        }

        function sincronizar() {
            var lista = $('#novidade-novas').empty();
            $.each(novas, function(i, item) {
                $('<li>')
                    .append($('<img>').attr('src', URL.createObjectURL(item.arquivo)))
                    .append($('<span class="novidade-nova-nome">').text(item.arquivo.name))
                    .append($('<span class="text-muted small">').text(item.aviso || mb(item.arquivo.size)))
                    .append($('<button type="button" class="btn btn-box-tool" title="Tirar"><i class="fa fa-times"></i></button>').on('click', function() {
                        novas.splice(i, 1);
                        sincronizar();
                    }))
                    .appendTo(lista);
            });
            if (podeAcumular) {
                var transfer = new DataTransfer();
                $.each(novas, function(i, item) { transfer.items.add(item.arquivo); });
                input.files = transfer.files;
            }
            atualizarResumo();
        }

        function adicionar(arquivos) {
            if (!podeAcumular) {
                novas = [];
            }
            $.each(arquivos, function(i, arquivo) {
                if (!/^image\/(jpeg|png|gif|webp)$/.test(arquivo.type)) { return; }
                if (arquivo.size <= LIMITE || !HTMLCanvasElement.prototype.toBlob) {
                    novas.push({ arquivo: arquivo });
                    sincronizar();
                    return;
                }
                compactar(arquivo, function(final) {
                    novas.push({ arquivo: final, aviso: final !== arquivo ? 'compactada de ' + mb(arquivo.size) + ' para ' + mb(final.size) : null });
                    sincronizar();
                });
            });
        }

        $(input).on('change', function() {
            adicionar(Array.prototype.slice.call(this.files));
        });
        $('#novidade-drop')
            .on('dragenter dragover', function(e) { e.preventDefault(); $(this).addClass('is-dragover'); })
            .on('dragleave drop', function(e) {
                e.preventDefault();
                $(this).removeClass('is-dragover');
                if (e.type === 'drop' && podeAcumular) {
                    adicionar(Array.prototype.slice.call(e.originalEvent.dataTransfer.files));
                }
            });

        $('#btn-previa').on('click', function() {
            if (typeof window.mostrarNovidade !== 'function') { return; }
            var escapar = function(t) { return $('<div>').text(t).html(); };
            var imagens = [];
            $('.novidade-imagem').not('.is-removendo').find('img').each(function() { imagens.push({ url: this.src, original: this.src }); });
            $.each(novas, function(i, item) { var u = URL.createObjectURL(item.arquivo); imagens.push({ url: u, original: u }); });
            window.mostrarNovidade({
                titulo: $.trim($('#titulo').val()) || 'Título da novidade',
                conteudo_html: escapar($('#conteudo').val() || 'Texto da novidade.').replace(/\n/g, '<br>'),
                periodo: 'Pré-visualização',
                imagens: imagens
            }, { previa: true });
        });

        $('#form-novidade').on('submit', function(e) {
            var inicio = lerData('data_inicio'), fim = lerData('data_fim');
            var total = existentes - $('.js-remover-imagem:checked').length + novas.length;
            if (!$.trim($('#titulo').val()) || !$.trim($('#conteudo').val()) || !inicio || !fim || fim <= inicio || total > MAX) {
                e.preventDefault();
                atualizarResumo();
                window.alert(total > MAX ? 'Limite de ' + MAX + ' imagens por novidade.' : 'Preencha título, texto e um período com o fim depois do início.');
                return;
            }
            $('#btn-salvar-novidade').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Salvando...');
        });

        atualizarResumo();
    });
</script>
<style type="text/css">
    .novidade-form #conteudo { resize: vertical; min-height: 180px; }
    .novidade-atalhos { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; }
    .novidade-imagens { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; margin-bottom: 12px; }
    .novidade-imagem { position: relative; margin: 0; border: 1px solid #d2d6de; border-radius: 6px; overflow: hidden; cursor: pointer; font-weight: 400 !important; }
    .novidade-imagem img { display: block; width: 100%; height: 110px; object-fit: cover; }
    .novidade-imagem-faltando { display: flex; align-items: center; justify-content: center; height: 110px; color: #999; font-size: 12px; }
    .novidade-imagem-remover { display: block; padding: 5px 8px; font-size: 12px; background: #f7f9fb; border-top: 1px solid #d2d6de; }
    .novidade-imagem.is-removendo { border-color: #dd4b39; }
    .novidade-imagem.is-removendo img { opacity: .35; }
    .novidade-drop { display: block; padding: 22px 16px; border: 2px dashed #c9d3dd; border-radius: 6px; background: #f9fbfc; color: #667; text-align: center; cursor: pointer; font-weight: 400 !important; }
    .novidade-drop:hover, .novidade-drop.is-dragover { border-color: #3c8dbc; background: #eef5fa; }
    .novidade-drop .fa { display: block; margin-bottom: 6px; font-size: 26px; color: #3c8dbc; }
    .novidade-drop strong { color: #3c8dbc; }
    .novidade-novas { margin: 10px 0 0; padding: 0; list-style: none; }
    .novidade-novas li { display: flex; align-items: center; gap: 10px; padding: 6px 8px; border: 1px solid #eee; border-radius: 4px; margin-bottom: 4px; }
    .novidade-novas img { width: 48px; height: 36px; object-fit: cover; border-radius: 3px; }
    .novidade-nova-nome { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    body.dark-mode .novidade-imagem, body.dark-mode .novidade-imagem-remover, body.dark-mode .novidade-novas li { border-color: #2d2d2d !important; }
    body.dark-mode .novidade-imagem-remover { background: #232323 !important; }
    body.dark-mode .novidade-drop { background: #181818 !important; border-color: #333333 !important; color: #9ca3af !important; }
    body.dark-mode .novidade-drop:hover, body.dark-mode .novidade-drop.is-dragover { background: #1c2626 !important; border-color: #3c8dbc !important; }
</style>
<?php $this->load->view('admin/_partials/cadastro_estilos'); ?>
