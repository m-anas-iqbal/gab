<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Around | Email Verification</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            /* background-color: #f4f4f4; */
            margin: 0;
            padding: 0;
        }
        p {
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 10px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div style="max-width: 700px;margin: 0 auto;padding: 20px;text-align:center;font-size:14px">
        <div style="width: 100px;margin:0 auto">
            <img src="https://stage523.yourdesigndemo.net/assets/media/logo-icon.png" alt="" style="width: 100%;height: 100%;">
        </div>
        <p><strong>We received a request to verify your account. To complete the process, please use the following code:</strong></p>
        <p class="highlight" style="width:fit-content;margin:20px auto;background-color: #000;color: #fff;padding: 5px 15px;font-size:20px; text-align: center;border-radius: 5px;">{{ $code }}</p>
        <p>Please note that this code is valid for <strong>one hour</strong> from the time this email was sent.</p>
        <hr>
        <p>&copy; copyright 2025 | <a href="{{ env('APP_FRONT_URL',"#") }}" style="color:inherit;text-decoration:none">Workaround</a></p>
    </div>
</body>
</html>
