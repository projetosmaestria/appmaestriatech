<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$modoExibicao = "listagem";
if(isset($_GET["modoExibicao"]) && (@$_GET["modoExibicao"] == "listagem" || @$_GET["modoExibicao"] == "rotacao")) {
  if($_GET["modoExibicao"] == "rotacao") {
    if(!isset($_GET["bloco"])) {
      $bloco = "1";
    } else {
      if($_GET["bloco"] != "1" && $_GET["bloco"] != "2" && $_GET["bloco"] != "3" && $_GET["bloco"] != "4") {
        $bloco = "1";
      } else {
        $bloco = $_GET["bloco"];
      }
    }
  }
  $modoExibicao = $_GET["modoExibicao"];
}

$filtros = "";
if(isset($_GET["situacao_compra"]) || isset($_GET["situacao_venda"])) {
  if(isset($_GET["situacao_compra"])) {
    $filtros .= "situacao_compra=".$_GET["situacao_compra"];
  }
  if(isset($_GET["situacao_venda"])) {
    if($filtros != "")
      $filtros .= "&";
    $filtros .= "situacao_venda=".$_GET["situacao_venda"];
  }
}
?>

<section class="col-md-9">
  <nav class="navbar navbar-default">
    <div class="container-fluid">
        <ul class="nav navbar-nav">
          <li <?php echo ($modoExibicao == "listagem") ? 'class="active"' : '' ?>><a href="<?php echo base_url('dashboard/index?modoExibicao=listagem') ?>" alt="" title=""><i class="fa fa-list-ul" aria-hidden="true"></i><?php echo ($modoExibicao == "listagem") ? ' <span class="sr-only">(current)</span>' : '' ?></a></li>
          <li <?php echo ($modoExibicao == "rotacao") ? 'class="active"' : '' ?>><a href="<?php echo base_url('dashboard/index?modoExibicao=rotacao') ?>" alt="" title=""><i class="fa fa-refresh" aria-hidden="true"></i><?php echo ($modoExibicao == "rotacao") ? ' <span class="sr-only">(current)</span>' : '' ?></a></li>
        </ul>
    </div>
  </nav>
</section>

<section class="col-md-3">
  <div class="navbar navbar-default text-center">
    <?php
      if($sinc->num_rows() > 0) {
        $date1 = new DateTime($sinc->row()->ultima_sinc);
        $date2 = new DateTime(date("Y-m-d H:i:s"));
        $interval_sinc = $date1->diff($date2);

        if($interval_sinc->y == 0 && $interval_sinc->m == 0 && $interval_sinc->d == 0 && $interval_sinc->h == 0) {
          if($interval_sinc->i <= 30) {
            $status_sinc = "text-green";
          } elseif ($interval_sinc->i <= 60) {
            $status_sinc = "text-orange";
          } else {
            $status_sinc = "text-red";
          }
        } else {
          $status_sinc = "text-red";
        }
        $ultima_sinc = "Última sincronização: ".date("d/m/Y H:i", strtotime($sinc->row()->ultima_sinc));
      } else {
        $ultima_sinc = "Última sincronização: -";
        $status_sinc = "text-red";
      }
    ?>
    <div class="col-md-6"><p><?php echo $ultima_sinc ?></p><p><i class="fa fa-circle fa-lg <?php echo $status_sinc ?>"></i></p></div>
    <div class="col-md-6"><p>Próxima atualização:</p><div id="countdown"></div></div>
  </div>
</section>

<?php if($modoExibicao == "listagem" || ($modoExibicao == "rotacao" && $bloco == "1")) { ?>
<!--<section class="col-lg- --><?php //echo ($modoExibicao == "rotacao") ? '12' : '9' ?> <!-- connectedSortable">-->
<section class="col-lg-<?php echo ($modoExibicao == "rotacao") ? '12' : '12' ?> connectedSortable">
  <div class="box">
    <div class="box-header">
      <h3 class="box-title"><i class="fa fa-shopping-cart" aria-hidden="true"></i> Compras</h3>
      <div class="box-tools pull-right">
        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
        </button>
        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
      </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
      <div class="form-group">
          <?php echo lang('filtro_situacao_label', 'filtro_situacao_label', array('class' => 'col-sm-1 control-label'), false); ?>
          <div class="col-md-4 input-group">
              <?php echo form_dropdown($filtro_situacao_compra);?>
          </div>
      </div>
      <table id="datatableCompra" class="table table-striped">
        <thead>
          <tr>
            <th></th>
            <th style="width: 10px" class="col-md-1">#</th>
            <th class="col-md-2">Fornecedor</th>
            <th class="col-md-1">Total Prod</th>
            <th class="col-md-1">Comprador</th>
            <th class="col-md-1">Situação</th>
            <th class="col-md-1">Data Pedido</th>
            <th class="col-md-1">Previsão Entrega</th>
            <th class="col-md-1">Tipo</th>
            <th class="col-md-3">Progresso</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($compras->result() as $compra) { ?>
            <?php 
              $situacao = "";
              if($compra->situacao == "AI")
                $situacao = "Aprovado Integral";
              elseif($compra->situacao == "AP")
                $situacao = "Aprovado Parcial";
              elseif($compra->situacao == "EA")
                $situacao = "Em andamento";
              elseif($compra->situacao == "RE")
                $situacao = "Reprovado";
              elseif($compra->situacao == "FP")
                $situacao = "Faturado parcial";
              elseif($compra->situacao == "CA")
                $situacao = "Cancelado";
              elseif($compra->situacao == "BM")
                $situacao = "Baixa manual";
              elseif($compra->situacao == "FI")
                $situacao = "Faturado Integral";
            ?>
            <tr id="<?php echo $compra->id ?>" style="cursor:pointer;">
              <td></td>
              <td><?php echo ($compra->sku) ? $compra->sku : "-" ?></td>
              <td><?php echo $compra->nome_forn." ".$compra->sobrenome_forn ?></td>
              <td><?php echo intval($compra->total_produtos) ?></td>
              <td><?php echo $compra->nome_comp." ".$compra->sobrenome_comp ?></td>
              <td><?php echo $situacao ?></td>
              <td data-order="<?php echo strtotime($compra->data_pedido);?>"><?php echo date("d/m/Y", strtotime($compra->data_pedido)) ?></td>
              <td data-order="<?php echo strtotime($compra->data_receb." ".$compra->hora_receb);?>"><?php echo date("d/m/Y", strtotime($compra->data_receb." ".$compra->hora_receb)) ?></td>
              <?php
                $date1 = new DateTime($compra->data_pedido);
                $date2 = new DateTime($compra->data_receb);
                $interval_previsao = $date1->diff($date2);

                $date1 = new DateTime($compra->data_pedido);
                $date2 = new DateTime(date("Y-m-d"));
                $interval_atual = $date1->diff($date2);

                $percent = @($interval_atual->days / $interval_previsao->days * 100);

                // echo "<br>interval_previsao: ".$interval_previsao->days."<br>interval_atual: ".$interval_atual->days."<br>percent: ".$interval_atual->days/$interval_previsao->days*100;
              ?>
              <td><?php echo $compra->tipo_pedido_desc ?></td>
              <td>
                <div class="progress">
                  <div class="bar">
                    <?php
                      if($percent > 100) {
                        $background_color = "#dd4b39";
                      } elseif($percent == 100) {
                        $background_color = "#0073b7";
                      } else {
                        $background_color = "#00a65a";
                      }
                    ?>
                    <div class="percent" style="width: <?php echo ($percent > 100) ? '100' : $percent ?>%; background-color: <?php echo $background_color ?>">&nbsp;</div>
                    <?php
                      if($compra->data_receb < date("Y-m-d")) {
                        echo '<div class="ref" data-ref="" style="left:'.(100 - $percent / 4).'%;"></div>';
                      }                    
                    ?>
                  </div>
                </div>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <!-- /.box-body -->
    <div class="box-footer">
      <p><strong>Legenda:</strong> &nbsp; <span style="width: 1.8px;background-color:black;">|</span> Atraso previsão entrega</p>
    </div>
  </div>
  <!-- /.box -->
</section>
<?php } ?>



<?php if($modoExibicao == "listagem" || ($modoExibicao == "rotacao" && $bloco == "2")) { ?>
<section class="col-lg-<?php echo ($modoExibicao == "rotacao") ? '12' : '12' ?> connectedSortable">
  <div class="box">
    <div class="box-header">
      <h3 class="box-title"><i class="fa fa-money" aria-hidden="true"></i> Vendas</h3>
      <div class="box-tools pull-right">
        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
        </button>
        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
      </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
      <div class="form-group">
          <?php echo lang('filtro_situacao_label', 'filtro_situacao_label', array('class' => 'col-sm-1 control-label'), false); ?>
          <div class="col-md-4 input-group">
              <?php echo form_dropdown($filtro_situacao_venda);?>
          </div>
      </div>
      <table id="datatableVenda" class="table table-striped">
        <thead>
          <tr>
            <th class="col-md-1"></th>
            <th class="col-md-1">#</th>
            <th class="col-md-2">Vendedor</th>
            <!--<th class="col-md-1">Total Prod</th>-->
            <th class="col-md-2">Situação</th>
            <th class="col-md-1">Data Pedido</th>
            <th class="col-md-1">Previsão Entrega</th>
            <th class="col-md-1">Prazo Faturamento</th>
            <th class="col-md-1">Tipo</th>
            <th class="col-md-3">Progresso</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($vendas->result() as $venda) { ?>
            <?php 
              $situacao = "";
              if($venda->situacao == "AI")
                $situacao = "Aprovado Integral";
              elseif($venda->situacao == "AP")
                $situacao = "Aprovado Parcial";
              elseif($venda->situacao == "EA")
                $situacao = "Em andamento";
              elseif($venda->situacao == "RE")
                $situacao = "Reprovado";
              elseif($venda->situacao == "FP")
                $situacao = "Faturado parcial";
              elseif($venda->situacao == "CA")
                $situacao = "Cancelado";
              elseif($venda->situacao == "BA")
                $situacao = "Baixa manual";
            ?>
            <tr id="<?php echo $venda->id ?>" style="cursor:pointer;">
              <td></td>
              <td><?php echo ($venda->cod_site) ? $venda->cod_site : "-" ?></td>
              <td><?php echo $venda->first_name." ".$venda->last_name ?></td>
              <!--<td><?php //echo intval($venda->total_produtos) ?></td>-->
              <td><?php echo $situacao ?></td>
              <td data-order="<?php echo strtotime($venda->data_pedido);?>"><?php echo date("d/m/Y", strtotime($venda->data_pedido)) ?></td>
              <td data-order="<?php echo strtotime($venda->previsao_entrega." ".$venda->hora_previsao);?>"><?php echo date("d/m/Y", strtotime($venda->previsao_entrega." ".$venda->hora_previsao)) ?></td>
              <?php if($venda->data_prazo_faturamento != "" && $venda->data_prazo_faturamento != "0000-00-00") { ?>
                <td data-order="<?php echo strtotime($venda->data_prazo_faturamento);?>"><?php echo date("d/m/Y", strtotime($venda->data_prazo_faturamento)) ?></td>
              <?php } else { ?>
                <td>-</td>
              <?php } ?>
              <?php
                $date1 = new DateTime($venda->data_pedido);
                $date2 = new DateTime($venda->previsao_entrega);
                $interval_previsao = $date1->diff($date2);

                $date1 = new DateTime($venda->data_pedido);
                $date2 = new DateTime(date("Y-m-d"));
                $interval_atual = $date1->diff($date2);

                $percent_previsao_entrega = $interval_atual->days / $interval_previsao->days * 100;


                $date1 = new DateTime($venda->data_pedido);
                $date2 = new DateTime($venda->data_prazo_faturamento);
                $interval_previsao = $date1->diff($date2);

                $date1 = new DateTime($venda->data_pedido);
                $date2 = new DateTime(date("Y-m-d"));
                $interval_atual = $date1->diff($date2);

                $percent_prazo_faturamento = @($interval_atual->days / $interval_previsao->days * 100);

                // echo "<br>interval_previsao: ".$interval_previsao->days."<br>interval_atual: ".$interval_atual->days."<br>percent: ".$interval_atual->days/$interval_previsao->days*100;
              ?>
              <td><?php echo $compra->tipo_pedido_desc ?></td>
              <td>
                <div class="progress">
                  <div class="bar">
                    <?php
                      if($percent_previsao_entrega > 100) {
                        $background_color = "#dd4b39";
                      } elseif($percent_previsao_entrega == 100) {
                        $background_color = "#0073b7";
                      } else {
                        $background_color = "#00a65a";
                      }
                    ?>
                    <div class="percent" style="width: <?php echo ($percent_previsao_entrega > 100) ? '100' : $percent_previsao_entrega ?>%; background-color: <?php echo $background_color ?>">&nbsp;</div>
                    <?php
                      if($venda->previsao_entrega < date("Y-m-d")) {
                        echo '<div class="ref" data-ref="" style="left:'.(100 - $percent_previsao_entrega / 4).'%;"></div>';
                      }
                      if($venda->data_prazo_faturamento < date("Y-m-d") && ($venda->data_prazo_faturamento != "" && $venda->data_prazo_faturamento != "0000-00-00")) {
                        echo '<div class="ref-prazo-faturamento" data-ref="" style="left:'.(100 - $percent_prazo_faturamento / 4).'%;"></div>';
                      }
                    ?>
                  </div>
                </div>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <!-- /.box-body -->
    <div class="box-footer">
      <p><strong>Legenda:</strong> &nbsp; <span style="width: 1.8px;background-color:black;">|</span> Atraso previsão entrega &nbsp;&nbsp; <span style="width: 1.8px;background-color:#0073b7;">|</span> Atraso prazo faturamento</p>
    </div>
  </div>
  <!-- /.box -->
</section>
<?php } ?>

<?php if($modoExibicao == "listagem" || ($modoExibicao == "rotacao" && $bloco == "3")) { ?>
<section class="col-lg-<?php echo ($modoExibicao == "rotacao") ? '12' : '6' ?> connectedSortable">
  <div class="box">
    <div class="box-header with-border">
      <h3 class="box-title"><i class="fa fa-truck" aria-hidden="true"></i> Entregas Compra</h3>

      <div class="box-tools pull-right">
        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
        </button>
        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
      </div>
    </div>
    <div class="box-body">
    <div class="chart-responsive col-md-8 text-center">
        <canvas id="pieChart" style="height:250px"></canvas>
      </div>
      <div class="col-md-4"> 
        <!--<ul class="chart-legend clearfix"> 
          <li><i class="fa fa-circle text-green"></i> Entrega Futura</li> 
          <li><i class="fa fa-circle text-blue"></i> Entrega Hoje</li> 
          <li><i class="fa fa-circle text-red"></i> Entrega Atrasada</li> 
        </ul>-->
        <?php 
          $graficoCompra_decoded = json_decode($graficoCompra, true);
          $futura_count = 0;
          $hoje_count = 0;
          $atrasada_count = 0;
          foreach ($graficoCompra_decoded as $key => $value) {
            if($value["label"] == "Entrega Futura")
              $futura_count = $value["value"];
            elseif($value["label"] == "Entrega Hoje")
              $hoje_count = $value["value"];
            elseif($value["label"] == "Entrega Atrasada")
              $atrasada_count = $value["value"];
          }
        ?>
        <div class="info-box bg-green" style="min-height: 60px !important;">
            <span class="info-box-icon" style="height:auto !important;line-height: 60px !important;"><i class="fa fa-truck"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Entrega Futura</span>
              <span class="info-box-number"><?php echo $futura_count ?></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <div class="info-box bg-blue" style="min-height: 60px !important;">
            <span class="info-box-icon" style="height:auto !important;line-height: 60px !important;"><i class="fa fa-truck"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Entrega Hoje</span>
              <span class="info-box-number"><?php echo $hoje_count ?></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <div class="info-box bg-red" style="min-height: 60px !important;">
            <span class="info-box-icon" style="height:auto !important;line-height: 60px !important;"><i class="fa fa-truck"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Entrega Atrasada</span>
              <span class="info-box-number"><?php echo $atrasada_count ?></span>
            </div>
            <!-- /.info-box-content -->
          </div>
        <div class="info-box bg-yellow" style="min-height: 60px !important;">
            <span class="info-box-icon" style="height:auto !important;line-height: 60px !important;"><i class="fa fa-truck"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Total</span>
              <span class="info-box-number"><?php echo ($futura_count + $hoje_count + $atrasada_count) ?></span>
            </div>
            <!-- /.info-box-content -->
        </div> 
      </div>
    </div>
    <!-- /.box-body -->
  </div>
</section>
<?php } ?>

<?php if($modoExibicao == "listagem" || ($modoExibicao == "rotacao" && $bloco == "4")) { ?>
<section class="col-lg-<?php echo ($modoExibicao == "rotacao") ? '12' : '6' ?> connectedSortable">
  <div class="box">
    <div class="box-header with-border">
      <h3 class="box-title"><i class="fa fa-truck" aria-hidden="true"></i> Entregas Venda</h3>

      <div class="box-tools pull-right">
        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
        </button>
        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
      </div>
    </div>
    <div class="box-body">
    <div class="chart-responsive col-md-8 text-center">
        <canvas id="pieChart2" style="height:250px"></canvas>
      </div>
      <div class="col-md-4"> 
        <!--<ul class="chart-legend clearfix"> 
          <li><i class="fa fa-circle text-green"></i> Entrega Futura</li> 
          <li><i class="fa fa-circle text-blue"></i> Entrega Hoje</li> 
          <li><i class="fa fa-circle text-red"></i> Entrega Atrasada</li> 
        </ul>-->
        <?php 
          $graficoVenda_decoded = json_decode($graficoVenda, true);
          $futura_count = 0;
          $hoje_count = 0;
          $atrasada_count = 0;
          foreach ($graficoVenda_decoded as $key => $value) {
            if($value["label"] == "Entrega Futura")
              $futura_count = $value["value"];
            elseif($value["label"] == "Entrega Hoje")
              $hoje_count = $value["value"];
            elseif($value["label"] == "Entrega Atrasada")
              $atrasada_count = $value["value"];
          }
        ?>
        <div class="info-box bg-green" style="min-height: 60px !important;">
            <span class="info-box-icon" style="height:auto !important;line-height: 60px !important;"><i class="fa fa-truck"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Entrega Futura</span>
              <span class="info-box-number"><?php echo $futura_count ?></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <div class="info-box bg-blue" style="min-height: 60px !important;">
            <span class="info-box-icon" style="height:auto !important;line-height: 60px !important;"><i class="fa fa-truck"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Entrega Hoje</span>
              <span class="info-box-number"><?php echo $hoje_count ?></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <div class="info-box bg-red" style="min-height: 60px !important;">
            <span class="info-box-icon" style="height:auto !important;line-height: 60px !important;"><i class="fa fa-truck"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Entrega Atrasada</span>
              <span class="info-box-number"><?php echo $atrasada_count ?></span>
            </div>
            <!-- /.info-box-content -->
          </div>
        <div class="info-box bg-yellow" style="min-height: 60px !important;">
            <span class="info-box-icon" style="height:auto !important;line-height: 60px !important;"><i class="fa fa-truck"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Total</span>
              <span class="info-box-number"><?php echo ($futura_count + $hoje_count + $atrasada_count) ?></span>
            </div>
            <!-- /.info-box-content -->
        </div> 
      </div>
    </div>
    <!-- /.box-body -->
  </div>
</section>
<?php } ?>

<?php 
  //montar proxima url
  if($modoExibicao == "listagem")
    $proxima_url = base_url('dashboard/index?modoExibicao='.$modoExibicao);
  elseif ($modoExibicao == "rotacao") {
    if(($bloco == "1" || $bloco == "2" || $bloco == "3")) 
        $proxima_url = base_url('dashboard/index?modoExibicao='.$modoExibicao.'&bloco='.(intval($bloco) + 1));
    else
      $proxima_url = base_url('dashboard/index?modoExibicao='.$modoExibicao.'&bloco=1');      
  } else 
    $proxima_url = base_url('dashboard/index');
  if($filtros != "") {
    $proxima_url .= "&".$filtros;
  }
?>

<script type="text/javascript">
  window.onload = function() {
    $('[data-toggle="tooltip"]').tooltip();

    setTimeout(function(){
       window.location.href='<?php echo $proxima_url ?>';
    }, 300000);

    function addParameterToURL(url, param){
      _url = url;
      _url += (_url.split('?')[1] ? '&':'?') + param;
      return _url;
    }

    $('#filtro_situacao_compra').on('change',function(){
        var selectedValue = $(this).val();
        if('<?php echo $proxima_url ?>'.includes("situacao_compra")){
          var current_url = window.location.href;
          var url_href = current_url.replace('&situacao_compra=<?php echo @$_GET["situacao_compra"] ?>', '&situacao_compra='+selectedValue);
        }else
          var url_href = addParameterToURL(window.location.href, 'situacao_compra='+selectedValue);
        window.location.href=url_href;
    });
    $('#filtro_situacao_venda').on('change',function(){
        var selectedValue = $(this).val();
        if('<?php echo $proxima_url ?>'.includes("situacao_venda")) {
          var current_url = window.location.href;
          var url_href = current_url.replace('&situacao_venda=<?php echo @$_GET["situacao_venda"] ?>', '&situacao_venda='+selectedValue);
        }else
          var url_href = addParameterToURL(window.location.href, 'situacao_venda='+selectedValue);
        window.location.href=url_href;
    });

    <?php if($modoExibicao == "rotacao") { ?>
      $('.sidebar-toggle').click();
    <?php } ?>

    <?php if($modoExibicao == "listagem" || ($modoExibicao == "rotacao" && $bloco == "3")) { ?>
      var pieChartCanvas = $('#pieChart').get(0).getContext('2d');
      var pieChart       = new Chart(pieChartCanvas);
      var PieData        = <?php echo $graficoCompra ?>;
      var pieOptions     = {
        segmentShowStroke    : true,
        segmentStrokeColor   : '#fff',
        segmentStrokeWidth   : 2,
        percentageInnerCutout: 50, 
        animationSteps       : 100,
        animationEasing      : 'easeOutBounce',
        animateRotate        : true,
        animateScale         : false,
        responsive           : true,
        maintainAspectRatio  : true,
        legendTemplate       : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>'
      };
      pieChart.Doughnut(PieData, pieOptions);
    <?php } ?>
    <?php if($modoExibicao == "listagem" || ($modoExibicao == "rotacao" && $bloco == "4")) { ?>
      var pieChartCanvas = $('#pieChart2').get(0).getContext('2d');
      var pieChart       = new Chart(pieChartCanvas);
      var PieData        = <?php echo $graficoVenda ?>;
      var pieOptions     = {
        segmentShowStroke    : true,
        segmentStrokeColor   : '#fff',
        segmentStrokeWidth   : 2,
        percentageInnerCutout: 50, 
        animationSteps       : 100,
        animationEasing      : 'easeOutBounce',
        animateRotate        : true,
        animateScale         : false,
        responsive           : true,
        maintainAspectRatio  : true,
        legendTemplate       : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>'
      };
      pieChart.Doughnut(PieData, pieOptions);
    <?php } ?>

    <?php if($modoExibicao == "listagem" || ($modoExibicao == "rotacao" && $bloco == "1")) { ?>
      var tableCompra = $('#datatableCompra').DataTable({
        'paging'      : true,
        'lengthChange': false,
        'searching'   : true,
        'ordering'    : true,
        'info'        : true,
        'autoWidth'   : false,
        "oLanguage": {
          "sProcessing":   "Processando...",
          "sLengthMenu":   "Mostrar _MENU_ registros",
          "sZeroRecords":  "Não foram encontrados resultados",
          "sInfo":         "Mostrando de _START_ até _END_ de _TOTAL_ registros",
          "sInfoEmpty":    "Mostrando de 0 até 0 de 0 registros",
          "sInfoFiltered": "",
          "sInfoPostFix":  "",
          "sSearch":       "Buscar:",
          "sUrl":          "",
          "oPaginate": {
              "sFirst":    "Primeiro",
              "sPrevious": "Anterior",
              "sNext":     "Seguinte",
              "sLast":     "Último"
          }
        },
        "columnDefs": [ 
          { "targets": 0, "data": null, "class": "details-control", "defaultContent" : "", "orderable": false },
          { "responsivePriority": 1, targets: 1 },
          { "responsivePriority": 2, targets: 7 },
          { "responsivePriority": 3, targets: -1 }
        ],
        "order": [[ 7, 'asc' ]]
      });

      $('#datatableCompra tbody').on('click', 'tr', function () {
          var tr = $(this);
          var row = tableCompra.row( tr );
   
          if ( row.child.isShown() ) {
            row.child.hide();
            tr.removeClass('shown');
          }
          else {
            format(tr, row, 'buscar_prod_compra');
          }
      } );
    <?php } ?>

    <?php if($modoExibicao == "listagem" || ($modoExibicao == "rotacao" && $bloco == "2")) { ?>
      var tableVenda = $('#datatableVenda').DataTable({
        'paging'      : true,
        'lengthChange': false,
        'searching'   : true,
        'ordering'    : true,
        'info'        : true,
        'autoWidth'   : false,
        "oLanguage": {
          "sProcessing":   "Processando...",
          "sLengthMenu":   "Mostrar _MENU_ registros",
          "sZeroRecords":  "Não foram encontrados resultados",
          "sInfo":         "Mostrando de _START_ até _END_ de _TOTAL_ registros",
          "sInfoEmpty":    "Mostrando de 0 até 0 de 0 registros",
          "sInfoFiltered": "",
          "sInfoPostFix":  "",
          "sSearch":       "Buscar:",
          "sUrl":          "",
          "oPaginate": {
              "sFirst":    "Primeiro",
              "sPrevious": "Anterior",
              "sNext":     "Seguinte",
              "sLast":     "Último"
          }
        },
        "columnDefs": [ 
          { "targets": 0, "data": null, "class": "details-control", "defaultContent" : "", "orderable": false },
          { "responsivePriority": 1, targets: 1 },
          { "responsivePriority": 2, targets: 6 },
          { "responsivePriority": 3, targets: -1 }
        ],
        "order": [[ 5, "asc" ], [ 4, "asc" ]]
      });

      $('#datatableVenda tbody').on('click', 'tr', function () {
        var tr = $(this);
        var row = tableVenda.row( tr );
 
        if ( row.child.isShown() ) {
          row.child.hide();
          tr.removeClass('shown');
        }
        else {
          format(tr, row, 'buscar_prod_venda');
        }
      } );
    <?php } ?>

    function format ( tr, row, url ) {
      $.ajax({
        url: '<?php echo base_url() ?>dashboard/'+url,
        type: 'POST',
        data: {id:tr.attr("id")},
        beforeSend: function( xhr ) {
          row.child( 'carregando...' ).show();
          tr.addClass('shown');
        },
        success: function(data){
          var result = $.parseJSON(data);
          row.child( result.data ).show();
        }
      });
      return true;
    }

    $('#countdown').countdown({until: '+5m', format: 'YOWDHMS', significant: 2});
  };
</script>