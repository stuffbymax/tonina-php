<?php
session_start();
$usersFile = 'users.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

// Redirect to installer if no admin
if(!isset($users['admin'])){ header("Location: install.php"); exit(); }
$users=json_decode(file_get_contents($usersFile),true);
$msg='';
if(isset($_POST['username'],$_POST['password'])){
    $u=$_POST['username']; $p=$_POST['password'];
    if(isset($users[$u]) && password_verify($p,$users[$u])){
        $_SESSION['user']=$u;
        header("Location:player.php");exit();
    }else $msg='Access Denied!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>Login</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<h2>Login</h2>
<?php if($msg)echo"<p>$msg</p>"; ?>
<form method="post">
<input type="text" name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>
<button type="submit">Login</button>
</form>
</body>
</html>
