<?php
function createBookmark($data, $username, $password){
    
    $networks = "";
    $output = "";
    
    if(isset($data['networks']) && $data['networks']!="")
    {
         $networks = "&networks={$data['networks']}";
    }
    
    $query = SITE_URL."api/v2/add/bookmark?url={$data['url']}&title={$data['title']}&tags={$data['tags']}&description={$data['description']}&scheduled={$data['scheduled']}{$networks}";

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:  application/json"));
    curl_setopt($ch, CURLOPT_URL, $query);
    curl_setopt($ch, CURLOPT_USERPWD, $username.":".$password);
    $output = curl_exec($ch);

    curl_close($ch);
    
    return $output;
}

function getServiceLogins($username, $password){
    
    $query = SITE_URL."api/v2/user/networks";
    $ch    = curl_init();

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:  application/json"));
    curl_setopt($ch, CURLOPT_URL, $query);
    curl_setopt($ch, CURLOPT_USERPWD, $username.":".$password);

    $output = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($output);
}

function getUser($username, $password, $url = ""){
    
    $query = SITE_URL."api/v2/user/info";
    
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:  application/json"));
    curl_setopt($ch, CURLOPT_URL, $query);
    curl_setopt($ch, CURLOPT_USERPWD, $username.":".$password);
    $output = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($output);
}
function checkUser($username, $password){
    
    $query = SITE_URL."api/v2/user/info";
    
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:  application/json"));
    curl_setopt($ch, CURLOPT_URL, $query);
    curl_setopt($ch, CURLOPT_USERPWD, $username.":".$password);
    $output = curl_exec($ch);
    curl_close($ch);
    
    return $output;
} 
?>