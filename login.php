<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if ($email && $password && $role) {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ? AND role = ?");
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $role;

                if ($role === 'student') {
                    header("Location: student-dashboard.php");
                    exit;
                }
                
            } else {
                $error = "Invalid credentials.";
            }
        } else {
            $error = "User not found.";
        }
    } else {
        $error = "Please fill all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EduGestion Login</title>
</head>
<body>
  <form method="post" action="login.php">
    
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <label>Email:</label>
    <input type="email" name="email" required />
    <label>Password:</label>
    <input type="password" name="password" required />
    <label>Role:</label>
    <select name="role" required>
      <option value="student">Student</option>
      <option value="teacher">Teacher</option>
    </select>
    <button type="submit">Login</button>
  </form>
</body>
</html>
