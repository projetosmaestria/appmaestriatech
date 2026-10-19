/**
 * menus.js - Gerenciamento da Árvore de Menus e Seleção Múltipla de Empresas
 * Projeto: SaxWeb / MaestriaTech
 */

(function() {
    'use strict';

    if (window._menusJsInitialized) {
        return;
    }
    window._menusJsInitialized = true;

    console.log('[menus.js] Inicializando gerenciador de menus...');

    // -------------------------------------------------------------------------
    // UTILITÁRIOS DA ÁRVORE
    // -------------------------------------------------------------------------

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /**
     * Obtém o elemento sub-ramificação (UL.tree-menu-branch) filho do nó LI
     */
    function getSubBranch(node) {
        if (!node) return null;
        // 1. Busca nos filhos imediatos do nó LI
        for (var i = 0; i < node.children.length; i++) {
            var child = node.children[i];
            if (child.tagName === 'UL' || (child.classList && child.classList.contains('tree-menu-branch'))) {
                return child;
            }
        }
        // 2. Busca pelo irmão seguinte da linha .tree-node-row
        var row = node.querySelector('.tree-node-row');
        if (row && row.nextElementSibling && (row.nextElementSibling.tagName === 'UL' || row.nextElementSibling.classList.contains('tree-menu-branch'))) {
            return row.nextElementSibling;
        }
        // 3. Fallback para querySelector
        return node.querySelector('.tree-menu-branch');
    }

    /**
     * Alterna a visibilidade (collapse/expand) de um nó da árvore
     */
    function toggleNode(node) {
        if (!node) return;
        var subBranch = getSubBranch(node);
        if (!subBranch) return;

        // Determina se está visível no momento
        var isVisible = (subBranch.style.display === 'block') || 
                        (subBranch.style.display !== 'none' && window.getComputedStyle(subBranch).display !== 'none');

        var toggleIcon = node.querySelector('.tree-toggle i');
        var folderIcon = node.querySelector('.tree-icon');

        if (isVisible) {
            subBranch.style.display = 'none';
            node.classList.remove('is-open');
            if (toggleIcon) {
                toggleIcon.className = 'fa fa-chevron-right';
            }
            if (folderIcon && folderIcon.classList.contains('fa-folder-open')) {
                folderIcon.classList.remove('fa-folder-open');
                folderIcon.classList.add('fa-folder');
            }
        } else {
            subBranch.style.display = 'block';
            node.classList.add('is-open');
            if (toggleIcon) {
                toggleIcon.className = 'fa fa-chevron-down';
            }
            if (folderIcon && folderIcon.classList.contains('fa-folder')) {
                folderIcon.classList.remove('fa-folder');
                folderIcon.classList.add('fa-folder-open');
            }
        }
    }

    /**
     * Define o Menu Pai selecionado e atualiza a interface
     */
    function selecionarMenuPai(id, nome) {
        var hiddenInput = document.getElementById('pai_id');
        if (hiddenInput) {
            hiddenInput.value = (id !== null && id !== undefined && id !== '') ? id : '';
        }

        // Limpa seleção anterior
        var allRows = document.querySelectorAll('.tree-node-row');
        for (var i = 0; i < allRows.length; i++) {
            allRows[i].classList.remove('selected');
        }
        var rootCard = document.getElementById('tree-root-card');
        if (rootCard) {
            rootCard.classList.remove('is-selected');
        }
        var allChecks = document.querySelectorAll('.tree-check-mark');
        for (var j = 0; j < allChecks.length; j++) {
            allChecks[j].style.display = 'none';
        }

        var badge = document.getElementById('badge-pai-selecionado');

        if (!id) {
            // Selecionado: Menu Raiz / 1º Nível
            if (rootCard) {
                rootCard.classList.add('is-selected');
                var rootCheck = rootCard.querySelector('.tree-check-mark');
                if (rootCheck) rootCheck.style.display = 'inline-block';
            }
            if (badge) {
                badge.className = 'label label-primary';
                badge.innerHTML = '<i class="fa fa-home"></i> <span id="text-pai-selecionado">Nenhum (Menu Raiz / 1º Nível)</span>';
            }
        } else {
            // Selecionado: Item específico
            var targetNode = document.querySelector('.tree-menu-node[data-id="' + id + '"]');
            if (targetNode) {
                var row = targetNode.querySelector('.tree-node-row');
                if (row) {
                    row.classList.add('selected');
                    var check = row.querySelector('.tree-check-mark');
                    if (check) check.style.display = 'inline-block';
                }
                var nodeNome = nome || targetNode.getAttribute('data-nome') || ('Menu #' + id);
                if (badge) {
                    badge.className = 'label label-success';
                    badge.innerHTML = '<i class="fa fa-folder-open"></i> <span id="text-pai-selecionado">' + escapeHtml(nodeNome) + '</span>';
                }

                // Abre todas as ramificações ancestrais para manter o item visível
                var parent = targetNode.parentElement;
                while (parent) {
                    if (parent.classList && parent.classList.contains('tree-menu-branch')) {
                        parent.style.display = 'block';
                    }
                    if (parent.classList && parent.classList.contains('tree-menu-node')) {
                        parent.classList.add('is-open');
                        var tIcon = parent.querySelector('.tree-toggle i');
                        if (tIcon) {
                            tIcon.className = 'fa fa-chevron-down';
                        }
                        var fIcon = parent.querySelector('.tree-icon');
                        if (fIcon && fIcon.classList.contains('fa-folder')) {
                            fIcon.classList.remove('fa-folder');
                            fIcon.classList.add('fa-folder-open');
                        }
                    }
                    parent = parent.parentElement;
                }
            }
        }
    }

    /**
     * Expande todas as pastas da árvore
     */
    function expandirTudo() {
        var branches = document.querySelectorAll('.tree-menu-branch');
        for (var b = 0; b < branches.length; b++) {
            branches[b].style.display = 'block';
        }
        var nodes = document.querySelectorAll('.tree-menu-node');
        for (var n = 0; n < nodes.length; n++) {
            nodes[n].classList.add('is-open');
        }
        var chevrons = document.querySelectorAll('.tree-toggle i');
        for (var c = 0; c < chevrons.length; c++) {
            chevrons[c].className = 'fa fa-chevron-down';
        }
        var folders = document.querySelectorAll('.tree-icon.fa-folder');
        for (var f = 0; f < folders.length; f++) {
            folders[f].classList.remove('fa-folder');
            folders[f].classList.add('fa-folder-open');
        }
    }

    /**
     * Recolhe todas as pastas da árvore (exceto raiz)
     */
    function recolherTudo() {
        var branches = document.querySelectorAll('.tree-menu-branch:not(.tree-menu-root)');
        for (var b = 0; b < branches.length; b++) {
            branches[b].style.display = 'none';
        }
        var nodes = document.querySelectorAll('.tree-menu-node');
        for (var n = 0; n < nodes.length; n++) {
            nodes[n].classList.remove('is-open');
        }
        var chevrons = document.querySelectorAll('.tree-toggle i');
        for (var c = 0; c < chevrons.length; c++) {
            chevrons[c].className = 'fa fa-chevron-right';
        }
        var folders = document.querySelectorAll('.tree-icon.fa-folder-open');
        for (var f = 0; f < folders.length; f++) {
            folders[f].classList.remove('fa-folder-open');
            folders[f].classList.add('fa-folder');
        }
    }

    /**
     * Filtra os nós da árvore por nome em tempo real
     */
    function filtrarArvore(term) {
        term = (term || '').toLowerCase().trim();
        var allNodes = document.querySelectorAll('.tree-menu-node');
        if (!term) {
            for (var i = 0; i < allNodes.length; i++) {
                allNodes[i].style.display = '';
            }
            return;
        }

        for (var i = 0; i < allNodes.length; i++) {
            var node = allNodes[i];
            var nome = (node.getAttribute('data-nome') || '').toLowerCase();
            if (nome.indexOf(term) !== -1) {
                node.style.display = '';
                var p = node.parentElement;
                while (p) {
                    if (p.classList && p.classList.contains('tree-menu-branch')) {
                        p.style.display = 'block';
                    }
                    if (p.classList && p.classList.contains('tree-menu-node')) {
                        p.style.display = '';
                        p.classList.add('is-open');
                        var icon = p.querySelector('.tree-toggle i');
                        if (icon) {
                            icon.className = 'fa fa-chevron-down';
                        }
                    }
                    p = p.parentElement;
                }
            } else {
                var childNodes = node.querySelectorAll('.tree-menu-node');
                var hasMatch = false;
                for (var c = 0; c < childNodes.length; c++) {
                    var childNome = (childNodes[c].getAttribute('data-nome') || '').toLowerCase();
                    if (childNome.indexOf(term) !== -1) {
                        hasMatch = true;
                        break;
                    }
                }
                if (!hasMatch) {
                    node.style.display = 'none';
                } else {
                    node.style.display = '';
                }
            }
        }
    }

    // -------------------------------------------------------------------------
    // DELEGAÇÃO GLOBAL DE EVENTOS (CLICK / INPUT)
    // -------------------------------------------------------------------------

    document.addEventListener('click', function(e) {
        var target = e.target;
        if (!target) return;

        // 1. Clique na setinha de expandir/recolher
        var toggleBtn = target.closest('.tree-toggle');
        if (toggleBtn) {
            e.preventDefault();
            e.stopPropagation();
            var node = toggleBtn.closest('.tree-menu-node');
            toggleNode(node);
            return;
        }

        // 2. Clique no ícone da pasta (se o nó tiver filhos)
        var folderIcon = target.closest('.tree-icon');
        if (folderIcon) {
            var node = folderIcon.closest('.tree-menu-node');
            if (node && getSubBranch(node)) {
                e.preventDefault();
                e.stopPropagation();
                toggleNode(node);
                return;
            }
        }

        // 3. Clique no rótulo para selecionar como Menu Pai
        var nodeLabel = target.closest('.tree-node-label');
        if (nodeLabel) {
            var node = nodeLabel.closest('.tree-menu-node');
            if (node && !node.classList.contains('is-prohibited') && !node.querySelector('.tree-node-row.disabled')) {
                e.preventDefault();
                e.stopPropagation();
                selecionarMenuPai(node.getAttribute('data-id'), node.getAttribute('data-nome'));
                return;
            }
        }

        // 4. Clique na opção Menu Raiz (card ou botão no cabeçalho)
        var rootCard = target.closest('#tree-root-card');
        var setRootBtn = target.closest('#btn-set-root');
        if (rootCard || setRootBtn) {
            e.preventDefault();
            selecionarMenuPai('', 'Nenhum (Menu Raiz / 1º Nível)');
            return;
        }

        // 5. Botão Expandir Tudo
        var expandAllBtn = target.closest('#btn-tree-expand-all');
        if (expandAllBtn) {
            e.preventDefault();
            expandirTudo();
            return;
        }

        // 6. Botão Recolher Tudo
        var collapseAllBtn = target.closest('#btn-tree-collapse-all');
        if (collapseAllBtn) {
            e.preventDefault();
            recolherTudo();
            return;
        }

        // 7. Botão Limpar Busca
        var clearSearchBtn = target.closest('#btn-clear-search');
        if (clearSearchBtn) {
            e.preventDefault();
            var searchInput = document.getElementById('tree-search-input');
            if (searchInput) {
                searchInput.value = '';
                filtrarArvore('');
            }
            return;
        }
    });

    // Filtro em tempo real ao digitar
    document.addEventListener('input', function(e) {
        if (e.target && e.target.id === 'tree-search-input') {
            filtrarArvore(e.target.value);
        }
    });

    // -------------------------------------------------------------------------
    // SELECT MÚLTIPLO DE EMPRESAS (FASTSELECT)
    // -------------------------------------------------------------------------

    function inicializarFastSelectEmpresas() {
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.fastselect) {
            var $empresas = window.jQuery('#empresas');
            if ($empresas.length && !$empresas.data('fastselect')) {
                $empresas.fastselect({
                    placeholder: 'Clique para selecionar as empresas...',
                    searchPlaceholder: 'Buscar empresa...'
                });
                console.log('[menus.js] FastSelect empresas configurado com sucesso.');
            }
            return true;
        }
        return false;
    }

    // Polling não bloqueante a cada 50ms até jQuery e FastSelect estarem disponíveis
    var fastAttempts = 0;
    var fastTimer = setInterval(function() {
        fastAttempts++;
        if (inicializarFastSelectEmpresas() || fastAttempts > 100) {
            clearInterval(fastTimer);
        }
    }, 50);

    window.addEventListener('load', function() {
        inicializarFastSelectEmpresas();
    });

    console.log('[menus.js] Carregado com sucesso.');
})();
