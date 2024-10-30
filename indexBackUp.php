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
        input[type="text"], input[type="file"] {
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
    <!-- Your existing styles go here -->
    </head>
<body>
    <div class="container">
        <form id="attendeeForm">
            <input type="text" id="fullName" placeholder="First, Last Name" required>
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
        <div id="counterDisplay" style="display: none;">Counter Number: <span id="counterNumber"></span></div>
    </div>

    <script>
        document.getElementById('attendeeForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const fullName = document.getElementById('fullName').value;
            const profilePicInput = document.getElementById('profilePic');
            const canvas = document.getElementById('designCanvas');
            const ctx = canvas.getContext('2d');

            // Fetch the unique ID and QR code from PHP
            const response = await fetch('design_generator.php');
            const data = await response.json();
            
            if (data.uniqueID && data.qrCodePath) {
                // Display the counter number on the page
                document.getElementById('counterNumber').textContent = data.uniqueID;
                document.getElementById('counterDisplay').style.display = 'block';

                // Load the PNG template
                const template = new Image();
                template.src = 'your-design.png';
                template.onload = function () {
                    canvas.width = 1290;
                    canvas.height = 898;
                    ctx.drawImage(template, 0, 0);

                    // Load and draw the profile picture
                    if (profilePicInput.files && profilePicInput.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const profileImg = new Image();
                            profileImg.onload = function () {
                                ctx.drawImage(profileImg, 800, 100, 400, 400);  // Adjust as needed

                                // Add text for the name
                                ctx.font = "40px Arial";
                                ctx.fillText(fullName, 600, 600);

                                // Add the counter number to the design
                                ctx.font = "30px Arial";
                                ctx.fillStyle = "#000";
                                ctx.fillText("ID: " + data.uniqueID, 600, 650);  // Position as needed

                                // Display the canvas
                                canvas.style.display = 'block';
                                document.getElementById('downloadLink').href = canvas.toDataURL('image/png');
                                document.getElementById('downloadLink').style.display = 'block';

                                // Display the QR code
                                const qrCodeImage = document.getElementById('qrCodeImage');
                                qrCodeImage.src = data.qrCodePath;
                                qrCodeImage.style.display = 'block';
                                qrCodeImage.alt = 'QR Code for ID ' + data.uniqueID;
                            };
                            profileImg.src = e.target.result;
                        };
                        reader.readAsDataURL(profilePicInput.files[0]);
                    }
                };
            }
        });
    </script>
</body>
</html>