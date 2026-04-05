<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once 'config.php';

try {
    if (!isset($_SESSION['id'])) throw new Exception("User not logged in");

    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = :uid");
    $stmt->execute([':uid' => $_SESSION['id']]);
    $user = $stmt->fetch();

    if (!$user) throw new Exception("User not found");

    echo json_encode(['success' => true, 'balance' => floatval($user['balance'])]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>