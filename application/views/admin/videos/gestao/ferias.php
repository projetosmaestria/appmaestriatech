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
                    <div class="box-body">
                    	<div class="embed-responsive embed-responsive-16by9">
                        	<iframe src="https://player.vimeo.com/video/239674346" class="embed-responsive-item" width="640" height="360" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                    	</div>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>
