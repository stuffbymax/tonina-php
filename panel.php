<?php
session_start();
$usersFile = 'users.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

// Only allow admin
if(!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin'){
    header("Location:index.php");
    exit();
}

$msg = '';

// Handle Add User
if(isset($_POST['action']) && $_POST['action'] === 'add'){
    $newUser = $_POST['new_username'];
    $newPass = $_POST['new_password'];
    if(isset($users[$newUser])){
        $msg = "User already exists!";
    } else {
        $users[$newUser] = password_hash($newPass, PASSWORD_DEFAULT);
        file_put_contents($usersFile, json_encode($users));
        $msg = "User added successfully!";
    }
}

// Handle Update User
if(isset($_POST['action']) && $_POST['action'] === 'update'){
    $oldUser = $_POST['old_username'];
    $newUser = $_POST['new_username'];
    $newPass = $_POST['new_password'];

    if(isset($users[$oldUser])){
        // Remove old if username changed
        if($oldUser !== $newUser){
            $users[$newUser] = $users[$oldUser];
            unset($users[$oldUser]);
        }
        // Update password if provided
        if(!empty($newPass)){
            $users[$newUser] = password_hash($newPass, PASSWORD_DEFAULT);
        }
        file_put_contents($usersFile, json_encode($users));
        $msg = "User updated successfully!";
    } else {
        $msg = "User not found!";
    }
}

// Handle Delete User
if(isset($_POST['action']) && $_POST['action'] === 'delete'){
    $delUser = $_POST['del_user'];
    if(isset($users[$delUser]) && $delUser !== 'admin'){
        unset($users[$delUser]);
        file_put_contents($usersFile, json_encode($users));
        $msg = "User deleted successfully!";
    } else {
        $msg = "Cannot delete admin!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Panel</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<h2>Admin Panel</h2>
<?php if($msg) echo "<p>$msg</p>"; ?>
<a href="player.php" style="color:#1DB954">Back to Player</a> | 
<a href="logout.php" style="color:#1DB954">Logout</a>

<h3>Existing Users</h3>
<table border="1" cellpadding="5" style="color:#fff;">
<tr><th>Username</th><th>Actions</th></tr>
<?php foreach($users as $u=>$h): ?>
<tr>
<td><?php echo $u; ?></td>
<td>
<form method="post" style="display:inline;">
<input type="hidden" name="action" value="delete">
<input type="hidden" name="del_user" value="<?php echo $u; ?>">
<button type="submit">Delete</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>

<h3>Add New User</h3>
<form method="post">
<input type="hidden" name="action" value="add">
<input type="text" name="new_username" placeholder="Username" required>
<input type="password" name="new_password" placeholder="Password" required>
<button type="submit">Add User</button>
</form>

<h3>Update User</h3>
<form method="post">
<input type="hidden" name="action" value="update">
<select name="old_username" required>
<option value="">Select user</option>
<?php foreach($users as $u=>$h): ?>
<option value="<?php echo $u;?>"><?php echo $u;?></option>
<?php endforeach; ?>
</select>
<input type="text" name="new_username" placeholder="New Username">
<input type="password" name="new_password" placeholder="New Password">
<button type="submit">Update User</button>
</form>
</body>
</html>
