<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Stampit - Reset Your Password</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9fafb;
            color: #2d3748;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 500px;
            margin: 60px auto;
            background-color: #ffffff;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        h1 {
            font-size: 28px;
            color: #1a202c;
            margin-bottom: 10px;
        }

        h2 {
            font-size: 18px;
            color: #4a5568;
            margin-bottom: 25px;
        }

        .code-box {
            display: inline-block;
            background-color: #edf2f7;
            color: #1a202c;
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 6px;
            padding: 14px 28px;
            border-radius: 10px;
            margin: 20px 0;
        }

        p {
            font-size: 16px;
            color: #4a5568;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .footer {
            font-size: 13px;
            color: #a0aec0;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Password Reset Request 🔒</h1>
        <h2>Stampit - Secure Document Authentication</h2>

        <p>We received a request to reset your password. Use the code below to proceed:</p>

        <div class="code-box">{{ $authCode }}</div>

        <p>Enter this code on the password reset screen to create a new password. This code will expire in 5 minutes.</p>

        <p>If you didn’t request a password reset, please ignore this email or contact support.</p>

        <div class="footer">
            &copy; {{ date('Y') }} Stampit. All rights reserved.
        </div>
    </div>
</body>

</html>
<!-- This email template is designed for sending a password reset code to users. It includes a header, a code box, and a footer. The design is clean and modern, with a focus on readability and user experience. The use of colors and spacing enhances the overall aesthetic of the email. -->
<!-- The code is structured to be responsive and looks good on both desktop and mobile devices. The font choices and sizes are consistent throughout the email, ensuring a professional appearance. -->