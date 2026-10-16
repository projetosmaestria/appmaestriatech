<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Curl {

  private $ch;

  public function post($url,$data)
  {
    $this->init($url);

    curl_setopt($this->ch,CURLOPT_POST, 1);
    curl_setopt($this->ch,CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($this->ch,CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($this->ch,CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($this->ch,CURLOPT_SSL_VERIFYPEER, 0);

    return $this->exec();
  }

  public function get($url)
  {
    $this->init($url);  
    return $this->exec();
  }

  private function init($url)
  {
    $this->ch = curl_init();
    curl_setopt($this->ch, CURLOPT_URL, $url);
  }

  private function exec()
  {
    $result = curl_exec($this->ch);
    curl_close($this->ch);

    return $result;
  }

}