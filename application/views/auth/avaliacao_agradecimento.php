<?php defined('BASEPATH') OR exit('No direct script access allowed');
$c = $chamado->row();
$media = isset($media_avaliacao) ? (float) $media_avaliacao : 0;
$estrelas = (int) round($media);
?>
<style>
    html { background: #e2e8f0; }
    body { font-family: 'Inter', 'Source Sans Pro', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: radial-gradient(120% 120% at 50% 10%, #f8fafc 0%, #edf2f7 50%, #e2e8f0 100%) fixed; min-height: 100vh; color: #1e293b; }
    .aval-wrap { max-width: 480px; margin: 0 auto; padding: 60px 16px; }
    .aval-card { background: #ffffff; border-radius: 16px; box-shadow: 0 10px 40px rgba(15, 23, 42, .08); padding: 32px 28px; text-align: center; }
    .aval-card img { width: 160px; max-width: 60%; height: auto; }
    .aval-check { width: 64px; height: 64px; margin: 22px auto 14px; border-radius: 50%; background: #f0fdf4; color: #16a34a; font-size: 32px; line-height: 64px; }
    .aval-card h1 { margin: 0 0 8px; font-size: 22px; font-weight: 700; color: #0f172a; }
    .aval-card p { margin: 0 0 6px; color: #64748b; font-size: 14px; line-height: 1.6; }
    .aval-stars { margin: 14px 0 4px; font-size: 28px; letter-spacing: 2px; }
    .aval-stars .on { color: #f59e0b; }
    .aval-stars .off { color: #cbd5e1; }
    .aval-media { font-size: 13px; color: #94a3b8; }
    .aval-btn { display: inline-block; margin-top: 22px; padding: 12px 22px; border-radius: 10px; background: linear-gradient(135deg, #375265 0%, #516480 100%); color: #ffffff !important; font-weight: 600; text-decoration: none !important; }
</style>

<div class="aval-wrap">
    <div class="aval-card">
        <img src="<?php echo base_url('assets/images/xkey-chamados.png') ?>" alt="XKEY Chamados">
        <div class="aval-check">&#10003;</div>
        <h1>Obrigado pela avaliação!</h1>
        <p>Recebemos sua opinião sobre o chamado <strong>#<?php echo (int) $c->id ?></strong> — <?php echo html_escape($c->titulo) ?>.</p>
        <?php if ($estrelas > 0) { ?>
            <div class="aval-stars" aria-label="Média <?php echo number_format($media, 1, ',', '') ?> de 5">
                <?php for ($i = 1; $i <= 5; $i++) { ?><span class="<?php echo $i <= $estrelas ? 'on' : 'off' ?>">&#9733;</span><?php } ?>
            </div>
            <div class="aval-media">Sua média: <?php echo number_format($media, 1, ',', '') ?> de 5</div>
        <?php } ?>
        <p style="margin-top:14px;">Ela ajuda nossa equipe a melhorar cada atendimento.</p>
        <a href="<?php echo base_url('auth/login') ?>" class="aval-btn">Acessar a central de chamados</a>
    </div>
</div>
