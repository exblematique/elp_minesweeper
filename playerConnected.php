<?php
$pdo = new PDO("mysql:host=mysql-elp.alwaysdata.net;dbname=elp_demineur", "elp", "Esaip49");
$sql = "SELECT pseudo FROM game_".$_COOKIE["gameId"];
$str=$pdo->query($sql);
if (file_exists("games/".$_COOKIE["gameId"]))
    header("Location: game.html");
else {
    while($row = $str->fetch())
        echo "<li>$row[0]</li>";
}
?>