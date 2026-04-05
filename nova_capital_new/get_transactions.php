<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once 'config.php';

try {
    if (!isset($_SESSION['id'])) {
        throw new Exception("User not logged in");
    }

    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = :user_id ORDER BY transaction_date DESC LIMIT 50");
    $stmt->execute([':user_id' => $_SESSION['id']]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success'      => true,
        'transactions' => $transactions
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>