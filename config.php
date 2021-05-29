<?php
require "predis/autoload.php";
Predis\Autoloader::register();

//check whether server is running or not 
try {

    $redis = new Predis\Client(array(
        "scheme" => "redis",
        "host" => "localhost",//changer le nom de la base
        "port" => 6379,
    ));

}
catch (Exception $e) {
    die($e->getMessage());
}

session_start();
$userUniq = uniqid();

if(!isset($_SESSION["user"])){

    $_SESSION["user"] = $userUniq;
    
    $user = [
        "id"=>$userUniq,
        "name"=>$userUniq
    ];

    $redis->set("user".$userUniq,json_encode($user));
}
else{

    $user = json_decode($redis->get("user".$_SESSION["user"]), true);

    $_SESSION["user"] = $user["id"];

}