<?php
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = sanitize_input($_POST['fname']);
    $lname = sanitize_input($_POST['lname']);
    $email = sanitize_input($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    // DO NOT CALCULATE OR INSERT AGE - Managed securely via Database BEFORE INSERT Trigger.
    $phone = sanitize_input($_POST['phone']);
    $city = sanitize_input($_POST['city']);
    $street = sanitize_input($_POST['street']);
    $building = sanitize_input($_POST['building']);
    $emergency = sanitize_input($_POST['emergency_contact']);
    $blood_group = sanitize_input($_POST['blood_group']);

    // Check if email already exists
    $check = $conn->prepare("SELECT User_ID FROM User_Login WHERE Email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $error = "This email is already associated with an active digital health passport.";
    } else {
        $conn->begin_transaction();
        try {
            // 1. Insert User_Login
            $stmt = $conn->prepare("INSERT INTO User_Login (Email, Password, Role) VALUES (?, ?, 'Patient')");
            $stmt->bind_param("ss", $email, $password);
            $stmt->execute();
            $user_id = $conn->insert_id;

            // 2. Insert Patient (Excluding Age column to strictly adhere to the trigger protocol)
            $stmt2 = $conn->prepare("INSERT INTO Patient (Fname, Lname, Gender, Date_of_Birth, City, Street, Building, Emergency_Contact, Blood_Group, User_ID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("sssssssssi", $fname, $lname, $gender, $dob, $city, $street, $building, $emergency, $blood_group, $user_id);
            $stmt2->execute();
            $patient_id = $conn->insert_id;

            // 3. Insert Phone (Multi-valued attribute handling per Schema)
            if (!empty($phone)) {
                $stmt3 = $conn->prepare("INSERT INTO Patient_phones (Patient_ID, PhoneNumber) VALUES (?, ?)");
                $stmt3->bind_param("is", $patient_id, $phone);
                $stmt3->execute();
            }

            $conn->commit();
            header("Location: login.php?msg=registered");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "We encountered a secure transaction error during registration. Please try again or contact support.";
            // Error logging could be placed here. Hidden from user interface to avoid technical jargon.
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Begin Patient Journey - InnovAI Medical Center</title>
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
        body, html { min-height: 100%; margin: 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: var(--bg-light); }
        .split-container { display: flex; min-height: 100vh; flex-wrap: wrap; }
        .split-left {
            flex: 1; min-width: 300px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(2, 132, 199, 0.85)), url('https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&q=80&w=1600') no-repeat center center;
            background-size: cover; background-attachment: fixed;
            display: flex; flex-direction: column; justify-content: center; align-items: center; color: white; padding: 3rem;
            position: relative;
        }
        .split-right {
            flex: 1.5; min-width: 350px; display: flex; align-items: center; justify-content: center; padding: 3rem 2rem; background: var(--bg-light);
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
            padding: 3rem; width: 100%; max-width: 750px;
            position: relative;
        }
        .form-floating > label { color: #64748b; font-weight: 500; }
        .form-control, .form-select { border-radius: 10px; border: 1px solid #cbd5e1; padding-top: 1rem; padding-bottom: 1rem; transition: all 0.3s ease; }
        .form-control:focus, .form-select:focus { border-color: var(--accent-cyan); box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1); }
        
        .section-title { font-size: 0.9rem; font-weight: 700; color: var(--primary-blue); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1.5rem; margin-top: 2rem; display: flex; align-items: center; }
        .section-title::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; margin-left: 15px; }
        .section-title.first { margin-top: 0; }
        
        .btn-cyan { background-color: var(--accent-cyan); color: white; font-weight: 600; border-radius: 10px; padding: 1rem; transition: all 0.3s ease; }
        .btn-cyan:hover { background-color: #0284c7; color: white; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3); }
        
        .password-toggle { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8; transition: color 0.2s; z-index: 10; }
        .password-toggle:hover { color: var(--primary-blue); }
        .back-link { position: absolute; top: 20px; right: 30px; text-decoration: none; color: #64748b; font-weight: 600; transition: color 0.2s; }
        .back-link:hover { color: var(--primary-blue); }

        /* Progress Bar */
        .progress { height: 6px; border-radius: 3px; background-color: #e2e8f0; }
        .pwd-text { position: absolute; right: 0; top: -20px; font-size: 0.8rem; font-weight: 600; }
        
        @media (max-width: 992px) {
            .split-left { display: none; }
            .glass-panel { padding: 2rem; border-radius: 15px; }
        }
    </style>
</head>
<body>

<div class="split-container">
    <!-- Left Visual Panel -->
    <div class="split-left">
        <div class="text-center" style="max-width: 500px; z-index: 2;">
            <i class="fas fa-heartbeat fa-4x text-info mb-4"></i>
            <h1 class="display-5 fw-bold mb-3">Begin Your InnovAI Patient Journey</h1>
            <p class="lead fw-light opacity-75">Join thousands of patients experiencing seamless AI triage, instant QR check-ins, and secure mobile health records.</p>
        </div>
        <div class="position-absolute bottom-0 start-0 w-100 h-50" style="background: radial-gradient(circle at bottom right, rgba(14,165,233,0.3) 0%, transparent 60%); z-index: 1;"></div>
    </div>

    <!-- Right Form Panel -->
    <div class="split-right position-relative">
        <a href="index.php" class="back-link"><i class="fas fa-arrow-left me-1"></i> Home</a>
        
        <div class="glass-panel">
            <div class="text-center mb-4">
                <h3 class="fw-bold" style="color: var(--primary-blue);">Secure Registration</h3>
                <p class="text-muted">Create your universal health profile.</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-danger border-0 border-start border-4 border-danger shadow-sm d-flex align-items-center mb-4" role="alert">
                    <i class="fas fa-exclamation-triangle me-3 fs-5 text-danger"></i>
                    <div><?php echo $error; ?></div>
                </div>
            <?php endif; ?>

            <form method="POST">
                <!-- Account Credentials -->
                <div class="section-title first"><i class="fas fa-shield-alt me-2 text-cyan"></i> Account Credentials</div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="form-floating">
                            <input type="email" name="email" class="form-control" id="regEmail" placeholder="name@example.com" required>
                            <label for="regEmail">Email Address</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-floating position-relative">
                            <input type="password" name="password" class="form-control pe-5" id="regPassword" placeholder="Password" oninput="checkStrength(this.value)" required>
                            <label for="regPassword">Secure Password</label>
                            <i class="fas fa-eye password-toggle" id="toggleRegPassword" onclick="toggleRegPasswordVis()"></i>
                        </div>
                        <div class="position-relative mt-2">
                            <div class="progress">
                                <div id="pwd-meter" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span id="pwd-text" class="pwd-text"></span>
                        </div>
                    </div>
                </div>

                <!-- Personal Demographics -->
                <div class="section-title"><i class="fas fa-user me-2 text-cyan"></i> Personal Demographics</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" name="fname" class="form-control" id="regFname" placeholder="First Name" required>
                            <label for="regFname">First Name</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" name="lname" class="form-control" id="regLname" placeholder="Last Name" required>
                            <label for="regLname">Last Name</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating">
                            <select name="gender" class="form-select" id="regGender" required>
                                <option value="" disabled selected>Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                            <label for="regGender">Biological Sex</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating">
                            <input type="date" name="dob" class="form-control" id="regDob" required>
                            <label for="regDob">Date of Birth</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating">
                            <select name="blood_group" class="form-select" id="regBlood">
                                <option value="" selected>Unknown</option>
                                <option value="A+">A+</option><option value="A-">A-</option>
                                <option value="B+">B+</option><option value="B-">B-</option>
                                <option value="O+">O+</option><option value="O-">O-</option>
                                <option value="AB+">AB+</option><option value="AB-">AB-</option>
                            </select>
                            <label for="regBlood">Blood Group</label>
                        </div>
                    </div>
                </div>

                <!-- Contact & Location -->
                <div class="section-title"><i class="fas fa-map-marker-alt me-2 text-cyan"></i> Contact & Location</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" name="phone" class="form-control" id="regPhone" placeholder="Mobile Number" required>
                            <label for="regPhone">Mobile Number</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" name="emergency_contact" class="form-control" id="regEmergency" placeholder="Emergency Contact">
                            <label for="regEmergency">Emergency Contact</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating">
                            <input type="text" name="city" class="form-control" id="regCity" placeholder="City">
                            <label for="regCity">City</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating">
                            <input type="text" name="street" class="form-control" id="regStreet" placeholder="Street">
                            <label for="regStreet">Street</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating">
                            <input type="text" name="building" class="form-control" id="regBuilding" placeholder="Building">
                            <label for="regBuilding">Bldg / Apt</label>
                        </div>
                    </div>
                </div>

                <div class="mt-5">
                    <button type="submit" class="btn btn-cyan w-100 py-3 text-uppercase letter-spacing-1">Securely Create Health Profile <i class="fas fa-shield-check ms-2"></i></button>
                </div>
            </form>

            <div class="text-center mt-4 pt-3 border-top">
                <p class="text-muted mb-0">Already possess a digital health passport?</p>
                <a href="login.php" class="text-decoration-none fw-bold" style="color: var(--primary-blue);">Access Your Portal</a>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleRegPasswordVis() {
        const input = document.getElementById('regPassword');
        const icon = document.getElementById('toggleRegPassword');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    function checkStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength += 1;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 1;
        if (password.match(/\d/)) strength += 1;
        if (password.match(/[^a-zA-Z\d]/)) strength += 1;

        const meter = document.getElementById('pwd-meter');
        const text = document.getElementById('pwd-text');
        
        meter.style.width = (strength * 25) + '%';
        
        if (password.length === 0) {
            meter.style.width = '0%';
            text.innerText = '';
        } else if (strength <= 1) {
            meter.className = 'progress-bar bg-danger';
            text.innerText = 'Weak';
            text.className = 'pwd-text text-danger';
        } else if (strength === 2) {
            meter.className = 'progress-bar bg-warning';
            text.innerText = 'Fair';
            text.className = 'pwd-text text-warning';
        } else if (strength === 3) {
            meter.className = 'progress-bar bg-info';
            text.innerText = 'Good';
            text.className = 'pwd-text text-info';
        } else {
            meter.className = 'progress-bar bg-success';
            text.innerText = 'Strong';
            text.className = 'pwd-text text-success';
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>