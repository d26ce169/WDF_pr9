<?php
// login.php - Secure Student Login
require_once __DIR__ . "/connection.php";

$pageTitle = "Student Login";
$errorMsg = "";
$infoMsg = "";

// Check for URL messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'logged_out') {
        $infoMsg = "You have been logged out successfully.";
    } elseif ($_GET['msg'] === 'login_required') {
        $infoMsg = "Access Restricted: Please sign in to access that page. When logged out, only Home, Register, and Login pages are accessible.";
    }
}

// Redirect if already logged in
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$login_id = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login_id = trim($_POST["login_id"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($login_id === "" || $password === "") {
        $errorMsg = "Please enter your username/email and password.";
    } else {
        // Query student by USERNAME or EMAIL using prepared statement
        $sql = "SELECT ID, STUDENT_NAME, USERNAME, EMAIL, MOBILE_NUMBER, GENDER, PASSWORD FROM `student` WHERE `USERNAME` = ? OR `EMAIL` = ? LIMIT 1";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $errorMsg = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $login_id, $login_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Verify hashed password
                if (password_verify($password, $user['PASSWORD'])) {
                    // Prevent session fixation
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = (int)$user['ID'];
                    $_SESSION['student_id'] = $user['USERNAME'];
                    $_SESSION['student_name'] = $user['STUDENT_NAME'];
                    $_SESSION['username'] = $user['USERNAME'];
                    $_SESSION['email'] = $user['EMAIL'];
                    $_SESSION['mobile_number'] = $user['MOBILE_NUMBER'];
                    $_SESSION['gender'] = $user['GENDER'];

                    $stmt->close();
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $errorMsg = "Invalid credentials. The password you entered is incorrect.";
                }
            } else {
                $errorMsg = "No student account found with the provided username or email.";
            }
            $stmt->close();
        }
    }
}

require_once __DIR__ . "/header.php";
?>

<div class="page-header" style="justify-content: center; text-align: center;">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-right-to-bracket" style="color:var(--primary);"></i> Student Login</h1>
        <p class="page-subtitle">Access your personal dashboard, registered events, and student profile.</p>
    </div>
</div>

<div class="form-container">

    <?php if (!empty($infoMsg)): ?>
        <div class="alert alert-info">
            <i class="fa-solid fa-circle-info" style="font-size:1.2rem;"></i>
            <div><?= htmlspecialchars($infoMsg) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-triangle-exclamation" style="font-size:1.2rem;"></i>
            <div><?= htmlspecialchars($errorMsg) ?></div>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST" autocomplete="on">
        <div class="form-group">
            <label class="form-label" for="login_id">
                <i class="fa-solid fa-id-badge"></i> Student ID / Username or Email <span class="required">*</span>
            </label>
            <input 
                type="text" 
                class="form-control" 
                id="login_id" 
                name="login_id" 
                placeholder="Enter your Student ID (e.g. D26CE169) or email" 
                value="<?= htmlspecialchars($login_id) ?>" 
                required 
                autofocus
            >
        </div>

        <div class="form-group">
            <label class="form-label" for="password">
                <i class="fa-solid fa-lock"></i> Password <span class="required">*</span>
            </label>
            <input 
                type="password" 
                class="form-control" 
                id="password" 
                name="password" 
                placeholder="Enter your account password" 
                required
            >
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:1.5rem;">
            <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In to Portal
        </button>

        <div style="text-align:center; margin-top:1.5rem; font-size:0.9rem; color:var(--dark-muted);">
            Don't have an account yet? <a href="register.php" style="font-weight:600;">Register new student</a>
        </div>
    </form>

    <div style="margin-top:2rem; padding:1rem; background:#f8fafc; border-radius:8px; border:1px dashed var(--border); font-size:0.85rem; color:var(--dark-muted);">
        <strong style="color:var(--dark);"><i class="fa-solid fa-user-plus" style="color:var(--primary);"></i> Fresh Database Notice:</strong><br>
        All tables are cleared and fresh! Please create your first student account using the <a href="register.php" style="font-weight:600;">Student Registration Page</a>, then sign in here with your new credentials.
    </div>
</div>

<?php require_once __DIR__ . "/footer.php"; ?>
