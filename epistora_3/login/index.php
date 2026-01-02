<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $usersFile = '../data/users.json';
    $usersData = json_decode(file_get_contents($usersFile), true);

    $foundUser = null;
    $foundId = null;

    foreach ($usersData as $id => $data) {
        if ($data['email'] === $email && password_verify($pass, $data['password'])) {
            $foundUser = $data;
            $foundId = $id;
            break;
        }
    }

    if ($foundUser) {
        // Load the specific user_id.json file
        $personalFile = "../data/user_data/$foundId.json";
        $personalData = json_decode(file_get_contents($personalFile), true);

        // Store in Session
        $_SESSION['user_id'] = $foundId;
        $_SESSION['username'] = $foundUser['username'];
        $_SESSION['profile'] = $personalData;

        echo "Login successful! Welcome " . $_SESSION['username'];
        // header("Location: ../dashboard.php"); // Redirect to dashboard
    } else {
        echo "Invalid email or password.";
    }
}
?>

<form method="POST">
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Login</button>
</form>