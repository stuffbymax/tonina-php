<?php
// For debugging: uncomment these lines if you see a blank page.
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$config = include('config.php');
$music_folder_relative = $config['music_folder'];
$message = '';
$message_type = 'success';

// --- Handle Settings Update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $config['music_folder'] = rtrim(trim($_POST['music_folder']), '/') . '/';
    $config['theme'] = $_POST['theme'];
    $config_content = "<?php\nreturn " . var_export($config, true) . ";";
    if (file_put_contents('config.php', $config_content)) {
        $message = "Settings updated successfully.";
        $config = include('config.php'); // Reload config
        $music_folder_relative = $config['music_folder'];
    } else {
        $message = "Error: Could not write to config.php."; $message_type = 'error';
    }
}

// --- Handle Music Upload ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['music_file'])) {
    if (isset($_FILES['music_file']['error']) && $_FILES['music_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['music_file'];
        if (in_array($file['type'], ['audio/mpeg', 'audio/ogg'])) {
            $upload_dir = __DIR__ . '/' . rtrim($music_folder_relative, '/') . '/';
            $filename = preg_replace("/[^a-zA-Z0-9._-]/", "", basename($file['name']));
            $destination = $upload_dir . $filename;
            if (!is_dir($upload_dir)) { @mkdir($upload_dir, 0755, true); }
            if (is_writable($upload_dir) && move_uploaded_file($file['tmp_name'], $destination)) {
                $message = "Success: File '{$filename}' was uploaded.";
            } else { $message = "Error: Could not move file. Check if '{$music_folder_relative}' is writable."; $message_type = 'error'; }
        } else { $message = "Error: Invalid file type (MP3/OGG only)."; $message_type = 'error'; }
    } else { /* ... Upload error handling logic ... */ }
}

// --- Handle User Management ---
$users = json_decode(file_get_contents('users.json'), true);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    if (!empty($username) && !empty($password)) {
        $users[] = ['username' => $username, 'password' => password_hash($password, PASSWORD_DEFAULT), 'role' => $_POST['role']];
        file_put_contents('users.json', json_encode(array_values($users), JSON_PRETTY_PRINT));
        $message = "User '{$username}' added successfully.";
    } else { $message = "Error: Username and password cannot be empty."; $message_type = 'error'; }
}
if (isset($_GET['delete_user'])) {
    $user_to_delete = $_GET['delete_user'];
    if ($user_to_delete !== $_SESSION['user']['username']) {
        $users = array_filter($users, fn($user) => $user['username'] !== $user_to_delete);
        file_put_contents('users.json', json_encode(array_values($users), JSON_PRETTY_PRINT));
        header('Location: panel_admin.php'); exit;
    }
}

// --- Load Data for Display ---
$playlists = json_decode(file_get_contents('data/playlists.json'), true);
$music_files = glob(__DIR__ . '/' . rtrim($music_folder_relative, '/') . '/*.{mp3,ogg}', GLOB_BRACE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="themes/<?php echo htmlspecialchars($config['theme']); ?>">
</head>
<body>
<div class="container">
    <header>
        <h1>Admin Dashboard</h1>
        <a href="logout.php" class="btn">Logout</a>
    </header>

    <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="tabs">
        <button class="tab-button active" onclick="openTab(event, 'player-view')">Music Player</button>
        <button class="tab-button" onclick="openTab(event, 'management-view')">Management</button>
    </div>

    <!-- PLAYER TAB -->
    <div id="player-view" class="tab-content" style="display: block;">
        <h2>Listen to Music</h2>
        <div class="grid-container">
            <?php if (empty($playlists)): ?>
                <p>No playlists found. Go to the Management tab to create one.</p>
            <?php else: foreach ($playlists as $playlist): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($playlist['name']); ?></h3>
                    <ul class="track-list">
                        <?php foreach ($playlist['tracks'] as $track_path): ?>
                            <li>
                                <span class="track-name"><?php echo htmlspecialchars(basename($track_path)); ?></span>
                                <audio controls preload="none">
                                    <source src="player.php?file=<?php echo urlencode(basename($track_path)); ?>" type="audio/mpeg">
                                </audio>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <!-- MANAGEMENT TAB -->
    <div id="management-view" class="tab-content" style="display: none;">
        <section class="admin-section">
            <h2>Settings & Uploads</h2>
            <div class="form-grid">
                <!-- Settings Form -->
                <form action="panel_admin.php" method="post">
                    <input type="hidden" name="update_settings" value="1">
                    <h3>Application Settings</h3>
                    <div class="form-group"><label for="music_folder">Music Folder</label><input type="text" name="music_folder" value="<?php echo htmlspecialchars($config['music_folder']); ?>" required></div>
                    <div class="form-group"><label for="theme">Theme</label><select name="theme">
                        <option value="spotify.css" <?= $config['theme'] == 'spotify.css' ? 'selected' : '' ?>>Spotify Dark</option>
                        <option value="dark.css" <?= $config['theme'] == 'dark.css' ? 'selected' : '' ?>>Classic Dark</option>
                        <option value="light.css" <?= $config['theme'] == 'light.css' ? 'selected' : '' ?>>Light</option>
                        <option value="winxp.css" <?= $config['theme'] == 'winxp.css' ? 'selected' : '' ?>>Windows XP</option>
                    </select></div>
                    <button type="submit" class="btn">Save Settings</button>
                </form>
                <!-- Upload Form -->
                <form action="panel_admin.php" method="post" enctype="multipart/form-data">
                    <h3>Upload Music</h3>
                    <div class="form-group"><label for="music_file">Select Audio File (MP3/OGG)</label><input type="file" name="music_file" required></div>
                    <button type="submit" class="btn">Upload File</button>
                </form>
            </div>
        </section>

        <section class="admin-section">
            <h2>Manage Playlists</h2>
            <div class="form-grid">
                <!-- Create Playlist Form -->
                <form action="playlist.php" method="post">
                    <input type="hidden" name="action" value="create">
                    <h3>Create New Playlist</h3>
                    <div class="form-group"><label>Playlist Name</label><input type="text" name="name" required></div>
                    <div class="form-group"><label>Select Tracks</label><select name="tracks[]" multiple required size="8"><?php foreach ($music_files as $file):?><option value="<?php echo htmlspecialchars($file); ?>"><?php echo htmlspecialchars(basename($file)); ?></option><?php endforeach; ?></select></div>
                    <button type="submit" class="btn">Create Playlist</button>
                </form>
                <!-- Existing Playlists -->
                <div>
                    <h3>Existing Playlists</h3>
                    <ul class="item-list">
                        <?php if(empty($playlists)): ?>
                            <li>No playlists exist.</li>
                        <?php else: foreach ($playlists as $index => $playlist): ?>
                            <li><span><?php echo htmlspecialchars($playlist['name']); ?></span> <a href="playlist.php?action=delete&id=<?php echo $index; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a></li>
                        <?php endforeach; endif; ?>
                    </ul>
                </div>
            </div>
        </section>

        <section class="admin-section">
            <h2>Manage Users</h2>
            <div class="form-grid">
                <!-- Create User Form -->
                <form action="panel_admin.php" method="post">
                    <input type="hidden" name="add_user" value="1">
                    <h3>Create New User</h3>
                    <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
                    <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
                    <div class="form-group"><label>Role</label><select name="role"><option value="user">User</option><option value="admin">Admin</option></select></div>
                    <button type="submit" class="btn">Add User</button>
                </form>
                <!-- Existing Users -->
                <div>
                    <h3>Existing Users</h3>
                    <ul class="item-list">
                         <?php foreach ($users as $user): ?>
                            <li>
                                <span><?php echo htmlspecialchars($user['username']); ?> (<?php echo $user['role']; ?>)</span>
                                <?php if ($user['username'] !== $_SESSION['user']['username']): ?>
                                    <a href="?delete_user=<?php echo urlencode($user['username']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </section>
    </div>
</div>
<footer>Made with PHP</footer>
<script>
    function openTab(evt, tabName) {
        let i, tabcontent, tabbuttons;
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) { tabcontent[i].style.display = "none"; }
        tabbuttons = document.getElementsByClassName("tab-button");
        for (i = 0; i < tabbuttons.length; i++) { tabbuttons[i].className = tabbuttons[i].className.replace(" active", ""); }
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";
    }
</script>
</body>
</html>