<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $username        = trim($_POST["name"] ?? '');
        $email           = trim($_POST["email"] ?? '');
        $password        = $_POST["password"] ?? '';
        $initial_balance = 10000.00;

        // Validate required fields
        if (empty($username) || empty($email) || empty($password)) {
            header("Location: signup.php?error=missing_fields");
            exit();
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: signup.php?error=invalid_email");
            exit();
        }

        // Validate username length
        if (strlen($username) < 3 || strlen($username) > 50) {
            header("Location: signup.php?error=invalid_username");
            exit();
        }

        // Password validation
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        if (!preg_match($pattern, $password)) {
            header("Location: signup.php?error=weak_password");
            exit();
        }

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            showEmailExistsAlert($email);
            exit();
        }

        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            header("Location: signup.php?error=username_exists");
            exit();
        }

        // Hash password
        $algo            = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
        $hashed_password = password_hash($password, $algo);

        // FIX: Insert WITHOUT created_at since your table may not have it yet.
        // If you ran the ALTER TABLE command, the created_at column now exists
        // and will be auto-filled by DEFAULT CURRENT_TIMESTAMP — no need to
        // pass it manually in the INSERT.
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO users (username, email, password, balance)
                 VALUES (:username, :email, :password, :balance)"
            );
            $stmt->bindParam(":username", $username,        PDO::PARAM_STR);
            $stmt->bindParam(":email",    $email,           PDO::PARAM_STR);
            $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
            $stmt->bindParam(":balance",  $initial_balance, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $user_id = $pdo->lastInsertId();
                $pdo->commit();

                $_SESSION['registration_success'] = true;
                $_SESSION['user_email']           = $email;
                $_SESSION['temp_user_id']         = $user_id;

                header("Location: signin.php?registered=1");
                exit();
            } else {
                $pdo->rollBack();
                header("Location: signup.php?error=registration_failed");
                exit();
            }

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Registration transaction failed: " . $e->getMessage());
            header("Location: signup.php?error=registration_failed");
            exit();
        }

    } else {
        header("Location: signup.php");
        exit();
    }

} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    header("Location: signup.php?error=server_error");
    exit();
}

// ─── Helper: Email Already Exists Alert ──────────────────────────────────────
function showEmailExistsAlert($email) {
    $escaped_email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $encoded_email = urlencode($email);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Alert</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .alert-overlay {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 1000;
            display: flex; align-items: center; justify-content: center;
        }
        .alert-box {
            background: white; padding: 30px; border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            max-width: 450px; width: 90%; text-align: center;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .alert-box h2 { color: #333; margin-bottom: 15px; font-size: 1.5em; }
        .alert-box p  { color: #666; margin-bottom: 10px; line-height: 1.5; }
        .email-highlight { font-weight: bold; color: #2196F3; }
        .alert-buttons {
            margin-top: 25px; display: flex;
            gap: 15px; justify-content: center; flex-wrap: wrap;
        }
        .alert-buttons button {
            padding: 12px 24px; cursor: pointer; border: none;
            border-radius: 5px; font-size: 14px; font-weight: 500;
            transition: all 0.3s ease; min-width: 150px;
        }
        .btn-signin { background: #4CAF50; color: white; }
        .btn-signin:hover { background: #45a049; transform: translateY(-1px); }
        .btn-signup { background: #2196F3; color: white; }
        .btn-signup:hover { background: #1976D2; transform: translateY(-1px); }
    </style>
</head>
<body>
    <div class="alert-overlay">
        <div class="alert-box">
            <h2>⚠️ Email Already Registered</h2>
            <p>The email <span class="email-highlight"><?php echo $escaped_email; ?></span> is already associated with an account.</p>
            <p>Would you like to sign in with this email or use a different one?</p>
            <div class="alert-buttons">
                <button class="btn-signin" onclick="window.location.href='signin.php?email=<?php echo $encoded_email; ?>'">
                    Sign In
                </button>
                <button class="btn-signup" onclick="window.location.href='signup.php'">
                    Use Different Email
                </button>
            </div>
        </div>
    </div>
</body>
</html>
<?php
}
?>