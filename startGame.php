<?php
include('Secure/pdo.php');

//Créer la partie lorsque le joueur host la démarre
$gameFolder = "./games/".$_COOKIE["gameId"];
if (!is_dir($gameFolder) && $_COOKIE["playerId"]=="1") {
    is_numeric($_POST['time']) ? $m = +$_POST['time'] : exit(1);
    if (!is_int($m) OR $m<1 OR $m>1500) exit(1);
    $h = intdiv($m, 60);
    $m %= 60;
    $endTime = 'ADDTIME(CURRENT_TIMESTAMP, "'.$h.':'.$m.'")';
    //echo $pdo->query('SELECT '.$endTime)->fetch()[0];
    //exit(0);
    mkdir($gameFolder);
    
    //Selectionne le mode de jeu
    switch ($_POST['mode']){
        case "same": //Tout le monde possède la même carte
            $query = 'INSERT INTO games(id, game_mode, end_time, difficult_x, difficult_y, mines)
                    VALUES ('.$_COOKIE["gameId"].', "'.$_POST['mode'].'", '.$endTime.', '.$_POST['difficultX'].', '.$_POST['difficultY'].', '.$_POST['mines'].');';
            break;
        default: //Chacun choisi sa carte
            $query = 'INSERT INTO games(id, game_mode, end_time)
                    VALUES ('.$_COOKIE["gameId"].', "'.$_POST['mode'].'", '.$endTime.');';
            break;
    }
    $pdo->query($query);
}
?>