/*
 * tagSelect: transforma um <select multiple> em campo de badges.
 * Clique ou digite para buscar, clique na opção para adicionar e no "x" da badge para remover.
 * O select original continua no formulário (escondido) e é ele que vai no POST.
 * Quando as opções do select são trocadas por JS (ex.: troca de cliente), o campo se refaz sozinho.
 *
 * Uso: $('#interessados').tagSelect({ placeholder: 'Buscar...' });
 */
(function($) {
    if (!$ || $.fn.tagSelect) { return; }

    var padrao = {
        placeholder: 'Clique ou digite para buscar',
        semResultado: 'Nenhum resultado',
        // "Nome (email@x.com)" vira só "Nome" na badge; o texto completo fica no title.
        rotulo: function(texto) {
            return $.trim(String(texto).replace(/\s*\([^()]*@[^()]*\)\s*$/, '')) || texto;
        }
    };

    function normalizar(texto) {
        texto = String(texto || '').toLowerCase();
        return texto.normalize ? texto.normalize('NFD').replace(/[̀-ͯ]/g, '') : texto;
    }

    function TagSelect(select, opcoes) {
        this.$select = $(select);
        this.opcoes = $.extend({}, padrao, opcoes || {});
        this.ativo = -1;
        this.montar();
        this.renderizar();
    }

    TagSelect.prototype.montar = function() {
        var self = this;
        var id = this.$select.attr('id') || ('tag-select-' + Math.random().toString(36).slice(2));
        this.idMenu = id + '-opcoes';

        this.$select.addClass('tag-select-nativo').attr({ 'aria-hidden': 'true', tabindex: '-1' });
        this.$campo = $('<div class="tag-select form-control">');
        this.$badges = $('<span class="tag-select-badges">');
        this.$busca = $('<input type="text" class="tag-select-busca" autocomplete="off">').attr({
            id: id + '-busca',
            role: 'combobox',
            'aria-autocomplete': 'list',
            'aria-expanded': 'false',
            'aria-controls': this.idMenu
        });
        this.$menu = $('<ul class="tag-select-menu" role="listbox">').attr('id', this.idMenu);
        this.$campo.append(this.$badges, this.$busca);
        this.$raiz = $('<div class="tag-select-wrap">').append(this.$campo, this.$menu).insertAfter(this.$select);

        // O label do select passa a focar a busca.
        if (this.$select.attr('id')) {
            $('label[for="' + this.$select.attr('id') + '"]').attr('for', this.$busca.attr('id'));
        }

        this.$campo.on('mousedown', function(e) {
            if ($(e.target).closest('.tag-select-remover').length) { return; }
            e.preventDefault();
            self.$busca.focus();
            self.abrir();
        });
        this.$busca.on('focus', function() { self.$campo.addClass('is-foco'); });
        this.$busca.on('blur', function() {
            self.$campo.removeClass('is-foco');
            // Espera o clique na opção ser tratado antes de fechar.
            setTimeout(function() {
                if (!self.$busca.is(':focus')) { self.fechar(); }
            }, 150);
        });
        this.$busca.on('input', function() {
            self.ativo = 0;
            self.abrir();
        });
        this.$busca.on('keydown', function(e) { self.teclado(e); });

        this.$badges.on('click', '.tag-select-remover', function(e) {
            e.preventDefault();
            self.alternar($(this).closest('.tag-select-badge').data('valor'), false);
            self.$busca.focus();
        });
        this.$menu.on('mousedown', '.tag-select-opcao', function(e) {
            e.preventDefault();
            self.alternar($(this).data('valor'), true);
            self.$busca.val('');
            self.abrir();
        });

        this.$select.on('change.tagSelect', function() { self.renderizar(); });
        // Opções recarregadas por AJAX (carregar_select_default_chamados) ou acrescentadas pelo cadastro rápido.
        if (window.MutationObserver) {
            this.observador = new window.MutationObserver(function() {
                self.renderizar();
                if (self.$raiz.hasClass('is-aberto')) { self.listar(); }
            });
            this.observador.observe(this.$select[0], { childList: true, subtree: true, attributes: true, attributeFilter: ['selected', 'disabled'] });
        }
    };

    TagSelect.prototype.validas = function() {
        return this.$select.find('option').filter(function() {
            return this.value !== '' && !this.disabled;
        });
    };

    TagSelect.prototype.renderizar = function() {
        var self = this;
        this.$badges.empty();
        this.validas().filter(':selected').each(function() {
            var texto = $(this).text();
            var rotulo = self.opcoes.rotulo(texto);
            $('<span class="tag-select-badge">').attr('title', texto).data('valor', this.value)
                .append($('<span class="tag-select-badge-texto">').text(rotulo))
                .append($('<button type="button" class="tag-select-remover">&times;</button>').attr('aria-label', 'Remover ' + rotulo))
                .appendTo(self.$badges);
        });
        var temBadge = this.$badges.children().length > 0;
        this.$campo.toggleClass('has-badges', temBadge);
        this.$busca.attr('placeholder', temBadge ? '' : this.opcoes.placeholder);
    };

    TagSelect.prototype.listar = function() {
        var self = this;
        var termo = normalizar(this.$busca.val());
        var $validas = this.validas();
        this.$menu.empty();

        $validas.not(':selected').each(function() {
            var texto = $(this).text();
            if (termo && normalizar(texto).indexOf(termo) === -1) { return; }
            $('<li class="tag-select-opcao" role="option">').data('valor', this.value).text(texto).appendTo(self.$menu);
        });

        var $itens = this.$menu.children('.tag-select-opcao');
        if (!$itens.length) {
            // Sem opções válidas mostra o aviso do próprio select ("Carregando...", "Esta unidade não usuários!").
            var aviso = $validas.length ? this.opcoes.semResultado : $.trim(this.$select.find('option').first().text()) || this.opcoes.semResultado;
            $('<li class="tag-select-vazio">').text(aviso).appendTo(this.$menu);
        }
        this.ativo = Math.min(Math.max(this.ativo, 0), $itens.length - 1);
        this.destacar();
    };

    TagSelect.prototype.destacar = function() {
        var $itens = this.$menu.children('.tag-select-opcao').removeClass('is-ativo').attr('aria-selected', 'false');
        if (this.ativo < 0 || !$itens.length) { return; }
        var $item = $itens.eq(this.ativo).addClass('is-ativo').attr('aria-selected', 'true');
        var topo = $item[0].offsetTop;
        var base = topo + $item.outerHeight();
        if (topo < this.$menu.scrollTop()) {
            this.$menu.scrollTop(topo);
        } else if (base > this.$menu.scrollTop() + this.$menu.innerHeight()) {
            this.$menu.scrollTop(base - this.$menu.innerHeight());
        }
    };

    TagSelect.prototype.abrir = function() {
        this.$raiz.addClass('is-aberto');
        this.$busca.attr('aria-expanded', 'true');
        this.listar();
    };

    TagSelect.prototype.fechar = function() {
        this.$raiz.removeClass('is-aberto');
        this.$busca.attr('aria-expanded', 'false').val('');
        this.ativo = -1;
    };

    TagSelect.prototype.alternar = function(valor, marcar) {
        this.$select.find('option').filter(function() { return this.value === String(valor); }).prop('selected', marcar);
        this.$select.trigger('change');
    };

    TagSelect.prototype.teclado = function(e) {
        var $itens = this.$menu.children('.tag-select-opcao');
        switch (e.which) {
            case 40: // seta para baixo
                e.preventDefault();
                if (!this.$raiz.hasClass('is-aberto')) { this.abrir(); return; }
                this.ativo = Math.min(this.ativo + 1, $itens.length - 1);
                this.destacar();
                break;
            case 38: // seta para cima
                e.preventDefault();
                this.ativo = Math.max(this.ativo - 1, 0);
                this.destacar();
                break;
            case 13: // enter: adiciona a opção destacada sem enviar o formulário
                if (!this.$raiz.hasClass('is-aberto')) { return; }
                e.preventDefault();
                if (this.ativo >= 0 && $itens.length) {
                    this.alternar($itens.eq(this.ativo).data('valor'), true);
                    this.$busca.val('');
                    this.listar();
                }
                break;
            case 8: // backspace com a busca vazia remove a última badge
                if (this.$busca.val() === '') {
                    var $ultima = this.$badges.children().last();
                    if ($ultima.length) { this.alternar($ultima.data('valor'), false); }
                }
                break;
            case 27: // esc
                if (this.$raiz.hasClass('is-aberto')) {
                    e.preventDefault();
                    this.fechar();
                }
                break;
        }
    };

    $.fn.tagSelect = function(opcoes) {
        return this.each(function() {
            if (this.tagName !== 'SELECT' || !this.multiple || $.data(this, 'tagSelect')) { return; }
            $.data(this, 'tagSelect', new TagSelect(this, opcoes));
        });
    };
})(window.jQuery);
