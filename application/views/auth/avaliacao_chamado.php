<?php defined('BASEPATH') OR exit('No direct script access allowed');
$c = $chamado->row();
$solicitante = trim($c->first_name_dono . ' ' . $c->last_name_dono);
$responsavel = trim($c->first_name . ' ' . $c->last_name);
$caminho = array_filter(array($c->produto_desc, $c->modulo_desc, $c->catalogo_desc));
$dicas = array(
    'atendimento' => 'A equipe foi cordial, clara e atenciosa?',
    'prazo' => 'O chamado foi resolvido no tempo esperado?',
    'satisfação' => 'Você ficou satisfeito com a solução entregue?',
    'satisfacao' => 'Você ficou satisfeito com a solução entregue?',
);
?>
<style>
    html { background: #e2e8f0; }
    body { font-family: 'Inter', 'Source Sans Pro', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: radial-gradient(120% 120% at 50% 10%, #f8fafc 0%, #edf2f7 50%, #e2e8f0 100%) fixed; min-height: 100vh; color: #1e293b; }
    .aval-wrap { max-width: 560px; margin: 0 auto; padding: 40px 16px; }
    .aval-card { background: #ffffff; border-radius: 16px; box-shadow: 0 10px 40px rgba(15, 23, 42, .08); overflow: hidden; }
    .aval-head { text-align: center; padding: 28px 28px 18px; }
    .aval-head img { width: 180px; max-width: 70%; height: auto; }
    .aval-head h1 { margin: 18px 0 6px; font-size: 22px; font-weight: 700; color: #0f172a; }
    .aval-head p { margin: 0; color: #64748b; font-size: 14px; }
    .aval-ticket { margin: 0 28px; padding: 14px 16px; border: 1px solid #e2e8f0; border-left: 4px solid #516480; border-radius: 10px; background: #f8fafc; }
    .aval-ticket-id { font-size: 12px; color: #64748b; font-weight: 600; letter-spacing: .3px; }
    .aval-ticket-title { margin: 4px 0 6px; font-size: 16px; font-weight: 600; color: #0f172a; }
    .aval-ticket-meta { font-size: 13px; color: #64748b; line-height: 1.6; }
    .aval-body { padding: 20px 28px 8px; }
    .aval-alert { margin: 0 0 16px; padding: 10px 14px; border-radius: 8px; background: #fef2f2; color: #991b1b; font-size: 14px; }
    .aval-question { padding: 16px 0; border-bottom: 1px solid #f1f5f9; }
    .aval-question:last-of-type { border-bottom: 0; }
    .aval-question-title { font-weight: 600; font-size: 15px; color: #0f172a; }
    .aval-question-hint { font-size: 13px; color: #64748b; margin: 2px 0 10px; }
    .aval-stars { display: inline-flex; flex-direction: row-reverse; gap: 4px; }
    .aval-stars input { position: absolute; opacity: 0; pointer-events: none; }
    .aval-stars label { cursor: pointer; font-size: 34px; line-height: 1; color: #cbd5e1; transition: color .1s, transform .1s; margin: 0; }
    .aval-stars label:hover { transform: scale(1.12); }
    .aval-stars input:checked ~ label,
    .aval-stars label:hover,
    .aval-stars label:hover ~ label { color: #f59e0b; }
    .aval-stars input:focus-visible + label { outline: 2px solid #516480; outline-offset: 2px; border-radius: 4px; }
    .aval-score { display: inline-block; margin-left: 12px; vertical-align: 8px; font-size: 13px; font-weight: 600; color: #94a3b8; }
    .aval-score.is-set { color: #b45309; }
    .aval-comment { padding: 6px 0 4px; }
    .aval-comment label { font-weight: 600; font-size: 15px; color: #0f172a; }
    .aval-comment small { color: #94a3b8; font-weight: 400; }
    .aval-comment textarea { width: 100%; margin-top: 8px; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 14px; resize: vertical; min-height: 90px; }
    .aval-comment textarea:focus { outline: none; border-color: #516480; box-shadow: 0 0 0 3px rgba(81, 100, 128, .15); }
    .aval-footer { padding: 16px 28px 26px; }
    .aval-progress { font-size: 13px; color: #64748b; margin-bottom: 10px; text-align: center; }
    .aval-submit { width: 100%; padding: 13px; border: 0; border-radius: 10px; background: linear-gradient(135deg, #375265 0%, #516480 100%); color: #ffffff; font-size: 15px; font-weight: 600; cursor: pointer; transition: opacity .15s; }
    .aval-submit:disabled { opacity: .45; cursor: not-allowed; }
    .aval-note { margin-top: 12px; text-align: center; font-size: 12px; color: #94a3b8; }
</style>

<div class="aval-wrap">
    <div class="aval-card">
        <div class="aval-head">
            <img src="<?php echo base_url('assets/images/xkey-chamados.png') ?>" alt="XKEY Chamados">
            <h1>Como foi o seu atendimento?</h1>
            <p>Olá<?php echo $solicitante !== '' ? ', ' . html_escape(strtok($solicitante, ' ')) : '' ?>! Sua opinião leva menos de um minuto e nos ajuda a melhorar.</p>
        </div>

        <div class="aval-ticket">
            <div class="aval-ticket-id">CHAMADO #<?php echo (int) $c->id ?> · CONCLUÍDO EM <?php echo date('d/m/Y', strtotime($c->data_finalizacao)) ?></div>
            <div class="aval-ticket-title"><?php echo html_escape($c->titulo) ?></div>
            <div class="aval-ticket-meta">
                <?php echo html_escape($c->cliente) ?>
                <?php if ($caminho) { ?><br><?php echo html_escape(implode(' › ', $caminho)) ?><?php } ?>
                <?php if ($responsavel !== '') { ?><br>Atendido por <strong><?php echo html_escape($responsavel) ?></strong><?php } ?>
            </div>
        </div>

        <form id="avaliacao_chamado_form" action="<?php echo base_url('auth/avaliacao_chamado/' . (int) $c->dono_id . '/' . $c->hash) ?>" method="post">
            <div class="aval-body">
                <?php if (!empty($message)) { ?>
                    <div class="aval-alert"><?php echo strip_tags($message) ?></div>
                <?php } ?>

                <?php foreach ($avaliacoes as $pergunta) {
                    $chave = mb_strtolower(trim($pergunta['descricao']), 'UTF-8');
                    $atual = isset($respostas[$pergunta['id']]) ? (int) $respostas[$pergunta['id']] : 0;
                ?>
                    <div class="aval-question" data-question>
                        <div class="aval-question-title"><?php echo html_escape($pergunta['descricao']) ?></div>
                        <?php if (isset($dicas[$chave])) { ?><div class="aval-question-hint"><?php echo $dicas[$chave] ?></div><?php } ?>
                        <div class="aval-stars" role="radiogroup" aria-label="<?php echo html_escape($pergunta['descricao']) ?>">
                            <?php for ($nota = 5; $nota >= 1; $nota--) { ?>
                                <input type="radio" id="star-<?php echo (int) $pergunta['id'] ?>-<?php echo $nota ?>" name="star-<?php echo (int) $pergunta['id'] ?>" value="<?php echo $nota ?>" <?php echo $atual === $nota ? 'checked' : '' ?>>
                                <label for="star-<?php echo (int) $pergunta['id'] ?>-<?php echo $nota ?>" title="<?php echo $nota ?> de 5">&#9733;</label>
                            <?php } ?>
                        </div>
                        <span class="aval-score" data-score>Escolha de 1 a 5</span>
                    </div>
                <?php } ?>

                <div class="aval-comment">
                    <label for="comentario">Quer deixar um comentário? <small>(opcional)</small></label>
                    <textarea id="comentario" name="comentario" maxlength="2000" placeholder="Conte o que funcionou bem ou o que podemos melhorar."><?php echo html_escape($comentario) ?></textarea>
                </div>
            </div>

            <div class="aval-footer">
                <div class="aval-progress" id="aval-progress"></div>
                <button type="submit" class="aval-submit" id="btn_avaliar">Enviar avaliação</button>
                <div class="aval-note">Você pode avaliar até <?php echo $prazo_avaliacao ?>. O comentário fica registrado no chamado.</div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    (function() {
        var labels = ['', 'Péssimo', 'Ruim', 'Regular', 'Bom', 'Excelente'];
        var questions = document.querySelectorAll('[data-question]');
        var button = document.getElementById('btn_avaliar');
        var progress = document.getElementById('aval-progress');

        function refresh() {
            var answered = 0;
            questions.forEach(function(question) {
                var checked = question.querySelector('input:checked');
                var score = question.querySelector('[data-score]');
                if (checked) {
                    answered++;
                    score.textContent = checked.value + ' · ' + labels[checked.value];
                    score.classList.add('is-set');
                } else {
                    score.textContent = 'Escolha de 1 a 5';
                    score.classList.remove('is-set');
                }
            });
            var missing = questions.length - answered;
            button.disabled = missing > 0;
            progress.textContent = missing > 0
                ? (missing === 1 ? 'Falta 1 pergunta para enviar.' : 'Faltam ' + missing + ' perguntas para enviar.')
                : 'Tudo pronto! Obrigado pela atenção.';
        }

        document.getElementById('avaliacao_chamado_form').addEventListener('change', refresh);
        document.getElementById('avaliacao_chamado_form').addEventListener('submit', function() {
            button.disabled = true;
            button.textContent = 'Enviando...';
        });
        refresh();
    })();
</script>
