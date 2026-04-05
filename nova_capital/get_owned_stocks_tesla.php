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
    
    $stmt = $pdo->prepare("SELECT SUM(CASE WHEN transaction_type = 'buy' THEN quantity ELSE -quantity END) as owned 
        FROM transactions WHERE user_id = :user_id AND stock_symbol = 'TSLA'");
    $stmt->execute([':user_id' => $_SESSION['id']]);
    $result = $stmt->fetch();
    
    echo json_encode(['owned' => $result['owned'] ?? 0]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>