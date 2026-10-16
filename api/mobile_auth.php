<?php
// 1. Put your api_key and api_secret here.
// At this time, you should know this info.
// If you don't, reach a Voxy representative.
define('API_KEY', 'VQFRC2OlxdOdOdFO');
define('API_SECRET', 'RghiWnFU0vLGm3xHg9S3S2ho');

function user_has_permission($username, $password) {
    // 2. Remove the root/root demo if below and
    // implement your logic to check if the user
    // can or can not use the app. These credentials
    // usually comes from a database.
    if ($username == "thiago@maestriatech.com.br" && $password == "123123") {
        return true;
    }
    
    return false;
}

function get_voxy_user_id($username) {
    // 3. Search on your [usually a] database
    // and return the external_user_id
    return '62';
}

function generate_header($signature) {
    $key = API_KEY;
    
    return [
      'HTTP/1.1 200 Authorized',
      "AUTHORIZATION: Voxy {$key}:{$signature}",
      'Content-Type: application/json',
    ];
}

function generate_signature($params) {
    ksort($params);
    $to_hash = http_build_query($params);
    
    return hash("sha256", API_SECRET . $to_hash);
}

function build_authorized_response($voxy_user_id, $post) {
    $signature = generate_signature($post);
    $headers = generate_header($signature);
    
    foreach ($headers as $header) {
        header($header);
    }

    echo json_encode(['voxy_user_id' => $voxy_user_id]);
}

function build_non_authorized_response() {
    header('HTTP/1.1 401 Unauthorized');
}

$post = array("username"=>"thiago@maestriatech.com.br","password"=>"123123","action"=>"login");

if (user_has_permission($post["username"], $post["password"])) {
    $voxy_user_id = get_voxy_user_id($username);
    build_authorized_response($voxy_user_id, $post);
} else {
    build_non_authorized_response();
}