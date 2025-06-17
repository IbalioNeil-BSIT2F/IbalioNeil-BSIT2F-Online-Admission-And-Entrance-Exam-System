<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Application Status</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background: #f8f9fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .container {
      background: #fff;
      padding: 40px;
      max-width: 600px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      text-align: center;
    }

    .emoji {
      font-size: 64px;
      margin-bottom: 20px;
    }

    h1 {
      color: #dc3545;
      margin-bottom: 10px;
    }

    p {
      color: #555;
      font-size: 18px;
      line-height: 1.6;
    }

    .btn {
      margin-top: 30px;
      display: inline-block;
      padding: 10px 20px;
      background: #6c757d;
      color: #fff;
      text-decoration: none;
      border-radius: 6px;
      transition: background 0.3s;
    }

    .btn:hover {
      background: #5a6268;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="emoji">😔</div>
    <h1>We're Sorry</h1>
    <p>
      Thank you for your interest in joining our university. <br>
      After careful review of your application, we regret to inform you that you were not accepted this time.
    </p>
    <p>
      We encourage you to continue pursuing your educational goals, and we wish you all the best in your future endeavors.
    </p>
    <a href="userdashboard.php" class="btn">Return to Homepage</a>
  </div>
</body>
</html>
