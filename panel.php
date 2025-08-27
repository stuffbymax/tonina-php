<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$config = include('config.php');
$playlists = json_decode(file_get_contents('data/playlists.json'), true);

// Supported audio formats
$mime_map = [
    'mp3'  => 'audio/mpeg',
    'wav'  => 'audio/wav',
    'ogg'  => 'audio/ogg',
    'oga'  => 'audio/ogg',
    'flac' => 'audio/flac',
    'aac'  => 'audio/aac',
    'm4a'  => 'audio/mp4',
    'weba' => 'audio/webm'
];

$music_folder = rtrim($config['music_folder'], '/') . '/';
$extensions = implode(',', array_keys($mime_map));
$all_music_files = glob($music_folder . '*.{'.$extensions.'}', GLOB_BRACE);
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

    <!-- Playlists -->
    <h2>Playlists</h2>
    <div class="grid-container">
        <?php if (empty($playlists)): ?>
            <p>No playlists created yet.</p>
        <?php else: ?>
            <?php foreach ($playlists as $playlist): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($playlist['name']); ?></h3>
                    <ul class="track-list">
                        <?php foreach ($playlist['tracks'] as $track): 
                            $basename = basename($track);
                            $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
                            $mime = $mime_map[$ext] ?? 'audio/mpeg';
                        ?>
                        <li>
                            <span class="track-name"><?php echo htmlspecialchars($basename); ?></span>
                            <audio controls preload="none">
                                <source src="player.php?file=<?php echo urlencode($basename); ?>" type="<?php echo $mime; ?>">
                                Your browser does not support the audio element.
                            </audio>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- All Music -->
    <h2>All Music</h2>
    <div class="grid-container">
        <?php if (empty($all_music_files)): ?>
            <p>No music available.</p>
        <?php else: ?>
            <ul class="track-list">
                <?php foreach ($all_music_files as $file): 
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
<footer>Made with PHP</footer>
</body>
</html>
