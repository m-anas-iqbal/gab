<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Around | Join Group invitation</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        p {
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 10px;
            line-height: 1.6;
        }

        h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div style="max-width: 700px;margin: 0 auto;padding: 20px;text-align:center;font-size:14px">
        <div style="width: 100px;margin:0 auto">
            <img src="https://stage523.yourdesigndemo.net/assets/media/logo-icon.png" alt="" style="width: 100%;height: 100%;">
        </div>
        <p><strong>You are invited to join the Work Around group!</strong> </p>
        <p>Click the Join button below to join and register in group: {{ $groupName }}</p>
        <a href="{{ $inviteLink }}" style="text-decoration:none;width:fit-content;margin:20px auto;background-color: #000;color: #fff;padding: 5px 15px;font-size:20px; text-align: center;border-radius: 5px;" >Join</a>
        <hr>
        <p>&copy; copyright 2025 | <a href="{{ env('APP_FRONT_URL',"#") }}"style="color:inherit;text-decoration:none">Workaround</a></p>
    </div>
</body>
</html>
