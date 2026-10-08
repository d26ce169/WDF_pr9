<?php
// profile.php - Student Profile & Account Security (Table: student)
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/connection.php";

$pageTitle = "Student Profile & Security";
$profileMsg = "";
$profileErr = "";
$passMsg = "";
$passErr = "";

$studentId = (int)$_SESSION['user_id'];

// Fetch current details
$stmt = $conn->prepare("SELECT * FROM `student` WHERE `ID` = ? LIMIT 1");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    die("Student record not found.");
}

// Handle Profile Details Update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "update_profile") {
    $newName = trim($_POST["student_name"] ?? "");
    $newMobile = trim($_POST["mobile_number"] ?? "");
    $newGender = trim($_POST["gender"] ?? "");

    if (strlen($newName) < 3) {
        $profileErr = "Student name must contain at least 3 characters.";
    } elseif (!preg_match("/^[0-9]{10}$/", $newMobile)) {
        $profileErr = "Mobile number must be exactly 10 digits.";
    } elseif (!in_array($newGender, ["Male", "Female", "Other"])) {
        $profileErr = "Please select a valid gender option.";
    } else {
        $upStmt = $conn->prepare("UPDATE `student` SET `STUDENT_NAME` = ?, `MOBILE_NUMBER` = ?, `GENDER` = ? WHERE `ID` = ?");
        if ($upStmt) {
            $upStmt->bind_param("sssi", $newName, $newMobile, $newGender, $studentId);
            if ($upStmt->execute()) {
                $profileMsg = "Profile updated successfully!";
                $_SESSION['student_name'] = $newName;
                $_SESSION['mobile_number'] = $newMobile;
                $_SESSION['gender'] = $newGender;

                $student['STUDENT_NAME'] = $newName;
                $student['MOBILE_NUMBER'] = $newMobile;
                $student['GENDER'] = $newGender;
            } else {
                $profileErr = "Failed to update profile: " . $upStmt->error;
            }
            $upStmt->close();
        }
    }
}

// Handle Password Change
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "change_password") {
    $currentPass = $_POST["current_password"] ?? "";
    $newPass = $_POST["new_password"] ?? "";
    $confirmPass = $_POST["confirm_new_password"] ?? "";

    if ($currentPass === "" || $newPass === "" || $confirmPass === "") {
        $passErr = "All password fields are required.";
    } elseif (!password_verify($currentPass, $student['PASSWORD'])) {
        $passErr = "Current password is incorrect.";
    } elseif (strlen($newPass) < 8) {
        $passErr = "New password must contain at least 8 characters.";
    } elseif ($newPass !== $confirmPass) {
        $passErr = "New passwords do not match.";
    } else {
        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $passStmt = $conn->prepare("UPDATE `student` SET `PASSWORD` = ? WHERE `ID` = ?");
        if ($passStmt) {
            $passStmt->bind_param("si", $hashed, $studentId);
            if ($passStmt->execute()) {
                $passMsg = "Your password has been changed securely!";
                $student['PASSWORD'] = $hashed;
            } else {
                $passErr = "Failed to update password: " . $passStmt->error;
            }
            $passStmt->close();
        }
    }
}

require_once __DIR__ . "/header.php";
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-user-gear" style="color:var(--primary);"></i> Student Profile & Account Security</h1>
        <p class="page-subtitle">View and update your student account stored in MySQL <code>student</code> table.</p>
    </div>
    <div>
        <a href="dashboard.php" class="btn btn-outline-primary"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>

<div class="grid-2">
    <!-- Profile Information Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fa-solid fa-id-card-clip" style="color:var(--primary);"></i> Personal Information</h3>
            <span class="badge badge-primary">ID: <?= $studentId ?></span>
        </div>

        <?php if (!empty($profileMsg)): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <div><?= htmlspecialchars($profileMsg) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($profileErr)): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div><?= htmlspecialchars($profileErr) ?></div>
            </div>
        <?php endif; ?>

        <form action="profile.php" method="POST">
            <input type="hidden" name="action" value="update_profile">

            <!-- Read-only Username & Email -->
            <div class="form-group">
                <label class="form-label"><i class="fa-solid fa-at"></i> Username (Unique Identifier)</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($student['USERNAME']) ?>" disabled style="background:#f1f5f9; cursor:not-allowed;">
                <div class="form-hint">Username is locked to protect table references.</div>
            </div>

            <div class="form-group">
                <label class="form-label"><i class="fa-solid fa-envelope"></i> Email Address</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($student['EMAIL']) ?>" disabled style="background:#f1f5f9; cursor:not-allowed;">
                <div class="form-hint">Unique email registered in database.</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="student_name"><i class="fa-solid fa-user"></i> Full Student Name <span class="required">*</span></label>
                <input type="text" class="form-control" id="student_name" name="student_name" value="<?= htmlspecialchars($student['STUDENT_NAME']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="mobile_number"><i class="fa-solid fa-phone"></i> Mobile Number (10 Digits) <span class="required">*</span></label>
                <input type="tel" class="form-control" id="mobile_number" name="mobile_number" maxlength="10" value="<?= htmlspecialchars($student['MOBILE_NUMBER']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label"><i class="fa-solid fa-venus-mars"></i> Gender <span class="required">*</span></label>
                <div class="radio-group">
                    <label class="radio-label">
                        <input type="radio" name="gender" value="Male" <?= ($student['GENDER'] === 'Male') ? 'checked' : '' ?>>
                        Male
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="gender" value="Female" <?= ($student['GENDER'] === 'Female') ? 'checked' : '' ?>>
                        Female
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="gender" value="Other" <?= ($student['GENDER'] === 'Other') ? 'checked' : '' ?>>
                        Other
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fa-solid fa-floppy-disk"></i> Save Profile Changes
            </button>
        </form>
    </div>

    <!-- Password & Security Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fa-solid fa-lock" style="color:var(--danger);"></i> Change Password</h3>
            <span class="badge badge-success"><i class="fa-solid fa-shield-halved"></i> password_hash()</span>
        </div>

        <?php if (!empty($passMsg)): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <div><?= htmlspecialchars($passMsg) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($passErr)): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div><?= htmlspecialchars($passErr) ?></div>
            </div>
        <?php endif; ?>

        <form action="profile.php" method="POST">
            <input type="hidden" name="action" value="change_password">

            <div class="form-group">
                <label class="form-label" for="current_password">
                    <i class="fa-solid fa-key"></i> Current Password <span class="required">*</span>
                </label>
                <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Enter existing password" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="new_password">
                    <i class="fa-solid fa-lock"></i> New Password <span class="required">*</span>
                </label>
                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="At least 8 characters" required>
                <div class="form-hint">Must be at least 8 characters long.</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm_new_password">
                    <i class="fa-solid fa-check-double"></i> Confirm New Password <span class="required">*</span>
                </label>
                <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" placeholder="Re-enter new password" required>
            </div>

            <button type="submit" class="btn btn-danger btn-block">
                <i class="fa-solid fa-shield-halved"></i> Update Password Securely
            </button>
        </form>

        <div style="margin-top:2rem; padding:1rem; background:#f8fafc; border-radius:8px; border:1px solid var(--border); font-size:0.85rem; color:var(--dark-muted);">
            <strong style="color:var(--dark);"><i class="fa-solid fa-info-circle" style="color:var(--primary);"></i> Security Implementation:</strong><br>
            Passwords are never stored in plaintext. They are encrypted with strong Bcrypt salts using PHP's native <code>password_hash($pwd, PASSWORD_DEFAULT)</code>.
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/footer.php"; ?>
