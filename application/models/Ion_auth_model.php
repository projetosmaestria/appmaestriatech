<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Ion Auth Model
*
* Version: 2.5.2
*
* Author:  Ben Edmunds
* 		   ben.edmunds@gmail.com
*	  	   @benedmunds
*
* Added Awesomeness: Phil Sturgeon
*
* Location: http://github.com/benedmunds/CodeIgniter-Ion-Auth
*
* Created:  10.01.2009
*
* Last Change: 3.22.13
*
* Changelog:
* * 3-22-13 - Additional entropy added - 52aa456eef8b60ad6754b31fbdcc77bb
*
* Description:  Modified auth system based on redux_auth with extensive customization.  This is basically what Redux Auth 2 should be.
* Original Author name has been kept but that does not mean that the method has not been modified.
*
* Requirements: PHP5 or above
*
*/

require_once(APPPATH."third_party/phpmailer/PHPMailerAutoload.php");

class Ion_auth_model extends CI_Model
{
	/**
	 * Holds an array of tables used
	 *
	 * @var array
	 **/
	public $tables = array();

	/**
	 * activation code
	 *
	 * @var string
	 **/
	public $activation_code;

	/**
	 * forgotten password key
	 *
	 * @var string
	 **/
	public $forgotten_password_code;

	/**
	 * new password
	 *
	 * @var string
	 **/
	public $new_password;

	/**
	 * Identity
	 *
	 * @var string
	 **/
	public $identity;

	/**
	 * Where
	 *
	 * @var array
	 **/
	public $_ion_where = array();

	/**
	 * Select
	 *
	 * @var array
	 **/
	public $_ion_select = array();

	/**
	 * Like
	 *
	 * @var array
	 **/
	public $_ion_like = array();
	
	/**
	 * Join
	 *
	 * @var array
	 **/
	public $_ion_join = array();

	/**
	 * Limit
	 *
	 * @var string
	 **/
	public $_ion_limit = NULL;

	/**
	 * Offset
	 *
	 * @var string
	 **/
	public $_ion_offset = NULL;

	/**
	 * Order By
	 *
	 * @var string
	 **/
	public $_ion_order_by = NULL;

	/**
	 * Order
	 *
	 * @var string
	 **/
	public $_ion_order = NULL;

	/**
	 * Hooks
	 *
	 * @var object
	 **/
	protected $_ion_hooks;

	/**
	 * Response
	 *
	 * @var string
	 **/
	protected $response = NULL;

	/**
	 * message (uses lang file)
	 *
	 * @var string
	 **/
	protected $messages;

	/**
	 * error message (uses lang file)
	 *
	 * @var string
	 **/
	protected $errors;

	/**
	 * error start delimiter
	 *
	 * @var string
	 **/
	protected $error_start_delimiter;

	/**
	 * error end delimiter
	 *
	 * @var string
	 **/
	protected $error_end_delimiter;

	/**
	 * caching of users and their groups
	 *
	 * @var array
	 **/
	public $_cache_user_in_group = array();

	/**
	 * caching of groups
	 *
	 * @var array
	 **/
	protected $_cache_groups = array();

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->config('ion_auth', TRUE);
		$this->load->helper('cookie');
		$this->load->helper('date');
		$this->lang->load('ion_auth');

		// initialize db tables data
		$this->tables  = $this->config->item('tables', 'ion_auth');

		//initialize data
		$this->identity_column = $this->config->item('identity', 'ion_auth');
		$this->store_salt      = $this->config->item('store_salt', 'ion_auth');
		$this->salt_length     = $this->config->item('salt_length', 'ion_auth');
		$this->join			   = $this->config->item('join', 'ion_auth');


		// initialize hash method options (Bcrypt)
		$this->hash_method = $this->config->item('hash_method', 'ion_auth');
		$this->default_rounds = $this->config->item('default_rounds', 'ion_auth');
		$this->random_rounds = $this->config->item('random_rounds', 'ion_auth');
		$this->min_rounds = $this->config->item('min_rounds', 'ion_auth');
		$this->max_rounds = $this->config->item('max_rounds', 'ion_auth');


		// initialize messages and error
		$this->messages    = array();
		$this->errors      = array();
		$delimiters_source = $this->config->item('delimiters_source', 'ion_auth');

		// load the error delimeters either from the config file or use what's been supplied to form validation
		if ($delimiters_source === 'form_validation')
		{
			// load in delimiters from form_validation
			// to keep this simple we'll load the value using reflection since these properties are protected
			$this->load->library('form_validation');
			$form_validation_class = new ReflectionClass("CI_Form_validation");

			$error_prefix = $form_validation_class->getProperty("_error_prefix");
			$error_prefix->setAccessible(TRUE);
			$this->error_start_delimiter = $error_prefix->getValue($this->form_validation);
			$this->message_start_delimiter = $this->error_start_delimiter;

			$error_suffix = $form_validation_class->getProperty("_error_suffix");
			$error_suffix->setAccessible(TRUE);
			$this->error_end_delimiter = $error_suffix->getValue($this->form_validation);
			$this->message_end_delimiter = $this->error_end_delimiter;
		}
		else
		{
			// use delimiters from config
			$this->message_start_delimiter = $this->config->item('message_start_delimiter', 'ion_auth');
			$this->message_end_delimiter   = $this->config->item('message_end_delimiter', 'ion_auth');
			$this->error_start_delimiter   = $this->config->item('error_start_delimiter', 'ion_auth');
			$this->error_end_delimiter     = $this->config->item('error_end_delimiter', 'ion_auth');
		}


		// initialize our hooks object
		$this->_ion_hooks = new stdClass;

		// load the bcrypt class if needed
		if ($this->hash_method == 'bcrypt') {
			if ($this->random_rounds)
			{
				$rand = rand($this->min_rounds,$this->max_rounds);
				$params = array('rounds' => $rand);
			}
			else
			{
				$params = array('rounds' => $this->default_rounds);
			}

			$params['salt_prefix'] = $this->config->item('salt_prefix', 'ion_auth');
			$this->load->library('bcrypt',$params);
		}

		$this->trigger_events('model_constructor');
	}

	/**
	 * Misc functions
	 *
	 * Hash password : Hashes the password to be stored in the database.
	 * Hash password db : This function takes a password and validates it
	 * against an entry in the users table.
	 * Salt : Generates a random salt value.
	 *
	 * @author Mathew
	 */

	/**
	 * Hashes the password to be stored in the database.
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function hash_password($password, $salt=false, $use_sha1_override=FALSE)
	{
		if (empty($password))
		{
			return FALSE;
		}

		// bcrypt
		if ($use_sha1_override === FALSE && $this->hash_method == 'bcrypt')
		{
			return $this->bcrypt->hash($password);
		}


		if ($this->store_salt && $salt)
		{
			return  sha1($password . $salt);
		}
		else
		{
			$salt = $this->salt();
			return  $salt . substr(sha1($salt . $password), 0, -$this->salt_length);
		}
	}

	/**
	 * This function takes a password and validates it
	 * against an entry in the users table.
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function hash_password_db($id, $password, $use_sha1_override=FALSE)
	{
		if (empty($id) || empty($password))
		{
			return FALSE;
		}

		$this->trigger_events('extra_where');

		$query = $this->db->select('password, salt')
		                  ->where('id', $id)
		                  ->limit(1)
		                  ->order_by('id', 'desc')
		                  ->get($this->tables['users']);

		$hash_password_db = $query->row();

		if ($query->num_rows() !== 1)
		{
			return FALSE;
		}

		// bcrypt
		if ($use_sha1_override === FALSE && $this->hash_method == 'bcrypt')
		{
			if ($this->bcrypt->verify($password,$hash_password_db->password))
			{
				return TRUE;
			}

			return FALSE;
		}

		// sha1
		if ($this->store_salt)
		{
			$db_password = sha1($password . $hash_password_db->salt);
		}
		else
		{
			$salt = substr($hash_password_db->password, 0, $this->salt_length);

			$db_password =  $salt . substr(sha1($salt . $password), 0, -$this->salt_length);
		}

		if($db_password == $hash_password_db->password)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Generates a random salt value for forgotten passwords or any other keys. Uses SHA1.
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function hash_code($password)
	{
		return $this->hash_password($password, FALSE, TRUE);
	}

	/**
	 * Generates a random salt value.
	 *
	 * Salt generation code taken from https://github.com/ircmaxell/password_compat/blob/master/lib/password.php
	 *
	 * @return void
	 * @author Anthony Ferrera
	 **/
	public function salt()
	{

		$raw_salt_len = 16;

 		$buffer = '';
        $buffer_valid = false;

        if (function_exists('mcrypt_create_iv') && !defined('PHALANGER')) {
            $buffer = mcrypt_create_iv($raw_salt_len, MCRYPT_DEV_URANDOM);
            if ($buffer) {
                $buffer_valid = true;
            }
        }

        if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
            $buffer = openssl_random_pseudo_bytes($raw_salt_len);
            if ($buffer) {
                $buffer_valid = true;
            }
        }

        if (!$buffer_valid && @is_readable('/dev/urandom')) {
            $f = fopen('/dev/urandom', 'r');
            $read = strlen($buffer);
            while ($read < $raw_salt_len) {
                $buffer .= fread($f, $raw_salt_len - $read);
                $read = strlen($buffer);
            }
            fclose($f);
            if ($read >= $raw_salt_len) {
                $buffer_valid = true;
            }
        }

        if (!$buffer_valid || strlen($buffer) < $raw_salt_len) {
            $bl = strlen($buffer);
            for ($i = 0; $i < $raw_salt_len; $i++) {
                if ($i < $bl) {
                    $buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
                } else {
                    $buffer .= chr(mt_rand(0, 255));
                }
            }
        }

        $salt = $buffer;

        // encode string with the Base64 variant used by crypt
        $base64_digits   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
        $bcrypt64_digits = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $base64_string   = base64_encode($salt);
        $salt = strtr(rtrim($base64_string, '='), $base64_digits, $bcrypt64_digits);

	    $salt = substr($salt, 0, $this->salt_length);


		return $salt;

	}

	/**
	 * Activation functions
	 *
	 * Activate : Validates and removes activation code.
	 * Deactivae : Updates a users row with an activation code.
	 *
	 * @author Mathew
	 */

	/**
	 * activate
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function activate($id, $code = false)
	{
		$this->trigger_events('pre_activate');

		if ($code !== FALSE)
		{
			$query = $this->db->select($this->identity_column)
			                  ->where('activation_code', $code)
			                  ->where('id', $id)
			                  ->limit(1)
		    				  ->order_by('id', 'desc')
			                  ->get($this->tables['users']);

			$result = $query->row();

			if ($query->num_rows() !== 1)
			{
				$this->trigger_events(array('post_activate', 'post_activate_unsuccessful'));
				$this->set_error('activate_unsuccessful');
				return FALSE;
			}

			$data = array(
			    'activation_code' => NULL,
			    'active'          => 1
			);

			$this->trigger_events('extra_where');
			$this->db->update($this->tables['users'], $data, array('id' => $id));
		}
		else
		{
			$data = array(
			    'activation_code' => NULL,
			    'active'          => 1
			);


			$this->trigger_events('extra_where');
			$this->db->update($this->tables['users'], $data, array('id' => $id));
		}


		$return = $this->db->affected_rows() == 1;
		if ($return)
		{
			$this->trigger_events(array('post_activate', 'post_activate_successful'));
			$this->set_message('activate_successful');
		}
		else
		{
			$this->trigger_events(array('post_activate', 'post_activate_unsuccessful'));
			$this->set_error('activate_unsuccessful');
		}


		return $return;
	}


	/**
	 * Deactivate
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function deactivate($id = NULL)
	{
		$this->trigger_events('deactivate');

		if (!isset($id))
		{
			$this->set_error('deactivate_unsuccessful');
			return FALSE;
		}

		$activation_code       = sha1(md5(microtime()));
		$this->activation_code = $activation_code;

		$data = array(
		    'activation_code' => $activation_code,
		    'active'          => 0
		);

		$this->trigger_events('extra_where');
		$this->db->update($this->tables['users'], $data, array('id' => $id));

		$return = $this->db->affected_rows() == 1;
		if ($return)
			$this->set_message('deactivate_successful');
		else
			$this->set_error('deactivate_unsuccessful');

		return $return;
	}

	public function clear_forgotten_password_code($code) {

		if (empty($code))
		{
			return FALSE;
		}

		$this->db->where('forgotten_password_code', $code);

		if ($this->db->count_all_results($this->tables['users']) > 0)
		{
			$data = array(
			    'forgotten_password_code' => NULL,
			    'forgotten_password_time' => NULL
			);

			$this->db->update($this->tables['users'], $data, array('forgotten_password_code' => $code));

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * reset password
	 *
	 * @return bool
	 * @author Mathew
	 **/
	public function reset_password($identity, $new) {
		$this->trigger_events('pre_change_password');

		if (!$this->identity_check($identity)) {
			$this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
			return FALSE;
		}

		$this->trigger_events('extra_where');

		$query = $this->db->select('id, password, salt')
		                  ->where($this->identity_column, $identity)
		                  ->limit(1)
		    			  ->order_by('id', 'desc')
		                  ->get($this->tables['users']);

		if ($query->num_rows() !== 1)
		{
			$this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
			$this->set_error('password_change_unsuccessful');
			return FALSE;
		}

		$result = $query->row();

		$new = $this->hash_password($new, $result->salt);

		// store the new password and reset the remember code so all remembered instances have to re-login
		// also clear the forgotten password code
		$data = array(
		    'password' => $new,
		    'remember_code' => NULL,
		    'forgotten_password_code' => NULL,
		    'forgotten_password_time' => NULL,
		    'password_updated' => date('Y-m-d H:i:s'),
		);

		$this->trigger_events('extra_where');
		$this->db->update($this->tables['users'], $data, array($this->identity_column => $identity));

		$return = $this->db->affected_rows() == 1;
		if ($return)
		{
			$this->trigger_events(array('post_change_password', 'post_change_password_successful'));
			$this->set_message('password_change_successful');
		}
		else
		{
			$this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
			$this->set_error('password_change_unsuccessful');
		}

		return $return;
	}

	/**
	 * change password
	 *
	 * @return bool
	 * @author Mathew
	 **/
	public function change_password($identity, $old, $new)
	{
		$this->trigger_events('pre_change_password');

		$this->trigger_events('extra_where');

		$query = $this->db->select('id, password, salt')
		                  ->where($this->identity_column, $identity)
		                  ->limit(1)
		    			  ->order_by('id', 'desc')
		                  ->get($this->tables['users']);

		if ($query->num_rows() !== 1)
		{
			$this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
			$this->set_error('password_change_unsuccessful');
			return FALSE;
		}

		$user = $query->row();

		$old_password_matches = $this->hash_password_db($user->id, $old);

		if ($old_password_matches === TRUE)
		{
			// store the new password and reset the remember code so all remembered instances have to re-login
			$hashed_new_password  = $this->hash_password($new, $user->salt);
			$data = array(
			    'password' => $hashed_new_password,
			    'remember_code' => NULL,
			);

			$this->trigger_events('extra_where');

			$successfully_changed_password_in_db = $this->db->update($this->tables['users'], $data, array($this->identity_column => $identity));
			if ($successfully_changed_password_in_db)
			{
				$this->trigger_events(array('post_change_password', 'post_change_password_successful'));
				$this->set_message('password_change_successful');
			}
			else
			{
				$this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
				$this->set_error('password_change_unsuccessful');
			}

			return $successfully_changed_password_in_db;
		}

		$this->set_error('password_change_unsuccessful');
		return FALSE;
	}

	/**
	 * Checks username
	 *
	 * @return bool
	 * @author Mathew
	 **/
	public function username_check($username = '')
	{
		$this->trigger_events('username_check');

		if (empty($username))
		{
			return FALSE;
		}

		$this->trigger_events('extra_where');

		return $this->db->where('username', $username)
										->group_by("id")
										->order_by("id", "ASC")
										->limit(1)
		                ->count_all_results($this->tables['users']) > 0;
	}

	/**
	 * Checks email
	 *
	 * @return bool
	 * @author Mathew
	 **/
	public function email_check($email = '')
	{
		$this->trigger_events('email_check');

		if (empty($email))
		{
			return FALSE;
		}

		$this->trigger_events('extra_where');

		return $this->db->where('email', $email)
										->group_by("id")
										->order_by("id", "ASC")
										->limit(1)
		                ->count_all_results($this->tables['users']) > 0;
	}

	/**
	 * Identity check
	 *
	 * @return bool
	 * @author Mathew
	 **/
	public function identity_check($identity = '')
	{
		$this->trigger_events('identity_check');

		if (empty($identity))
		{
			return FALSE;
		}

		return $this->db->where($this->identity_column, $identity)
		                ->count_all_results($this->tables['users']) > 0;
	}

	/**
	 * Insert a forgotten password key.
	 *
	 * @return bool
	 * @author Mathew
	 * @updated Ryan
	 * @updated 52aa456eef8b60ad6754b31fbdcc77bb
	 **/
	public function forgotten_password($identity)
	{
		if (empty($identity))
		{
			$this->trigger_events(array('post_forgotten_password', 'post_forgotten_password_unsuccessful'));
			return FALSE;
		}
		
		// All some more randomness
		$activation_code_part = "";
		if(function_exists("openssl_random_pseudo_bytes")) {
			$activation_code_part = openssl_random_pseudo_bytes(128);
		}

		for($i=0;$i<1024;$i++) {
			$activation_code_part = sha1($activation_code_part . mt_rand() . microtime());
		}

		$key = $this->hash_code($activation_code_part.$identity);

		// If enable query strings is set, then we need to replace any unsafe characters so that the code can still work
		if ($key != '' && $this->config->item('permitted_uri_chars') != '' && $this->config->item('enable_query_strings') == FALSE)
		{
			// preg_quote() in PHP 5.3 escapes -, so the str_replace() and addition of - to preg_quote() is to maintain backwards
			// compatibility as many are unaware of how characters in the permitted_uri_chars will be parsed as a regex pattern
			if ( ! preg_match("|^[".str_replace(array('\\-', '\-'), '-', preg_quote($this->config->item('permitted_uri_chars'), '-'))."]+$|i", $key))
			{
				$key = preg_replace("/[^".$this->config->item('permitted_uri_chars')."]+/i", "-", $key);
			}
		}

		$this->forgotten_password_code = $key;

		$this->trigger_events('extra_where');

		$update = array(
		    'forgotten_password_code' => $key,
		    'forgotten_password_time' => time()
		);

		$this->db->update($this->tables['users'], $update, array($this->identity_column => $identity));

		$return = $this->db->affected_rows() == 1;

		// if ($return)
		// 	$this->trigger_events(array('post_forgotten_password', 'post_forgotten_password_successful'));
		// else
		// 	$this->trigger_events(array('post_forgotten_password', 'post_forgotten_password_unsuccessful'));
		
		
		if ($return){
			$email_config_db = $this->get_email_config(1)->row();
				
			if($email_config_db->test == 1) {
				$email_config = rowToConfigEmail($email_config_db);
				$from_email = $email_config_db->from_email;
				$from_name = $email_config_db->from_name;
			} else {
				$email_config = $this->config->item('email_config', 'ion_auth');
				$from_email = $this->config->item('admin_email', 'ion_auth');
				$from_name = $this->config->item('site_title', 'ion_auth');
			}

			$padrao_email = $this->get_email_padrao(3, 1)->row();
						
			$padrao_email_msg = $padrao_email->mensagem;
			$padrao_email_msg = str_replace("{email}", $identity, $padrao_email_msg);
			$padrao_email_msg = str_replace("{link}",  base_url('auth/reset_password/'.$key), $padrao_email_msg);

			$mail = new PHPMailer;
			$mail->isSMTP();
			$mail->Host     = $email_config_db->host;
			$mail->SMTPAuth = true;
			$mail->Username = $email_config_db->user;
			$mail->Password = $email_config_db->pass;
			$mail->SMTPSecure = 'tsl';
			$mail->Port     = $email_config_db->port;

			$mail->setFrom($from_email, utf8_decode($from_name));
			
			$mail->addAddress($identity);
			$mail->Subject = utf8_decode($padrao_email->assunto);
			$mail->isHTML(true);

			$mail->Body = utf8_decode($padrao_email_msg);
			$mail->send();
		}

		return $return;
	}

	/**
	 * Forgotten Password Complete
	 *
	 * @return string
	 * @author Mathew
	 **/
	public function forgotten_password_complete($code, $salt=FALSE)
	{
		$this->trigger_events('pre_forgotten_password_complete');

		if (empty($code))
		{
			$this->trigger_events(array('post_forgotten_password_complete', 'post_forgotten_password_complete_unsuccessful'));
			return FALSE;
		}

		$profile = $this->where('forgotten_password_code', $code)->users()->row(); //pass the code to profile

		if ($profile) {

			if ($this->config->item('forgot_password_expiration', 'ion_auth') > 0) {
				//Make sure it isn't expired
				$expiration = $this->config->item('forgot_password_expiration', 'ion_auth');
				if (time() - $profile->forgotten_password_time > $expiration) {
					//it has expired
					$this->set_error('forgot_password_expired');
					$this->trigger_events(array('post_forgotten_password_complete', 'post_forgotten_password_complete_unsuccessful'));
					return FALSE;
				}
			}

			$password = $this->salt();

			$data = array(
			    'password'                => $this->hash_password($password, $salt),
			    'forgotten_password_code' => NULL,
			    'active'                  => 1,
			 );

			$this->db->update($this->tables['users'], $data, array('forgotten_password_code' => $code));

			$this->trigger_events(array('post_forgotten_password_complete', 'post_forgotten_password_complete_successful'));
			return $password;
		}

		$this->trigger_events(array('post_forgotten_password_complete', 'post_forgotten_password_complete_unsuccessful'));
		return FALSE;
	}

	/**
	 * register
	 *
	 * @return bool
	 * @author Mathew
	 **/
	public function register($identity, $password, $email, $additional_data = array(), $active = array(), $groups = array(), $setores = array(), $clients = array())
	{
		$this->trigger_events('pre_register');

		$manual_activation = $this->config->item('manual_activation', 'ion_auth');

		if ($this->identity_check($identity))
		{
			$this->set_error('account_creation_duplicate_identity');
			return FALSE;
		}
		/*elseif ( !$this->config->item('default_group', 'ion_auth') && empty($groups) )
		{
			$this->set_error('account_creation_missing_default_group');
			return FALSE;
		}
		elseif ( empty($clients) )
		{
			$this->set_error('account_creation_empty_unids');
			return FALSE;
		}
		elseif ( empty($this->session->userdata('empresa_id')) )
		{
			$this->set_error('account_creation_empty_empresa');
			return FALSE;
		}*/

		// check if the default set in config exists in database
		$query = $this->db->get_where($this->tables['groups'],array('name' => $this->config->item('default_group', 'ion_auth')),1)->row();
		if( !isset($query->id) && empty($groups) )
		{
			$this->set_error('account_creation_invalid_default_group');
			return FALSE;
		}

		// capture default group details
		$default_group = $query;

		// IP Address
		$ip_address = $this->_prepare_ip($this->input->ip_address());
		$salt       = $this->store_salt ? $this->salt() : FALSE;
		$password   = $this->hash_password($password, $salt);

		$prefs = $this->get_admin_preferences();

		// Users table.
		$data = array(
		    $this->identity_column   => $identity,
		    'password'   => $password,
		    'email'      => $email,
		    'ip_address' => $ip_address,
		    'created_on' => time(),
		    'active'     => ($prefs->cadastro_user_completo == 0) ? ($manual_activation === false ? 1 : 0) : 1
		);

        if($prefs->cadastro_user_completo == 1) {
        	$data['first_name'] = $additional_data['first_name'];
        	$data['last_name'] = $additional_data['last_name'];
        	$data['password_updated'] = date('Y-m-d H:i:s');
        }

		if ($this->store_salt)
		{
			$data['salt'] = $salt;
		}

		// filter out any data passed that doesnt have a matching column in the users table
		// and merge the set user data and the additional data
		$user_data = array_merge($this->_filter_data($this->tables['users'], $additional_data), $data);

		$this->trigger_events('extra_set');

		$this->db->insert($this->tables['users'], $user_data);

		$id = $this->db->insert_id();

		// add in groups array if it doesn't exits and stop adding into default group if default group ids are set
		if( isset($default_group->id) && empty($groups) )
		{
			$groups[] = $default_group->id;
		}

		if (!empty($active))
		{
			foreach ($active as $key => $emp)
			{
				$this->add_to_empresas_users($key, $id);
			}
		}

		if (!empty($setores))
		{
			// add to groups
			foreach ($setores as $setor)
			{
				$this->add_to_setor($setor, $id);
			}
		}

		if (!empty($groups))
		{
			// add to groups
			foreach ($groups as $group)
			{
				$this->add_to_group($group, $id);
			}
		}

		if (!empty($clients))
		{
			// add to clients
			foreach ($clients as $cli)
			{
				$this->add_to_unid($cli, $id);
			}
		}

		$this->trigger_events('post_register');

		return (isset($id)) ? $id : FALSE;
	}

	/**
	 * login
	 *
	 * @return bool
	 * @author Mathew
	 **/
	public function login($identity, $password, $remember=FALSE)
	{
		$this->trigger_events('pre_login');
		
		if (empty($identity) || empty($password))
		{
			$this->set_error('login_unsuccessful');
			return FALSE;
		}
		
		$this->trigger_events('extra_where');
		
		$query = $this->db->select('u.'.$this->identity_column . ', u.email, u.id, u.password, u.active, u.last_login, eu.empresa_id, u.password_updated')
				->join($this->tables['empresas_users'].' eu', 'eu.'.$this->join['users'].'= u.id')
				->where('u.'.$this->identity_column, $identity)
				->limit(1)
				->order_by('u.id', 'desc')
				->get($this->tables['users'].' u');

		if($this->is_time_locked_out($identity))
		{
			// Hash something anyway, just to take up time
			$this->hash_password($password);

			$this->trigger_events('post_login_unsuccessful');
			$this->set_error('login_timeout');

			return FALSE;
		}

		if ($query->num_rows() === 1)
		{
			$user = $query->row();

			$password = $this->hash_password_db($user->id, $password);

			if ($password === TRUE)
			{
				if ($user->active == 0)
				{
					$this->trigger_events('post_login_unsuccessful');
					$this->set_error('login_unsuccessful_not_active');

					return FALSE;
				}

				/*if($this->is_password_greater_than_90_days($user))
				{
					$this->ion_auth->forgotten_password($identity);
					$this->set_error('password_must_be_updated');
					return FALSE;
				}*/
				
				$this->set_session($user);

				$this->update_last_login($user->id);

				$this->clear_login_attempts($identity);

				if ($remember && $this->config->item('remember_users', 'ion_auth'))
				{
					$this->remember_user($user->id);
				}

				$this->trigger_events(array('post_login', 'post_login_successful'));
				//$this->set_message('login_successful');

				return TRUE;
			}
		}

		// Hash something anyway, just to take up time
		$this->hash_password($password);

		$this->increase_login_attempts($identity);

		$this->trigger_events('post_login_unsuccessful');
		$this->set_error('login_unsuccessful');

		return FALSE;
	}

	/**
	 * is_max_login_attempts_exceeded
	 * Based on code from Tank Auth, by Ilya Konyukhov (https://github.com/ilkon/Tank-Auth)
	 *
	 * @param string $identity
	 * @return boolean
	 **/
	public function is_max_login_attempts_exceeded($identity) {
		if ($this->config->item('track_login_attempts', 'ion_auth')) {
			$max_attempts = $this->config->item('maximum_login_attempts', 'ion_auth');
			if ($max_attempts > 0) {
				$attempts = $this->get_attempts_num($identity);
				return $attempts >= $max_attempts;
			}
		}
		return FALSE;
	}

	/**
	 * Get number of attempts to login occured from given IP-address or identity
	 * Based on code from Tank Auth, by Ilya Konyukhov (https://github.com/ilkon/Tank-Auth)
	 *
	 * @param	string $identity
	 * @return	int
	 */
	function get_attempts_num($identity)
	{
        if ($this->config->item('track_login_attempts', 'ion_auth')) {
            $ip_address = $this->_prepare_ip($this->input->ip_address());
            $this->db->select('1', FALSE);
            if ($this->config->item('track_login_ip_address', 'ion_auth')) {
            	$this->db->where('ip_address', $ip_address);
            	$this->db->where('login', $identity);
            } else if (strlen($identity) > 0) $this->db->or_where('login', $identity);
            $qres = $this->db->get($this->tables['login_attempts']);
            return $qres->num_rows();
        }
        return 0;
	}
	/**
	 * Get a boolean to determine if an account should be locked out due to
	 * exceeded login attempts within a given period
	 *
	 * @return	boolean
	 */
	public function is_time_locked_out($identity) {
	
		return $this->is_max_login_attempts_exceeded($identity) && $this->get_last_attempt_time($identity) > time() - $this->config->item('lockout_time', 'ion_auth');
	}

	/**
	 * Get the time of the last time a login attempt occured from given IP-address or identity
	 *
	 * @param	string $identity
	 * @return	int
	 */
	public function get_last_attempt_time($identity) {
		if ($this->config->item('track_login_attempts', 'ion_auth')) {
			$ip_address = $this->_prepare_ip($this->input->ip_address());

			$this->db->select_max('time');
            if ($this->config->item('track_login_ip_address', 'ion_auth')) $this->db->where('ip_address', $ip_address);
			else if (strlen($identity) > 0) $this->db->or_where('login', $identity);
			$qres = $this->db->get($this->tables['login_attempts'], 1);

			if($qres->num_rows() > 0) {
				return $qres->row()->time;
			}
		}

		return 0;
	}

	/**
	 * increase_login_attempts
	 * Based on code from Tank Auth, by Ilya Konyukhov (https://github.com/ilkon/Tank-Auth)
	 *
	 * @param string $identity
	 **/
	public function increase_login_attempts($identity) {
		if ($this->config->item('track_login_attempts', 'ion_auth')) {
			$ip_address = $this->_prepare_ip($this->input->ip_address());
			return $this->db->insert($this->tables['login_attempts'], array('ip_address' => $ip_address, 'login' => $identity, 'time' => time()));
		}
		return FALSE;
	}

	/**
	 * clear_login_attempts
	 * Based on code from Tank Auth, by Ilya Konyukhov (https://github.com/ilkon/Tank-Auth)
	 *
	 * @param string $identity
	 **/
	public function clear_login_attempts($identity, $expire_period = 86400) {
		if ($this->config->item('track_login_attempts', 'ion_auth')) {
			$ip_address = $this->_prepare_ip($this->input->ip_address());

			$this->db->where(array('ip_address' => $ip_address, 'login' => $identity));
			// Purge obsolete login attempts
			$this->db->or_where('time <', time() - $expire_period, FALSE);

			return $this->db->delete($this->tables['login_attempts']);
		}
		return FALSE;
	}

	public function limit($limit)
	{
		$this->trigger_events('limit');
		$this->_ion_limit = $limit;

		return $this;
	}

	public function offset($offset)
	{
		$this->trigger_events('offset');
		$this->_ion_offset = $offset;

		return $this;
	}

	public function join($join)
	{
		if (is_array($join))
		{
			array_push($this->_ion_join, $join);
		}

		return $this;
	}

	public function where($where, $value = NULL)
	{
		$this->trigger_events('where');

		if (!is_array($where))
		{
			$where = array($where => $value);
		}

		array_push($this->_ion_where, $where);

		return $this;
	}

	public function like($like, $value = NULL, $position = 'both')
	{
		$this->trigger_events('like');

		if (!is_array($like))
		{
			$like = array($like => array(
				'value'    => $value,
				'position' => $position,
			));
		}

		array_push($this->_ion_like, $like);

		return $this;
	}

	public function select($select)
	{
		$this->trigger_events('select');

		$this->_ion_select[] = $select;

		return $this;
	}

	public function order_by($by, $order='desc')
	{
		$this->trigger_events('order_by');

		$this->_ion_order_by = $by;
		$this->_ion_order    = $order;

		return $this;
	}

	public function row()
	{
		$this->trigger_events('row');

		$row = $this->response->row();

		return $row;
	}

	public function row_array()
	{
		$this->trigger_events(array('row', 'row_array'));

		$row = $this->response->row_array();

		return $row;
	}

	public function result()
	{
		$this->trigger_events('result');

		$result = $this->response->result();

		return $result;
	}

	public function result_array()
	{
		$this->trigger_events(array('result', 'result_array'));

		$result = $this->response->result_array();

		return $result;
	}

	public function num_rows()
	{
		$this->trigger_events(array('num_rows'));

		$result = $this->response->num_rows();

		return $result;
	}

	/**
	 * users
	 *
	 * @return object Users
	 * @author Ben Edmunds
	 **/
	public function users($groups = NULL)
	{
		$this->trigger_events('users');

		if (isset($this->_ion_select) && !empty($this->_ion_select))
		{
			foreach ($this->_ion_select as $select)
			{
				$this->db->select($select);
			}

			$this->_ion_select = array();
		}
		else
		{
			//default selects
			$this->db->select(array(
			    $this->tables['users'].'.*',
			    $this->tables['users'].'.id as id',
			    $this->tables['users'].'.id as user_id'
			));
		}

		if (isset($this->_ion_join) && !empty($this->_ion_join))
		{
			$this->db->distinct();
			foreach ($this->_ion_join as $join)
			{
				$this->db->join($join[0],$join[1]);
			}

			$this->_ion_join = array();
		}

		// filter by group id(s) if passed
		if (isset($groups))
		{
			// build an array if only one group was passed
			if (!is_array($groups))
			{
				$groups = Array($groups);
			}

			// join and then run a where_in against the group ids
			if (isset($groups) && !empty($groups))
			{
				$this->db->distinct();
				$this->db->join(
				    $this->tables['users_groups'],
				    $this->tables['users_groups'].'.'.$this->join['users'].'='.$this->tables['users'].'.id',
				    'inner'
				);
			}

			// verify if group name or group id was used and create and put elements in different arrays
			$group_ids = array();
			$group_names = array();
			foreach($groups as $group)
			{
				if(is_numeric($group)) $group_ids[] = $group;
				else $group_names[] = $group;
			}
			$or_where_in = (!empty($group_ids) && !empty($group_names)) ? 'or_where_in' : 'where_in';
			// if group name was used we do one more join with groups
			if(!empty($group_names))
			{
				$this->db->join($this->tables['groups'], $this->tables['users_groups'] . '.' . $this->join['groups'] . ' = ' . $this->tables['groups'] . '.id', 'inner');
				$this->db->where_in($this->tables['groups'] . '.name', $group_names);
			}
			if(!empty($group_ids))
			{
				$this->db->{$or_where_in}($this->tables['users_groups'].'.'.$this->join['groups'], $group_ids);
			}
		}

		$this->trigger_events('extra_where');

		// run each where that was passed
		if (isset($this->_ion_where) && !empty($this->_ion_where))
		{
			foreach ($this->_ion_where as $where)
			{
				$this->db->where($where);
			}

			$this->_ion_where = array();
		}

		if (isset($this->_ion_like) && !empty($this->_ion_like))
		{
			foreach ($this->_ion_like as $like)
			{
				$this->db->or_like($like);
			}

			$this->_ion_like = array();
		}

		if (isset($this->_ion_limit) && isset($this->_ion_offset))
		{
			$this->db->limit($this->_ion_limit, $this->_ion_offset);

			$this->_ion_limit  = NULL;
			$this->_ion_offset = NULL;
		}
		else if (isset($this->_ion_limit))
		{
			$this->db->limit($this->_ion_limit);

			$this->_ion_limit  = NULL;
		}

		// set the order
		if (isset($this->_ion_order_by) && isset($this->_ion_order))
		{
			$this->db->order_by($this->_ion_order_by, $this->_ion_order);

			$this->_ion_order    = NULL;
			$this->_ion_order_by = NULL;
		}

		$this->response = $this->db->get($this->tables['users']);

		return $this;
	}

	/**
	 * user
	 *
	 * @return object
	 * @author Ben Edmunds
	 **/
	public function user($id = NULL)
	{
		$this->trigger_events('user');

		// if no id was passed use the current users id
		$id || $id = $this->session->userdata('user_id');

		$this->limit(1);
		$this->order_by($this->tables['users'].'.id', 'desc');
		$this->where($this->tables['users'].'.id', $id);

		$this->users();

		return $this;
	}

	/**
	 * get_users_groups
	 *
	 * @return array
	 * @author Ben Edmunds
	 **/
	public function get_users_groups($id=FALSE, $id_empresa = FALSE)
	{
		$this->trigger_events('get_users_group');

		// if no id was passed use the current users id
		$id || $id = $this->session->userdata('user_id');

		$id_empresa || $id_empresa = $this->session->userdata('empresa_id');

		//return $this->db->select($this->tables['users_groups'].'.'.$this->join['groups'].' as id, '.$this->tables['groups'].'.name, '.$this->tables['groups'].'.description')
        /* ADD => , '.$this->tables['groups'].'.bgcolor' */
		return $this->db->select($this->tables['users_groups'].'.'.$this->join['groups'].' as id, '.$this->tables['groups'].'.name, '.$this->tables['groups'].'.description, '.$this->tables['groups'].'.bgcolor, '.$this->tables['groups'].'.permissoes')
		                ->join($this->tables['groups'], $this->tables['users_groups'].'.'.$this->join['groups'].'='.$this->tables['groups'].'.id')
		                ->where($this->tables['users_groups'].'.'.$this->join['users'], $id)
		                ->where($this->tables['groups'].'.empresa_id', $id_empresa)
		                ->get($this->tables['users_groups']);
	}

	/**
	 * get_users_groups_permissoes
	 *
	 * @return array
	 * @author Ben Edmunds
	 **/
	public function get_users_groups_permissoes($id=FALSE)
	{
		$this->trigger_events('get_users_group');

		// if no id was passed use the current users id
		$id || $id = $this->session->userdata('user_id');

		//return $this->db->select($this->tables['users_groups'].'.'.$this->join['groups'].' as id, '.$this->tables['groups'].'.name, '.$this->tables['groups'].'.description')
        /* ADD => , '.$this->tables['groups'].'.bgcolor' */
		return $this->db->select($this->tables['users_groups'].'.'.$this->join['groups'].' as id, '.$this->tables['groups'].'.name, '.$this->tables['groups'].'.description, '.$this->tables['groups'].'.bgcolor, '.$this->tables['groups'].'.permissoes, '.$this->tables['groups'].'.limited')
		                ->where($this->tables['users_groups'].'.'.$this->join['users'], $id)
		                ->where($this->tables['groups'].'.empresa_id', $this->session->userdata('empresa_id'))
		                ->join($this->tables['groups'], $this->tables['users_groups'].'.'.$this->join['groups'].'='.$this->tables['groups'].'.id')
		                ->get($this->tables['users_groups']);
	}

	/**
	 * add_to_group
	 *
	 * @return bool
	 * @author Ben Edmunds
	 **/
	public function add_to_group($group_ids, $user_id=false)
	{
		$this->trigger_events('add_to_group');

		// if no id was passed use the current users id
		$user_id || $user_id = $this->session->userdata('user_id');

		if(!is_array($group_ids))
		{
			$group_ids = array($group_ids);
		}

		$return = 0;

		// Then insert each into the database
		foreach ($group_ids as $group_id)
		{			
			if ($this->db->insert($this->tables['users_groups'], array( $this->join['groups'] => (float)$group_id, $this->join['users'] => (float)$user_id)))
			{
				if (isset($this->_cache_groups[$group_id])) {
					$group_name = $this->_cache_groups[$group_id];
				}
				else {
					$group = $this->group($group_id)->result();
					$group_name = $group[0]->name;
					$this->_cache_groups[$group_id] = $group_name;
				}
				$this->_cache_user_in_group[$user_id][$group_id] = $group_name;

				// Return the number of groups added
				$return += 1;
			}
		}

		return $return;
	}

	/**
	 * remove_from_group
	 *
	 * @return bool
	 * @author Ben Edmunds
	 **/
	public function remove_from_group($group_ids=false, $user_id=false)
	{
		$this->trigger_events('remove_from_group');

		// user id is required
		if(empty($user_id))
		{
			return FALSE;
		}

		// if group id(s) are passed remove user from the group(s)
		if( ! empty($group_ids))
		{
			if(!is_array($group_ids))
			{
				$group_ids = array($group_ids);
			}

			foreach($group_ids as $group_id)
			{
				$this->db->delete($this->tables['users_groups'], array($this->join['groups'] => (float)$group_id->id, $this->join['users'] => (float)$user_id));
				if (isset($this->_cache_user_in_group[$user_id]) && isset($this->_cache_user_in_group[$user_id][$group_id->id]))
				{
					unset($this->_cache_user_in_group[$user_id][$group_id->id]);
				}
			}

			$return = TRUE;
		}
		else
		{
			if ($return = $this->db->delete($this->tables['users_groups'], array($this->join['users'] => (float)$user_id))) {
				$this->_cache_user_in_group[$user_id] = array();
			}
		}
		return $return;
	}

	/**
	 * groups
	 *
	 * @return object
	 * @author Ben Edmunds
	 **/
	public function groups()
	{
		$this->trigger_events('groups');

		// run each where that was passed
		if (isset($this->_ion_where) && !empty($this->_ion_where))
		{
			foreach ($this->_ion_where as $where)
			{
				$this->db->where($where);
			}
			$this->_ion_where = array();
		}

		if (isset($this->_ion_limit) && isset($this->_ion_offset))
		{
			$this->db->limit($this->_ion_limit, $this->_ion_offset);

			$this->_ion_limit  = NULL;
			$this->_ion_offset = NULL;
		}
		else if (isset($this->_ion_limit))
		{
			$this->db->limit($this->_ion_limit);

			$this->_ion_limit  = NULL;
		}

		// set the order
		if (isset($this->_ion_order_by) && isset($this->_ion_order))
		{
			$this->db->order_by($this->_ion_order_by, $this->_ion_order);
		}

		$this->response = $this->db->get($this->tables['groups']);

		return $this;
	}

	/**
	 * group
	 *
	 * @return object
	 * @author Ben Edmunds
	 **/
	public function group($id = NULL)
	{
		$this->trigger_events('group');

		if (isset($id))
		{
			$this->where($this->tables['groups'].'.id', $id);
		}

		$this->limit(1);
		$this->order_by('id', 'desc');

		return $this->groups();
	}

	/**
	 * update
	 *
	 * @return bool
	 * @author Phil Sturgeon
	 **/
	public function update($id, array $data)
	{
		$this->trigger_events('pre_update_user');

		$user = $this->user($id)->row();

		$this->db->trans_begin();

		if (array_key_exists($this->identity_column, $data) && $this->identity_check($data[$this->identity_column]) && $user->{$this->identity_column} !== $data[$this->identity_column])
		{
			$this->db->trans_rollback();
			$this->set_error('account_creation_duplicate_identity');

			$this->trigger_events(array('post_update_user', 'post_update_user_unsuccessful'));
			$this->set_error('update_unsuccessful');

			return FALSE;
		}

		// Filter the data passed
		$data = $this->_filter_data($this->tables['users'], $data);

		if (array_key_exists($this->identity_column, $data) || array_key_exists('password', $data) || array_key_exists('email', $data))
		{
			if (array_key_exists('password', $data))
			{
				if( ! empty($data['password']))
				{
					$data['password'] = $this->hash_password($data['password'], $user->salt);
				}
				else
				{
					// unset password so it doesn't effect database entry if no password passed
					unset($data['password']);
				}
			}
		}

		$this->trigger_events('extra_where');
		$this->db->update($this->tables['users'], $data, array('id' => $user->id));

		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();

			$this->trigger_events(array('post_update_user', 'post_update_user_unsuccessful'));
			$this->set_error('update_unsuccessful');
			return FALSE;
		}

		$this->db->trans_commit();

		$this->trigger_events(array('post_update_user', 'post_update_user_successful'));
		$this->set_message('update_successful');
		return TRUE;
	}

	public function can_delete_user($id) {
		$user = $this->db->get_where('users', array('id' => $id))->row();
		if($user->first_name == "" && $user->last_name == "" && $user->active == 0) {
			return true;
		}
		return false;
	}

	/**
	* delete_user
	*
	* @return bool
	* @author Phil Sturgeon
	**/
	public function delete_user($id)
	{
		$this->trigger_events('pre_delete_user');

		$this->db->trans_begin();

		// remove user from emps, groups and setors
		$this->remove_from_empresas('', $id);
		$this->remove_from_group('', $id);
		$this->remove_from_setor('', $id);
		$this->remove_from_unid('', $id);

		$has_pessoa = $this->db->get_where('pessoas', array('user_id' => $id));
		if($has_pessoa->num_rows() > 0) {
			$this->db->delete('pessoas', array('user_id' => $id));
		}

		// delete user from users table should be placed after remove from group
		$this->db->delete($this->tables['users'], array('id' => $id));

		// if user does not exist in database then it returns FALSE else removes the user from groups
		if ($this->db->affected_rows() == 0)
		{
		    return FALSE;
		}

		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			$this->trigger_events(array('post_delete_user', 'post_delete_user_unsuccessful'));
			$this->set_error('delete_unsuccessful');
			return FALSE;
		}

		$this->db->trans_commit();

		$this->trigger_events(array('post_delete_user', 'post_delete_user_successful'));
		$this->set_message('delete_successful');
		return TRUE;
	}

	/**
	 * update_last_login
	 *
	 * @return bool
	 * @author Ben Edmunds
	 **/
	public function update_last_login($id)
	{
		$this->trigger_events('update_last_login');

		$this->load->helper('date');

		$this->trigger_events('extra_where');

		$this->db->update($this->tables['users'], array('last_login' => time()), array('id' => $id));

		return $this->db->affected_rows() == 1;
	}

	/**
	 * set_lang
	 *
	 * @return bool
	 * @author Ben Edmunds
	 **/
	public function set_lang($lang = 'en')
	{
		$this->trigger_events('set_lang');

		// if the user_expire is set to zero we'll set the expiration two years from now.
		if($this->config->item('user_expire', 'ion_auth') === 0)
		{
			$expire = (60*60*24*365*2);
		}
		// otherwise use what is set
		else
		{
			$expire = $this->config->item('user_expire', 'ion_auth');
		}

		set_cookie(array(
			'name'   => 'lang_code',
			'value'  => $lang,
			'expire' => $expire
		));

		return TRUE;
	}

	/**
	 * set_session
	 *
	 * @return bool
	 * @author jrmadsen67
	 **/
	public function set_session($user)
	{

		$this->trigger_events('pre_set_session');

		$session_data = array(
		    'identity'             => $user->{$this->identity_column},
		    $this->identity_column => $user->{$this->identity_column},
		    'email'                => $user->email,
		    'user_id'              => $user->id, //everyone likes to overwrite id so we'll use user_id
		    'old_last_login'       => $user->last_login,
		    'empresa_id'       	   => $this->get_empresa_inicial($user->id, $user->empresa_id),
		);

		$this->session->set_userdata($session_data);

		$this->trigger_events('post_set_session');

		return TRUE;
	}

	/**
	 * remember_user
	 *
	 * @return bool
	 * @author Ben Edmunds
	 **/
	public function remember_user($id)
	{
		$this->trigger_events('pre_remember_user');

		if (!$id)
		{
			return FALSE;
		}

		$user = $this->user($id)->row();

		$salt = $this->salt();

		$this->db->update($this->tables['users'], array('remember_code' => $salt), array('id' => $id));

		if ($this->db->affected_rows() > -1)
		{
			// if the user_expire is set to zero we'll set the expiration two years from now.
			if($this->config->item('user_expire', 'ion_auth') === 0)
			{
				$expire = (60*60*24*365*2);
			}
			// otherwise use what is set
			else
			{
				$expire = $this->config->item('user_expire', 'ion_auth');
			}

			set_cookie(array(
			    'name'   => $this->config->item('identity_cookie_name', 'ion_auth'),
			    'value'  => $user->{$this->identity_column},
			    'expire' => $expire
			));

			set_cookie(array(
			    'name'   => $this->config->item('remember_cookie_name', 'ion_auth'),
			    'value'  => $salt,
			    'expire' => $expire
			));

			$this->trigger_events(array('post_remember_user', 'remember_user_successful'));
			return TRUE;
		}

		$this->trigger_events(array('post_remember_user', 'remember_user_unsuccessful'));
		return FALSE;
	}

	/**
	 * login_remembed_user
	 *
	 * @return bool
	 * @author Ben Edmunds
	 **/
	public function login_remembered_user()
	{
		$this->trigger_events('pre_login_remembered_user');

		// check for valid data
		if (!get_cookie($this->config->item('identity_cookie_name', 'ion_auth'))
			|| !get_cookie($this->config->item('remember_cookie_name', 'ion_auth'))
			|| !$this->identity_check(get_cookie($this->config->item('identity_cookie_name', 'ion_auth'))))
		{
			$this->trigger_events(array('post_login_remembered_user', 'post_login_remembered_user_unsuccessful'));
			return FALSE;
		}

		// get the user				
		$this->trigger_events('extra_where');

		$query = $this->db->select('u.'.$this->identity_column . ', u.email, u.id, u.password, u.active, u.last_login, un.empresa_id')
				->join($this->tables['user_unidade'].' uu', 'uu.'.$this->join['users'].'= u.id')
				->join($this->tables['unidades'].' un', 'uu.'.$this->join['unidades'].'= un.id')
				->where('u.'.$this->identity_column, get_cookie($this->config->item('identity_cookie_name', 'ion_auth')))
				->where('u.remember_code', get_cookie($this->config->item('remember_cookie_name', 'ion_auth')))
				->limit(1)
				->order_by('u.id', 'desc')
				->get($this->tables['users'].' u');

		// if the user was found, sign them in
		if ($query->num_rows() == 1)
		{
			$user = $query->row();

			$this->update_last_login($user->id);

			$this->set_session($user);

			// extend the users cookies if the option is enabled
			if ($this->config->item('user_extend_on_login', 'ion_auth'))
			{
				$this->remember_user($user->id);
			}

			$this->trigger_events(array('post_login_remembered_user', 'post_login_remembered_user_successful'));
			return TRUE;
		}

		$this->trigger_events(array('post_login_remembered_user', 'post_login_remembered_user_unsuccessful'));
		return FALSE;
	}


	/**
	 * create_group
	 *
	 * @author aditya menon
	*/
	public function create_group($group_name = FALSE, $group_description = '', $additional_data = array())
	{
		// bail if the group name was not passed
		if(!$group_name)
		{
			$this->set_error('group_name_required');
			return FALSE;
		}

		// bail if the group name already exists
		// $existing_group = $this->db->get_where($this->tables['groups'], array('name' => $group_name))->num_rows();
		// if($existing_group !== 0)
		// {
		// 	$this->set_error('group_already_exists');
		// 	return FALSE;
		// }

		$data = array('name'=>$group_name,'description'=>$group_description);

		// filter out any data passed that doesnt have a matching column in the groups table
		// and merge the set group data and the additional data
		if (!empty($additional_data)) $data = array_merge($this->_filter_data($this->tables['groups'], $additional_data), $data);

		$this->trigger_events('extra_group_set');

		// insert the new group
		$this->db->insert($this->tables['groups'], $data);
		$group_id = $this->db->insert_id();

		// report success
		$this->set_message('group_creation_successful');
		// return the brand new group id
		return $group_id;
	}

	/**
	 * update_group
	 *
	 * @return bool
	 * @author aditya menon
	 **/
	public function update_group($group_id = FALSE, $group_name = FALSE, $additional_data = array())
	{
		if (empty($group_id)) return FALSE;

		$data = array();

		if (!empty($group_name))
		{
			// we are changing the name, so do some checks

			// bail if the group name already exists
			// $existing_group = $this->db->get_where($this->tables['groups'], array('name' => $group_name))->row();
			// if(isset($existing_group->id) && $existing_group->id != $group_id)
			// {
			// 	$this->set_error('group_already_exists');
			// 	return FALSE;
			// }

			$data['name'] = $group_name;
		}

		if (!empty($additional_data['permissoes']) && count($additional_data['permissoes']) > 0) {
			$menus_habilitados_empresa_atual = $this->get_menus_habilitados_empresa($additional_data['empresa_id'])->result();
			$menus = array();
	        foreach ($menus_habilitados_empresa_atual as $menu) {
	            $menus[] = $menu->controlador;
	        }
	        foreach ($additional_data['permissoes'] as $key => $permissao) {
	        	if(!in_array($key, $menus) && $key != "contatos_interno") {
	        		$this->set_error('permissao_not_exist_for_empresa');
					return FALSE;
	        	}
	        }
	        $additional_data['permissoes'] = serialize($additional_data['permissoes']);
        }

		// restrict change of name of the admin group
        $group = $this->db->get_where($this->tables['groups'], array('id' => $group_id))->row();
        if($this->config->item('admin_group', 'ion_auth') === $group->name && $group_name !== $group->name)
        {
            $this->set_error('group_name_admin_not_alter');
            return FALSE;
        }


		// IMPORTANT!! Third parameter was string type $description; this following code is to maintain backward compatibility
		// New projects should work with 3rd param as array
		if (is_string($additional_data)) $additional_data = array('description' => $additional_data);


		// filter out any data passed that doesnt have a matching column in the groups table
		// and merge the set group data and the additional data
		if (!empty($additional_data)) $data = array_merge($this->_filter_data($this->tables['groups'], $additional_data), $data);

		$this->db->update($this->tables['groups'], $data, array('id' => $group_id));

		$this->set_message('group_update_successful');

		return TRUE;
	}

	/**
	* delete_group
	*
	* @return bool
	* @author aditya menon
	**/
	public function delete_group($group_id = FALSE)
	{
		// bail if mandatory param not set
		if(!$group_id || empty($group_id))
		{
			return FALSE;
		}
		$group = $this->group($group_id)->row();
		if($group->name == $this->config->item('admin_group', 'ion_auth'))
		{
			$this->trigger_events(array('post_delete_group', 'post_delete_group_notallowed'));
			$this->set_error('group_delete_notallowed');
			return FALSE;
		}

		$this->trigger_events('pre_delete_group');

		$this->db->trans_begin();

		// remove all users from this group
		$this->db->delete($this->tables['users_groups'], array($this->join['groups'] => $group_id));
		// remove the group itself
		$this->db->delete($this->tables['groups'], array('id' => $group_id));

		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			$this->trigger_events(array('post_delete_group', 'post_delete_group_unsuccessful'));
			$this->set_error('group_delete_unsuccessful');
			return FALSE;
		}

		$this->db->trans_commit();

		$this->trigger_events(array('post_delete_group', 'post_delete_group_successful'));
		$this->set_message('group_delete_successful');
		return TRUE;
	}

	public function set_hook($event, $name, $class, $method, $arguments)
	{
		$this->_ion_hooks->{$event}[$name] = new stdClass;
		$this->_ion_hooks->{$event}[$name]->class     = $class;
		$this->_ion_hooks->{$event}[$name]->method    = $method;
		$this->_ion_hooks->{$event}[$name]->arguments = $arguments;
	}

	public function remove_hook($event, $name)
	{
		if (isset($this->_ion_hooks->{$event}[$name]))
		{
			unset($this->_ion_hooks->{$event}[$name]);
		}
	}

	public function remove_hooks($event)
	{
		if (isset($this->_ion_hooks->$event))
		{
			unset($this->_ion_hooks->$event);
		}
	}

	protected function _call_hook($event, $name)
	{
		if (isset($this->_ion_hooks->{$event}[$name]) && method_exists($this->_ion_hooks->{$event}[$name]->class, $this->_ion_hooks->{$event}[$name]->method))
		{
			$hook = $this->_ion_hooks->{$event}[$name];

			return call_user_func_array(array($hook->class, $hook->method), $hook->arguments);
		}

		return FALSE;
	}

	public function trigger_events($events)
	{
		if (is_array($events) && !empty($events))
		{
			foreach ($events as $event)
			{
				$this->trigger_events($event);
			}
		}
		else
		{
			if (isset($this->_ion_hooks->$events) && !empty($this->_ion_hooks->$events))
			{
				foreach ($this->_ion_hooks->$events as $name => $hook)
				{
					$this->_call_hook($events, $name);
				}
			}
		}
	}

	/**
	 * set_message_delimiters
	 *
	 * Set the message delimiters
	 *
	 * @return void
	 * @author Ben Edmunds
	 **/
	public function set_message_delimiters($start_delimiter, $end_delimiter)
	{
		$this->message_start_delimiter = $start_delimiter;
		$this->message_end_delimiter   = $end_delimiter;

		return TRUE;
	}

	/**
	 * set_error_delimiters
	 *
	 * Set the error delimiters
	 *
	 * @return void
	 * @author Ben Edmunds
	 **/
	public function set_error_delimiters($start_delimiter, $end_delimiter)
	{
		$this->error_start_delimiter = $start_delimiter;
		$this->error_end_delimiter   = $end_delimiter;

		return TRUE;
	}

	/**
	 * set_message
	 *
	 * Set a message
	 *
	 * @return void
	 * @author Ben Edmunds
	 **/
	public function set_message($message)
	{
		$this->messages[] = $message;

		return $message;
	}



	/**
	 * messages
	 *
	 * Get the messages
	 *
	 * @return void
	 * @author Ben Edmunds
	 **/
	public function messages()
	{
		$_output = '';
		foreach ($this->messages as $message)
		{
			$messageLang = $this->lang->line($message) ? $this->lang->line($message) : '##' . $message . '##';
			$_output .= $this->message_start_delimiter . $messageLang . $this->message_end_delimiter;
		}

		return $_output;
	}

	/**
	 * messages as array
	 *
	 * Get the messages as an array
	 *
	 * @return array
	 * @author Raul Baldner Junior
	 **/
	public function messages_array($langify = TRUE)
	{
		if ($langify)
		{
			$_output = array();
			foreach ($this->messages as $message)
			{
				$messageLang = $this->lang->line($message) ? $this->lang->line($message) : '##' . $message . '##';
				$_output[] = $this->message_start_delimiter . $messageLang . $this->message_end_delimiter;
			}
			return $_output;
		}
		else
		{
			return $this->messages;
		}
	}


	/**
	 * clear_messages
	 *
	 * Clear messages
	 *
	 * @return void
	 * @author Ben Edmunds
	 **/
	public function clear_messages()
	{
		$this->messages = array();

		return TRUE;
	}


	/**
	 * set_error
	 *
	 * Set an error message
	 *
	 * @return void
	 * @author Ben Edmunds
	 **/
	public function set_error($error)
	{
		$this->errors[] = $error;

		return $error;
	}

	/**
	 * errors
	 *
	 * Get the error message
	 *
	 * @return void
	 * @author Ben Edmunds
	 **/
	public function errors()
	{
		$_output = '';
		foreach ($this->errors as $error)
		{
			$errorLang = $this->lang->line($error) ? $this->lang->line($error) : '##' . $error . '##';
			$_output .= $this->error_start_delimiter . $errorLang . $this->error_end_delimiter;
		}

		return $_output;
	}

	/**
	 * errors as array
	 *
	 * Get the error messages as an array
	 *
	 * @return array
	 * @author Raul Baldner Junior
	 **/
	public function errors_array($langify = TRUE)
	{
		if ($langify)
		{
			$_output = array();
			foreach ($this->errors as $error)
			{
				$errorLang = $this->lang->line($error) ? $this->lang->line($error) : '##' . $error . '##';
				$_output[] = $this->error_start_delimiter . $errorLang . $this->error_end_delimiter;
			}
			return $_output;
		}
		else
		{
			return $this->errors;
		}
	}


	/**
	 * clear_errors
	 *
	 * Clear Errors
	 *
	 * @return void
	 * @author Ben Edmunds
	 **/
	public function clear_errors()
	{
		$this->errors = array();

		return TRUE;
	}



	protected function _filter_data($table, $data)
	{
		$filtered_data = array();
		$columns = $this->db->list_fields($table);

		if (is_array($data))
		{
			foreach ($columns as $column)
			{
				if (array_key_exists($column, $data))
					$filtered_data[$column] = $data[$column];
			}
		}

		return $filtered_data;
	}

	protected function _prepare_ip($ip_address) {
		// just return the string IP address now for better compatibility
		return $ip_address;
	}


	//à partir daqui foram adicionados novos métodos ------------------------------------------

	/**
	 * lista_sexo
	 *
	 * @return array
	 * @author Ellan Garbelini
	 **/
	public function lista_sexo()
	{        
		return $this->db->select('grupo, letterkey, descricao')
              ->where('grupo','Sexo')
              ->order_by('descricao', 'desc')
              ->get($this->tables['sys_lookup']);
    }

    /**
	 * lista_estado_civil
	 *
	 * @return array
	 * @author Ellan Garbelini
	 **/
	public function lista_estado_civil()
	{
		return $this->db->select('grupo, letterkey, descricao')
              ->where('grupo','EstadoCivil')
              ->order_by('descricao', 'desc')
              ->get($this->tables['sys_lookup']);
	}

	/**
	 * lista_estados
	 *
	 * @return array
	 * @author Ellan Garbelini
	 **/
	public function lista_estados()
	{
		return $this->db->select('uf, nome')
              ->get($this->tables['estado']);
	}

	/**
	 * lista_unidades
	 *
	 * @return array
	 * @author Ellan Garbelini
	 **/
	public function lista_unidades($data = array('select'=>'unidades.*', 'order_by'=>array('unidades.id', 'desc')))
	{
		$query = $this->db->select($data['select'])->distinct();
        if(@$data['join']) {
            foreach ($data['join'] as $join) {
                $query->join($join[0], $join[1]);
            }
        }
        if(@$data['where']) {
            $query->where($data['where']);
        }
        $query->order_by($data['order_by'][0], $data['order_by'][1]);
        return $query->get($this->tables['unidades']);
	}

	/**
	 * get_empresa
	 *
	 * @return array
	 * @author Ellan Garbelini
	 **/
	public function get_empresa($id)
	{
		return $this->db->select('nome')
            ->where('id', $id)
            ->get($this->tables['empresas']);
	}

	/**
	 * get_empresas_user
	 *
	 * @return array
	 * @author Ellan Garbelini
	 **/
	public function get_empresas_user($user_id = null)
	{
		return $this->db->select($this->join['empresas'])
            ->where($this->join['users'], $user_id)
            ->get($this->tables['empresas_users']);

        /*return $this->db->select($this->join['empresas'])
        	->distinct()
        	->join('unidades','unidades.id=user_unidade.unidade_id')
            ->where('user_unidade.user_id', $user_id)
            ->get($this->tables['user_unidade']);*/
	}

	/**
	 * get_empresas_user_to_update
	 *
	 * @return array
	 * @author Ellan Garbelini
	 **/
	public function get_empresas_user_to_update($user_id = null)
	{
		$this->db->select($this->join['empresas'])
            ->where($this->join['users'], $user_id);

        if (isset($this->_ion_where) && !empty($this->_ion_where))
		{
			foreach ($this->_ion_where as $where)
			{
				$this->db->where($where);
			}

			$this->_ion_where = array();
		}

        return $this->db->get($this->tables['empresas_users']);
	}

	/**
	 * get_empresas_user_to_sidebar
	 *
	 * @return array
	 * @author Ellan Garbelini
	 **/
	public function get_empresas_user_to_sidebar($user_id = null)
	{
        return $this->db->select($this->join['empresas'].', empresas.nome as nome_empresa')
        	->distinct()
        	->join('unidades','unidades.id=user_unidade.unidade_id')
        	->join('empresas','empresas.id=unidades.empresa_id')
            ->where('user_unidade.user_id', $user_id)
            ->get($this->tables['user_unidade']);
	}

	/**
	 * get_empresas_unidades_user
	 *
	 * @return array
	 * @author Ellan Garbelini
	 **/
	public function get_empresas_unidades_user($user_id = null)
	{
		return $this->db->select($this->tables['empresas'].'.nome, '.$this->tables['unidades'].'.unidade, '.$this->tables['unidades'].'.id, '.$this->tables['user_unidade'].'.padrao')
			->join($this->tables['unidades'], $this->tables['unidades'].'.'.$this->join['empresas'].'='.$this->tables['empresas'].'.id')
			->join($this->tables['user_unidade'], $this->tables['user_unidade'].'.'.$this->join['unidades'].'='.$this->tables['unidades'].'.id')
            ->where($this->tables['user_unidade'].'.user_id', $user_id)
            ->where($this->tables['empresas'].'.id', $this->session->userdata('empresa_id'))
          	->order_by($this->tables['unidades'].'.unidade', 'asc')
            ->get($this->tables['empresas']);
	}

	/**
	 * get_users_unid
	 *
	 * @return array
	 * @author Ellan Garbelini
	 **/
	public function get_users_unid($id=FALSE)
	{
		$this->trigger_events('get_users_unid');

		// if no id was passed use the current users id
		$id || $id = $this->session->userdata('user_id');

		return $this->db->select('*')
		                ->where($this->tables['user_unidade'].'.'.$this->join['users'], $id)
		                ->get($this->tables['user_unidade']);
	}

	/**
	 * get_users_setores
	 *
	 * @return array
	 * @author Ellan Garbelini
	 **/
	public function get_users_setores($id=FALSE, $id_empresa=FALSE)
	{
		$id || $id = $this->session->userdata('user_id');

		$id_empresa || $id_empresa = $this->session->userdata('empresa_id');

		return $this->db->select('*')
		                ->join($this->tables['setores'], $this->tables['users_setores'].'.'.$this->join['setores'].'='.$this->tables['setores'].'.id')
		                ->where($this->tables['setores'].'.empresa_id', $id_empresa)
		                ->where($this->tables['users_setores'].'.'.$this->join['users'], $id)
		                ->get($this->tables['users_setores']);
	}

	/**
	 * lista_setores
	 *
	 * @return array
	 * @author Ellan Garbelini
	 **/
	public function lista_setores($where)
	{
		$query = $this->db->select('*')->where('actived = "1"');
		if($where)
			$query = $this->db->where($where);
		$query = $this->db->order_by('descricao', 'asc')->get($this->tables['setores']);
		return $query;
	}

	/**
	 * add_to_unid
	 *
	 * @return bool
	 * @author Ellan Garbelini
	 **/
	public function add_to_unid($clients_ids, $user_id=false)
	{
		$this->trigger_events('add_to_unid');

		// if no id was passed use the current users id
		$user_id || $user_id = $this->session->userdata('user_id');

		if(!is_array($clients_ids))
		{
			$clients_ids = array($clients_ids);
		}

		$return = 0;

		// Then insert each into the database
		foreach ($clients_ids as $cliente_id)
		{			
			if ($this->db->insert($this->tables['user_unidade'], array( $this->join['unidades'] => (float)$cliente_id, $this->join['users'] => (float)$user_id)))
			{
				$return += 1;
			}
		}

		return $return;
	}

	/**
	 * add_to_setor
	 *
	 * @return bool
	 * @author Ellan Garbelini
	 **/
	public function add_to_setor($setores_ids, $user_id=false)
	{
		$this->trigger_events('add_to_setor');

		// if no id was passed use the current users id
		$user_id || $user_id = $this->session->userdata('user_id');

		if(!is_array($setores_ids))
		{
			$setores_ids = array($setores_ids);
		}

		$return = 0;

		// Then insert each into the database
		foreach ($setores_ids as $setor_id)
		{	
			if($setor_id) {
				if ($this->db->insert($this->tables['users_setores'], array( "user_id" => (float)$user_id, "setor_id" => (float)$setor_id)))
				{
					$return += 1;
				}
			}
		}

		return $return;
	}

	/**
	 * remove_from_unid
	 *
	 * @return bool
	 * @author Ellan Garbelini
	 **/
	public function remove_from_unid($clients_ids=false, $user_id=false)
	{
		$this->trigger_events('remove_from_unid');

		// user id is required
		if(empty($user_id))
		{
			return FALSE;
		}

		// if group id(s) are passed remove user from the group(s)
		if( ! empty($clients_ids))
		{
			if(!is_array($clients_ids))
			{
				$clients_ids = array($clients_ids);
			}

			foreach($clients_ids as $client_id)
			{
				$this->db->delete($this->tables['user_unidade'], array($this->join['unidades'] => (float)$client_id->id, $this->join['users'] => (float)$user_id));
			}

			$return = TRUE;
		}
		// otherwise remove user from all groups
		else
		{
			$return = $this->db->delete($this->tables['user_unidade'], array($this->join['users'] => (float)$user_id));
		}
		return $return;
	}

	/**
	 * remove_from_setor
	 *
	 * @return bool
	 * @author Ellan Garbelini
	 **/
	public function remove_from_setor($setor_ids=false, $user_id=false)
	{
		if(empty($user_id))
		{
			return FALSE;
		}

		if(!empty($setor_ids)) {
			if(!is_array($setor_ids))
			{
				$setor_ids = array($setor_ids);
			}

			foreach($setor_ids as $setor_id)
			{
				$this->db->delete($this->tables['users_setores'], array($this->join['setores'] => (float)$setor_id->id, $this->join['users'] => (float)$user_id));
			}
		} else {
			$this->db->delete($this->tables['users_setores'], array($this->join['users'] => (float)$user_id));
		}

		$return = TRUE;
		return $return;
	}

	/**
	 * remove_from_empresas
	 *
	 * @return bool
	 * @author Ellan Garbelini
	 **/
	public function remove_from_empresas($emp_ids=false, $user_id=false)
	{
		if(empty($user_id))
		{
			return FALSE;
		}

		if(!empty($emp_ids)) {
			if(!is_array($emp_ids))
			{
				$emp_ids = array($emp_ids);
			}

			foreach($emp_ids as $emp_id)
			{
				$this->db->delete($this->tables['empresas_users'], array($this->join['empresas'] => (float)$emp_id->empresa_id, $this->join['users'] => (float)$user_id));
			}
		} else {
			$this->db->delete($this->tables['empresas_users'], array($this->join['users'] => (float)$user_id));
		}

		$return = TRUE;
		return $return;
	}

	/**
	 * add_to_empresas_users
	 *
	 * @return bool
	 * @author Ellan Garbelini
	 **/
	public function add_to_empresas_users($empresa_id, $user_id=false)
	{
		$return = $this->db->insert($this->tables['empresas_users'], array( $this->join['empresas'] => (float)$empresa_id, $this->join['users'] => (float)$user_id));
		return $return;
	}

	public function update_user_info($empresas_user_array, $emp_data, $group_data, $unid_data, $setor_data, $user_id) {
    	
		$empresas_registradas = $this->get_empresas_user_to_update($user_id);
		if (isset($emp_data) && !empty($emp_data))
		{
			$this->where("empresa_id IN (".implode(",", $empresas_user_array).")");
			$this->remove_from_empresas($empresas_registradas->result(), $user_id);

			foreach ($emp_data as $key => $emp)
			{
				$this->add_to_empresas_users($key, $user_id);
			}
		} else {
			$this->remove_from_empresas($empresas_registradas->result(), $user_id);
		}

		// Sincroniza os grupos mesmo quando nenhum vem marcado, para permitir tirar todos.
		// Só mexe nos grupos das empresas do usuário logado; remove_from_group() com lista vazia apagaria todos.
		if (!empty($empresas_user_array))
        {
    		$this->where("empresa_id IN (".implode(",", array_map('intval', $empresas_user_array)).")");
        	$grupos_delete = $this->groups()->result();

			if (!empty($grupos_delete))
			{
				$this->remove_from_group($grupos_delete, $user_id);
			}

			foreach ((array) $group_data as $grp)
            {
				$this->add_to_group($grp, $user_id);
			}
		}

		if (isset($unid_data) && !empty($unid_data))
        {
        	$unidades_usuario_logado = $this->lista_unidades(array(
				'select'=>'unidades.id', 
				'join'=>array(
					array('empresas','unidades.empresa_id=empresas.id'), 
					array('user_unidade','user_unidade.unidade_id=unidades.id')
				), 
				'where'=>'user_unidade.user_id = '.$this->session->userdata('user_id'), 
				'order_by'=>array('unidades.id','desc')))->result();
        	$unidades_usuario_logado_array = [];
        	foreach ($unidades_usuario_logado as $value) {
        		$unidades_usuario_logado_array[] = $value->id;
        	}
        	$unids_delete = $this->lista_unidades(array(
				'select'=>'unidades.id', 
				'join'=>array(
					array('user_unidade','user_unidade.unidade_id=unidades.id')
				), 
				'where'=>'unidades.empresa_id IN ('.implode(",", $empresas_user_array).') AND user_unidade.user_id = '.$user_id.' AND unidades.id IN ('.implode(",", $unidades_usuario_logado_array).')', 
				'order_by'=>array('unidades.id','desc')))->result();

			$this->remove_from_unid($unids_delete, $user_id);

			foreach ($unid_data as $unid)
            {
				$this->add_to_unid($unid, $user_id);
			}
		}

		if (isset($setor_data) && !empty($setor_data))
        {
        	$setores_delete = $this->lista_setores("empresa_id IN (".implode(",", $empresas_user_array).")")->result();
			$this->remove_from_setor($setores_delete, $user_id);

			foreach ($setor_data as $str)
            {
				// "Nenhum" no select do setor chega vazio: só limpa o setor daquela empresa.
				if ($str === '' || $str === null) { continue; }
				$this->add_to_setor($str, $user_id);
			}
		}

		return true;
	}

	/**
	 * code_is_valid
	 *
	 * @return bool
	 * @author Ellan Garbelini
	 **/
	public function code_is_valid($id, $code)
	{
		$query = $this->db->select($this->identity_column)
			->where('activation_code', $code)
			->where('id', $id)
			->limit(1)
			->order_by('id', 'desc')
			->get($this->tables['users']);

		$result = $query->row();

		if ($query->num_rows() == 1) {
			return true;
		} else {
			$this->set_error('invalid_code');
			return false;
		}		
	}

	/**
	 * update_session
	 *
	 * @return bool
	 * @author Ellan Garbelini
	 **/
	public function update_session($empresa_id)
	{
		$this->session->set_userdata('empresa_id', $empresa_id);
		$this->registrar_ultima_empresa($this->session->userdata('user_id'), $empresa_id);
		return TRUE;
	}

	/**
	 * get_empresa_inicial
	 *
	 * Empresa carregada no login: a última que o usuário escolheu no seletor de empresas.
	 * Sem escolha registrada, mantém a empresa encontrada no login ($fallback).
	 *
	 * @return int
	 **/
	public function get_empresa_inicial($user_id, $fallback)
	{
		if (!$this->has_ultimo_acesso_empresa()) {
			return $fallback;
		}
		$row = $this->db->select('empresa_id')
			->where('user_id', (int) $user_id)
			->where('ultimo_acesso IS NOT NULL', null, false)
			->order_by('ultimo_acesso', 'desc')
			->limit(1)
			->get($this->tables['empresas_users'])
			->row();
		return $row ? (int) $row->empresa_id : $fallback;
	}

	/**
	 * registrar_ultima_empresa
	 *
	 * @return bool
	 **/
	public function registrar_ultima_empresa($user_id, $empresa_id)
	{
		if (!$user_id || !$empresa_id || !$this->has_ultimo_acesso_empresa()) {
			return FALSE;
		}
		return $this->db->where('user_id', (int) $user_id)
			->where('empresa_id', (int) $empresa_id)
			->update($this->tables['empresas_users'], array('ultimo_acesso' => date('Y-m-d H:i:s')));
	}

	// Permite publicar o código antes de executar install/sql/empresa_ultimo_acesso.sql.
	private function has_ultimo_acesso_empresa()
	{
		static $exists = null;
		if ($exists === null) {
			$exists = $this->db->field_exists('ultimo_acesso', $this->tables['empresas_users']);
		}
		return $exists;
	}

	/**
	 * valida_empresa_usuario
	 *
	 * @return bool
	 * @author Ellan Garbelini
	 **/
	public function valida_empresa_usuario($empresa_id)
	{
		/*$query = $this->db->select('u.empresa_id')
			->join($this->tables['unidades'].' u', 'uu.unidade_id = u.id')
          	->where('u.empresa_id', $empresa_id)
            ->where('uu.user_id', $this->session->userdata('user_id'))
            ->limit(1)
            ->get($this->tables['user_unidade'].' uu');*/
        $query = $this->db->select('id')
          	->where('empresa_id', $empresa_id)
            ->where('user_id', $this->session->userdata('user_id'))
            ->limit(1)
            ->get('empresas_users');
		$result = $query->row();

		if ($query->num_rows() == 1) {
			return true;
		} else {
			return false;
		}		
	}

	/**
	 * get_menus_habilitados_empresa
	 *
	 * @return array
	 * @author Ellan Garbelini
	 **/
	public function get_menus_habilitados_empresa($empresa_id)
	{
		return $this->db->select('m.controlador')
			->join('menus m', 'm.id = me.menus_id')
          	->where('me.empresas_id', $empresa_id)
            ->get('menus_empresas me');
    }

    public function get_admin_preferences($emp_id = null) {
    	if($emp_id == null)
    		$emp_id = $this->session->userdata('empresa_id');

    	return $this->db->get_where('admin_preferences', array('empresa_id' => $emp_id))->row();
    }

    public function has_pessoa($user_id) {
    	return $this->db->get_where('pessoas', array('user_id' => $user_id))->num_rows();
    }

	/**
	 * is_aluno
	 *
	 * @return bool
	 * @author Ben Edmunds
	 **/
	public function is_aluno($id=false)
	{
		$groups = array();

		if($id == false)
			return false;

		$query_aluno_grupo = $this->get_admin_preferences();
		if($query_aluno_grupo->grupo_aluno != "") {
			$groups = $this->get_users_groups($id);
			if($groups->num_rows() > 0) {
				foreach ($groups->result() as $g) {
					if($g->id == $query_aluno_grupo->grupo_aluno)
						return true;
				}
			}
		}
		return false;
	}

	/**
	 * is_aluno_ead
	 *
	 * @return bool
	 * @author Ellan Garbelini
	 **/
	public function is_aluno_ead($id=false)
	{
		if($id == false)
			return false;

		$query = $this
                ->db
                ->select('eu.*')
                ->join("cad_orca o", "o.user_id = eu.user_id")
                ->join("unidades u", "u.id = o.filial")
        		->where("eu.user_id", $id)
        		->where("u.empresa_id", $this->session->userdata('empresa_id'))
                ->get('ead_users eu');
        
        if($query->num_rows()>0)
        {
            return true; 
        }
        else
        {
            return false;
        }
	}

	/**
	 * is_tutor_ead
	 *
	 * @return bool
	 * @author Ellan Garbelini
	 **/
	public function is_tutor_ead($id=false)
	{
		$groups = array();

		if($id == false)
			return false;

		$query_tutor_grupo = $this->get_admin_preferences();
		if($query_tutor_grupo->grupo_tutor != "") {
			$groups = $this->get_users_groups($id);
			if($groups->num_rows() > 0) {
				foreach ($groups->result() as $g) {
					if($g->id == $query_tutor_grupo->grupo_tutor)
						return true;
				}
			}
		}
		return false;
	}

	public function get_tutoria_ead($orc_id) {
		$query = $this
                ->db
                ->select('t.*')
                ->join("cad_orca o", "t.orc_id = o.id")
                ->join("unidades u", "u.id = o.filial")
        		->where("u.empresa_id", $this->session->userdata('empresa_id'))
        		->where("o.id", $orc_id)
        		->order_by('t.data_hora_fim', 'asc')
                ->get('tutoria t');
        return $query;
	}

	public function get_timeline_profile($id) {
		$query = $this
                ->db
                ->select('a.*, CONCAT(u.first_name, " ", u.last_name) as nome')
                ->join("users u", "u.id = a.LIG_ID_USUARIO")
        		->where("a.empresa_id", $this->session->userdata('empresa_id'))
        		->where("a.LIG_ID_CLIENTE", $id)
        		->order_by('a.DATA_REGISTRO, a.HORA_REGISTRO', 'desc')
        		->limit(5)
                ->get('cad_clie_auditoria a');
        return $query;
	}

	public function has_pesquisa($contrato_id) {
        $num_rows = $this->db->get_where('pesquisa_perfil', array("orc_id" => $contrato_id))->num_rows();
        if($num_rows > 0)
            return true;
        else
            return false;
    }

	public function is_recorrencia($contrato_id, $user_id) {
		/*$num_rows = $this
                ->db
                ->select('c.iugu_id')
                ->join("iugu_users u", "u.iugu_id = c.iugu_customer_id")
                ->join("iugu_subscriptions s", "s.cad_orca_id = ".$contrato_id)
        		->where("u.user_id", $user_id)
                ->get('iugu_users_cc c')->num_rows();*/
        $num_rows = $this
                ->db
                ->select('s.iugu_id')
                ->join("iugu_subscriptions s", "s.cad_orca_id = c.id")
        		->where("c.id", $contrato_id)
                ->get('cad_orca c')->num_rows();
        if($num_rows > 0)
            return true;
        else
            return false;
    }

	public function is_assinatura($contrato_id) {
		$num_rows = $this
                ->db
                ->select('s.iugu_id')
                ->join("iugu_subscriptions s", "s.cad_orca_id = c.id")
        		->where("c.id", $contrato_id)
                ->get('cad_orca c')->num_rows();
        if($num_rows > 0)
            return true;
        else
            return false;
    }

    public function get_pesquisa($contrato_id) {
        $return = $this->db->get_where('pesquisa_perfil', array("orc_id" => $contrato_id))->row();
        return $return;
    }

	/**
	 * is_vendedor
	 *
	 * @return bool
	 * @author Ben Edmunds
	 **/
	public function is_vendedor($id=false)
	{
		$groups = array();

		if($id == false)
			return false;

		$query_vendedor_grupo = $this->get_admin_preferences();
		if($query_vendedor_grupo->grupo_vendedor != "") {
			$groups = $this->get_users_groups($id);
			if($groups->num_rows() > 0) {
				foreach ($groups->result() as $g) {
					if($g->id == $query_vendedor_grupo->grupo_vendedor)
						return true;
				}
			}
		}
		return false;
	}

	/**
	 * Unidades da empresa às quais o usuário logado está vinculado (limitam a listagem de usuários).
	 */
	public function unidades_visiveis_usuarios()
	{
		$ids = array();
		foreach ($this->db->select('uu.unidade_id, u.unidade')
			->join('unidades u', 'u.id = uu.unidade_id')
			->where('u.empresa_id', (int) $this->session->userdata('empresa_id'))
			->where('uu.user_id', (int) $this->session->userdata('user_id'))
			->order_by('u.unidade', 'asc')
			->get('user_unidade uu')->result() as $row) {
			$ids[(int) $row->unidade_id] = $row->unidade;
		}
		return $ids;
	}

	/**
	 * Listagem de usuários com filtros. $filtros: unidades (int[] permitidas), unidade, grupo, status (ativo|inativo|pendente), q.
	 */
	public function usuarios_lista($filtros, $limit, $start, $ordem, $direcao)
	{
		$colunas = array('id' => 'users.id', 'nome' => 'users.first_name', 'ultimo_acesso' => 'users.last_login', 'status' => 'users.active');
		$this->_filtros_usuarios_lista($filtros);
		return $this->db->select('users.id, users.first_name, users.last_name, users.email, users.active, users.activation_code, users.last_login, users.created_on')
			->order_by(isset($colunas[$ordem]) ? $colunas[$ordem] : 'users.first_name', $direcao === 'desc' ? 'desc' : 'asc')
			->order_by('users.id', 'asc')
			->limit(max(1, min(100, (int) $limit)), max(0, (int) $start))
			->get()
			->result();
	}

	public function usuarios_lista_total($filtros)
	{
		$this->_filtros_usuarios_lista($filtros);
		return $this->db->count_all_results();
	}

	private function _filtros_usuarios_lista($filtros)
	{
		$unidades = !empty($filtros['unidade']) ? array((int) $filtros['unidade']) : $filtros['unidades'];
		$unidades = $unidades ? array_map('intval', $unidades) : array(0);
		$this->db->from('users')
			->where('users.id IN (SELECT uu.user_id FROM user_unidade uu WHERE uu.unidade_id IN (' . implode(',', $unidades) . '))', null, false);
		if (!empty($filtros['grupo'])) {
			$this->db->where('users.id IN (SELECT ug.user_id FROM users_groups ug WHERE ug.group_id = ' . (int) $filtros['grupo'] . ')', null, false);
		}
		if (isset($filtros['status']) && $filtros['status'] === 'ativo') {
			$this->db->where('users.active', 1);
		} elseif (isset($filtros['status']) && $filtros['status'] === 'inativo') {
			$this->db->where('users.active', 0);
		} elseif (isset($filtros['status']) && $filtros['status'] === 'pendente') {
			$this->db->where('users.active', 0)->where("users.activation_code IS NOT NULL AND users.activation_code <> ''", null, false);
		}
		if (!empty($filtros['q'])) {
			$termo = $this->db->escape_like_str($filtros['q']);
			$this->db->where("(CONCAT(users.first_name, ' ', users.last_name) LIKE '%" . $termo . "%' ESCAPE '!' OR users.email LIKE '%" . $termo . "%' ESCAPE '!'" .
				(ctype_digit((string) $filtros['q']) ? ' OR users.id = ' . (int) $filtros['q'] : '') . ')', null, false);
		}
	}

	/**
	 * Grupos (da empresa logada) dos usuários informados, agrupados por usuário.
	 */
	public function grupos_dos_usuarios($user_ids)
	{
		$grupos = array();
		if (!$user_ids) {
			return $grupos;
		}
		foreach ($this->db->select('ug.user_id, g.id, g.name, g.bgcolor')
			->from('users_groups ug')
			->join('groups g', 'g.id = ug.group_id')
			->where('g.empresa_id', (int) $this->session->userdata('empresa_id'))
			->where_in('ug.user_id', array_map('intval', $user_ids))
			->order_by('g.name', 'asc')
			->get()->result() as $row) {
			$grupos[(int) $row->user_id][] = $row;
		}
		return $grupos;
	}

	function allserver_side_count()
    {   
    	$query_unids = $this->db->select('uu.*')
            ->join('unidades u', 'u.id = uu.unidade_id')
            ->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->where('uu.user_id', $this->session->userdata('user_id'))
            ->get('user_unidade uu');
        $unids = array(0);
        foreach ($query_unids->result() as $value) {
            $unids[] = $value->unidade_id;
        }

        $query = $this
            ->db
            ->select('users.id')
            ->join('user_unidade', 'user_unidade.user_id = users.id', 'left')
    		->where('user_unidade.unidade_id IN ('.implode(",",$unids).')')
            ->group_by('users.id')
            ->get('users');
    
        return $query->num_rows();  

    }
    
    function allserver_side($limit,$start,$col,$dir)
    {   
    	$query_unids = $this->db->select('uu.*')
            ->join('unidades u', 'u.id = uu.unidade_id')
            ->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->where('uu.user_id', $this->session->userdata('user_id'))
            ->get('user_unidade uu');
        $unids = array(0);
        foreach ($query_unids->result() as $value) {
            $unids[] = $value->unidade_id;
        }

       	$query = $this
                ->db
                ->select('users.*, users.id as user_id')
	            ->join('user_unidade', 'user_unidade.user_id = users.id', 'left')
	    		->where('user_unidade.unidade_id IN ('.implode(",",$unids).')')
                ->limit($limit,$start)
                ->group_by('users.id')
                ->order_by($col,$dir)
                ->get('users');
        
        if($query->num_rows()>0)
        {
            return $query->result(); 
        }
        else
        {
            return null;
        }
        
    }
   
    function server_side_search($limit,$start,$search,$col,$dir)
    {
    	$query_unids = $this->db->select('uu.*')
            ->join('unidades u', 'u.id = uu.unidade_id')
            ->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->where('uu.user_id', $this->session->userdata('user_id'))
            ->get('user_unidade uu');
        $unids = array(0);
        foreach ($query_unids->result() as $value) {
            $unids[] = $value->unidade_id;
        }

        $query = $this
                ->db
                ->select("users.*, users.id as user_id, CONCAT(first_name, ' ', last_name) as 'full_name'")
	            ->join('user_unidade', 'user_unidade.user_id = users.id', 'left')
	    		->where('user_unidade.unidade_id IN ('.implode(",",$unids).')')
                ->where("(CONCAT(first_name, ' ', last_name) LIKE '%".$search."%' OR email LIKE '%".$search."%' OR users.id LIKE '%".$search."%')")
                ->limit($limit,$start)
                ->group_by('users.id')
                ->order_by($col,$dir)
                ->get('users');

        if($query->num_rows()>0)
        {
            return $query->result();  
        }
        else
        {
            return null;
        }
    }

    function server_side_search_count($search)
    {
    	$query_unids = $this->db->select('uu.*')
            ->join('unidades u', 'u.id = uu.unidade_id')
            ->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->where('uu.user_id', $this->session->userdata('user_id'))
            ->get('user_unidade uu');
        $unids = array(0);
        foreach ($query_unids->result() as $value) {
            $unids[] = $value->unidade_id;
        }

        $query = $this
                ->db
                ->select('users.id')
	            ->join('user_unidade', 'user_unidade.user_id = users.id', 'left')
	    		->where('user_unidade.unidade_id IN ('.implode(",",$unids).')')
                ->where("(CONCAT(first_name, ' ', last_name) LIKE '%".$search."%' OR email LIKE '%".$search."%' OR users.id LIKE '%".$search."%')")
                ->or_like('email',$search)
                ->group_by('users.id')
                ->get('users');
    
        return $query->num_rows();
    }

    public function get_historico($user_id = null)
    {
        if($user_id == null)
            return false;

        return $this->db->select('h.*, u.first_name as nome_usu, u.last_name as sobrenome_usu, c.first_name as nome_cli, c.last_name as sobrenome_cli, s.descricao as status_desc')
            ->join('users c', 'c.id = h.LIG_ID_CLIENTE')
            ->join('users u', 'u.id = h.LIG_ID_USUARIO')
            ->join('users_historicos_status s', 's.id = h.STATUS')
            ->where('h.LIG_ID_CLIENTE', $user_id)
            ->where('h.empresa_id', $this->session->userdata('empresa_id'))
            ->order_by('DATA_REGISTRO', 'DESC')
            ->order_by('HORA_REGISTRO', 'DESC')
            ->get("cad_clie_auditoria h");
    }

    public function get_historico_status()
    {
        return $this->db->select('*')
            ->where('empresa_id', $this->session->userdata('empresa_id'))
            ->where('status', 1)
            ->order_by('descricao', 'asc')
            ->get("users_historicos_status");
    }

    public function historico_create($data = array())
    {
    	$data["empresa_id"] = $this->session->userdata('empresa_id');
        if($this->db->insert('cad_clie_auditoria', $data)) {
            $id = $this->db->insert_id();
            $this->set_message('creation_historico_successful');
        } else {
            $this->set_error('creation_historico_unsuccessful');
            return FALSE;
        }
        return $id;
    }

    public function update_pessoas($user_id, $data) {
    	if($this->db->get_where('pessoas', array('user_id' => $user_id))->num_rows() == 0) {
    		$data['user_id'] = $user_id;
    		$this->db->insert('pessoas', $data);
    	} else {
	    	if($this->db->update('pessoas', $data, array('user_id' => $user_id))) {
	    		$this->set_message('update_pessoas_successful');
	    		return TRUE;
	    	} else {
	    		$this->set_error('update_pessoas_unsuccessful');
	    		return FALSE;
	    	}
    	}
    }

    public function get_pessoa($user_id = null)
    {
        if($user_id == null)
            return false;

        return $this->db->select('*')
            ->where('user_id', $user_id)
            ->get("pessoas");
    }

    public function get_contratos_ead($user_id = null)
    {        
    	if($user_id == null)
            return false;

        $query = $this->db->select('c.*, u.first_name, u.last_name, un.unidade, cu.DESCRICAO as curso_desc, s.descricao as status_desc, ecs.segment_id')
            ->join('users u', 'c.user_id = u.id')
            ->join('unidades un', 'un.id = c.filial')
            ->join('curso cu', 'cu.ID_CURSO = c.lig_id_curso')
            ->join('cad_status s', 's.id = c.status')
            ->join('ead_contratos_segments ecs', 'ecs.contrato_id = c.id', 'left')
            ->where('c.tipo_saxweb', '2')
            ->where('c.user_id', $user_id)
            ->where('un.empresa_id', $this->session->userdata('empresa_id'));
        
        $query->order_by('c.id', 'desc');
        return $query->group_by('c.id')
            ->get("cad_orca c");
    }

    public function create_sms($data = array())
    {
        $this->db->insert('envios_sms', $data);
        $id = $this->db->insert_id();
        return $id;
    }

    public function create_auditoria($data = array())
    {
        if($this->db->insert('auditorias', $data)) {
            $id = $this->db->insert_id();
        	return $id;
        } else {
            return FALSE;
        }
    }

    public function get_email_config($empresa_id = null)
    {
    	if($empresa_id == null)
    		$empresa_id = $this->session->userdata('empresa_id');

        return $this->db->select('*')
            ->where('empresa_id', $empresa_id)
            ->get("config_email");
    }

    public function get_email_padrao($tipo_email, $empresa_id = null)
    {
		if($empresa_id == null)
    		$empresa_id = $this->session->userdata('empresa_id');

        return $this->db->select('*')
            ->where('empresa_id', $empresa_id)
            ->where('tipo_email', $tipo_email)
            ->get("email_padrao");
    }

    /**
	 * get_condicoes
	 *
	 * @return array
	 * @author Ellan Garbelini
	 **/
	public function get_condicoes($user_id = null)
	{
		$query_unids = $this->db->select('uu.*')
            ->join('unidades u', 'u.id = uu.unidade_id')
            ->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->where('uu.user_id', $this->session->userdata('user_id'))
            ->get('user_unidade uu');
        $unids = array(0);
        foreach ($query_unids->result() as $value) {
        	$unids[] = $value->unidade_id;
        }

		$query = $this->db->select('c.*, p.DESCRICAO as pag_desc, r.NOME, u.unidade')
			->join('cad_fpgt p', 'p.CODIGO = c.Id_formpag')
			->join('tip_rece r', 'r.CODIGO = c.Id_TipoReceb')
			->join('unidades u', 'u.id = c.unidade_id')
			->where("c.unidade_id IN (".implode(",", $unids).")");

		if($user_id != null) {
			$query->join('user_condicao uc', 'uc.condicao_id = c.ID')
				->where("uc.user_id", $user_id);
		}

		$query = $this->db->order_by('c.DESCRICAO', 'asc')->get("tipo_condicao_parcela_orcamento c");
		return $query;
	}

	/**
	 * get_users_cond
	 *
	 * @return array
	 * @author Ellan Garbelini
	 **/
	public function get_users_cond($id=FALSE)
	{
		$id || $id = $this->session->userdata('user_id');

		return $this->db->select('*')
            ->where('user_id', $id)
            ->get('user_condicao');
	}

	public function get_unidades_permitidas() {
        return $this->db->select('uu.unidade_id, u.unidade, u.razao_social')
            ->join('unidades u', 'u.id = uu.unidade_id')
            ->join('empresas e', 'e.id = u.empresa_id')
            ->where('uu.user_id', $this->session->userdata('user_id'))
            ->where('e.id', $this->session->userdata('empresa_id'))
            ->get('user_unidade uu');
    }

    public function remove_user_cond($user_id = null) {
    	if($user_id == null)
    		return false;
    	$this->db->delete('user_condicao', array('user_id' => $user_id));
    }

    public function insert_user_cond($data = array()) {
    	if(!$data)
    		return false;
    	$this->db->insert_batch('user_condicao', $data);
    }



    public function get_ct_receb($id = null) {
        if($id == null)
            return false;

        $ct_receb = $this->db->select('c.*, u.email')
            ->join('users u', 'u.id = c.CLIENTE')
            ->where('c.CODIGO', $id)
            ->get("ct_receb c");

        return $ct_receb;
    }

    public function check_login($identity, $password)
    {
        if (empty($identity) || empty($password))
        {
            $this->set_error('login_unsuccessful');
            return FALSE;
        }

        $query = $this->db->select('u.email, u.id, u.password, u.active, u.last_login, eu.empresa_id')
                ->join('empresas_users eu', 'eu.user_id = u.id')
                ->where('u.email', $identity)
                ->limit(1)
                ->order_by('u.id', 'desc')
                ->get('users u');

        
        if ($query->num_rows() === 1)
        {
            $user = $query->row();

            $password = $this->ion_auth->hash_password_db($user->id, $password);

            if ($password === TRUE)
            {
                if ($user->active == 0)
                {
                    $this->trigger_events('post_login_unsuccessful');
                    $this->set_error('login_unsuccessful_not_active');

                    return FALSE;
                }

                return TRUE;
            }
        }

        return FALSE;
    }

    public function update_unidade_padrao($data = array()) {
    	if(empty($data)) {
    		return FALSE;
    	}

    	$unidades_usuario = array(0);
    	foreach ($data["unidades_usuario"] as $key => $value) {
    		$unidades_usuario[] = $value->id;
    	}

    	if($this->db->update('user_unidade', array("padrao" => "0"), "user_id = ".$data["user_id"]." AND unidade_id IN (".implode(",", $unidades_usuario).")")) {
    		if($this->db->update('user_unidade', array("padrao" => "1"), array('user_id' => $data["user_id"], 'unidade_id' => $data["unidade_padrao"]))) {
    			return TRUE;
    		}
    		return TRUE;
    	} else {
    		return FALSE;
    	}
    }
	
	function allserver_side_count_ativos()
    {   
    	$query_unids = $this->db->select('uu.*')
            ->join('unidades u', 'u.id = uu.unidade_id')
            ->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->where('uu.user_id', $this->session->userdata('user_id'))
            ->get('user_unidade uu');
        $unids = array(0);
        foreach ($query_unids->result() as $value) {
            $unids[] = $value->unidade_id;
        }

        $query = $this
            ->db
            ->join('user_unidade', 'user_unidade.user_id = users.id', 'left')
    		->where('user_unidade.unidade_id IN ('.implode(",",$unids).')')
                ->where("users.active", 1)
            ->group_by('users.id')
            ->get('users');
    
        return $query->num_rows();  

    }
    
    function allserver_side_ativos($limit,$start,$col,$dir)
    {   
    	$query_unids = $this->db->select('uu.*')
            ->join('unidades u', 'u.id = uu.unidade_id')
            ->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->where('uu.user_id', $this->session->userdata('user_id'))
            ->get('user_unidade uu');
        $unids = array(0);
        foreach ($query_unids->result() as $value) {
            $unids[] = $value->unidade_id;
        }

       	$query = $this
                ->db
                ->select('users.*, users.id as user_id')
	            ->join('user_unidade', 'user_unidade.user_id = users.id', 'left')
	    		->where('user_unidade.unidade_id IN ('.implode(",",$unids).')')
                ->where("users.active", 1)
                ->limit($limit,$start)
                ->group_by('users.id')
                ->order_by($col,$dir)
                ->get('users');
        
        if($query->num_rows()>0)
        {
            return $query->result(); 
        }
        else
        {
            return null;
        }
        
    }
   
    function server_side_search_ativos($limit,$start,$search,$col,$dir)
    {
    	$query_unids = $this->db->select('uu.*')
            ->join('unidades u', 'u.id = uu.unidade_id')
            ->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->where('uu.user_id', $this->session->userdata('user_id'))
            ->get('user_unidade uu');
        $unids = array(0);
        foreach ($query_unids->result() as $value) {
            $unids[] = $value->unidade_id;
        }

        $query = $this
                ->db
                ->select("users.*, users.id as user_id, CONCAT(first_name, ' ', last_name) as 'full_name'")
	            ->join('user_unidade', 'user_unidade.user_id = users.id', 'left')
	    		->where('user_unidade.unidade_id IN ('.implode(",",$unids).')')
                ->where("(CONCAT(first_name, ' ', last_name) LIKE '%".$search."%' OR email LIKE '%".$search."%' OR users.id LIKE '%".$search."%')")
                ->limit($limit,$start)
                ->where("users.active", 1)
                ->group_by('users.id')
                ->order_by($col,$dir)
                ->get('users');

        if($query->num_rows()>0)
        {
            return $query->result();  
        }
        else
        {
            return null;
        }
    }

    function server_side_search_count_ativos($search)
    {
    	$query_unids = $this->db->select('uu.*')
            ->join('unidades u', 'u.id = uu.unidade_id')
            ->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->where('uu.user_id', $this->session->userdata('user_id'))
            ->get('user_unidade uu');
        $unids = array(0);
        foreach ($query_unids->result() as $value) {
            $unids[] = $value->unidade_id;
        }

        $query = $this
                ->db
	            ->join('user_unidade', 'user_unidade.user_id = users.id', 'left')
	    		->where('user_unidade.unidade_id IN ('.implode(",",$unids).')')
                ->where("(CONCAT(first_name, ' ', last_name) LIKE '%".$search."%' OR email LIKE '%".$search."%' OR users.id LIKE '%".$search."%')")
                ->or_like('email',$search)
                ->where("users.active", 1)
                ->group_by('users.id')
                ->get('users');
    
        return $query->num_rows();
    }
	
	public function is_password_greater_than_90_days($user) {
    	$data_inicial = $user->password_updated;
		$data_final = date('Y-m-d H:i:s');

		// Calcula a diferença em segundos entre as datas
		$diferenca = strtotime($data_final) - strtotime($data_inicial);

		//Calcula a diferença em dias
		$dias = floor($diferenca / (60 * 60 * 24));

		if($dias > 90)
			return TRUE;
		else
			return FALSE;
    }

    function get_attempts($identity)
	{
        $this->db->select('*')
        	->where('login', $identity);
        $qres = $this->db->get($this->tables['login_attempts']);
        return $qres->result();
	}

}
