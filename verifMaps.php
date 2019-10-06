<?php
/*****************************************************************************
*************************** VARIABLES EXISTANTES : ***************************
******************************************************************************
    - $GLOBALS['$mapMines'] : contient la carte complète des bombes (-) et du nombre de bombes à proximité
    - $GLOBALS['$playerMap'] : tableau de booléen, true quand la case est découverte par le joueur, false sinon 
    - $GLOBALS['$casesRestantes'] : nombre de cases restantes à découvrir avant de gagner
    - $GLOBALS['$addScore'] : Variable contenant le multiplicateur de score, +valeur++ à chaque case découverte
*****
    - $pdo contient la base de données utilisée pour le programme   **********/
include('pdo');
/****
    - $game_mode : contient le nom du mode de jeu sélectionné
    - $currentMap : numéro de la carte chargée par le joueur sélectionné
    - $gameFolder : dossier de jeu de la partie
    - $remindTime : temps restant pour la partie
    - $n[x, y] : tableau contenant la taille du plateau
    - $b : nombre de bombes présentes                               **********/
$game_mode = $pdo->query('SELECT game_mode FROM games WHERE id='.$_COOKIE["gameId"])->fetch()[0];
$currentMap = $pdo->query("SELECT current_map FROM game_".$_COOKIE["gameId"]." WHERE id=".$_COOKIE["playerId"])->fetch()[0];
$gameFolder = "./games/".$_COOKIE["gameId"];
$remindTime = $pdo->query("SELECT MINUTE(TIMEDIFF(end_time, start_time)) FROM games WHERE id=".$_COOKIE['gameId'])->fetch()[0];
switch ($_POST['difficult']){
    case "easy":
        $n = array(9, 9);
        $b = 10;
        break; 
    case "medium":
        $n = array(16, 16);     //Nombre de colonnes et lignes
        $b = 40;                //Nombre de bombes
        break;
    case "hard":
        $n = array(30, 16);
        $b = 99;
        break;
    default:
        $settings = $pdo->query("SELECT difficult_x, difficult_y, mines FROM games WHERE id=".$_COOKIE["gameId"])->fetch();
        $n = array($settings[0], $settings[1]);
        $b = $settings[2];
        break; 
}

    



/************************************************************************************   
*********************************   FUNCTIONS   *************************************
*************************************************************************************/
function decouverte($x, $y, $n){
    /****
        Fonction par récurcivité qui regarde quelles cases ont été decouverte par le joueur
        Si une case ne contient pas de mines à proximité, on regarde les cases alentours
    ****/
        
    //Si la case n'est pas découverte alors on vient de la decouvrir
    $currentSquare = $x+$y*$n[0];
    if ($GLOBALS['$playerMap'][$currentSquare] == '0'){
        $GLOBALS['$playerMap'][$currentSquare] = '1';
        $GLOBALS['$count']++;
        $GLOBALS['$out'] .= '{"x":'.$x.', "y":'.$y.', "z":'.$GLOBALS['$mapMines'][$currentSquare].'}, ';
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


function fullMap($n){
    /****
        Fonction qui envoie toutes les parties de la map qui n'ont pas encore été découverte par le joueur
    ****/
    $out = '';
    for ($x=0; $x<$n[0]; $x++){
        for ($y=0; $y<$n[1]; $y++){
            if (!$GLOBALS['$playerMap'][$x+$y*$n[0]]){
                $z = '"'.$GLOBALS['$mapMines'][$x+$y*$n[0]].'"';
                $out .= '{"x":'.$x.', "y":'.$y.', "z":'.$z.'}, ';
            }
        }
    }
    $out = rtrim($out, ", ");
    return '['.$out.']';
}

/*************************************************************************************/    
/********************************   PROGRAMME   **************************************/
/*************************************************************************************/
//S'il reste du temps, lance le script du mode de jeu selectionné sinon envoie la carte complète
if ($remindTime > 0) {
    include('gameMode/'.$game_mode.'.php');

    $pdo->query("UPDATE game_".$_COOKIE["gameId"]." SET player_map='".$GLOBALS['$playerMap']."' WHERE id=".$_COOKIE["playerId"]);
    $pdo->query("UPDATE game_".$_COOKIE["gameId"]." SET squares_remind='".$GLOBALS['$casesRestantes']."' WHERE id=".$_COOKIE["playerId"]);
    $pdo->query("UPDATE game_".$_COOKIE["gameId"]." SET add_score='".$GLOBALS['$addScore']."' WHERE id=".$_COOKIE["playerId"]);
    $pdo->query("UPDATE game_".$_COOKIE["gameId"]." SET score='".$GLOBALS['$score']."' WHERE id=".$_COOKIE["playerId"]);
    echo ('{"initSquare": '.$result.',
            "score": '.$GLOBALS['$score'].',
            "remindSquare": '.$GLOBALS['$casesRestantes'].',
            ');
    if (!$GLOBALS['$casesRestantes']) {
        echo ('"pseudo": "'.$pdo->query("SELECT pseudo FROM game_".$_COOKIE["gameId"]." WHERE id=".$_COOKIE["playerId"])->fetch()[0].'",
            ');
        $currentMap++;
        $pdo->query("UPDATE game_".$_COOKIE["gameId"]." SET current_map=". $currentMap ." WHERE id=".$_COOKIE["playerId"]);
        $GLOBALS['$out'] .= ', ';
        for ($x=0; $x<$n[0]; $x++){
            for ($y=0; $y<$n[1]; $y++){
                if ($GLOBALS['$mapMines'][$x+$y*$n[0]] == '-') $GLOBALS['$out'] .= '{"x":'.$x.', "y":'.$y.', "z":"-"}, ';
            }
        }
        $GLOBALS['$out'] = rtrim($GLOBALS['$out'], ", ");
    }
    echo('"newValues":'.$GLOBALS['$out'].'}');
}
else $GLOBALS['$out'] = fullMap($n);
?>