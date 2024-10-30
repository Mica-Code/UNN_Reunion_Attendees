<?php
// Increment the counter from a file
$counterFile = 'counter.txt';

// Check if file exists, if not, create it with an initial count
if (!file_exists($counterFile)) {
    file_put_contents($counterFile, '1');
}

$currentCount = (int) file_get_contents($counterFile);
$uniqueID = str_pad($currentCount, 3, '0', STR_PAD_LEFT);  // Pad to 3 digits (e.g., 001, 002)
file_put_contents($counterFile, $currentCount + 1);  // Increment count

// Generate QR Code using phpqrcode
require_once 'phpqrcode/qrlib.php'; // Make sure to include the library

$qrCodePath = 'qrcodes/qrcode_' . $uniqueID . '.png';
QRcode::png($uniqueID, $qrCodePath, QR_ECLEVEL_L, 4); // Generate QR code with the unique ID

// Here, we'll output JSON to use in JavaScript
echo json_encode([
    "uniqueID" => $uniqueID,
    "qrCodePath" => $qrCodePath,
]);
?>
