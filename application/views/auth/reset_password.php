<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body,
    html {
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
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    /* Elementos flutuantes de tecnologia */
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

    .tech-icon:nth-child(1) {
        top: 10%;
        left: 5%;
        animation-delay: 0s;
    }

    .tech-icon:nth-child(2) {
        top: 70%;
        left: 10%;
        animation-delay: 1s;
    }

    .tech-icon:nth-child(3) {
        top: 20%;
        right: 5%;
        animation-delay: 2s;
    }

    .tech-icon:nth-child(4) {
        bottom: 15%;
        right: 10%;
        animation-delay: 1.5s;
    }

    .tech-icon:nth-child(5) {
        top: 40%;
        left: 15%;
        animation-delay: 2.5s;
    }

    .tech-icon:nth-child(6) {
        bottom: 25%;
        left: 8%;
        animation-delay: 0.8s;
    }

    .tech-icon:nth-child(7) {
        top: 60%;
        right: 15%;
        animation-delay: 1.2s;
    }

    .tech-icon:nth-child(8) {
        bottom: 10%;
        right: 5%;
        animation-delay: 2.2s;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px) rotate(0deg);
        }

        50% {
            transform: translateY(-20px) rotate(5deg);
        }
    }

    .main-container {
        position: relative !important;
        z-index: 10 !important;
        width: 100% !important;
        max-width: 450px !important;
        margin: 0 auto !important;
    }

    /* Card */
    .reset-card {
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

    /* Barra superior animada */
    .reset-card::before {
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
        0% {
            background-position: 0% 0%;
        }

        100% {
            background-position: 300% 0%;
        }
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

    /* Header centralizado */
    .reset-header {
        text-align: center !important;
        margin-bottom: 35px !important;
    }

    .reset-logo {
        margin-bottom: 25px !important;
    }

    .reset-logo img {
        max-width: 250px !important;
        height: auto !important;
        transition: transform 0.3s ease !important;
    }

    .reset-logo img:hover {
        transform: scale(1.05);
    }

    .reset-title {
        font-size: 28px !important;
        font-weight: 600 !important;
        color: #1E1D3F !important;
        margin-bottom: 12px !important;
        letter-spacing: -0.5px !important;
    }

    .reset-subtitle {
        color: #6b7280 !important;
        font-size: 15px !important;
        font-weight: 400 !important;
        line-height: 1.5 !important;
        margin-bottom: 10px !important;
    }

    /* Inputs */
    .form-group {
        margin-bottom: 20px !important;
        position: relative !important;
    }

    .input-wrapper {
        position: relative !important;
        display: flex !important;
        align-items: center !important;
        background: #f8fafc !important;
        border: 2px solid #e2e8f0 !important;
        border-radius: 16px !important;
        transition: all 0.3s ease !important;
        height: 60px !important;
    }

    .input-wrapper:focus-within {
        border-color: #9CD7D4 !important;
        background: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(156, 215, 212, 0.15) !important;
    }

    .input-icon {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 60px !important;
        height: 100% !important;
        background: transparent !important;
        border-right: 2px solid #e2e8f0 !important;
        transition: all 0.3s ease !important;
    }

    .input-wrapper:focus-within .input-icon {
        border-right-color: #9CD7D4 !important;
    }

    .input-icon img {
        width: 22px !important;
        height: 22px !important;
        filter: brightness(0) saturate(100%) invert(40%) sepia(8%) saturate(525%) hue-rotate(202deg) brightness(94%) contrast(87%) !important;
        transition: filter 0.3s ease !important;
    }

    .input-wrapper:focus-within .input-icon img {
        filter: brightness(0) saturate(100%) invert(28%) sepia(15%) saturate(2174%) hue-rotate(201deg) brightness(94%) contrast(87%) !important;
    }

    .form-control {
        flex: 1 !important;
        height: 100% !important;
        border: none !important;
        background: transparent !important;
        padding: 0 20px !important;
        font-size: 16px !important;
        color: #1e293b !important;
        outline: none !important;
        font-weight: 400 !important;
        width: 100% !important;
    }

    .form-control::placeholder {
        color: #94a3b8 !important;
        font-weight: 300 !important;
    }

    /* Container do campo de senha */
    .password-field-container {
        flex: 1 !important;
        height: 100% !important;
        position: relative !important;
        display: flex !important;
        align-items: center !important;
    }

    .password-field-container .form-control {
        padding-right: 45px !important;
        width: 100% !important;
    }

    /* Botão de visualizar senha */
    .toggle-password {
        position: absolute !important;
        right: 8px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        background: transparent !important;
        border: none !important;
        cursor: pointer !important;
        color: #94a3b8 !important;
        font-size: 18px !important;
        padding: 8px !important;
        z-index: 10 !important;
        width: 36px !important;
        height: 36px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 8px !important;
        transition: all 0.2s ease !important;
        outline: none !important;
    }

    .toggle-password:hover {
        color: #1E1D3F !important;
        background: rgba(156, 215, 212, 0.1) !important;
    }

    /* Esconder botões nativos do navegador */
    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear,
    input[type="password"]::-webkit-clear-button,
    input[type="password"]::-webkit-inner-spin-button,
    input[type="password"]::-webkit-outer-spin-button,
    input[type="password"]::-webkit-credentials-auto-fill-button,
    input[type="password"]::-webkit-caps-lock-indicator {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }

    /* Barra de força da senha */
    .progress {
        height: 4px !important;
        margin-top: 8px !important;
        background: #e2e8f0 !important;
        border-radius: 2px !important;
        overflow: hidden !important;
    }

    .pwstrength_viewport_progress {
        height: 100% !important;
        width: 0;
        background: linear-gradient(90deg, #ef4444, #f59e0b, #10b981) !important;
        transition: width 0.3s ease !important;
    }

    /* Botão principal */
    .btn-reset {
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
        margin-top: 10px !important;
        text-transform: uppercase !important;
    }

    .btn-reset:hover {
        background: #2a294f !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 12px 25px rgba(156, 215, 212, 0.3) !important;
    }

    .btn-reset:active {
        transform: translateY(0) !important;
    }

    /* Link de voltar ao login */
    .back-to-login {
        text-align: center !important;
        margin-top: 25px !important;
    }

    .back-to-login a {
        color: #64748b !important;
        text-decoration: none !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        transition: color 0.2s ease !important;
        position: relative !important;
        padding-bottom: 2px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
    }

    .back-to-login a i {
        font-size: 14px !important;
        transition: transform 0.2s ease !important;
    }

    .back-to-login a:hover {
        color: #1E1D3F !important;
    }

    .back-to-login a:hover i {
        transform: translateX(-3px) !important;
    }

    .back-to-login a::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background: linear-gradient(90deg, #1E1D3F, #9CD7D4);
        transition: width 0.3s ease;
    }

    .back-to-login a:hover::after {
        width: 100%;
    }

    /* Mensagem de alerta */
    .alert-message {
        background: #f0f9ff !important;
        border-left: 4px solid #9CD7D4 !important;
        border-radius: 12px !important;
        padding: 15px 20px !important;
        margin-bottom: 25px !important;
        color: #1E1D3F !important;
        font-size: 14px !important;
        line-height: 1.5 !important;
    }

    .alert-message.error {
        background: #fef2f2 !important;
        border-left-color: #ef4444 !important;
        color: #991b1b !important;
    }

    .alert-message.success {
        background: #f0fdf4 !important;
        border-left-color: #22c55e !important;
        color: #166534 !important;
    }

    .alert-message:empty {
        display: none !important;
    }

    /* Campos hidden */
    .hidden-fields {
        display: none !important;
    }

    /* Container de erros do pwstrength */
    .pwstrength_viewport_errors {
        display: none;
    }
    .pwstrength_viewport_errors:not(:empty) {
        display: block;
        background: #fef2f2 !important;
        border-left: 4px solid #ef4444 !important;
        border-radius: 12px !important;
        padding: 15px 20px !important;
        margin-top: -5px !important;
        margin-bottom: 20px !important;
        color: #991b1b !important;
        font-size: 13px !important;
    }
    .pwstrength_viewport_errors ul {
        margin: 0 !important;
        padding-left: 20px !important;
        list-style-type: none !important;
    }
    .pwstrength_viewport_errors ul li {
        margin-bottom: 5px !important;
        position: relative !important;
    }
    .pwstrength_viewport_errors ul li:last-child {
        margin-bottom: 0 !important;
    }
    .pwstrength_viewport_errors ul li::before {
        content: "•";
        position: absolute;
        left: -15px;
        color: #ef4444;
    }

    /* Esconder error-list original injetado pelo plugin que quebra o layout flex */
    .password-field-container .error-list {
        display: none !important;
    }

    /* Loading state */
    .btn-reset.loading {
        position: relative !important;
        color: transparent !important;
        pointer-events: none !important;
    }

    .btn-reset.loading::after {
        content: '';
        position: absolute;
        width: 24px;
        height: 24px;
        top: 50%;
        left: 50%;
        margin-left: -12px;
        margin-top: -12px;
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* ===== RESPONSIVO PARA CELULAR ===== */
    @media (max-width: 768px) {
        .main-row {
            padding: 0 !important;
        }

        .main-container {
            max-width: 100% !important;
            padding: 0 !important;
            min-height: 100vh !important;
            display: flex !important;
            align-items: center !important;
        }

        .reset-card {
            border-radius: 0 !important;
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            padding: 30px 25px !important;
            margin: 0 !important;
            box-shadow: none !important;
            animation: none !important;
        }

        .reset-card::before {
            height: 6px !important;
        }

        .reset-title {
            font-size: 32px !important;
        }

        .reset-subtitle {
            font-size: 16px !important;
        }

        .input-wrapper {
            height: 65px !important;
        }

        .btn-reset {
            height: 60px !important;
            font-size: 18px !important;
        }

        .tech-icon {
            opacity: 0.08 !important;
            font-size: 2rem !important;
        }
    }

    /* Celulares muito pequenos */
    @media (max-width: 380px) {
        .reset-card {
            padding: 25px 20px !important;
        }

        .reset-title {
            font-size: 28px !important;
        }

        .input-wrapper {
            height: 60px !important;
        }

        .input-icon {
            min-width: 50px !important;
        }

        .input-icon img {
            width: 18px !important;
            height: 18px !important;
        }

        .toggle-password {
            width: 32px !important;
            height: 32px !important;
            font-size: 16px !important;
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
        <div class="reset-card">
            <div class="reset-header">
                <div class="reset-logo">
                    <img src="<?php echo base_url('assets/images/login/page-login-logo.png?v=' . time()) ?>" alt="Logo">
                </div>
                <h1 class="reset-title">Redefinir senha</h1>
                <p class="reset-subtitle">
                    Escolha uma nova senha forte<br>
                    para sua conta
                </p>
            </div>

            <?php echo form_open('auth/reset_password/' . $code, ['id' => 'resetForm', 'novalidate' => 'novalidate']); ?>

            <!-- Nova senha -->
            <div class="form-group">
                <div class="input-wrapper">
                    <span class="input-icon">
                        <img src="<?php echo base_url('assets/images/login/page-login-icon-pass.png') ?>" alt="password">
                    </span>
                    <div class="password-field-container">
                        <?php
                        $new_password['class'] = 'form-control';
                        $new_password['placeholder'] = 'Nova senha';
                        $new_password['id'] = 'new_password';
                        echo form_input($new_password);
                        ?>
                        <button type="button" class="toggle-password" id="toggleNewPassword" tabindex="-1">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </div>
                </div>
                <!-- Barra de força da senha -->
                <div class="progress">
                    <div class="pwstrength_viewport_progress"></div>
                </div>
            </div>

            <!-- Confirmar senha -->
            <div class="form-group">
                <div class="input-wrapper">
                    <span class="input-icon">
                        <img src="<?php echo base_url('assets/images/login/page-login-icon-pass.png') ?>" alt="confirm password">
                    </span>
                    <div class="password-field-container">
                        <?php
                        $new_password_confirm['class'] = 'form-control';
                        $new_password_confirm['placeholder'] = 'Confirmar nova senha';
                        $new_password_confirm['id'] = 'confirm_password';
                        echo form_input($new_password_confirm);
                        ?>
                        <button type="button" class="toggle-password" id="toggleConfirmPassword" tabindex="-1">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Container de Erros JS -->
            <div class="pwstrength_viewport_errors"></div>

            <!-- Campos hidden -->
            <div class="hidden-fields">
                <?php echo form_input($user_id); ?>
                <?php echo form_hidden($csrf); ?>
            </div>

            <!-- Mensagem de alerta -->
            <?php if (isset($message) && !empty($message)): ?>
                <div class="alert-message <?php echo (strpos($message, 'sucesso') !== false || strpos($message, 'success') !== false) ? 'success' : 'error'; ?>" style="margin-top: -5px; margin-bottom: 20px; padding: 12px 15px; font-size: 13px;">
                    <i class="<?php echo (strpos($message, 'sucesso') !== false || strpos($message, 'success') !== false) ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'; ?>" style="margin-right: 5px;"></i> <?php echo strip_tags($message); ?>
                </div>
            <?php endif; ?>

            <button type="submit" name="submit" class="btn-reset" id="submitBtn">
                Redefinir senha
            </button>

            <div class="back-to-login">
                <?php echo anchor('auth/login', '<i class="fas fa-arrow-left"></i> Voltar para o login'); ?>
            </div>

            <?php echo form_close(); ?>
        </div>
    </div>
</div>