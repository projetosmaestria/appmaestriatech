<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<section class="col-lg-12 connectedSortable">
    <div class="box box-primary">
        <div class="box-header with-border">
            <i class="fa fa-bar-chart"></i>
            <h3 class="box-title">Informações para Franqueadora</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-cubes"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Unidades Ativas</span>
                        <span class="info-box-number"><?php echo $unidades ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-user"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Alunos</span>
                        <span class="info-box-number"><?php echo $alunos ?></span>
                    </div>
                </div>
            </div>

            <div class="clearfix visible-sm-block"></div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-maroon"><i class="fa fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Qtd alunos responderam na semana</span>
                        <span class="info-box-number"><?php echo $respostas_alunos ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-check-square-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Qtd respostas semana</span>
                        <span class="info-box-number"><?php echo $respostas ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>