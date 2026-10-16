<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.1.2/css/star-rating.min.css">

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body, html {
        height: 100%;
        overflow-x: hidden;
    }

    .main-row {
        min-height: 100vh !important;
        background: linear-gradient(135deg, #1E1D3F 0%, #2a294f 100%) !important;
        position: relative !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        animation: gradientShift 10s ease infinite;
        background-size: 200% 200% !important;
        margin: 0 !important;
        padding: 20px !important;
    }

    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    .floating-tech {
        position: fixed;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
        top: 0;
        left: 0;
    }

    .tech-icon {
        position: absolute;
        color: rgba(156, 215, 212, 0.15);
        font-size: clamp(1.5rem, 5vw, 2.5rem);
        animation: float 6s ease-in-out infinite;
    }

    .tech-icon:nth-child(1) { top: 10%; left: 5%; animation-delay: 0s; }
    .tech-icon:nth-child(2) { top: 70%; left: 10%; animation-delay: 1s; }
    .tech-icon:nth-child(3) { top: 20%; right: 5%; animation-delay: 2s; }
    .tech-icon:nth-child(4) { bottom: 15%; right: 10%; animation-delay: 1.5s; }
    .tech-icon:nth-child(5) { top: 40%; left: 15%; animation-delay: 2.5s; }
    .tech-icon:nth-child(6) { bottom: 25%; left: 8%; animation-delay: 0.8s; }
    .tech-icon:nth-child(7) { top: 60%; right: 15%; animation-delay: 1.2s; }
    .tech-icon:nth-child(8) { bottom: 10%; right: 5%; animation-delay: 2.2s; }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(5deg); }
    }

    .main-container {
        position: relative !important;
        z-index: 10 !important;
        width: 100% !important;
        max-width: 550px !important;
        margin: 0 auto !important;
    }

    .avaliacao-card {
        background: #ffffff !important;
        border-radius: 32px !important;
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3) !important;
        padding: 40px 35px !important;
        width: 100% !important;
        animation: slideUp 0.6s ease-out !important;
        border: 1px solid rgba(156, 215, 212, 0.2) !important;
        position: relative !important;
        overflow: hidden !important;
    }


    .avaliacao-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #1E1D3F, #9CD7D4, #1E1D3F, #9CD7D4);
        animation: borderGlow 3s linear infinite;
        background-size: 300% 100%;
        z-index: 1;
    }

    @keyframes borderGlow {
        0% { background-position: 0% 0%; }
        100% { background-position: 300% 0%; }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .avaliacao-header {
        text-align: center !important;
        margin-bottom: 30px !important;
    }

    .avaliacao-logo {
        margin-bottom: 20px !important;
    }

    .avaliacao-logo img {
        max-width: 140px !important;
        height: auto !important;
        transition: transform 0.3s ease !important;
    }

    .avaliacao-logo img:hover {
        transform: scale(1.05);
    }

    .avaliacao-title {
        font-size: 26px !important;
        font-weight: 600 !important;
        color: #1E1D3F !important;
        margin-bottom: 10px !important;
        letter-spacing: -0.5px !important;
    }

    .avaliacao-title span {
        background: linear-gradient(135deg, #1E1D3F, #9CD7D4) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
        font-weight: 700 !important;
    }

    .chamado-titulo {
        background: rgba(156, 215, 212, 0.1) !important;
        border: 1px solid rgba(156, 215, 212, 0.3) !important;
        border-radius: 12px !important;
        padding: 15px 20px !important;
        margin: 20px 0 !important;
        text-align: left !important;
    }

    .chamado-titulo p {
        margin: 0 !important;
        color: #4b5563 !important;
        font-size: 15px !important;
    }

    .chamado-titulo strong {
        color: #1E1D3F !important;
        font-weight: 600 !important;
    }

    .avaliacao-body {
        margin: 25px 0 !important;
    }

    .pergunta-item {
        background: #f8fafc !important;
        border: 2px solid #e2e8f0 !important;
        border-radius: 16px !important;
        padding: 20px !important;
        margin-bottom: 15px !important;
        transition: all 0.3s ease !important;
    }

    .pergunta-item:hover {
        border-color: #9CD7D4 !important;
        box-shadow: 0 0 0 4px rgba(156, 215, 212, 0.1) !important;
    }

    .pergunta-item label {
        display: block !important;
        margin-bottom: 15px !important;
        color: #1E1D3F !important;
        font-weight: 600 !important;
        font-size: 15px !important;
    }

    .rating-container {
        margin: 10px 0 !important;
    }

    .rating-stars {
        color: #9CD7D4 !important;
    }

    .rating-stars .fa-star {
        font-size: 24px !important;
        margin-right: 5px !important;
    }

    .rating-stars .fa-star.rated {
        color: #1E1D3F !important;
    }

    .btn-avaliar {
        width: 100% !important;
        height: 56px !important;
        background: #1E1D3F !important;
        color: white !important;
        border: none !important;
        border-radius: 16px !important;
        font-size: 16px !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 8px 20px rgba(30, 29, 63, 0.3) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        letter-spacing: 0.5px !important;
        margin-top: 20px !important;
        text-transform: uppercase !important;
        gap: 10px !important;
    }

    .btn-avaliar:hover {
        background: #2a294f !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 12px 25px rgba(156, 215, 212, 0.3) !important;
    }

    .btn-avaliar:active {
        transform: translateY(0) !important;
    }

    .btn-avaliar i {
        font-size: 18px !important;
        transition: transform 0.3s ease !important;
    }

    .btn-avaliar:hover i {
        transform: translateX(5px) !important;
    }

    .btn-voltar {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        color: #64748b !important;
        text-decoration: none !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        transition: color 0.2s ease !important;
        margin-top: 20px !important;
    }

    .btn-voltar:hover {
        color: #1E1D3F !important;
        text-decoration: none !important;
    }

    .btn-voltar i {
        font-size: 14px !important;
        transition: transform 0.2s ease !important;
    }

    .btn-voltar:hover i {
        transform: translateX(-3px) !important;
    }

    .modal-content {
        border-radius: 24px !important;
        border: none !important;
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3) !important;
        overflow: hidden !important;
    }

    .modal-header {
        background: linear-gradient(135deg, #1E1D3F, #2a294f) !important;
        color: white !important;
        padding: 20px 25px !important;
        border: none !important;
        position: relative !important;
    }

    .modal-header .close {
        color: white !important;
        opacity: 0.8 !important;
        text-shadow: none !important;
    }

    .modal-header .close:hover {
        opacity: 1 !important;
    }

    .modal-title {
        font-weight: 600 !important;
        font-size: 18px !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
    }

    .modal-title i {
        color: #9CD7D4 !important;
        font-size: 20px !important;
    }

    .modal-body {
        padding: 25px !important;
        text-align: center !important;
    }

    .modal-body i {
        font-size: 50px !important;
        color: #9CD7D4 !important;
        margin-bottom: 15px !important;
    }

    .modal-body p {
        color: #4b5563 !important;
        font-size: 16px !important;
        line-height: 1.6 !important;
        margin: 0 !important;
    }

    .modal-footer {
        padding: 20px 25px !important;
        border-top: 1px solid #e2e8f0 !important;
        display: flex !important;
        justify-content: center !important;
        gap: 10px !important;
    }

    .modal-btn {
        padding: 10px 30px !important;
        border-radius: 12px !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
        border: none !important;
        cursor: pointer !important;
    }

    .modal-btn-primary {
        background: #1E1D3F !important;
        color: white !important;
    }

    .modal-btn-primary:hover {
        background: #2a294f !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 5px 15px rgba(30, 29, 63, 0.3) !important;
    }

    .modal-btn-default {
        background: #e2e8f0 !important;
        color: #4b5563 !important;
    }

    .modal-btn-default:hover {
        background: #cbd5e1 !important;
        transform: translateY(-2px) !important;
    }

    .error-card {
        background: #ffffff !important;
        border-radius: 32px !important;
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3) !important;
        padding: 50px 40px !important;
        width: 100% !important;
        text-align: center !important;
        position: relative !important;
        overflow: hidden !important;
    }

    .error-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #ef4444, #f59e0b, #ef4444);
        animation: errorGlow 3s linear infinite;
        background-size: 300% 100%;
    }

    @keyframes errorGlow {
        0% { background-position: 0% 0%; }
        100% { background-position: 300% 0%; }
    }

    .error-icon {
        font-size: 60px !important;
        color: #ef4444 !important;
        margin-bottom: 20px !important;
        animation: shake 0.5s ease !important;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }

    .error-title {
        font-size: 28px !important;
        font-weight: 600 !important;
        color: #1E1D3F !important;
        margin-bottom: 15px !important;
    }

    .error-message {
        color: #6b7280 !important;
        font-size: 16px !important;
        margin-bottom: 30px !important;
    }

    /* ===== RESPONSIVO ===== */
    @media (max-width: 768px) {
        .main-row {
            padding: 0 !important;
        }

        .main-container {
            max-width: 100% !important;
            padding: 15px !important;
        }

        .avaliacao-card, .error-card {
            border-radius: 28px !important;
            padding: 30px 25px !important;
        }

        .avaliacao-card::before, .error-card::before {
            height: 6px !important;
        }

        .avaliacao-title {
            font-size: 24px !important;
        }

        .pergunta-item {
            padding: 15px !important;
        }

        .rating-stars .fa-star {
            font-size: 22px !important;
        }

        .btn-avaliar {
            height: 52px !important;
            font-size: 15px !important;
        }

        .tech-icon {
            opacity: 0.08 !important;
        }
    }

    @media (max-width: 380px) {
        .avaliacao-card, .error-card {
            padding: 25px 20px !important;
        }

        .avaliacao-title {
            font-size: 22px !important;
        }

        .pergunta-item label {
            font-size: 14px !important;
        }

        .rating-stars .fa-star {
            font-size: 20px !important;
        }

        .btn-avaliar {
            height: 48px !important;
            font-size: 14px !important;
        }
    }
</style>

<div class="row main-row fill">
    <!-- Elementos flutuantes -->
    <div class="floating-tech">
        <div class="tech-icon"><i class="fas fa-code"></i></div>
        <div class="tech-icon"><i class="fas fa-laptop-code"></i></div>
        <div class="tech-icon"><i class="fas fa-cloud"></i></div>
        <div class="tech-icon"><i class="fas fa-shield-alt"></i></div>
        <div class="tech-icon"><i class="fas fa-database"></i></div>
        <div class="tech-icon"><i class="fas fa-network-wired"></i></div>
        <div class="tech-icon"><i class="fas fa-cog"></i></div>
        <div class="tech-icon"><i class="fas fa-bolt"></i></div>
    </div>

    <div class="main-container">
        <?php if($chamado == null || $chamado->num_rows() == 0): ?>
            <!-- Card de erro - chamado não encontrado -->
            <div class="error-card">
                <div class="error-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h1 class="error-title">Chamado não encontrado!</h1>
                <p class="error-message">
                    O chamado que você está tentando avaliar<br>
                    não existe ou expirou.
                </p>
                <a href="<?php echo base_url('auth/login') ?>" class="btn-avaliar" style="width: auto; padding: 0 40px; display: inline-flex;">
                    <i class="fas fa-arrow-left"></i>
                    <span>Ir para o login</span>
                </a>
            </div>
        <?php else: ?>
            <!-- Card de avaliação -->
            <div class="avaliacao-card">
                <div class="avaliacao-header">
                    <div class="avaliacao-logo">
                        <img src="<?php echo base_url('assets/images/login/page-login-logo.png') ?>" alt="Logo">
                    </div>
                    <h1 class="avaliacao-title">
                        Avaliação do chamado <span>#<?php echo $chamado->row()->id ?></span>
                    </h1>
                </div>

                <!-- Título do chamado em destaque -->
                <div class="chamado-titulo">
                    <p><strong>Título:</strong> <?php echo $chamado->row()->titulo ?></p>
                </div>

                <form id="avaliacao_chamado_form" action="<?php echo base_url('auth/avaliacao_chamado/'.$chamado->row()->dono_id.'/'.$chamado->row()->hash) ?>" method="post">
                    <div class="avaliacao-body">
                        <?php if(count($avaliacoes) > 0): ?>
                            <?php if($has_avaliacao): ?>
                                <?php foreach ($avaliacoes as $value): ?>
                                    <div class="pergunta-item">
                                        <label for="star-<?php echo $value["id"] ?>">
                                            <?php echo $value['descricao'] ?>
                                        </label>
                                        <input 
                                            name="star-<?php echo $value["id"] ?>" 
                                            id="star-<?php echo $value["id"] ?>" 
                                            class="rating rating-loading" 
                                            value="<?php echo $value["resposta"] ?>" 
                                            data-min="0" 
                                            data-max="5" 
                                            data-step="1" 
                                            data-size="xs" 
                                            data-show-caption="false"
                                            data-symbol="★"
                                            data-glyphicon="false"
                                            data-rating-class="rating-fa"
                                        >
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <button type="button" id="btn_avaliar" class="btn-avaliar">
                        <span>Enviar avaliação</span>
                        <i class="fas fa-star"></i>
                    </button>

                    <div class="text-center">
                        <a href="<?php echo base_url('auth/login') ?>" class="btn-voltar">
                            <i class="fas fa-arrow-left"></i>
                            Voltar para o login
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de confirmação -->
<div id="confirm" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Avaliação incompleta
                </h4>
            </div>
            <div class="modal-body">
                <i class="fas fa-star"></i>
                <p>
                    <strong>Você não avaliou todas as perguntas.</strong><br>
                    Selecione uma avaliação para cada pergunta antes de finalizar.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="modal-btn modal-btn-default">
                    <i class="fas fa-times"></i>
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.1.2/js/star-rating.min.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    $('.rating').rating({
        theme: 'krajee-fa',
        filledStar: '<i class="fas fa-star"></i>',
        emptyStar: '<i class="far fa-star"></i>',
        showCaption: false,
        min: 0,
        max: 5,
        step: 1
    });

    $("#btn_avaliar").click(function(event) {
        var preenchido = 0;
        var total_perg = $(".rating").length;
        
        $(".rating").each(function() {
            if($(this).val() > 0) {
                preenchido++;
            }
        });
        
        if(preenchido < total_perg) {
            show_modal_avaliacao();
        } else {
            var btn = $(this);
            btn.addClass('loading');
            btn.prop('disabled', true);
            btn.html('<i class="fas fa-spinner fa-spin"></i> Enviando...');
            
            $("#avaliacao_chamado_form").submit();
        }
    });
    
    function show_modal_avaliacao() {
        $('#confirm').modal({
            backdrop: 'static',
            keyboard: false
        });
    }

    $('.pergunta-item').hover(
        function() {
            $(this).find('.rating-stars').addClass('hover');
        },
        function() {
            $(this).find('.rating-stars').removeClass('hover');
        }
    );
});

const style = document.createElement('style');
style.textContent = `
    .btn-avaliar.loading {
        position: relative;
        pointer-events: none;
        opacity: 0.8;
    }
    .btn-avaliar i.fa-spinner {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    .rating-stars.hover {
        transform: scale(1.05);
    }
`;
document.head.appendChild(style);
</script>