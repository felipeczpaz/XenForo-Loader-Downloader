<?php

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.0 405 Method Not Allowed");
    echo "Only POST requests are allowed";
    exit;
}

// Ensure PHP version is at least 7.0.0
if (version_compare(PHP_VERSION, '7.0.0', '<')) {
    die("PHP 7.0.0 or newer is required. You are running PHP " . PHP_VERSION . ". Please ask your host to upgrade PHP.");
}

// Function to generate a random name
function generateRandomName($length = 10) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $randomName = '';
    $maxIndex = strlen($characters) - 1;

    for ($i = 0; $i < $length; $i++) {
        $randomName .= $characters[rand(0, $maxIndex)];
    }

    return $randomName;
}

// Function to handle file download
function downloadClient() {
    $filePath = 'path_to_your_loader_exe.exe';

    if (file_exists($filePath)) {
        $randomFileName = generateRandomName() . '.exe';

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $randomFileName . '"');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
    } else {
        echo "File not found.";
    }
}

// Function to check user permissions for downloading
function doesUserHavePermissionToDownload($user) {
    $allowedGroupIds = [3, 4, 5]; // Moderator, Admin, Customer group IDs

    if (!$user['user_id'] || $user['is_banned']) {
        return false;
    }

    if (in_array($user['user_group_id'], $allowedGroupIds)) {
        return true;
    }

    if (in_array(5, $user->secondary_group_ids)) {
        return true;
    }

    if ($user['is_moderator'] || $user['is_admin'] || $user['is_super_admin']) {
        return true;
    }

    return false;
}

require __DIR__ . '/src/XF.php';

XF::start(__DIR__);
$app = \XF::setupApp('XF\Pub\App');
$app->start();

$user = \XF::visitor();

if (doesUserHavePermissionToDownload($user)) {
    downloadClient();
} else {
    header('HTTP/1.1 403 Forbidden');
    echo 'You are not allowed to download this Cheat.';
}
?>
