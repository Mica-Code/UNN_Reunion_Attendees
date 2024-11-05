<?php

include_once('db.php');

$sql = "SELECT * FROM students Order by FullName";
//you can do it like this 
$result = $conn->query($sql);

$getAllData = [];
while ($row = $result->fetch_assoc()) {
    $getAllData[$row['studentID']] = $row['FullName'];
}


//echo "<script>console.log('".$result->num_rows."')</script>";

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Design Generator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f0f0;
        }
        .container {
            text-align: center;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        form {
            margin-bottom: 20px;
        }
        input[type="text"], input[type="file"], select {
            display: block;
            margin: 10px auto;
            padding: 10px;
            width: 80%;
            max-width: 300px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            margin: 10px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        canvas {
            display: none;
            margin: 20px auto;
            border: 1px solid #ccc;
        }
        .buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
    </style>
    <!-- jQuery 3.x -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Your existing styles go here -->
    </head>
<body>
    <div class="container">
        <form id="attendeeForm">
            <input type="text" id="fullName" placeholder="First, Last Name" style="display:none">
            <p style="color:red; font-size:10px;" id="note"><strong>Note:</strong> If your name is not in the list click the Add New Button</p>
            <select style="/*width: 88%;*/" class="attNames" id="attNames" required>
            <option value=""></option>
                <?php
                    
                    foreach ($getAllData as $key => $value): ?>
                        <option value="<?= $key; ?>">
                            <?= $value; ?>
                        </option>
                    <?php endforeach; 

                ?>
            </select><button type="button" id="addNew">Add New</button>
            <input type="file" id="profilePic" accept="image/*" required>
            <button type="submit">Generate Design</button>
        </form>
        <canvas id="designCanvas"></canvas>
        <div class="buttons">
            <a id="downloadLink" download="design.png" style="display: none;">
                <button>Download your design</button>
            </a>
            <button id="shareButton" style="display: none;">Share design</button>
        </div>
        <img id="qrCodeImage" style="display: none;" alt="QR Code">
        <!-- <div id="counterDisplay" style="display: none;">Counter Number: <span id="counterNumber"></span></div> -->
    </div>

    <script>
        document.getElementById('attendeeForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const fullName = document.getElementById('fullName').value;
    const attNames = document.getElementById('attNames').value;
    const profilePicInput = document.getElementById('profilePic');
    const canvas = document.getElementById('designCanvas');
    const ctx = canvas.getContext('2d');

    // Collect form data to send to PHP
    const formData = new FormData();
    formData.append('fullName', fullName);
    formData.append('attNames', attNames);
    formData.append('profilePic', profilePicInput.files[0]);

    fetch('design_generator.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const uniqueID = data.uniqueID;
        const qrCodePath = data.qrCodePath;
        const displayName = data.displayName;

        // Load the template image
        const template = new Image();
        template.src = 'your-design.png'; // Replace with your template path

        template.onload = function () {
            // Set canvas size based on template dimensions
            const templateWidth = 1290;
            const templateHeight = 898;
            canvas.width = templateWidth;
            canvas.height = templateHeight;

            // Draw the template onto the canvas
            ctx.drawImage(template, 0, 0, templateWidth, templateHeight);

            // Load and draw profile picture
            if (profilePicInput.files && profilePicInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const profileImg = new Image();
                    profileImg.onload = function () {
                        const picX = templateWidth * 0.6; // Adjust as needed
                        const picY = templateHeight * 0.1;
                        const picWidth = templateWidth * 0.3;
                        const picHeight = templateHeight * 0.5;
                        ctx.drawImage(profileImg, picX, picY, picWidth, picHeight);

                        // Draw name under profile picture
                        // ctx.font = `${templateWidth * 0.05}px Arial`;
                        ctx.font = `48px Arial`;
                        ctx.fillStyle = "#000";
                        ctx.fillText(displayName, templateWidth * 0.55, templateHeight * 0.68);

                        // Draw the unique ID
                        ctx.fillText(`ID: ${uniqueID}`, templateWidth * 0.55, templateHeight * 0.75);

                        // Load and draw QR code
                        const qrCodeImg = new Image();
                        qrCodeImg.src = qrCodePath;
                        qrCodeImg.onload = function () {
                            const qrX = templateWidth * 0.7; // Adjust as needed
                            const qrY = templateHeight * 0.7;
                            const qrSize = templateWidth * 0.1;
                            ctx.drawImage(qrCodeImg, qrX, qrY, qrSize, qrSize);

                            // Generate download link and display canvas
                            canvas.style.display = 'block';
                            const downloadLink = document.getElementById('downloadLink');
                            const shareBtn = document.getElementById('shareButton');
                            downloadLink.href = canvas.toDataURL('image/png', 1.0);
                            downloadLink.style.display = 'block';
                            shareBtn.style.display = 'block';
                        };
                    };
                    profileImg.src = e.target.result;
                };
                reader.readAsDataURL(profilePicInput.files[0]);
            }
        };
    })
    .catch(error => console.log( error));
});

document.getElementById('shareButton').addEventListener('click', function () {
    const canvas = document.getElementById('designCanvas');
    canvas.toBlob(function (blob) {
        const file = new File([blob], 'design.png', { type: 'image/png' });

        // Check if Web Share API is supported
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            navigator.share({
                files: [file],
            })
            .then(() => {
                console.log('Design shared successfully!');
            })
            .catch((error) => {
                console.error('Error sharing the design:', error);
            });
        } else {
            alert('Sharing not supported on this device.');
        }
    }, 'image/png', 1.0); // Ensures maximum quality for the exported image
});

//Searchable Select

$(document).ready(function() {
    $('.attNames').select2();
});

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('addNew').addEventListener('click', () => {
        // Hide the button, <p>, and select elements
        document.getElementById('addNew').style.display = 'none';
        document.getElementById('note').style.display = 'none';

        // Hide the Select2 dropdown
        document.getElementById('attNames').removeAttribute('required');
        $('#attNames').select2('destroy'); // Destroy Select2 instance
        document.getElementById('attNames').style.display = 'none';

        // Show the input field
        const inputField = document.getElementById('fullName');
        inputField.style.display = 'block';
        inputField.setAttribute('required', '');
    });
});


// document.getElementById('addNew').addEventListener('click', ()=>{

//     // let isTextDisplayed = document.getElementById('inputId').style.display;

//     // if(isTextDisplayed = 'none'){
//         document.getElementById('addNew').style.display = 'none';
//         document.getElementById('note').style.display = 'none';
//         document.getElementById('attName').style.display = 'none';

//         // Show the input field
//         document.getElementById('fullName').style.display = 'block';
//     // }
    

// });

    </script>
</body>
</html>