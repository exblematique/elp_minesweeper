<?php
if ($_POST['reset']){
    $currentMap = $pdo->query("SELECT current_map FROM game_".$_COOKIE["gameId"]." WHERE id=".$_COOKIE["playerId"])->fetch()[0];
    $currentMap++;
    $pdo->query("UPDATE game_".$_COOKIE["gameId"]." SET current_map=". $currentMap ." WHERE id=".$_COOKIE["playerId"]);
    echo '{"difficult": ['.$n[0].','.$n[1].']}';
    exit(0);
}

//Vérifie si le joueur possède une carte déjà active sinon la créer
$gameFile = $gameFolder."/".$currentMap.".".$_COOKIE["playerId"];

if (file_exists($gameFile)){
    $fdGame = fopen($gameFile, 'r');
    $GLOBALS['$mapMines'] = fgets($fdGame);
    fclose($fdGame);
    
    $GLOBALS['$casesRestantes'] = $pdo->query("SELECT squares_remind FROM game_".$_COOKIE["gameId"]." WHERE id=".$_COOKIE["playerId"])->fetch()[0];
    $GLOBALS['$playerMap'] = $pdo->query("SELECT player_map FROM game_".$_COOKIE["gameId"]." WHERE id=".$_COOKIE["playerId"])->fetch()[0];
    $GLOBALS['$addScore'] = $pdo->query("SELECT add_score FROM game_".$_COOKIE["gameId"]." WHERE id=".$_COOKIE["playerId"])->fetch()[0];
} else {
    include('createMap.php');
    //Enregiste la nouvelle map dans un fichier
    $fdGame = fopen($gameFile, 'x');
    fputs($fdGame, $GLOBALS['$mapMines']);
    fclose($fdGame);
    
    //Update the datadase
    $GLOBALS['$playerMap'] = '';
    for ($i=0; $i<$n[0]*$n[1]; $i++) $GLOBALS['$playerMap'] .= '0';

    $GLOBALS['$casesRestantes'] = $n[0]*$n[1] - $b;
    $GLOBALS['$addScore'] = 1;
    $pdo->query("UPDATE game_".$_COOKIE["gameId"]." SET player_map='".$GLOBALS['$playerMap']."' WHERE id=".$_COOKIE["playerId"]);
    $pdo->query("UPDATE game_".$_COOKIE["gameId"]." SET squares_remind='".$GLOBALS['$casesRestantes']."' WHERE id=".$_COOKIE["playerId"]);
    $pdo->query("UPDATE game_".$_COOKIE["gameId"]." SET add_score='".$GLOBALS['$addScore']."' WHERE id=".$_COOKIE["playerId"]);
}

/*************************************************************************************/    
/*******************************    NEW GRID    **************************************/
/*************************************************************************************/ 
$GLOBALS['$count'] = 0;
$GLOBALS['$score'] = $pdo->query("SELECT score FROM game_".$_COOKIE["gameId"]." WHERE id=".$_COOKIE["playerId"])->fetch()[0];

//Verifie la valeur de la case choisi :
switch ($GLOBALS['$mapMines'][$_POST['x']+$_POST['y']*$n[0]]){
    case "-":
        $result = -1;
        $currentMap++;
        // création du tableau final
        $GLOBALS['$out'] = fullMap($n);
        $pdo->query("UPDATE game_".$_COOKIE["gameId"]." SET current_map=". $currentMap ." WHERE id=".$_COOKIE["playerId"]);
        break;
    default:
        $GLOBALS['$out'] = "[";
        decouverte($_POST['x'], $_POST['y'], $n);
        $GLOBALS['$out'] = rtrim($GLOBALS['$out'], ", ");
        $GLOBALS['$out'] .= ']';
        $GLOBALS['$casesRestantes'] ? $result = $GLOBALS['$count'] : $result = -2;
        break;
}
?>