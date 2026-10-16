<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<section class="col-lg-12 connectedSortable">
    <div class="box box-primary">
        <div class="box-header with-border">
            <i class="fa fa-info-circle"></i>
            <h3 class="box-title">Informação</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body">
            <?php echo $msg ?>
        </div>
    </div>
</section>