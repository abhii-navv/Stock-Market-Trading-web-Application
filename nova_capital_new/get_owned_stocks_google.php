<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once 'config.php';

try {
    if (!isset($_SESSION['id'])) throw new Exception("User not logged in");

    $symbol = strtoupper('GOOG');
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(CASE WHEN transaction_type='buy' THEN quantity ELSE -quantity END),0) as owned
        FROM transactions WHERE user_id = :uid AND stock_symbol = :sym");
    $stmt->execute([':uid' => $_SESSION['id'], ':sym' => $symbol]);
    echo json_encode(['owned' => intval($stmt->fetch()['owned'])]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>