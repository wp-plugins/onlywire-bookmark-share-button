<?php
include ("postrequest.php");
// this file takes care of posting the "form" data to onlywire to get the button id
extract($_GET);

$url = "http://onlywire.com/widget/http_auth.php?auth_user=".$ow_username."&auth_pw=".$ow_password;
$a = PostRequest($url,"", $_GET);

echo $a[1];
?>