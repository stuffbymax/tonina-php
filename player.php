<?php
session_start();
if(!isset($_SESSION['user'])){header("Location:index.php");exit();}
$cats=['Rock'=>['song1.mp3','song2.mp3'],'Pop'=>['song3.mp3','song4.mp3']];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>Music Player</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<h2>Welcome, <?php echo $_SESSION['user'];?>!</h2>
<audio id="player" controls></audio>
<?php foreach($cats as $c=>$songs):?>
<div class="category"><?php echo $c;?></div>
<ul>
<?php foreach($songs as $s):?>
<li onclick="playSong('<?php echo $c.'/'.$s;?>',this)"><?php echo pathinfo($s,PATHINFO_FILENAME);?></li>
<?php endforeach;?>
</ul>
<?php endforeach;?>
<form action="logout.php" method="post"><button class="logout" type="submit">Logout</button></form>
<script>
let player=document.getElementById('player'),activeSong;
function playSong(path,el){
player.src='music/'+path;player.play();
if(activeSong)activeSong.classList.remove('active');
el.classList.add('active');activeSong=el;
}
</script>
<?php if($_SESSION['user']=='admin'): ?>
<p><a href="panel.php" style="color:#1DB954">Admin Panel</a></p>
<?php endif; ?>
</body>
</html>
