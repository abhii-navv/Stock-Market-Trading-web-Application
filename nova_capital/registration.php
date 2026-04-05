<?php
session_start();

// Database configuration - consider moving to a separate config file
$host = "localhost";
$dbname = "nova_capital";
$username = "root";
$password = "Abhi"; // Consider using environment variables for production

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Input validation and sanitization
        $username = trim($_POST["name"] ?? '');
        $email = trim($_POST["email"] ?? '');
        $password = $_POST["password"] ?? '';
        $initial_balance = 10000.00;

        // Validate required fields
        if (empty($username) || empty($email) || empty($password)) {
            header("Location: signup.html?error=missing_fields");
            exit();
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: signup.html?error=invalid_email");
            exit();
        }

        // Validate username length and characters
        if (strlen($username) < 3 || strlen($username) > 50) {
            header("Location: signup.html?error=invalid_username");
            exit();
        }

        // Password validation - at least 8 chars, 1 uppercase, 1 lowercase, 1 digit, 1 special char
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        if (!preg_match($pattern, $password)) {
            header("Location: signup.html?error=weak_password");
            exit();
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Show alert popup for existing email
            showEmailExistsAlert($email);
            exit();
        }

        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            header("Location: signup.html?error=username_exists");
            exit();
        }

        // Hash password securely
        $hashed_password = password_hash($password, PASSWORD_ARGON2ID);

        // Begin transaction for data integrity
        $conn->beginTransaction();

        try {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, balance, created_at) VALUES (:username, :email, :password, :balance, NOW())");
            $stmt->bindParam(":username", $username, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
            $stmt->bindParam(":balance", $initial_balance, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $user_id = $conn->lastInsertId();
                
                // Commit transaction
                $conn->commit();
                
                // Set session variables for successful registration
                $_SESSION['registration_success'] = true;
                $_SESSION['user_email'] = $email;
                $_SESSION['temp_user_id'] = $user_id; // Temporary, will be cleared on signin
                
                // Redirect to signin page
                header("Location: signin.php?registered=1");
                exit();
            } else {
                $conn->rollBack();
                header("Location: signup.html?error=registration_failed");
                exit();
            }
        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Registration transaction failed: " . $e->getMessage());
            header("Location: signup.html?error=registration_failed");
            exit();
        }
    } else {
        // If not POST request, redirect to signup page
        header("Location: signup.html");
        exit();
    }

} catch(PDOException $e) {
    // Log the error for debugging
    error_log("Database connection error: " . $e->getMessage());
    header("Location: signup.html?error=server_error");
    exit();
}

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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .alert-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .alert-box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            max-width: 450px;
            width: 90%;
            text-align: center;
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-box h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.5em;
        }
        
        .alert-box p {
            color: #666;
            margin-bottom: 10px;
            line-height: 1.5;
        }
        
        .email-highlight {
            font-weight: bold;
            color: #2196F3;
        }
        
        .alert-buttons {
            margin-top: 25px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .alert-buttons button {
            padding: 12px 24px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            min-width: 150px;
        }
        
        .btn-signin {
            background: #4CAF50;
            color: white;
        }
        
        .btn-signin:hover {
            background: #45a049;
            transform: translateY(-1px);
        }
        
        .btn-signup {
            background: #2196F3;
            color: white;
        }
        
        .btn-signup:hover {
            background: #1976D2;
            transform: translateY(-1px);
        }
        
        @media (max-width: 480px) {
            .alert-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .alert-buttons button {
                width: 100%;
                max-width: 200px;
            }
        }
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
                <button class="btn-signup" onclick="window.location.href='signup.html'">
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