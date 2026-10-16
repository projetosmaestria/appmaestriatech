// Função para adicionar uma nova etapa
function adicionarNovaEtapa() {
    console.log("Função adicionarNovaEtapa chamada!");
    let etapasAtuais = document.querySelectorAll('.etapa-item');
    let contador = etapasAtuais.length;
    let container = document.getElementById('etapas-container');

    let novaEtapa = document.createElement('div');
    novaEtapa.className = 'etapa-item';
    novaEtapa.id = 'etapa-' + contador;

    novaEtapa.innerHTML = `
        <div style="background:#f5f5f5; padding:10px 15px; border-bottom:1px solid #ddd; overflow:hidden;">
            <h4 style="float:left; margin:0; font-size:16px;">Etapa ${contador + 1}</h4>
            <div style="float:right;">
                <button type="button" class="btn btn-sm btn-danger" onclick="removerEtapa('etapa-${contador}')">
                    <i class="fa fa-trash"></i> Remover
                </button>
            </div>
        </div>
        <div style="padding:15px;">
            <div style="margin-bottom:15px;">
                <label for="etapas_${contador}_nome" style="display:block; margin-bottom:5px; font-weight:600;">Nome da Etapa *</label>
                <input type="text" name="etapas[${contador}][nome]" id="etapas_${contador}_nome" class="form-control" placeholder="Digite o nome da etapa" required style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px;">
            </div>
            <div style="margin-bottom:15px;">
                <label for="etapas_${contador}_descricao" style="display:block; margin-bottom:5px; font-weight:600;">Descrição</label>
                <textarea name="etapas[${contador}][descricao]" id="etapas_${contador}_descricao" class="form-control" rows="2" placeholder="Descrição da etapa" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px;"></textarea>
            </div>
            
            <!-- NOVO CAMPO: Prazo Padrão (dias) -->
            <div style="margin-bottom:15px;">
                <label for="etapas_${contador}_prazo_padrao" style="display:block; margin-bottom:5px; font-weight:600;">Prazo Padrão (dias)</label>
                <input type="number" name="etapas[${contador}][prazo_padrao]" id="etapas_${contador}_prazo_padrao" class="form-control" placeholder="Quantidade de dias" min="0" step="1" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px;">
                <small class="text-muted">Apenas números inteiros (dias)</small>
            </div>
        </div>
        <input type="hidden" name="etapas[${contador}][ordem]" value="${contador}">
        <input type="hidden" name="etapas[${contador}][status]" value="1">
        <input type="hidden" name="etapas[${contador}][responsavel]" value="">
        <input type="hidden" name="etapas[${contador}][data_inicio]" value="">
    `;

    container.appendChild(novaEtapa);
    console.log("Nova etapa adicionada: etapa-" + contador);
}

// Função para remover uma etapa
function removerEtapa(id) {
    console.log("Função removerEtapa chamada para: " + id);
    let etapa = document.getElementById(id);
    if (etapa && id !== 'etapa-0') {
        etapa.remove();
        console.log("Etapa removida: " + id);
        
        // Renomear as etapas restantes para manter a numeração correta
        renomearEtapas();
    }
}

// Função para renomear as etapas após remoção
function renomearEtapas() {
    let etapas = document.querySelectorAll('.etapa-item');
    etapas.forEach((etapa, index) => {
        // Atualizar o ID
        etapa.id = 'etapa-' + index;
        
        // Atualizar o título
        let titulo = etapa.querySelector('h4');
        if (titulo) {
            titulo.textContent = 'Etapa ' + (index + 1);
        }
        
        // Atualizar os atributos name e id dos inputs
        let inputs = etapa.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            if (input.name) {
                input.name = input.name.replace(/\[\d+\]/, '[' + index + ']');
            }
            if (input.id) {
                input.id = input.id.replace(/_\d+_/, '_' + index + '_');
            }
        });
        
        // Atualizar o atributo name dos textareas
        let textareas = etapa.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            if (textarea.name) {
                textarea.name = textarea.name.replace(/\[\d+\]/, '[' + index + ']');
            }
            if (textarea.id) {
                textarea.id = textarea.id.replace(/_\d+_/, '_' + index + '_');
            }
        });
        
        // Atualizar o onclick do botão remover
        let btnRemover = etapa.querySelector('.btn-danger');
        if (btnRemover) {
            btnRemover.setAttribute('onclick', "removerEtapa('etapa-" + index + "')");
        }
    });
}

// Aguarda o DOM estar completamente carregado
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM carregado!");

    // Verifica se o botão existe
    let botao = document.getElementById('btnAddEtapa');
    if (botao) {
        console.log("Botão encontrado!");
        
        // Remove qualquer evento anterior (usando cloneNode)
        let novoBotao = botao.cloneNode(true);
        botao.parentNode.replaceChild(novoBotao, botao);
        
        // Adiciona o evento de clique apenas uma vez
        novoBotao.addEventListener('click', function(e) {
            e.preventDefault(); // Previne comportamento padrão do botão
            console.log("Botão clicado!");
            adicionarNovaEtapa();
        });
    } else {
        console.error("Botão não encontrado!");
    }
});

// Prevenir que o formulário seja submetido ao pressionar Enter nos campos de etapa
document.addEventListener('DOMContentLoaded', function() {
    let form = document.getElementById('form-checklist');
    if (form) {
        form.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                return false;
            }
        });
    }
});