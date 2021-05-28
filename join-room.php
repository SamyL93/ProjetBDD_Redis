<?php
    require "config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <title>Salles</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <h2>Salles</h2>
        <p>Selectionner la salle de vos rêve :)</p>            
        <table class="table">
            <thead>
            <tr>
                <th>Id</th>
                <th>Nom</th>
                <th>Joueurs</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
                <?php
                if($redis->exists("salle")){
                    $rooms = $redis->lrange("salle", 0, -1);
                    $roomNames = $redis->lrange("salleNom", 0, -1);
                    $roomPlayers = $redis->lrange("salleJoueur", 0, -1);
                    $salleJoueurCurr = $redis->lrange("salleJoueurCurr", 0, -1);
                    for ($i=0; $i < count($rooms); $i++) { 
                        echo "<tr>";
                        echo "<td>$rooms[$i]</td>";
                        echo "<td>$roomNames[$i]</td>";
                        echo "<td>$salleJoueurCurr[$i]/$roomPlayers[$i]</td>";
                        if($salleJoueurCurr[$i] < $roomPlayers[$i]){
                            echo 
                            '<td>
                                <form action="game-multi.php">
                                    <input type="hidden" value='.$rooms[$i].' name="idRoom">
                                    <button type="submit" name="join" class="btn btn-primary">
                                        Rejoindre
                                    </button>
                                </form>
                            </td>';
                        }
                        else{
                            echo "<td></td>";
                        }
 
                        echo "</tr>";
                    }
                }
                else{
                    echo `
                        <tr>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                    `;
                }
                ?>
            </tbody>
        </table>
    </div>
    
</body>

</html>
