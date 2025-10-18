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

        // Redirect based on role
       $role = trim(strtolower($res['role']));
if ($role === 'admin') {
    header('Location: admin_dashboard.php');
} else {
    header('Location: official_dashboard.php');
}

        exit;
    } else {
        $error = 'Invalid credentials';
    }
}

// --- REGISTER LOGIC ---
if (isset($_POST['register'])) {
    $fullname = $mysqli->real_escape_string($_POST['fullname']);
    $username = $mysqli->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $mysqli->real_escape_string($_POST['role']);

    $check = $mysqli->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
    $check->bind_param("s", $username);
    $check->execute();
    $exists = $check->get_result()->num_rows > 0;

    if ($exists) {
        $reg_error = "Username already exists!";
    } else {
        $stmt = $mysqli->prepare("INSERT INTO users (fullname, username, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fullname, $username, $password, $role);
        if ($stmt->execute()) {
            $reg_success = "Account registered successfully!";
        } else {
            $reg_error = "Registration failed. Try again.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>SK Payatas File Management | Login & Register</title>
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

    .btn-success {
        background: #198754;
        border: none;
        transition: 0.3s;
    }

    .btn-success:hover {
        background: #157347;
        transform: scale(1.03);
    }

    .toggle-link {
        color: #6610f2;
        cursor: pointer;
        text-decoration: underline;
        font-size: 0.9rem;
    }

    .form-section {
        display: none;
        animation: fadeIn 0.4s ease;
    }

    .form-section.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.98); }
        to { opacity: 1; transform: scale(1); }
    }

    .title {
        text-align: center;
        font-size: 1.6rem;
        margin-bottom: 10px;
        color: #444;
    }

</style>
</head>
<body>

<div class="card p-4">
    <h4 class="title mb-3 text-center">SK Payatas File Management</h4>

    <!-- Alerts -->
    <?php if(!empty($error)): ?>
        <div class="alert alert-danger text-center"><?=htmlspecialchars($error)?></div>
    <?php elseif(!empty($reg_error)): ?>
        <div class="alert alert-danger text-center"><?=htmlspecialchars($reg_error)?></div>
    <?php elseif(!empty($reg_success)): ?>
        <div class="alert alert-success text-center"><?=htmlspecialchars($reg_success)?></div>
    <?php endif; ?>

    <!-- LOGIN FORM -->
    <div id="loginForm" class="form-section active">
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
            <p class="text-center">Don't have an account? <span class="toggle-link" onclick="toggleForms()">Register</span></p>
        </form>
    </div>

    <!-- REGISTER FORM -->
    <div id="registerForm" class="form-section">
        <form method="post">
            <h5 class="text-center mb-3">Register</h5>
            <div class="mb-3"><input name="fullname" class="form-control" placeholder="Full Name" required></div>
            <div class="mb-3"><input name="username" class="form-control" placeholder="Username" required></div>
            <div class="mb-3"><input name="password" type="password" class="form-control" placeholder="Password" required></div>
            <div class="mb-3">
                <select name="role" class="form-select" required>
                    <option value="" disabled selected>Select Role</option>
                    <option value="Admin">Admin</option>
                    <option value="Official">Official</option>
                </select>
            </div>
            <div class="d-grid mb-2">
                <button name="register" class="btn btn-success">Register</button>
            </div>
            <p class="text-center">Already have an account? <span class="toggle-link" onclick="toggleForms()">Login</span></p>
        </form>
    </div>
</div>

<script>
function toggleForms() {
    document.getElementById('loginForm').classList.toggle('active');
    document.getElementById('registerForm').classList.toggle('active');
}
</script>
</body>
</html>
