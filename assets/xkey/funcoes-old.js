$(document).ready(function(){
  $('#cep').inputmask('99999-999');
  $('#cpf').inputmask('999.999.999-99');
  $('#cnpj').inputmask('99.999.999/9999-99');
  $('#cpf_cliente').inputmask('999.999.999-99');
  $('#cnpj_cliente').inputmask('99.999.999/9999-99');
  $('.cpf_mask').inputmask('999.999.999-99');
  $("#telefone").inputmask({
    mask: ["(99) 9999-9999", "(99) 99999-9999"],
    keepStatic: true
  });
  $("#telefone1").inputmask({
    mask: ["(99) 9999-9999", "(99) 99999-9999"],
    keepStatic: true
  });
  $("#telefone2").inputmask({
    mask: ["(99) 9999-9999", "(99) 99999-9999"],
    keepStatic: true
  });
  $("#celular").inputmask({
    mask: ["(99) 9999-9999", "(99) 99999-9999"],
    keepStatic: true
  });
  $('#data_nascimento').inputmask('99/99/9999');

  $(".clickable-row").click(function() {
      window.location = $(this).data("href");
  });

  $('.rating').on('rating.change', function(event, value, caption) {
    var pergunta = $(this).attr("id").replace("star-", "");
    $.ajax({
      url: '../avaliar_chamado',
      type: 'POST',
      data: {pergunta_id:pergunta, resposta:value, chamado_id:$("#id_chamado").prop("defaultValue")},
      success: function(data){
        if(data == true) {
          window.location = window.location.href;
        }
        //var result = $.parseJSON(data);
      }
    });
  });

  $('.rating').on('rating.clear', function(event) {
    var pergunta = $(this).attr("id").replace("star-", "");
    $.ajax({
      url: '../desavaliar_chamado',
      type: 'POST',
      data: {pergunta_id:pergunta, chamado_id:$("#id_chamado").prop("defaultValue")},
      success: function(data){
        //var result = $.parseJSON(data);
      }
    });
  });

  $('.singleInputDynamic').fastselect();

  if($('.treeview').length) {
    $('.treeview').treeview({data: getTree(), 'multiSelect': true, 
      onNodeSelected: function(event, data) {
        $(".categorias-hidden").append('<input type="hidden" id="categorias_hidden-'+data.dataId+'" name="categorias_hidden[]" value="'+data.dataId+'">');
      },
      onNodeUnselected: function(event, data) {
        $("#categorias_hidden-"+data.dataId).remove();
      }
    });
  }

  if($('#file-3').length) {
    var initialPreview = [];
    var initialPreviewConfig = [];
    var last_id = "";
    var array_config = {};
    $('input[name^="fotos_hidden"]').each(function(index, element) {
      var element_id = $(this).attr('id');
      var element_id_number = $(this).attr('id').split("-");

      if(last_id != element_id_number[1]) {
        array_config = {};
      }
      if(element_id.includes("caminho")) {
        initialPreview.push($(this).val());
        array_config["downloadUrl"] = $(this).val();
      }
      if(element_id.includes("descricao")) {
        array_config["caption"] = $(this).val();
        array_config["filename"] = $(this).val();
      }
      if(element_id.includes("tamanho")) {
        array_config["size"] = $(this).val();
      }
      if(element_id.includes("seq")) {
        array_config["key"] = $(this).val();
      }
      if(last_id != element_id_number[1]) {
        initialPreviewConfig.push(array_config);
      }
      
      last_id = element_id_number[1];
    });

    $("#file-3").fileinput({
        language: "pt-BR",
        showUpload: false,
        showCaption: false,
        browseClass: "btn btn-primary btn-lg",
        previewFileIcon: "<i class='glyphicon glyphicon-king'></i>",
        allowedFileExtensions: ["png", "jpg"],
        overwriteInitial: false,
        initialPreviewAsData: true,
        maxFileSize: 2000,
        deleteUrl: $("#base_url").val()+"/cad_prod/remove_file",
        initialPreview: initialPreview,
        initialPreviewConfig: initialPreviewConfig
    });

    
    $(".kv-file-remove").click(function() {
      $('#fotos_hidden-'+$(this).attr('data-key')+'-caminho').remove();
      $('#fotos_hidden-'+$(this).attr('data-key')+'-descricao').remove();
      $('#fotos_hidden-'+$(this).attr('data-key')+'-seq').remove();
      $('#fotos_hidden-'+$(this).attr('data-key')+'-tamanho').remove();
    });
  }
});

function getTree() {
    var stuff = $(".json_treeview").html();
    return JSON.parse(stuff);
}

function loadEndereco(cep, field) {
  $.ajax({
    url: $("#base_url").val()+'/unidades/buscar_endereco',
    type: 'POST',
    data: {cep:cep},
    success: function(data){
      var result = $.parseJSON(data);
      $("#logradouro"+field).val(result[0].logradouro_nome);
      $("#bairro"+field).val(result[0].bairro_nome);
      $("#cidade"+field).val(result[0].cidade_nome);
      $("#uf"+field).val(result[0].uf);
    }
  });
}

function loadUnidadesLivro(livro, prefix_url) {
  $.ajax({
    url: prefix_url+'app_audios/get_unidades_livro',
    type: 'POST',
    data: {livro:livro},
    success: function(data){
      $("#unidade_livro").html(data);
    }
  });
}

$("#tipo_j").click(function() {
  $("#div-tipo-f").hide();
  $("#div-tipo-j").show();
});

$("#tipo_f").click(function() {
  $("#div-tipo-j").hide();
  $("#div-tipo-f").show();
});

$('#mostrar_resolvidos').change(function() {
  $("#form-chamados").submit();
});

$("#tree").treejs({
    sourceType  : 'json',
    dataSource  : '',
    initialState: 'close'
});

$(".btn_ead_user_update").click(function() {
  var url_submit = $('#base_url').val()+'ead_users/update/'+$(this).attr('id');
  $('#form-ead_user').attr('action', url_submit);

  var user_id = $(this).attr('id');
  var data_expiracao = $("#data_expiracao_"+$(this).attr('id')).val();
  var data_proximo_vpa = $("#data_proximo_vpa_"+$(this).attr('id')).val();
  var level = $("#level_"+$(this).attr('id')).val();

  $.ajax({
    url: $('#base_url').val()+'ead_users/buscar_dados_user_voxy',
    type: 'POST',
    data: {user_id:$(this).attr('id')},
    success: function(data){
      var result = $.parseJSON(data);
  
      $("#user_id").val(user_id);
      $("#data_expiracao").val(data_expiracao);
      $("#data_proximo_vpa").val(data_proximo_vpa);
      $("#level").val(level);
      $("#creditos").val(result.tutoring_credits);
      if(result.nova_metodologia == true)
        $('.div-nova_metodologia').hide();

      $('#eadUserModal').modal('show');
    }
  });
});

$(".btn_ead_user_create").click(function() {
  var url_submit = $('#base_url').val()+'ead_users/create/'+$(this).attr('id');
  $('#form-ead_user').attr('action', url_submit);
  $("#user_id").val($(this).attr('id'));
  $("#data_expiracao").val('');
  $("#data_proximo_vpa").val('');
  $("#level").val('');
  $("#creditos").val('');
  $('#eadUserModal').modal('show');
});


$(".btn_suspender_assinatura").click(function() {
  var url_submit = $('#base_url').val()+'iugu/suspender_assinatura/'+$(this).attr('id');  
  $('#form-assinatura_suspender').attr('action', url_submit);
  $('#iugu_id').val($("#iugu_id_"+$(this).attr('id')).val());
  $("#span_suspender_assinatura").html($("#iugu_id_"+$(this).attr('id')).val());
  $('#suspenderAssinaturaModal').modal('show');
});

$(".btn_2a_via").click(function() {
  var url_submit = $('#base_url').val()+'iugu/gerar_segunda_via_entrada/'+$(this).attr('id');
  $('#form-contrato_2via').attr('action', url_submit);
  $("#contrato_id_2via").val($(this).attr('id'));
  $("#span_contrato_2via").html($(this).attr('id'));
  $('#2aViaIuguContratoModal').modal('show');
});

$(".btn_hist_parcela").click(function() {
  $("#span_parcela_hist").html($(this).attr('id'));
  $('#historicoParcelaIuguModal').modal('show');

  $.ajax({
    url: $('#base_url').val()+'iugu/historico_parcela',
    type: 'POST',
    data: {id:$(this).attr('id')},
    beforeSend: function( xhr ) {
      $(".overlay").show();
    },
    success: function(data){
      var result = $.parseJSON(data);
      $(".overlay").hide();
      $(".bodyHistoricoParcela").html(result.data);
    }
  });
});

$(".btn_2a_via_parcela").click(function() {
  var url_submit = $('#base_url').val()+'iugu/gerar_segunda_via_parcela/'+$(this).attr('id');
  $('#form-parcela_2via').attr('action', url_submit);
  $("#parcela_id_2via").val($(this).attr('id'));
  $("#span_parcela_2via").html($(this).attr('id'));
  $('#2aViaIuguParcelaModal').modal('show');
});

$(".btn_ead_tutor_seg").click(function() {
  var id = $(this).val();
  var url_submit = $('#base_url').val()+'ead_seguimentos/selecionar_tutor/'+id;
  $('#form-ead_tutor').attr('action', url_submit);
  $("#seg").val(id);
  $("#tutor").val($('#tutor_'+id).val());

  $('#eadTutorModal').modal('show');
});

$('#datetimepicker').datetimepicker({
    locale:'pt-br',
    format: 'DD/MM/YYYY',
    showClose: true,
    icons: {
        close: 'closeText'
    }
});
$('#datetimepicker2').datetimepicker({
    locale:'pt-br',
    format: 'DD/MM/YYYY',
    showClose: true,
    icons: {
        close: 'closeText'
    }
});

$('#datetimehourpicker').datetimepicker({
    locale:'pt-br',
    format: 'DD/MM/YYYY HH:mm',
    showClose: true,
    icons: {
        close: 'closeText'
    }
});

$('#datetimehouronlypicker1').datetimepicker({
    locale:'pt-br',
    format: 'HH:mm',
    showClose: true,
    icons: {
        close: 'closeText'
    }
});
$('#datetimehouronlypicker2').datetimepicker({
    locale:'pt-br',
    format: 'HH:mm',
    showClose: true,
    icons: {
        close: 'closeText'
    }
});
$('#datetimehouronlypicker3').datetimepicker({
    locale:'pt-br',
    format: 'HH:mm',
    showClose: true,
    icons: {
        close: 'closeText'
    }
});


$(".btn_ead_contrato_seguimento").click(function() {
  $("#contrato_id_seguimento").val($(this).attr('id'));
  var array_seguimentos = JSON.parse( $("#seguimentos_"+$(this).attr('id')).val() );

  var seguimentos_lista = $.parseJSON($("#seguimentos_lista").val());
  var html = '<select class="multipleSelect" multiple name="seguimentos[]">';
  $.each(seguimentos_lista, function(key, item) {
    html += "<option value='"+item+"'>"+item+"</option>";
  });
  html += '</select>';
  $(".selectSeguimentos").html(html);

  $(".multipleSelect").val(array_seguimentos);
  $('.multipleSelect').fastselect();
  $('#eadContratoSeguimentoModal').modal('show');
});

function remove_user_seguimento(user_id) {
  $("#ul-user-"+user_id).fadeOut("normal", function() {
      $(this).remove();
      $("#hidden-user-"+user_id).remove();
  });
  //$("#hidden-user-"+user_id).remove();
  $("#form-users_seguimentos").append('<input type="hidden" name="users_remove[]" value="'+user_id+'">');
}

function remove_seguimento_confirm(seguimento) {
  var url = $('#base_url').val()+'ead_seguimentos/delete/'+seguimento
  $('#confirm').modal({
      backdrop: 'static',
      keyboard: false
    })
    .one('click', '#delete', function(e) {
      window.location=url;
    });
}

$(".btn_add_user").click(function() {
  var user_id = $(".singleInputDynamic").val();
  if(user_id == "") {
    alert("Selecione um usuário.");
    return false;
  } else {
    if($("#ul-user-"+user_id).length) {
      $("#ul-user-"+user_id).html("<li class='list-group-item '><label>#</label>"+user_id+" &nbsp; "+$(".singleInputDynamic option:selected").text()+" <button type='button' class='btn btn-danger btn-lista' style='float:right;' onclick='remove_user_seguimento(\""+user_id+"\")'><i class=\"fa fa-times\" aria-hidden=\"true\"></i></button></li> <li class='list-group-item'><label>Data Expiração:</label> "+$("#data_expiracao_"+user_id).val()+"</li>");
    } else {
      $("#form-users_seguimentos").append('<input type="hidden" name="users[]" value="'+user_id+'">');
      $(".div_form_users_seguimento").append("<ul class='list-group' id='ul-user-"+user_id+"'> <li class='list-group-item '><label>#</label>"+user_id+" &nbsp; "+$(".singleInputDynamic option:selected").text()+" <button type='button' class='btn btn-danger btn-lista' style='float:right;' onclick='remove_user_seguimento(\""+user_id+"\")'><i class=\"fa fa-times\" aria-hidden=\"true\"></i></button></li> <li class='list-group-item'><label>Data Expiração:</label> "+$("#data_expiracao_"+user_id).val()+"</li> </ul>");
    }
  }
});

$(".btn_view_contato").click(function() {
  $.ajax({
    url: $('#base_url').val()+'contatos/buscar_detalhes_contato',
    type: 'POST',
    data: {id:$(this).attr("data-id")},
    success: function(html){
      $(".modal-body").html(html);
      $('#contatoModal').modal('show');
    }
  });
});


$(".btn_view_sms").click(function() {
  $.ajax({
    url: 'buscar_detalhes_sms',
    type: 'POST',
    data: {id:$(this).val()},
    success: function(data){
      var result = $.parseJSON(data);
      $(".modal-body").html("<p><strong>Unidade: </strong>"+result.unidade+"</p><p><strong>Remetente: </strong>"+result.remetente+"</p><p><strong>Telefone: </strong>"+result.telefone+"</p><p><strong>Data Cadastro: </strong>"+result.data_cadastro+"</p><p><strong>Data Agendamento: </strong>"+result.data_agendamento+"</p><p><strong>Código Status: </strong>"+result.status_code+"</p><p><strong>Descrição Status: </strong>"+result.status_description+"</p><p><strong>Mensagem: </strong>"+result.mensagem+"</p>");
      $('#smsModal').modal('show');
    }
  });
});

function getMetodos() {
  var base_url = $('#base_url').val();
  $.ajax({
    url: base_url+'menus/buscar_metodos',
    type: 'POST',
    data: {controlador:$("#controlador option:selected").text()},
    success: function(data){
      var result = $.parseJSON(data);
      var html = "";
      $.each(result, function(key, item) {
        html += "<option value='"+item+"'>"+item+"</option>";
      });
      $("#metodo").html(html);
    }
  });
}

$(".btn_form_evento_orc").click(function() {
  var url_submit = $('#base_url').val()+'midias_eventos_orcamentos/'+$(this).val();
  $('#form-midias_eventos_orcamentos').attr('action', url_submit);
  $('#form-midias_eventos_orcamentos').submit();
});

function add_resposta(tipo, chamado_id) {
  if($('.dialogo-anexo').html() == "") {
    if(tipo == 1) {
      var html_interno = '';
      if($('#acesso_interno').val() == "1")
        html_interno += "<div class='form-group'><label for='interno'><input type='checkbox' id='interno' name='interno' value='1'> Interno</label></div>";
      var html = "<form id='resposta-chamado-form' action='"+$('#base_url').val()+"chamados/dialogo/"+chamado_id+"' method='post'><div class='form-group'><br><label for='dialogo'>Diálogo</label> <textarea id='dialogo' name='dialogo' class='form-control' rows='3' placeholder='Digite aqui...'></textarea></div>"+html_interno+"<div class='form-group'><button type='submit' class='btn btn-primary btn-flat'>Salvar</button></div></form>"; 
      $('.dialogo-anexo').hide().html(html).fadeIn('slow');
    }
    if(tipo == 2) {
      var html = "<form enctype='multipart/form-data' id='resposta-chamado-form' action='"+$('#base_url').val()+"chamados/anexo/"+chamado_id+"' method='post'><div class='form-group'><br><label for='anexo_desc'>Descrição do Anexo</label><textarea id='anexo_desc' name='anexo_desc' class='form-control' rows='3' placeholder='Digite aqui...'></textarea></div><div class='form-group'><label class='btn btn-primary' for='anexo'> <input id='anexo' type='file' name='anexo[]' multiple style='display:none' onchange='informarArquivos(this.files)'> Selecionar Anexo... </label> <span class='label label-info' id='upload-file-info'></span></div><div class='form-group'><button type='submit' class='btn btn-primary btn-flat'>Salvar</button></div></form>"; 
      $('.dialogo-anexo').hide().html(html).fadeIn('slow');
    }
  } else {
    $('.dialogo-anexo').html('');
  }
}
function informarArquivos(files) {
  var html = "";
  $.each(files, function(i, obj){
    if(html != "")
      html += ", ";
    html += obj.name;
  });
  $("#upload-file-info").html(html)
}

//Carregar produtos habilitados para empresa
$('#unidade').change(function(e){
    var id_unidade = $('#unidade').val();
    var base_url = $('#base_url').val();
    carregar_select_default(id_unidade, 'produto', base_url+'chamados/get_produtos_habilitados', 'Esta unidade não possui produtos habilitados!');
    carregar_select_default_chamados(id_unidade, 'interessados', base_url+'chamados/get_users_unidade', 'Esta unidade não possui usuários vinculados!');
    carregar_select_default(id_unidade, 'plano_acao', base_url+'chamados/get_plano_acao', 'Esta unidade não possui plano de ação habilitados!');
});

//Carregar Módulos de acordo com o produto
$('#produto').change(function(e){
    var id_produto = $('#produto').val();
    var id_unidade = $('#unidade').val();
    var base_url = $('#base_url').val();
    carregar_select_modulos(id_unidade, id_produto, 'modulo', base_url+'chamados/get_modulos_habilitados', 'Este produto não possui módulos habilitados!');
});

//Carregar Categorias de acordo com o módulo
$('#modulo').change(function(e){
    var id_modulo = $('#modulo').val();
    var base_url = $('#base_url').val();
    carregar_select_default(id_modulo, 'categoria', base_url+'chamados/get_modulo_categorias', 'Este módulo não possui categorias!');
});

//Carregar Módulos de Categorias de acordo com o módulo
$('#produto_modulo_categoria').change(function(e){
    var id_produto = $('#produto_modulo_categoria').val();
    var base_url = $('#base_url').val();
    carregar_select_default(id_produto, 'modulo', base_url+'produtos_modulos_categorias/get_modulos_habilitados', 'Este produto não possui módulos!');
});

//Carregar Módulos de Categorias Edit de acordo com o módulo
$('#produto_modulo_categoria_edit').change(function(e){
    var id_produto = $('#produto_modulo_categoria_edit').val();
    var base_url = $('#base_url').val();
    carregar_select_default(id_produto, 'modulo_edit', base_url+'produtos_modulos_categorias/get_modulos_habilitados', 'Este produto não possui módulos!');
});

//Carregar Catalogo servico edit de acordo com o módulo
$('#modulo_edit').change(function(e){
    var id_modulo = $('#modulo_edit').val();
    var base_url = $('#base_url').val();
    carregar_select_default(id_modulo, 'categoria_edit', base_url+'chamados/get_modulo_categorias', 'Este módulo não possui categorias!');
});

$('#categoria').change(function(e){
    var id_categoria = $('#categoria').val();
    var base_url = $('#base_url').val();

    $('#tipo_servico').html('<option value="">Carregando...</option>');

    var option = '';
    $.post(base_url+'chamados/get_servicos',{
        id: id_categoria
    }, function(data){
      
        if(!jQuery.isEmptyObject(data.select)){
            option += '<option data-hidden="true" value="">Selecione</option>';
            $.each(data.select, function(i, obj){
                option += '<option value="'+obj.id+'">'+obj.nome+'</option>';
            });
            $('#tipo_servico').html(option).show();
        }else{
            option += '<option value="">Esta categoria não possui serviço!</option>';
            $('#tipo_servico').html(option).show();
        }

        $('#desc').html(data.layout);

    }, 'json');
});

$('#categoria_edit').change(function(e){

    var id_categoria = $('#categoria_edit').val();
    var base_url = $('#base_url').val();

    $('#tipo_servico').html('<option value="">Carregando...</option>');

    var option = '';
    $.post(base_url+'chamados/get_servicos',{
        id: id_categoria
    }, function(data){
      
        if(!jQuery.isEmptyObject(data.select)){
            option += '<option data-hidden="true" value="">Selecione</option>';
            $.each(data.select, function(i, obj){
                option += '<option value="'+obj.id+'">'+obj.nome+'</option>';
            });
            $('#tipo_servico').html(option).show();
        }else{
            option += '<option value="">Esta categoria não possui serviço!</option>';
            $('#tipo_servico').html(option).show();
        }
        
        $('#desc').html(data.layout);

    }, 'json');
});

function carregar_select_default_chamados(id, select, controller, msg){
    $('#'+select).html('<option value="">Carregando...</option>');
    $('#dono').html('<option value="">Carregando...</option>');

    var option_select = '<option value="">Selecione</option>';
    var option_multi = '';
    $.post(controller,{
        id: id
    }, function(data){
        if(!jQuery.isEmptyObject(data)){
            $.each(data, function(i, obj){
                option_select += '<option value="'+obj.id+'">'+obj.nome+'</option>';
                option_multi += '<option value="'+obj.id+'">'+obj.nome+' ('+obj.email+')</option>';
            });
            $('#interessados').html(option_multi).show();
            $('#dono').html(option_select).show();

            $('#interessados').fastselect();
            $('#dono').fastselect();
        }else{
            option_select = '<option value="">'+msg+'</option>';
            $('#interessados').html(option_select).show();
            $('#dono').html(option_select).show();

            $('#interessados').fastselect();
            $('#dono').fastselect();
        }
    }, 'json');
}

function carregar_select_default(id, select, controller, msg){
    $('#'+select).html('<option value="">Carregando...</option>');

    var option = '';
    $.post(controller,{
        id: id
    }, function(data){
        if(!jQuery.isEmptyObject(data)){
            option += '<option data-hidden="true" value="">Selecione</option>';
            $.each(data, function(i, obj){
                option += '<option value="'+obj.id+'">'+obj.nome+'</option>';
            });
            $('#'+select).html(option).show();
        }else{
            option += '<option value="">'+msg+'</option>';
            $('#'+select).html(option).show();
        }
    }, 'json');
}

function carregar_select_modulos(id_unidade, id_produto, select, controller, msg){
    $('#'+select).html('<option value="">Carregando...</option>');
    
    var option = '';
    $.post(controller,{
        id_unidade: id_unidade,
        id_produto: id_produto
    }, function(data){
        if(!jQuery.isEmptyObject(data)){
            option += '<option data-hidden="true" value="">Selecione</option>';
            $.each(data, function(i, obj){
                option += '<option value="'+obj.id+'">'+obj.nome+'</option>';
            });
            $('#'+select).html(option).show();
        }else{
            option += '<option value="">'+msg+'</option>';
            $('#'+select).html(option).show();
        }
    }, 'json');
}


$(".reagendarChamado").click(function(event) {
  $("#chamado_id").val($(this).attr('id'));
  //$("#data_expiracao").val($("#data_expiracao_"+$(this).val()).val());
  $(".modal-title").html("Previsão Resolução Chamado #"+$(this).attr('id'));

  $('#reagendarModal').modal('show');
  event.stopImmediatePropagation();
});

$(".escalarChamado").click(function(event) {
  //$("#chamado_id").val($(this).attr('id'));
  $(".modal-title").html("Escalar Chamado #"+$(this).attr('id'));
  $('#escalarModal').modal('show');
});

$(".statusChamado").click(function(event) {
  //$("#chamado_id").val($(this).attr('id'));
  $(".modal-title").html("Transferir Chamado #"+$(this).attr('id'));
  $('#statusModal').modal('show');
});

function atualizar_prioridade(chamado_id, prioridade){
    $.post('../alterar_prioridade',{
        chamado_id: chamado_id,
        prioridade: prioridade
    }, function(data){

    }, 'json');
}

function buscar_seguimentos(tutor) {
  option = '<option data-hidden="true" value="">Selecione</option>';
  $.post('buscar_seguimentos',{
      tutor: tutor
  }, function(data){
    if(!jQuery.isEmptyObject(data)){
        $.each(data, function(i, obj){
            option += '<option value="'+obj.id+'">'+obj.segment_label+'</option>';
        });
        $('#filtro_seguimento').html(option).show();
    }else{
        $('#filtro_seguimento').html(option).show();
    }
  }, 'json');
}

$('#mostrar_resolvidos_toggle').change(function() {
  if($(this).prop('checked') == true)
    var res = 1;
  else
    var res = 0;
  $("#mostrar_resolvidos").val(res);
  //$("#form-chamados")[0].submit();
});

$('#mostrar_responsaveis_toggle').change(function() {
  if($(this).prop('checked') == true)
    var res = 1;
  else
    var res = 0;
  $("#mostrar_responsaveis").val(res);
  //$("#form-chamados")[0].submit();
});

$('#mostrar_corrigidos_toggle').change(function() {
  if($(this).prop('checked') == true)
    var res = 1;
  else
    var res = 0;
  $("#mostrar_corrigidos").val(res);
  //$("#form-respostas")[0].submit();
});

$('#filtro_status').change(function() {
  if($(this).val() == 5)
    $('.avaliacao_de_ate').fadeIn();
  else
    $('.avaliacao_de_ate').fadeOut();
});

$('.btn-tutoria-msg').click( function() {
  var tableMsg = $('#datatable_select').DataTable();
    var data = tableMsg.rows({selected:  true}).data();
    var data_list = [];
    $.each( data, function( key, value ) {
      data_list.push(value['DT_RowId']);
    });

    seen = []; 
    var replacer = function(key, value) {
      if (value != null && typeof value == "object") {
        if (seen.indexOf(value) >= 0) {
          return;
        }
        seen.push(value);
      }
      return value;
    };
    $('#users_to_hidden').val(JSON.stringify(data_list, replacer));
  });

$("#form-create_app_turmas .adiciona-dia").click(function(){
    if($('#unidade_turmas').val() != "") {
        $.post('get_professores',{
            unidade: $('#unidade_turmas').val()
        }, function(data){
            var retorno = data;
            var uniqid = '_'+Math.random().toString(36).substr(2, 9);
            var nova_linha = $('<div class="row dia_semana" style="margin-top:5px;">'
                                +'<input type="hidden" id="turma_horario_id'+ uniqid +'" name="turma_horario_id[]">'
                                +'<div class="col-md-3"><select class="form-control" id="turma_horario_professor'+ uniqid +'" name="turma_horario_professor[]">'+retorno+'</select></div>'
                                +'<div class="col-md-2"><select class="form-control" id="turma_horario_dia'+ uniqid +'" name="turma_horario_dia[]"><option value="">Selecione um Dia</option><option value="DOMINGO">DOMINGO</option><option value="SEGUNDA-FEIRA">SEGUNDA-FEIRA</option><option value="TERCA-FEIRA">TERCA-FEIRA</option><option value="QUARTA-FEIRA">QUARTA-FEIRA</option><option value="QUINTA-FEIRA">QUINTA-FEIRA</option><option value="SEXTA-FEIRA">SEXTA-FEIRA</option><option value="SABADO">SABADO</option></select></div>'
                                +'<div class="col-md-2"><input type="time" class="form-control" id="turma_horario_inicio'+ uniqid +'" name="turma_horario_inicio[]"></div>'
                                +'<div class="col-md-2"><input type="time" class="form-control" id="turma_horario_fim'+ uniqid +'" name="turma_horario_fim[]"></div>'
                                +'<div class="col-md-2">'
                                    +'<a href="#" class="btn btn-danger btn-xs remove-dia">Remover</a>'
                                +'</div>'
                            +'</div>');
            $(".dia_semana:last").after(nova_linha);
            nova_linha.fadeIn("slow");
        }, "html");
        return false;
    } else {
        alert("Selecione primeiramente uma unidade.");
        return false;
    }
});
//Remover linha extra
$('#form-create_app_turmas').on('click', '.remove-dia', function(){
    $(this).parent().parent().fadeOut("slow", function() {
        $(this).remove();
    });
    return false;
});

$("#form-edit_app_turmas .adiciona-dia").click(function(){
    if($('#unidade_turmas').val() != "") {
        $.post('../get_professores',{
            unidade: $('#unidade_turmas').val()
        }, function(data){
            var retorno = data;
            var uniqid = '_'+Math.random().toString(36).substr(2, 9);
            var nova_linha = $('<div class="row dia_semana" style="margin-top:5px;">'
                                +'<input type="hidden" id="turma_horario_id'+ uniqid +'" name="turma_horario_id[]">'
                                +'<div class="col-md-3"><select class="form-control" id="turma_horario_professor'+ uniqid +'" name="turma_horario_professor[]">'+retorno+'</select></div>'
                                +'<div class="col-md-2"><select class="form-control" id="turma_horario_dia'+ uniqid +'" name="turma_horario_dia[]"><option value="">Selecione um Dia</option><option value="DOMINGO">DOMINGO</option><option value="SEGUNDA-FEIRA">SEGUNDA-FEIRA</option><option value="TERCA-FEIRA">TERCA-FEIRA</option><option value="QUARTA-FEIRA">QUARTA-FEIRA</option><option value="QUINTA-FEIRA">QUINTA-FEIRA</option><option value="SEXTA-FEIRA">SEXTA-FEIRA</option><option value="SABADO">SABADO</option></select></div>'
                                +'<div class="col-md-2"><input type="time" class="form-control" id="turma_horario_inicio'+ uniqid +'" name="turma_horario_inicio[]"></div>'
                                +'<div class="col-md-2"><input type="time" class="form-control" id="turma_horario_fim'+ uniqid +'" name="turma_horario_fim[]"></div>'
                                +'<div class="col-md-2">'
                                    +'<a href="#" class="btn btn-danger btn-xs remove-dia">Remover</a>'
                                +'</div>'
                            +'</div>');
            $(".dia_semana:last").after(nova_linha);
            nova_linha.fadeIn("slow");
        }, "html");
        return false;
    } else {
        alert("Selecione primeiramente uma unidade.");
        return false;
    }
});
//Remover linha extra
$('#form-edit_app_turmas').on('click', '.remove-dia', function(){
    $(this).parent().parent().fadeOut("slow", function() {
        $(this).remove();
    });
    return false;
});

function remove_user_turma(user_id) {
  $("#ul-user-"+user_id).fadeOut("normal", function() {
      $(this).remove();
      $("#hidden-user-"+user_id).remove();
  });
  //$("#hidden-user-"+user_id).remove();
  $("#form-users_turma").append('<input type="hidden" name="users_remove[]" value="'+user_id+'">');
}

$(".btn_add_user_turma").click(function() {
  var user_id = $(".singleInputDynamic").val();
  if(user_id == "") {
    alert("Selecione um usuário.");
    return false;
  } else {
    if($("#ul-user-"+user_id).length) {
      $("#ul-user-"+user_id).html("<li class='list-group-item '><label>#</label>"+user_id+"<button type='button' class='btn btn-danger btn-lista' style='float:right;' onclick='remove_user_turma(\""+user_id+"\")'><i class=\"fa fa-times\" aria-hidden=\"true\"></i></button></li> <li class='list-group-item'>"+$(".singleInputDynamic option:selected").text()+"</li>");
    } else {
      $("#form-users_turma").append('<input type="hidden" name="users[]" value="'+user_id+'">');
      $(".div_form_users_turma").append("<ul class='list-group' id='ul-user-"+user_id+"'> <li class='list-group-item '><label>#</label>"+user_id+"<button type='button' class='btn btn-danger btn-lista' style='float:right;' onclick='remove_user_turma(\""+user_id+"\")'><i class=\"fa fa-times\" aria-hidden=\"true\"></i></button></li> <li class='list-group-item'>"+$(".singleInputDynamic option:selected").text()+"</li> </ul>");
    }
  }
});

function getWeekNumber(d) {
    d = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
    d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay()||7));
    var yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
    var weekNo = Math.ceil(( ( (d - yearStart) / 86400000) + 1)/7);
    return [d.getUTCFullYear(), weekNo];
}

function remove_dispositivo(disp_id) {
  /*$('#confirm-delete').modal({
    backdrop: 'static',
    keyboard: false
  })
  .one('click', '.btn-ok', function(e) {
    $('#dispositivo_id').val($(item).attr('id'));
    $('#form_dispositivos').submit();
  });*/
  $("#ul-dispositivo-"+disp_id).fadeOut("normal", function() {
      $(this).remove();
      $("#hidden-dispositivo-"+disp_id).remove();
  });
  $("#form-alunos_dispositivos").append('<input type="hidden" name="dispositivos_remove[]" value="'+disp_id+'">');
};

function carregarDadosQrcode(obj){
    var id = $(obj).attr('data-id');
    var min_id = $(obj).attr('data-id-min');
    var max_id = $(obj).attr('data-id-max');
    $('#inputQRCodeinfoId').val(id);
    var div_box = document.getElementById('infomacoes-qrcode');
    var html = '<h4><strong>Atenção</strong></h4>'+
                '<p>Número de registros encontrados entre <b>'+min_id+'</b> e <b>'+max_id+'</b>, utilize estes valores para selecionar os qrcodes!</p>';
    div_box.innerHTML = html;
}

$("#inputQRCodeIdTodos").click(function() {
  if($(this).prop('checked')){
      $(".input-number").prop("disabled", true);
      $(".input-number").val('');
  }else{
      $(".input-number").prop("disabled", false);
  }
});

function gerarToken() {
  var letras = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz';
  var tamanho = 100;
  var aleatorio = '';
  for (var i = 0; i < tamanho; i++) {
      var rnum = Math.floor(Math.random() * letras.length);
      aleatorio += letras.substring(rnum, rnum + 1);
  }
  $("#token").val(aleatorio);
}

function addCommas(nStr)
{
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}

function selecao_curso(curso_id) {
  $.ajax({
    url: 'selecao_curso',
    type: 'POST',
    data: {curso:curso_id},
    success: function(data){
      var result = $.parseJSON(data);
      var html_condicoes = "";
      var html_turmas = "";
      var html_disciplinas = "";
      var html_produtos = "";
      var html_servicos = "";
      var html_parcelas = "";
      if(result.condicoes.length > 0) {
        html_condicoes += "<table class='table'>";
        html_condicoes += "<theader>";
        html_condicoes += "<tr>";
        html_condicoes += "<th>Descrição</th>";
        html_condicoes += "<th>Forma Pagto</th>";
        html_condicoes += "<th>Qtde Parcelas</th>";
        html_condicoes += "<th>Tipo Recebimento</th>";
        html_condicoes += "</tr>";
        html_condicoes += "</theader>";
        html_condicoes += "<tbody>";
        $.each(result.condicoes, function(key, item) {
          html_condicoes += "<tr>";
          html_condicoes += "<td>"+item.DESCRICAO+"</td>";
          html_condicoes += "<td>";
          if (item.FormaReceb == null){
            html_condicoes += "-"
          } else {
            html_condicoes += item.FormaReceb;
          }
          html_condicoes += "</td>";
          html_condicoes += "<td>";
          if (item.NUM_PARCELAS == null){
            html_condicoes += "-"
          } else {
            html_condicoes += item.NUM_PARCELAS;
          }
          html_condicoes += "</td>";
          html_condicoes += "<td>";
          if (item.rece_desc == null){
            html_condicoes += "-"
          } else {
            html_condicoes += item.rece_desc;
          }
          html_condicoes += "</td>";
          html_condicoes += "</tr>";
        });
        html_condicoes += "</tbody>";
        html_condicoes += "</table>";
      } else {
        html_condicoes += "<h6>Não há dados para mostrar</h6>";
      }
      
      html_turmas += "<div class='row'><div class='col-md-6'><fieldset class='scheduler-border'><legend class='scheduler-border'>Turmas:</legend>";
      if(result.turmas.length > 0) {
        html_turmas += "<table class='table'>";
        html_turmas += "<theader>";
        html_turmas += "<tr>";
        html_turmas += "<th></th>";
        html_turmas += "<th>Descrição</th>";
        html_turmas += "<th>Cap. Alunos</th>";
        html_turmas += "<th>Início</th>";
        html_turmas += "</tr>";
        html_turmas += "</theader>";
        html_turmas += "<tbody>";
        $.each(result.turmas, function(key, item) {
          html_turmas += "<tr>";
          html_turmas += "<td><input type='checkbox' name='turmas[]' value='"+item.ID+"'></td>";
          html_turmas += "<td>"+item.DESCRICAO+"</td>";
          html_turmas += "<td>"+item.CAPACIDADE_ALUNO+"</td>";
          html_turmas += "<td>"+item.DATA_INICIO+"</td>";
          html_turmas += "</tr>";
        });
        html_turmas += "</tbody>";
        html_turmas += "</table>";
      } else {
        html_turmas += "<h6>Não há dados para mostrar</h6>";
      }
      html_turmas += "</fieldset></div>";
      
      html_disciplinas += "<div class='col-md-6'><fieldset class='scheduler-border'><legend class='scheduler-border'>Disciplinas:</legend>";
      if(result.disciplinas.length > 0) {
        html_disciplinas += "<table class='table'>";
        html_disciplinas += "<theader>";
        html_disciplinas += "<tr>";
        html_disciplinas += "<th></th>";
        html_disciplinas += "<th>Descrição</th>";
        html_disciplinas += "<th>Frequência</th>";
        html_disciplinas += "</tr>";
        html_disciplinas += "</theader>";
        html_disciplinas += "<tbody>";
        $.each(result.disciplinas, function(key, item) {
          html_disciplinas += "<tr>";
          html_disciplinas += "<td><input type='checkbox' name='disciplinas[]' value='"+item.ID_DISCIPLINA+"'></td>";
          html_disciplinas += "<td>"+item.DESCRICAO+"</td>";
          html_disciplinas += "<td>-</td>";
          html_disciplinas += "</tr>";
        });
        html_disciplinas += "</tbody>";
        html_disciplinas += "</table>";
        html_disciplinas += "</fieldset>";
      } else {
        html_disciplinas += "<h6>Não há dados para mostrar</h6>";
      }
      html_disciplinas += "</fieldset></div></div>";

      if(result.produtos.length > 0) {
        html_produtos += "<table class='table'>";
        html_produtos += "<theader>";
        html_produtos += "<tr>";
        html_produtos += "<th>Descrição</th>";
        html_produtos += "<th>Quantidade</th>";
        html_produtos += "<th>Preço Unitário</th>";
        html_produtos += "</tr>";
        html_produtos += "</theader>";
        html_produtos += "<tbody>";
        $.each(result.produtos, function(key, item) {
          html_produtos += "<tr>";
          html_produtos += "<td>"+item.DESCRICAO+"</td>";
          html_produtos += "<td>1</td>";
          html_produtos += "<td>"+jQuery.number(item.VALOR, 2)+"</td>";
          html_produtos += "</tr>";
        });
        html_produtos += "</tbody>";
        html_produtos += "</table>";
      } else {
        html_produtos += "<h6>Não há dados para mostrar</h6>";
      }

      if(result.servicos.length > 0) {
        html_servicos += "<table class='table'>";
        html_servicos += "<theader>";
        html_servicos += "<tr>";
        html_servicos += "<th>Descrição</th>";
        html_servicos += "<th>Quantidade</th>";
        html_servicos += "<th>Preço Unitário</th>";
        html_servicos += "</tr>";
        html_servicos += "</theader>";
        html_servicos += "<tbody>";
        $.each(result.servicos, function(key, item) {
          html_servicos += "<tr>";
          html_servicos += "<td>"+item.APLICACAO+"</td>";
          html_servicos += "<td>1</td>";
          html_servicos += "<td>"+jQuery.number(item.VALOR, 2)+"</td>";
          html_servicos += "</tr>";
        });
        html_servicos += "</tbody>";
        html_servicos += "</table>";
      } else {
        html_servicos += "<h6>Não há dados para mostrar</h6>";
      }

      if(result.parcelas.length > 0) {
        html_parcelas += "<table class='table'>";
        html_parcelas += "<theader>";
        html_parcelas += "<tr>";
        html_parcelas += "<th>Condição</th>";
        html_parcelas += "<th>Parcela</th>";
        html_parcelas += "<th>Valor</th>";
        html_parcelas += "<th>Vencimento</th>";
        html_parcelas += "<th>Recebimento</th>";
        html_parcelas += "</tr>";
        html_parcelas += "</theader>";
        html_parcelas += "<tbody>";
        $.each(result.parcelas, function(key, item) {
          html_parcelas += "<tr>";
          html_parcelas += "<td>"+item.condicao+"</td>";
          html_parcelas += "<td>"+item.parcela+"</td>";
          html_parcelas += "<td>"+item.valor+"</td>";
          html_parcelas += "<td>"+item.vencimento+"</td>";
          html_parcelas += "<td>"+item.recebimento+"</td>";
          html_parcelas += "</tr>";
        });
        html_parcelas += "</tbody>";
        html_parcelas += "</table>";
      } else {
        html_parcelas += "<h6>Não há dados para mostrar</h6>";
      }

      jQuery("#condicoes").html(html_condicoes);
      jQuery("#turmas_disciplinas").html(html_turmas+html_disciplinas);
      jQuery("#produtos").html(html_produtos);
      jQuery("#servicos").html(html_servicos);
      jQuery("#parcelas").html(html_parcelas);
    }
  });
}

function loadEventos(midia, unidade, prefix_url) {
  $.ajax({
    url: prefix_url+'buscar_eventos',
    type: 'POST',
    data: {midia:midia,unidade:unidade},
    success: function(data){
      var result = $.parseJSON(data);
      var html = "<option value=''>Selecione</option>";
      $.each(result, function(key, item) {
        html += "<option value='"+key+"'>"+item+"</option>";
      });
      $("#evento").html(html);
    }
  });
}

function loadAudioBase64(id, prefix_url) {
  $.ajax({
    url: prefix_url+'app_audios/get_audio_base64',
    type: 'POST',
    data: {id:id},
    success: function(data){
      $(".modal-body").html(data);
      $('#modalAudioBase64').modal('show');
    }
  });
}

function get_eventos(unidade, prefix_url) {
  $.ajax({
    url: prefix_url+'midias_eventos_orcamentos/get_eventos',
    type: 'POST',
    data: {unidade:unidade},
    success: function(data){
      $("#filtro_eventos").html(data);
    }
  });
}

$( "#form-agenda_configurar" ).submit(function( event ) {
  var table = $('#datatable_select').DataTable();
  var data = table.rows({selected:  true}).data();

  $.each( data, function( key, value ) {
    $('<input>').attr({
        type: 'hidden',
        id: 'remover_usu_'+value[1],
        name: 'remover_usu[]',
        value: value[1]
    }).appendTo('#form-agenda_configurar');
  });
});

function deletar_agenda(agenda) {
  var url = $('#base_url').val()+'agenda/excluir_agenda/'+agenda
  $('#confirm').modal({
      backdrop: 'static',
      keyboard: false
    })
    .one('click', '#delete', function(e) {
      window.location=url;
    });
}

$(".btn_alterar_desempenho").click(function() {
  $("#contrato_id_desempenho").val($(this).attr('id'));
  var desempenho_geral = $("#desempenho_geral_"+$(this).attr('id')).val();
  if(desempenho_geral == "higher" || desempenho_geral == "higher_medium" || desempenho_geral == "medium" || desempenho_geral == "emerging") {
    $("#"+desempenho_geral).iCheck('check');
  }
  $("#span_contrato_desempenho").html($(this).attr('id'));
  $('#alterarDesempenhoModal').modal('show');
});

function loadPodcastBase64(id, prefix_url) {
  $.ajax({
    url: prefix_url+'app_podcasts/get_podcast_base64',
    type: 'POST',
    data: {id:id},
    success: function(data){
      $(".modal-body").html(data);
      $('#modalPodcastBase64').modal('show');
    }
  });
};

function remove_vinculo_disciplina(id) {
    $.ajax({
        url: '../remove_vinculo',
        type: 'POST',
        data: {id:id},
        success: function(data){
            var result = $.parseJSON(data);
            if(result.erro) {

            } else {
                $(".ul-vinculo-"+id).remove();
            }
        }
    });
};

function calculaPrazoChamado(prefix_url) {
  $.ajax({
    url: prefix_url+'chamados/calculaPrazo',
    type: 'POST',
    data: {chamado_id: $("#chamado_id").val(), tipo_servico: $("#tipo_servico").val()},
    success: function(data){
      var result = $.parseJSON(data);
      $("#data_reagendar").val(result.calculaPrazo);
      $("#horas_prevista").val(result.horas_prevista);
    }
  });
}

//Carregar Produtos
function carregarProdutos(id_cliente, base_url){
    $('#modalHabilitacaoProdutosCliente').modal('show');

    var form = new FormData();
    form.append('id_cliente', id_cliente);

    $.ajax({
        url: base_url+'habilitacoes/carregar_produtos',
        type: 'POST',
        xhrFields: {
            withCredentials: true
        },
        async: true,
        cache: false,
        contentType: false,
        processData: false,
        data: form,
        success: function (data) {
            var retorno = jQuery.parseJSON(data)['produtos_disponiveis'];
            var retorno_hbltds = jQuery.parseJSON(data)['produtos_habilitados'];
            var div = $('#modalHabilitacaoProdutosCliente');
            var html = '';
            var html_hbltds = '';

            //Lista de Produtos Disponíveis
            if(retorno.length > 0){
                div.find('#content-produtos').html('');
                    //Produtos
                    for(var i = 0; i < retorno.length; i++){
                        var modulos = '';
                        if(retorno[i].modulos){modulos = 1;}else{modulos = 0;}
                            html += '<div class="row row-habilitacao" data-toggle="collapse" data-target="#collapseModulos'+retorno[i].id+'" aria-expanded="false" aria-controls="collapseModulo'+retorno[i].id+'" rel="tooltip" data-placement="top" title="Clique para mais informações" data-form="formHabilitarProduto'+retorno[i].id+'" onclick="insertDateMask(this)">'
                                        +'<div class="col-sm-2">'+retorno[i].id+'</div>'
                                        +'<div class="col-sm-4">'+retorno[i].produto+'</div>'
                                        +'<div class="col-sm-3">'+retorno[i].aplicacao+'</div>'
                                        +'<div class="col-sm-3">'+retorno[i].versao+'</div>';
                            html += '</div>';
                                html += '<div class="collapse collapse-habilitacao" id="collapseModulos'+retorno[i].id+'">';
                                    html += '<form method="post" enctype="multipart/form-data" id="formHabilitarProduto'+retorno[i].id+'" name="formHabilitarProduto'+retorno[i].id+'" class="form" role="" onsubmit="return false;">'
                                        html += '<input type="hidden" id="inputIdCliente" name="inputIdCliente" value="'+btoa(retorno[i].id_cliente)+'">';
                                        html += '<input type="hidden" id="inputIdProduto" name="inputIdProduto" value="'+btoa(retorno[i].id)+'">';
                                        html += '<input type="hidden" id="inputModulos" name="inputModulos" value="'+modulos+'">';
                                        html += '<div class="row">';
                                            //Período
                                            html += '<div class="col-md-6">'
                                                        +'<label for="inputHabilitacaoProdutoPeriodo" class="control-label">Período</label>'
                                                        +'<div class="input-group input-group-md">'
                                                            +'<div class="input-daterange input-group" id="datepicker">'
                                                                +'<input type="text" class="form-control" id="inputHabilitacaoProdutoInicio" name="inputHabilitacaoProdutoInicio"/>'
                                                                +'<span class="input-group-addon">Até</span>'
                                                                +'<input type="text" class="form-control" id="inputHabilitacaoProdutoFim" name="inputHabilitacaoProdutoFim"/>'
                                                            +'</div>'
                                                        +'</div>'
                                                    +'</div>';
                                            //Contrato SAX
                                            html += '<div class="col-md-6">'
                                                        +'<label for="inputContratoSax" class="control-label">Contrato SAX</label>'
                                                        +'<input type="text" class="form-control" id="inputContratoSax" name="inputContratoSax">'
                                                    +'</div>';
                                            //Habilitar
                                            html += '<div class="col-md-12">'
                                                        +'<div class="form-check">'
                                                            +'<input type="checkbox" id="inputProdutoHabilitar" name="inputProdutoHabilitar" value="S" class="form-check-input">'
                                                            +'<label class="form-check-label" for="inputProdutoHabilitar">Habilitar</label>'
                                                        +'</div>'
                                                    +'</div>';
                                            //Habilitar Demo
                                            html += '<div class="col-md-12">'
                                                        +'<div class="form-check">'
                                                            +'<input type="checkbox" id="inputHabilitarDemo" name="inputHabilitarDemo" value="S" data-form="formHabilitarProduto'+retorno[i].id+'" onclick="desabilitarPeriodo(this)" class="form-check-input">'
                                                            +'<label class="form-check-label" for="inputHabilitarDemo">Demonstração(30 Dias)</label>'  
                                                        +'</div>'
                                                    +'</div>';
                                             html += "<div class='col-md-12'><button type='submit' id='habilitar_produto' class='btn btn-success habilitar-produto' style='position: relative;' data-action='insert' onclick='return habilitarProduto(this.form,this,\""+base_url+"\");'><i class='fa fa-floppy-o'></i> Salvar</button></div>";
                                        html += '</div>';
                                        html += '<br>';

                                    if(retorno[i].modulos.length > 0){
                                        //Módulos
                                        html += '<div class="panel panel-default"><div class="panel-heading">'
                                                    +'<div class="row">'
                                                        +'<div class="col-sm-4">Módulo(s)</div>'
                                                    +'</div>'
                                                +'</div>';
                                        html += '<table id="lista-modulos" class="table table-striped table-hover">'
                                                    +'<thead>'
                                                        +'<tr>'
                                                            +'<th class="col-sm-1"><i class="fa fa-database"></i> ID</th>'
                                                            +'<th class="col-sm-4"><i class="glyphicon glyphicon-circle-arrow-down"></i> Módulo</th>'
                                                            +'<th class="col-sm-2"><i class="glyphicon glyphicon-user"></i> Usuários</th>'
                                                            +'<th class="col-sm-2"><i class="glyphicon glyphicon-ok-circle"></i> Liberado</th>'
                                                            +'<th class="col-sm-2"><i class="glyphicon glyphicon-ban-circle"></i> Bloqueado</th>'
                                                            +'<th class="col-sm-2"><i class="glyphicon glyphicon-list-alt"></i> Consulta</th>'
                                                        +'</tr>'
                                                    +'</thead>'
                                                    +'<tbody>';
                                            for(var im = 0; im < retorno[i].modulos.length; im++){
                                                html += '<input type="hidden" id="inputModuloId" name="inputModuloId['+retorno[i].modulos[im].id+']" value="'+retorno[i].modulos[im].id+'">';
                                                html += '<tr>';
                                                    html += '<td class="text-left">'+retorno[i].modulos[im].id+'</td>';
                                                    html += '<td class="text-left">'+retorno[i].modulos[im].nome+'</td>';
                                                    html += '<td class="text-left"><input type="text" id="inputHabilitacaoModuloUsu" name="inputHabilitacaoModuloUsu-'+retorno[i].modulos[im].id+'" value="'+retorno[i].modulos[im].usuarios+'" class="col-sm-5"></td>';
                                                    html += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno[i].modulos[im].id+']" value="L"></td>';
                                                    html += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno[i].modulos[im].id+']" value="B" checked="checked"></td>';
                                                    html += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno[i].modulos[im].id+']" value="C"></td>';
                                                html += '</tr>';
                                            }
                                            html += '</tbody>';
                                        html += '</table>';
                                    }
                                    html += '</form>';
                                html += '</div>';
                        html += '</div>';
                    }
                div.find('#content-produtos').html(html);                
            }else{                
                div.find('#content-produtos').html('');
            }

            //Lista de Produtos Habilitados
            if(retorno_hbltds.length > 0){
                div.find('#content-produtos-habilitados').html('');
                    //Variáveis utilizadas
                    var demo = '';
                    var disabled = '';
                    var modulos = '';
                    var periodo_demo = '';

                    //Produtos
                    for(var i = 0; i < retorno_hbltds.length; i++){

                        //Checa se demonstração
                        if(retorno_hbltds[i].demonstracao == 'S'){
                            demo = 'checked="checked"';
                            periodo_demo = 'Sim';
                            disabled = 'disabled="disabled"';
                        }else{
                            demo = '';
                            periodo_demo = 'Não';
                            disabled = '';
                        }

                        if(retorno_hbltds[i].modulos){modulos = 1;}else{modulos = 0;}
                            html_hbltds += '<div class="row row-habilitacao '+retorno_hbltds[i].vencimento+'" data-toggle="collapse" data-target="#collapseModulosHabilitado'+retorno_hbltds[i].id_habilitacao+'" aria-expanded="false" aria-controls="collapseModulosHabilitado'+retorno_hbltds[i].id_habilitacao+'" rel="tooltip" data-placement="top" title="Clique para mais informações" data-form="formHabilitarProduto'+retorno_hbltds[i].id_habilitacao+'" onclick="insertDateMask(this)">'
                                                +'<div class="col-sm-2">'+retorno_hbltds[i].id+'</div>'
                                                +'<div class="col-sm-4">'+retorno_hbltds[i].produto+'</div>'
                                                +'<div class="col-sm-2">'+retorno_hbltds[i].aplicacao+'</div>'
                                                +'<div class="col-sm-2 text-center">'+periodo_demo+'</div>'
                                                +'<div class="col-sm-2 text-center">'+retorno_hbltds[i].periodo_fim+'</div>'
                            html_hbltds += '</div>';
                            html_hbltds += '<div class="collapse collapse-habilitacao" id="collapseModulosHabilitado'+retorno_hbltds[i].id_habilitacao+'">';
                                html_hbltds += '<form method="post" enctype="multipart/form-data" id="formHabilitacao'+retorno_hbltds[i].id_habilitacao+'" name="formHabilitacao'+retorno_hbltds[i].id_habilitacao+'" class="form" role="" onsubmit="return false;">'
                                    html_hbltds += '<input type="hidden" id="inputIdHabilitacao" name="inputIdHabilitacao" value="'+btoa(retorno_hbltds[i].id_habilitacao)+'">';
                                    html_hbltds += '<input type="hidden" id="inputIdCliente" name="inputIdCliente" value="'+btoa(retorno_hbltds[i].id_cliente)+'">';
                                    html_hbltds += '<input type="hidden" id="inputIdProduto" name="inputIdProduto" value="'+btoa(retorno_hbltds[i].id)+'">';
                                    html_hbltds += '<input type="hidden" id="inputModulos" name="inputModulos" value="'+modulos+'">';
                                    html_hbltds += '<div class="row">';
                                      //Período
                                      html_hbltds += '<div class="col-md-6">'
                                                  +'<label for="inputHabilitacaoProdutoPeriodo" class="control-label">Período</label>'
                                                  +'<div class="input-group input-group-md">'
                                                      +'<div class="input-daterange input-group" id="datepicker">'
                                                          +'<input type="text" class="form-control" id="inputHabilitacaoProdutoInicio" name="inputHabilitacaoProdutoInicio" value="'+retorno_hbltds[i].periodo_inicio+'" '+disabled+'/>'
                                                          +'<span class="input-group-addon">Até</span>'
                                                          +'<input type="text" class="form-control" id="inputHabilitacaoProdutoFim" name="inputHabilitacaoProdutoFim" value="'+retorno_hbltds[i].periodo_fim+'" '+disabled+'/>'
                                                      +'</div>'
                                                  +'</div>'
                                              +'</div>';
                                      //Contrato SAX
                                      html_hbltds += '<div class="col-md-6">'
                                                  +'<label for="inputContratoSax" class="control-label">Contrato SAX</label>'
                                                  +'<input type="text" class="form-control" id="inputContratoSax" name="inputContratoSax" value="'+((retorno_hbltds[i].contrato_sax != null) ? retorno_hbltds[i].contrato_sax : '')+'">'
                                              +'</div>';
                                      //Habilitar Demo
                                      html_hbltds += '<div class="col-md-12">'
                                                      +'<div class="form-check">'
                                                        +'<input type="checkbox" id="inputHabilitarDemo" name="inputHabilitarDemo" value="S" '+demo+' data-form="formHabilitacao'+retorno_hbltds[i].id_habilitacao+'" onclick="desabilitarPeriodo(this)" class="form-check-input">'
                                                        +'<label class="form-check-label" for="inputHabilitarDemo">Demonstração(30 Dias) ?</label>'
                                                      +'</div>'
                                                    +'</div>';
                                      html_hbltds += "<div class='col-md-12'>";
                                      html_hbltds += "<button type='submit' id='habilitar_produto' class='btn btn-success habilitar-produto' style='position: relative;' data-action='update' onclick='return habilitarProduto(this.form,this,\""+base_url+"\");'><i class='fa fa-floppy-o'></i> Salvar</button>&nbsp;";
                                      if(retorno_hbltds[i].status == 'N'){
                                          html_hbltds += '<button type="submit" class="btn btn-warning" style="position: relative;" data-habilitacao="'+retorno_hbltds[i].id_habilitacao+'" data-status="S" onclick="return desabilitarProduto(this);"><i class="glyphicon glyphicon-ok-circle"></i> Reabilitar</button>&nbsp;';
                                      }else{
                                          html_hbltds += '<button type="submit" class="btn btn-danger" style="position: relative;" data-habilitacao="'+retorno_hbltds[i].id_habilitacao+'" data-status="N" onclick="return desabilitarProduto(this);"><i class="glyphicon glyphicon-ban-circle"></i> Desabilitar</button>';
                                      }                                        
                                      html_hbltds += '</div>';
                                    html_hbltds += '</div>';
                                    html_hbltds += '<br>';

                                if(retorno_hbltds[i].modulos.length > 0){
                                    //Módulos
                                    html_hbltds += '<div class="panel panel-default"><div class="panel-heading">'
                                                        +'<div class="row">'
                                                            +'<div class="col-sm-4">Módulo(s)</div>'
                                                        +'</div>'
                                                    +'</div>';
                                    html_hbltds += '<table id="lista-modulos" class="table table-striped table-hover">'
                                                        +'<thead>'
                                                            +'<tr>'
                                                                +'<th class="col-sm-1"><i class="fa fa-database"></i> ID</th>'
                                                                +'<th class="col-sm-4"><i class="glyphicon glyphicon-circle-arrow-down"></i> Módulo</th>'
                                                                +'<th class="col-sm-2"><i class="glyphicon glyphicon-user"></i> Usuários</th>'
                                                                +'<th class="col-sm-2"><i class="glyphicon glyphicon-ok-circle"></i> Liberado</th>'
                                                                +'<th class="col-sm-2"><i class="glyphicon glyphicon-ban-circle"></i> Bloqueado</th>'
                                                                +'<th class="col-sm-2"><i class="glyphicon glyphicon-list-alt"></i> Consulta</th>'
                                                            +'</tr>'
                                                        +'</thead>'
                                                        +'<tbody>';
                                            for(var im = 0; im < retorno_hbltds[i].modulos.length; im++){
                                                //Verificar se já possui módulo habilitado para carregar id da habilitação
                                                var hbltc_mdl_id = '';
                                                if(retorno_hbltds[i].modulos[im].id_hbltd_mdl > 0){
                                                    hbltc_mdl_id = retorno_hbltds[i].modulos[im].id_hbltd_mdl;
                                                }
                                                //Carrega id da habilitação de acordo com o módulo
                                                html_hbltds += '<input type="hidden" id="inputHabilitacaoModuloId" name="inputHabilitacaoModuloId['+retorno_hbltds[i].modulos[im].id+']" value="'+hbltc_mdl_id+'">';
                                                html_hbltds += '<tr>';
                                                    html_hbltds += '<td class="text-left">'+retorno_hbltds[i].modulos[im].id+'</td>';
                                                    html_hbltds += '<td class="text-left">'+retorno_hbltds[i].modulos[im].nome+'</td>';
                                                    html_hbltds += '<td class="text-left"><input type="text" id="inputHabilitacaoModuloUsu" name="inputHabilitacaoModuloUsu-'+retorno_hbltds[i].modulos[im].id+'" class="col-sm-5" value="'+retorno_hbltds[i].modulos[im].usuarios+'" ></td>';
                                                    //Verificação de status de módulo
                                                    if(retorno_hbltds[i].modulos[im].status == 'L'){
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="L" checked="checked"></td>';
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="B"></td>';
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="C"></td>';
                                                    }else if(retorno_hbltds[i].modulos[im].status == 'B'){
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="L"></td>';
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="B" checked="checked"></td>';
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="C"></td>';
                                                    }else if(retorno_hbltds[i].modulos[im].status == 'C'){
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="L"></td>';
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="B"></td>';
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="C" checked="checked"></td>';
                                                    }else{
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="L"></td>';
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="B" checked="checked"></td>';
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="C"></td>';
                                                    }
                                                html_hbltds += '</tr>';
                                            }
                                        html_hbltds += '</tbody>';
                                    html_hbltds += '</table>';
                                }
                                html_hbltds += '</form>';
                            html_hbltds += '</div>';
                        html_hbltds += '</div>';
                    }
                div.find('#content-produtos-habilitados').html(html_hbltds);                
            }else{                
                div.find('#content-produtos-habilitados').html('');
            }
        },
        error: function (data) {
            alert('Falha no envio, entre em contato com o administrador!');
        }
    });
}

function habilitarProduto(obj, acao, base_url) {
    var form = new FormData($('#'+obj.id)[0]);
    var funcao = $(acao).attr('data-action');
    var acao = "";

    if(funcao == "insert"){
        acao = 'cadastrar_habilitacao';
    }else if(funcao == "update"){
        acao = 'atualizar_habilitacao';
    }

    $.ajax({
        url: base_url+'habilitacoes/'+acao,
        type: 'POST',
        xhrFields: {
            withCredentials: true
        },
        async: true,
        cache: false,
        contentType: false,
        processData: false,
        data: form,
        beforeSend: function( xhr ) {
          $(".overlay").show();
        },
        success: function (data) {
          /*var retorno = jQuery.parseJSON(data);
          $(".overlay").hide();*/
          window.location.href = base_url+'habilitacoes';
        },
        error: function (data) {
          alert('Falha no envio, entre em contato com o administrador!');
        }
    });
}

function desabilitarProduto(obj) {
    $('#inputHabilitacaoId').val(btoa($(obj).attr('data-habilitacao')));
    $('#inputStatus').val($(obj).attr('data-status'));
    $('#confirm').modal({
      backdrop: 'static',
      keyboard: false
    })
    .one('click', '#desabilitar', function(e) {
      $('#form-desativar-habilitacao').submit();
    });
}

//Inserir máscara de data para campos período
function insertDateMask(obj) {
    var form = $(obj).attr('data-form');
    $('#'+form+' #inputHabilitacaoProdutoInicio').inputmask('99/99/9999');
    $('#'+form+' #inputHabilitacaoProdutoFim').inputmask('99/99/9999');    
}

//Desabilitar campos de período quando demonstração
function desabilitarPeriodo(obj){
    var form = $(obj).attr('data-form');
    if($(obj).prop('checked')){
        $('#'+form+' #inputHabilitacaoProdutoInicio').prop('disabled', true);
        $('#'+form+' #inputHabilitacaoProdutoFim').prop('disabled', true);
        $('#'+form+' #inputHabilitacaoProdutoInicio').val('');
        $('#'+form+' #inputHabilitacaoProdutoFim').val('');
    }else{
        $('#'+form+' #inputHabilitacaoProdutoInicio').prop('disabled', false);
        $('#'+form+' #inputHabilitacaoProdutoFim').prop('disabled', false);
    }
}

function visualizarHistorico(id_habilitacao, base_url) {
    var form = new FormData();
    form.append('id_habilitacao', id_habilitacao);

    $.ajax({
        url: base_url+'habilitacoes/carregar_historico',
        type: 'POST',
        xhrFields: {
            withCredentials: true
        },
        async: true,
        cache: false,
        contentType: false,
        processData: false,
        data: form,
        success: function (data) {
            var retorno = jQuery.parseJSON(data);            
            if(retorno.erro == false) {
              var html = '<div class="modal-header">'
                        +'<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Fechar</span></button>'
                        +'<h4 class="modal-title-chamado" id="title-chamado"> Histórico de Habilitação</h4>'
                    +'</div>'
                    +'<div class="modal-body">';
              html += '<table class="table table-striped">';
                html += '<tr>'
                  +'<th>ID</th>'
                  +'<th>Usuário</th>'
                  +'<th>Ação</th>'
                  +'<th>Observação</th>'
                  +'<th>Cadastro</th>'                                
                +'</tr>';
                  //Produtos
                  $.each(retorno.data, function(key, item) {
                      html += '<tr>'
                        +'<td>'+item.id+'</td>'
                        +'<td>'+item.usuario+'</td>'
                        +'<td>'+item.acao+'</td>'
                        +'<td>'+item.observacao+'</td>'
                        +'<td>'+item.data_cadastro+'</td>'                                
                      +'</tr>';
                  });
              html += '</table>'
                +'</div>';
              html += '<div class="modal-footer">'
                     +'<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Fechar</button>'
                 +'</div>';

              $("#historico").find(".modal-content").html(html);
              $('#historico').modal('show');
            }
        },
        error: function (data) {
            alert('Falha no envio, entre em contato com o administrador!');
        }
    });
}

//Adicionar linhas extras
$("#form-create_app_unidades_livro .adiciona-video_yt").click(function(){
    var uniqid = '_'+Math.random().toString(36).substr(2, 9);
    var nova_linha = $('<div class="form-group video_yt">'+
                        '<label for="video_yt'+ uniqid +'" class="control-label col-sm-2">Vídeo Youtube <span class="label-numbers"></span></label>' + //no + '</span></label>'+
                        '<div class="col-sm-8">'+
                            '<input type="text" id="video_yt' + uniqid + '" name="video_yt[]" class="form-control" placeholder="Informe o Vídeo do Youtube" maxlength="500">'+
                        '</div>'+
                        '<div class="col-sm-2">'+
                            '<a href="#" class="btn btn-danger remove-video_yt">Remover</a>'+
                        '</div>'+
                    '</div>');
    
    nova_linha.hide();
    $(".video_yt:last").after(nova_linha);
    nova_linha.fadeIn("slow");
    return false;
});

//Remover linha extra
$('#form-create_app_unidades_livro').on('click', '.remove-video_yt', function(){
    $(this).parent().parent().css( 'background-color', '#FF6C6C' );
    $(this).parent().parent().fadeOut("slow", function() {
        $(this).parent().parent().css( 'background-color', '#FFFFFF' );
        $(this).remove();
    });
    return false;
});

function resetQrcodeModal(id) {
  $('#id').val(id);
  $('#resetQrcodeModal').modal('show');
};

$("#sidebar-search").keyup(function() {
  var base_url = $('#base_url').val();
  $.ajax({
    url: base_url+'menus/busca_sidebar',
    type: 'POST',
    data: {q:$(this).val()},
    success: function(data){
      $(".sidebar-menu").html(data);
    }
  });
});

$(".apontarChamado").click(function(event) {
    var id = $(this).attr('id');
    $.ajax({
      url: $("#base_url").val()+'chamados/check_can_apontar',
      type: 'POST',
      data: {},
      beforeSend: function( xhr ) {
      },
      success: function(data){
        var retorno = jQuery.parseJSON(data);

        if(retorno.erro == false) {
          $(".modal-title").html("Apontar Chamado #"+id);
          $('#apontarModal').modal('show');
        } else {
          $("#apontamentoExistenteModal .modal-title").html("Apontamento aberto no chamado #"+retorno.data);
          $("#apontamentoExistenteModal .modal-body").html("<p>Você possui um apontamento aberto, <a target='_blank' href='"+$("#base_url").val()+"/chamados/details/"+retorno.data+"' alt='Chamado "+retorno.data+"' title='Chamado "+retorno.data+"'>acesse aqui o chamado #"+retorno.data+"</a> para para-lo antes de continuar.</p>");
          $("#apontamentoExistenteModal").modal("show");
        }
      }
  });
});

$(".btn_apontamento_excluir").click(function() {
  $("#apontamento_id_excluir").val($(this).attr('id'));
  $('#apontamentoExcluirModal').modal('show');
});

$(".btn_apontamento_editar").click(function() {
  $(".modal-title").html("Apontar Chamado #"+$(this).attr('id'));
  $("#apontamento_id").val($(this).attr('id')).val();
  $("#data").val($("#data-"+$(this).attr('id')).val());
  $("#esforco").val($("#esforco-"+$(this).attr('id')).val());
  $("#hora_inicio").val($("#hora_inicio-"+$(this).attr('id')).val());
  $("#hora_termino").val($("#hora_termino-"+$(this).attr('id')).val());
  $("#observacao").val($("#observacao-"+$(this).attr('id')).val());
  $('#apontarModal').modal('show');
});

$("#hora_inicio").blur(function(){
    var hora_inicio = $("#hora_inicio").val();
    if(hora_inicio != "") {
        $("#hora_termino").val(hora_inicio);
    }
});

$("#esforco").blur(function(){
    var hora_inicio = $("#hora_inicio").val();
    var esforco = $("#esforco").val();
    if(hora_inicio != "") {
        const a = moment.duration(hora_inicio);
        const b = moment.duration(esforco);

        const c = a.add(b);
        $("#hora_termino").val(("0" + c.hours()).slice(-2)+":"+("0" + c.minutes()).slice(-2));

        if(c.days() > 0) {
            startdate = $("#data").val();
            var new_date = moment(startdate, "DD-MM-YYYY").add(c.days(), 'days');
            $("#hora_termino").val(("0" + c.hours()).slice(-2)+":"+("0" + c.minutes()).slice(-2)+" "+new_date.format('DD')+"/"+new_date.format('MM')+"/"+new_date.format('YYYY'));
        }
    }
});

$("#hora_termino").blur(function(){
    var hora_inicio = $("#hora_inicio").val();
    var hora_termino = $("#hora_termino").val();
    if(hora_inicio != "" && hora_termino != "") {
      var today = new Date();
      var dd = String(today.getDate()).padStart(2, '0');
      var mm = String(today.getMonth() + 1).padStart(2, '0'); 
      var yyyy = today.getFullYear();
      var now  = yyyy+"-"+mm+"-"+dd+" "+hora_inicio;
      if(hora_inicio <= hora_termino) {
        var then = yyyy+"-"+mm+"-"+dd+" "+hora_termino;
        var ms = moment(then,"YYYY-MM-DD HH:mm").diff(moment(now,"YYYY-MM-DD HH:mm"));
        var d = moment.duration(ms);
        /*var s = Math.floor(d.asHours()) + moment.utc(ms).format(":mm");*/
        $("#esforco").val(("0" + d.hours()).slice(-2)+":"+("0" + d.minutes()).slice(-2));
      } else {
        $("#hora_termino").val('');
      }
    }
});

$("#esforco").blur(function(){
    var hora_inicio = $("#hora_inicio").val();
    var esforco = $("#esforco").val();
    if(hora_inicio != "") {
        const a = moment.duration(hora_inicio);
        const b = moment.duration(esforco);

        const c = a.add(b);
        $("#hora_termino").val(("0" + c.hours()).slice(-2)+":"+("0" + c.minutes()).slice(-2));

        if(c.days() > 0) {
            startdate = $("#data").val();
            var new_date = moment(startdate, "DD-MM-YYYY").add(c.days(), 'days');
            $("#hora_termino").val(("0" + c.hours()).slice(-2)+":"+("0" + c.minutes()).slice(-2)+" "+new_date.format('DD')+"/"+new_date.format('MM')+"/"+new_date.format('YYYY'));
        }
    }
});

function formatTime(timeInput) {
  intValidNum = timeInput.value;

  if (intValidNum.length == 2) {
      timeInput.value = timeInput.value + ":";
      return false;  
  }
  if (intValidNum == 24 && intValidNum.length == 2) {
      timeInput.value = timeInput.value.length - 2 + "0:";
      return false;
  }

  if (intValidNum.length == 5 && intValidNum.slice(-2) < 60) {
    timeInput.value = timeInput.value;
    return false;
  }
  if (intValidNum.length == 5 && intValidNum.slice(-2) > 60) {
    timeInput.value = timeInput.value.slice(0, 2);
    return false;
  }
  if (intValidNum.length == 5 && intValidNum.slice(-2) == 60) {
    timeInput.value = timeInput.value.slice(0, 2);
    return false;
  }
}

function dataAtualFormatada(){
    var data = new Date(),
        dia  = data.getDate().toString().padStart(2, '0'),
        mes  = (data.getMonth()+1).toString().padStart(2, '0'), //+1 pois no getMonth Janeiro começa com zero.
        ano  = data.getFullYear();
    return dia+"/"+mes+"/"+ano;
}

function startApontamento() {
    var btn = $('.btn-app');
    $.ajax({
        url: $("#base_url").val()+'chamados/check_can_apontar',
        type: 'POST',
        data: {},
        beforeSend: function( xhr ) {
          btn.attr('disabled', true);
        },
        success: function(data){
          var retorno = jQuery.parseJSON(data);

          if(retorno.erro == false) {
            var progressBar = $('.progress-bar');
            var percentVal = 0;
            var d = new Date();
            var h = d.getHours();
            var m = d.getMinutes();
            var hora_inicio = ("0" + h).slice(-2)+":"+("0" + m).slice(-2);

            $.ajax({
              url: $("#base_url").val()+'chamados/apontamento_automatico_criar',
              type: 'POST',
              data: {chamado_id:$("#chamado_id").val(), data:dataAtualFormatada(), hora_inicio:hora_inicio, esforco:"00:00"},
              beforeSend: function( xhr ) {
                /*btn.attr('disabled', true);*/
              },
              success: function(data){
                  var retorno = jQuery.parseJSON(data);

                  if(retorno.erro == false) {
                      btn.attr('onclick', 'stopApontamento()');
                      btn.html('<i class="fa fa-stop"></i> Parar');
                      $('.inicio-apontamento-span').html(hora_inicio);
                      $('.inicio-apontamento').fadeIn();
                      $('#obs_apontamento').removeAttr('readonly');
                      $('#apontamento_id').val(retorno.data);

                      myTimer = window.setInterval(function(){
                          percentVal += 1;
                          progressBar.css("width", ((percentVal / 60) * 100) + '%').attr("aria-valuenow", ((percentVal / 60) * 100) + '%');

                          if (percentVal == 60)
                          {
                              percentVal = 0;
                          }
                      }, 1000);

                      $('.duration-clock').countimer();
                  }
              }
            });
          } else {
            $("#apontamentoExistenteModal .modal-title").html("Apontamento aberto no chamado #"+retorno.data);
            $("#apontamentoExistenteModal .modal-body").html("<p>Você possui um apontamento aberto, <a target='_blank' href='"+$("#base_url").val()+"/chamados/details/"+retorno.data+"' alt='Chamado "+retorno.data+"' title='Chamado "+retorno.data+"'>acesse aqui o chamado #"+retorno.data+"</a> para para-lo antes de continuar.</p>");
            $("#apontamentoExistenteModal").modal("show");
          }
          btn.attr('disabled', false);
        }
    });
}

function stopApontamento() {
    if (typeof myTimer !== 'undefined') {
        clearInterval(myTimer);
    }
    var progressBar = $('.progress-bar');
    var percentVal = 0;
    var btn = $('.btn-app');
    var observacao = $('#obs_apontamento').val();
    var hora_inicio = $('.inicio-apontamento-span').html();
    var d = new Date();
    var h = d.getHours();
    var m = d.getMinutes();
    var hora_termino = ("0" + h).slice(-2)+":"+("0" + m).slice(-2);
    var esforco = $('.duration-clock').html();
    $('#obs_apontamento').val('');
    $('#obs_apontamento').attr('readonly','readonly');
    $('.inicio-apontamento-span').append(' - '+hora_termino);
    btn.attr('onclick', 'startApontamento()');
    btn.html('<i class="fa fa-play"></i> Iniciar');
    progressBar.css("width", percentVal + '%').attr("aria-valuenow", percentVal + '%');
    try {
        $('.duration-clock').countimer('destroy');
    } catch(e) {}
    $('.duration-clock').html("00:00:00");

    $.ajax({
        url: $("#base_url").val()+'chamados/apontamento_automatico_criar',
        type: 'POST',
        data: {chamado_id:$("#chamado_id").val(), data:dataAtualFormatada(), hora_inicio:hora_inicio, hora_termino:hora_termino, observacao:observacao, esforco:esforco, apontamento_id:$('#apontamento_id').val()},
        beforeSend: function( xhr ) {
          btn.attr('disabled', true);
        },
        success: function(data){
            btn.attr('disabled', false);
        }
    });
}

function loadUnidades(vendedor_id) {
    clearDivsCondicoes();
    $.ajax({
        url: $("#base_url").val()+'cupom/carregar_unidades',
        type: 'POST',
        data: {vendedor_id:vendedor_id},
        success: function(data){
            $("#unidades").html(data);
        }
    });
};

function loadCondicoesVincularUsuario(unidade_id) {
    if($(".list-condicao-unid-"+unidade_id).length) {
        $(".msg-condicoes").html("");
        $(".list-group").fadeOut();
        $(".list-condicao-unid-"+unidade_id).fadeIn();
    } else {
        $(".list-group").fadeOut();
        if(unidade_id == "")
            $(".msg-condicoes").html('Selecione uma unidade acima.');
        else
            $(".msg-condicoes").html('<p class="control-label" style="text-align: left !important;">Não há condições disponíveis para essa unidade.</p>');
    }
}

function loadCursos(unidade_id) {
  $.ajax({
      url: $("#base_url").val()+'cupom/carregar_cursos',
      type: 'POST',
      data: {unidade_id:unidade_id},
      beforeSend: function( xhr ) {
      },
      success: function(data){
          var result = $.parseJSON(data);
          $(".list-curso").html(result.curso.html);
      }
  });
};
function loadLivros(curso_id) {
  $.ajax({
      url: $("#base_url").val()+'cupom/carregar_cursos',
      type: 'POST',
      data: {curso_id:curso_id},
      beforeSend: function( xhr ) {
      },
      success: function(data){
          var result = $.parseJSON(data);
          $(".list-livro").html(result.livro.html);
      }
  });
};


function loadCondicoes(curso_id) {
    var unidade_id = $("#unidades").val();
    
    if(unidade_id == "") {
        clearDivsCondicoes();
    } else {
        $.ajax({
            url: $("#base_url").val()+'cupom/carregar_condicoes',
            type: 'POST',
            data: {unidade_id: unidade_id, vendedor_id: $("#vendedor").val(), curso_id: curso_id},
            beforeSend: function( xhr ) {
              $(".loading").show();
            },
            success: function(data){
                var result = $.parseJSON(data);
                if(result.erro == true)
                    $(".msg-condicoes").html('Selecione uma unidade acima.');
                else {
                    if(result.entrada.count == 0) {
                        $(".msg-entradas").html('<p class="control-label" style="text-align: left !important;">Não há entradas disponíveis para essa unidade.</p>');
                        $(".list-entradas").html('');
                    } else {
                        $(".msg-entradas").html('');
                        $(".list-entradas").html(result.entrada.html);
                    }

                    if(result.condicao.count == 0) {
                        $(".msg-condicoes").html('<p class="control-label" style="text-align: left !important;">Não há condições disponíveis para essa unidade.</p>');
                        $(".list-condicoes").html('');
                    } else {
                        $(".msg-condicoes").html('');
                        $(".list-condicoes").html(result.condicao.html);
                    }

                    $("#turmas").html(result.turma.html);
                    $("#disciplinas").html(result.disciplina.html);
                    $('#disciplinas').data('fastselect').destroy();
                    $('.multipleSelect').fastselect();
                }
                $(".loading").hide();
            }
        });
    }
};

function clearDivsCondicoes() {
    $(".msg-entradas").html('<p class="control-label" style="text-align: left !important;">Selecione uma unidade acima.</p>');
    $(".list-entradas").html('');
    $(".msg-condicoes").html('<p class="control-label" style="text-align: left !important;">Selecione uma unidade acima.</p>');
    $(".list-condicoes").html('');
}

$('.condicao').change(function() { 
  var item_funcao = $(this);
  if($('#sequencial').is(':checked')) { 
    if(item_funcao.is(':checked')) { 
      $('<input>').attr({type: 'hidden', id: 'condicao_hidden-'+item_funcao.val(), name: 'condicao_hidden[]', value: item_funcao.val()}).appendTo('.list-condicoes'); 
      $('<input>').attr({type: 'hidden', id: 'condicao_hidden_order-'+item_funcao.val(), name: 'condicao_hidden_order[]', value: $('input[name*="condicao_hidden[]"]').length }).appendTo('.list-condicoes'); 
    } else {
      $('#condicao_hidden-'+item_funcao.val()).remove(); 
      $('#condicao_hidden_order-'+item_funcao.val()).remove(); 
    }
    $.ajax({
      url: $("#base_url").val()+'cupom/carregar_vencimentos', 
      type: 'POST', 
      data: { condicoes:$('.list-condicoes input[name=\"condicao_hidden[]\"]').serialize(), dt_inicio: $('#datetimehourpickerSequencia').data('date') }, 
      beforeSend: function( xhr ) { 
        $('.vencimentos').find('.loading').show(); 
      }, 
      success: function(data){ 
        var result = $.parseJSON(data); 
        var html = ''; 
        if(result.erro == false) {
          $.each(result.data, function(key, item) { 
            html += '<tr><td>'+item.desc+'</td><td>'+item.parc.toString()+'</td><td>'+item.primeira_parc+'</td><td>'+item.ultima_parc+'</td><td>'+item.vlr_bruto+'</td><td>'+item.desconto+'</td><td>'+item.vlr_liquido+'</td></tr>'; 
          });
          $('#vencimentos_table').find('tbody').html(html); 
        } else {
          $('#condicao_hidden-'+item_funcao.val()).remove(); 
          $('#condicao_hidden_order-'+item_funcao.val()).remove(); 
          $('.condicao').removeAttr('checked');
          alert(result.msg);
        }
        $('.vencimentos').find('.loading').hide(); 
        return true;
      } 
    }); 
  } else { 
    return false; 
  } 
});

$('#isento_matricula').change(function() {
    if(this.checked) {
        $(".tipo_entrada").fadeOut();
        $(".entrada").fadeOut();
    } else {
        $(".tipo_entrada").fadeIn();
        $(".entrada").fadeIn();
    }
});

$('#sequencial').change(function() {
    if(this.checked) {
        $(".data_inicio_sequencial").fadeIn();
        $(".vencimentos").fadeIn();
        $('.condicao').removeAttr('checked');
        $("input[name='condicao_hidden[]']").remove(); 
        $("input[name='condicao_hidden_order[]']").remove(); 
    } else {
        $(".data_inicio_sequencial").fadeOut();
        $("input[name='condicao_hidden[]']").remove(); 
        $("input[name='condicao_hidden_order[]']").remove(); 
        $(".vencimentos").fadeOut();
    }
});

$('#unidade_turmas').change(function(e){
    var id_unidade = $('#unidade_turmas').val();
    var base_url = $('#base_url').val();
    carregar_select_default(id_unidade, 'curso', base_url+'app_turmas/get_cursos', 'Esta unidade não possui curso(s)!');
});

$('#curso').change(function(e){
  var id_livro = $('#curso').val();
  var base_url= $('#base_url').val();
  carregar_select_default(id_livro, 'livro', base_url+'app_turmas/get_livros', 'Este curso não possui livro(s)!');
})

$(".add-to-email").click(function() {
  if(isEmail($("input[name='email']").val())) {
    var base_url = $("#base_url").val();

    var html = $( "#inputs_hidden" ).html();

    if($( "input[name*='pessoas_email']" ).length > 0) {
      var i = parseInt($( "input[name*='pessoas_email']" ).last().attr('class').replace("pessoas-email-", ""));
    } else
      var i = 0;

    var table = $('#datatable').DataTable();

    var cobranca = "N";
    var doc_fiscais = "N";
    if($("select[name='email_cobranca'] option").filter(':selected').val() == "S")
      cobranca = "S";
    if($("select[name='email_doc_fiscais'] option").filter(':selected').val() == "S")
      doc_fiscais = "S";
        
    html += "<input type='hidden' class='pessoas-email-"+(i+1)+"' name='pessoas_email["+(i+1)+"][email]' value='"+$("input[name='email']").val()+"'>";
    html += "<input type='hidden' class='pessoas-email-"+(i+1)+"' name='pessoas_email["+(i+1)+"][cobranca]' value='"+cobranca+"'>";
    html += "<input type='hidden' class='pessoas-email-"+(i+1)+"' name='pessoas_email["+(i+1)+"][doc_fiscais]' value='"+doc_fiscais+"'>";

    if(cobranca == "S")
      cobranca = "Sim";
    else
      cobranca = "Não";

    if(doc_fiscais == "S")
      doc_fiscais = "Sim";
    else
      doc_fiscais = "Não";
           
    var rowNode = table.row.add( [ $("input[name='email']").val(), cobranca, doc_fiscais, '<button type="button" title="Remover" class="btn btn-danger" onclick="remove_email(this)" id="'+(i+1)+'"><i class="fa fa-times" aria-hidden="true"></i><span class="txt-btn-orders"></span></button>' ] ).draw().node();
    
    $("#inputs_hidden").html(html);
    
    $("input[name='email']").css('border-color', '');
    
    $("input[name='email']").val('');
    $("select[name='email_cobranca']").val('');
    $("select[name='email_doc_fiscais']").val('');
  } else {
    $("input[name='email']").css('border-color', 'red');
  }
});

function remove_email(item) {
  $('#confirm-delete').modal({
      backdrop: 'static',
      keyboard: false
    })
    .one('click', '.btn-ok', function(e) {
      var table = $('#datatable').DataTable();
      if(item != false) {
        table.row($(item).parents('tr')).remove().draw();

        $(".pessoas-email-"+$(item).attr('id')).remove();
      } else {
        table.rows().remove().draw();
        $("#inputs_hidden").html('');
      }
  });
};

function isEmail(email) {
  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  return regex.test(email);
}

$( "#cnpj_cliente" ).blur(function() {
    var field = $(this);
    $.ajax({
      url: $("#base_url_controlador").val()+'/validar_cnpj',
      type: 'POST',
      data: {doc:field.val(), id: $("#id").val()},
      beforeSend: function( xhr ) {

      },
      success: function(data){
        var result = $.parseJSON(data);
        if(result.erro == true) {
          field.parent().addClass('has-error');
          $('.help-block').remove();
          field.parent().parent().append('<span class="help-block">'+result.msg+'</span>');
          if(result.codigo == 1) {
            $("#modal-cliente-existente").modal('show');
            $(".href-cliente").attr("href", result.href);
          }
          if(result.codigo == 2) {
            $('#modal-pessoa-existente').modal({
              backdrop: 'static',
              keyboard: false
            })
            .one('click', '.btn-ok', function(e) {
              $("#ie").val(result.pessoa.rg);
              $("#razao_social").val(result.pessoa.nome_razao_social);
              $("#nome_fantasia").val(result.pessoa.apelido_nome_fantasia);
              $("#telefone").val(result.pessoa.telefone);
              $("#celular").val(result.pessoa.celular);
              $("#site").val(result.pessoa.site);
              $("#cep").val(result.pessoa.cep);
              $("#logradouro").val(result.pessoa.endereco);
              $("#numero").val(result.pessoa.numero);
              $("#bairro").val(result.pessoa.bairro);
              $("#cidade").val(result.pessoa.cidade);
              $("#uf").val(result.pessoa.uf);

              $.each(result.pessoa.enderecos, function(key, item) { 
                if(item.tipo == "C") {
                  $("#cep_cobranca").val(item.cep);
                  $("#logradouro_cobranca").val(item.endereco);
                  $("#numero_cobranca").val(item.numero);
                  $("#bairro_cobranca").val(item.bairro);
                  $("#cidade_cobranca").val(item.cidade);
                  $("#uf_cobranca").val(item.uf);
                }
                if(item.tipo == "E") {
                  $("#cep_entrega").val(item.cep);
                  $("#logradouro_entrega").val(item.endereco);
                  $("#numero_entrega").val(item.numero);
                  $("#bairro_entrega").val(item.bairro);
                  $("#cidade_entrega").val(item.cidade);
                  $("#uf_entrega").val(item.uf);
                }
              });

              var i=0;
              $.each(result.pessoa.emails, function(key, item) {
                var table = $('#datatable').DataTable();
                table.clear();
                if(item.cobranca == "N")
                  item.cobranca = "Não";
                else
                  item.cobranca = "Sim";

                if(item.doc_fiscais == "N")
                  item.doc_fiscais = "Não";
                else
                  item.doc_fiscais = "Sim";
                var rowNode = table.row.add( [ item.email, item.cobranca, item.doc_fiscais, '<button type="button" title="Remover" class="btn btn-danger" onclick="remove_email(this)" id="'+(i+1)+'"><i class="fa fa-times" aria-hidden="true"></i><span class="txt-btn-orders"></span></button>' ] ).draw().node();
              });
            });
          }
        } else {
          $('.help-block').remove();
          field.parent().removeClass('has-error');
        }
      }
    });
});

$( "#cpf_cliente" ).blur(function() {
    var field = $(this);
    $.ajax({
      url: $("#base_url_controlador").val()+'/validar_cpf',
      type: 'POST',
      data: {doc:$(this).val(), id: $("#id").val()},
      beforeSend: function( xhr ) {

      },
      success: function(data){
        var result = $.parseJSON(data);
        if(result.erro == true) {
          field.parent().addClass('has-error');
          $('.help-block').remove();
          field.parent().append('<span class="help-block">'+result.msg+'</span>');
          if(result.codigo == 1) {
            $("#modal-cliente-existente").modal('show');
            $(".href-cliente").attr("href", result.href);
          }
          if(result.codigo == 2) {
            $('#modal-pessoa-existente').modal({
              backdrop: 'static',
              keyboard: false
            })
            .one('click', '.btn-ok', function(e) {
              $("#rg").val(result.pessoa.rg);
              $("#nome").val(result.pessoa.nome_razao_social);
              $("#apelido").val(result.pessoa.apelido_nome_fantasia);
              $("#telefone").val(result.pessoa.telefone);
              $("#celular").val(result.pessoa.celular);
              $("#site").val(result.pessoa.site);
              $("#cep").val(result.pessoa.cep);
              $("#logradouro").val(result.pessoa.endereco);
              $("#numero").val(result.pessoa.numero);
              $("#bairro").val(result.pessoa.bairro);
              $("#cidade").val(result.pessoa.cidade);
              $("#uf").val(result.pessoa.uf);

              $.each(result.pessoa.enderecos, function(key, item) { 
                if(item.tipo == "C") {
                  $("#cep_cobranca").val(item.cep);
                  $("#logradouro_cobranca").val(item.endereco);
                  $("#numero_cobranca").val(item.numero);
                  $("#bairro_cobranca").val(item.bairro);
                  $("#cidade_cobranca").val(item.cidade);
                  $("#uf_cobranca").val(item.uf);
                }
                if(item.tipo == "E") {
                  $("#cep_entrega").val(item.cep);
                  $("#logradouro_entrega").val(item.endereco);
                  $("#numero_entrega").val(item.numero);
                  $("#bairro_entrega").val(item.bairro);
                  $("#cidade_entrega").val(item.cidade);
                  $("#uf_entrega").val(item.uf);
                }
              });

              var i=0;
              $.each(result.pessoa.emails, function(key, item) {
                var table = $('#datatable').DataTable();
                table.clear();
                if(item.cobranca == "N")
                  item.cobranca = "Não";
                else
                  item.cobranca = "Sim";

                if(item.doc_fiscais == "N")
                  item.doc_fiscais = "Não";
                else
                  item.doc_fiscais = "Sim";
                var rowNode = table.row.add( [ item.email, item.cobranca, item.doc_fiscais, '<button type="button" title="Remover" class="btn btn-danger" onclick="remove_email(this)" id="'+(i+1)+'"><i class="fa fa-times" aria-hidden="true"></i><span class="txt-btn-orders"></span></button>' ] ).draw().node();
              });
            });
          }
        } else {
          $('.help-block').remove();
          field.parent().removeClass('has-error');
        }
      }
    });
});

/*function show_buscar_receita() {
  $.ajax({
    url: $("#base_url").val()+'contatos/buscar_img_receita', 
    type: 'POST', 
    data: { cnpj : $('#cnpj').val(), img_receita : $("#img_receita").val() }, 
    beforeSend: function( xhr ) { 
      
    }, 
    success: function(data){ 
      $(".img_html").html('<img id="captcha_cnpj" src="'+data+'" border="0">'); 
      $(".img_receita").fadeIn(); 
    }
  });
}

function buscar_receita() {
  $.ajax({
    url: $("#base_url").val()+'contatos/buscar_receita', 
    type: 'POST', 
    data: { cnpj : $('#cnpj').val(), img_receita : $("#img_receita").val() }, 
    beforeSend: function( xhr ) { 
      
    }, 
    success: function(data){ 
      var result = $.parseJSON(data);

      $(".img_html").html('');
      $("#img_receita").val('');
      $(".img_receita").fadeOut(); 

      if(result.status == "OK") {
        $("#nome").val(result[2]);
        $("#celular").val(result[13]);
        $("#cidade").val(result[10]);
        $("#estado").val(result[11]);
        $("#email").val(result[12]);
      } else {
        alert(result.status);
      }
    }
  });
}*/

function buscar_receita(btn) {
  if($('#cnpj').val() == "") {
    return false;
  }
  $.ajax({
    url: $("#base_url").val()+'contatos/buscar_receita', 
    type: 'POST', 
    data: { cnpj : $("input[name='cnpj']").val() }, 
    beforeSend: function( xhr ) { 
      $(btn).attr('disabled', true);
      $("#razao_social").val('');
      $("#nome_fantasia").val('');
      $("#celular").val('');
      $("#cidade").val('');
      $("#estado").val('');
      $("#email").val('');
      $("#razao_social").attr('placeholder', 'Carregando...');
      $("#nome_fantasia").attr('placeholder', 'Carregando...');
      $("#celular").attr('placeholder', 'Carregando...');
      $("#cidade").attr('placeholder', 'Carregando...');
      $("#estado").attr('placeholder', 'Carregando...');
      $("#email").attr('placeholder', 'Carregando...');

      $("#cep").val('');
      $("#cep").attr('placeholder', 'Carregando...');
      $("#logradouro").val('');
      $("#logradouro").attr('placeholder', 'Carregando...');
      $("#numero").val('');
      $("#numero").attr('placeholder', 'Carregando...');
      $("#uf").val('');
      $("#uf").attr('placeholder', 'Carregando...');
      $("#bairro").val('');
      $("#bairro").attr('placeholder', 'Carregando...');
      $("#fantasia").val('');
      $("#fantasia").attr('placeholder', 'Carregando...');
      $("input[name='data_fundacao']").val('');
      $("input[name='data_fundacao']").attr('placeholder', 'Carregando...');
    }, 
    success: function(data){ 
      /*contato*/
      $("#razao_social").attr('placeholder', '');
      $("#nome_fantasia").attr('placeholder', '');
      $("#celular").attr('placeholder', '');
      $("#cidade").attr('placeholder', '');
      $("#estado").attr('placeholder', '');
      $("#email").attr('placeholder', '');

      /*cliente*/
      $("#cep").attr('placeholder', '');
      $("#logradouro").attr('placeholder', '');
      $("#numero").attr('placeholder', '');
      $("#uf").attr('placeholder', '');
      $("#bairro").attr('placeholder', '');
      $("#fantasia").attr('placeholder', '');
      $("input[name='data_fundacao']").attr('placeholder', '');

      try {
        var result = $.parseJSON(data);

        if(result.status == "OK") {
          $("#razao_social").val(result.nome);
          $("#nome_fantasia").val(result.fantasia);
          $("#celular").val(result.telefone);
          $("#cidade").val(result.municipio);
          $("#estado").val(result.uf);
          $("#email").val(result.email);

          $("#cep").val(result.cep);
          $("#logradouro").val(result.logradouro);
          $("#numero").val(result.numero);
          $("#uf").val(result.uf);
          $("#bairro").val(result.bairro);
          $("#fantasia").val(result.fantasia);
          $("input[name='data_fundacao']").val(result.abertura);
        } else {
          alert(result.message);
        }
      } catch (e) {
        alert('Permitida apenas 3 buscas por minuto. Aguarde e tente novamente.');
      }

      $(btn).attr('disabled', false);
    }
  });
}


$('.btn-avancar-oportunidade').on( 'click', function () {
    var step = $("#step").val();

    if(step != 1 && step != 2 && step != 3 && step != 4) {
      return;
    }

    if(step == 1) {
      var error = false;
      if($("#lead").val() == "") {
        $("#lead").parent().parent().addClass('has-error');
        $("#lead").parent().parent().find('.help-block').remove();
        $("#lead").parent().parent().append('<span class="help-block">Digite um lead.</span>');
        error = true;
      } else {
        $("#lead").parent().parent().removeClass('has-error');
        $("#lead").parent().parent().find('.help-block').remove();
      }
      if($("#vendedor").val() == "") {
        $("#vendedor").parent().addClass('has-error');
        $("#vendedor").parent().find('.help-block').remove();
        $("#vendedor").parent().append('<span class="help-block">Selecione um vendedor.</span>');
        error = true;
      } else {
        $("#vendedor").parent().removeClass('has-error');
        $("#vendedor").parent().find('.help-block').remove();
      }
      if($("#datetimepickerInicio").find("input").val() == "") {
        $("#datetimepickerInicio").parent().addClass('has-error');
        $("#datetimepickerInicio").parent().find('.help-block').remove();
        $("#datetimepickerInicio").parent().append('<span class="help-block">Digite uma data inicial.</span>');
        error = true;
      } else {
        $("#datetimepickerInicio").parent().removeClass('has-error');
        $("#datetimepickerInicio").parent().find('.help-block').remove();
      }
      if($("#datetimepickerFim").find("input").val() == "") {
        if($("#datetimepickerFim").find("input").prop('disabled') == false) {
          $("#datetimepickerFim").parent().addClass('has-error');
          $("#datetimepickerFim").parent().find('.help-block').remove();
          $("#datetimepickerFim").parent().append('<span class="help-block">Digite uma data final.</span>');
          error = true;
        } else {
          $("#datetimepickerFim").parent().removeClass('has-error');
          $("#datetimepickerFim").parent().find('.help-block').remove();
        }
      } else {
        $("#datetimepickerFim").parent().removeClass('has-error');
        $("#datetimepickerFim").parent().find('.help-block').remove();
      }

      if(error)
        return

      $( ".tab_1" ).removeAttr("data-toggle");
      $( ".tab_1" ).attr("aria-expanded", false);
      $( ".tab_2" ).attr("data-toggle", "tab");
      $( ".tab_2" ).click();

      $(".btn-cancel-step1").hide();
      $(".btn-back-steps").show();
      $(".btn-voltar-oportunidade").attr('href', '#tab_1');
      $(".btn-voltar-oportunidade").attr('data-toggle', "tab");

      if($("#inputs_prods_hidden").html() == "")
        $(".btn-avancar-oportunidade").attr("disabled", true);

      $("#step").val('2');
    }

    if(step == 2) {
      if($("#inputs_prods_hidden").html() == "") {
        alert("Adicione os produtos primeiramente.");
        return;
      }

      $(".btn-voltar-oportunidade").attr('href', '#tab_2');
      $(".btn-voltar-oportunidade").attr('data-toggle', "tab");

      $(".btn-avancar-oportunidade").attr("disabled", true);
      if($( "#accordionStep" ).val() == "4") {
        $(".btn-avancar-oportunidade").attr("disabled", false);
      }

      $( ".tab_2" ).parent().removeClass("disabled");
      $( ".tab_2" ).removeAttr("data-toggle");
      $( ".tab_2" ).attr("aria-expanded", false);
      $( ".tab_3" ).attr("data-toggle", "tab");
      $( ".tab_3" ).click();

      $("#step").val('3');
    }

    if(step == 3) {
      var tables_equips = $(".produtosOportunidadeAccordion").DataTable();

      var ids_equips = $.map(tables_equips.rows('.selected').data(), function (item) {
        return item[1];
      });

      var qtds_equips = tables_equips.$('input').serialize();

      var check_periodo_max_invest = "";
      if($("#check-periodo_max_invest").is(":checked"))
        check_periodo_max_invest = "1";

      $.ajax({
        url: $("#base_url").val()+'oportunidades/revisao_oportunidade', 
        type: 'POST', 
        data: {data : $("#form-create_oportunidade").serializeJSON(), check_periodo_max_invest : check_periodo_max_invest, ids_equips : ids_equips, qtds_equips : qtds_equips}, 
        beforeSend: function( xhr ) { 
          $(".btn-avancar-oportunidade").attr("disabled", true);
          $(".btn-avancar-oportunidade").html('Avançar <i class="fa fa-refresh fa-spin"></i>');
        }, 
        success: function(data){
          var result = $.parseJSON(data);

          $(".vendedor-col").html(result.vendedor_col);
          $(".comprador-col").html(result.comprador_col);
          $(".lead-col").html(result.lead_col);
          $(".produtos-col").html(result.produtos_col);
          $(".equips-col").html(result.equips_col);
          $(".montante-html").html(result.montante_html);

          $(".btn-voltar-oportunidade").attr('href', '#tab_3');
          $( ".tab_3" ).parent().removeClass("disabled");
          $( ".tab_3" ).removeAttr("data-toggle");
          $( ".tab_3" ).attr("aria-expanded", false);
          $( ".tab_4" ).attr("data-toggle", "tab");
          $( ".tab_4" ).click();

          $(".btn-avancar-oportunidade").addClass("btn-lg");
          $(".btn-avancar-oportunidade").html("Concluir");
          /*$(".btn-avancar-oportunidade").attr("disabled", true);

          if(result.aceita) {
            $(".btn-avancar-oportunidade").attr("disabled", false);
            $(".aceita-html").html('');
          } else {
            $(".aceita-html").html(result.aceita_html);
          }*/
          $(".aceita-html").html(result.aceita_html);
          $(".btn-avancar-oportunidade").attr("disabled", false);

          $("#step").val('4');
        }
      });
    }

    if(step == 4) {
      var tables_equips = $(".produtosOportunidadeAccordion").DataTable();

      var ids_equips = $.map(tables_equips.rows('.selected').data(), function (item) {
          return item[1]
      });
      var qtds_equips = tables_equips.$('input').serialize();

      var check_periodo_max_invest = "";
      if($("#check-periodo_max_invest").is(":checked"))
        check_periodo_max_invest = "1";

      $.ajax({
        url: $("#base_url").val()+'oportunidades/salvar_oportunidade', 
        type: 'POST', 
        data: {data : $("#form-create_oportunidade").serializeJSON(), check_periodo_max_invest : check_periodo_max_invest, ids_equips : ids_equips, qtds_equips : qtds_equips}, 
        beforeSend: function( xhr ) { 
          $(".btn-avancar-oportunidade").attr("disabled", true);
          $(".btn-avancar-oportunidade").html('Concluir <i class="fa fa-refresh fa-spin"></i>');
        }, 
        success: function(data){
          var result = $.parseJSON(data);

          if(result.erro == true) {
            $("#modalErroSalvarOportunidade > .modal-body").html(result.msg);
            $("#modalErroSalvarOportunidade").modal('show');
          } else {
            window.location.href = $("#base_url").val()+'oportunidades/';
          }
        }
      });
    }
});

$('.btn-voltar-oportunidade').on( 'click', function (event) {

  $( ".btns_tabs" ).removeAttr("data-toggle");
  $( ".btns_tabs" ).attr("aria-expanded", false);
  var btn_class = ".tab_"+(parseInt($("#step").val()) - 1);

  event.preventDefault();
  if(btn_class == ".tab_1") {
    $(".btn-cancel-step1").show();
    $(".btn-back-steps").hide();
    $(".btn-avancar-oportunidade").attr("disabled", false);
    $(this).attr('href', "#tab_"+(parseInt($("#step").val()) - 1));
  } else {

    $(".btn-avancar-oportunidade").removeClass("btn-lg");
    $(".btn-avancar-oportunidade").html('Avançar <i class="fa fa-arrow-right" aria-hidden="true"></i>');
    $(".btn-avancar-oportunidade").attr('disabled', false);

    $(this).attr('href', "#tab_"+(parseInt($("#step").val()) - 1));
  }

  $("#step").val(btn_class.replace(".tab_", ""));
  $( btn_class ).attr("data-toggle", "tab");

  $( btn_class ).click();
});


$(".add-to-oportunidade").click(function() {

    if($("#codigo_produto").val() == "") {
      alert("Selecione um produto.");
      return;
    }

    if( Number.isInteger(parseInt($("#qtd_produto").val())) == false || parseInt($("#qtd_produto").val()) <= 0 ) {
      alert("A quantidade não pode ser 0.");
      return;
    }

    if($("#vlr_unit_produto").val() == "") {
      alert("O valor unitário não pode ser 0.");
      return;
    }

    var find = false;
    $('#datatable_order_id').DataTable().rows( function ( idx, data, node ) {
      if(data[0] === $("#codigo_produto").val()) {
        find = true;
      }
    }); 

    if(find == false) {
      addProdutoOportunidade();
    } else {
      confirmChangeProduct($("#codigo_produto").val());
    }
    
});

function addProdutoOportunidade() {
  $.ajax({
    url: $("#base_url").val()+'oportunidades/buscar_fotos_prod', 
    type: 'POST', 
    data: {id : $("#codigo_produto").val(), descricao : $("#descricao_produto").html()}, 
    beforeSend: function( xhr ) { 
      
    }, 
    success: function(data){
      var result = $.parseJSON(data);

      var html = "";
      html += "<input type='hidden' class='prod-"+$("#codigo_produto").val()+"' name='prods[][codigo]' value='"+$("#codigo_produto").val()+"'>";
      html += "<input type='hidden' class='prod-"+$("#codigo_produto").val()+"' name='prods[][descricao]' value='"+$("#descricao_produto").html()+"'>";
      html += "<input type='hidden' class='prod-"+$("#codigo_produto").val()+"' name='prods[][vlr_unit]' value='"+$("#vlr_unit_produto").val()+"'>";
      html += "<input type='hidden' class='prod-"+$("#codigo_produto").val()+"' name='prods[][qtd_produto]' value='"+$("#qtd_produto").val()+"'>";

      var unit_convert = $("#vlr_unit_produto").val().replace(".",".").replace(",",".");
      var column_img = "";
      if(result.count > 0) {
        column_img = result.data;
      }

      var rowNode = $('#datatable_order_id').DataTable().row.add( [ 
        $("#codigo_produto").val(), 
        $("#descricao_produto").html(), 
        column_img, 
        $("#qtd_produto").val(), 
        'R$ '+$("#vlr_unit_produto").val(), 
        'R$ '+number_format(unit_convert * $("#qtd_produto").val(), 2, ',', '.'), 
        '<button type="button" title="Remover" class="btn btn-danger" onclick="remove_prod(this)" id="btn-remover-prod-'+$("#codigo_produto").val()+'"><i class="fa fa-times" aria-hidden="true"></i><span class="txt-btn-orders"></span></button>' 
      ] ).draw().node();

      $("#codigo_produto").val('');
      $("#descricao_produto").html('');
      $("#qtd_produto").val('');
      $("#vlr_unit_produto").val('');

      $("#inputs_prods_hidden").append(html);
      $(".btn-avancar-oportunidade").attr("disabled", false);
    }
  });

}

$('#modalLeads tbody').on( 'click', 'tr', function () {
    var data = $('#leadsOportunidades').DataTable().row(this).data();
    $("#lead").val(data[1]);
    $("#descricao_lead").html(" "+data[2]);
    $("#captador").val(data[6]);
    $("#modalLeads").modal('hide');
});

$('#modalProds tbody').on( 'click', 'tr', function () {
    var data = $('#prodsOportunidades').DataTable().row(this).data();
    $("#codigo_produto").val(data[1]);
    $("#descricao_produto").html(data[2]);
    $("#modalProds").modal('hide');
});

$('#lead').on( 'blur', function () {
    if($(this).val().trim() != "") {
      var find = false;
      var el_val = $(this).val();
      $('#leadsOportunidades').DataTable().rows( function ( idx, data, node ) {             

        if(data[1] === el_val) {
          $("#lead").val(data[1]);
          $("#descricao_lead").html(data[2]);
          $("#captador").val(data[6]);
          find = true;
          return;
        }
      }); 
      
      if(find == false) {
        $("#lead").val('');
        alert("Lead não encontrado.");
      }
    }
});

$('#codigo_produto').on( 'blur', function () {
    if($(this).val().trim() != "") {
      var find = false;
      var el_val = $(this).val();
      $('#prodsOportunidades').DataTable().rows( function ( idx, data, node ) {             

        if(data[1] === el_val) {
          $("#codigo_produto").val(data[1]);
          $("#descricao_produto").html(" "+data[2]);
          find = true;
          return;
        }
      }); 
      
      if(find == false) {
        $("#codigo_produto").val('');
        alert("Produto não encontrado.");
      }
    }
});

function remove_prod(item) {
  var table = $('#datatable_order_id').DataTable();
  if(item != false) {
    table.row($(item).parents('tr')).remove().draw();
    if ( ! table.data().count() ) {
      $(".btn-avancar-oportunidade").attr("disabled", true);
    }
    console.log($(item).attr('id'));
    $(".prod-"+$(item).attr('id').replace('btn-remover-prod-','')).remove();
  } 
};

$('.accordionOportunidades').click(function(e){
  e.preventDefault();
  e.stopPropagation();
});

$('.btnOportunidadeEquipContinue').click(function(e){
  var step = parseInt($("#accordionStep").val());
  $("#collapse"+step).toggle();
  $("#collapse"+(step+1)).toggle();
  $("#accordionStep").val(step+1);

  if(step == 3) {
    $(".btn-avancar-oportunidade").attr("disabled", false);
  }
});

$('.btnOportunidadeEquipBack').click(function(e){
  var step = parseInt($("#accordionStep").val());
  $("#collapse"+step).toggle();
  $("#collapse"+(step-1)).toggle();
  $("#accordionStep").val(step-1);

  if(step <= 4) {
    $(".btn-avancar-oportunidade").attr("disabled", true);
  }
});

function number_format (number, decimals, dec_point, thousands_sep) {
    // Strip all characters but numerical ones.
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function confirmApprove(id) {
  var url = $('#base_url').val()+'oportunidades/aprovar/'+id
  $('#confirmApprove').modal({
      backdrop: 'static',
      keyboard: false
    })
    .one('click', '#approve', function(e) {
      if($("input[name='enviar_email']").is(":checked"))
        url += "/1";
      window.location=url;
    });
}

function confirmChangeProduct(id) {
  $('#confirmChangeProduct').modal({
      backdrop: 'static',
      keyboard: false
    })
    .one('click', '#approveProd', function(e) {
      var table = $('#datatable_order_id').DataTable();
      table.row($("#btn-remover-prod-"+id).parents('tr')).remove().draw();
      if ( ! table.data().count() ) {
        $(".btn-avancar-oportunidade").attr("disabled", true);
      }

      $(".prod-"+id).remove();
      addProdutoOportunidade();
    });
}

$(document).on('click', '[data-toggle="lightbox"]', function(event) {
  event.preventDefault();
  $(this).ekkoLightbox({
    alwaysShowClose: true
  });
});

$('#btn-filtros').click(function(e){
  $(".btn-box-tool").click();
});

function editComissao() {
  $('#editComissao').modal({
      backdrop: 'static',
      keyboard: false
    })
    .one('click', '#editComissaoSim', function(e) {
     
      $.ajax({
        url: $('#base_url').val()+'contatos/buscar_detalhes_contato',
        type: 'POST',
        data: {id:$(this).attr("data-id")},
        success: function(html){
          $(".modal-body").html(html);
          $('#editComissao').modal('show');
        }
      });
    });
}

$(".btn_edit_comissao").click(function() {
  var id = $(this).attr("data-id");
  var num = $(this).attr("data-num");
  var comissao = $("#comissao-"+id).val();

  $(".id_oportunidade_comissao_span").html(num);
  $("#oportunidade_num_modal").val(num);
  $("#oportunidade_id_modal").val(id);
  $("#percentual_comissao_modal").val(comissao);

  $('#editComissao').modal('show');

});

$(".btn_cancelar_contrato").click(function() {
  var id = $(this).attr("data-id");
  var num = $(this).attr("data-num");

  $(".id_oportunidade_span").html(num);
  $("#oportunidade_num_modal_cancel").val(num);
  $("#oportunidade_id_modal_cancel").val(id);

  $('#cancelarContrato').modal('show');

});

$("#form-parametros .adiciona-percentual").click(function(){
  var ultimo_valor_perc_lucr_de = parseFloat($( "input[name*='percentual_lucratividade_de']" ).last().val());
  var ultimo_valor_perc_lucr_ate = parseFloat($( "input[name*='percentual_lucratividade_ate']" ).last().val());
  var ultimo_valor_perc_comis = parseFloat($( "input[name*='percentual_comissao']" ).last().val());

  if(isNaN(ultimo_valor_perc_lucr_de) || (parseInt(ultimo_valor_perc_lucr_de) <= 0 && !isFloat(ultimo_valor_perc_lucr_de))) {
      alert("Preencha corretamente os campos já existentes. (Números inteiros ou decimais)");
      return false;
  }

  if(isNaN(ultimo_valor_perc_lucr_ate) || (parseInt(ultimo_valor_perc_lucr_ate) <= 0 && !isFloat(ultimo_valor_perc_lucr_ate))) {
      alert("Preencha corretamente os campos já existentes. (Números inteiros ou decimais)");
      return false;
  }

  if(isNaN(ultimo_valor_perc_comis) || (parseInt(ultimo_valor_perc_comis) <= 0 && !isFloat(ultimo_valor_perc_comis))) {
      alert("Preencha corretamente os campos já existentes. (Números inteiros ou decimais)");
      return false;
  }

  var uniqid = Math.random().toString(36).substr(2, 9);
  var nova_linha = $('<div class="form-group percentual_comissao">'+
                      '<label class="control-label col-sm-2"></label>' + 
                      '<div class="col-sm-2">' +
                          '<input type="text" id="percentual_lucratividade_de_'+ uniqid +'" name="percentual_lucratividade_de['+ uniqid +']" class="form-control" placeholder="% lucratividade de" value="">' +
                      '</div>' +
                      '<div class="col-sm-2">' +
                          '<input type="text" id="percentual_lucratividade_ate_'+ uniqid +'" name="percentual_lucratividade_ate['+ uniqid +']" class="form-control" placeholder="% lucratividade até" value="">' +
                      '</div>' +
                      '<div class="col-sm-2">' +
                          '<input type="text" id="percentual_comissao_'+ uniqid +'" name="percentual_comissao['+ uniqid +']" class="form-control" placeholder="% comissão" value="">' +
                      '</div>'+
                      '<div class="col-sm-2">'+
                          '<a href="#" class="btn btn-danger remove-percentual">Remover</a>'+
                      '</div>'+
                  '</div>');
  nova_linha.hide();
  $(".percentual_comissao:last").after(nova_linha);
  nova_linha.fadeIn("slow");
  return false;
});

$('#form-parametros').on('click', '.remove-percentual', function(){
  $(this).parent().parent().fadeOut("slow", function() {
      $(this).remove();
  });
  return false;
});

function isFloat(n){
    return Number(n) === n && n % 1 !== 0;
}

function confirmAguardarAprovacao(id) {
  var url = $('#base_url').val()+'oportunidades/aguardar_aprovacao/'+id
  $('#confirmAguardarAprovacao').modal({
      backdrop: 'static',
      keyboard: false
    })
    .one('click', '#aguardarAprovacao', function(e) {
      window.location=url;
    });
}

function openEditarIdAssinaturaIugu(id, orc_id) {
  $(".assinaturaIdClass").html("<div class='input-group input-group-sm'>"+
      "<input type='text' class='form-control' id='assinaturaIdTxt' name='assinaturaIdTxt' value='"+id+"'>"+
          "<span class='input-group-btn'>"+
            "<button type='button' class='btn btn-info btn-flat btn-alterarIdAssinatura' onclick='editarIdAssinaturaIugu(\""+orc_id+"\")'>Salvar</button>"+
          "</span>"+
    "</div>");
}

function editarIdAssinaturaIugu(orc_id) {
  var url = $('#base_url').val()+'iugu/alterar_id_assinatura/';
  var iugu_id = $('#assinaturaIdTxt').val().trim();
  var assinatura_id_base = $('#assinatura_id_base').val();

  if(iugu_id == "") {
    alert("Preencha um id iugu para salvar!");
    return false;
  }

  $.ajax({
    url: url,
    type: 'POST',
    data: {orc_id: orc_id, assinatura_id: iugu_id},
    beforeSend: function( xhr ) { 
      $(".btn-alterarIdAssinatura").attr("disabled", true);
      $(".btn-alterarIdAssinatura").html("Confirmando...");
    }, 
    success: function(data){
      var result = $.parseJSON(data);

      if(result.erro) {
        alert(result.msg);
        $(".assinaturaIdClass").html("<b>"+assinatura_id_base+"</b>"+
          "<a href='#' class='btn btn-tool' title='Editar ID assinatura' alt='Editar ID assinatura' onclick='openEditarIdAssinaturaIugu(\""+assinatura_id_base+"\", \""+orc_id+"\")'>"+
              "<i class='fa fa-pencil'></i>"+
          "</a>");
      } else {
        $('.modal-body').html(result.msg);
        $('#iugu_id').val(iugu_id);
        $('#orc_id').val(orc_id);

        $('#confirmEditAssin').modal({
          backdrop: 'static',
          keyboard: false
        })
        .one('click', '#confirmarEdicao', function(e) {
          window.location=$('#base_url').val() + 'iugu/alterar_id_assinatura_ajax/'+orc_id+'/'+iugu_id;
        });

        $(".assinaturaIdClass").html("<b>"+iugu_id+"</b>"+
          "<a href='#' class='btn btn-tool' title='Editar ID assinatura' alt='Editar ID assinatura' onclick='openEditarIdAssinaturaIugu(\""+iugu_id+"\", \""+orc_id+"\")'>"+
              "<i class='fa fa-pencil'></i>"+
          "</a>");
      }
    }
  });
}


$(".btn_anular_contrato").click(function() {
  var base = $('#base_url').val();
  var orc_id = $(this).attr('id');
  
  $.ajax({
    url: base+'contratos/verificar_anulacao',
    type: 'POST',
    data: {orc_id: orc_id},
    beforeSend: function( xhr ) { 
      $(".loading").show();
    }, 
    success: function(data){
      var result = $.parseJSON(data);

      if(result.erro == false) {
        var url_submit = base+'contratos/anular/'+orc_id;
        $('#form-contrato_anulado').attr('action', url_submit);
        $("#contrato_id").val(orc_id);
        $("#span_contrato_anular").html(orc_id);
        $('.html-anular').html(result.html);
        $('#anularContratoModal').modal('show');

        $(".loading").hide();
      }
    }
  });
});


function carregarProdutosContrato(id_contrato, id_cliente, base_url, id_habilitacao){

    $('#filial').val(id_cliente);

    var form = new FormData();
    form.append('id_cliente', id_cliente);

    $.ajax({
        url: base_url+'habilitacoes/carregar_produtos',
        type: 'POST',
        xhrFields: {
            withCredentials: true
        },
        async: true,
        cache: false,
        contentType: false,
        processData: false,
        data: form,
        beforeSend: function( xhr ) { 
          $('#divHabilitacaoProdutosCliente').hide();
          $(".loading").show();
        }, 
        success: function (data) {
            if(data == false) {
              $(".loading").hide();
              return false;
            }

            var retorno = jQuery.parseJSON(data)['produtos_disponiveis'];
            var retorno_hbltds = jQuery.parseJSON(data)['produtos_habilitados'];
            var div = $('#divHabilitacaoProdutosCliente');
            var html = '';
            var html_hbltds = '';

            //Lista de Produtos Disponíveis
            if(retorno.length > 0){
                div.find('#content-produtos').html('');
                    //Produtos
                    for(var i = 0; i < retorno.length; i++){
                        var modulos = '';
                        if(retorno[i].modulos){modulos = 1;}else{modulos = 0;}
                            html += '<div class="row row-habilitacao" data-toggle="collapse" data-target="#collapseModulos'+retorno[i].id+'" aria-expanded="false" aria-controls="collapseModulo'+retorno[i].id+'" rel="tooltip" data-placement="top" title="Clique para mais informações" data-form="formHabilitarProduto'+retorno[i].id+'" onclick="insertDateMask(this)">'
                                        +'<div class="col-sm-2">'+retorno[i].id+'</div>'
                                        +'<div class="col-sm-4">'+retorno[i].produto+'</div>'
                                        +'<div class="col-sm-3">'+retorno[i].aplicacao+'</div>'
                                        +'<div class="col-sm-3">'+retorno[i].versao+'</div>';
                            html += '</div>';
                                html += '<div class="collapse collapse-habilitacao" id="collapseModulos'+retorno[i].id+'">';
                                    html += '<form method="post" enctype="multipart/form-data" id="formHabilitarProduto'+retorno[i].id+'" name="formHabilitarProduto'+retorno[i].id+'" class="form-horizontal" role="" onsubmit="return false;">'
                                        html += '<input type="hidden" id="inputIdCliente" name="inputIdCliente" value="'+btoa(retorno[i].id_cliente)+'">';
                                        html += '<input type="hidden" id="inputIdProduto" name="inputIdProduto" value="'+btoa(retorno[i].id)+'">';
                                        html += '<input type="hidden" id="inputModulos" name="inputModulos" value="'+modulos+'">';
                                        html += '<div class="col-md-12"><div class="form-group">';
                                            //Período
                                            html += '<div class="col-md-5">'
                                                        +'<label for="inputHabilitacaoProdutoPeriodo" class="control-label">Período</label>'
                                                        +'<div class="input-group input-group-md">'
                                                            +'<div class="input-daterange input-group" id="datepicker">'
                                                                +'<input type="text" class="form-control" id="inputHabilitacaoProdutoInicio" name="inputHabilitacaoProdutoInicio"/>'
                                                                +'<span class="input-group-addon">Até</span>'
                                                                +'<input type="text" class="form-control" id="inputHabilitacaoProdutoFim" name="inputHabilitacaoProdutoFim"/>'
                                                            +'</div>'
                                                        +'</div>'
                                                    +'</div>';
                                            //Habilitar
                                            html += '<div class="col-md-2">'
                                                        +'<label class="control-label" for="inputProdutoHabilitar">Habilitar ?</label>'
                                                        +'<input type="checkbox" class="checkbox" id="inputProdutoHabilitar" name="inputProdutoHabilitar" value="S">'
                                                    +'</div>';
                                            //Habilitar Demo
                                            html += '<div class="col-md-3">'
                                                        +'<label class="control-label" for="inputHabilitarDemo">Demonstração(30 Dias) ?</label>'
                                                        +'<input type="checkbox" class="checkbox" id="inputHabilitarDemo" name="inputHabilitarDemo" value="S" data-form="formHabilitarProduto'+retorno[i].id+'" onclick="desabilitarPeriodo(this)">'
                                                    +'</div>';
                                             html += "<div class='col-md-2'><button type='submit' id='habilitar_produto' class='btn btn-success habilitar-produto' style='position: relative;margin-top: 7px;' data-action='insert' onclick='return habilitarProdutoContrato(\""+id_contrato+"\", this.form,this,\""+base_url+"\");'><i class='fa fa-floppy-o'></i> Salvar</button></div>";
                                            html += '<div class="hidden-sm clear-10"></div>';
                                        html += '</div></div>';

                                    if(retorno[i].modulos.length > 0){
                                        //Módulos
                                        html += '<div class="panel panel-default"><div class="panel-heading">'
                                                    +'<div class="row">'
                                                        +'<div class="col-sm-4">Módulo(s)</div>'
                                                    +'</div>'
                                                +'</div>';
                                        html += '<table id="lista-modulos" class="table table-striped table-hover">'
                                                    +'<thead>'
                                                        +'<tr>'
                                                            +'<th class="col-sm-1"><i class="fa fa-database"></i> ID</th>'
                                                            +'<th class="col-sm-4"><i class="glyphicon glyphicon-circle-arrow-down"></i> Módulo</th>'
                                                            +'<th class="col-sm-2"><i class="glyphicon glyphicon-user"></i> Usuários</th>'
                                                            +'<th class="col-sm-2"><i class="glyphicon glyphicon-ok-circle"></i> Liberado</th>'
                                                            +'<th class="col-sm-2"><i class="glyphicon glyphicon-ban-circle"></i> Bloqueado</th>'
                                                            +'<th class="col-sm-2"><i class="glyphicon glyphicon-list-alt"></i> Consulta</th>'
                                                        +'</tr>'
                                                    +'</thead>'
                                                    +'<tbody>';
                                            for(var im = 0; im < retorno[i].modulos.length; im++){
                                                html += '<input type="hidden" id="inputModuloId" name="inputModuloId['+retorno[i].modulos[im].id+']" value="'+retorno[i].modulos[im].id+'">';
                                                html += '<tr>';
                                                    html += '<td class="text-left">'+retorno[i].modulos[im].id+'</td>';
                                                    html += '<td class="text-left">'+retorno[i].modulos[im].nome+'</td>';
                                                    html += '<td class="text-left"><input type="text" id="inputHabilitacaoModuloUsu" name="inputHabilitacaoModuloUsu-'+retorno[i].modulos[im].id+'" value="'+retorno[i].modulos[im].usuarios+'" class="form-control"></td>';
                                                    html += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno[i].modulos[im].id+']" value="L"></td>';
                                                    html += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno[i].modulos[im].id+']" value="B" checked="checked"></td>';
                                                    html += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno[i].modulos[im].id+']" value="C"></td>';
                                                html += '</tr>';
                                            }
                                            html += '</tbody>';
                                        html += '</table>';
                                    }
                                    html += '</form>';
                                html += '</div>';
                        html += '</div>';
                    }
                div.find('#content-produtos').html(html);                
            }else{                
                div.find('#content-produtos').html('');
            }

            //Lista de Produtos Habilitados
            if(retorno_hbltds.length > 0){
                div.find('#content-produtos-habilitados').html('');
                    //Variáveis utilizadas
                    var demo = '';
                    var disabled = '';
                    var modulos = '';
                    var periodo_demo = '';

                    //Produtos
                    for(var i = 0; i < retorno_hbltds.length; i++){

                        //Checa se demonstração
                        if(retorno_hbltds[i].demonstracao == 'S'){
                            demo = 'checked="checked"';
                            periodo_demo = 'Sim';
                            disabled = 'disabled="disabled"';
                        }else{
                            demo = '';
                            periodo_demo = 'Não';
                            disabled = '';
                        }

                        if(retorno_hbltds[i].modulos){modulos = 1;}else{modulos = 0;}
                            
                            html_hbltds += '<div class="row row-habilitacao '+retorno_hbltds[i].vencimento+'" data-toggle="collapse" data-target="#collapseModulosHabilitado'+retorno_hbltds[i].id_habilitacao+'" aria-expanded="false" aria-controls="collapseModulosHabilitado'+retorno_hbltds[i].id_habilitacao+'" rel="tooltip" data-placement="top" title="Clique para mais informações" data-form="formHabilitarProduto'+retorno_hbltds[i].id_habilitacao+'" onclick="insertDateMask(this)">'
                                                +'<div class="col-sm-2">'+retorno_hbltds[i].id+'</div>'
                                                +'<div class="col-sm-4">'+retorno_hbltds[i].produto+'</div>'
                                                +'<div class="col-sm-2">'+retorno_hbltds[i].aplicacao+'</div>'
                                                +'<div class="col-sm-2 text-center">'+periodo_demo+'</div>'
                                                +'<div class="col-sm-2 text-center">'+retorno_hbltds[i].periodo_fim+'</div>'
                            html_hbltds += '</div>';
                            html_hbltds += '<div class="collapse collapse-habilitacao" id="collapseModulosHabilitado'+retorno_hbltds[i].id_habilitacao+'">';
                                html_hbltds += '<form method="post" enctype="multipart/form-data" id="formHabilitacao'+retorno_hbltds[i].id_habilitacao+'" name="formHabilitacao'+retorno_hbltds[i].id_habilitacao+'" class="form-horizontal" role="" onsubmit="return false;">'
                                    html_hbltds += '<input type="hidden" id="inputIdHabilitacao" name="inputIdHabilitacao" value="'+btoa(retorno_hbltds[i].id_habilitacao)+'">';
                                    html_hbltds += '<input type="hidden" id="inputIdCliente" name="inputIdCliente" value="'+btoa(retorno_hbltds[i].id_cliente)+'">';
                                    html_hbltds += '<input type="hidden" id="inputIdProduto" name="inputIdProduto" value="'+btoa(retorno_hbltds[i].id)+'">';
                                    html_hbltds += '<input type="hidden" id="inputModulos" name="inputModulos" value="'+modulos+'">';
                                    html_hbltds += '<div class="col-md-12"><div class="form-group">';
                                        //Período
                                        html_hbltds += '<div class="col-md-5">'
                                                    +'<label for="inputHabilitacaoProdutoPeriodo" class="control-label">Período</label>'
                                                    +'<div class="input-group input-group-md">'
                                                        +'<div class="input-daterange input-group" id="datepicker">'
                                                            +'<input type="text" class="form-control" id="inputHabilitacaoProdutoInicio" name="inputHabilitacaoProdutoInicio" value="'+retorno_hbltds[i].periodo_inicio+'" '+disabled+'/>'
                                                            +'<span class="input-group-addon">Até</span>'
                                                            +'<input type="text" class="form-control" id="inputHabilitacaoProdutoFim" name="inputHabilitacaoProdutoFim" value="'+retorno_hbltds[i].periodo_fim+'" '+disabled+'/>'
                                                        +'</div>'
                                                    +'</div>'
                                                +'</div>';

                                        //Habilitar Demo
                                        html_hbltds += '<div class="col-md-3">'
                                                            +'<label class="control-label" for="inputHabilitarDemo">Demonstração(30 Dias) ?</label>'
                                                            +'<input type="checkbox" class="checkbox" id="inputHabilitarDemo" name="inputHabilitarDemo" value="S" '+demo+' data-form="formHabilitacao'+retorno_hbltds[i].id_habilitacao+'" onclick="desabilitarPeriodo(this)">'
                                                        +'</div>';
                                        html_hbltds += "<div class='col-md-2 pull-right'><button type='submit' id='habilitar_produto' class='btn btn-success habilitar-produto' style='position: relative;margin-top: 7px;' data-action='update' onclick='return habilitarProdutoContrato(\""+id_contrato+"\", this.form,this,\""+base_url+"\");'><i class='fa fa-floppy-o'></i> Salvar</button></div>";
                                        if(retorno_hbltds[i].status == 'N'){
                                            html_hbltds += '<div class="col-md-2 pull-right"><button type="submit" class="btn btn-warning" style="position: relative;margin-top: 7px;" data-habilitacao="'+retorno_hbltds[i].id_habilitacao+'" data-status="S" onclick="return desabilitarProduto(this);"><i class="glyphicon glyphicon-ok-circle"></i> Reabilitar</button></div>';
                                        }else{
                                            html_hbltds += '<div class="col-md-2 pull-right"><button type="submit" class="btn btn-danger" style="position: relative;margin-top: 7px;" data-habilitacao="'+retorno_hbltds[i].id_habilitacao+'" data-status="N" onclick="return desabilitarProduto(this);"><i class="glyphicon glyphicon-ban-circle"></i> Desabilitar</button></div>';
                                        }                                        
                                        html_hbltds += '<div class="hidden-sm clear-10"></div>';
                                    html_hbltds += '</div></div>';

                                if(retorno_hbltds[i].modulos.length > 0){
                                    //Módulos
                                    html_hbltds += '<div class="panel panel-default"><div class="panel-heading">'
                                                        +'<div class="row">'
                                                            +'<div class="col-sm-4">Módulo(s)</div>'
                                                        +'</div>'
                                                    +'</div>';
                                    html_hbltds += '<table id="lista-modulos" class="table table-striped table-hover">'
                                                        +'<thead>'
                                                            +'<tr>'
                                                                +'<th class="col-sm-1"><i class="fa fa-database"></i> ID</th>'
                                                                +'<th class="col-sm-4"><i class="glyphicon glyphicon-circle-arrow-down"></i> Módulo</th>'
                                                                +'<th class="col-sm-2"><i class="glyphicon glyphicon-user"></i> Usuários</th>'
                                                                +'<th class="col-sm-2"><i class="glyphicon glyphicon-ok-circle"></i> Liberado</th>'
                                                                +'<th class="col-sm-2"><i class="glyphicon glyphicon-ban-circle"></i> Bloqueado</th>'
                                                                +'<th class="col-sm-2"><i class="glyphicon glyphicon-list-alt"></i> Consulta</th>'
                                                            +'</tr>'
                                                        +'</thead>'
                                                        +'<tbody>';
                                            for(var im = 0; im < retorno_hbltds[i].modulos.length; im++){
                                                //Verificar se já possui módulo habilitado para carregar id da habilitação
                                                var hbltc_mdl_id = '';
                                                if(retorno_hbltds[i].modulos[im].id_hbltd_mdl > 0){
                                                    hbltc_mdl_id = retorno_hbltds[i].modulos[im].id_hbltd_mdl;
                                                }
                                                //Carrega id da habilitação de acordo com o módulo
                                                html_hbltds += '<input type="hidden" id="inputHabilitacaoModuloId" name="inputHabilitacaoModuloId['+retorno_hbltds[i].modulos[im].id+']" value="'+hbltc_mdl_id+'">';
                                                html_hbltds += '<tr>';
                                                    html_hbltds += '<td class="text-left">'+retorno_hbltds[i].modulos[im].id+'</td>';
                                                    html_hbltds += '<td class="text-left">'+retorno_hbltds[i].modulos[im].nome+'</td>';
                                                    html_hbltds += '<td class="text-left"><input type="text" id="inputHabilitacaoModuloUsu" class="form-control" name="inputHabilitacaoModuloUsu-'+retorno_hbltds[i].modulos[im].id+'" class="col-sm-5" value="'+retorno_hbltds[i].modulos[im].usuarios+'" ></td>';
                                                    //Verificação de status de módulo
                                                    if(retorno_hbltds[i].modulos[im].status == 'L'){
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="L" checked="checked"></td>';
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="B"></td>';
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="C"></td>';
                                                    }else if(retorno_hbltds[i].modulos[im].status == 'B'){
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="L"></td>';
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="B" checked="checked"></td>';
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="C"></td>';
                                                    }else if(retorno_hbltds[i].modulos[im].status == 'C'){
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="L"></td>';
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="B"></td>';
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="C" checked="checked"></td>';
                                                    }else{
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="L"></td>';
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="B" checked="checked"></td>';
                                                        html_hbltds += '<td class="text-left"><input type="radio" id="inputHabilitacaoModuloStatus" name="inputHabilitacaoModuloStatus['+retorno_hbltds[i].modulos[im].id+']" value="C"></td>';
                                                    }
                                                html_hbltds += '</tr>';
                                            }
                                        html_hbltds += '</tbody>';
                                    html_hbltds += '</table>';
                                }
                                html_hbltds += '</form>';
                            html_hbltds += '</div>';
                        html_hbltds += '</div>';
                    }
                div.find('#content-produtos-habilitados').html(html_hbltds);                
            }else{                
                div.find('#content-produtos-habilitados').html('');
            }

            $(".loading").hide();
            div.show();

            if(id_habilitacao !== undefined) {
               $('*[data-target="#collapseModulosHabilitado'+id_habilitacao+'"]').click();
            }
            window.location.href="#content-produtos-habilitados";
        },
        error: function (data) {
            alert('Falha no envio, entre em contato com o administrador!');
        }
    });
}

function habilitarProdutoContrato(id_contrato, obj, acao, base_url) {
    var form = new FormData($('#'+obj.id)[0]);
    form.append('id_contrato', id_contrato);
    
    var funcao = $(acao).attr('data-action');
    var acao = "";

    if(funcao == "insert"){
        acao = 'cadastrar_habilitacao';
    }else if(funcao == "update"){
        acao = 'atualizar_habilitacao';
    }

    $.ajax({
        url: base_url+'contratos_sax/'+acao,
        type: 'POST',
        xhrFields: {
            withCredentials: true
        },
        async: true,
        cache: false,
        contentType: false,
        processData: false,
        data: form,
        beforeSend: function( xhr ) {
          $(".overlay").show();
        },
        success: function (data) {
          /*var retorno = jQuery.parseJSON(data);
          $(".overlay").hide();*/
          window.location.href = base_url+'contratos_sax/edit/'+$("#id_contrato").val();
        },
        error: function (data) {
          alert('Falha no envio, entre em contato com o administrador!');
        }
    });
}

function suspenderOportunidade(id) {
  var url = $('#base_url').val()+'oportunidades/suspender/'+id
  $(".id_oportunidade_span").html(id);
  $('#suspenderModal').modal({
      backdrop: 'static',
      keyboard: false
    })
    .one('click', '#suspenderOportunidade', function(e) {
      window.location=url;
    });
}

function excluirOportunidade(id) {
  var url = $('#base_url').val()+'oportunidades/excluir/'+id
  $(".id_oportunidade_span").html(id);
  $('#excluirModal').modal({
      backdrop: 'static',
      keyboard: false
    })
    .one('click', '#excluirOportunidade', function(e) {
      window.location=url;
    });
}

function FormataStringData(data) {
  var dia  = data.split("/")[0];
  var mes  = data.split("/")[1];
  var ano  = data.split("/")[2];

  return ano + '-' + ("0"+mes).slice(-2) + '-' + ("0"+dia).slice(-2);
}

$('#check-periodo_max_invest').change(function() {
    if(this.checked) {
      /*if($("#datetimepickerInicio").find("input").val() != "" && $("#periodo_maximo_investimento").val() != "") {
        var formatDate = new Date(FormataStringData($("#datetimepickerInicio").find("input").val()));
        var newDate = new Date(formatDate.setMonth(parseInt(formatDate.getMonth())+parseInt($("#periodo_maximo_investimento").val())));
        let date, month, year;
        date = newDate.getDate();
        month = newDate.getMonth() + 1; 
        year = newDate.getFullYear();
        if (date < 10) {
          date = '0' + date;
        }
        if (month < 10) {
          month = '0' + month;
        }
        $("#datetimepickerFim").data("DateTimePicker").date(date+"/"+month+"/"+year);

      }*/
      $("#hidden-periodo_max_invest").val("1");
      $("#datetimepickerFim").find("input").prop('disabled', true);
    } else {
      /*$("#datetimepickerFim").data("DateTimePicker").clear();*/
      $("#hidden-periodo_max_invest").val("");
      $("#datetimepickerFim").find("input").prop('disabled', false);
    }
});

$('#tipo_servico').change(function(e){
    var id_servico = $('#tipo_servico').val();
    var base_url = $('#base_url').val();

    $('#nivel').attr('disabled', true);

    var option = '';
    $.post(base_url+'chamados/get_niveis',{
        id: id_servico
    }, function(data){
      
        if(!jQuery.isEmptyObject(data.select)){
            $.each(data.select, function(i, obj){
                $('#nivel').val(obj.id);
            });
        }
      $('#nivel').attr('disabled', false);

    }, 'json');
});

function loadAudioExercicioBase64(id, prefix_url) {
  $.ajax({
    url: prefix_url+'app_exercicios/get_audio_base64',
    type: 'POST',
    data: {id:id},
    success: function(data){
      $(".modal-body").html(data);
      $('#modalAudioBase64').modal('show');
    }
  });
}