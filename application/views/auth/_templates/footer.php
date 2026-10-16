<?php
defined('BASEPATH') OR exit('No direct script access allowed');

?>
        <script src="<?php echo base_url($frameworks_dir . '/jquery/jquery.min.js'); ?>"></script>
        <script src="<?php echo base_url($frameworks_dir . '/bootstrap/js/bootstrap.min.js'); ?>"></script>

<?php if ($this->router->fetch_class() == 'auth' && ($this->router->fetch_method() == 'avaliacao_chamado')): ?>
        <script src="<?php echo base_url($plugins_dir . '/star-rating/star-rating.min.js'); ?>"></script>
<?php endif; ?>

<?php if ($this->router->fetch_class() == 'auth' && ($this->router->fetch_method() == 'activate' || $this->router->fetch_method() == 'reset_password')): ?>
        <script src="<?php echo base_url($plugins_dir . '/pwstrength/pwstrength.min.js?v=2'); ?>"></script>
        <script src="<?php echo base_url($frameworks_dir . '/domprojects/js/dp.min.js?v=4'); ?>"></script>
<?php endif; ?>
    </body>
</html>