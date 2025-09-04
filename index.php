<?php
session_start();

if (isset($_SESSION['user'])) {
    header('Location: ' . ($_SESSION['user']['role'] === 'admin' ? 'panel_admin.php' : 'panel.php'));
    exit;
}

if (!file_exists('config.php') || !file_exists('users.json')) {
    header('Location: install.php');
    exit;
}

$config = include('config.php');
$users = json_decode(file_get_contents('users.json'), true);
$error = '';
$message = isset($_GET['installed']) ? 'Installation successful! Please log in.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    foreach ($users as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password'])) {
            $_SESSION['user'] = ['username' => $user['username'], 'role' => $user['role']];
            header('Location: ' . ($user['role'] === 'admin' ? 'panel_admin.php' : 'panel.php'));
            exit;
        }
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="themes/<?php echo htmlspecialchars($config['theme']); ?>">
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2>Login</h2>
            <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
            <?php if ($message): ?><p class="success"><?php echo $message; ?></p><?php endif; ?>
            <form action="index.php" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn">Login</button>
            </form>
        </div>
    </div>
    <footer>&copy; 2025 MartinP MIT. Made with PHP</footer>
</body>
</html>