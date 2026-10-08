<?php
// register.php - Secure Student Registration
require_once __DIR__ . "/connection.php";

$pageTitle = "Student Registration";
$errorMsg = "";
$successMsg = "";

// Retain form values in case of validation error
$student_name = "";
$username = "";
$email = "";
$mobile_number = "";
$gender = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_name = trim($_POST["student_name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $mobile_number = trim($_POST["mobile_number"] ?? "");
    $gender = trim($_POST["gender"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    // Backend Validation 1: Required Fields
    if ($student_name === "" || $username === "" || $email === "" || $mobile_number === "" || $gender === "" || $password === "" || $confirm_password === "") {
        $errorMsg = "All fields are required. Please fill in all the details.";
    }
    // Backend Validation 2: Student Name length
    elseif (strlen($student_name) < 3) {
        $errorMsg = "Student Name must contain at least 3 characters.";
    }
    // Backend Validation 3: Username format (3-20 alphanumeric and underscore)
    elseif (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username)) {
        $errorMsg = "Username must be 3-20 characters long and contain only letters, numbers, or underscores.";
    }
    // Backend Validation 4: Email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Please enter a valid email address.";
    }
    // Backend Validation 5: Mobile Number format (exact 10 digits)
    elseif (!preg_match("/^[0-9]{10}$/", $mobile_number)) {
        $errorMsg = "Mobile number must contain exactly 10 numeric digits.";
    }
    // Backend Validation 6: Gender choice
    elseif (!in_array($gender, ["Male", "Female", "Other"])) {
        $errorMsg = "Please select a valid gender option.";
    }
    // Backend Validation 7: Password length
    elseif (strlen($password) < 8) {
        $errorMsg = "Password must be at least 8 characters long.";
    }
    // Backend Validation 8: Password match
    elseif ($password !== $confirm_password) {
        $errorMsg = "Password and Confirm Password do not match.";
    }
    else {
        // Backend Check 9: Duplicate Email or Username Check using MySQLi Prepared Statement
        $checkSql = "SELECT ID, USERNAME, EMAIL FROM `student` WHERE `EMAIL` = ? OR `USERNAME` = ?";
        $checkStmt = $conn->prepare($checkSql);
        
        if (!$checkStmt) {
            $errorMsg = "Database error: " . $conn->error;
        } else {
            $checkStmt->bind_param("ss", $email, $username);
            $checkStmt->execute();
            $checkRes = $checkStmt->get_result();

            if ($checkRes->num_rows > 0) {
                $existing = $checkRes->fetch_assoc();
                if (strcasecmp($existing['EMAIL'], $email) === 0 && strcasecmp($existing['USERNAME'], $username) === 0) {
                    $errorMsg = "Both this Email ('" . htmlspecialchars($email) . "') and Username ('" . htmlspecialchars($username) . "') are already registered.";
                } elseif (strcasecmp($existing['EMAIL'], $email) === 0) {
                    $errorMsg = "The email address '" . htmlspecialchars($email) . "' is already registered. Please use another email or log in.";
                } else {
                    $errorMsg = "The username '" . htmlspecialchars($username) . "' is already taken. Please choose a different username.";
                }
                $checkStmt->close();
            } else {
                $checkStmt->close();

                // Securely Hash Password using password_hash()
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert student details into MySQL using MySQLi Prepared Statement
                $insertSql = "INSERT INTO `student` (`STUDENT_NAME`, `USERNAME`, `EMAIL`, `MOBILE_NUMBER`, `GENDER`, `PASSWORD`) VALUES (?, ?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertSql);

                if (!$insertStmt) {
                    $errorMsg = "Failed to prepare database insertion: " . $conn->error;
                } else {
                    $insertStmt->bind_param("ssssss", $student_name, $username, $email, $mobile_number, $gender, $hashed_password);
                    
                    if ($insertStmt->execute()) {
                        $newStudentId = $insertStmt->insert_id;
                        $successMsg = "Registration successful! Welcome to Student Hub, " . htmlspecialchars($student_name) . ". Your registered Student ID is <strong>" . htmlspecialchars($username) . "</strong>. Your account has been securely created.";
                        
                        // Automatically initialize their session for immediate convenience
                        $_SESSION['user_id'] = $newStudentId;
                        $_SESSION['student_id'] = $username;
                        $_SESSION['student_name'] = $student_name;
                        $_SESSION['username'] = $username;
                        $_SESSION['email'] = $email;
                        
                        // Clear input fields
                        $student_name = "";
                        $username = "";
                        $email = "";
                        $mobile_number = "";
                        $gender = "";
                    } else {
                        if ($insertStmt->errno == 1062) {
                            $errorMsg = "Duplicate entry detected: Email or Username is already registered.";
                        } else {
                            $errorMsg = "Registration failed due to a server error. Please try again.";
                        }
                    }
                    $insertStmt->close();
                }
            }
        }
    }
}

require_once __DIR__ . "/header.php";
?>

<div class="page-header" style="justify-content: center; text-align: center;">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-user-plus" style="color:var(--primary);"></i> Secure Student Registration</h1>
        <p class="page-subtitle">Join the Student Hub community. Input is validated on both frontend & backend with password encryption.</p>
    </div>
</div>

<div class="form-container">

    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger" id="backendAlert">
            <i class="fa-solid fa-triangle-exclamation" style="font-size:1.25rem; margin-top:2px;"></i>
            <div>
                <strong>Registration Error:</strong><br>
                <?= htmlspecialchars($errorMsg) ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($successMsg)): ?>
        <div class="alert alert-success" id="backendAlert">
            <i class="fa-solid fa-circle-check" style="font-size:1.3rem; margin-top:2px;"></i>
            <div>
                <strong>Success!</strong><br>
                <?= $successMsg ?><br>
                <div style="margin-top:0.75rem; display:flex; gap:0.5rem; flex-wrap:wrap;">
                    <a href="dashboard.php" class="btn btn-sm btn-success"><i class="fa-solid fa-gauge"></i> Go to Dashboard</a>
                    <a href="events.php" class="btn btn-sm btn-outline"><i class="fa-solid fa-calendar"></i> Browse Events</a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Client-side validation message container -->
    <div class="alert alert-warning" id="clientAlert" style="display: none;">
        <i class="fa-solid fa-circle-exclamation" style="font-size:1.2rem;"></i>
        <div id="clientAlertText"></div>
    </div>

    <form action="register.php" method="POST" id="regForm" onsubmit="return validateForm();" novalidate>

        <!-- Student Name -->
        <div class="form-group">
            <label class="form-label" for="student_name">
                <i class="fa-solid fa-id-card"></i> Student Full Name <span class="required">*</span>
            </label>
            <input 
                type="text" 
                class="form-control" 
                id="student_name" 
                name="student_name" 
                placeholder="e.g. Heet Doshi" 
                value="<?= htmlspecialchars($student_name) ?>"
                required
            >
            <div class="form-hint">At least 3 characters.</div>
            <div class="form-error" id="err_student_name">Name must contain at least 3 characters.</div>
        </div>

        <!-- Username -->
        <div class="form-group">
            <label class="form-label" for="username">
                <i class="fa-solid fa-id-badge"></i> Student ID / Roll No <span class="required">*</span>
            </label>
            <input 
                type="text" 
                class="form-control" 
                id="username" 
                name="username" 
                placeholder="e.g. D26CE169" 
                value="<?= htmlspecialchars($username) ?>"
                required
            >
            <div class="form-hint">Official Student ID / Roll No (e.g. <code>D26CE169</code>). This will be your permanent Student ID across all event registrations.</div>
            <div class="form-error" id="err_username">Student ID must contain 3-20 letters, numbers, or underscores.</div>
        </div>

        <!-- Email Address -->
        <div class="form-group">
            <label class="form-label" for="email">
                <i class="fa-solid fa-envelope"></i> Email Address <span class="required">*</span>
            </label>
            <input 
                type="email" 
                class="form-control" 
                id="email" 
                name="email" 
                placeholder="e.g. student@college.edu" 
                value="<?= htmlspecialchars($email) ?>"
                required
            >
            <div class="form-hint">Must be a valid email format. Used for notifications and verification.</div>
            <div class="form-error" id="err_email">Please enter a valid email address.</div>
        </div>

        <!-- Mobile Number -->
        <div class="form-group">
            <label class="form-label" for="mobile_number">
                <i class="fa-solid fa-phone"></i> Mobile Number <span class="required">*</span>
            </label>
            <input 
                type="tel" 
                class="form-control" 
                id="mobile_number" 
                name="mobile_number" 
                placeholder="Enter 10-digit mobile number" 
                maxlength="10"
                value="<?= htmlspecialchars($mobile_number) ?>"
                required
            >
            <div class="form-hint">Must be exactly 10 numeric digits.</div>
            <div class="form-error" id="err_mobile_number">Mobile number must contain exactly 10 digits.</div>
        </div>

        <!-- Gender Selection -->
        <div class="form-group">
            <label class="form-label">
                <i class="fa-solid fa-venus-mars"></i> Gender <span class="required">*</span>
            </label>
            <div class="radio-group">
                <label class="radio-label">
                    <input type="radio" name="gender" value="Male" <?= $gender === 'Male' ? 'checked' : '' ?>>
                    Male
                </label>
                <label class="radio-label">
                    <input type="radio" name="gender" value="Female" <?= $gender === 'Female' ? 'checked' : '' ?>>
                    Female
                </label>
                <label class="radio-label">
                    <input type="radio" name="gender" value="Other" <?= $gender === 'Other' ? 'checked' : '' ?>>
                    Other
                </label>
            </div>
            <div class="form-error" id="err_gender">Please select a gender.</div>
        </div>

        <!-- Password -->
        <div class="form-group">
            <label class="form-label" for="password">
                <i class="fa-solid fa-lock"></i> Password <span class="required">*</span>
            </label>
            <input 
                type="password" 
                class="form-control" 
                id="password" 
                name="password" 
                placeholder="Create a strong password (min 8 characters)" 
                required
            >
            <div class="form-hint">Minimum 8 characters. Will be securely hashed with password_hash().</div>
            <div class="form-error" id="err_password">Password must contain at least 8 characters.</div>
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <label class="form-label" for="confirm_password">
                <i class="fa-solid fa-key"></i> Confirm Password <span class="required">*</span>
            </label>
            <input 
                type="password" 
                class="form-control" 
                id="confirm_password" 
                name="confirm_password" 
                placeholder="Re-enter password" 
                required
            >
            <div class="form-error" id="err_confirm_password">Passwords do not match.</div>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:1.5rem;">
            <i class="fa-solid fa-user-shield"></i> Complete Secure Registration
        </button>

        <p style="text-align:center; font-size:0.9rem; color:var(--dark-muted); margin-top:1.25rem;">
            Already have a registered account? <a href="login.php" style="font-weight:600;">Sign in here</a>
        </p>

    </form>
</div>

<!-- Frontend Validation Script with Instant Feedback -->
<script>
function showAlert(msg) {
    const box = document.getElementById('clientAlert');
    const txt = document.getElementById('clientAlertText');
    if (box && txt) {
        txt.textContent = msg;
        box.style.display = 'flex';
        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function hideAlert() {
    const box = document.getElementById('clientAlert');
    if (box) box.style.display = 'none';
}

function validateForm() {
    hideAlert();
    let isValid = true;

    const name = document.getElementById("student_name").value.trim();
    const username = document.getElementById("username").value.trim();
    const email = document.getElementById("email").value.trim();
    const mobile = document.getElementById("mobile_number").value.trim();
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm_password").value;
    const gender = document.querySelector('input[name="gender"]:checked');

    // 1. Name Check
    if (name.length < 3) {
        showAlert("Validation Error: Student Name must contain at least 3 characters.");
        document.getElementById("student_name").focus();
        return false;
    }

    // 2. Username Check
    const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
    if (!usernameRegex.test(username)) {
        showAlert("Validation Error: Username must be 3-20 characters long (letters, numbers, underscores only).");
        document.getElementById("username").focus();
        return false;
    }

    // 3. Email Check
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showAlert("Validation Error: Please provide a valid email address format.");
        document.getElementById("email").focus();
        return false;
    }

    // 4. Mobile Check
    const mobileRegex = /^[0-9]{10}$/;
    if (!mobileRegex.test(mobile)) {
        showAlert("Validation Error: Mobile number must contain exactly 10 digits.");
        document.getElementById("mobile_number").focus();
        return false;
    }

    // 5. Gender Check
    if (!gender) {
        showAlert("Validation Error: Please choose your gender.");
        return false;
    }

    // 6. Password Check
    if (password.length < 8) {
        showAlert("Validation Error: Password must contain at least 8 characters.");
        document.getElementById("password").focus();
        return false;
    }

    // 7. Password Match Check
    if (password !== confirmPassword) {
        showAlert("Validation Error: Passwords do not match. Please re-enter.");
        document.getElementById("confirm_password").focus();
        return false;
    }

    return true;
}

// Live real-time feedback
document.addEventListener("DOMContentLoaded", function() {
    const inputs = ['student_name', 'username', 'email', 'mobile_number', 'password', 'confirm_password'];
    inputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', function() {
                hideAlert();
            });
        }
    });
});
</script>

<?php require_once __DIR__ . "/footer.php"; ?>