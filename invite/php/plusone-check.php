<?php
$name = trim($_POST['name'] ?? '');
if ($name === '') {
    echo json_encode(['blocked' => true, 'reason' => 'No name provided']);
    exit;
}

$normalized = strtolower(preg_replace('/\s+/', ' ', $name));
$ip = $_SERVER['REMOTE_ADDR'];
$date = date("Y-m-d H:i:s");

$rsvpLogFile = 'rsvp_log.txt';
$nameUsed = false;
$plusOneCountFromIP = 0;

// Check log for duplicate name or too many entries from IP
if (file_exists($rsvpLogFile)) {
    $lines = file($rsvpLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $lineLower = strtolower($line);
        if (strpos($lineLower, $normalized) !== false) {
            $nameUsed = true;
        }
        if (strpos($lineLower, 'plus one:') !== false && strpos($lineLower, $ip) !== false) {
            $plusOneCountFromIP++;
        }
    }
}

// 🚫 Blocked by name reuse
if ($nameUsed) {
    $entry = "⛔ Blocked Plus One: $normalized | IP: $ip | Date: $date | Reason: Name already used";
    file_put_contents($rsvpLogFile, $entry . PHP_EOL, FILE_APPEND);
    echo json_encode(['blocked' => true, 'reason' => 'Name already used']);
    exit;
}

// 🚫 Blocked by IP limit (now 3)
if ($plusOneCountFromIP >= 3) {
    $entry = "⛔ Blocked Plus One: $normalized | IP: $ip | Date: $date | Reason: IP limit reached (3)";
    file_put_contents($rsvpLogFile, $entry . PHP_EOL, FILE_APPEND);
    echo json_encode(['blocked' => true, 'reason' => 'IP limit reached']);
    exit;
}

// ✅ All good
echo json_encode(['blocked' => false]);
?>
