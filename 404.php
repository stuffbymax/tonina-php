<?php
// Set the 404 HTTP response code. This is important for browsers and search engines.
http_response_code(404);

// Attempt to load the configuration to get the current theme.
// If config doesn't exist (e.g., accessed before installation), default to a safe theme.
if (file_exists('config.php')) {
    $config = include('config.php');
    $theme = $config['theme'];
} else {
    $theme = 'light.css'; // A safe default
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Not Found</title>
    <!-- Load the base and theme CSS for consistent styling -->
    <link rel="stylesheet" href="themes/base.css">
    <link rel="stylesheet" href="themes/<?php echo htmlspecialchars($theme); ?>">
</head>
<body>
<div class="container">
    <div class="login-container" style="text-align: center;">
        <h1 style="font-size: 4rem; margin-bottom: 0;">404</h1>
        <h2>Page Not Found</h2>
        <p>Sorry, the page or file you were looking for could not be found.</p>
        <div style="margin-top: 2rem;">
            <!-- A "Go Back" button that uses browser history -->
            <a href="javascript:history.back()" class="btn">Go Back</a>
            <!-- A link to the homepage -->
            <a href="index.php" class="btn">Go to Homepage</a>
        </div>
    </div>
</div>
<footer>Made with PHP</footer>
</body>
</html>