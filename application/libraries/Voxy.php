<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Voxy
{
    //private $api_key = "VQFRC2OlxdOdOdFO"; //hom
    //private $api_secret = "RghiWnFU0vLGm3xHg9S3S2ho"; //hom
    //private $base_url = "https://web-stage.voxy.com/partner_api"; //hom
	
	private $api_key = "QEetPu4WyuDcsU9o"; //prod
    private $api_secret = "zz7ouE9hrih5jrEykt2ERgOY"; //prod
    private $base_url = "https://partner-api.voxy.com/partner_api"; //prod

    /**
     *
     */
    // Define application credentials for Staging environment
    /*define('API_KEY', 'VQFRC2OlxdOdOdFO');
    define('API_SECRET', 'RghiWnFU0vLGm3xHg9S3S2ho');
    define('BASE_URL', 'https://web-stage.voxy.com/partner_api');*/
    // Local development credentials (Debug):
    // define('API_KEY', 'API_KEY_PHOENIX');
    // define('API_SECRET', 'API_SECRET_PHOENIX');
    // define('BASE_URL', 'http://localhost:8000/partner_api');
    /**
     * define your user's credentials here.
     * NOTICE: You cannot create duplicate users.
     * SOLUTION: Change the user id every time you are creating a new user.
     */
    /**
     * Returns a sha256 of your api scecret/payload.
     */
    function generate_signature($params) {
        ksort($params);
        $to_hash = http_build_query($params);
        return hash("sha256", $this->api_secret . $to_hash);
    }
    /**
     * Prepares the request headers
     */
    function generate_header($signature) {
        $key = $this->api_key;
        return array(
            "AUTHORIZATION: Voxy {$key}:{$signature}",
        );
    }
    /**
     * Returns necessary request headers.
     */
    function sign_request($params) {
        $signature = $this->generate_signature($params);
        $headers = $this->generate_header($signature);
        return $headers;
    }
    /**
     * Makes the necessary Web Service request.
     */
    function make_request($endpoint, $method='GET', $params=array()) {
        $curl = curl_init();
        //Add URL Params if it is a GET
        if($method == 'GET' && !empty($params)) {
            $params_url = '';
            foreach($params as $key=>$value){
                $params_url .= $key.'='.$value.'&';
            }
            $params_url = trim($params_url, '&');
            curl_setopt($curl, CURLOPT_URL, $this->base_url.$endpoint.'?'.$params_url);
        } else {
            curl_setopt($curl, CURLOPT_URL, $this->base_url.$endpoint);
        }
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // POST and PUT requests have a payload
        if($method == 'POST' || $method == 'PUT') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        }
        //anulando ssl
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

        // Setup the request headers
        $headers = $this->sign_request($params);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        //echo "$method : $endpoint\n";
        $response_content = curl_exec($curl);
        $response_status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        //echo("Status : " . $response_status_code . "\n");
        //echo("Body : " . $response_content . "\n");
        
        if($response_status_code == 0) {
            # http://stackoverflow.com/questions/10227879/php-curl-http-code-return-0
            //echo("* Additional error details: " . curl_error($curl) . "\n");
        }
        
        // Close curl connection
        curl_close($curl);
        $response = array(
            'status_code' => $response_status_code,
            'content' => $response_content
        );
        return $response;
    }
}