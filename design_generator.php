<?php

include_once('db.php');

$sql = "SELECT * FROM students";
//you can do it like this 
$result = $conn->query($sql);
//Total Number of records
$totNumber = $result->num_rows;

$inputField = $_POST['fullName'];
$selectField = $_POST['attNames'];
// echo "<script>console.log('".$inputField."')</script>";
// echo $inputField;
$displayName = "";
//echo "'".$selectField."'";
if ($selectField != "") {
    $sql2 = "SELECT * FROM students WHERE studentID = '$selectField'";
    $result2 = $conn->query($sql2);

    if ($result2) {

        $row = $result2->fetch_assoc();
        $displayName = $row['FullName'];
        $uniqueID = str_pad($row['studentID'], 3, '0', STR_PAD_LEFT);  // Pad to 3 digits (e.g., 001, 002)
        //echo " b4 update";
        $updateTBL = "UPDATE students SET designGenerated = 1 WHERE studentID = $selectField";
        $updateResult = $conn->query($updateTBL);
        //echo "update was succesfful";
        if(!$updateResult){
            echo json_encode(['status' => 'error', 'message' => 'update failed: '.htmlspecialchars($conn->error)]);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'selecting all users failed: '.htmlspecialchars($conn->error)]);
        exit;
    }
} else {
    $displayName = $inputField;
    $uniqueID = str_pad(($totNumber + 1), 3, '0', STR_PAD_LEFT);  // Pad to 3 digits (e.g., 001, 002)

    $nameParts = explode(' ', $inputField);
    $surname = array_pop($nameParts);
    $otherNames = implode(' ', $nameParts);

    $insertTBL = "INSERT INTO students (studentID, Name, Surname, Dept, FullName, designGenerated) VALUES ('$uniqueID', '$otherNames', '$surname', 'No department', '$inputField', TRUE)";
    $insertResult = $conn->query($insertTBL);

    if (!$insertResult) {
        echo "Error: " . $conn->error;
    }
}



// Generate QR Code using phpqrcode
require_once 'phpqrcode/qrlib.php'; // Make sure to include the library

$qrCodePath = 'qrcodes/qrcode_' . $uniqueID . '.png';
QRcode::png($uniqueID." - (".$displayName.")", $qrCodePath, QR_ECLEVEL_L, 4); // Generate QR code with the unique ID

// Here, we'll output JSON to use in JavaScript
echo json_encode([
    "uniqueID" => $uniqueID,
    "qrCodePath" => $qrCodePath,
    "displayName" => $displayName
]);
?>
