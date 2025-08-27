<?php
http_response_code(400);
$config = file_exists('config.php') ? include('config.php') : ['theme' => 'light.css'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>400 - Bad Request</title>
    <link rel="stylesheet" href="themes/base.css">
    <link rel="stylesheet" href="themes/<?php echo htmlspecialchars($config['theme']); ?>">
</head>
<body>
<div class="container">
    <div class="login-container" style="text-align: center;">
        <h1 style="font-size: 4rem; margin-bottom: 0;">400</h1>
        <h2>Bad Request</h2>
        <p>Your browser sent a request that this server could not understand.</p>
        <div style="margin-top: 2rem;">
            <a href="javascript:history.back()" class="btn">Go Back</a>
            <a href="index.php" class="btn">Homepage</a>
        </div>
    </div>
</div>
<footer>Made with PHP</footer>
</body>
</html>