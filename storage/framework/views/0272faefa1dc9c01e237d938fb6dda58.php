
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation Mail</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Hello, <?php echo e($name); ?>!</h1>
        <p>You are invited to join our amazing platform as Employee of <?php echo e($company); ?>. login now to get started!</p>
        <p>Use the following credentials to log in:</p>
        <ul>
            <li>Email: <?php echo e($email); ?></li>
        </ul>
        <a href="<?php echo e($resetLink); ?>" class="btn">Set Password & Log In</a>
        <p>If you have any questions, feel free to contact us at support@example.com.</p>
        <p>Best regards,<br>Team Track</p>
    </div>
</body>
</html>



<?php /**PATH /Users/ztlab85/Desktop/employee-management-system/Backend/resources/views/employee_invitation_email.blade.php ENDPATH**/ ?>