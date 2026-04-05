<?php
session_start();

$host = "localhost";
$dbname = "nova_capital";
$username = "root";
$password = "Abhi"; // adjust as needed

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $identifier = $_POST['user_identifier'];
        $pass = $_POST['password'];

        // Query to check by username or email
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :id OR email = :id");
        $stmt->bindParam(':id', $identifier);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        // echo "<pre>";
        // print_r($user);
        // echo "</pre>";
        // exit();


        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['id'] = $user['id']; // for access control in other pages
            $_SESSION['user'] = $user['username'];
            header("Location: dash1.php");
            exit();
        } else {
            header("Location: signin.php?error=Invalid username/email or password");
            exit();
        }
    }
} catch (PDOException $e) {
    echo "Database connection or query error: " . $e->getMessage();
    exit();
}

?>
