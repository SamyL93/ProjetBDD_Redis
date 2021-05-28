<?php
// Start the session
    require "config.php";
    if(isset($_POST["nickname"])){
        $_SESSION["userName"] = $_POST["nicknameField"];
        $redis->set("userName".$_SESSION["user"], $_SESSION["userName"]);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="allthethings">
        <div id="left"></div>

        <div id="single">
            <a href="game.php" class="link-menu">
                <p>Solo</p>
            </a>
        </div>

        <div id="multiplayer">
            <a href="multi-options.php" class="link-menu">
                <p>Multijouer</p>
            </a>
        </div>
        <div id="options">
            <a href="rename.php" class="link-menu">
                <p>Pseudo</p>
            </a>
        </div>
        <div id="right"></div>
        <!-- <div id="exit"></div> -->
        <!-- <div id="circle"></div> -->
        <div class="pseudo-box">Votre pseudo est: 
            <?php 
                echo $_SESSION["userName"]
            ?>
        </div>
    </div>

    
</body>

</html>
