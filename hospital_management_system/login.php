<?php
require_once 'includes/db_connection.php';
session_start();

if (isset($_SESSION['User_ID'])) {
    // Redirect already logged-in users to their respective dashboards
    switch ($_SESSION['Role']) {
        case 'Admin': header("Location: admin/dashboard.php"); exit();
        case 'Receptionist': header("Location: receptionist/dashboard.php"); exit();
        case 'Doctor': header("Location: doctor/dashboard.php"); exit();
        case 'Nurse': header("Location: nurse/dashboard.php"); exit();
        case 'Patient': header("Location: patient/dashboard.php"); exit();
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT User_ID, Password, Role FROM User_Login WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['Password'])) {
            $_SESSION['User_ID'] = $user['User_ID'];
            $_SESSION['Role'] = $user['Role'];
            
            // Map the role to the specific portal ID to make querying easier later
            if ($user['Role'] === 'Admin') {
                $stmt2 = $conn->prepare("SELECT Admin_ID FROM Admin WHERE User_ID = ?");
                $stmt2->bind_param("i", $user['User_ID']);
                $stmt2->execute();
                $res = $stmt2->get_result();
                if($row = $res->fetch_assoc()) $_SESSION['Admin_ID'] = $row['Admin_ID'];
                header("Location: admin/dashboard.php");
            } elseif ($user['Role'] === 'Receptionist') {
                $stmt2 = $conn->prepare("SELECT Receptionist_ID FROM Receptionist WHERE User_ID = ?");
                $stmt2->bind_param("i", $user['User_ID']);
                $stmt2->execute();
                $res = $stmt2->get_result();
                if($row = $res->fetch_assoc()) $_SESSION['Receptionist_ID'] = $row['Receptionist_ID'];
                header("Location: receptionist/dashboard.php");
            } elseif ($user['Role'] === 'Doctor') {
                $stmt2 = $conn->prepare("SELECT Doctor_ID FROM Doctor WHERE User_ID = ?");
                $stmt2->bind_param("i", $user['User_ID']);
                $stmt2->execute();
                $res = $stmt2->get_result();
                if($row = $res->fetch_assoc()) $_SESSION['Doctor_ID'] = $row['Doctor_ID'];
                header("Location: doctor/dashboard.php");
            } elseif ($user['Role'] === 'Nurse') {
                $stmt2 = $conn->prepare("SELECT Nurse_ID FROM Nurse WHERE User_ID = ?");
                $stmt2->bind_param("i", $user['User_ID']);
                $stmt2->execute();
                $res = $stmt2->get_result();
                if($row = $res->fetch_assoc()) $_SESSION['Nurse_ID'] = $row['Nurse_ID'];
                header("Location: nurse/dashboard.php");
            } elseif ($user['Role'] === 'Patient') {
                $stmt2 = $conn->prepare("SELECT Patient_ID FROM Patient WHERE User_ID = ?");
                $stmt2->bind_param("i", $user['User_ID']);
                $stmt2->execute();
                $res = $stmt2->get_result();
                if($row = $res->fetch_assoc()) $_SESSION['Patient_ID'] = $row['Patient_ID'];
                header("Location: patient/dashboard.php");
            }
            exit();
        } else {
            $error = "The email or password provided is incorrect. Please try again.";
        }
    } else {
        $error = "The email or password provided is incorrect. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Access - InnovAI Medical Center</title>
    <link rel="icon" type="image/png" href="/hospital_management_system/assets/IU_Logo_icon.png">
    <!-- Bootstrap 5.3 & FontAwesome 6 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-blue: #0f172a;
            --accent-cyan: #0ea5e9;
            --bg-light: #f8fafc;
        }
        body, html { height: 100%; margin: 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: var(--bg-light); }
        .split-container { display: flex; min-height: 100vh; flex-wrap: wrap; }
        .split-left {
            flex: 1; min-width: 300px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(2, 132, 199, 0.8)), url('https://images.unsplash.com/photo-1551076805-e18690c5e53b?auto=format&fit=crop&q=80&w=1600') no-repeat center center;
            background-size: cover;
            display: flex; flex-direction: column; justify-content: center; align-items: center; color: white; padding: 3rem;
            position: relative;
        }
        .split-right {
            flex: 1; min-width: 350px; display: flex; align-items: center; justify-content: center; padding: 2rem; background: var(--bg-light);
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
            padding: 3rem; width: 100%; max-width: 500px;
            position: relative;
        }
        .form-floating > label { color: #64748b; font-weight: 500; }
        .form-control { border-radius: 10px; border: 1px solid #cbd5e1; padding: 1rem 0.75rem; transition: all 0.3s ease; }
        .form-control:focus { border-color: var(--accent-cyan); box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1); }
        .btn-cyan { background-color: var(--accent-cyan); color: white; font-weight: 600; border-radius: 10px; padding: 0.8rem; transition: all 0.3s ease; }
        .btn-cyan:hover { background-color: #0284c7; color: white; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3); }
        .password-toggle { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8; transition: color 0.2s; }
        .password-toggle:hover { color: var(--primary-blue); }
        .back-link { position: absolute; top: 20px; right: 30px; text-decoration: none; color: #64748b; font-weight: 600; transition: color 0.2s; }
        .back-link:hover { color: var(--primary-blue); }
        
        @media (max-width: 768px) {
            .split-left { display: none; } /* Hide image on mobile for clean form focus */
            .glass-panel { padding: 2rem; border-radius: 15px; }
        }
    </style>
</head>
<body>

<div class="split-container">
    <!-- Left Visual Panel -->
    <div class="split-left">
        <div class="text-center" style="max-width: 500px; z-index: 2;">
            <i class="fas fa-hospital-symbol fa-4x text-info mb-4"></i>
            <h1 class="display-5 fw-bold mb-3">Welcome to the Future of Care</h1>
            <p class="lead fw-light opacity-75">Access your secure digital health passport, view medical summaries, and connect with your clinical team instantly.</p>
        </div>
        <!-- Decorative blob -->
        <div class="position-absolute bottom-0 start-0 w-100 h-50" style="background: radial-gradient(circle at bottom left, rgba(14,165,233,0.3) 0%, transparent 60%); z-index: 1;"></div>
    </div>

    <!-- Right Form Panel -->
    <div class="split-right position-relative">
        <a href="index.php" class="back-link"><i class="fas fa-arrow-left me-1"></i> Home</a>
        
        <div class="glass-panel">
            <div class="text-center mb-4">
                <h3 class="fw-bold" style="color: var(--primary-blue);">Access Your Digital Health Passport</h3>
                <p class="text-muted">Enter your secure credentials to continue.</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-danger border-0 border-start border-4 border-danger shadow-sm d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-circle me-3 fs-5 text-danger"></i>
                    <div><?php echo $error; ?></div>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'registered'): ?>
                <div class="alert alert-success border-0 border-start border-4 border-success shadow-sm d-flex align-items-center" role="alert">
                    <i class="fas fa-check-circle me-3 fs-5 text-success"></i>
                    <div>Registration successful! You may now access your portal.</div>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-floating mb-4">
                    <input type="email" name="email" class="form-control" id="floatingEmail" placeholder="name@example.com" required autofocus>
                    <label for="floatingEmail"><i class="fas fa-envelope me-2 text-muted"></i>Email Address</label>
                </div>
                
                <div class="form-floating mb-4 position-relative">
                    <input type="password" name="password" class="form-control pe-5" id="floatingPassword" placeholder="Password" required>
                    <label for="floatingPassword"><i class="fas fa-lock me-2 text-muted"></i>Secure Password</label>
                    <i class="fas fa-eye password-toggle" id="togglePasswordIcon" onclick="togglePassword()"></i>
                </div>

                <button type="submit" class="btn btn-cyan w-100 mb-4">Sign In securely <i class="fas fa-arrow-right ms-2"></i></button>
            </form>

            <div class="text-center mt-3">
                <p class="text-muted mb-0">Don't have a health passport yet?</p>
                <a href="register.php" class="text-decoration-none fw-bold" style="color: var(--accent-cyan);">Begin Your InnovAI Patient Journey</a>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById('floatingPassword');
        const icon = document.getElementById('togglePasswordIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>