<?php
// Start the session
    require "config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>Crée une salle</h2>

        <form action="index.php" method="POST">
            <div class="form-group">
                <label for="nicknameField">Nom: </label>
                <input type="text" class="form-control" id="nicknameField" placeholder="Nouveau pseudo" name="nicknameField">
            </div>
            <button type="submit" name="nickname" class="btn btn-default">Changer</button>
        </form>

        <div class="pseudo-box">Votre pseudo est: 
            <?php 
                echo $_SESSION["user"]
            ?>
        </div>
    </div>
</body>

</html>
