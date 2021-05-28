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
if(!$redis->exists("user")){
    $_SESSION["user"] = uniqid();
    $redis->set("user", $_SESSION["user"]);
}
else{
    $_SESSION["user"] = $redis->get("user");
}