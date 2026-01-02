<?php
session_start();

// Basic Security: Only allow admins (You can refine this later)
// if ($_SESSION['profile']['role'] !== 'admin') { die("Access Denied"); }

$usersFile = '../data/users.json';
$usersData = json_decode(file_get_contents($usersFile), true);

// Handle Role Update
if (isset($_POST['update_role'])) {
    $target_id = $_POST['user_id'];
    $new_role = $_POST['role'];

    if (isset($usersData[$target_id])) {
        // 1. Update main users.json
        $usersData[$target_id]['role'] = $new_role;
        file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT));

        // 2. Update the individual [user_id].json file
        $personalFilePath = "../data/user_data/$target_id.json";
        if (file_exists($personalFilePath)) {
            $personalData = json_decode(file_get_contents($personalFilePath), true);
            $personalData['role'] = $new_role;
            file_put_contents($personalFilePath, json_encode($personalData, JSON_PRETTY_PRINT));
        }
        
        $msg = "User role updated successfully!";
        // Refresh data
        $usersData = json_decode(file_get_contents($usersFile), true);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Manage Roles</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        .msg { color: green; font-weight: bold; }
    </style>
</head>
<body>
    <h2>User Management</h2>
    <?php if(isset($msg)) echo "<p class='msg'>$msg</p>"; ?>

    <table>
        <tr>
            <th>User ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Current Role</th>
            <th>Action</th>
        </tr>
        <?php foreach ($usersData as $id => $user): ?>
        <tr>
            <td><?php echo $id; ?></td>
            <td><?php echo htmlspecialchars($user['username']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><strong><?php echo strtoupper($user['role']); ?></strong></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="user_id" value="<?php echo $id; ?>">
                    <select name="role">
                        <option value="user" <?php if($user['role']=='user') echo 'selected'; ?>>User</option>
                        <option value="writer" <?php if($user['role']=='writer') echo 'selected'; ?>>Writer</option>
                        <option value="v_writer" <?php if($user['role']=='v_writer') echo 'selected'; ?>>Verified Writer</option>
                        <option value="admin" <?php if($user['role']=='admin') echo 'selected'; ?>>Admin</option>
                    </select>
                    <button type="submit" name="update_role">Update</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>