<?php
$name = trim($_POST['name'] ?? '');
if ($name === '') {
    echo json_encode(['exists' => true]);
    exit;
}

$normalized = strtolower(preg_replace('/\s+/', ' ', $name));
$rsvpLogFile = 'rsvp_log.txt';
$exists = false;

if (file_exists($rsvpLogFile)) {
    foreach (file($rsvpLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(strtolower($line), $normalized) !== false) {
            $exists = true;
            break;
        }
    }
}

echo json_encode(['exists' => $exists]);
?>
