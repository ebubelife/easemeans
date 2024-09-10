<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f2f4f6;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            text-align: center;
            background-color: #4CAF50;
            padding: 15px;
            border-radius: 8px 8px 0 0;
            color: #ffffff;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .email-body {
            padding: 20px;
            text-align: center;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            letter-spacing: 5px;
            margin: 20px 0;
        }
        .email-footer {
            margin-top: 30px;
            font-size: 12px;
            color: #666;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #dddddd;
        }
        .email-button {
            background-color: #4CAF50;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            display: inline-block;
            margin: 20px 0;
        }
        .email-button:hover {
            background-color: #45a049;
        }
        .email-footer p {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Email Verification</h1>
        </div>
        <div class="email-body">
            <p>Hello {{ $name }},</p>
            <p>Please use the following OTP to complete your verification:</p>
            
            <div class="otp-code">
                {{ $otp }}
            </div>
            
            <p>If you did not request this code, please ignore this email.</p>
            
            <a href="{{ $verificationLink }}" class="email-button">Verify Now</a>
        </div>

        <div class="email-footer">
            <p>Thank you for choosing our service!</p>
            <p>&copy; {{ date('Y') }} Your Company Name. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
