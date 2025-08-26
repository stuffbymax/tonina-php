<?php
session_start();

if (!isset($_SESSION['user'])) { // Allow admins to view this page too if you want, or keep it user-specific
    header('Location: index.php');
    exit;
}

$config = include('config.php');
$playlists = json_decode(file_get_contents('data/playlists.json'), true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Player</title>
    <link rel="stylesheet" href="themes/<?php echo htmlspecialchars($config['theme']); ?>">
</head>
<body>
    <div class="container">
        <header>
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>!</h1>
            <div>
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <a href="panel_admin.php" class="btn">Admin Panel</a>
                <?php endif; ?>
                <a href="logout.php" class="btn">Logout</a>
            </div>
        </header>

        <h2>Playlists</h2>
        <div class="grid-container">
            <?php if (empty($playlists)): ?>
                <p>No playlists have been created yet.</p>
            <?php else: ?>
                <?php foreach ($playlists as $playlist): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($playlist['name']); ?></h3>
                        <ul class="track-list">
                            <?php foreach ($playlist['tracks'] as $track): ?>
                                <li>
                                    <span class="track-name"><?php echo htmlspecialchars(basename($track)); ?></span>
                                    <audio controls preload="none">
                                        <source src="player.php?file=<?php echo urlencode(basename($track)); ?>" type="audio/mpeg">
                                        Your browser does not support the audio element.
                                    </audio>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <footer>Made with PHP</footer>
</body>
</html>