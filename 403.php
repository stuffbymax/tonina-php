<?php
// Set the 403 HTTP response code. This is important for browsers and search engines.
http_response_code(403);

// Attempt to load the configuration to get the current theme.
// If config doesn't exist (e.g., accessed before installation), default to a safe theme.
$config = file_exists('config.php') ? include('config.php') : ['theme' => 'light.css'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Forbidden</title>
    <link rel="stylesheet" href="themes/base.css">
    <link rel="stylesheet" href="themes/<?php echo htmlspecialchars($config['theme']); ?>">
</head>
<body>
<div class="container">
    <div class="login-container" style="text-align: center;">
        <h1 style="font-size: 4rem; margin-bottom: 0;">403</h1>
        <h2>Forbidden</h2>
        <p>You do not have the necessary permissions to access this page.</p>
        <div style="margin-top: 2rem;">
            <a href="javascript:history.back()" class="btn">Go Back</a>
            <a href="index.php" class="btn">Homepage</a>
        </div>
    </div>
</div>
<footer>&copy; 2025 MartinP MIT. Made with PHP</footer>
</body>
</html>