<?php
require_once 'vendor/autoload.php';

use Twilio\Rest\Client;

$sid = 'YOUR_TWILIO_SID';
$token = 'YOUR_TWILIO_AUTH_TOKEN';
$twilio_number = '+1234567890'; // Your Twilio phone number

$client = new Client($sid, $token);

$client->messages->create(
    '+919876543210', // Recipient phone number
    [
        'from' => $twilio_number,
        'body' => 'Hello! This is a test SMS sent using PHP.'
    ]
);
echo "SMS sent successfully!";
?>
