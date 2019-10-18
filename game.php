<?php
//Initialise le programme
include('Secure/pdo.php');
$game_mode = $pdo->query('SELECT game_mode FROM games WHERE id='.$_COOKIE["gameId"])->fetch()[0];
$remindTime = $pdo->query("SELECT TIMEDIFF(end_time, CURRENT_TIMESTAMP) FROM games WHERE id=".$_COOKIE['gameId'])->fetch()[0];
//$remindTime = $pdo->query("SELECT HOUR(TIMEDIFF(end_time, CURRENT_TIMESTAMP)) MINUTE(TIMEDIFF(end_time, CURRENT_TIMESTAMP)) SECOND(TIMEDIFF(end_time, CURRENT_TIMESTAMP)) FROM games WHERE id=".$_COOKIE['gameId'])->fetch();
if ($remindTime[0] == "-") $remindTime=0;
else {
    $remindTime = preg_split("/[:]/", $remindTime);
    $remindTime = $remindTime[0]*3600 + $remindTime[1]*60 + $remindTime[2];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Démineur de l'ELP - Partie </title>
    <link rel="stylesheet" type="text/css" href="game.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>
<body>
<header>
<div id="score">SCORE ACTUEL : 0</div>
<div id="remindSquare">CASES RESTANTES : 0</div>
<div id="remindTime">TEMPS RESTANT :</div>
<button id='reset' disabled='true'>Recommencer</button>

<?php
switch ($game_mode){
    case "different":
?>
<div id="difficult">
    <div>DIFFICULTÉ</div>
    <a id="easy" value="9">Facile</a>
    <a id="medium" value="16">Moyen</a>
    <a id="hard" value="30">Difficile</a>
</div>
<?php
    break;
}
?>
    
</header>
    <table id="board"></table>
    <table id="leaderboard"></table>
    <script>
    /******* FUNCTIONS *********/
    //Charge le leaderboard
    function leaderboard() {
        $.ajax({
            url: 'leaderboard.php'
        }).done(function(result) {
            document.querySelector('#leaderboard').innerHTML = result;
        });
        if (document.querySelector("#remindTime").value != "0") setTimeout("leaderboard()", 1500);
    }
    
    //Affiche le compte à rebours
    function timer(){
        remindTime = document.querySelector('#remindTime');
        remindTime.innerHTML = "TEMPS RESTANT : " + --remindTime.value;
        remindTime.value ? setTimeout("timer()", 1000) : endTimer();
    }
    
    //Function qui s'active quand la partie est terminée
    function endTimer(){
        alert("LA PARTIE EST TERMINÉE !!!!!!");
        document.querySelector('#remindTime').innerHTML = "PARTIE TERMINÉE !"
        document.querySelector('#board').innerHTML = "";
    <?php if($game_mode == "different") {?>
        var inputs = document.querySelectorAll('#difficult a');
        for (var i=0; i<inputs.length; i++) inputs[i].onclick = function(){};
    <?php ;} ?>
    }
    
    //Fonction qui se lance une fois que tous les éléments de la page sont chargés
    function startGame(){
        var gameId=/gameId=(\d)+/.exec(document.cookie)[0];
        gameId = gameId.replace(/gameId=/, '');
        document.querySelector('title').text += gameId;
        leaderboard();
        document.querySelector('#remindTime').value = <?php echo $remindTime;?>;
        document.querySelector('#remindTime').value ? timer() : endTimer();
    }
    
    <?php if($game_mode == "different") {?>
    //Creation d'un tableau lors du changement de difficulté
    var inputs = document.querySelectorAll('#difficult a');
    for (var i=0; i<inputs.length; i++){
        /******* Changement de difficulté ***************/
        inputs[i].onclick = function(){            
            //Changement de valeur de l'input et blocage du bouton reset
            var inputs = document.querySelectorAll('#difficult a');
            for (var i=0; i<inputs.length; i++) inputs[i].className = "";
            this.className = "checked";
            document.querySelector("#reset").disabled = false;
            var difficult = this.id;
    
            //Demande de nouvelle map
            $.ajax({
                url: "verifMaps.php",
                method: "POST",
                data: {reset: true, difficult: difficult},
                dataType: "json"
            }
            <?php } else echo '$.post("verifMaps.php"';?>).done(function(result){
                var xMax = result.difficult[0];
                var yMax = result.difficult[1];
                var board = document.querySelector('#board');
                board.innerHTML = "";
                for (var y=0; y<yMax; y++){
                    var row = document.createElement("tr");
                    for (var x=0; x<xMax; x++){
                        var col = document.createElement("td");
                        var imgStatus = document.createElement("img");
                        imgStatus.id = "pos" + x + "_" + y;
                        imgStatus.xPosition=x;
                        imgStatus.yPosition=y;
                        imgStatus.src = "img/I.png";
                        imgStatus.difficult = difficult;
                        imgStatus.oncontextmenu = function(event) {
                            //Désactive le click droit et ajoute la fonctionnalité drapeau
                            if (this.src.indexOf("I.png") !== -1) this.src = "img/F.png";
                            else if (this.src.indexOf("F.png") !== -1) this.src = "img/Q.png";
                            else if (this.src.indexOf("Q.png") !== -1) this.src = "img/I.png";
                            return false;
                        };
                        imgStatus.onclick = function onImg(){
                            if (/I.png/.test(this.src)){
                                console.log("OnClick "+ this.xPosition + "_" + this.yPosition +" difficulty= " + this.difficult);
                                $.ajax({
                                    url: "verifMaps.php",
                                    method: "POST",
                                    data: {x: this.xPosition, y: this.yPosition, difficult: this.difficult},
                                    dataType: "json"
                                }).done(function(result){
                                    console.info(result);
                                    switch (result.initSquare){
                                        case -1:    //Quand on a perdu 
                                            for (var i=0; i<result.newValues.length; i++){
                                                var square = document.querySelector("#pos" + result.newValues[i].x + "_" + result.newValues[i].y);
                                                square.oncontextmenu = function(){return false;};
                                                if (result.newValues[i].z == "-"){
                                                    if (/[IQ].png/.test(square.src)) square.src = "img/M.png";
                                                } else {
                                                    /F.png/.test(square.src) ? square.src = "img/NM.png" : square.onclick = function(){};
                                                }
                                            }
                                            this.src = "img/RM.png"
                                            //alert("You lose !");
                                            break;
                                        case -2:    //Quand on a gagné
                                            for (var i=0; i<result.newValues.length; i++){
                                                var square = document.querySelector("#pos" + result.newValues[i].x + "_" + result.newValues[i].y);
                                                if (result.newValues[i].z == "-") {
                                                    square.oncontextmenu = function(event) {return false};
                                                    square.src = "img/F.png"
                                                }
                                                else square.src = "img/" + result.newValues[i].z + ".png";
                                            }
                                            //alert("Bravo " + result.pseudo + ", tu as gagné et en plus tu as de jolis yeux !");
                                            break;
                                        default:    //Partie en cours
                                            for (var i=0; i<result.initSquare; i++){
                                                var square = document.querySelector("#pos" + result.newValues[i].x + "_" + result.newValues[i].y);
                                                square.src = "img/" + result.newValues[i].z + ".png";
                                            }
                                            break;
                                    }
                                    document.querySelector('#score').innerHTML = "SCORE ACTUEL : " + result.score;
                                    document.querySelector('#remindSquare').innerHTML = "CASES RESTANTES : " + result.remindSquare;
                                    this.src = "img/" + result.initSquare + ".png";
                                }).fail(function(msgErr,status) {console.info("FAIL! Msg: "+msgErr + " Status: "+ status);});
                            }
                        };
                        col.appendChild(imgStatus);
                        row.appendChild(col);
                    }
                    board.appendChild(row);
                }
            });
    <?php if($game_mode == "different") echo '};}';?>
    
    document.querySelector('#reset').onclick = function onRst(){
        $.ajax({
                url: "verifMaps.php",
                method: "POST",
                data: {reset: true}
        }).done(function(result) {
            document.querySelector('#board').innerHTML= "";
            //Reinitialisation de la difficulté et blocage du bouton reset
            var inputs = document.querySelectorAll('#difficult a');
            for (var i=0; i<inputs.length; i++) inputs[i].className = "";
            document.querySelector("#reset").disabled = true;
        })
    };
    
    startGame();
    
    </script>
</body>
</html>