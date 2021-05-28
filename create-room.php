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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>Crée une salle</h2>
        <form action="game-multi.php" name="create">
            <div class="form-group">
                <label for="roomName">Salle:</label>
                <input type="text" class="form-control" id="roomName" placeholder="Nom de salle" name="roomName">
            </div>
            <div class="form-group">
                <label for="players">Nombre de Joueurs:</label>
                <input type="number" class="form-control" id="numPlayers" name="players">
            </div>
            <button type="submit" class="btn btn-default">Crée</button>
        </form>
    </div>
</body>

</html>
