<?php
session_start();
header('Content-Type: application/json');

$host = "localhost";
$dbname = "nova_capital";
$username = "root";
$password = "Abhi";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = :user_id ORDER BY transaction_date DESC LIMIT 50");
    $stmt->execute([':user_id' => $_SESSION['id']]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'transactions' => $transactions
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>