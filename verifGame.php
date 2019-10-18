<?php
include('Secure/pdo.php');

if($_POST["create"]) {
    $idFile = fopen('lastIdGame', 'r+');
    $id = fgets($idFile);
    $id += 1;
    fseek($idFile, 0);
    fputs($idFile, $id);
    fclose($idFile);
    
    $sql = "CREATE TABLE `game_".$id."`(`id` INT NOT NULL AUTO_INCREMENT, 
                        `pseudo` VARCHAR(255) NOT NULL,
                        `score` INT DEFAULT 0,
                        `add_score` INT NOT NULL DEFAULT 1,
                        `current_map` INT DEFAULT 0,
                        `squares_remind` INT DEFAULT 0,
                        `player_map` VARCHAR(600) DEFAULT '0',
						PRIMARY KEY (`id`)) ;";
    $pdo->query($sql);
} else {
    $id = $_POST["gameId"];
    $verifTable = "SHOW TABLES LIKE 'game_" . $id . "';";
    if (!$pdo->query($verifTable)->rowCount()){
        echo '-1';
        exit(0);
    }
}
$sql = "INSERT INTO game_" . $id . "(pseudo) VALUES ('". $_POST["pseudo"] ."');";
$str=$pdo->query($sql);
$playerId = $pdo->query("SELECT MAX(id) FROM game_".$id." WHERE pseudo='".$_POST["pseudo"]."';")->fetch();
setcookie("playerId", $playerId[0]);
setcookie("gameId", $id);
echo $id
?>