<?php
//Créer la partie lorsque le joueur host la démarre
$gameFolder = "./games/".$_COOKIE["gameId"];
if (!is_dir($gameFolder) && $_COOKIE["playerId"]=="1") mkdir($gameFolder);
?>