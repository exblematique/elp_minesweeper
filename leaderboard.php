<?php
include('Secure/pdo.php');
$sql = "SELECT id, pseudo, score FROM game_".$_COOKIE["gameId"]." ORDER BY score DESC";
$str=$pdo->query($sql);
echo '<tr id="title"><th>Pseudo</th><th>Score</th></tr>';
while($row = $str->fetch()){
	if ($row[0]==$_COOKIE["playerId"]) echo '<tr id="me">';
    else echo '<tr>';
	for($i=1; $i<3; $i++) echo "<td>$row[$i]</td>";
	echo '</tr>';
}

?>