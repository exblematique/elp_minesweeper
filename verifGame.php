<?php
$pdo = new PDO("mysql:host=mysql-elp.alwaysdata.net;dbname=elp_demineur", "elp", "Esaip49");
$sql = "";
//echo $_POST["gameId"];
if($_POST["create"]) {
    $idFile = fopen('lastIdGame', 'r+');
    $id = fgets($idFile);
    $id += 1;
    fseek($idFile, 0);
    fputs($idFile, $id);
    fclose($idFile);
    
    $sql .= "CREATE TABLE `game_" . $id ."`(`id` INT NOT NULL AUTO_INCREMENT, 
                        `pseudo` VARCHAR(255) NOT NULL,
						PRIMARY KEY (`id`)) ;";
} else {
    $id = $_POST["gameId"];
    $verifTable = "SHOW TABLES LIKE 'game_" . $id . "';";
    if (!$pdo->query($verifTable)->rowCount()){
        echo '-1';
        exit(0);
    }
}
$sql .= "INSERT INTO game_" . $id . "(pseudo) VALUES ('". $_POST["pseudo"] ."');";
$str=$pdo->query($sql);
echo $id
?>