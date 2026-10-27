<?php
defined('BASEPATH') OR exit('No direct script access allowed');

?>
        <script src="<?php echo base_url($frameworks_dir . '/jquery/jquery.min.js'); ?>"></script>
        <script src="<?php echo base_url($frameworks_dir . '/bootstrap/js/bootstrap.min.js'); ?>"></script>

<?php if ($this->router->fetch_class() == 'auth' && ($this->router->fetch_method() == 'activate' || $this->router->fetch_method() == 'reset_password')): ?>
        <script src="<?php echo base_url($plugins_dir . '/pwstrength/pwstrength.min.js?v=2'); ?>"></script>
        <script src="<?php echo base_url($frameworks_dir . '/domprojects/js/dp.min.js?v=2'); ?>"></script>
<?php endif; ?>

        <script src="<?php echo base_url('assets/xkey/login.js?v=' . (file_exists(FCPATH . 'assets/xkey/login.js') ? filemtime(FCPATH . 'assets/xkey/login.js') : time())); ?>"></script>
    </body>
</html>