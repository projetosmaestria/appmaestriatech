<!DOCTYPE html>
<html>
<head>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta charset="utf-8">
    <title></title>
    <link rel="stylesheet" href="<?php echo base_url('assets/frameworks/bootstrap/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/frameworks/adminlte/css/adminlte.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/frameworks/adminlte/css/skins/skin-blue.min.css'); ?>">
    <style>
      ul {
        list-style-type: none;
      }
      .tarefa-titulo {
        font-size: 9px;
      }
    </style>
</head>
<body>

  <div class="content-wrapper">
      <section class="content">
          <div class="row">
            <div class="col-xs-12">
              <h2 class="page-header">
                <i class="fa fa-list"></i> Planos de Ação
              </h2>
            </div>
            <!-- /.col -->
          </div>
          <!-- info row -->

          <!-- Table row -->
          <div class="row">
            <div class="col-xs-12">
              <?php 
                if(count($planos) > 0) { 
                  $ult_tipo = "";
                  foreach($planos as $plano) {
                    
                    if($plano->tipo != $ult_tipo) {
                      if($ult_tipo != "")
                        echo "<br>";

                      echo ($plano->tipo == "I") ? "<h4>Implantação</h4>" : "<h4>Desenvolvimento</h4>";
                    }

                    echo "<ul>";
                    echo "<li>
                      <table class='table'>
                        <tr>
                          <td width='40%'>".$plano->desc."</td>
                          <td width='20%'>".$plano->horas_prevista."h (prevista(s))</td>
                          <td width='20%'>".$plano->horas_utilizadas."h (utilizada(s))</td>
                          <td width='20%'>".$plano->horas_utilizadas."h (concluída(s))</td>
                        </tr>
                      </table>";

                      if(count($plano->tarefas) > 0) {
                        echo "<ol>";
                        echo "<table class='table'>";

                          foreach($plano->tarefas as $tarefa) {
                            echo "<tr>
                              <td><span >".$tarefa->titulo."</span></td>
                            </tr>";
                          }

                        echo "</table>";
                        echo "</ol>";
                      }

                    echo "</li>";
                    echo "</ul>";

                    $ult_tipo = $plano->tipo;
                  }
                } else {
                  echo "<p>Não existe(m) plano(s) de ação.</p>";
                }

              ?>
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
      </section>
  </div>
</body>
<?php //exit; ?>