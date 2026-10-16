<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use FacebookAds\Api;
use FacebookAds\Object\Ad;
use FacebookAds\Object\Fields\AdFields;

class Webhooks {

	public function lead()
	{
		$challenge = @$_REQUEST['hub_challenge'];
		$verify_token = @$_REQUEST['hub_verify_token'];

		if($verify_token === 'abc123') {
			echo $challenge;
		}

		$input = json_decode(file_get_contents('php://input'), true);
		//$input = json_decode('{"entry":[{"changes":[{"field":"leadgen","value":{"created_time":1533575898,"page_id":"289800947719821","form_id":"1262860490523556","leadgen_id":"1263209117155360"}}],"id":"289800947719821","time":1533575898}],"object":"page"}',true);


		if(isset($input)) {

			$db['default'] = array(
				'dsn'	=> '',
				'hostname' => 'localhost',
				'username' => 'xkeystic_xkeynet',
				'password' => 'WtUV]!3_T6V,',
				'database' => 'xkeystic_xkeynet2',
				'dbdriver' => 'mysqli',
				'dbprefix' => '',
				'pconnect' => FALSE,
				'db_debug' => (ENVIRONMENT !== 'production'),
				'cache_on' => FALSE,
				'cachedir' => '',
				'char_set' => 'utf8',
				'dbcollat' => 'utf8_general_ci',
				'swap_pre' => '',
				'encrypt' => FALSE,
				'compress' => FALSE,
				'stricton' => FALSE,
				'failover' => array(),
				'save_queries' => TRUE
			);

			$conn = mysqli_connect($db['default']["hostname"], $db['default']["username"], $db['default']["password"], $db['default']["database"]);

			if (!$conn) {
			    echo "Error: Unable to connect to MySQL." . PHP_EOL;
			    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
			    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
			    exit;
			}

			$form_id = $input['entry'][0]['changes'][0]['value']['form_id'];
			$ad_id = @$input['entry'][0]['changes'][0]['value']['ad_id'];
			$page_id = $input['entry'][0]['changes'][0]['value']['page_id'];
			$leadgen_id = $input['entry'][0]['changes'][0]['value']['leadgen_id'];


			//$unidade_info = $this->webhooks_model->get_unidade_by_page_id($page_id);
			//$unidade_info = $this->db->get_where("unidades", array("fb_page_id" => $page_id, "fb_status_api" => "S", "empresa_id" => $this->session->userdata('empresa_id')));
			$sql = "SELECT * FROM unidades WHERE fb_page_id = '" . $page_id ."' AND fb_status_api = 'S'";
			$unidade_info_rs = $conn->query($sql);
			if($unidade_info_rs->num_rows > 0) {
				while($unidade = $unidade_info_rs->fetch_assoc()) {
					$unidade_info = $unidade;
					if($unidade_info['fb_expires_at'] <= date("Y-m-d H:i:s")) {
						//$token = $this->webhooks_model->get_access_token_unids($unidade_info->fb_app_id, $unidade_info->fb_app_secret);

						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/oauth/access_token?client_id=" . $unidade_info['fb_app_id'] . "&client_secret=" . $unidade_info['fb_app_secret'] . "&grant_type=fb_exchange_token&fb_exchange_token=" . $unidade_info['fb_access_token']);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
						$result = curl_exec($ch);
						$refreshData = json_decode($result, true);

						if($refreshData["access_token"]) {
							$data["fb_access_token"] = $refreshData["access_token"];
						}
						if(@$refreshData["expires_in"]) {
							$data["fb_expires_at"] = date('Y/m/d H:i:s', strtotime("+".$refreshData["expires_in"]." seconds"));
						}
						//$this->webhooks_model->update_unidade($unidade_info->id, $data);
						//$this->db->update("unidades", $data, array('id' => $unidade_info['id']));

						$sql = "UPDATE unidades SET fb_access_token = '".$data["fb_access_token"]."', fb_expires_at = '".$data["fb_expires_at"]."' WHERE id = '".$unidade_info['id']."'";
						$conn->query($sql);
					}

					// exit("<Pre>".print_r($input,true)."</pre>");

					if($ad_id != "") {
						// $anuncio_existe = $this->webhooks_model->get_anuncio_by_fb_id($ad_id);
						// $query = $this->db->select('e.*');
				  //       $query->join('midias m', 'm.id = e.midia_id');
				  //       $query->where('e.fb_id', $ad_id);
				  //       $query->where('m.empresa_id', $this->session->userdata('empresa_id'));
				  //       $anuncio_existe = $query->get('eventos e');
				        $sql = "SELECT e.* FROM eventos e INNER JOIN midias m ON m.id = e.midia_id WHERE e.fb_id = '" . $ad_id ."' AND m.empresa_id = '".$unidade_info['empresa_id']."'";
						$anuncio_existe = $conn->query($sql);

						Api::init($unidade_info['fb_app_id'], $unidade_info['fb_app_secret'], $unidade_info['fb_access_token']);

						$ad = new Ad($ad_id);
						$ad->read(array(
							AdFields::NAME,
							AdFields::STATUS,
						));

						if($anuncio_existe->num_rows == 0) {
							$data = array("midia_id" => $unidade_info['fb_midia_id'], "fb_id" => $ad_id, "descricao" => $ad->name, "status" => $ad->status);
							// $anuncio_id = $this->webhooks_model->insere_anuncio($data);
							// if($this->db->insert('eventos', $data)) {
					  //           $anuncio_id = $this->db->insert_id();
					  //       }
					        $sql = "INSERT INTO eventos (midia_id, fb_id, descricao, status) VALUES('".$unidade_info['fb_midia_id']."', '".$ad_id."', '".$ad->name."', '".$ad->status."')";
					        $conn->query($sql);
					        $anuncio_id = $conn->insert_id;
						} else {
							$anuncio_existe_row = $anuncio_existe->fetch_assoc();
							$anuncio_id = $anuncio_existe_row['id'];
						}
					} else {
						$anuncio_id = $unidade_info['fb_evento_id'];
					}

					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v2.8/" . $leadgen_id . "?access_token=" . $unidade_info['fb_access_token']);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
					$result = curl_exec($ch);
					$leadData = json_decode($result, true);
					//exit("<Pre>".print_r($leadData,true)."</pre>");

					$lead = [];
					$nome = "";
					$email = "";
					$telefone = "";
					for($i = 0; $i < count($leadData["field_data"]); $i++)
					{
						if($leadData["field_data"][$i]["name"] == "name" || $leadData["field_data"][$i]["name"] == "full_name" || $leadData["field_data"][$i]["name"] == "nome_completo") {
							$nome = $leadData["field_data"][$i]["values"][0];
						}
						if($leadData["field_data"][$i]["name"] == "email") {
							$email = $leadData["field_data"][$i]["values"][0];
						}
						if($leadData["field_data"][$i]["name"] == "phone_number" || $leadData["field_data"][$i]["name"] == "telefone/whatsapp") {
							$telefone = $leadData["field_data"][$i]["values"][0];
						}
						//$lead[$leadData[$i]['name']] = $leadData[$i]['values'][0];
					}

					$data = array("unidade_id" => $unidade_info['id'], "evento_id" => $anuncio_id, "nome" => $nome, "email" => $email, "celular" => $telefone, "mensagem" => $result, "created_at" => date("Y-m-d H:i:s", strtotime($leadData["created_time"])), "lead_id" => $leadData["id"]);
					
					//$this->webhooks_model->insere_lead($data);
					
					// $existing = $this->db->get_where('contatos', array('lead_id' => $data["lead_id"], 'unidade_id' => $data["unidade_id"]))->num_rows();
			  //       if($existing == 0)
			  //       {
			  //          if($this->db->insert('contatos', $data)) {
				 //            $id = $this->db->insert_id();
				 //        }
			  //       }

			        $sql = "SELECT * FROM contatos WHERE lead_id = '" . $data["lead_id"] ."' AND unidade_id = '".$data["unidade_id"]."'";
					$lead_exist = $conn->query($sql);
					if($lead_exist->num_rows == 0) {
						$sql = "INSERT INTO contatos (unidade_id, evento_id, nome, email, celular, mensagem, created_at, lead_id) VALUES('".$unidade_info['id']."', '".$anuncio_id."', '".$nome."', '".$email."', '".$telefone."', '".$result."', '".date("Y-m-d H:i:s", strtotime($leadData["created_time"]))."', '".$leadData["id"]."')";
				        $conn->query($sql);
					}
				}
			}
		}

		exit;
	}
	
}
