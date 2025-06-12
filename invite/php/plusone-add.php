<?php
$apiKey = '//add own key//';

$name = trim($_POST['name'] ?? '');
if ($name === '') {
    echo json_encode(['status' => 'error', 'message' => 'No name provided']);
    exit;
}

$normalized = strtolower(preg_replace('/\s+/', ' ', $name));
$ip = $_SERVER['REMOTE_ADDR'];
$date = date("Y-m-d H:i:s");

$rsvpLogFile = 'rsvp_log.txt';
$nameOnlyLog = 'guest_names.txt';
$debugLogFile = 'plusone_debug_log.txt';

// ✅ Log the successful plus one
$logEntry = "Plus One: $normalized | IP: $ip | Date: $date";
file_put_contents($rsvpLogFile, $logEntry . PHP_EOL, FILE_APPEND);
file_put_contents($nameOnlyLog, $normalized . PHP_EOL, FILE_APPEND | LOCK_EX);

// ✅ Send email confirmation
$emailData = [
    'sender' => ['name' => 'RSVP Bot', 'email' => 'alvodelrio@gmail.com'],
    'to' => [['email' => 'wanjohialvins@gmail.com']],
    'subject' => '✅ New Plus One Added',
    'htmlContent' => "
        <h2>🎉 Plus One RSVP Received</h2>
        <p><strong>Name:</strong> $name</p>
        <p><strong>IP:</strong> $ip</p>
        <p><strong>Date:</strong> $date</p>"
];

$ch = curl_init("https://api.brevo.com/v3/smtp/email");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "api-key: $apiKey"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 🪵 Log the result of the email attempt
$debugEntry = "$date | Plus One: $name | IP: $ip | Email status: HTTP $httpCode";
file_put_contents($debugLogFile, $debugEntry . PHP_EOL, FILE_APPEND);

if ($httpCode >= 200 && $httpCode < 300) {
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Email failed']);
}
?>
