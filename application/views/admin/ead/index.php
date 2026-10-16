<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if(isset($message)) {echo $message;} ?>
        <div class="row">
            <div class="col-md-12">
                 <div class="box">
                    <div class="box-body text-center">
                        <?php if(@$this->data['url_login']): ?>
                            <a class="btn btn-primary btn-flat" href="<?php echo $url_login ?>" target="_blank"><i class="fa fa-key" aria-hidden="true"></i> Acessar EAD</a>
                        <?php else: ?>
                            <p>Seu usuário está expirado ou não possui cadastro EAD.</p>
                        <?php endif ?>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>
