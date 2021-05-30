<?php

require_once 'pendu.php';

require "config.php";

$method = $_SERVER["REQUEST_METHOD"];

if($method == 'POST'){

  if(isset($_POST["create"])) {
    $roomId = uniqid();
    $roomName = $_POST["roomName"];
    $players = $_POST["players"];
    $word = strtoupper($_POST["word"]);
    $creator = $_SESSION["user"];
    $allplayers = [$creator];
    $index = 0;

    $salle = [
      "id" => $roomId,  
      "name" => $roomName,  
      "creator"  => $creator,
      "maxPlayers" => $players,
      "currPlayers" =>count($allplayers),
      "players" => $allplayers,
      'wrong' => 0,
      'guessedLetters' => []
    ];

    $redis->setex("mot".$salle["id"], 60, $word);

    if($redis->exists("salles")){
      
      $rooms = $redis->lrange("salles", 0, -1);

      $alreadyExist = false;

      for ($i=0; $i < count($rooms); $i++) { 
        
        $salle = json_decode($rooms[$i], true);
        
        if($salle["id"] == $roomId){
          $alreadyExist = true;
          break;
        }

      }

      if(!$alreadyExist){
        $redis->lpush('salles', json_encode($salle));
      }
      else{
        echo '<script>window.location.href = "create-room.php";</script>';
        exit();
      }
    }
    else{
      $redis->lpush('salles', json_encode($salle));
    }

    $currRoom = $salle;
  }
  elseif(isset($_POST["join"])) {
    $roomId = $_POST["idRoom"];
    $rooms = $redis->lrange("salles", 0, -1);
    
    $currRoom = null;
    $index = null;

    for ($i=0; $i < count($rooms); $i++) { 
      $room = json_decode($rooms[$i], true);
      
      if($room["id"] == $roomId){
        $currRoom = $room;
        $index = $i;
        break;
      }
    }
    
    if(is_null($currRoom)){
      echo '<script>window.location.href = "join-room.php";</script>';
      exit();
    }

    if(!in_array($user["id"], $currRoom["players"])){
      array_push($currRoom["players"], $user["id"]);
      $currRoom["currPlayers"] += 1; 
      
      $redis->lset('salles', $index, json_encode($currRoom));
    }
  }
  elseif(isset($_POST["newWordAddition"])){

    $rooms = $redis->lrange("salles", 0, -1);
    $index = $_POST["index"];
    $word = $_POST["newWord"];
    $currRoom = json_decode($rooms[$index], true);

    $currRoom["wrong"] = 0;
    $currRoom["guessedLetters"]=[];
    $redis->lset('salles', $index, json_encode($currRoom));

    if(!$redis->exists("mot".$currRoom["id"])){
      $redis->setex("mot".$currRoom["id"], 60, strtoupper($word));
    }

  }
  else{

    $rooms = $redis->lrange("salles", 0, -1);
    $index = $_POST["index"];
    $currRoom = json_decode($rooms[$index], true);
  }
  $words = [$redis->get("mot".$currRoom["id"])];

  $numwords = 0;

  function console_log( $data ){
    echo '<script>';
    echo 'console.log('. json_encode( $data ) .')';
    echo '</script>';
  }

  function printPage($image, $guesstemplate, $which, $guessed, $index) {
      $script = $_SERVER["PHP_SELF"];
    echo <<<ENDPAGE
  <!DOCTYPE html>
  <html>
    <head>
    <title>Hangman</title>
    </head>
  </html>
  <body>
    <h1>Hangman Game</h1>
    <br />
    <pre>$image</pre>
    <br />
    <p><strong>Mot à deviner: $guesstemplate</strong></p>
    <p>Lettres utilisées jusqu'à présent: $guessed</p>
    <form method="post" action="$script">
      <input type="hidden" name="lettersguessed" value="$guessed" />
      <input type="hidden" name="word" value="$which" />
      <input type="hidden" name="index" value="$index" />
      <fieldset>
        <legend>Proposer une lettre:</legend>
        <input type="text" pattern="^\w{1}$" name="letter" autofocus />
        <input type="submit" name="guess" value="Deviner" />
      </fieldset>
      <fieldset>
        <legend>Deviner le mot:</legend>
        <input type="text" name="wordGuess" autofocus />
        <input type="submit" name="guess" value="Deviner" />
      </fieldset>
    </form>
    <form id="newWordForm" method="post" action="$script">
      <input type="hidden" name="index" value="$index" />
      <input type="hidden" name="newWordForm" value="" />
    </form>
  </body>
  ENDPAGE;
  }

  function startGame() {
    global $words;
    global $pendu;
    global $index;
    global $currRoom;

    $which = 0;
    $word =  $words[$which];
    $len = strlen($word);
    $guesstemplate = str_repeat('_ ', $len);

    printPage($pendu[0], $guesstemplate, $which, "", $index);
  }

  function killPlayer($word) {
    global $index;
    global $redis;
    global $currRoom;

    $redis->del("mot".$currRoom["id"]);

    echo <<<ENDPAGE
    <!DOCTYPE html>
    <html>
      <head>
        <title>Hangman</title>
      </head>
      <body>
        <h1>Perdus! le mots étais $word! Donner un nouveaux mot!</h1>
        <form method="post" action="game-multi.php">
          <input type="hidden" name="index" value="$index" />
          <fieldset>
            <legend>Mot:</legend>
            <input type="text" name="newWord" />
            <input type="submit" name="newWordAddition" value="Valider" />
          </fieldset>
        </form>
      </body>
    </html>
    ENDPAGE;
  }

  function congratulateWinner($word) {
    global $index;
    global $redis;
    global $currRoom;

    $redis->del("mot".$currRoom["id"]);

    echo <<<ENDPAGE
    <!DOCTYPE html>
    <html>
      <head>
        <title>Hangman</title>
      </head>
      <body>
        <h1>Gagner! le mots étais $word! Donner un nouveaux mot!</h1>
        <form method="post" action="game-multi.php">
          <input type="hidden" name="index" value="$index" />
          <fieldset>
            <legend>Mot:</legend>
            <input type="text" name="newWord" />
            <input type="submit" name="newWordAddition" value="Valider" />
          </fieldset>
        </form>
      </body>
    </html>
    ENDPAGE;
  }

  function matchLetters($word, $guessedLetters) {
    $len = strlen($word);
    $guesstemplate = str_repeat("_ ", $len);
    
    for ($i = 0; $i < $len; $i++) {
    $ch = $word[$i];
    if (strstr($guessedLetters, $ch)) {
      $pos = 2 * $i;
      $guesstemplate[$pos] = $ch;
    }
    }

    return $guesstemplate;
  }

  function handleGuess() {
    global $words;
    global $pendu;
    global $redis;
    global $currRoom;
    global $index;

    $which = 0;
    $word  = $words[$which];


    if($_POST["letter"] == null){
      $guess = strtoupper($_POST["wordGuess"]);
      $lettersguessed = implode("",$currRoom["guessedLetters"]);
      
      if($guess == $word){
        congratulateWinner($word);
      }
      else{
        $currRoom["wrong"]++;
        if($currRoom["wrong"] >= 9){
          killPlayer($word);
        }
        else{
          $guesstemplate = matchLetters($word, $lettersguessed);
          printPage($pendu[$currRoom["wrong"]], $guesstemplate, $which, $lettersguessed, $index);
        }
      }
    }
    else{
      
      $guess = $_POST["letter"];
      $letter = strtoupper($guess[0]);
      $letters = $currRoom["guessedLetters"];
      
      if(!in_array($letter, $letters)){
        array_push($currRoom["guessedLetters"], $letter);
        $redis->lset('salles', $index, json_encode($currRoom));

        if(!strstr($word, $letter)) {
          $currRoom["wrong"]++;
        }
        $letters = $currRoom["guessedLetters"];
      }
      
      $lettersguessed = implode("",$letters);
      
      $guesstemplate = matchLetters($word, $lettersguessed);
      
      if (!strstr($guesstemplate, "_")) {
        congratulateWinner($word);
      } else if ($currRoom["wrong"] >= 9) {
        killPlayer($word);
      } else {
        printPage($pendu[$currRoom["wrong"]], $guesstemplate, $which, $lettersguessed, $index);
      }
    }
    $redis->lset('salles', $index, json_encode($currRoom));
  }

  function newWordForm(){
    global $index;
    echo <<<ENDPAGE
    <!DOCTYPE html>
    <html>
      <head>
        <title>Hangman</title>
      </head>
      <body>
        <h1>Donner un nouveaux mot!</h1>
        <form method="post" action="game-multi.php">
          <input type="hidden" name="index" value="$index" />
          <fieldset>
            <legend>Mot:</legend>
            <input type="text" name="newWord" />
            <input type="submit" name="newWordAddition" value="Valider" />
          </fieldset>
        </form>
      </body>
    </html>
    ENDPAGE;
  }

  if(!$redis->exists("mot".$currRoom["id"])){
    newWordForm();
  }
  else{
    if (isset($_POST["guess"]) || isset($_POST["join"])) {
      handleGuess();
    }
    elseif(isset($_POST["newWordForm"])){
      newWordForm();
    }
     else {
      startGame();
    }
  }

}
else{
  echo '<script>window.location.href = "index.php";</script>';
  exit();
}


?>

<div id="countdown"></div>

<script type="text/javascript">

  var timeleft = <?php echo $redis->ttl("mot".$currRoom["id"]) ?>;
  if(timeleft > 0){
    var downloadTimer = setInterval(function(){
      if(timeleft <= 0){
        clearInterval(downloadTimer);
        document.getElementById("newWordForm").submit();
      } else {
        document.getElementById("countdown").innerHTML = timeleft + " seconde pour deviner le mot";
      }
      timeleft -= 1;
    }, 1000);
  }

</script>