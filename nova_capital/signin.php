<?php
// Start session only once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set a dummy user_id for testing (remove this in production)
$_SESSION['user_id'] = 1;  // Replace with actual user ID after login validation
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Sign In | Nova Capital</title>
    <link rel="icon" type="image/png" href="Bull-removebg-preview.png" />
    <style>
        /* --- styles remain unchanged --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f5f7f8;
        }

        .container {
            display: flex;
            width: 800px;
            height: 450px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .left-panel {
            width: 40%;
            background: linear-gradient(135deg, #00a389, #00574d);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .left-panel h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .left-panel p {
            font-size: 14px;
            text-align: center;
            opacity: 0.9;
        }

        .right-panel {
            width: 60%;
            background: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .right-panel h2 {
            color: #00574d;
            font-size: 22px;
            margin-bottom: 20px;
        }

        .input-field {
            width: 100%;
            margin-bottom: 15px;
        }

        .input-field input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            background: #f5f7f8;
            color: #333;
            outline: none;
        }

        .checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .checkbox input {
            margin-right: 8px;
        }

        .checkbox label {
            color: #666;
            font-size: 12px;
        }

        .btn-container {
            display: flex;
            gap: 10px;
        }

        button, input[type="submit"] {
            flex: 1;
            padding: 12px;
            border: none;
            font-size: 14px;
            cursor: pointer;
            border-radius: 5px;
            color: white;
        }

        .btn-signup {
            background: white;
            color: #00a389;
            border: 1px solid #00a389;
        }

        .btn-signin {
            background: #00a389;
        }

        button:hover, input[type="submit"]:hover {
            opacity: 0.8;
        }

        .forgot-password {
            text-align: right;
            margin-bottom: 15px;
            font-size: 12px;
            color: #00a389;
            cursor: pointer;
        }

        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
            padding: 8px;
            background-color: rgba(231, 76, 60, 0.1);
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left Panel -->
        <div class="left-panel">
            <h1>Welcome Back</h1>
            <img src="Qd6QO301.svg" alt="Stock Exchange Illustration" />
        </div>

        <!-- Right Panel -->
        <div class="right-panel">
            <h2>Sign In to Nova Capital</h2>

            <!-- Show error if any -->
            <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
            <?php endif; ?>

            <form action="login.php" method="post">
                <div class="input-field">
                    <input type="text" name="user_identifier" placeholder="Email" required />
                </div>

                <div class="input-field">
                    <input type="password" name="password" placeholder="Enter your password" required />
                </div>

                <div class="forgot-password">Forgot Password?</div>

                <div class="btn-container">
                    <button type="button" class="btn-signup" onclick="window.location.href='Homepage.html'">Home</button>
                    <button type="button" class="btn-signup" onclick="window.location.href='signup.html'">Sign Up</button>
                    <input type="submit" class="btn-signin" value="Log In" />
                </div>

                
            </form>
        </div>
    </div>

    <script>
        // Forgot Password functionality
        document.querySelector('.forgot-password').addEventListener('click', function () {
            alert('Password reset functionality will be implemented soon.');
        });
    </script>
</body>
</html>
