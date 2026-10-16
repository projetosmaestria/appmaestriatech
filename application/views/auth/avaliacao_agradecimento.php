<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

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
        max-width: 500px !important;
        margin: 0 auto !important;
    }

    .thankyou-card {
        background: #ffffff !important;
        border-radius: 32px !important;
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3) !important;
        padding: 50px 40px !important;
        width: 100% !important;
        animation: slideUp 0.6s ease-out !important;
        border: 1px solid rgba(156, 215, 212, 0.2) !important;
        position: relative !important;
        overflow: hidden !important;
        text-align: center !important;
    }

    .thankyou-card::before {
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

    .success-icon {
        margin-bottom: 30px !important;
        animation: bounceIn 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55) !important;
    }

    .success-icon i {
        font-size: 80px !important;
        background: linear-gradient(135deg, #1E1D3F, #9CD7D4) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
        filter: drop-shadow(0 10px 20px rgba(30, 29, 63, 0.2)) !important;
    }

    @keyframes bounceIn {
        0% {
            opacity: 0;
            transform: scale(0.3);
        }
        50% {
            opacity: 1;
            transform: scale(1.1);
        }
        70% {
            transform: scale(0.9);
        }
        100% {
            transform: scale(1);
        }
    }

    .thankyou-title {
        font-size: 32px !important;
        font-weight: 700 !important;
        color: #1E1D3F !important;
        margin-bottom: 15px !important;
        letter-spacing: -0.5px !important;
        line-height: 1.3 !important;
    }

    .thankyou-title span {
        background: linear-gradient(135deg, #1E1D3F, #9CD7D4) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
        font-weight: 800 !important;
    }

    .chamado-number {
        display: inline-block !important;
        background: linear-gradient(135deg, rgba(30, 29, 63, 0.05), rgba(156, 215, 212, 0.1)) !important;
        padding: 8px 25px !important;
        border-radius: 50px !important;
        margin-bottom: 25px !important;
        border: 1px solid rgba(156, 215, 212, 0.3) !important;
        animation: pulse 2s infinite !important;
    }

    .chamado-number p {
        font-size: 18px !important;
        color: #1E1D3F !important;
        font-weight: 500 !important;
        margin: 0 !important;
    }

    .chamado-number strong {
        color: #9CD7D4 !important;
        font-size: 24px !important;
        margin-left: 5px !important;
        font-weight: 700 !important;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(156, 215, 212, 0.4);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(156, 215, 212, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(156, 215, 212, 0);
        }
    }

    .thankyou-message {
        margin-bottom: 35px !important;
    }

    .thankyou-message p {
        font-size: 18px !important;
        color: #4b5563 !important;
        line-height: 1.6 !important;
        margin: 0 !important;
    }

    .thankyou-message i {
        color: #9CD7D4 !important;
        margin: 0 5px !important;
    }

    /* Botão */
    .btn-login {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 10px !important;
        background: #1E1D3F !important;
        color: white !important;
        border: none !important;
        border-radius: 16px !important;
        padding: 16px 40px !important;
        font-size: 16px !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 8px 20px rgba(30, 29, 63, 0.3) !important;
        text-decoration: none !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        width: auto !important;
        min-width: 220px !important;
    }

    .btn-login:hover {
        background: #2a294f !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 12px 25px rgba(156, 215, 212, 0.3) !important;
        color: white !important;
        text-decoration: none !important;
    }

    .btn-login:active {
        transform: translateY(0) !important;
    }

    .btn-login i {
        font-size: 18px !important;
        transition: transform 0.3s ease !important;
    }

    .btn-login:hover i {
        transform: translateX(5px) !important;
    }

    .confetti {
        position: absolute;
        width: 10px;
        height: 10px;
        background: #9CD7D4;
        opacity: 0.6;
        animation: confetti 5s ease-in-out infinite;
        border-radius: 2px;
    }

    .confetti:nth-child(1) {
        top: -10%;
        left: 10%;
        animation-delay: 0s;
        background: #1E1D3F;
    }

    .confetti:nth-child(2) {
        top: -10%;
        right: 15%;
        animation-delay: 1s;
        background: #9CD7D4;
        width: 15px;
        height: 15px;
    }

    .confetti:nth-child(3) {
        top: -10%;
        left: 20%;
        animation-delay: 2s;
        background: #1E1D3F;
        width: 8px;
        height: 8px;
    }

    .confetti:nth-child(4) {
        top: -10%;
        right: 25%;
        animation-delay: 1.5s;
        background: #9CD7D4;
        width: 12px;
        height: 12px;
    }

    .confetti:nth-child(5) {
        top: -10%;
        left: 40%;
        animation-delay: 2.5s;
        background: #1E1D3F;
    }

    @keyframes confetti {
        0% {
            transform: translateY(0) rotate(0deg);
            opacity: 0.6;
        }
        100% {
            transform: translateY(110vh) rotate(720deg);
            opacity: 0;
        }
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

        .thankyou-card {
            border-radius: 28px !important;
            padding: 40px 25px !important;
        }

        .thankyou-card::before {
            height: 6px !important;
        }

        .success-icon i {
            font-size: 70px !important;
        }

        .thankyou-title {
            font-size: 28px !important;
        }

        .chamado-number p {
            font-size: 16px !important;
        }

        .chamado-number strong {
            font-size: 22px !important;
        }

        .thankyou-message p {
            font-size: 16px !important;
        }

        .btn-login {
            padding: 14px 30px !important;
            min-width: 200px !important;
            font-size: 15px !important;
        }

        .tech-icon {
            opacity: 0.08 !important;
        }

        .confetti {
            display: none;
        }
    }

    @media (max-width: 380px) {
        .thankyou-card {
            padding: 30px 20px !important;
        }

        .success-icon i {
            font-size: 60px !important;
        }

        .thankyou-title {
            font-size: 24px !important;
        }

        .chamado-number {
            padding: 6px 20px !important;
        }

        .chamado-number strong {
            font-size: 20px !important;
        }

        .btn-login {
            padding: 12px 25px !important;
            min-width: 180px !important;
            font-size: 14px !important;
        }
    }
</style>

<div class="row main-row fill">
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

    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>

    <div class="main-container">
        <div class="thankyou-card">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>

            <h1 class="thankyou-title">
                Chamado <span>#<?php echo $chamado->row()->id ?></span>
            </h1>

            <div class="chamado-number">
                <p>Avaliado com sucesso! <strong>✓</strong></p>
            </div>

            <div class="thankyou-message">
                <p>
                    <i class="fas fa-heart" style="color: #9CD7D4;"></i>
                    Obrigado pela sua avaliação!
                    <i class="fas fa-heart" style="color: #9CD7D4;"></i>
                </p>
                <p style="font-size: 15px; margin-top: 10px; color: #6b7280;">
                    Sua opinião é muito importante para nós.
                </p>
            </div>

            <a href="<?php echo base_url('auth/login') ?>" class="btn-login">
                <span>Ir para o login</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const card = document.querySelector('.thankyou-card');
    if (card) {
        card.style.animation = 'slideUp 0.6s ease-out';
    }

    function createHeart() {
        const heart = document.createElement('i');
        heart.className = 'fas fa-heart';
        heart.style.position = 'fixed';
        heart.style.color = '#9CD7D4';
        heart.style.fontSize = Math.random() * 20 + 10 + 'px';
        heart.style.left = Math.random() * 100 + '%';
        heart.style.top = '-10%';
        heart.style.opacity = '0.3';
        heart.style.pointerEvents = 'none';
        heart.style.zIndex = '2';
        heart.style.animation = `confetti ${Math.random() * 3 + 3}s linear infinite`;
        heart.style.animationDelay = Math.random() * 2 + 's';
        
        document.body.appendChild(heart);
        
        setTimeout(() => {
            heart.remove();
        }, 6000);
    }

    if (window.innerWidth > 768) {
        for (let i = 0; i < 10; i++) {
            setTimeout(createHeart, i * 200);
        }
    }
});
</script>