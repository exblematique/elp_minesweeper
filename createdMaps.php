<?php
////// Variables globales
$b = 5;   //Nombre de bombes
$n = 5;   //Nombre de ligne et colonne
$GLOBALS['$grilleMine'] = array();     //Contient la grille des positions des mines
$GLOBALS['$grilleJoueur'] = array();   //Grille booléen contenant les cases découverte par le joueur
$GLOBALS['$casesRestantes'] = 0;
$coord = setup($n, $b);
foreach ($GLOBALS['$grilleMine'] as $x){
    foreach ($x as $y) echo $y;
    echo '<br/>';
}
echo '<br/>';
echo '<br/>';
foreach ($GLOBALS['$grilleJoueur'] as $x){
    foreach ($x as $y) echo $y;
    echo '<br/>';
}

////// Fonctions

function bombes($coordDepart, $n, $b){
    /*
On crée une liste avec toutes les coordonnées sauf celle de départ,
on la mélange, et on renvoie les b-premiers éléments
    */
    $temp = array();
    $out = array();

    //Création d'une liste contenant toutes les coordonnées
    for($x=0; $x<$n; $x++){
        for($y=0; $y<$n; $y++)
            $temp[] = array($x,$y);
    }
    shuffle($temp);
    for ($i=0; $i<$b; $i++) {
        $temp[$i]==$coordDepart ? $out[]=$temp[$b] : $out[]=$temp[$i];
    }
    return $out;
}

function IsAdjacente($x,$y,$m){
    $dx=abs($x-$m[0]);
    $dy=abs($y-$m[1]);
    return max($dx,$dy)==1;
}

function NbMines($x,$y,$M){
    $out = 0;     //contient le nombre de mines autour de la case

    ////On teste la distance avec chaque bombe et incrémente out si la bombe est à proximité
    foreach ((array)$M as $m){
        if (IsAdjacente($x,$y,$m)) $out = $out + 1;
    }
    return $out;
}

function IsMine($x,$y,$M){
    //On regarde si chaque mine est une bombe
    foreach((array)$M as $m){
        if ($m[0]==$x && $m[1]==$y) return true;
    }
    return false;
}

function creationGrille($coord, $n, $b){
    /*
Le démineur contient deux grilles :
    - grilleMine qui contient les cases avec les mines et la proximité de celle-ci
    - grilleJoueur qui contient True si la case à été découverte par le joueur
    */
    $M = bombes($coord, $n, $b);
    
    for($x=0; $x<$n; $x++){
        $GLOBALS['$grilleMine'][] = array();
        $GLOBALS['$grilleJoueur'][] = array();
        
        for($y=0; $y<$n; $y++){
            if (IsMine($x,$y,$M)) $GLOBALS['$grilleMine'][$x][] = -1;
            else $GLOBALS['$grilleMine'][$x][] = NbMines($x,$y,$M);
            $GLOBALS['$grilleJoueur'][$x][] = false;
        }
    }
    decouverte($coord, $n);
}

function decouverte($coord, $n){
    /*
Fonction par récurcivité qui regarde quelles cases ont été decouverte par le joueur
Si une case ne contient pas de mines à proximité, on regarde les cases alentours
    */
    //Si la case n'est pas découverte alors on vient de la decouvrir
    $x = $coord[0];
    $y = $coord[1];
    if (!$GLOBALS['$grilleJoueur'][$x][$y]){
        $GLOBALS['$grilleJoueur'][$x][$y] = true;
        $GLOBALS['$casesRestantes'] -= 1;
        //S'il y a pas de mine à proximité on débloque les cases environnantes avec sécurité de sortie de tableau
        if (!$GLOBALS['$grilleMine'][$x][$y]){
            for ($i=$x-1; $i<$x+2; $i++){
                for ($j=$y-1; $j<$y+2; $j++){
                    if ($i>=0 && $i<$n && $j>=0 && $j<$n)
                        decouverte(array($i,$j), $n);
                }
            }    
        }
    }
}


////// Initialisation
function setup($n, $b){
    $GLOBALS['$casesRestantes'] = $n**2 - $b;
    $coord = array(0,0);
    ////On créer la map
    creationGrille($coord, $n, $b);
    return $coord;
}
?>