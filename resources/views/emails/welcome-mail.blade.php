<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Work Around!</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 700px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .logo {
            width: 100px;
            margin: 0 auto;
        }
        .logo img {
            width: 100%;
            height: auto;
        }
        h1 {
            font-size: 26px;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        p {
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 10px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #000;
            color: #fff;
            font-size: 18px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #95a5a6;
        }
        .footer a {
            color: inherit;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="https://stage523.yourdesigndemo.net/assets/media/logo-icon.png" alt="Work Around">
        </div>
        <h1>Welcome to Work Around, {{ $username }}!</h1>
        <p>We are thrilled to have you on board. Work Around is designed to help you connect, collaborate, and achieve more.</p>
        <p>Click the button below to explore your new account and get started.</p>
        <a href="{{ env('APP_FRONT_URL', '#') }}" class="btn">Get Started</a>
        <hr>
        <p class="footer">&copy; 2025 | <a href="{{ env('APP_FRONT_URL', '#') }}">Work Around</a></p>
    </div>
</body>
</html>
