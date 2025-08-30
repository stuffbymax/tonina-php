<?php
session_start();

// --- Security and Setup ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    include 'index.php'; // Or header('Location: index.php');
    exit;
}

$config = include('config.php');
$music_folder_relative = $config['music_folder'];
$message = '';
$message_type = 'success';

// --- Master list of supported formats ---
$mime_map = ['mp3'=>'audio/mpeg', 'wav'=>'audio/wav', 'ogg'=>'audio/ogg', 'oga'=>'audio/ogg', 'flac'=>'audio/flac', 'aac'=>'audio/aac', 'm4a'=>'audio/mp4', 'weba'=>'audio/webm'];
$allowed_extensions = array_keys($mime_map);

// --- Handle ALL Form Submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $users = json_decode(file_get_contents('users.json'), true);
    $playlists = json_decode(file_get_contents('data/playlists.json'), true);

    // Settings Update
    if (isset($_POST['update_settings'])) {
        $config['music_folder'] = rtrim(trim($_POST['music_folder']), '/') . '/';
        $config['theme'] = $_POST['theme'];
        $config_content = "<?php\nreturn " . var_export($config, true) . ";";
        if (file_put_contents('config.php', $config_content)) {
            $message = "Settings updated successfully.";
            $config = include 'config.php'; // Reload config
            $music_folder_relative = $config['music_folder'];
        } else { $message = "Error writing to config file."; $message_type = 'error'; }
    }

    // Add User
    if (isset($_POST['add_user'])) {
        $username = trim($_POST['username']); $password = $_POST['password'];
        if (!empty($username) && !empty($password)) {
            $users[] = ['username' => $username, 'password' => password_hash($password, PASSWORD_DEFAULT), 'role' => $_POST['role']];
            file_put_contents('users.json', json_encode(array_values($users), JSON_PRETTY_PRINT));
            $message = "User '{$username}' added.";
        } else { $message = "Username and password cannot be empty."; $message_type = 'error'; }
    }

    // --- NEW: Edit User Logic ---
    if (isset($_POST['edit_user'])) {
        $id = (int)$_POST['user_id'];
        if (isset($users[$id])) {
            $users[$id]['username'] = trim($_POST['username']);
            // Only update password if a new one was entered
            if (!empty($_POST['password'])) {
                $users[$id]['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            $users[$id]['role'] = $_POST['role'];
            file_put_contents('users.json', json_encode(array_values($users), JSON_PRETTY_PRINT));
            $message = "User updated successfully.";
        }
    }

    // --- NEW: Edit Playlist Logic ---
    if (isset($_POST['edit_playlist'])) {
        $id = (int)$_POST['playlist_id'];
        if (isset($playlists[$id])) {
            $playlists[$id]['name'] = trim($_POST['name']);
            $playlists[$id]['tracks'] = $_POST['tracks'] ?? []; // Use empty array if no tracks are selected
            file_put_contents('data/playlists.json', json_encode($playlists, JSON_PRETTY_PRINT));
            $message = "Playlist updated successfully.";
        }
    }
}

// --- Handle File Upload ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['music_file'])) {
    if (isset($_FILES['music_file']['error']) && $_FILES['music_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['music_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (array_key_exists($ext, $mime_map)) {
            $upload_dir = __DIR__ . '/' . rtrim($music_folder_relative, '/') . '/';
            $filename = preg_replace("/[^a-zA-Z0-9._-]/", "", basename($file['name']));
            $destination = $upload_dir . $filename;
            if (!is_dir($upload_dir)) { @mkdir($upload_dir, 0755, true); }
            if (is_writable($upload_dir) && move_uploaded_file($file['tmp_name'], $destination)) {
                $message = "Success: File '{$filename}' was uploaded.";
            } else { $message = "Error: Could not move file. Check permissions for '{$music_folder_relative}'."; $message_type = 'error'; }
        } else { $message = "Error: Invalid file type. Allowed: " . implode(", ", array_keys($mime_map)); $message_type = 'error'; }
    }
}

// --- Handle Deletions ---
if (isset($_GET['delete_user'])) {
    $user_to_delete = $_GET['delete_user'];
    if ($user_to_delete !== $_SESSION['user']['username']) {
        $users = json_decode(file_get_contents('users.json'), true);
        $users = array_filter($users, fn($u)=>$u['username']!==$user_to_delete);
        file_put_contents('users.json', json_encode(array_values($users), JSON_PRETTY_PRINT));
        header('Location: panel_admin.php'); exit;
    }
}

// --- Load Final Data for Display ---
$users = json_decode(file_get_contents('users.json'), true);
$playlists = json_decode(file_get_contents('data/playlists.json'), true);
$glob_pattern = __DIR__ . '/' . rtrim($music_folder_relative, '/') . '/*.{' . implode(',', $allowed_extensions) . '}';
$music_files = glob($glob_pattern, GLOB_BRACE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="themes/<?php echo htmlspecialchars($config['theme']); ?>">
</head>
<body>
<div class="container">
    <header><h1>Admin Dashboard</h1><a href="logout.php" class="btn">Logout</a></header>
    <?php if ($message): ?><div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div><?php endif; ?>
    <div class="tabs">
        <button class="tab-button active" onclick="openTab(event, 'player-view')">Music Player</button>
        <button class="tab-button" onclick="openTab(event, 'management-view')">Management</button>
    </div>

<div id="player-view" class="tab-content" style="display: block;">
    <h2>Listen to Music</h2>

    <!-- Playlists Section -->
    <div class="grid-container">
        <?php if (empty($playlists)): ?>
            <p>No playlists found. Go to the Management tab to create one.</p>
        <?php else: foreach ($playlists as $playlist): ?>
            <div class="card">
                <h3><?php echo htmlspecialchars($playlist['name']); ?></h3>
                <ul class="track-list">
                    <?php foreach ($playlist['tracks'] as $track_path):
                        $basename = basename($track_path);
                        $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
                        $mime = $mime_map[$ext] ?? 'audio/mpeg';
                    ?>
                        <li>
                            <span class="track-name"><?php echo htmlspecialchars($basename); ?></span>
                            <audio controls preload="none">
                                <source src="player.php?file=<?php echo urlencode($basename); ?>" type="<?php echo $mime; ?>">
                            </audio>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <!-- All Music Section -->
    <h2>All Music Files</h2>
    <div class="grid-container">
        <?php if (empty($music_files)): ?>
            <p>No music files found in "<?php echo htmlspecialchars($music_folder_relative); ?>".</p>
        <?php else: ?>
            <ul class="track-list">
                <?php foreach ($music_files as $file):
                    $basename = basename($file);
                    $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
                    $mime = $mime_map[$ext] ?? 'audio/mpeg';
                ?>
                    <li>
                        <span class="track-name"><?php echo htmlspecialchars($basename); ?></span>
                        <audio controls preload="none">
                            <source src="player.php?file=<?php echo urlencode($basename); ?>" type="<?php echo $mime; ?>">
                        </audio>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>


    <div id="management-view" class="tab-content" style="display: none;">
        <section class="admin-section">
            <h2>Settings & Uploads</h2>
            <div class="form-grid">
                <!-- Settings Form -->
                <form action="panel_admin.php" method="post">
                    <input type="hidden" name="update_settings" value="1"><h3>Settings</h3>
                    <div class="form-group"><label>Music Folder</label><input type="text" name="music_folder" value="<?php echo htmlspecialchars($config['music_folder']); ?>" required></div>
                    <div class="form-group"><label>Theme</label><select name="theme">
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
                    <div class="form-group"><label>Select Audio File (<?php echo implode(', ', $allowed_extensions);?>)</label><input type="file" name="music_file" required></div>
                    <button type="submit" class="btn">Upload File</button>
                </form>
            </div>
        </section>

        <section class="admin-section">
            <h2>Manage Playlists</h2>
            <!-- Create Playlist Form -->
            <form action="playlist.php" method="post">
                <input type="hidden" name="action" value="create"><h3>Create New Playlist</h3>
                <div class="form-group"><label>Playlist Name</label><input type="text" name="name" required></div>
                <div class="form-group"><label>Select Tracks</label><select name="tracks[]" multiple required size="8"><?php foreach ($music_files as $file):?><option value="<?php echo htmlspecialchars($file); ?>"><?php echo htmlspecialchars(basename($file)); ?></option><?php endforeach; ?></select></div>
                <button type="submit" class="btn">Create Playlist</button>
            </form>
            <hr style="margin: 2rem 0; border-color: #333;">
            
            <!-- NEW: Edit Existing Playlists Section -->
            <h3>Edit Existing Playlists</h3>
            <?php if(empty($playlists)): ?>
                <p>No playlists to edit.</p>
            <?php else: foreach ($playlists as $id => $playlist): ?>
                <form action="panel_admin.php" method="post" class="form-grid edit-form">
                    <input type="hidden" name="edit_playlist" value="1">
                    <input type="hidden" name="playlist_id" value="<?php echo $id; ?>">
                    <div class="form-group"><label>Playlist Name</label><input type="text" name="name" value="<?php echo htmlspecialchars($playlist['name']); ?>" required></div>
                    <div class="form-group"><label>Select Tracks</label><select name="tracks[]" multiple size="8">
                        <?php foreach ($music_files as $file): $selected = in_array($file, $playlist['tracks']) ? 'selected' : ''; ?>
                        <option value="<?php echo htmlspecialchars($file); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars(basename($file)); ?></option>
                        <?php endforeach; ?>
                    </select></div>
                    <div>
                        <button type="submit" class="btn">Save Changes</button>
                        <a href="playlist.php?action=delete&id=<?php echo $id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                    </div>
                </form>
            <?php endforeach; endif; ?>
        </section>

        <section class="admin-section">
            <h2>Manage Users</h2>
            <!-- Create User Form -->
            <form action="panel_admin.php" method="post">
                <input type="hidden" name="add_user" value="1"><h3>Create New User</h3>
                <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
                <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
                <div class="form-group"><label>Role</label><select name="role"><option value="user">User</option><option value="admin">Admin</option></select></div>
                <button type="submit" class="btn">Add User</button>
            </form>
            <hr style="margin: 2rem 0; border-color: #333;">

            <!-- NEW: Edit Existing Users Section -->
            <h3>Edit Existing Users</h3>
            <?php if(count($users) <= 0): ?>
                <p>No users to edit.</p>
            <?php else: foreach ($users as $id => $user): ?>
                 <form action="panel_admin.php" method="post" class="form-grid edit-form">
                    <input type="hidden" name="edit_user" value="1">
                    <input type="hidden" name="user_id" value="<?php echo $id; ?>">
                    <div class="form-group"><label>Username</label><input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required></div>
                    <div class="form-group"><label>New Password</label><input type="password" name="password" placeholder="Leave blank to keep current"></div>
                    <div class="form-group"><label>Role</label><select name="role">
                        <option value="user" <?php if($user['role']=='user') echo 'selected'; ?>>User</option>
                        <option value="admin" <?php if($user['role']=='admin') echo 'selected'; ?>>Admin</option>
                    </select></div>
                    <div>
                        <button type="submit" class="btn">Save Changes</button>
                        <?php if($user['username'] !== $_SESSION['user']['username']): ?>
                        <a href="?delete_user=<?php echo urlencode($user['username']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        <?php endif; ?>
                    </div>
                </form>
            <?php endforeach; endif; ?>
        </section>
    </div>
</div>
<footer>Made with PHP</footer>
<style>.edit-form { border-top: 1px solid #333; padding-top: 1.5rem; margin-top: 1.5rem; }</style>
<script>
    function openTab(evt, tabName) {
        var i, tabcontent, tabbuttons;
        tabcontent = document.getElementsByClassName("tab-content"); for (i = 0; i < tabcontent.length; i++) { tabcontent[i].style.display = "none"; }
        tabbuttons = document.getElementsByClassName("tab-button"); for (i = 0; i < tabbuttons.length; i++) { tabbuttons[i].className = tabbuttons[i].className.replace(" active", ""); }
        document.getElementById(tabName).style.display = "block"; evt.currentTarget.className += " active";
    }
</script>
</body>
</html>