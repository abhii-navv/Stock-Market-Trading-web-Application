<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $identifier = trim($_POST['user_identifier'] ?? '');
        $pass       = $_POST['password'] ?? '';

        if (empty($identifier) || empty($pass)) {
            header("Location: signin.php?error=Please+enter+your+username+and+password");
            exit();
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :id1 OR email = :id2");
        $stmt->bindParam(':id1', $identifier);
        $stmt->bindParam(':id2', $identifier);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($pass, $user['password'])) {
            session_regenerate_id(true);

            // Store the actual logged-in user's data
            $_SESSION['id']       = $user['id'];
            $_SESSION['user']     = $user['username'];
            $_SESSION['email']    = $user['email'];

            header("Location: dash1.php");
            exit();
        } else {
            header("Location: signin.php?error=Invalid+username%2Femail+or+password");
            exit();
        }
    } else {
        header("Location: signin.php");
        exit();
    }

} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    header("Location: signin.php?error=A+server+error+occurred.+Please+try+again.");
    exit();
}
?>