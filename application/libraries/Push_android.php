<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Push_android
{
    private $url;
    private $apiKey;
    private $output;

    function __construct(){
        //$this->apiKey = 'AIzaSyBNlkF-9XdXRVXrCcsIkH9F6nX3DIxbR0c';
        $this->apiKey = 'AIzaSyDpAupc_cQxgO3tpf0woNft3VVP9K1aSS4';
        $this->url  = 'https://fcm.googleapis.com/fcm/send';
    }

    public function notify($regIds, $data){
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, $this->url );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $this->getHeaders() );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, $this->getPostFields($regIds, $data) );
 
        $result = curl_exec($ch);
        if ($result === false) {
            throw new \Exception(curl_error($ch));
        }
        
        curl_close($ch);
        return $result;
    }

    private function getHeaders(){
        return [
            'Authorization: key=' . $this->apiKey,
            'Content-Type: application/json'
        ];
    }

    private function getPostFields($regIds, $data){
        $fields = array
        (
            'to'            => is_string($regIds) ? $regIds : $regIds,
            'notification'  => $data,
        );

        return json_encode($fields);
    }
    
}