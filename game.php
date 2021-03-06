<?php

require_once 'pendu.php';

require "config.php";

// mise à jour de la valeur
$redis->set('Mot_a_trouver', 'TEST');

// $redis->lpush('Past_letter', "A");
// console_log($redis->lrange("Past_letter", 0, -1));

// recuperation de la valeur
$words = [$redis->get('Mot_a_trouver')];
// $lettersguessed = $redis->get('Past_letter');

$numwords = 0;

// affichage de la valeur
// print($value);

// echo ($redis->exists('message')) ? "Oui" : "Non";

// //suppression de la clé
// $redis->del('message');

function console_log( $data ){
  echo '<script>';
  echo 'console.log('. json_encode( $data ) .')';
  echo '</script>';
}

function printPage($image, $guesstemplate, $which, $guessed, $wrong) {
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
	<input type="hidden" name="wrong" value="$wrong" />
	<input type="hidden" name="lettersguessed" value="$guessed" />
	<input type="hidden" name="word" value="$which" />
	<fieldset>
	  <legend>Proposer une lettre:</legend>
	  <input type="text" pattern="^\w{1}$" name="letter" autofocus />
	  <input type="submit" value="Deviner" />
	</fieldset>
	<fieldset>
	  <legend>Deviner le mot:</legend>
	  <input type="text" name="wordGuess" autofocus />
	  <input type="submit" value="Deviner" />
	</fieldset>
  </form>
</body>
ENDPAGE;
}

function loadWords() {
    global $words;
    global $numwords;
    global $redis;
    $words = [$redis->get('Mot_a_trouver')];
    $numwords = 0;
}

function startGame() {
  global $words;
  global $numwords;
  global $pendu;

  $which = 0;
  $word =  $words[$which];
  $len = strlen($word);
  $guesstemplate = str_repeat('_ ', $len);

  printPage($pendu[0], $guesstemplate, $which, "", 0);
}

function redisStart(){
  global $redis;
  $redis->del("Past_letter");
}

function killPlayer($word) {
  echo <<<ENDPAGE
<!DOCTYPE html>
<html>
 <head>
	<title>Le Pendu</title>
  </head>
  <body>
	<h1>Tu as perdue :(</h1>
	<p>Le mot que vous cherchez est : <em>$word</em>.</p>
  </body>
</html>
ENDPAGE;
}

function congratulateWinner($word) {
  echo <<<ENDPAGE
<!DOCTYPE html>
<html>
  <head>
	<title>Le Pendue</title>
  </head>
  <body>
	<h1>Tu as gangner!</h1>
	<p>Bravo! Le mots étais donc bien : <em>$word</em>.</p>
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

  $which = 0;
  $word  = $words[$which];
  $wrong = $_POST["wrong"];

  if($_POST["letter"] == null){
    $guess = strtoupper($_POST["wordGuess"]);
    $lettersguessed = implode("",$redis->lrange("Past_letter", 0, -1));

    if($guess == $word){
      congratulateWinner($word);
    }
    else{
      $wrong++;
      if($wrong >= 9){
        killPlayer($word);
      }
      else{
        $guesstemplate = matchLetters($word, $lettersguessed);
        printPage($pendu[$wrong], $guesstemplate, $which, $lettersguessed, $wrong);
      }
    }
  }
  else{

    $guess = $_POST["letter"];
    $letter = strtoupper($guess[0]);
    $letters =  $redis->lrange("Past_letter", 0, -1);

    if(!in_array($letter, $letters)){
      $redis->lpush("Past_letter", $letter);
      $letters =  $redis->lrange("Past_letter", 0, -1);
      if(!strstr($word, $letter)) {
        $wrong++;
      }
    }
    $lettersguessed = implode("",$letters);

    // $lettersguessed = $lettersguessed . $letter;
    $guesstemplate = matchLetters($word, $lettersguessed);
    
    if (!strstr($guesstemplate, "_")) {
       congratulateWinner($word);
    } else if ($wrong >= 9) {
      killPlayer($word);
    } else {
      printPage($pendu[$wrong], $guesstemplate, $which, $lettersguessed, $wrong);
    }
  }
}


//header("Content-type: text/plain");
loadWords();

$method = $_SERVER["REQUEST_METHOD"];

if ($method == "POST") {
  handleGuess();
} else {
  redisStart();
  startGame();
}

?>