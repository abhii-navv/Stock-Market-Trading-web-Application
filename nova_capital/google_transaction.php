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

$host = "localhost";
$dbname = "nova_capital";
$username = "root";
$password = "Abhi";

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
        
        $user_id = $_SESSION['id'];
        $stock_symbol = $data['stock_symbol'] ?? 'GOOG';
        $stock_name = $data['stock_name'] ?? 'Google Inc.';
        $transaction_type = strtolower($data['transaction_type'] ?? '');
        $quantity = intval($data['quantity'] ?? 0);
        $price_per_share = floatval($data['price_per_share'] ?? 0);
        
        if (empty($transaction_type) || !in_array($transaction_type, ['buy', 'sell'])) {
            throw new Exception("Invalid transaction type");
        }
        
        if ($quantity <= 0) {
            throw new Exception("Invalid quantity");
        }
        
        if ($price_per_share <= 0) {
            throw new Exception("Invalid price per share");
        }
        
        $total_amount = $quantity * $price_per_share;
        $transaction_fees = $total_amount * 0.005;

        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Begin transaction
        $pdo->beginTransaction();
        
        // Get current balance and owned stocks
        $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = :user_id FOR UPDATE");
        $stmt->execute([':user_id' => $user_id]);
        $user = $stmt->fetch();
        $current_balance = $user['balance'];
        
        $stmt = $pdo->prepare("SELECT SUM(CASE WHEN transaction_type = 'buy' THEN quantity ELSE -quantity END) as owned 
            FROM transactions WHERE user_id = :user_id AND stock_symbol = :stock_symbol");
        $stmt->execute([':user_id' => $user_id, ':stock_symbol' => $stock_symbol]);
        $owned = $stmt->fetch()['owned'] ?? 0;
        
        // Validate transaction
        if ($transaction_type === 'buy' && $current_balance < $total_amount + $transaction_fees) {
            throw new Exception("Insufficient balance");
        }
        if ($transaction_type === 'sell' && $owned < $quantity) {
            throw new Exception("Insufficient stocks to sell");
        }
        
        // Update balance
        $new_balance = $transaction_type === 'buy' 
            ? $current_balance - ($total_amount + $transaction_fees)
            : $current_balance + $total_amount - $transaction_fees;
        
        $stmt = $pdo->prepare("UPDATE users SET balance = :balance WHERE id = :user_id");
        $stmt->execute([':balance' => $new_balance, ':user_id' => $user_id]);
        
        // Insert transaction
        $stmt = $pdo->prepare("
            INSERT INTO transactions (
                user_id, stock_symbol, stock_name, transaction_type,
                quantity, price_per_share, total_amount, transaction_fees,
                transaction_date, status, notes
            ) VALUES (
                :user_id, :stock_symbol, :stock_name, :transaction_type,
                :quantity, :price_per_share, :total_amount, :transaction_fees,
                NOW(), 'completed', :notes
            )
        ");
        
        $stmt->execute([
            ':user_id' => $user_id,
            ':stock_symbol' => $stock_symbol,
            ':stock_name' => $stock_name,
            ':transaction_type' => $transaction_type,
            ':quantity' => $quantity,
            ':price_per_share' => $price_per_share,
            ':total_amount' => $total_amount,
            ':transaction_fees' => $transaction_fees,
            ':notes' => $data['notes'] ?? null
        ]);
        
        $transaction_id = $pdo->lastInsertId();
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Transaction completed successfully',
            'transaction_id' => $transaction_id,
            'new_balance' => $new_balance,
            'details' => [
                'transaction_type' => $transaction_type,
                'quantity' => $quantity,
                'price_per_share' => $price_per_share,
                'total_amount' => $total_amount,
                'transaction_fees' => $transaction_fees
            ]
        ]);
        
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}

ob_end_flush();
?>