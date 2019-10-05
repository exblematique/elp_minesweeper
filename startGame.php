<?php
$pdo = new PDO("mysql:host=mysql-elp.alwaysdata.net;dbname=elp_demineur", "elp", "Esaip49");

//Créer la partie lorsque le joueur host la démarre
$gameFolder = "./games/".$_COOKIE["gameId"];
if (!is_dir($gameFolder) && $_COOKIE["playerId"]=="1") {
    mkdir($gameFolder);
    $endTime = 'ADDTIME(CURRENT_TIME, ":'.$_POST['time'].'")';
    
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