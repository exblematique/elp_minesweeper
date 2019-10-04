<?php
$pdo = new PDO("mysql:host=mysql-elp.alwaysdata.net;dbname=elp_demineur", "elp", "Esaip49");
$sql = "SELECT pseudo, score FROM game_".$_COOKIE["gameId"];
$str=$pdo->query($sql);
echo '<tr id="title"><th>Pseudo</th><th>Score</th></tr>';
while($row = $str->fetch()){
	echo '<tr>';
	for($i=0; $i<2; $i++) echo "<td>$row[$i]</td>";
	echo '</tr>';		
}

?>