<?php
session_start();
ob_start();

// Database configuration
$host = "localhost";
$dbname = "nova_capital";
$username = "root";
$password = "Abhi";

try {
    // Check if user is logged in
    if (!isset($_SESSION['id'])) {
        header("Location: signin.php?error=Please log in to view transaction history");
        exit();
    }

    $user_id = $_SESSION['id'];

    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch transaction history
    $stmt = $pdo->prepare("
        SELECT 
            id,
            stock_symbol,
            stock_name,
            transaction_type,
            quantity,
            price_per_share,
            total_amount,
            transaction_fees,
            transaction_date,
            status
        FROM transactions 
        WHERE user_id = :user_id 
        ORDER BY transaction_date DESC
    ");
    $stmt->execute([':user_id' => $user_id]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total investment
    $total_investment = 0;
    foreach ($transactions as $tx) {
        if ($tx['transaction_type'] === 'buy') {
            $total_investment += $tx['total_amount'] + $tx['transaction_fees'];
        } else {
            $total_investment -= ($tx['total_amount'] - $tx['transaction_fees']);
        }
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History | Nova Capital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f7f8;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .history-container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 25px;
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0f0e0;
        }

        .history-header h2 {
            color: #00574d;
            font-size: 24px;
            margin: 0;
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid #95a5a6;
            border-radius: 4px;
            background-color: white;
            color: #00574d;
            font-weight: 500;
            cursor: pointer;
            outline: none;
        }

        .filter-select:hover {
            border-color: #00574d;
        }

        .transaction-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .transaction-table th {
            background-color: #00574d;
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .transaction-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0f0e0;
            font-size: 14px;
        }

        .transaction-table tr:hover {
            background-color: #f5f7f8;
        }

        .type-buy {
            color: #2e7d32;
            font-weight: bold;
            background-color: #e8f5e9;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
        }

        .type-sell {
            color: #c62828;
            font-weight: bold;
            background-color: #ffebee;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
        }

        .total-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #e8f5e9;
            border-radius: 6px;
            text-align: right;
            font-size: 16px;
            color: #00574d;
            font-weight: 600;
        }

        .no-transactions {
            text-align: center;
            padding: 40px 0;
            color: #666;
            font-style: italic;
            font-size: 16px;
        }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #00574d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }

        .back-button:hover {
            background-color: #00776b;
        }
    </style>
</head>
<body>
    <div class="history-container">
        <div class="history-header">
            <h2>Transaction History</h2>
            <select class="filter-select" onchange="filterTransactions(this.value)">
                <option value="all">All Transactions</option>
                <option value="buy">Buy Only</option>
                <option value="sell">Sell Only</option>
                <option value="completed">Completed Only</option>
            </select>
        </div>

        <?php if (empty($transactions)): ?>
            <div class="no-transactions">
                No transactions found in your history
            </div>
        <?php else: ?>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date & Time</th>
                        <th>Type</th>
                        <th>Stock</th>
                        <th>Quantity</th>
                        <th>Price/Share</th>
                        <th>Total</th>
                        <th>Fees</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx): ?>
                        <tr class="transaction-row" 
                            data-type="<?php echo $tx['transaction_type']; ?>" 
                            data-status="<?php echo $tx['status']; ?>">
                            <td><?php echo htmlspecialchars($tx['id']); ?></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($tx['transaction_date']))); ?></td>
                            <td>
                                <span class="<?php echo $tx['transaction_type'] === 'buy' ? 'type-buy' : 'type-sell'; ?>">
                                    <?php echo ucfirst(htmlspecialchars($tx['transaction_type'])); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($tx['stock_symbol'] . ' - ' . $tx['stock_name']); ?></td>
                            <td><?php echo htmlspecialchars($tx['quantity']); ?></td>
                            <td>$<?php echo number_format($tx['price_per_share'], 2); ?></td>
                            <td>$<?php echo number_format($tx['total_amount'], 2); ?></td>
                            <td>$<?php echo number_format($tx['transaction_fees'], 2); ?></td>
                            <td><?php echo htmlspecialchars($tx['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="total-section">
                Total Net Investment: 
                <span>
                    <?php 
                    echo $total_investment >= 0 
                        ? '$' . number_format($total_investment, 2) 
                        : '-$' . number_format(abs($total_investment), 2);
                    ?>
                </span>
            </div>
        <?php endif; ?>

        <a href="dash1.php" class="back-button">Back to Dashboard</a>
    </div>

    <script>
        function filterTransactions(filter) {
            const rows = document.querySelectorAll('.transaction-row');
            
            rows.forEach(row => {
                const type = row.getAttribute('data-type');
                const status = row.getAttribute('data-status');
                
                switch(filter) {
                    case 'buy':
                        row.style.display = type === 'buy' ? '' : 'none';
                        break;
                    case 'sell':
                        row.style.display = type === 'sell' ? '' : 'none';
                        break;
                    case 'completed':
                        row.style.display = status === 'completed' ? '' : 'none';
                        break;
                    default:
                        row.style.display = '';
                }
            });
        }
    </script>
</body>
</html>

<?php
ob_end_flush();
?>