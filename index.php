<?php
require 'includes/config.php';

// --- LOGIN LOGIC ---
if (isset($_POST['login'])) {
    $user = $mysqli->real_escape_string($_POST['username']);
    $pass = $_POST['password'];

    $stmt = $mysqli->prepare('SELECT id, username, password, role, fullname FROM users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $user);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res && password_verify($pass, $res['password'])) {
        $_SESSION['user_id'] = $res['id'];
        $_SESSION['username'] = $res['username'];
        $_SESSION['role'] = $res['role'];
        $_SESSION['fullname'] = $res['fullname'];

        $role = strtolower(trim($res['role']));
        if ($role === 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: official_dashboard.php');
        }
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>SK Payatas File Management | Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background: linear-gradient(135deg, #007bff, #6610f2);
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        font-family: 'Poppins', sans-serif;
        overflow: hidden;
    }

    .card {
        width: 380px;
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        animation: fadeInUp 0.8s ease;
        background: #fff;
    }

    @keyframes fadeInUp {
        0% { opacity: 0; transform: translateY(30px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    h4 {
        font-weight: 600;
        color: #333;
    }

    .btn-primary {
        background: #007bff;
        border: none;
        transition: 0.3s;
    }

    .btn-primary:hover {
        background: #0056b3;
        transform: scale(1.03);
    }

    .title {
        text-align: center;
        font-size: 1.6rem;
        margin-bottom: 10px;
        color: #444;
    }

    .footer-note {
        text-align: center;
        font-size: 0.9rem;
        margin-top: 10px;
        color: #666;
    }
</style>
</head>
<body>

<div class="card p-4">
    <h4 class="title mb-3 text-center">SK Payatas File Management</h4>

    <!-- Alerts -->
    <?php if(!empty($error)): ?>
        <div class="alert alert-danger text-center"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>

    <!-- LOGIN FORM -->
    <div id="loginForm">
        <form method="post">
            <h5 class="text-center mb-3">Login</h5>
            <div class="mb-3">
                <input name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="mb-3">
                <input name="password" type="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="d-grid mb-2">
                <button name="login" class="btn btn-primary">Login</button>
            </div>
        </form>
    </div>

    <p class="footer-note">
        Only the admin can create accounts for officials.
    </p>
</div>

</body>
</html>
