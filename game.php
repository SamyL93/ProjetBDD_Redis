<?php

require_once 'pendu.php';

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

// mise à jour de la valeur
$redis->set('Mot_a_trouver', 'Test');

$redis->sadd('Lettres_deja_proposees', []);

// recuperation de la valeur
$words = [$redis->get('Mot_a_trouver')];
$lettersguessed = $redis->get('Lettres_deja_proposees');
$numwords = 0;

// affichage de la valeur
// print($value);

// echo ($redis->exists('message')) ? "Oui" : "Non";

// //suppression de la clé
// $redis->del('message');


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
  <p><strong>Word to guess: $guesstemplate</strong></p>
  <p>Letters used in guesses so far: $guessed</p>
  <form method="post" action="$script">
	<input type="hidden" name="wrong" value="$wrong" />
	<input type="hidden" name="lettersguessed" value="$guessed" />
	<input type="hidden" name="word" value="$which" />
	<fieldset>
	  <legend>Your next guess</legend>
	  <input type="text" name="letter" autofocus />
	  <input type="submit" value="Guess" />
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

function killPlayer($word) {
  echo <<<ENDPAGE
<!DOCTYPE html>
<html>
 <head>
	<title>Hangman</title>
  </head>
  <body>
	<h1>You lost!</h1>
	<p>The word you were trying to guess was <em>$word</em>.</p>
  </body>
</html>
ENDPAGE;
}

function congratulateWinner($word) {
  echo <<<ENDPAGE
<!DOCTYPE html>
<html>
  <head>
	<title>Hangman</title>
  </head>
  <body>
	<h1>You win!</h1>
	<p>Congratulations! You guessed that the word was <em>$word</em>.</p>
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
  $lettersguessed = $redis->get("Lettres_deja_proposees");
  $guess = $_POST["letter"];
  $letter = strtoupper($guess[0]);

  if(!strstr($word, $letter)) {
	$wrong++;
  }

  $lettersguessed = $lettersguessed . $letter;
  $guesstemplate = matchLetters($word, $lettersguessed);

  if (!strstr($guesstemplate, "_")) {
   	congratulateWinner($word);
  } else if ($wrong >= 9) {
	killPlayer($word);
  } else {
	printPage($pendu[$wrong], $guesstemplate, $which, $lettersguessed, $wrong);
  }
}

//header("Content-type: text/plain");
loadWords();

$method = $_SERVER["REQUEST_METHOD"];

if ($method == "POST") {
  handleGuess();
} else {
  startGame();
}

?>