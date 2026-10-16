<style>
.info-box-link {
  color:#000000 !important;
  text-decoration:none;
}
.unidades .btn {
  padding: 0 !important;
}
.unidades .card{
  position: relative;
  display: -ms-flexbox;
  display: flex;
  -ms-flex-direction: column;
  flex-direction: column;
  min-width: 0;
  word-wrap: break-word;
  background-color: #fff;
  background-clip: border-box;
  border: 0 solid rgba(0,0,0,.125);
  border-radius: .25rem;

  border-radius: 8px;
  border: 1px solid #cccccc;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 5px;
  box-sizing: border-box;
  width: 40px;
  height: 40px;
  transition: all linear 200ms;
}
.unidades  a .card{
  color: #333;
}

.m-1 {
  margin: .25rem !important;
}

.container-fostrap {
  display: table-cell;
  padding: 1em;
  text-align: center;
  vertical-align: middle;
}
.fostrap-logo {
  width: 100px;
  margin-bottom:15px
}
h1.heading {
  color: #fff;
  font-size: 1.15em;
  font-weight: 900;
  margin: 0 0 0.5em;
  color: #505050;
}
@media (min-width: 450px) {
  h1.heading {
    font-size: 3.55em;
  }
}
@media (min-width: 760px) {
  h1.heading {
    font-size: 3.05em;
  }
}
@media (min-width: 900px) {
  h1.heading {
    font-size: 3.25em;
    margin: 0 0 0.3em;
  }
} 
.card {
  display: block; 
    margin-bottom: 20px;
    line-height: 1.42857143;
    background-color: #fff;
    border-radius: 2px;
    box-shadow: 0 1px 3px 0 rgba(0,0,0,0.16),0 1px 5px 0 rgba(0,0,0,0.12); 
    transition: box-shadow .25s; 
}
.card:hover {
  box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 3px 10px 0 rgba(0,0,0,0.19);
}
.img-card {
  width: 100%;
  height:200px;
  border-top-left-radius:2px;
  border-top-right-radius:2px;
  display:block;
    overflow: hidden;
}
.img-card img{
  width: 100%;
  height: 200px;
  object-fit:cover; 
  transition: all .25s ease;
} 
.card-content {
  padding:15px;
  text-align:left;
}
.card-title {
  margin-top:0px;
  font-weight: 700;
  font-size: 1.65em;
}
.card-title a {
  color: #000;
  text-decoration: none !important;
}
.card-read-more {
  border-top: 1px solid #D4D4D4;
}
.card-read-more a {
  text-decoration: none !important;
  padding:10px;
  font-weight:600;
  text-transform: uppercase
}
.audios .info-box-content, .audios_exercicios .info-box-content {
  margin-left: 0px;
}
.audios .info-box, .audios_exercicios .info-box {
  min-height: 50px !important;
  box-shadow: 0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2);
  border-radius: .25rem !important;
  background-color: #fff !important;
  display: -ms-flexbox !important;
  display: flex !important;
  margin-bottom: 1rem !important;
  min-height: 50px !important;
  padding: .5rem !important;
  position: relative !important;
  width: 100% !important;
}
.audios_exercicios .info-box .info-box-content {
  font-size: 12px !important;
}
.audios .info-box .info-box-icon, .audios_exercicios .info-box .info-box-icon {
  width: 50px !important;
    height: 50px !important;
  font-size: 1.3rem !important;
  border-radius: .25rem !important;
  -ms-flex-align: center !important;
  align-items: center !important;
  display: -ms-flexbox !important;
  display: flex !important;
  font-size: 1.875rem !important;
  -ms-flex-pack: center !important;
  justify-content: center !important;
  text-align: center !important;
  width: 50px !important;
}
 #overlay {
  position: fixed; /* Sit on top of the page content */
  display: none; /* Hidden by default */
  width: 100%; /* Full width (cover the whole page) */
  height: 100%; /* Full height (cover the whole page) */
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0,0,0,0.5); /* Black background with opacity */
  z-index: 2; /* Specify a stack order in case you're using a different order for other elements */
  cursor: pointer; /* Add a pointer on hover */
}
#overlay i {
  position: absolute;
  top: 50%;
  left: 50%;
  font-size: 50px;
  transform: translate(-50%,-50%);
  -ms-transform: translate(-50%,-50%);
}
</style>
<div class="container-fluid">

    <div id="overlay">
      <i class="fa fa-refresh fa-spin"></i>
    </div>

  <input type="hidden" name="base_url" id="base_url" value="<?= base_url() ?>">

  <!-- Info boxes -->
  <div class="row">
    <div class="col-sm-12">
      <!-- select -->
      <div class="form-group">
          <label for="curso">Curso:</label>
          <form method="get" name="form-curso">
            <select id="curso" class="form-control" name="curso" onchange="this.form.submit()" disabled>
              <option value="">Carregando cursos...</option>
            </select>
          </form>
      </div>
    </div>
  </div>
  <div class="row">

    <?php foreach($livros as $livro) { ?>
      <div class="col-12 col-lg-3 col-md-4 col-sm-3">
        <a href="javascript:void(0);" class="info-box-link" data-id="<?= $livro->id ?>">
          <div class="info-box" style="opacity: 0.5;">
            <span class="info-box-icon elevation-1" style="background-color: <?= $livro->cor ?> !important"><i class="fa fa-book"></i></span>

            <div class="info-box-content">
              <span class="info-box-text"><b><?= $livro->titulo ?></b></span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </a>
      </div>
    <?php } ?>
    
  </div>
  <!-- /.row -->

  <div class="d-flex flex-wrap align-items-center unidades mb-10" style="margin-bottom: 1rem; display: flex !important; align-items: center !important;  flex-wrap: wrap !important;">
    <a href="#" class="btn disabled"><div class="m-1 card">1</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">2</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">3</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">4</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">5</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">6</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">7</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">8</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">9</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">10</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">11</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">12</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">13</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">14</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">15</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">16</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">17</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">18</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">19</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">20</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">21</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">22</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">23</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">24</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">25</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">26</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">27</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">28</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">29</div></a>
    <a href="#" class="btn disabled"><div class="m-1 card">30</div></a>
  </div>

  <div class="row videos">
    
  </div>

  <div class="row audios">
    
  </div>

  <div class="row audios_exercicios">
    
  </div>
  <!-- <div class="col-xs-12"> -->
    <div class="materiais col-xs-6"></div>

    <div class="materiais_unidade col-xs-6"></div>
  <!-- </div> -->

  <hr>
  
</div><!--/. container-fluid -->

<div class="modal fade" id="modalAudioBase64" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Ouvir Áudio</h4>                        
            </div><!--/modal-header-->
            <div class="modal-body">
                
            </div><!--/.modal-body-->
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Fechar</button>
            </div><!--/.modal-footer-->
        </div><!--/.modal-content-->
    </div><!--/.modal-dialog-->
</div><!--/.modal-->

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const cursoSelect = document.getElementById('curso');

    fetch($("#base_url").val()+'/dashboard/get_curso')
      .then(response => {
        if (!response.ok) {
          throw new Error('Falha ao buscar cursos');
        }
        return response.json();
      })
      .then(data => {
        cursoSelect.innerHTML = '<option value="">Selecione um curso</option>';

        const todosOption = document.createElement('option');
        todosOption.value = "";
        todosOption.textContent = "Todos";
        cursoSelect.appendChild(todosOption);

        if (data.data.length) {
          data.data.forEach(curso => {
            const option = document.createElement('option');
            option.value = curso.ID_CURSO;
            option.textContent = curso.DESCRICAO;
            cursoSelect.appendChild(option);
          });
          cursoSelect.disabled = false;
        } else {
          cursoSelect.innerHTML = '<option value="">Nenhum curso disponível</option>';
          cursoSelect.disabled = true;
        }
      })
      .catch(error => {
        console.error('Erro:', error);
        cursoSelect.innerHTML = '<option value="">Erro ao carregar cursos</option>';
        cursoSelect.disabled = true;
      });
  });
</script>