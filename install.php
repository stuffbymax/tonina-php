<?php
session_start();

// --- Pre-installation Checks ---
$errors = [];
$is_installable = true;

if (file_exists('config.php')) {
    header('Location: index.php');
    exit;
}

if (version_compare(PHP_VERSION, '7.2.0', '<')) {
    $errors[] = 'PHP version 7.2.0 or higher is required.';
    $is_installable = false;
}
if (!function_exists('json_decode')) {
    $errors[] = 'The JSON PHP extension is required.';
    $is_installable = false;
}
if (!is_writable(__DIR__)) {
    $errors[] = "The root application directory is not writable. Please check its permissions.";
    $is_installable = false;
}

// --- Form Submission Logic ---
$form_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_installable) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $form_error = 'Username and password cannot be empty.';
    } elseif (strlen($password) < 8) {
        $form_error = 'Password must be at least 8 characters long.';
    } else {
        // Create directories
        if (!is_dir('data')) mkdir('data', 0755, true);
        if (!is_dir('music')) mkdir('music', 0755, true);
        if (!is_dir('themes')) mkdir('themes', 0755, true); // Create themes folder if not present

        // Create initial files
        $users = [[
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'admin'
        ]];
        file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT));
        file_put_contents('data/playlists.json', json_encode([], JSON_PRETTY_PRINT));

        // Create config file
        $config_content = "<?php
return [
    'music_folder' => 'music/',
    'theme' => 'spotify.css',
];";
        file_put_contents('config.php', $config_content);

        header('Location: index.php?installed=true');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Installation</title>
    <link rel="stylesheet" href="themes/light.css">
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2>Audio Player Installation</h2>
            <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <h4>Server Requirements Not Met</h4>
                    <ul><?php foreach ($errors as $error): ?><li><?php echo $error; ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <p>Welcome! This will set up your audio player system. Please create the first administrator account.</p>
            <?php if ($form_error): ?><p class="error"><?php echo $form_error; ?></p><?php endif; ?>

            <form action="install.php" method="post">
                <div class="form-group">
                    <label for="username">Admin Username</label>
                    <input type="text" id="username" name="username" required <?php if (!$is_installable) echo 'disabled'; ?>>
                </div>
                <div class="form-group">
                    <label for="password">Admin Password (min. 8 characters)</label>
                    <input type="password" id="password" name="password" required <?php if (!$is_installable) echo 'disabled'; ?>>
                </div>
                <button type="submit" class="btn" <?php if (!$is_installable) echo 'disabled'; ?>>Install</button>
            </form>
        </div>
    </div>
    <footer>&copy; 2025 MartinP MIT. Made with PHP</footer>

</body>
</html>