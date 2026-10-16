<script type="text/javascript">
    window.onload = function() {

        $('#colecao').on('change', function() {
            document.forms["form-colecao"].submit();
        });

        $(".info-box-link").click(function() {
            var btn = $(this);
            $('.unidades').empty();
            $('.videos').empty();
            $('.audios').empty();
            $('.materiais').empty();
            $('.materiais_unidade').empty();
            $.ajax({
                    url: $("#base_url").val() + 'dashboard/get_unidades_livro',
                    type: 'GET',
                    data: {id:$(this).data('id')},
                    beforeSend: function( xhr ) {
                        $("#overlay").show();
                        $('.info-box-link .info-box').css('opacity', '0.5');
                    },
                    success: function(data){
                        btn.find('.info-box').css('opacity', '1');
                        var result = $.parseJSON(data);
                        if(result.erro == false) {
                            var html_unidades = '';
                            
                            $.each(result.data.unids, function(key, item) {
                                
                                html_unidades += '<a href="javascript:void(0)" class="btn unidade" data-id="'+item.id+'"><div class="m-1 card">'+item.unidade+'</div></a>';
                                
                                /*if(item.video_yt.length > 0) {
                                    $.each(item.video_yt, function(k, video) {
                                        html_videos += '<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">'+
                                            '<div class="card">'+
                                                '<a class="img-card" href="'+video.url+'" target="_blank">'+
                                                    '<img src="'+video.thumb+'" />'+
                                                '</a>'+
                                                '<div class="card-content">'+
                                                    '<p class="">'+video.titulo+'</p>'+
                                                '</div>'+
                                                '<div class="card-read-more">'+
                                                    '<a href="'+video.url+'" class="btn btn-link btn-block" target="_blank">Ver vídeo</a>'+
                                                '</div>'+
                                            '</div>'+
                                        '</div>';
                                    });
                                }*/
                            });

                            $('.unidades').html(html_unidades);

                        }
                        $("#overlay").hide();
                    }
            });
        });
        
        $(".unidades").on('click', '.unidade', function(){
            $.ajax({
                    url: $("#base_url").val() + 'dashboard/get_videos_unidade',
                    type: 'GET',
                    data: {id:$(this).data('id')},
                    beforeSend: function( xhr ) {
                        $("#overlay").show();
                    },
                    success: function(data){
                        $(this).children('.info-box').css('opacity:0');
                        var result = $.parseJSON(data);
                        if(result.erro == false) {
                            var html_videos = '';
                            var html_audios = '<div class="col-xs-12"><h4>Áudios das Unidades '+result.data.unid.unidade+'</h4></div>';
                            var html_audios_exercicios = '<div class="col-xs-12"><h4>Áudios dos Exercícios</h4></div>';
                            var html_materiais = '<div><h4>Materiais (Livros)</h4><table id="datatable" class="table table-striped table-hover"><thead style="font-weight: bold;"><tr><th>Título</th><th>Livro</th><th>Data de Criação</th><th>Ações</th></tr></thead><tbody>';
                            var html_materiais_unidade = '<div><h4>Materiais (Unidade)</h4><table id="datatable" class="table table-striped table-hover"><thead style="font-weight: bold;"><tr><th>Título</th><th>Unidade</th><th>Data de Criação</th><th>Ações</th></tr></thead><tbody>';
                            
                            if(result.data.unid.video_yt.length > 0) {
                                $.each(result.data.unid.video_yt, function(k, video) {
                                    html_videos += '<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">'+
                                        '<div class="card">'+
                                            '<a class="img-card" href="'+video.url+'" target="_blank">'+
                                                '<img src="'+video.thumb+'" />'+
                                            '</a>'+
                                            '<div class="card-content">'+
                                                '<p class="">'+video.titulo+'</p>'+
                                            '</div>'+
                                            '<div class="card-read-more">'+
                                                '<a href="'+video.url+'" class="btn btn-link btn-block" target="_blank">Ver vídeo</a>'+
                                            '</div>'+
                                        '</div>'+
                                    '</div>';
                                });
                            }

                            $.each(result.data.audios, function(key, item) {
                                var str_titulo = item.titulo;
                                if (str_titulo.length > 20)
                                    str_titulo = str_titulo.substr(0, 17) + '...';
                                
                                html_audios += '<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">'+
                                    "<div style='cursor:pointer;' class='info-box' onclick='loadAudioBase64(\""+item.id+"\", \""+$("#base_url").val()+"\")'>"+
                                        '<span class="info-box-icon bg-info elevation-1"><i class="fa fa-headphones"></i></span>'+
                                        '<div class="info-box-content">'+
                                            '<span class="info-box-text">'+str_titulo+'</span>'+
                                            '<small>Unidade '+item.num_unidade+'</small>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>';
                            });
                            console.log(result.data);
                            $.each(result.data.audios_exercicios, function(key, item) {
                                console.log(item);
                                var str_titulo = item.titulo_exercicio;
                                if (str_titulo.length > 20)
                                    str_titulo = str_titulo.substr(0, 17) + '...';
                                
                                html_audios_exercicios += '<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">'+
                                    "<div style='cursor:pointer;' class='info-box' onclick='loadAudioExercicioBase64(\""+item.id+"\", \""+$("#base_url").val()+"\")'>"+
                                        '<span class="info-box-icon bg-info elevation-1"><i class="fa fa-headphones"></i></span>'+
                                        '<div class="info-box-content">'+
                                            '<span class="info-box-text">'+str_titulo+'</span>'+
                                            '<small>Unidade '+item.num_unidade+' - Ordem '+item.ordem_exercicio+'</small>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>';
                            });

                            $.each(result.data.materiais, function(key, item) {
                                var str_titulo = item.titulo || '';
                                if (str_titulo.length > 20)
                                    str_titulo = str_titulo.substr(0, 17) + '...';
                                
                                var titulo_livro = item.titulo_livro && item.titulo_livro !== "null" ? item.titulo_livro : '';

                                html_materiais +=
                                                '<tr>'+
                                                    '<td>'+str_titulo+'</td>'+
                                                    '<td>'+titulo_livro+'</td>'+
                                                    '<td>'+item.data_criacao+'</td>'+
                                                    
                                                    '<td><a href="'+ $("#base_url").val() + 'upload/materiais/' + item.caminho + '" target="_blank" class="btn btn-primary btn-xs" rel="tooltip" data-placement="top" title="Download do anexo" download><i class="fa fa-floppy-o"></i> Download</a></td>'+    
                                                '</tr>';
                            });

                            

                            $.each(result.data.materiais, function(key, item) {
                                var str_titulo = item.titulo || '';
                                if (str_titulo.length > 20)
                                    str_titulo = str_titulo.substr(0, 17) + '...';
                                
                                var titulo_unidade = item.titulo_unidade && item.titulo_unidade !== "null" ? item.titulo_unidade : '';

                                html_materiais_unidade +=
                                                '<tr>'+
                                                    '<td>'+str_titulo+'</td>'+
                                                    '<td>'+titulo_unidade+'</td>'+
                                                    '<td>'+item.data_criacao+'</td>'+
                                                    '<td><a href="' + $("#base_url").val() + 'upload/materiais/' + item.caminho + '" target="_blank" class="btn btn-primary btn-xs" rel="tooltip" data-placement="top" title="Download do anexo" download><i class="fa fa-floppy-o"></i> Download</a></td>' +
                                                '</tr>';
                            });

                            html_materiais += '</tbody></table></div>';
                            html_materiais_unidade += '</tbody></table></div>';

                            $('.videos').html(html_videos);
                            $('.audios').html(html_audios);
                            $('.audios_exercicios').html(html_audios_exercicios);
                            $('.materiais').html(html_materiais);
                            $('.materiais_unidade').html(html_materiais_unidade);

                        }
                        $("#overlay").hide();
                    }
            });  
        });

    };
</script>