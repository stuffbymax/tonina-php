<?php
http_response_code(503);
$config = file_exists('config.php') ? include('config.php') : ['theme' => 'light.css'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 - Service Unavailable</title>
    <link rel="stylesheet" href="themes/base.css">
    <link rel="stylesheet" href="themes/<?php echo htmlspecialchars($config['theme']); ?>">
</head>
<body>
<div class="container">
    <div class="login-container" style="text-align: center;">
        <h1 style="font-size: 4rem; margin-bottom: 0;">503</h1>
        <h2>Service Unavailable</h2>
        <p>Our site is temporarily down for maintenance. Please try again in a few minutes.</p>
        <div style="margin-top: 2rem;">
            <a href="index.php" class="btn">Reload</a>
        </div>
    </div>
</div>
<footer>&copy; 2025 MartinP MIT. Made with PHP</footer>
</body>
</html>