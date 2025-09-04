<?php
http_response_code(401);
$config = file_exists('config.php') ? include('config.php') : ['theme' => 'light.css'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>401 - Unauthorized</title>
    <link rel="stylesheet" href="themes/base.css">
    <link rel="stylesheet" href="themes/<?php echo htmlspecialchars($config['theme']); ?>">
</head>
<body>
<div class="container">
    <div class="login-container" style="text-align: center;">
        <h1 style="font-size: 4rem; margin-bottom: 0;">401</h1>
        <h2>Unauthorized</h2>
        <p>You must be logged in to view this page. Please log in to continue.</p>
        <div style="margin-top: 2rem;">
            <a href="index.php" class="btn">Login</a>
        </div>
    </div>
</div>
<footer>&copy; 2025 MartinP MIT. Made with PHP</footer>
</body>
</html>