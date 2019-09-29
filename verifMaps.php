<?php
$pdo = new PDO("mysql:host=mysql-elp.alwaysdata.net;dbname=elp_demineur", "elp", "Esaip49");

if ($_POST['reset']){
    echo 'reset';
    $currentMap = $pdo->query("SELECT current_map FROM game_".$_COOKIE["gameId"]." WHERE id=".$_COOKIE["playerId"]);
    $pdo->query("UPDATE game_".$_COOKIE["gameId"]." SET current_map=". $currentMap+1 ." WHERE id=".$_COOKIE["playerId"]);
    exit(0);
}

switch ($_POST['difficult']){
    case 16:
        $n = array(16, 16);     //Nombre de colonnes et lignes
        $b = 40;                //Nombre de bombes
        break;
    case 30:
        $n = array(30, 16);
        $b = 99;
        break;
    default:
        $n = array(9, 9);
        $b = 10;
        break; 
}

//Créer la partie lorsque le joueur host la démarre
$gameFolder = "./games/".$_COOKIE["gameId"];
if (!is_dir($gameFolder) && $_COOKIE["playerId"]=="1") mkdir($gameFolder);

//Vérifie si le joueur possède une carte déjà active sinon la créer
$currentMap = $pdo->query("SELECT current_map FROM game_".$_COOKIE["gameId"]." WHERE id=".$_COOKIE["playerId"])->fetch()[0];
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
    $fdGame = fopen($gameFile, 'x+');
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
/********************************   FUNCTIONS   **************************************/
/*************************************************************************************/ 
function decouverte($x, $y, $n){
    /*Fonction par récurcivité qui regarde quelles cases ont été decouverte par le joueur
        Si une case ne contient pas de mines à proximité, on regarde les cases alentours */
        
    //Si la case n'est pas découverte alors on vient de la decouvrir
    $currentSquare = $x*$n[0]+$y;
    if ($GLOBALS['$playerMap'][$currentSquare] == '0'){
        $GLOBALS['$playerMap'][$currentSquare] = '1';
        $GLOBALS['$count']++;
        $GLOBALS['$out'] .= '{"x":'.$x.', "y":'.$y.', "z": '.$GLOBALS['$mapMines'][$currentSquare].'}, ';
        $GLOBALS['$casesRestantes'] -= 1;
        $GLOBALS['$score'] += $GLOBALS['$addScore']++;
            //S'il y a pas de mine à proximité on débloque les cases environnantes avec sécurité de sortie de tableau
        if ($GLOBALS['$mapMines'][$currentSquare] == "0"){
            for ($i=$x-1; $i<$x+2; $i++){
                for ($j=$y-1; $j<$y+2; $j++){
                    if ($i>=0 && $i<$n[0] && $j>=0 && $j<$n[1])
                        decouverte($i,$j,$n);
                }
            }    
        }
    }
}

/*************************************************************************************/    
/*******************************    NEW GRID    **************************************/
/*************************************************************************************/ 
$GLOBALS['$out'] = "";
$GLOBALS['$count'] = 0;
$GLOBALS['$score'] = $pdo->query("SELECT score FROM game_".$_COOKIE["gameId"]." WHERE id=".$_COOKIE["playerId"])->fetch()[0];

//Verifie la valeur de la case choisi :
switch ($GLOBALS['$mapMines'][$_POST['x']*$n[0]+$_POST['y']]){
    case "-":
        $result = -1;
        $currentMap++;
        $GLOBALS['$out'] .= '"'.$GLOBALS['$mapMines'].'"';
        $pdo->query("UPDATE game_".$_COOKIE["gameId"]." SET current_map=". $currentMap ." WHERE id=".$_COOKIE["playerId"]);
        break;
    default:
        $GLOBALS['$out'] .= ' [';
        decouverte($_POST['x'], $_POST['y'], $n);
        $GLOBALS['$out'] = rtrim($GLOBALS['$out'], ", ");
        $GLOBALS['$out'] .= ']';
        $result = $GLOBALS['$count'];
        break;
}
$pdo->query("UPDATE game_".$_COOKIE["gameId"]." SET player_map='".$GLOBALS['$playerMap']."' WHERE id=".$_COOKIE["playerId"]);
$pdo->query("UPDATE game_".$_COOKIE["gameId"]." SET squares_remind='".$GLOBALS['$casesRestantes']."' WHERE id=".$_COOKIE["playerId"]);
$pdo->query("UPDATE game_".$_COOKIE["gameId"]." SET add_score='".$GLOBALS['$addScore']."' WHERE id=".$_COOKIE["playerId"]);
$pdo->query("UPDATE game_".$_COOKIE["gameId"]." SET score='".$GLOBALS['$score']."' WHERE id=".$_COOKIE["playerId"]);
echo ('{"initSquare": '.$result.',
        "score": '.$GLOBALS['$score'].',
        "remindSquare": '.$GLOBALS['$casesRestantes'].',
        "newValues":'.$GLOBALS['$out'].'}');


?>