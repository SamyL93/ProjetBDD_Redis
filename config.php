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
    $_SESSION["userName"] = $_SESSION["user"];

    $redis->set("userId".$userUniq, $_SESSION["user"]);
    $redis->set("userName".$userUniq, $_SESSION["user"]);

}
else{

    $_SESSION["user"] = $redis->get("userId".$_SESSION["user"]);

}