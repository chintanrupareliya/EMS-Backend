<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333333;
            margin-bottom: 20px;
        }
        p {
            color: #666666;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
        }
        .footer {
            margin-top: 20px;
            color: #999999;
            font-size: 14px;
            border-top: 1px solid #dddddd;
            padding-top: 20px;
        }
        .footer p {
            margin: 0;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Password Reset</h1>
        <p>You have requested to reset your password for the following account:</p>
        <p><strong>Email Address:</strong> <?php echo e($email); ?></p>
        <p>If you did not request this password reset, please ignore this email. Your account's security is important to us.</p>
        <p>To reset your password, please click the button below:</p>
        <a href="<?php echo e($resetLink); ?>" class="btn">Reset Password</a>
        <div class="footer">
            <p>If you have any questions or need further assistance, please feel free to contact us at <a href="mailto:support@example.com">support@example.com</a>.</p>
            <p>Thank you for using our service.</p>
            <p>Best regards,<br>Team Track</p>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\chintan\Desktop\employee-management-system\employee-management-system-Backend\resources\views/reset_password.blade.php ENDPATH**/ ?>