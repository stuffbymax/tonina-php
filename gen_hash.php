<?php
if (isset($argv[1])) {
    echo "Password: " . $argv[1] . "\n";
    echo "Hash: " . password_hash($argv[1], PASSWORD_DEFAULT) . "\n";
} else {
    echo "Usage: php gen_hash.php [your_password_here]\n";
}