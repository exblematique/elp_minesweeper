<?php
$pdo = new PDO("mysql:host=mysql-elp.alwaysdata.net;dbname=elp_demineur", "elp", "Esaip49");

$sql = "SELECT pseudo FROM game_" . $_POST["gameId"];
$str=$pdo->query($sql);
while($row = $str->fetch()){
	echo "<li>$row[0]</li>";
}
?>