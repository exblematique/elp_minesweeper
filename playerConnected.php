<?php
include('Secure/pdo.php');
$sql = "SELECT pseudo FROM game_".$_COOKIE["gameId"];
$str=$pdo->query($sql);
if (!file_exists("games/".$_COOKIE["gameId"])){
    while($row = $str->fetch())
        echo "<li>$row[0]</li>";
} else echo $pdo->query("SELECT MINUTE(TIMEDIFF(end_time, start_time)) FROM games WHERE id=".$_COOKIE['gameId'])->fetch()[0];
?>