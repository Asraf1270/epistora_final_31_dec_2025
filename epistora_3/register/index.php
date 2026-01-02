<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $re_pass = $_POST['re_password'];

    if ($pass !== $re_pass) {
        die("Passwords do not match!");
    }

    $usersFile = '../data/users.json';
    $usersData = json_decode(file_get_contents($usersFile), true);

    // Check if email already exists
    foreach ($usersData as $user) {
        if ($user['email'] === $email) {
            die("Email already registered!");
        }
    }

    // Generate Unique ID and Hash Password
    $user_id = "USR" . time() . rand(10, 99);
    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

    // 1. Data for users.json
    $usersData[$user_id] = [
        "username" => $name,
        "email" => $email,
        "password" => $hashed_password,
        "role" => "user", // Automatically define as user
        "verified" => false,
        "created" => time(),
        "status" => "active"
    ];

    // 2. Data for individual [user_id].json
    $personalData = [
        "theme" => (object)[],
        "username" => $name,
        "email" => $email,
        "password" => $hashed_password,
        "role" => "user",
        "follows" => [],
        "bookmarks" => [],
        "history" => [],
        "notifications" => [],
        "privacy" => (object)[]
    ];

    // Save to files
    file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT));
    file_put_contents("../data/user_data/$user_id.json", json_encode($personalData, JSON_PRETTY_PRINT));

    echo "Registration successful! <a href='../login/'>Login here</a>";
}
?>

<form method="POST">
    <input type="text" name="name" placeholder="Full Name" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <input type="password" name="re_password" placeholder="Re-type Password" required><br>
    <button type="submit">Register</button>
</form>