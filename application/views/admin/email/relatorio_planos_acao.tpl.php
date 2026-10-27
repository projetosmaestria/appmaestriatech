<html>
<head>
   <!--[if (gte mso 9)|(IE)]>
  <style type="text/css">
    li {
        text-indent: -3em;
    }
  </style>
  <![endif]-->
  <style>
    body {
        font-family: 'Source Sans Pro','Helvetica Neue',Helvetica,Arial,sans-serif;
        font-weight: 400;
        overflow-x: hidden;
        overflow-y: auto;
        font-size: 12px;
        line-height: 1.42857143;
        color: #333;
        margin: 0;
    }
    html, body {
        height: 100%;
    }
    .col-xs-12 {
        width: 100%;
    }
    .col-xs-1, .col-xs-10, .col-xs-11, .col-xs-12, .col-xs-2, .col-xs-3, .col-xs-4, .col-xs-5, .col-xs-6, .col-xs-7, .col-xs-8, .col-xs-9 {
        float: left;
    }
    .col-lg-1, .col-lg-10, .col-lg-11, .col-lg-12, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8, .col-lg-9, .col-md-1, .col-md-10, .col-md-11, .col-md-12, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-sm-1, .col-sm-10, .col-sm-11, .col-sm-12, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-xs-1, .col-xs-10, .col-xs-11, .col-xs-12, .col-xs-2, .col-xs-3, .col-xs-4, .col-xs-5, .col-xs-6, .col-xs-7, .col-xs-8, .col-xs-9 {
        position: relative;
        min-height: 1px;
        padding-right: 15px;
        padding-left: 15px;
    }
  </style>
</head>
<body>
  <div class="content-wrapper">
    <div class="row">
      <div class="col-xs-12">
        <h2 style="font-family: 'Source Sans Pro',sans-serif;margin: 10px 0 20px 0;font-size: 22px;">
          <i class="fa fa-list"></i> Projetos em aberto
        </h2>
      </div>
    </div>

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

                echo ($plano->tipo == "I") ? "<h4 style='font-family: \"Source Sans Pro\",sans-serif;margin-top: 10px;margin-bottom: 10px;font-size: 18px;'>Implantação</h4>" : "<h4 style='font-family: \"Source Sans Pro\",sans-serif;margin-top: 10px;margin-bottom: 10px;font-size: 18px;'>Desenvolvimento</h4>";
              }

              echo "<ul style='list-style-type: none;'>";
              echo '<li style="">
                <table style="width: 750pt;" width="750" cellspacing="0" cellpadding="0" border="0">
                  <tr>
                    <td width="40%" style="padding: 0cm 0cm 11.25pt;"><p class="MsoNormal" style="line-height: 21pt;"><span style="font-size: 13.5pt; font-family: arial, sans-serif; color: rgb(83, 83, 83);">'.$plano->desc.'</span></p></td>
                    <td width="20%" style="padding: 0cm 0cm 11.25pt;"><p class="MsoNormal" style="line-height: 21pt;"><span style="font-size: 13.5pt; font-family: arial, sans-serif; color: rgb(83, 83, 83);">'.$plano->horas_prevista.'h (prevista(s))</span></p></td>
                    <td width="20%" style="padding: 0cm 0cm 11.25pt;"><p class="MsoNormal" style="line-height: 21pt;"><span style="font-size: 13.5pt; font-family: arial, sans-serif; color: rgb(83, 83, 83);">'.$plano->horas_utilizadas.'h (utilizada(s))</span></p></td>
                    <td width="20%" style="padding: 0cm 0cm 11.25pt;"><p class="MsoNormal" style="line-height: 21pt;"><span style="font-size: 13.5pt; font-family: arial, sans-serif; color: rgb(83, 83, 83);">'.$plano->horas_utilizadas.'h (concluída(s))</span></p></td>
                  </tr>
                </table>';

                if(count($plano->tarefas) > 0) {
                  echo "<ol>";
                  echo '<table style="width: 750pt;" width="750" cellspacing="0" cellpadding="0" border="0">';

                    foreach($plano->tarefas as $tarefa) {
                      echo '<tr>
                        <td width="100%" style="padding: 0cm 0cm 11.25pt;"><p class="MsoNormal" style="line-height: 5pt;"><span style="font-size: 10pt; font-family: arial, sans-serif; color: rgb(83, 83, 83);">#'.$tarefa->id.' - '.$tarefa->titulo.'</span></p></td>
                      </tr>';
                    }

                  echo "</table>";
                  echo "</ol>";
                }

              echo "</li>";
              echo "</ul>";

              $ult_tipo = $plano->tipo;
            }
          } else {
            echo "<p>Não existem projetos em aberto.</p>";
          }
        ?>
      </div>
    </div>

    

    <div class="row">
      <div class="col-xs-12">
        <br><hr><br>
        <h2 style="font-family: 'Source Sans Pro',sans-serif;margin: 10px 0 20px 0;font-size: 22px;">
          <i class="fa fa-list"></i> Projetos finalizados no mês
        </h2>
      </div>
    </div>

    <div class="row">
      <div class="col-xs-12">
        <?php

          if(count($planos_finalizados) > 0) { 
            $ult_tipo = "";
            foreach($planos_finalizados as $plano) {
              
              if($plano->tipo != $ult_tipo) {
                if($ult_tipo != "")
                  echo "<br>";

                echo ($plano->tipo == "I") ? "<h4 style='font-family: \"Source Sans Pro\",sans-serif;margin-top: 10px;margin-bottom: 10px;font-size: 18px;'>Implantação</h4>" : "<h4 style='font-family: \"Source Sans Pro\",sans-serif;margin-top: 10px;margin-bottom: 10px;font-size: 18px;'>Desenvolvimento</h4>";
              }

              echo "<ul style='list-style-type: none;'>";
              echo '<li style="">
                <table style="width: 750pt;" width="750" cellspacing="0" cellpadding="0" border="0">
                  <tr>
                    <td width="40%" style="padding: 0cm 0cm 11.25pt;"><p class="MsoNormal" style="line-height: 21pt;"><span style="font-size: 13.5pt; font-family: arial, sans-serif; color: rgb(83, 83, 83);">'.$plano->desc.'</span></p></td>
                    <td width="20%" style="padding: 0cm 0cm 11.25pt;"><p class="MsoNormal" style="line-height: 21pt;"><span style="font-size: 13.5pt; font-family: arial, sans-serif; color: rgb(83, 83, 83);">'.$plano->horas_prevista.'h (prevista(s))</span></p></td>
                    <td width="20%" style="padding: 0cm 0cm 11.25pt;"><p class="MsoNormal" style="line-height: 21pt;"><span style="font-size: 13.5pt; font-family: arial, sans-serif; color: rgb(83, 83, 83);">'.$plano->horas_utilizadas.'h (utilizada(s))</span></p></td>
                    <td width="20%" style="padding: 0cm 0cm 11.25pt;"><p class="MsoNormal" style="line-height: 21pt;"><span style="font-size: 13.5pt; font-family: arial, sans-serif; color: rgb(83, 83, 83);">'.$plano->horas_utilizadas.'h (concluída(s))</span></p></td>
                  </tr>
                </table>';

              echo "</li>";
              echo "</ul>";

              $ult_tipo = $plano->tipo;
            }
          } else {
            echo "<p>Não existem projetos finalizados.</p>";
          }
        ?>
      </div>
    </div>
  </div>
</body>
</html>