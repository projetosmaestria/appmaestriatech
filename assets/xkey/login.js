/**
 * XKey Chamados - Login Interface Scripts
 * Gerenciamento de credenciais salvas (localStorage), visualização de senha e detector de Caps Lock.
 */

(function() {
    'use strict';

    const STORAGE_KEY = 'xkey_chamados_saved_credentials';

    // Função de alternar visualização de senha
    function togglePasswordVisibility(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        const pwd = document.getElementById('password');
        const btn = document.getElementById('togglePassword') || (e ? e.target.closest('#togglePassword, .btn-toggle-password') : null);
        const icon = document.getElementById('toggleEyeIcon') || (btn ? btn.querySelector('i') : null);

        if (!pwd) return;

        const isPassword = pwd.getAttribute('type') === 'password';
        const newType = isPassword ? 'text' : 'password';
        pwd.setAttribute('type', newType);

        if (icon) {
            if (isPassword) {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }

        if (btn) {
            btn.setAttribute('title', isPassword ? 'Ocultar senha' : 'Mostrar senha');
        }

        // Manter o cursor no final do campo se já estiver focado
        if (document.activeElement === pwd) {
            const val = pwd.value;
            pwd.setSelectionRange(val.length, val.length);
        }
    }

    // Inicialização dos recursos da tela de login
    function initLogin() {
        const form = document.getElementById('loginForm');
        const identityInput = document.getElementById('identity');
        const passwordInput = document.getElementById('password');
        const rememberCheckbox = document.getElementById('remember');
        const toggleButton = document.getElementById('togglePassword');
        const capsLockWarning = document.getElementById('capsLockWarning');
        const autofillBadge = document.getElementById('autofillBadge');
        const btnClearSaved = document.getElementById('btnClearSaved');
        const btnSubmit = document.getElementById('btnSubmit');
        const btnText = document.getElementById('btnText');
        const btnArrow = document.getElementById('btnArrow');
        const btnSpinner = document.getElementById('btnSpinner');

        // 1. Carregar credenciais salvas localmente
        try {
            const savedRaw = localStorage.getItem(STORAGE_KEY);
            if (savedRaw) {
                const creds = JSON.parse(savedRaw);
                if (creds && creds.identity && identityInput) {
                    identityInput.value = creds.identity;
                    if (creds.password && passwordInput) {
                        passwordInput.value = creds.password;
                    }
                    if (rememberCheckbox) {
                        rememberCheckbox.checked = true;
                    }
                    if (autofillBadge) {
                        autofillBadge.style.display = 'flex';
                    }
                }
            }
        } catch (err) {
            console.warn('Erro ao carregar credenciais salvas:', err);
        }

        // 2. Botão limpar credenciais salvas
        if (btnClearSaved) {
            btnClearSaved.onclick = function(e) {
                e.preventDefault();
                try {
                    localStorage.removeItem(STORAGE_KEY);
                } catch (err) {}

                if (identityInput) identityInput.value = '';
                if (passwordInput) passwordInput.value = '';
                if (rememberCheckbox) rememberCheckbox.checked = false;
                if (autofillBadge) autofillBadge.style.display = 'none';
                if (identityInput) identityInput.focus();
            };
        }

        // 3. Ao desmarcar a opção Lembrar-me
        if (rememberCheckbox) {
            rememberCheckbox.onchange = function() {
                if (!this.checked) {
                    try {
                        localStorage.removeItem(STORAGE_KEY);
                    } catch (err) {}
                    if (autofillBadge) {
                        autofillBadge.style.display = 'none';
                    }
                }
            };
        }

        // 4. Salvar credenciais no envio
        if (form) {
            form.onsubmit = function() {
                if (rememberCheckbox && rememberCheckbox.checked && identityInput) {
                    try {
                        const dataToSave = {
                            identity: identityInput.value.trim(),
                            password: passwordInput ? passwordInput.value : '',
                            timestamp: Date.now()
                        };
                        localStorage.setItem(STORAGE_KEY, JSON.stringify(dataToSave));
                    } catch (err) {
                        console.warn('Não foi possível salvar no localStorage:', err);
                    }
                } else {
                    try {
                        localStorage.removeItem(STORAGE_KEY);
                    } catch (err) {}
                }

                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    if (btnText) btnText.textContent = 'Acessando...';
                    if (btnArrow) btnArrow.style.display = 'none';
                    if (btnSpinner) btnSpinner.style.display = 'inline-block';
                }
            };
        }

        // 5. Botão de alternar senha (evento direto no elemento)
        if (toggleButton) {
            toggleButton.onclick = togglePasswordVisibility;
        }

        // 6. Detector de Caps Lock
        function checkCapsLock(event) {
            if (event.getModifierState && capsLockWarning) {
                if (event.getModifierState('CapsLock')) {
                    capsLockWarning.style.display = 'inline-flex';
                } else {
                    capsLockWarning.style.display = 'none';
                }
            }
        }

        if (passwordInput) {
            passwordInput.addEventListener('keydown', checkCapsLock);
            passwordInput.addEventListener('keyup', checkCapsLock);
        }
    }

    // Delegação global de clique para o botão de visualização de senha
    // Garante funcionamento mesmo em casos de renderização assíncrona ou substituição de DOM
    document.addEventListener('click', function(e) {
        const toggleBtn = e.target.closest('#togglePassword, .btn-toggle-password');
        if (toggleBtn) {
            togglePasswordVisibility(e);
        }
    }, true);

    // Compatibilidade com jQuery se disponível
    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('click', '#togglePassword, .btn-toggle-password', function(e) {
            togglePasswordVisibility(e);
        });
    }

    // Inicializar assim que o DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLogin);
    } else {
        initLogin();
    }
})();
