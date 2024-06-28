<?php

define('FILE_PATH', 'path_to_your_loader_exe.exe');

function checkRequestMethod() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit(json_encode([
            'status' => 405,
            'error' => 'method_not_allowed',
            'message' => 'Method not allowed.'
        ]));
    }
}

function checkPhpVersion() {
    if (version_compare(PHP_VERSION, '7.0.0', '<')) {
        http_response_code(500);
        exit(json_encode([
            'status' => 500,
            'error' => 'php_version_requirement_not_met',
            'message' => 'PHP version requirement not met.'
        ]));
    }
}

function generateRandomName($length = 10) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $randomName = '';
    $maxIndex = strlen($characters) - 1;

    for ($i = 0; $i < $length; $i++) {
        $randomName .= $characters[rand(0, $maxIndex)];
    }

    return $randomName;
}

function downloadClient() {
    if (!file_exists(FILE_PATH)) {
        http_response_code(404);
        exit(json_encode(['error' => 'file_not_found', 'message' => 'File not found.']));
    }

    $randomFileName = generateRandomName() . '.exe';

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $randomFileName . '"');
    header('Content-Length: ' . filesize(FILE_PATH));

    readfile(FILE_PATH);
}

function isUserBanned($userData) {
    return !empty($userData['is_banned']);
}

function isUserCustomer($user) {
    $customerGroupId = 5;

    if ($user['user_group_id'] === $customerGroupId) {
        return true;
    }

    return in_array($customerGroupId, $user['secondary_group_ids']);
}

require __DIR__ . '/src/XF.php';

XF::start(__DIR__);
$app = \XF::setupApp('XF\Pub\App');
$app->start();

$user = \XF::visitor();

if (isUserCustomer($user)) {
    downloadClient();
    return;
}

http_response_code(403);
echo json_encode(['error' => 'forbidden', 'message' => 'You do not have permission to download this cheat.']);

?>