<?php
// Start the session
    session_start();
    $_SESSION["user"] = uniqid();
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
        <a href="game.php">
            <p>Solo</p>
        </a>
    </div>

    <div id="multiplayer"><p>Multijouer</p></div>
    <div id="options"><p>Pseudo</p></div>
    <div id="right"></div>
    <div id="exit"></div>
    <div id="circle"></div>
    </div>
    
</body>

</html>
