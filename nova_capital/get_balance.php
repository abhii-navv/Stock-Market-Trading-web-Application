<?php
session_start();
header('Content-Type: application/json');

// Database connection configuration
$host = "localhost";
$dbname = "nova_capital"; 
$username = "root";
$password = "Abhi";

try {
    // Check if user is logged in
    if (!isset($_SESSION['id'])) {
        throw new Exception("User not logged in");
    }
    
    $user_id = $_SESSION['id'];
    
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get user balance
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception("User not found");
    }
    
    echo json_encode([
        'success' => true,
        'balance' => floatval($user['balance'])
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>