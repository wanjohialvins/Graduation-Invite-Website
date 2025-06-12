<?php
$name = trim($_POST['name'] ?? '');
if ($name === '') {
    echo json_encode(['status' => 'error', 'message' => 'No name provided']);
    exit;
}

$normalized = strtolower(preg_replace('/\s+/', ' ', $name));
$ip = $_SERVER['REMOTE_ADDR'];
$date = date("Y-m-d H:i:s");

$logFile = 'rsvp_log.txt';
$line = "Repeat RSVP: $normalized | IP: $ip | Date: $date";
file_put_contents($logFile, $line . PHP_EOL, FILE_APPEND);

// Send email
$apiKey = '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';
$emailData = [
  'sender' => ['name' => 'RSVP Bot', 'email' => 'alvodelrio@gmail.com'],
  'to' => [['email' => 'wanjohialvins@gmail.com']],
  'subject' => 'Repeat RSVP Alert',
  'htmlContent' => "<p><strong>$name</strong> tried to RSVP again.</p><p>IP: $ip<br>Date: $date</p>"
];

$ch = curl_init("https://api.brevo.com/v3/smtp/email");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Content-Type: application/json",
  "api-key: $apiKey"
]);
curl_exec($ch);
curl_close($ch);

echo json_encode(['status' => 'ok']);
