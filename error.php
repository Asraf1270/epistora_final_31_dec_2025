<?php
// error.php → Final, Mobile-Perfect, Universal Error Page
$code = http_response_code() ?: 404;
if ($code < 400) $code = 404;

$errors = [
    400 => ["Bad Request", "Your browser sent a request we couldn't understand."],
    401 => ["Unauthorized", "Please log in to continue."],
    402 => ["Payment Required", "This feature requires payment."],
    403 => ["Forbidden", "You're not allowed to access this page."],
    404 => ["Oops! Page Not Found", "We couldn’t find the page you’re looking for. Don’t worry, you can go back home safely!"],
    405 => ["Method Not Allowed", "This action isn't allowed here."],
    408 => ["Request Timeout", "The server timed out waiting for your request."],
    410 => ["Gone", "This page has been permanently removed."],
    418 => ["I'm a Teapot", "Yes, really. This server is a teapot."],
    429 => ["Too Many Requests", "Whoa, slow down! You've sent too many requests."],
    500 => ["Server Error", "Something broke on our side. We're fixing it!"],
    501 => ["Not Implemented", "This feature isn't supported yet."],
    502 => ["Bad Gateway", "Invalid response from upstream server."],
    503 => ["Service Unavailable", "We're down for maintenance. Back soon!"],
    504 => ["Gateway Timeout", "The server took too long to respond."],
];

$title   = $errors[$code][0] ?? "Error";
$message = $errors[$code][1] ?? "An unexpected error occurred.";

http_response_code($code);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $code; ?> — <?php echo htmlspecialchars($title); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary: #64b3f4;
      --secondary: #3d8be1;
      --shadow: rgba(100, 179, 244, 0.3);
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Quicksand', sans-serif;
      min-height: 100vh;
      background: linear-gradient(135deg, #e3f6f5, #d1d9f6);
      color: #444;
      display: grid;
      place-items: center;
      overflow-x: hidden;
      padding: 20px;
    }

    /* Floating pastel blobs – super smooth */
    .float-shape {
      position: fixed;
      border-radius: 50%;
      opacity: 0.22;
      filter: blur(20px);
      pointer-events: none;
      animation: drift 20s infinite ease-in-out;
      will-change: transform;
    }

    .shape1 { width: 150px; height: 150px; background: #ff9a8b; top: 5%; left: 5%; }
    .shape2 { width: 120px; height: 120px; background: #8ecae6; bottom: 10%; right: 8%; animation-delay: 5s; }
    .shape3 { width: 100px; height: 100px; background: #a7c5eb; top: 60%; left: 50%; animation-delay: 10s; }
    .shape4 { width: 180px; height: 180px; background: #d4a5a5; top: 15%; right: 3%; animation-delay: 15s; }

    @keyframes drift {
      0%, 100% { transform: translate(0, 0) rotate(0deg); }
      33%      { transform: translate(50px, -70px) rotate(120deg); }
      66%      { transform: translate(-40px, 60px) rotate(240deg); }
      100%     { transform: translate(0, 0) rotate(360deg); }
    }

    .container {
      text-align: center;
      max-width: 90%;
      animation: fadeIn 1.4s ease-out;
    }

    .logo {
      width: 160px;
      height: 160px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 30px;
      box-shadow: 0 20px 45px rgba(0,0,0,0.15);
      border: 8px solid white;
      animation: gentleBounce 4s infinite ease-in-out;
    }

    @keyframes gentleBounce {
      0%, 100% { transform: translateY(0) scale(1); }
      50%      { transform: translateY(-28px) scale(1.04); }
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(40px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    h1 {
      font-size: clamp(3.5rem, 12vw, 6rem);
      font-weight: 700;
      color: #3d5a80;
      margin: 10px 0;
      line-height: 1;
    }

    h2 {
      font-size: clamp(1.4rem, 5vw, 2rem);
      color: #3d5a80;
      margin: 15px 0;
      font-weight: 500;
    }

    p {
      font-size: clamp(1rem, 4vw, 1.3rem);
      max-width: 600px;
      color: #4f5d75;
      line-height: 1.7;
      margin: 20px auto;
      padding: 0 15px;
    }

    .btn {
      display: inline-block;
      padding: 16px 40px;
      background: linear-gradient(90deg, var(--primary), var(--secondary));
      color: white;
      border-radius: 50px;
      font-weight: 700 1.1rem 'Quicksand', sans-serif;
      text-decoration: none;
      box-shadow: 0 10px 30px var(--shadow);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      margin-top: 10px;
    }

    .btn:hover {
      transform: translateY(-8px) scale(1.05);
      box-shadow: 0 20px 40px var(--shadow);
    }

    /* Perfect mobile adjustments */
    @media (max-width: 480px) {
      .logo { width: 130px; height: 130px; border-width: 6px; }
      .btn { padding: 14px 32px; font-size: 1rem; }
      .float-shape { filter: blur(15px); }
    }

    @media (max-width: 360px) {
      .logo { width: 110px; height: 110px; }
      h1 { font-size: 3.2rem; }
    }
  </style>
</head>
<body>

  <!-- Floating animated blobs -->
  <div class="float-shape shape1"></div>
  <div class="float-shape shape2"></div>
  <div class="float-shape shape3"></div>
  <div class="float-shape shape4"></div>

  <div class="container">
    <img src="https://cdn-icons-png.flaticon.com/512/616/616408.png" alt="Cute mascot" class="logo">

    <h1><?php echo $code; ?></h1>
    <h2><?php echo htmlspecialchars($title); ?></h2>
    <p><?php echo htmlspecialchars($message); ?></p>

    <a href="/" class="btn">Go Home</a>
  </div>

</body>
</html>