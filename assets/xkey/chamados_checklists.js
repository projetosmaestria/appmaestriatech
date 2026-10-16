var etapasPersonalizadas = {};

// Função para mostrar toast
function mostrarToast(mensagem, tipo = 'success') {
    var toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.position = 'fixed';
        toastContainer.style.top = '20px';
        toastContainer.style.right = '20px';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    var toast = document.createElement('div');
    toast.style.backgroundColor = tipo === 'success' ? '#28a745' : '#dc3545';
    toast.style.color = 'white';
    toast.style.padding = '15px 20px';
    toast.style.borderRadius = '5px';
    toast.style.marginBottom = '10px';
    toast.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
    toast.style.minWidth = '250px';
    toast.style.opacity = '0';
    toast.style.transition = 'opacity 0.3s ease-in-out';
    toast.innerHTML = mensagem;
    
    toastContainer.appendChild(toast);

    setTimeout(function() {
        toast.style.opacity = '1';
    }, 10);
    
    setTimeout(function() {
        toast.style.opacity = '0';
        setTimeout(function() {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

function fecharModalAdicionarEtapa() {
    $('#adicionarEtapaModal').modal('hide');
}

function selecionarTodosChecklists() {
    var checkboxes = document.querySelectorAll('.checklist-checkbox');
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = true;
    }

    var todasEtapas = document.querySelectorAll('.etapa-checkbox');
    for (var j = 0; j < todasEtapas.length; j++) {
        todasEtapas[j].checked = true;
    }
    
    atualizarBotaoSalvar();
}

function desmarcarTodosChecklists() {
    var checkboxes = document.querySelectorAll('.checklist-checkbox');
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = false;
    }
    
    var todasEtapas = document.querySelectorAll('.etapa-checkbox');
    for (var j = 0; j < todasEtapas.length; j++) {
        todasEtapas[j].checked = false;
    }
    
    atualizarBotaoSalvar();
}

function checklistTemEtapaSelecionada(checklistId) {
    var etapas = document.querySelectorAll('.etapa-checklist-' + checklistId);
    for (var i = 0; i < etapas.length; i++) {
        if (etapas[i].checked) {
            return true;
        }
    }
    return false;
}

function atualizarChecklistPorEtapas(checklistId) {
    var checklistCheckbox = document.querySelector('.checklist-checkbox[value="' + checklistId + '"]');
    if (!checklistCheckbox) return;
    
    var temEtapaSelecionada = checklistTemEtapaSelecionada(checklistId);
    
    if (temEtapaSelecionada) {
        checklistCheckbox.checked = true;
    } 
    else {
        checklistCheckbox.checked = false;
    }
}

function atualizarBotaoSalvar() {
    var checklistsSelecionados = document.querySelectorAll('input[name="checklists[]"]:checked');
    var btnSalvar = document.getElementById('btnSaveChecklist');
    var contador = document.getElementById('checklist-counter');
    
    if (!btnSalvar || !contador) return;
    
    var podeSalvar = false;
    var checklistsComEtapas = [];
    
    for (var i = 0; i < checklistsSelecionados.length; i++) {
        var checklistId = checklistsSelecionados[i].value;
        if (checklistTemEtapaSelecionada(checklistId)) {
            podeSalvar = true;
            checklistsComEtapas.push(checklistId);
        }
    }
    
    var etapasPersonalizadasSelecionadas = document.querySelectorAll('input[name^="etapas_personalizadas"]:checked');
    
    if (etapasPersonalizadasSelecionadas.length > 0) {
        podeSalvar = true;
    }
    
    if (podeSalvar) {
        btnSalvar.disabled = false;
        
        var totalChecklists = checklistsSelecionados.length;
        var totalEtapasPersonalizadas = etapasPersonalizadasSelecionadas.length;
        
        var msg = [];
        if (totalChecklists > 0) {
            msg.push(totalChecklists + ' checklist(s)');
        }
        if (totalEtapasPersonalizadas > 0) {
            msg.push(totalEtapasPersonalizadas + ' etapa(s) personalizada(s)');
        }
        
        contador.innerHTML = msg.join(' e ') + ' selecionados';
        contador.style.color = '#28a745';
        contador.style.fontWeight = 'bold';
    } else {
        btnSalvar.disabled = true;
        contador.innerHTML = 'Selecione um checklist com pelo menos uma etapa';
        contador.style.color = '#dc3545';
        contador.style.fontWeight = 'bold';
    }
}

function converterDataParaBD(dataBR) {
    if (!dataBR) return null;
    var partes = dataBR.split('/');
    if (partes.length === 3) {
        return partes[2] + '-' + partes[1] + '-' + partes[0];
    }
    return null;
}

function filtrarChecklists() {
    var termo = document.getElementById('search-checklist').value.toLowerCase().trim();
    var checklists = document.querySelectorAll('.checklist-item');
    var countVisiveis = 0;
    
    for (var i = 0; i < checklists.length; i++) {
        var checklist = checklists[i];
        var nome = checklist.getAttribute('data-checklist-nome') || '';
        
        if (termo === '' || nome.includes(termo)) {
            checklist.style.display = 'block';
            countVisiveis++;
        } else {
            checklist.style.display = 'none';
        }
    }
    
    var resultsCount = document.getElementById('search-results-count');
    if (resultsCount) {
        if (termo === '') {
            resultsCount.innerHTML = 'Mostrando todos os checklists (' + countVisiveis + ')';
        } else {
            resultsCount.innerHTML = 'Encontrados ' + countVisiveis + ' checklist(s) com "' + termo + '"';
        }
    }
}

function debounce(func, wait) {
    var timeout;
    return function() {
        var context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            func.apply(context, args);
        }, wait);
    };
}

function selecionarTodasEtapas(checklistId) {
    var checkboxes = document.querySelectorAll('.etapa-checklist-' + checklistId);
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = true;
    }
    
    var checklistCheckbox = document.querySelector('.checklist-checkbox[value="' + checklistId + '"]');
    if (checklistCheckbox) {
        checklistCheckbox.checked = true;
    }
    
    atualizarBotaoSalvar();
}

function desmarcarTodasEtapas(checklistId) {
    var checkboxes = document.querySelectorAll('.etapa-checklist-' + checklistId);
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = false;
    }
    
    var checklistCheckbox = document.querySelector('.checklist-checkbox[value="' + checklistId + '"]');
    if (checklistCheckbox) {
        checklistCheckbox.checked = false;
    }
    
    atualizarBotaoSalvar();
}

function calcularProximaOrdem(checklistId) {
    var container = document.getElementById('etapas-container-' + checklistId);
    var personalizadasContainer = document.getElementById('etapas-personalizadas-' + checklistId);
    
    var totalEtapas = 0;
    
    if (container) {
        totalEtapas += container.querySelectorAll('.etapa-item').length;
    }
    
    if (personalizadasContainer) {
        totalEtapas += personalizadasContainer.querySelectorAll('.etapa-item').length;
    }
    
    return totalEtapas;
}

function formatarDataBR(data) {
    if (!data) return '';
    var dia = String(data.getDate()).padStart(2, '0');
    var mes = String(data.getMonth() + 1).padStart(2, '0');
    var ano = data.getFullYear();
    return dia + '/' + mes + '/' + ano;
}

function converterDataParaDate(dataBR) {
    if (!dataBR) return null;
    var partes = dataBR.split('/');
    if (partes.length === 3) {
        return new Date(partes[2], partes[1] - 1, partes[0]);
    }
    return null;
}

function calcularDataPrazo(dataInicio, dias) {
    if (!dataInicio || dias <= 0) return dataInicio;
    
    var data = new Date(dataInicio);
    var diasAdicionados = 0;
    
    while (diasAdicionados < dias) {
        data.setDate(data.getDate() + 1);
        if (data.getDay() !== 6 && data.getDay() !== 0) {
            diasAdicionados++;
        }
    }
    
    return data;
}

function configurarEventosDatasModalAdicionar() {
    $('#etapa_data_inicio').off('change').on('change', function() {
        var dataInicio = $(this).val();
        var prazoPadrao = parseInt($('#etapa_prazo').val()) || 0;
        
        if (dataInicio && prazoPadrao > 0) {
            var dataInicioDate = converterDataParaDate(dataInicio);
            if (dataInicioDate) {
                var dataPrazoCalculada = calcularDataPrazo(dataInicioDate, prazoPadrao);
                $('#etapa_data_prazo').val(formatarDataBR(dataPrazoCalculada));
            }
        } else if (prazoPadrao === 0) {
            $('#etapa_data_prazo').val('');
        }
    });
    
    $('#etapa_prazo').off('change keyup').on('change keyup', function() {
        var prazoPadrao = parseInt($(this).val()) || 0;
        var dataInicio = $('#etapa_data_inicio').val();
        
        if (prazoPadrao > 0 && dataInicio) {
            var dataInicioDate = converterDataParaDate(dataInicio);
            if (dataInicioDate) {
                var dataPrazoCalculada = calcularDataPrazo(dataInicioDate, prazoPadrao);
                $('#etapa_data_prazo').val(formatarDataBR(dataPrazoCalculada));
            }
        } else if (prazoPadrao === 0) {
            $('#etapa_data_prazo').val('');
        }
    });
}

function abrirModalAdicionarEtapa(checklistId, dataInicioChecklist) {
    document.getElementById('etapa_checklist_id').value = checklistId;
    document.getElementById('etapa_nome').value = '';
    document.getElementById('etapa_descricao').value = '';
    document.getElementById('etapa_prazo').value = '0';
    document.getElementById('etapa_data_prazo').value = '';
    document.getElementById('etapa_responsavel').value = '';
    
    var dataInicio = obterUltimaDataPrazoParaNovaEtapa(checklistId);
    if (!dataInicio) {
        dataInicio = (dataInicioChecklist && dataInicioChecklist !== '')
            ? dataInicioChecklist
            : formatarDataBR(new Date());
    }
    
    document.getElementById('etapa_data_inicio').value = dataInicio;
    
    configurarEventosDatasModalAdicionar();
    
    $('#adicionarEtapaModal').modal({
        backdrop: 'static',
        keyboard: false
    });
}

function salvarEtapaPersonalizada() {
    var checklistId = document.getElementById('etapa_checklist_id').value;
    var nome = document.getElementById('etapa_nome').value.trim();
    var descricao = document.getElementById('etapa_descricao').value.trim();
    var prazo = parseInt(document.getElementById('etapa_prazo').value) || 0;
    
    // IMPORTANTE: Capturar o valor do select
    var responsavelSelect = document.getElementById('etapa_responsavel');
    var responsavel = responsavelSelect ? responsavelSelect.value : '';
    
    var dataInicioStr = document.getElementById('etapa_data_inicio').value;
    var dataPrazoStr = document.getElementById('etapa_data_prazo').value;
    
    
    // Validações
    if (!nome) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Por favor, informe o nome da etapa.',
                confirmButtonColor: '#3085d6'
            });
        } else {
            alert('Por favor, informe o nome da etapa.');
        }
        return;
    }
    
    if (!responsavel || responsavel === '') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Por favor, selecione um responsável para a etapa.',
                confirmButtonColor: '#3085d6'
            });
        } else {
            alert('Por favor, selecione um responsável para a etapa.');
        }
        return;
    }
    
    if (!dataInicioStr) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Por favor, informe a data de início.',
                confirmButtonColor: '#3085d6'
            });
        } else {
            alert('Por favor, informe a data de início.');
        }
        return;
    }
    
    var dataInicioDate = converterDataParaDate(dataInicioStr);
    if (!dataInicioDate || isNaN(dataInicioDate.getTime())) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Data de início inválida. Use o formato DD/MM/AAAA.',
                confirmButtonColor: '#3085d6'
            });
        } else {
            alert('Data de início inválida. Use o formato DD/MM/AAAA.');
        }
        return;
    }
    
    var ordem = calcularProximaOrdem(checklistId);
    
    var etapaId = 'temp_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
    
    if (!etapasPersonalizadas[checklistId]) {
        etapasPersonalizadas[checklistId] = [];
    }
    
    // Obter nome do responsável para exibição
    var responsavelNome = '';
    if (responsavelSelect) {
        var selectedOption = responsavelSelect.options[responsavelSelect.selectedIndex];
        if (selectedOption) {
            responsavelNome = selectedOption.text;
        }
    }
    
    // CRIAR O OBJETO DA ETAPA - GARANTIR QUE RESPONSAVEL ESTÁ INCLUÍDO
    var novaEtapa = {
        id: etapaId,
        nome: nome,
        descricao: descricao,
        prazo_padrao: prazo,
        responsavel: responsavel,        // <-- CAMPO OBRIGATÓRIO
        data_inicio: dataInicioStr,
        data_prazo: dataPrazoStr,
        ordem: ordem,
        is_personalizada: true
    };
    
    etapasPersonalizadas[checklistId].push(novaEtapa);
    
    adicionarEtapaNaInterface(checklistId, etapaId, nome, descricao, prazo, responsavel, responsavelNome, ordem, dataInicioStr, dataPrazoStr);
    
    var checklistCheckbox = document.querySelector('.checklist-checkbox[value="' + checklistId + '"]');
    if (checklistCheckbox) {
        checklistCheckbox.checked = true;
    }
    
    $('#adicionarEtapaModal').modal('hide');
    
    atualizarContadorEtapas(checklistId);
    atualizarBotaoSalvar();
}

function adicionarEtapaNaInterface(checklistId, etapaId, nome, descricao, prazo, responsavelId, responsavelNome, ordem, dataInicio, dataPrazo) {
    var container = document.getElementById('etapas-personalizadas-' + checklistId);
    if (!container) return;
    
    var numeroOrdem = ordem + 1;
    
    var responsavelHtml = responsavelNome ? '<br><small class="text-primary"><i class="fa fa-user"></i> Responsável: ' + escapeHtml(responsavelNome) + '</small>' : '';
    
    var dataInfo = '';
    if (dataInicio) {
        dataInfo += '<br><small class="text-info"><i class="fa fa-calendar"></i> Início: ' + dataInicio + '</small>';
    }
    if (dataPrazo) {
        dataInfo += '<br><small class="text-info"><i class="fa fa-calendar-check-o"></i> Prazo: ' + dataPrazo + '</small>';
    }
    
    var etapaHtml = `
        <div class="etapa-item etapa-personalizada" data-etapa-id="${etapaId}" data-checklist-id="${checklistId}" data-responsavel="${responsavelId}" style="margin-bottom: 8px; padding: 8px; border: 1px solid #f39c12; border-radius: 4px; background-color: #fff9e6; width: 100%; box-sizing: border-box;">
            <div class="row" style="margin: 0; width: 100%;">
                <div class="col-xs-1 text-center" style="padding: 0; width: 30px; float: left;">
                    <span class="badge" style="background-color: #f39c12;">${numeroOrdem}</span>
                </div>
                <div class="col-xs-1 text-center" style="padding: 0; width: 30px; float: left;">
                    <input type="checkbox" 
                           name="etapas_personalizadas[${checklistId}][]" 
                           value="${etapaId}" 
                           class="etapa-checkbox etapa-checklist-${checklistId} etapa-personalizada-checkbox"
                           checked
                           style="margin-top: 5px;">
                </div>
                <div class="col-xs-9" style="padding-left: 5px; padding-right: 0; width: calc(100% - 100px); float: left;">
                    <label style="font-weight: normal; cursor: pointer; width: 100%; margin-bottom: 0; word-wrap: break-word;">
                        <strong>${escapeHtml(nome)}</strong>
                        ${descricao ? '<br><small class="text-muted">' + escapeHtml(descricao) + '</small>' : ''}
                        ${prazo > 0 ? '<br><small class="text-info"><i class="fa fa-clock-o"></i> Prazo padrão: ' + prazo + ' dia(s)</small>' : ''}
                        ${responsavelHtml}
                        ${dataInfo}
                        <br><small class="text-success"><i class="fa fa-plus-circle"></i> Etapa personalizada</small>
                    </label>
                </div>
                <div class="col-xs-1 text-right" style="padding: 0; width: 40px; float: left;">
                    <button type="button" class="btn btn-xs btn-link text-danger" onclick="confirmarRemocaoEtapaPersonalizada(this)" title="Remover etapa" style="padding: 5px;">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', etapaHtml);
    
    var novaEtapaCheckbox = container.lastElementChild.querySelector('.etapa-checkbox');
    if (novaEtapaCheckbox) {
        novaEtapaCheckbox.addEventListener('change', function(e) {
            var etapaCheckbox = e.target;
            var etapaItem = etapaCheckbox.closest('.etapa-item');
            var checklistId = etapaItem.getAttribute('data-checklist-id');
            
            if (etapaCheckbox.checked) {
                var checklistCheckbox = document.querySelector('.checklist-checkbox[value="' + checklistId + '"]');
                if (checklistCheckbox) {
                    checklistCheckbox.checked = true;
                }
            } else {
                atualizarChecklistPorEtapas(checklistId);
            }
            
            atualizarBotaoSalvar();
        });
    }
    
    var collapse = document.getElementById('etapas-' + checklistId);
    if (collapse && !collapse.classList.contains('in')) {
        $(collapse).collapse('show');
    }
    
    setTimeout(function() {
        var novaEtapa = container.lastElementChild;
        if (novaEtapa) {
            novaEtapa.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }, 100);
    
    atualizarBotaoSalvar();
}

function escapeHtml(text) {
    if (!text) return '';
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function confirmarRemocaoEtapaPersonalizada(botao) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Confirmar remoção',
            text: 'Deseja remover esta etapa personalizada?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, remover',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                removerEtapaPersonalizada(botao);
            }
        });
    } else {
        if (confirm('Deseja remover esta etapa personalizada?')) {
            removerEtapaPersonalizada(botao);
        }
    }
}

function removerEtapaPersonalizada(botao) {
    var etapaItem = botao.closest('.etapa-item');
    if (!etapaItem) return;
    
    var etapaId = etapaItem.getAttribute('data-etapa-id');
    var checklistId = etapaItem.getAttribute('data-checklist-id');
    
    var isTemporaria = etapaId && etapaId.toString().startsWith('temp_');
    
    if (isTemporaria) {
        if (etapasPersonalizadas[checklistId]) {
            etapasPersonalizadas[checklistId] = etapasPersonalizadas[checklistId].filter(function(e) {
                return e.id !== etapaId;
            });
            
            if (etapasPersonalizadas[checklistId].length === 0) {
                delete etapasPersonalizadas[checklistId];
            }
        }

        etapaItem.remove();
        reordenarEtapasPersonalizadas(checklistId);
        atualizarChecklistPorEtapas(checklistId);
        atualizarContadorEtapas(checklistId);
        atualizarBotaoSalvar();
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Removida!',
                text: 'Etapa personalizada removida com sucesso.',
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            mostrarToast('Etapa personalizada removida com sucesso!', 'success');
        }
        
        return;
    }
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Removendo...',
            text: 'Por favor, aguarde.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
    
    $.ajax({
        url: base_url + 'chamados/excluir_etapa_personalizada',
        type: 'POST',
        data: {
            etapa_id: etapaId
        },
        dataType: 'json',
        success: function(response) {
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }
            
            if (response.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Removida!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    mostrarToast(response.message, 'success');
                }
                
                if (etapasPersonalizadas[checklistId]) {
                    etapasPersonalizadas[checklistId] = etapasPersonalizadas[checklistId].filter(function(e) {
                        return e.id !== etapaId;
                    });
                    
                    if (etapasPersonalizadas[checklistId].length === 0) {
                        delete etapasPersonalizadas[checklistId];
                    }
                }
                
                etapaItem.remove();
                reordenarEtapasPersonalizadas(checklistId);
                atualizarChecklistPorEtapas(checklistId);
                atualizarContadorEtapas(checklistId);
                atualizarBotaoSalvar();
                
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: response.message || 'Erro ao remover etapa'
                    });
                } else {
                    mostrarToast(response.message || 'Erro ao remover etapa', 'error');
                }
            }
        },
        error: function(xhr, status, error) {
            if (typeof Swal !== 'undefined') {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Erro de conexão ao remover etapa: ' + error
                });
            } else {
                mostrarToast('Erro de conexão ao remover etapa', 'error');
            }
        }
    });
}

function reordenarEtapasPersonalizadas(checklistId) {
    var container = document.getElementById('etapas-personalizadas-' + checklistId);
    if (!container) return;
    
    var etapas = container.querySelectorAll('.etapa-item');
    
    for (var i = 0; i < etapas.length; i++) {
        var badge = etapas[i].querySelector('.badge');
        if (badge) {
            badge.innerHTML = i + 1;
        }
    }
    
    if (etapasPersonalizadas[checklistId]) {
        for (var j = 0; j < etapasPersonalizadas[checklistId].length; j++) {
            etapasPersonalizadas[checklistId][j].ordem = j;
        }
    }
}

function atualizarContadorEtapas(checklistId) {
    var container = document.getElementById('etapas-container-' + checklistId);
    var personalizadasContainer = document.getElementById('etapas-personalizadas-' + checklistId);
    
    if (!container) return;
    
    var totalEtapas = container.querySelectorAll('.etapa-item').length;
    var totalPersonalizadas = personalizadasContainer ? personalizadasContainer.querySelectorAll('.etapa-item').length : 0;
    var total = totalEtapas + totalPersonalizadas;
    
    var badge = document.querySelector('.checklist-item[data-checklist-id="' + checklistId + '"] .badge');
    if (badge) {
        badge.innerHTML = total + ' etapa' + (total !== 1 ? 's' : '');
    }
}

function verificarChecklistsAssociados(checklistsIds, chamadoId) {
    return new Promise(function(resolve, reject) {
        var url = base_url + 'chamados/verificar_checklists_associados';
        
        var formData = new URLSearchParams();
        formData.append('chamado_id', chamadoId);
        formData.append('checklists_ids', JSON.stringify(checklistsIds));
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData,
            cache: 'no-cache'
        })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('Erro HTTP: ' + response.status);
            }
            return response.text();
        })
        .then(function(text) {
            try {
                var resposta = JSON.parse(text);
                
                if (!resposta.associados) {
                    resposta.associados = [];
                }
                
                if (!Array.isArray(resposta.associados)) {
                    resposta.associados = [];
                }
                
                resolve(resposta);
            } catch(e) {
                console.error('Erro ao parsear JSON:', e);
                console.error('Texto que causou erro:', text);
                reject(new Error('Resposta inválida do servidor'));
            }
        })
        .catch(function(error) {
            console.error('Erro na requisição Fetch:', error);
            reject(error);
        });
    });
}

function salvarChecklist() {
    var btn = document.getElementById('btnSaveChecklist');
    if (!btn) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Botão não encontrado'
            });
        } else {
            alert('Erro: Botão não encontrado');
        }
        return false;
    }
    
    var originalText = btn.innerText;
    btn.disabled = true;
    btn.innerText = 'Verificando...';

    var form = document.getElementById('form-associar-checklist-server');
    
    if (!form) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Formulário não encontrado!'
            });
        } else {
            alert('Erro: Formulário não encontrado!');
        }
        btn.disabled = false;
        btn.innerText = originalText;
        return false;
    }

    var checklistsSelecionados = form.querySelectorAll('input[name="checklists[]"]:checked');
    var etapasPersonalizadasSelecionadas = form.querySelectorAll('input[name^="etapas_personalizadas"]:checked');
    
    var temChecklistComEtapas = false;
    var checklistsIds = [];
    var checklistsComEtapasIds = [];
    
    for (var i = 0; i < checklistsSelecionados.length; i++) {
        var checklistId = checklistsSelecionados[i].value;
        checklistsIds.push(checklistId);
        if (checklistTemEtapaSelecionada(checklistId)) {
            temChecklistComEtapas = true;
            checklistsComEtapasIds.push(checklistId);
        }
    }
    
    if (!temChecklistComEtapas && etapasPersonalizadasSelecionadas.length === 0) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Selecione pelo menos um checklist com pelo menos uma etapa.',
                confirmButtonColor: '#3085d6'
            });
        } else {
            alert('Selecione pelo menos um checklist com pelo menos uma etapa.');
        }
        btn.disabled = false;
        btn.innerText = originalText;
        return false;
    }

    var chamado_id = document.getElementById('id_chamado').value;
    
    btn.innerText = 'Verificando...';

    verificarChecklistsAssociados(checklistsIds, chamado_id)
        .then(function(resultado) {
            if (!resultado.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro na verificação',
                        text: resultado.error || 'Erro ao verificar checklists'
                    });
                } else {
                    alert('Erro na verificação: ' + (resultado.error || 'Erro desconhecido'));
                }
                btn.disabled = false;
                btn.innerText = originalText;
                return;
            }

            var temAssociados = resultado.associados && 
                               Array.isArray(resultado.associados) && 
                               resultado.associados.length > 0;
            
            if (temAssociados) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Checklists já associados',
                        html: 'Os seguintes checklists <strong>já estão associados</strong> a este chamado e não podem ser adicionados novamente:<br><br>' + 
                              '<div style="text-align: left; max-height: 200px; overflow-y: auto; padding: 10px; background-color: #f8f9fa; border-radius: 5px;">' +
                              resultado.associados.map(function(item) { 
                                  return '<div style="padding: 5px; border-bottom: 1px solid #dee2e6;"><i class="fa fa-times-circle" style="color: #dc3545; margin-right: 8px;"></i>' + item + '</div>';
                              }).join('') +
                              '</div><br>' +
                              'Por favor, desmarque estes checklists e tente novamente.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Entendi'
                    });
                } else {
                    alert('Os seguintes checklists já estão associados: ' + resultado.associados.join(', ') + '. Por favor, desmarque-os.');
                }
                
                btn.disabled = false;
                btn.innerText = originalText;
                return;
            }
            btn.innerText = 'Salvando...';
            executarSalvamento(form, checklistsSelecionados, etapasPersonalizadasSelecionadas, chamado_id, btn, originalText);
        })
        .catch(function(error) {
            console.error('Erro ao verificar checklists associados (catch):', error);
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro na verificação',
                    text: 'Não foi possível verificar checklists já associados. Erro: ' + error.message,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            } else {
                alert('Erro na verificação: ' + error.message);
            }
            
            btn.disabled = false;
            btn.innerText = originalText;
        });
    
    return false;
}

function executarSalvamento(form, checklistsSelecionados, etapasPersonalizadasSelecionadas, chamado_id, btn, originalText) {
    var formData = [];
    
    for (var i = 0; i < checklistsSelecionados.length; i++) {
        var checklistId = checklistsSelecionados[i].value;
        var dataInicioInput = document.getElementById('data_inicio_' + checklistId);
        var dataInicio = dataInicioInput ? dataInicioInput.value : '';
        
        formData.push('checklists[]=' + encodeURIComponent(checklistId));
        
        var dataBD = converterDataParaBD(dataInicio);
        formData.push('data_inicio_checklist[' + checklistId + ']=' + encodeURIComponent(dataBD));
    }
    
    var etapasSelecionadas = form.querySelectorAll('input[name^="etapas["]:checked');
    for (var i = 0; i < etapasSelecionadas.length; i++) {
        var name = etapasSelecionadas[i].name;
        var value = etapasSelecionadas[i].value;
        formData.push(encodeURIComponent(name) + '=' + encodeURIComponent(value));
    }
    
    for (var i = 0; i < etapasPersonalizadasSelecionadas.length; i++) {
        var checkbox = etapasPersonalizadasSelecionadas[i];
        var name = checkbox.name;
        var etapaId = checkbox.value;
        
        var match = name.match(/etapas_personalizadas\[(\d+)\]\[\]/);
        if (match) {
            var checklistId = match[1];

            if (etapasPersonalizadas[checklistId]) {
                var etapa = etapasPersonalizadas[checklistId].find(function(e) {
                    return e.id === etapaId;
                });
                
                if (etapa) {
                    
                    formData.push('etapas_personalizadas_dados[' + checklistId + '][' + etapaId + '][nome]=' + encodeURIComponent(etapa.nome));
                    formData.push('etapas_personalizadas_dados[' + checklistId + '][' + etapaId + '][descricao]=' + encodeURIComponent(etapa.descricao || ''));
                    formData.push('etapas_personalizadas_dados[' + checklistId + '][' + etapaId + '][prazo_padrao]=' + encodeURIComponent(etapa.prazo_padrao));
                    formData.push('etapas_personalizadas_dados[' + checklistId + '][' + etapaId + '][responsavel]=' + encodeURIComponent(etapa.responsavel || ''));
                    formData.push('etapas_personalizadas_dados[' + checklistId + '][' + etapaId + '][ordem]=' + encodeURIComponent(etapa.ordem));
                    formData.push('etapas_personalizadas_dados[' + checklistId + '][' + etapaId + '][data_inicio]=' + encodeURIComponent(etapa.data_inicio || ''));
                    formData.push('etapas_personalizadas_dados[' + checklistId + '][' + etapaId + '][data_prazo]=' + encodeURIComponent(etapa.data_prazo || ''));
                    
                    formData.push('etapas_personalizadas_selecionadas[' + checklistId + '][]=' + encodeURIComponent(etapaId));
                } else {
                    console.warn('Etapa não encontrada em etapasPersonalizadas:', etapaId);
                }
            } else {
                console.warn('Checklist não encontrado em etapasPersonalizadas:', checklistId);
            }
        }
    }
    
    formData.push('chamado_id=' + encodeURIComponent(chamado_id));
    
    var fullData = formData.join('&');
    var url = base_url + 'chamados/associar_checklist';

    function fecharModal() {
        var modal = document.getElementById('associarChecklistModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('in');
            document.body.classList.remove('modal-open');
            var backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) backdrop.remove();
        }
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onload = function() {
        try {
            var resposta = JSON.parse(xhr.responseText);
            if (resposta.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Checklists associados com sucesso!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    mostrarToast('Checklists associados com sucesso!', 'success');
                }
                
                fecharModal();
                
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: resposta.error || 'Erro desconhecido'
                    });
                } else {
                    mostrarToast('Erro: ' + (resposta.error || 'Erro desconhecido'), 'error');
                }
                btn.disabled = false;
                btn.innerText = originalText;
            }
        } catch(e) {
            console.error('Erro ao parsear resposta:', e);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: 'Checklists associados com sucesso!',
                    showConfirmButton: false,
                    timer: 1500
                });
            } else {
                mostrarToast('Checklists associados com sucesso!', 'success');
            }
            
            fecharModal();
            
            setTimeout(function() {
                window.location.reload();
            }, 1500);
        }
    };
    
    xhr.onerror = function() {
        console.error('Erro na requisição');
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Checklists associados com sucesso!',
                showConfirmButton: false,
                timer: 1500
            });
        } else {
            mostrarToast('Checklists associados com sucesso!', 'success');
        }
        
        fecharModal();
        
        setTimeout(function() {
            window.location.reload();
        }, 1500);
    };
    
    xhr.ontimeout = function() {
        console.error('Timeout na requisição');
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Checklists associados com sucesso!',
                showConfirmButton: false,
                timer: 1500
            });
        } else {
            mostrarToast('Checklists associados com sucesso!', 'success');
        }
        
        fecharModal();
        
        setTimeout(function() {
            window.location.reload();
        }, 1500);
    };
    
    xhr.send(fullData);
}

function aplicarMascaraData(input) {
    if (!input) return;
    
    input.addEventListener('input', function(e) {
        let value = this.value.replace(/\D/g, '');
        
        if (value.length > 2) {
            value = value.substring(0, 2) + '/' + value.substring(2);
        }
        if (value.length > 5) {
            value = value.substring(0, 5) + '/' + value.substring(5, 9);
        }
        
        this.value = value;
    });
}

function validarData(data) {
    if (!data) return true;
    
    var partes = data.split('/');
    if (partes.length !== 3) return false;
    
    var dia = parseInt(partes[0], 10);
    var mes = parseInt(partes[1], 10);
    var ano = parseInt(partes[2], 10);
    
    if (isNaN(dia) || isNaN(mes) || isNaN(ano)) return false;
    if (mes < 1 || mes > 12) return false;
    
    var diasNoMes = new Date(ano, mes, 0).getDate();
    if (dia < 1 || dia > diasNoMes) return false;
    
    return true;
}

function salvarEtapa(etapaId) {
    var dataInicio = $('#data_inicio_etapa_' + etapaId).val();
    var dataPrazo = $('#data_prazo_etapa_' + etapaId).val();
    var responsavel = $('#responsavel_etapa_' + etapaId).val();
    
    var concluidaCheckbox = $('#etapa_concluida_' + etapaId);
    var concluida = concluidaCheckbox.is(':checked') ? 1 : 0;
    
    if (concluida == 1) {
        if (!responsavel || responsavel === '') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'Para concluir uma etapa, é necessário selecionar um responsável.',
                    confirmButtonColor: '#3085d6'
                });
            } else {
                alert('Para concluir uma etapa, é necessário selecionar um responsável.');
            }
            
            concluidaCheckbox.prop('checked', false);
            
            var row = $('#etapa-row-' + etapaId);
            row.removeClass('success');
            
            var label = concluidaCheckbox.next('span');
            
            var dataPrazoText = $('#data_prazo_etapa_' + etapaId).val();
            if (dataPrazoText) {
                var partes = dataPrazoText.split('/');
                if (partes.length === 3) {
                    var dataPrazoObj = new Date(partes[2], partes[1] - 1, partes[0]);
                    var hoje = new Date();
                    hoje.setHours(0, 0, 0, 0);
                    
                    if (dataPrazoObj < hoje) {
                        row.addClass('danger');
                        label.removeClass('label-success label-default').addClass('label-danger').text('Atrasado');
                    } else {
                        label.removeClass('label-success label-danger').addClass('label-default').text('Pendente');
                    }
                } else {
                    label.removeClass('label-success label-danger').addClass('label-default').text('Pendente');
                }
            } else {
                label.removeClass('label-success label-danger').addClass('label-default').text('Pendente');
            }
            
            return;
        }
    }
    
    if (dataInicio && !validarData(dataInicio)) {
        if (typeof Swal !== 'undefined') {
            Swal.fire('Erro', 'Data de início inválida', 'error');
        } else {
            alert('Data de início inválida');
        }
        return;
    }
    
    if (dataPrazo && !validarData(dataPrazo)) {
        if (typeof Swal !== 'undefined') {
            Swal.fire('Erro', 'Data de prazo inválida', 'error');
        } else {
            alert('Data de prazo inválida');
        }
        return;
    }
    
    var dataInicioBD = dataInicio ? converterDataParaBD(dataInicio) : null;
    var dataPrazoBD = dataPrazo ? converterDataParaBD(dataPrazo) : null;
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Salvando...',
            text: 'Por favor, aguarde.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    $.ajax({
        url: base_url + 'chamados/atualizar_etapa_checklist',
        type: 'POST',
        data: {
            etapa_id: etapaId,
            data_inicio: dataInicioBD,
            data_prazo: dataPrazoBD,
            responsavel: responsavel,
            concluida: concluida
        },
        dataType: 'json',
        success: function(response) {
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }
            
            if (response.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: response.message || 'Etapa atualizada',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    mostrarToast(response.message || 'Etapa atualizada com sucesso!', 'success');
                }
                
                atualizarInterfaceEtapa(etapaId, concluida, dataPrazoBD);
                
                if (response.message && response.message !== 'Nenhuma alteração detectada') {
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: response.message || 'Erro ao atualizar etapa'
                    });
                } else {
                    mostrarToast(response.message || 'Erro ao atualizar etapa', 'error');
                }
                
                if (response.campo === 'responsavel') {
                    concluidaCheckbox.prop('checked', false);
                    atualizarInterfaceEtapa(etapaId, 0, dataPrazoBD);
                }
            }
        },
        error: function(xhr, status, error) {
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }
            
            if (xhr.status === 200) {
                try {
                    var responseText = xhr.responseText;
                    responseText = responseText.trim();
                    var response = JSON.parse(responseText);
                    
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: response.message || 'Etapa atualizada',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        
                        atualizarInterfaceEtapa(etapaId, concluida, dataPrazoBD);
                        
                        if (response.message && response.message !== 'Nenhuma alteração detectada') {
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        }
                        return;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: response.message || 'Erro ao atualizar etapa'
                        });
                        
                        if (response.campo === 'responsavel') {
                            concluidaCheckbox.prop('checked', false);
                            atualizarInterfaceEtapa(etapaId, 0, dataPrazoBD);
                        }
                        return;
                    }
                } catch(e) {
                    console.error('Erro ao parsear resposta:', e);
                }
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Erro de conexão',
                text: 'Erro ao salvar etapa. Verifique sua conexão e tente novamente.'
            });
        }
    });
    
    function atualizarInterfaceEtapa(etapaId, concluida, dataPrazoBD) {
        var row = $('#etapa-row-' + etapaId);
        var concluidaCheckbox = $('#etapa_concluida_' + etapaId);
        
        row.removeClass('success danger');
        var label = concluidaCheckbox.next('span');
        
        if (concluida == 1) {
            row.addClass('success');
            label.removeClass('label-default label-danger').addClass('label-success').text('Concluído');
        } else {
            if (dataPrazoBD) {
                var hoje = new Date();
                hoje.setHours(0, 0, 0, 0);
                var prazoDate = new Date(dataPrazoBD + 'T00:00:00');
                
                if (prazoDate < hoje) {
                    row.addClass('danger');
                    label.removeClass('label-success label-default').addClass('label-danger').text('Atrasado');
                } else {
                    label.removeClass('label-success label-danger').addClass('label-default').text('Pendente');
                }
            } else {
                label.removeClass('label-success label-danger').addClass('label-default').text('Pendente');
            }
        }
    }
}

function inicializarChecklistModal() {
    function limparEtapasPersonalizadas() {
        etapasPersonalizadas = {};
        var containers = document.querySelectorAll('.etapas-personalizadas');
        for (var i = 0; i < containers.length; i++) {
            containers[i].innerHTML = '';
        }
    }
    
    function desmarcarTodasEtapasInicial() {
        var todasEtapas = document.querySelectorAll('.etapa-checkbox');
        for (var i = 0; i < todasEtapas.length; i++) {
            todasEtapas[i].checked = false;
        }
        
        var todosChecklists = document.querySelectorAll('.checklist-checkbox');
        for (var j = 0; j < todosChecklists.length; j++) {
            todosChecklists[j].checked = false;
        }
    }
    
    function configurarEventos() {
        var checkboxes = document.querySelectorAll('.checklist-checkbox');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].onchange = function() {
                var checklistItem = this.closest('.checklist-item');
                if (!checklistItem) return;
                
                var checklistId = checklistItem.getAttribute('data-checklist-id');
                var isChecked = this.checked;
                
                var etapas = document.querySelectorAll('.etapa-checklist-' + checklistId);
                for (var j = 0; j < etapas.length; j++) {
                    etapas[j].checked = isChecked;
                }
                
                atualizarBotaoSalvar();
            };
        }

        var etapas = document.querySelectorAll('.etapa-checkbox:not(.etapa-personalizada-checkbox)');
        for (var i = 0; i < etapas.length; i++) {
            etapas[i].addEventListener('change', function(e) {
                var etapaCheckbox = e.target;
                var etapaItem = etapaCheckbox.closest('.etapa-item');
                if (!etapaItem) return;
                
                var checklistId = etapaItem.getAttribute('data-checklist-id');
                
                if (etapaCheckbox.checked) {
                    var checklistCheckbox = document.querySelector('.checklist-checkbox[value="' + checklistId + '"]');
                    if (checklistCheckbox) {
                        checklistCheckbox.checked = true;
                    }
                } else {
                    atualizarChecklistPorEtapas(checklistId);
                }
                
                atualizarBotaoSalvar();
            });
        }
    }

    function inicializarDatepickers() {
        if (typeof $ !== 'undefined' && $.fn.datetimepicker) {
            $('.datetimepicker-checklist').datetimepicker({
                format: 'DD/MM/YYYY',
                locale: 'pt-br',
                useCurrent: true,
                ignoreReadonly: true
            });
            
            $('.datetimepicker-checklist input').each(function() {
                var $input = $(this);
                if (!$input.val()) {
                    var hoje = new Date();
                    var dia = String(hoje.getDate()).padStart(2, '0');
                    var mes = String(hoje.getMonth() + 1).padStart(2, '0');
                    var ano = hoje.getFullYear();
                    $input.val(dia + '/' + mes + '/' + ano);
                }
                var inputId = $input.attr('id') || '';
                var checklistId = (inputId.indexOf('data_inicio_') === 0)
                    ? inputId.replace('data_inicio_', '')
                    : null;
                if (checklistId) {
                    $input.closest('.datetimepicker-checklist')
                        .off('dp.change.recalc')
                        .on('dp.change.recalc', function() {
                            recalcularDatasTodasEtapas(checklistId);
                        });
                    $input.off('change.recalc input.recalc')
                        .on('change.recalc input.recalc', function() {
                            recalcularDatasTodasEtapas(checklistId);
                        });
                }
            });
        } else {
            console.warn('jQuery ou datetimepicker não disponível');
        }
    }

    function inicializarBusca() {
        var searchInput = document.getElementById('search-checklist');
        if (searchInput) {
            searchInput.addEventListener('keyup', debounce(filtrarChecklists, 300));
            searchInput.addEventListener('paste', function() {
                setTimeout(filtrarChecklists, 100);
            });
        }
    }

    var associarBtns = document.querySelectorAll('.associarChecklist');
    for (var i = 0; i < associarBtns.length; i++) {
        associarBtns[i].onclick = function() {
            limparEtapasPersonalizadas();
            
            var modal = document.getElementById('associarChecklistModal');
            if (modal) {
                modal.style.display = 'block';
                modal.classList.add('in');
                document.body.classList.add('modal-open');
                
                if (!document.querySelector('.modal-backdrop')) {
                    var backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade in';
                    document.body.appendChild(backdrop);
                }
                
                var searchInput = document.getElementById('search-checklist');
                if (searchInput) {
                    searchInput.value = '';
                }
                
                desmarcarTodasEtapasInicial();
                
                var checklists = document.querySelectorAll('.checklist-item');
                for (var j = 0; j < checklists.length; j++) {
                    checklists[j].style.display = 'block';
                }
                
                var resultsCount = document.getElementById('search-results-count');
                if (resultsCount) {
                    resultsCount.innerHTML = 'Mostrando todos os checklists (' + checklists.length + ')';
                }
                
                setTimeout(configurarEventos, 100);
                setTimeout(inicializarDatepickers, 200);
                setTimeout(atualizarBotaoSalvar, 200);
                setTimeout(function() {
                    var cls = document.querySelectorAll('.checklist-item');
                    for (var k = 0; k < cls.length; k++) {
                        var clId = cls[k].getAttribute('data-checklist-id');
                        if (clId) recalcularDatasTodasEtapas(clId);
                    }
                }, 300);
            }
        };
    }

    var closeBtns = document.querySelectorAll('#associarChecklistModal [data-dismiss="modal"], #associarChecklistModal .close, #associarChecklistModal .btn-default[data-dismiss="modal"]');
    for (var i = 0; i < closeBtns.length; i++) {
        closeBtns[i].onclick = function() {
            var modal = document.getElementById('associarChecklistModal');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.remove('in');
                document.body.classList.remove('modal-open');
                var backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            }
        };
    }

    inicializarBusca();
    configurarEventos();
    inicializarDatepickers();
    atualizarBotaoSalvar();
}

function recalcularDatasTodasEtapas(checklistId) {
    var dataInicioInput = document.getElementById('data_inicio_' + checklistId);
    if (!dataInicioInput || !dataInicioInput.value) return;

    var dataAtual = converterDataParaDate(dataInicioInput.value);
    if (!dataAtual) return;

    var container = document.getElementById('etapas-container-' + checklistId);
    if (!container) return;

    var etapasItems = container.querySelectorAll('.etapa-item');
    for (var i = 0; i < etapasItems.length; i++) {
        var item = etapasItems[i];
        var prazo = parseInt(item.getAttribute('data-prazo-padrao')) || 0;
        var spanDatas = item.querySelector('.etapa-datas-calc');
        if (!spanDatas) continue;

        var dataInicioEtapa = new Date(dataAtual);

        if (prazo > 0) {
            var dataPrazoEtapa = calcularDataPrazo(new Date(dataAtual), prazo);

            spanDatas.style.display = '';
            var strongInicio = spanDatas.querySelector('.etapa-data-inicio-calc strong');
            var strongPrazo = spanDatas.querySelector('.etapa-data-prazo-calc strong');
            if (strongInicio) strongInicio.textContent = formatarDataBR(dataInicioEtapa);
            if (strongPrazo) strongPrazo.textContent = formatarDataBR(dataPrazoEtapa);

            dataAtual = calcularProximaDataUtil(dataPrazoEtapa);
        } else {
            spanDatas.style.display = '';
            var strongInicio2 = spanDatas.querySelector('.etapa-data-inicio-calc strong');
            var strongPrazo2 = spanDatas.querySelector('.etapa-data-prazo-calc strong');
            if (strongInicio2) strongInicio2.textContent = formatarDataBR(dataInicioEtapa);
            var spanPrazo2 = spanDatas.querySelector('.etapa-data-prazo-calc');
            if (spanPrazo2) spanPrazo2.style.display = 'none';
        }
    }

    var personalizadasContainer = document.getElementById('etapas-personalizadas-' + checklistId);
    if (personalizadasContainer && etapasPersonalizadas[checklistId]) {
        var persItems = personalizadasContainer.querySelectorAll('.etapa-item');
        for (var j = 0; j < persItems.length; j++) {
            var persId = persItems[j].getAttribute('data-etapa-id');
            var etapa = etapasPersonalizadas[checklistId].find(function(e) { return e.id === persId; });
        }
    }
}

function inicializarBotoesExclusao() {
    $(document).on('click', '.excluir-etapa-personalizada', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var botao = $(this);
        var etapaId = botao.data('etapa-id');
        var row = botao.closest('tr');
        
        if (!etapaId) {
            console.error('ID da etapa não encontrado');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'ID da etapa não encontrado'
                });
            }
            return;
        }
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Confirmar remoção',
                text: 'Deseja remover esta etapa personalizada? Esta ação não pode ser desfeita.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, remover',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    executarExclusaoEtapaPersonalizada(etapaId, row);
                }
            });
        } else {
            if (confirm('Deseja remover esta etapa personalizada?')) {
                executarExclusaoEtapaPersonalizada(etapaId, row);
            }
        }
    });
}

function executarExclusaoEtapaPersonalizada(etapaId, row) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Removendo...',
            text: 'Por favor, aguarde.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
    
    $.ajax({
        url: base_url + 'chamados/excluir_etapa_personalizada',
        type: 'POST',
        data: {
            etapa_id: etapaId
        },
        dataType: 'json',
        contentType: 'application/x-www-form-urlencoded',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }
            
            if (response.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Removida!',
                        text: response.message || 'Etapa personalizada removida com sucesso.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    mostrarToast(response.message || 'Etapa personalizada removida com sucesso!', 'success');
                }
                
                if (row && row.length) {
                    row.fadeOut(300, function() {
                        $(this).remove();
                    });
                } else {
                    var linha = $('#etapa-row-' + etapaId);
                    if (linha.length) {
                        linha.fadeOut(300, function() {
                            $(this).remove();
                        });
                    }
                }
                
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: response.message || 'Erro ao remover etapa'
                    });
                } else {
                    mostrarToast(response.message || 'Erro ao remover etapa', 'error');
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro na requisição:');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response Text:', xhr.responseText);
            
            if (typeof Swal !== 'undefined') {
                Swal.close();
                
                var errorMessage = 'Erro ao remover etapa.';
                try {
                    if (xhr.responseText && xhr.responseText.trim() !== '') {
                        var response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    }
                } catch(e) {
                    console.error('Erro ao parsear resposta:', e);
                    errorMessage = 'Erro de comunicação com o servidor. A etapa pode ter sido removida, mas não foi possível confirmar.';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de comunicação',
                    text: errorMessage,
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                mostrarToast('Erro ao remover etapa. Recarregue a página para verificar.', 'error');
                setTimeout(function() {
                    window.location.reload();
                }, 2000);
            }
        }
    });
}

function inicializarFuncionalidadesEtapas() {
    if ($('.salvar-etapa').length > 0) {
        $('.salvar-etapa').off('click').on('click', function(e) {
            e.preventDefault();
            var etapaId = $(this).data('etapa-id');
            salvarEtapa(etapaId);
        });
    }
    
    if ($.fn.datetimepicker) {
        $('.datetimepicker-checklist-inline').datetimepicker({
            format: 'DD/MM/YYYY',
            locale: 'pt-br',
            useCurrent: false,
            ignoreReadonly: true
        });
        
        $(document).on('click', '.datetimepicker-checklist-inline .input-group-addon', function() {
            var input = $(this).siblings('input');
            if (input.data('DateTimePicker')) {
                input.data('DateTimePicker').show();
            }
        });
    }

    $('.data-inicio-etapa, .data-prazo-etapa').each(function() {
        aplicarMascaraData(this);
    });
    
    $('.etapa-concluida').each(function() {
        var $checkbox = $(this);
        var etapaId = $checkbox.data('etapa-id');
        
        if (!etapaId) {
            var parentRow = $checkbox.closest('tr');
            if (parentRow.length && parentRow.attr('id')) {
                etapaId = parentRow.attr('id').replace('etapa-row-', '');
                $checkbox.data('etapa-id', etapaId);
            }
        }
        
        if (etapaId) {
            if (!$checkbox.attr('id')) {
                $checkbox.attr('id', 'etapa_concluida_' + etapaId);
            }
            
            var row = $('#etapa-row-' + etapaId);
            
            $checkbox.off('change').on('change', function() {
                var isChecked = $(this).is(':checked');
                var label = $(this).next('span');
                
                if (isChecked) {
                    row.removeClass('danger').addClass('success');
                    label.removeClass('label-default label-danger').addClass('label-success').text('Concluído');
                } else {
                    row.removeClass('success');
                    
                    var dataPrazo = $('#data_prazo_etapa_' + etapaId).val();
                    if (dataPrazo) {
                        var partes = dataPrazo.split('/');
                        if (partes.length === 3) {
                            var dataPrazoObj = new Date(partes[2], partes[1] - 1, partes[0]);
                            var hoje = new Date();
                            hoje.setHours(0, 0, 0, 0);
                            
                            if (dataPrazoObj < hoje) {
                                row.addClass('danger');
                                label.removeClass('label-success label-default').addClass('label-danger').text('Atrasado');
                            } else {
                                label.removeClass('label-success label-danger').addClass('label-default').text('Pendente');
                            }
                        } else {
                            label.removeClass('label-success label-danger').addClass('label-default').text('Pendente');
                        }
                    } else {
                        label.removeClass('label-success label-danger').addClass('label-default').text('Pendente');
                    }
                }
            });
        }
    });
    
    $('select[name="responsavel"]').each(function() {
        var $select = $(this);
        var row = $select.closest('tr');
        
        if (row.length && row.attr('id')) {
            var etapaId = row.attr('id').replace('etapa-row-', '');
            if (etapaId) {
                var selectId = 'responsavel_etapa_' + etapaId;
                $select.attr('id', selectId);
                
                var etapaResponsavel = $select.data('responsavel');
                if (etapaResponsavel) {
                    $select.val(etapaResponsavel);
                } else {
                    var responsavelValue = row.data('responsavel');
                    if (responsavelValue) {
                        $select.val(responsavelValue);
                    }
                }
            }
        }
    });
    
    $('.data-inicio-etapa').each(function() {
        var $input = $(this);
        var etapaId = $input.data('etapa-id');
        
        if (etapaId && !$input.attr('id')) {
            $input.attr('id', 'data_inicio_etapa_' + etapaId);
        }
    });
    
    $('.data-prazo-etapa').each(function() {
        var $input = $(this);
        var etapaId = $input.data('etapa-id');
        
        if (etapaId && !$input.attr('id')) {
            $input.attr('id', 'data_prazo_etapa_' + etapaId);
        }
    });
    
    inicializarBotoesExclusao();
}

function inicializarModalAdicionarEtapa() {
    $('#adicionarEtapaModal').off('shown.bs.modal').on('shown.bs.modal', function() {
        configurarEventosDatasModalAdicionar();
        
        if (typeof $ !== 'undefined' && $.fn.datetimepicker) {
            $('.datetimepicker-modal-inline').datetimepicker({
                format: 'DD/MM/YYYY',
                locale: 'pt-br',
                useCurrent: false,
                ignoreReadonly: true
            });
        }
    });
}

function obterUltimaDataPrazoParaNovaEtapa(checklistId) {
    if (etapasPersonalizadas[checklistId] && etapasPersonalizadas[checklistId].length > 0) {
        var ult = etapasPersonalizadas[checklistId][etapasPersonalizadas[checklistId].length - 1];
        if (ult.data_prazo) {
            return formatarDataBR(calcularProximaDataUtil(converterDataParaDate(ult.data_prazo)));
        } else if (ult.data_inicio) {
            return ult.data_inicio;
        }
    }

    var dataInicioInput = document.getElementById('data_inicio_' + checklistId);
    var dataInicioStr = dataInicioInput ? dataInicioInput.value : '';

    if (!dataInicioStr) {
        return formatarDataBR(new Date());
    }

    var dataAtual = converterDataParaDate(dataInicioStr);
    if (!dataAtual) return dataInicioStr;

    var container = document.getElementById('etapas-container-' + checklistId);
    if (!container) return dataInicioStr;

    var etapasItems = container.querySelectorAll('.etapa-item');
    var ultimaDataPrazo = null;

    for (var i = 0; i < etapasItems.length; i++) {
        var etapaItem = etapasItems[i];
        var checkbox = etapaItem.querySelector('.etapa-checkbox');

        if (!checkbox || !checkbox.checked) continue;

        var smalls = etapaItem.querySelectorAll('small');
        var prazo = 0;
        for (var j = 0; j < smalls.length; j++) {
            var match = smalls[j].textContent.match(/(\d+)\s*dia/);
            if (match) {
                prazo = parseInt(match[1]);
                break;
            }
        }

        if (prazo > 0) {
            dataAtual = calcularDataPrazo(dataAtual, prazo);
            ultimaDataPrazo = dataAtual;
        }
    }

    if (ultimaDataPrazo) {
        return formatarDataBR(calcularProximaDataUtil(ultimaDataPrazo));
    }

    return dataInicioStr;
}

function calcularProximaDataUtil(dataReferencia) {
    var data = new Date(dataReferencia);
    data.setDate(data.getDate() + 1);
    
    while (data.getDay() === 6 || data.getDay() === 0) {
        data.setDate(data.getDate() + 1);
    }
    
    return data;
}

function abrirModalAdicionarEtapaExistente(chamadoChecklistId, checklistNome) {
    if (!checklistNome) {
        var checklistDiv = document.querySelector('.box.box-solid[data-checklist-id="' + chamadoChecklistId + '"]');
        if (checklistDiv) {
            var tituloElement = checklistDiv.querySelector('.box-title');
            if (tituloElement) {
                checklistNome = tituloElement.innerText.trim();
            }
        }
    }
    
    if (!checklistNome) {
        checklistNome = 'Checklist #' + chamadoChecklistId;
    }
    
    document.getElementById('etapa_existente_checklist_id').value = chamadoChecklistId;
    document.getElementById('etapa_existente_checklist_nome').value = checklistNome;
    document.getElementById('etapa_existente_nome').value = '';
    document.getElementById('etapa_existente_descricao').value = '';
    document.getElementById('etapa_existente_prazo').value = '0';
    document.getElementById('etapa_existente_data_inicio').value = '';
    document.getElementById('etapa_existente_data_prazo').value = '';
    
    $('#ultima_etapa_info').hide();
    
    var chamadoId = document.getElementById('chamado_id_hidden').value;
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Carregando...',
            text: 'Buscando informações do checklist',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
    
    obterUltimaEtapaChecklist(chamadoId, chamadoChecklistId)
        .then(function(resultado) {
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }
            
            ultimaEtapaChecklist = resultado.ultima_etapa;
            
            if (resultado.tem_etapas && resultado.ultima_etapa) {
                $('#ultima_etapa_info').show();
                var textoInfo = ' Última etapa: "' + resultado.ultima_etapa.nome + '"';
                
                if (resultado.ultima_etapa.data_prazo) {
                    var dataPrazo = new Date(resultado.ultima_etapa.data_prazo);
                    textoInfo += '<br>• Data de prazo: ' + formatarDataBR(dataPrazo);
                    
                    var proximaDataInicio = calcularProximaDataUtil(dataPrazo);
                    var dataInicioFormatada = formatarDataBR(proximaDataInicio);
                    document.getElementById('etapa_existente_data_inicio').value = dataInicioFormatada;
                    textoInfo += '<br>• Próxima data de início sugerida: ' + dataInicioFormatada;
                } else {
                    textoInfo += '<br>• Data de prazo não definida para a última etapa';
                }
                
                $('#ultima_etapa_texto').html(textoInfo);
                
                if (!document.getElementById('etapa_existente_data_inicio').value) {
                    var hoje = new Date();
                    document.getElementById('etapa_existente_data_inicio').value = formatarDataBR(hoje);
                }
                
                var prazoPadrao = parseInt(document.getElementById('etapa_existente_prazo').val()) || 0;
                if (prazoPadrao > 0) {
                    var dataInicio = converterDataParaDate(document.getElementById('etapa_existente_data_inicio').value);
                    if (dataInicio) {
                        var dataPrazoCalculada = calcularDataPrazo(dataInicio, prazoPadrao);
                        document.getElementById('etapa_existente_data_prazo').value = formatarDataBR(dataPrazoCalculada);
                    }
                }
            } else {
                var hoje = new Date();
                document.getElementById('etapa_existente_data_inicio').value = formatarDataBR(hoje);
                $('#ultima_etapa_info').hide();
            }
            
            $('#adicionarEtapaExistenteModal').modal({
                backdrop: 'static',
                keyboard: false
            });
        })
        .catch(function(error) {
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }
            
            console.error('Erro ao buscar última etapa:', error);
            
            var hoje = new Date();
            document.getElementById('etapa_existente_data_inicio').value = formatarDataBR(hoje);
            $('#ultima_etapa_info').hide();
            
            $('#adicionarEtapaExistenteModal').modal({
                backdrop: 'static',
                keyboard: false
            });
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Aviso',
                    text: 'Não foi possível buscar informações da última etapa. A data de início será a data atual.',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        });
}

function salvarEtapaExistente() {
    var chamadoChecklistId = document.getElementById('etapa_existente_checklist_id').value;
    var nome = document.getElementById('etapa_existente_nome').value.trim();
    var descricao = document.getElementById('etapa_existente_descricao').value.trim();
    var prazo = parseInt(document.getElementById('etapa_existente_prazo').value) || 0;
    var dataInicioStr = document.getElementById('etapa_existente_data_inicio').value;
    var dataPrazoStr = document.getElementById('etapa_existente_data_prazo').value;
    
    if (!nome) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Por favor, informe o nome da etapa.',
                confirmButtonColor: '#3085d6'
            });
        } else {
            alert('Por favor, informe o nome da etapa.');
        }
        return;
    }
    
    if (!dataInicioStr) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Por favor, informe a data de início.',
                confirmButtonColor: '#3085d6'
            });
        } else {
            alert('Por favor, informe a data de início.');
        }
        return;
    }
    
    var dataInicioDate = converterDataParaDate(dataInicioStr);
    if (!dataInicioDate || isNaN(dataInicioDate.getTime())) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Data de início inválida. Use o formato DD/MM/AAAA.',
                confirmButtonColor: '#3085d6'
            });
        } else {
            alert('Data de início inválida. Use o formato DD/MM/AAAA.');
        }
        return;
    }
    
    var dataPrazoDate = null;
    if (dataPrazoStr && dataPrazoStr.trim() !== '') {
        dataPrazoDate = converterDataParaDate(dataPrazoStr);
        if (!dataPrazoDate || isNaN(dataPrazoDate.getTime())) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'Data de prazo inválida. Use o formato DD/MM/AAAA.',
                    confirmButtonColor: '#3085d6'
                });
            } else {
                alert('Data de prazo inválida. Use o formato DD/MM/AAAA.');
            }
            return;
        }
    }
    
    if (prazo > 0 && (!dataPrazoStr || dataPrazoStr.trim() === '')) {
        dataPrazoDate = calcularDataPrazo(dataInicioDate, prazo);
        dataPrazoStr = formatarDataBR(dataPrazoDate);
    }
    
    var chamadoId = document.getElementById('chamado_id_hidden').value;
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Salvando...',
            text: 'Por favor, aguarde.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
    
    var dataInicioBD = dataInicioDate.getFullYear() + '-' + 
                       String(dataInicioDate.getMonth() + 1).padStart(2, '0') + '-' + 
                       String(dataInicioDate.getDate()).padStart(2, '0');
    
    var dataPrazoBD = null;
    if (dataPrazoDate) {
        dataPrazoBD = dataPrazoDate.getFullYear() + '-' + 
                      String(dataPrazoDate.getMonth() + 1).padStart(2, '0') + '-' + 
                      String(dataPrazoDate.getDate()).padStart(2, '0');
    }
    
    var formData = new URLSearchParams();
    formData.append('chamado_id', chamadoId);
    formData.append('chamado_checklist_id', chamadoChecklistId);
    formData.append('nome', nome);
    formData.append('descricao', descricao);
    formData.append('prazo_padrao', prazo);
    formData.append('data_inicio', dataInicioBD);
    if (dataPrazoBD) {
        formData.append('data_prazo', dataPrazoBD);
    }
    
    $.ajax({
        url: base_url + 'chamados/adicionar_etapa_checklist_existente',
        type: 'POST',
        data: formData.toString(),
        dataType: 'json',
        contentType: 'application/x-www-form-urlencoded',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }
            
            if (response.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: response.message || 'Etapa adicionada com sucesso!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    mostrarToast(response.message || 'Etapa adicionada com sucesso!', 'success');
                }
                
                $('#adicionarEtapaExistenteModal').modal('hide');
                
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: response.error || 'Erro ao adicionar etapa'
                    });
                } else {
                    mostrarToast(response.error || 'Erro ao adicionar etapa', 'error');
                }
            }
        },
        error: function(xhr, status, error) {
            if (typeof Swal !== 'undefined') {
                Swal.close();
                console.error('Erro detalhado:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de conexão',
                    text: 'Erro ao adicionar etapa. Verifique sua conexão e tente novamente.\nDetalhe: ' + error,
                    confirmButtonColor: '#3085d6'
                });
            } else {
                mostrarToast('Erro ao adicionar etapa: ' + error, 'error');
            }
        }
    });
}

function configurarEventosDatas() {
    $('#etapa_existente_data_inicio').off('change').on('change', function() {
        var dataInicio = $(this).val();
        var prazoPadrao = parseInt($('#etapa_existente_prazo').val()) || 0;
        
        if (dataInicio && prazoPadrao > 0) {
            var dataInicioDate = converterDataParaDate(dataInicio);
            if (dataInicioDate) {
                var dataPrazoCalculada = calcularDataPrazo(dataInicioDate, prazoPadrao);
                $('#etapa_existente_data_prazo').val(formatarDataBR(dataPrazoCalculada));
            }
        } else if (prazoPadrao === 0) {
            $('#etapa_existente_data_prazo').val('');
        }
    });
    
    $('#etapa_existente_prazo').off('change keyup').on('change keyup', function() {
        var prazoPadrao = parseInt($(this).val()) || 0;
        var dataInicio = $('#etapa_existente_data_inicio').val();
        
        if (prazoPadrao > 0 && dataInicio) {
            var dataInicioDate = converterDataParaDate(dataInicio);
            if (dataInicioDate) {
                var dataPrazoCalculada = calcularDataPrazo(dataInicioDate, prazoPadrao);
                $('#etapa_existente_data_prazo').val(formatarDataBR(dataPrazoCalculada));
            }
        } else if (prazoPadrao === 0) {
            $('#etapa_existente_data_prazo').val('');
        }
    });
}

function inicializarDatepickersModal() {
    if (typeof $ !== 'undefined' && $.fn.datetimepicker) {
        $('.datetimepicker-modal-inline').datetimepicker({
            format: 'DD/MM/YYYY',
            locale: 'pt-br',
            useCurrent: false,
            ignoreReadonly: true
        });
        
        $(document).on('click', '.datetimepicker-modal-inline .input-group-addon', function() {
            var input = $(this).siblings('input');
            if (input.data('DateTimePicker')) {
                input.data('DateTimePicker').show();
            }
        });
        
        $('#adicionarEtapaExistenteModal').off('shown.bs.modal').on('shown.bs.modal', function() {
            configurarEventosDatas();
        });
    } else {
        console.warn('jQuery ou datetimepicker não disponível');
    }
}

var ultimaEtapaChecklist = null;

function obterUltimaEtapaChecklist(chamadoId, checklistId) {
    return new Promise(function(resolve, reject) {
        $.ajax({
            url: base_url + 'chamados/obter_ultima_etapa_checklist',
            type: 'POST',
            data: {
                chamado_id: chamadoId,
                checklist_id: checklistId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    resolve(response);
                } else {
                    reject(response.error);
                }
            },
            error: function(xhr, status, error) {
                reject(error);
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        inicializarChecklistModal();
        inicializarFuncionalidadesEtapas();
        inicializarModalAdicionarEtapa();
        inicializarDatepickersModal();
    });
} else {
    inicializarChecklistModal();
    inicializarFuncionalidadesEtapas();
    inicializarModalAdicionarEtapa();
    inicializarDatepickersModal();
}

if (typeof jQuery !== 'undefined' && typeof $ !== 'undefined') {
    $(document).ready(function() {
        if (typeof inicializarFuncionalidadesEtapas === 'function') {
            setTimeout(function() {
                inicializarFuncionalidadesEtapas();
            }, 100);
        }
        if (typeof inicializarModalAdicionarEtapa === 'function') {
            setTimeout(function() {
                inicializarModalAdicionarEtapa();
            }, 100);
        }
    });
}