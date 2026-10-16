<?php

class Util{
    private $api_key = "VQFRC2OlxdOdOdFO"; //hom
    private $api_secret = "RghiWnFU0vLGm3xHg9S3S2ho"; //hom
    //private $api_key = "QEetPu4WyuDcsU9o"; //prod
    //private $api_secret = "zz7ouE9hrih5jrEykt2ERgOY"; //prod
        
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
}