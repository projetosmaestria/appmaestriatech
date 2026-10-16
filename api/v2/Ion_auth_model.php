<?php
require_once ('Bcrypt.php');
require_once ('conexao.php');

class Ion_auth_model 
{

	public $forgotten_password_code;

	public function __construct()
	{
		$this->store_salt = false;
		$this->hash_method = 'bcrypt';
		$this->salt_length = 22;
		$this->permitted_uri_chars = 'a-z 0-9~%.:_\-';
		$this->enable_query_strings = FALSE;
        $this->conexao = new Conexao();
	}

	public function forgotten_password($identity)
	{	
		// All some more randomness
		$activation_code_part = "";

		for($i=0;$i<1024;$i++) {
			$activation_code_part = sha1($activation_code_part . mt_rand() . microtime());
		}

		$key = $this->hash_code($activation_code_part.$identity);

		// If enable query strings is set, then we need to replace any unsafe characters so that the code can still work
		if ($key != '' && $this->permitted_uri_chars != '' && $this->enable_query_strings == FALSE)
		{
			// preg_quote() in PHP 5.3 escapes -, so the str_replace() and addition of - to preg_quote() is to maintain backwards
			// compatibility as many are unaware of how characters in the permitted_uri_chars will be parsed as a regex pattern
			if ( ! preg_match("|^[".str_replace(array('\\-', '\-'), '-', preg_quote($this->permitted_uri_chars, '-'))."]+$|i", $key))
			{
				$key = preg_replace("/[^".$this->permitted_uri_chars."]+/i", "-", $key);
			}
		}

		$this->forgotten_password_code = $key;

		$update = array(
		    'forgotten_password_code' => $key,
		    'forgotten_password_time' => time()
		);

		$sql_update = "UPDATE users SET "
            . " forgotten_password_code = '".$key."', "
            . " forgotten_password_time = '".time()."'"
            . " WHERE email = '".$identity."'";

		$mysqli = $this->conexao->getDatabase();
        
        if($mysqli->query($sql_update)){
        	return true;
        } else {
        	return false;
        }
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

	public function hash_code($password)
	{
		return $this->hash_password($password, FALSE, TRUE);
	}

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
			return sha1($password . $salt);
		}
		else
		{
			$salt = $this->salt();
			return  $salt . substr(sha1($salt . $password), 0, -$this->salt_length);
		}
	}

	public function hash_password_db($id, $password, $use_sha1_override=FALSE)
	{
		$conn = $this->conexao->getDatabase();

		if (empty($id) || empty($password))
		{
			return FALSE;
		}

		$sql = "SELECT password, salt FROM users WHERE id = $id LIMIT 1";
		$result = $conn->query($sql);
		$hash_password_db = $result->fetch_array(MYSQLI_ASSOC);
		
		if ($result->num_rows != 1)
		{
			return FALSE;
		}
		
		// bcrypt
		if ($use_sha1_override == FALSE && $this->hash_method == 'bcrypt')
		{
			$bcrypt = new Bcrypt();
			if ($bcrypt->verify($password,$hash_password_db['password']))
			{
				return TRUE;
			}

			return FALSE;
		}

		// sha1
		if ($this->store_salt)
		{
			$db_password = sha1($password . $hash_password_db['salt']);
		}
		else
		{
			$salt = substr($hash_password_db['password'], 0, $this->salt_length);

			$db_password =  $salt . substr(sha1($salt . $password), 0, -$this->salt_length);
		}

		if($db_password == $hash_password_db['password'])
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

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
	
	public function login($identity, $password, $remember=FALSE)
	{
		$conn = $this->conexao->getDatabase();
		
		if (empty($identity) || empty($password))
		{
			return FALSE;
		}
		
		$sql = "SELECT u.email, u.email, u.id, u.password, u.active, u.last_login, eu.empresa_id, u.password_updated 
				FROM users u JOIN empresas_users eu ON u.id = eu.user_id
				WHERE u.email = '$identity' AND u.active LIMIT 1";
		$result = $conn->query($sql);
		$user = $result->fetch_array(MYSQLI_ASSOC);
		
		if ($result->num_rows == 1)
		{
			$password = $this->hash_password_db($user['id'], $password);
			
			if ($password === TRUE)
			{
				return TRUE;
			}
		}

		return FALSE;
	}

}
