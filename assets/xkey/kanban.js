var myTimer = 0;
$(document).ready(function(){

	$(".detalhesTarefa").click(function() {
      	
		$('html, body').css("cursor", "wait");
      	$(".loading").show();

      	var tarefa_id = $(this).attr('data-id');

      	$.ajax({
		    url: $('#base_url').val()+'kanban/q_geral',
		    type: 'POST',
		    data: { id : tarefa_id },
		    success: function(data){
		    	if(data != false) {

			      	var result = $.parseJSON(data);

			      	if(result.erro == true) {
		      			$('html, body').css("cursor", "auto");
			      		alert(result.data);
			      		return false;
			      	} else {
			      		var tarefa = result.data.tarefa;

			      		$('.add_resp_dialogo').attr('onclick', "add_resposta(1,'"+tarefa_id+"')");
			      		$('.add_resp_anexo').attr('onclick', "add_resposta(2,'"+tarefa_id+"')");
			      		$('#status').attr('onchange', "atualizar_status('"+tarefa_id+"', this.value)");
			      		var status_html = '';
			      		$.each(result.data.status_options, function(k, i) {
			      			status_html += '<option value="'+k+'"';
			      			if(tarefa.status_id == k)
			      				status_html += ' selected';
			      			status_html += '>'+i+'</option>';
			      		});
			      		$('#status').html(status_html);

			      		$('#prioridade').attr('onchange', "atualizar_prioridade('"+tarefa_id+"', this.value)");
			      		var prioridade_html = '';
			      		$.each(result.data.prioridade_options, function(k, i) {
			      			prioridade_html += '<option value="'+k+'"';
			      			if(tarefa.prioridade_id == k)
			      				prioridade_html += ' selected';
			      			prioridade_html += '>'+i+'</option>';
			      		});
			      		$('#prioridade').html(prioridade_html);

			      		$("#chamado_id").val(tarefa_id);

			      		var progressBar = $('.progress-bar');
			      		$('.msgs_apont').html("");
			      		if(tarefa.apontamento_ativo.qtd > 0) {

                      		$('#apontamento_id').val(tarefa.apontamento_ativo.id);

				      		$(".left-myday").html('<a class="btn btn-app" onclick="stopApontamento()">'+
	                                '<i class="fa fa-stop"></i> Parar'+
	                            '</a>');

				      		$(".inicio-apontamento-span").html(tarefa.apontamento_ativo.hora_inicio);

    						$('.duration-clock').countimer({
				                initHours : tarefa.apontamento_ativo.horas,
				                initMinutes : tarefa.apontamento_ativo.minutos,
				                initSeconds: tarefa.apontamento_ativo.segundos
				            });
				            var percentVal = parseInt(tarefa.apontamento_ativo.segundos);
				            myTimer = window.setInterval(function(){
				                percentVal += 1;
				                progressBar.css("width", ((percentVal / 60) * 100) + '%').attr("aria-valuenow", ((percentVal / 60) * 100) + '%');

				                if (percentVal == 60)
				                {
				                    percentVal = 0;
				                }
				            }, 1000);
					    } else {
					    	var can_apontar = '';
					    	if(tarefa.has_outro_apontamento.confirm == true) {
					    		can_apontar = 'disabled="disabled"';
					    		$('.msgs_apont').html('<p class="text-center">Você possui um apontamento aberto, <a target="_blank" href="'+$("#base_url").val()+'/chamados/details/'+tarefa.has_outro_apontamento.id+'" alt="Chamado '+tarefa.has_outro_apontamento.id+'" title="Chamado '+tarefa.has_outro_apontamento.id+'">acesse aqui o chamado #'+tarefa.has_outro_apontamento.id+'</a> para para-lo antes de continuar.</p>');
					    	}  else {
					    		can_apontar = 'onclick="startApontamento()"';
					    	}

					    	$(".left-myday").html('<a class="btn btn-app" '+can_apontar+'>'+
						            '<i class="fa fa-play"></i> Iniciar'+
						        '</a>');

					    	if(myTimer != 0) {
    							window.clearInterval(myTimer);
    							myTimer = 0;
					    		$('.duration-clock').countimer('destroy');
					    	}

						    progressBar.css("width", '0%').attr("aria-valuenow", '0%');
						    $('.duration-clock').html("00:00:00");
							$('.inicio-apontamento').hide();
						    
					    }

			      		$(".modal-title").html('<a href="'+$('#base_url').val()+'chamados/details/'+tarefa_id+'" target="_blank">'+tarefa.titulo+'</a>');

			      		if(tarefa.dono == null)
			      			tarefa.dono = "-";

			      		$(".modal-body .criada_em").html("#"+tarefa.id+" Criada por: "+tarefa.dono+" em "+tarefa.criada_em);
			      		$(".modal-body #desc p").html(tarefa.descricao);

			      		var html_anexo = "";
			      		var last_data = "";
			      		var anexo_count = 0;
			      		$.each(tarefa.anexo, function(key, item) {
			      			var data_hora = item.criada_em;
			      			var explode_data_hora = data_hora.split(" ");

			      			if(last_data != explode_data_hora[0]) {
			      				last_data = explode_data_hora[0];

			      				html_anexo += '<li class="time-label">'+
				                    '<span class="bg-blue">'+
				                      explode_data_hora[0]+
				                    '</span>'+
			                  	'</li>';
			      			}

			      			if(item.responsavel == null)
			      				item.responsavel = "-";
					        html_anexo += '<li>'+
				                    '<i class="fa fa-file-text bg-purple"></i>'+

				                    '<div class="timeline-item">'+
				                      	'<span class="time"><i class="fa fa-clock-o"></i> '+explode_data_hora[1]+'</span>'+
				                      	'<h3 class="timeline-header"><a href="#">'+item.responsavel+'</a> enviou um anexo</h3>'+

					                    '<div class="timeline-body">'+
					                        item.descricao+
					                    '</div>'+
				                      	'<div class="timeline-footer">'+
					                        '<a href="'+$('#base_url').val()+'upload/anexo/'+item.anexo+'" target="_blank" class="btn btn-primary btn-xs" rel="tooltip" data-placement="top" title="Download do anexo" download><i class="fa fa-floppy-o"></i> Download</a>'+
					                    '</div>'+
				                    '</div>'+
				                '</li>';
				            anexo_count++;
					    });
					    if(anexo_count > 0) {
					    	html_anexo += '<li><i class="fa fa-clock-o bg-gray"></i></li>';
					    }
					    $(".modal-body #anexo .timeline-anexo").html(html_anexo);

			      		var html_hist = "";
			      		var last_data = "";
			      		var hist_count = 0;
			      		$.each(tarefa.historico, function(key, item) {
			      			var data_hora = item.criada_em;
			      			var explode_data_hora = data_hora.split(" ");

			      			if(last_data != explode_data_hora[0]) {
			      				last_data = explode_data_hora[0];

			      				html_hist += '<li class="time-label">'+
				                    '<span class="bg-blue">'+
				                      explode_data_hora[0]+
				                    '</span>'+
			                  	'</li>';
			      			}

			      			if(item.responsavel == null)
			      				item.responsavel = "-";
					        html_hist += '<li>'+
				                    '<i class="fa fa-comments bg-aqua"></i>'+

				                    '<div class="timeline-item">'+
				                      	'<span class="time"><i class="fa fa-clock-o"></i> '+explode_data_hora[1]+'</span>'+
				                      	'<h3 class="timeline-header"><a href="#">'+item.responsavel+'</a> status <strong>'+item.status_desc+'</strong></h3>'+

					                    '<div class="timeline-body">'+
					                        item.obs+
					                    '</div>'+
				                    '</div>'+
				                '</li>';
				            hist_count++;
					    });
					    if(hist_count > 0) {
					    	html_hist += '<li><i class="fa fa-clock-o bg-gray"></i></li>';
					    }
					    $(".modal-body #hist .timeline-hist").html(html_hist);

					    if(tarefa.data_prazo == null)
			      			tarefa.data_prazo = "-";
			      		if(tarefa.data_inicio == null)
			      			tarefa.data_inicio = "-";
			      		if(tarefa.data_finalizacao == null)
			      			tarefa.data_finalizacao = "-";
			      		if(tarefa.plano_acao_desc == null)
			      			tarefa.plano_acao_desc = "-";
					    $(".previsao").html(tarefa.data_prazo);
					    $(".inicio").html(tarefa.data_inicio);
					    $(".finalizacao").html(tarefa.data_finalizacao);
					    $(".plano_acao").html(tarefa.plano_acao_desc);

					    $(".esforco").html(tarefa.esforco+"h");

					    $('.nav-tabs a[href="#desc"]').tab('show');

		      			$("#detalhesTarefaModal").modal('show');
		      			$('html, body').css("cursor", "auto");
      				}
      			}
		    }
		});

  	});

	var tarefa_dragged = $(".connectedSortable").sortable({
	    stop: function(ui, event){
	        
	        $('html, body').css("cursor", "wait");
	      	$(".loading").show();
	      	
	        var quadro = $("#quadro_id").val();
	        var tarefa = event.item.attr('data-tarefa');
	        var coluna = event.item.parent().attr('data-coluna');
	        var pos = event.item.index();

	      	$.ajax({
			    url: $('#base_url').val()+'kanban/alterar_status_tarefa',
			    type: 'POST',
			    data: { quadro : quadro, coluna : coluna, tarefa : tarefa, pos : pos, usuario : $('input[name="usuario['+quadro+']"]').val() },
			    success: function(data){
			    	if(data != false) {

				      	var result = $.parseJSON(data);

				      	if(result.erro == true) {
			      			$('html, body').css("cursor", "auto");
				      		alert(result.data);
				      		return false;
				      	} else {

			      			$('html, body').css("cursor", "auto");
	      				}
	      			} else {
	      				$('html, body').css("cursor", "auto");
	      				tarefa_dragged.sortable('cancel');
	      				alert("Houve um problema ao tentar alterar o status da tarefa.");
			      		return false;
	      			}
			    }
			});
	    }
	});

	$('#detalhesTarefaModal').on('hidden.bs.modal', function () {
	   	
	   	var progressBar = $('.progress-bar');
	   	progressBar.css("width", '0%').attr("aria-valuenow", '0%');
		
		if(myTimer != 0) {
			clearInterval(myTimer);
			myTimer = 0;

			if($('.duration-clock').html() != "00:00:00") {
				$('.duration-clock').countimer('destroy');
				$('.inicio-apontamento').hide();
			}
		}
	});

});

function atualizar_status(chamado_id, status) {
	$('.overlay').show();

    $.ajax({
	    url: $('#base_url').val()+'chamados/status',
	    type: 'POST',
	    data: { chamado_id: chamado_id, status: status },
	    success: function(data){
	    	window.location.reload();
	    }
	});
}

function atualizar_prioridade(chamado_id, prioridade) {
	$('.overlay').show();

	$.ajax({
	    url: $('#base_url').val()+'chamados/alterar_prioridade',
	    type: 'POST',
	    data: { chamado_id: chamado_id, prioridade: prioridade },
	    success: function(data){
	    	window.location.reload();
	    }
	});

}