<?php
session_start();

// Check if admin exists
$usersFile = 'users.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
if(isset($users['admin'])) { header("Location: index.php"); exit(); }

$msg = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $adminUser = $_POST['admin_user'];
    $adminPass = $_POST['admin_pass'];
    $storePass = $_POST['store_pass'] ?? 'no';
    $theme = $_POST['theme'] ?? 'dark';
    $primaryColor = $_POST['primary_color'] ?? '#1DB954';
    $secondaryColor = $_POST['secondary_color'] ?? '#1ED760';
    $categories = array_filter(array_map('trim', explode(',', $_POST['categories'] ?? 'Rock,Pop,Classical')));
    $playlists = array_filter(array_map('trim', explode(',', $_POST['playlists'] ?? 'Chill,Workout,Focus')));

    // Music folder from server or upload
    if(isset($_POST['server_folder']) && $_POST['server_folder']!==''){
        $musicFolder = rtrim($_POST['server_folder'],'/');
        if(!file_exists($musicFolder)) mkdir($musicFolder,0777,true);
        foreach($categories as $cat) mkdir("$musicFolder/$cat",0777,true);
    }

    if(isset($_FILES['music_folder'])){
        $musicFolder = 'music_upload/';
        if(!file_exists($musicFolder)) mkdir($musicFolder,0777,true);
        foreach($_FILES['music_folder']['name'] as $key => $fullPath){
            $tmpName = $_FILES['music_folder']['tmp_name'][$key];
            $relativePath = str_replace('\\','/', $fullPath);
            $dest = $musicFolder . $relativePath;
            $dir = dirname($dest);
            if(!file_exists($dir)) mkdir($dir,0777,true);
            move_uploaded_file($tmpName, $dest);
        }
    }

    // CSS
    @mkdir('css',0777,true);
    $bg = ($theme==='dark')?'#121212':'#fff';
    $text = ($theme==='dark')?'#fff':'#000';
    $css = "body{background:$bg;color:$text;font-family:Arial;}
h2,h3{color:$primaryColor;} a,button{color:$primaryColor;} a.active{color:$secondaryColor;}
input,select,button{margin:5px 0;padding:5px;border-radius:4px;border:none;} button{cursor:pointer;} li{margin:5px 0;} audio{width:100%;}";
    file_put_contents('css/style.css',$css);

    // JSON storage
    file_put_contents('users.json', json_encode([$adminUser => password_hash($adminPass, PASSWORD_DEFAULT)]));
    file_put_contents('config.json', json_encode([
        'music_folder' => $musicFolder ?? 'music',
        'categories' => $categories,
        'theme' => $theme,
        'primary_color' => $primaryColor,
        'secondary_color' => $secondaryColor
    ]));

    $plistAssoc = [];
    foreach($playlists as $pl) $plistAssoc[$pl] = [];
    file_put_contents('playlists.json', json_encode($plistAssoc));

    if($storePass==='yes') file_put_contents('admin_pass.txt',$adminPass);
    file_put_contents('install.lock',"Installed on ".date('Y-m-d H:i:s'));
    $msg = "Installation complete! <a href='index.php'>Login</a>.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>ToninaPHP Installer</title></head>
<body>
<h2>ToninaPHP Installation</h2>
<?php if($msg) echo "<p>$msg</p>"; ?>
<form method="post" enctype="multipart/form-data">
<h3>Admin Setup</h3>
<input type="text" name="admin_user" placeholder="Username" required><br>
<input type="password" name="admin_pass" placeholder="Password" required><br>
<label>Store Admin Passkey?</label>
<select name="store_pass"><option value="no" selected>No</option><option value="yes">Yes</option></select><br><br>

<h3>Music Library</h3>
<p>Server folder (existing path) or leave blank to upload:</p>
<input type="text" name="server_folder" placeholder="music"><br>
<p>Upload local folder:</p>
<input type="file" name="music_folder[]" webkitdirectory directory multiple><br><br>

<h3>Categories</h3>
<input type="text" name="categories" value="Rock,Pop,Classical"><br><br>

<h3>Theme</h3>
<select name="theme"><option value="dark" selected>Dark</option><option value="light">Light</option></select><br>
Primary color: <input type="color" name="primary_color" value="#1DB954"><br>
Secondary color: <input type="color" name="secondary_color" value="#1ED760"><br><br>

<h3>Default Playlists</h3>
<input type="text" name="playlists" value="Chill,Workout,Focus"><br><br>

<button type="submit">Install ToninaPHP</button>
</form>
</body>
</html>
