<?php
$apiKey = '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';

$response = strtolower($_GET['response'] ?? '');
$name = htmlspecialchars(urldecode(trim($_GET['name'] ?? 'Unnamed Guest')));
$normalized = strtolower(preg_replace('/\s+/', ' ', $name));
$date = date("Y-m-d H:i:s");
$ip = $_SERVER['REMOTE_ADDR'];

$ipLogFile = 'ip_log.txt';
$rsvpLogFile = 'rsvp_log.txt';
$nameOnlyLog = 'guest_names.txt';

// Check logs for duplicates
$ipUsed = file_exists($ipLogFile) && in_array($ip, file($ipLogFile, FILE_IGNORE_NEW_LINES));
$nameUsed = false;

if (file_exists($rsvpLogFile)) {
    foreach (file($rsvpLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(strtolower($line), $normalized) !== false) {
            $nameUsed = true;
            break;
        }
    }
}

// 🚫 Block duplicate name or IP (but don't send email)
if ($ipUsed || $nameUsed) {
    $reason = ($ipUsed ? 'IP' : '') . ($ipUsed && $nameUsed ? ' and ' : '') . ($nameUsed ? 'Name' : '');
    $entry = "⛔ Repeat RSVP: $normalized | IP: $ip | Date: $date | Reason: $reason";
    file_put_contents($rsvpLogFile, $entry . PHP_EOL, FILE_APPEND);
    header("Location: already-rsvped.php");
    exit;
}

// 🎯 Handle "no" response — log only
if ($response === 'no') {
    $entry = "❌ RSVP Declined: $normalized | IP: $ip | Date: $date";
    file_put_contents($rsvpLogFile, $entry . PHP_EOL, FILE_APPEND);
    header("Location: regretpage.html");
    exit;
}

// ✅ Send RSVP email for "yes" response
$data = [
    'sender' => ['name' => 'RSVP Bot', 'email' => 'alvodelrio@gmail.com'],
    'to' => [['email' => 'wanjohialvins@gmail.com']],
    'subject' => '🎓 RSVP Response from ' . $name,
    'htmlContent' => "
        <h2>📧 RSVP Submission</h2>
        <p><strong>👤 Name:</strong> $name</p>
        <p><strong>✅ Response:</strong> YES</p>
        <p><strong>📅 Date:</strong> $date</p>
        <p><strong>🌐 IP:</strong> $ip</p>
    "
];

$ch = curl_init('https://api.brevo.com/v3/smtp/email');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'api-key: ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ✅ Log and redirect only after successful email
if ($httpcode >= 200 && $httpcode < 300) {
    file_put_contents($ipLogFile, $ip . PHP_EOL, FILE_APPEND | LOCK_EX);
    file_put_contents($rsvpLogFile, "✅ RSVP: $normalized | IP: $ip | Date: $date" . PHP_EOL, FILE_APPEND | LOCK_EX);
    file_put_contents($nameOnlyLog, $normalized . PHP_EOL, FILE_APPEND | LOCK_EX);
    header("Location: thankyou.html");
    exit;
} else {
    // ⏳ Retry silently by reloading page
    echo "<html><body><h2>⏳ Trying again...</h2>
    <p>Your RSVP couldn't be submitted yet. Retrying shortly...</p>
    <script>setTimeout(() => { window.location.href = window.location.href; }, 2000);</script>
    </body></html>";
    exit;
}
?>
