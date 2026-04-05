<?php
ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $json_data = file_get_contents("php://input");
        $data = json_decode($json_data, true);

        if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Invalid CSRF token");
        }

        if (!isset($_SESSION['id'])) {
            throw new Exception("User not logged in");
        }

        $user_id          = $_SESSION['id'];
        $stock_symbol     = $data['stock_symbol']     ?? 'TSLA';
        $stock_name       = $data['stock_name']       ?? 'Tesla Inc.';
        $transaction_type = strtolower($data['transaction_type'] ?? '');
        $quantity         = intval($data['quantity']         ?? 0);
        $price_per_share  = floatval($data['price_per_share'] ?? 0);

        if (empty($transaction_type) || !in_array($transaction_type, ['buy','sell']))
            throw new Exception("Invalid transaction type");
        if ($quantity <= 0)
            throw new Exception("Invalid quantity");
        if ($price_per_share <= 0)
            throw new Exception("Invalid price per share");

        $total_amount     = $quantity * $price_per_share;
        $transaction_fees = $total_amount * 0.005;

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = :uid FOR UPDATE");
        $stmt->execute([':uid' => $user_id]);
        $user = $stmt->fetch();
        if (!$user) throw new Exception("User not found");
        $current_balance = floatval($user['balance']);

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(CASE WHEN transaction_type='buy' THEN quantity ELSE -quantity END),0) as owned
            FROM transactions WHERE user_id=:uid AND stock_symbol=:sym");
        $stmt->execute([':uid' => $user_id, ':sym' => $stock_symbol]);
        $owned = intval($stmt->fetch()['owned']);

        if ($transaction_type === 'buy' && $current_balance < $total_amount + $transaction_fees)
            throw new Exception("Insufficient balance");
        if ($transaction_type === 'sell' && $owned < $quantity)
            throw new Exception("Insufficient stocks to sell");

        $new_balance = $transaction_type === 'buy'
            ? $current_balance - ($total_amount + $transaction_fees)
            : $current_balance + $total_amount - $transaction_fees;

        $stmt = $pdo->prepare("UPDATE users SET balance=:bal WHERE id=:uid");
        $stmt->execute([':bal' => $new_balance, ':uid' => $user_id]);

        $stmt = $pdo->prepare("INSERT INTO transactions
            (user_id,stock_symbol,stock_name,transaction_type,quantity,price_per_share,total_amount,transaction_fees,transaction_date,status,notes)
            VALUES (:uid,:sym,:name,:type,:qty,:pps,:total,:fees,NOW(),'completed',:notes)");
        $stmt->execute([
            ':uid'   => $user_id,   ':sym'  => $stock_symbol,
            ':name'  => $stock_name,':type' => $transaction_type,
            ':qty'   => $quantity,  ':pps'  => $price_per_share,
            ':total' => $total_amount, ':fees' => $transaction_fees,
            ':notes' => $data['notes'] ?? null
        ]);

        $transaction_id = $pdo->lastInsertId();
        $pdo->commit();

        echo json_encode([
            'success'        => true,
            'message'        => 'Transaction completed successfully',
            'transaction_id' => $transaction_id,
            'new_balance'    => $new_balance,
            'details'        => compact('transaction_type','quantity','price_per_share','total_amount','transaction_fees')
        ]);

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
ob_end_flush();
?>