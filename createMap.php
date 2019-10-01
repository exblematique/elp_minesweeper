<?php
////// Variables globales
switch ($_POST['difficult']){
    case "16":
        $n = array(16, 16);     //Nombre de colonnes et lignes
        $b = 40;                //Nombre de bombes
        break;
    case "30":
        $n = array(30, 16);
        $b = 99;
        break;
    default:
        $n = array(9, 9);
        $b = 10;
        break;
        
}

$GLOBALS['$mapMines'] = "";     //Contient la positions des mines ou le nombre à proximité
$GLOBALS['$casesRestantes'] = 0;
$coord = array($_POST['x'], $_POST['y']);
creationGrille($coord, $n, $b);


////// Fonctions

function bombes($coordDepart, $n, $b){
    /*
On crée une liste avec toutes les coordonnées sauf celle de départ,
on la mélange, et on renvoie les b-premiers éléments
    */
    $temp = array();
    $out = array();

    //Création d'une liste contenant toutes les coordonnées
    for($x=0; $x<$n[0]; $x++){
        for($y=0; $y<$n[1]; $y++){
            $temp[] = array($x,$y);
        }
    }
    shuffle($temp);
    for ($i=0; $i<$b; $i++) {
        $temp[$i]==$coordDepart ? $out[]=$temp[$b] : $out[]=$temp[$i];
    }
    return $out;
}

function creationGrille($coord, $n, $b){
    $M = bombes($coord, $n, $b);
    for($y=0; $y<$n[1]; $y++){
        for($x=0; $x<$n[0]; $x++){
            if (IsMine($x,$y,$M)) $GLOBALS['$mapMines'] .= '-';
            else $GLOBALS['$mapMines'] .= NbMines($x,$y,$M);
        }
    }
}

function IsMine($x,$y,$M){
    //On regarde si chaque mine est une bombe
    foreach((array)$M as $m){
        if ($m[0]==$x && $m[1]==$y) return true;
    }
    return false;
}

function NbMines($x,$y,$M){
    $out = 0;     //contient le nombre de mines autour de la case

    ////On teste la distance avec chaque bombe et incrémente out si la bombe est à proximité
    foreach ((array)$M as $m){
        if (IsAdjacente($x,$y,$m)) $out = $out + 1;
    }
    return $out;
}

function IsAdjacente($x,$y,$m){
    $dx=abs($x-$m[0]);
    $dy=abs($y-$m[1]);
    return max($dx,$dy)==1;
}
?>