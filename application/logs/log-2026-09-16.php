<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

DEBUG - 2026-09-16 16:41:12 --> UTF-8 Support Enabled
DEBUG - 2026-09-16 16:41:12 --> No URI present. Default controller set.
DEBUG - 2026-09-16 16:41:12 --> Global POST, GET and COOKIE data sanitized
DEBUG - 2026-09-16 16:41:12 --> Session: Initialization under CLI aborted.
ERROR - 2026-09-16 16:41:23 --> Severity: Warning --> mysqli::real_connect(): php_network_getaddresses: getaddrinfo for mysql.xkeysti.com.br failed: The requested name is valid, but no data of the requested type was found.  C:\wamp64\www\maestriatech\system\database\drivers\mysqli\mysqli_driver.php 201
ERROR - 2026-09-16 16:41:23 --> Severity: Error --> Uncaught mysqli_sql_exception: php_network_getaddresses: getaddrinfo for mysql.xkeysti.com.br failed: The requested name is valid, but no data of the requested type was found.  in C:\wamp64\www\maestriatech\system\database\drivers\mysqli\mysqli_driver.php:201
Stack trace:
#0 C:\wamp64\www\maestriatech\system\database\drivers\mysqli\mysqli_driver.php(201): mysqli->real_connect('mysql.xkeysti.c...', 'xkeysti1', Object(SensitiveParameterValue), 'xkeysti1', NULL, NULL, 0)
#1 C:\wamp64\www\maestriatech\system\database\DB_driver.php(401): CI_DB_mysqli_driver->db_connect(false)
#2 C:\wamp64\www\maestriatech\system\database\DB.php(216): CI_DB_driver->initialize()
#3 C:\wamp64\www\maestriatech\system\core\Loader.php(393): DB(Array, NULL)
#4 C:\wamp64\www\maestriatech\application\core\MY_Controller.php(12): CI_Loader->database()
#5 C:\wamp64\www\maestriatech\application\core\MY_Controller.php(70): MY_Controller->__construct()
#6 C:\wamp64\www\maestriatech\application\controllers\Dashboard.php(13): Admin_Controller->__construct()
#7 C:\wamp64\www\maestriatech\system\core\CodeIgniter.php(518): Dashboard->__construct()
#8 C:\wamp64\www\maestriatech\index.php(297): require_once('C:\\wamp64\\www\\m...')
#9 Command line code(1): require('C:\\wamp64\\www\\m...')
#10 {main}
  thrown C:\wamp64\www\maestriatech\system\database\drivers\mysqli\mysqli_driver.php 201
