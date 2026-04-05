<?php
session_start();

// ✅ Redirect if user is not logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// ✅ Database connection using PDO
$host = "localhost";
$dbname = "nova_capital";
$username = "root";
$password = "Abhi";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
}

// ✅ Fetch transactions
$user_id = $_SESSION['id'];
$transactions = [];
$total = 0;

$sql = "SELECT transaction_id, stock_symbol, stock_name, transaction_type, quantity, price_per_share, total_amount, transaction_date
        FROM transactions 
        WHERE user_id = :user_id
        ORDER BY transaction_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Calculate total
foreach ($transactions as $row) {
    $total += ($row['transaction_type'] === 'buy') ? (-$row['total_amount']) : ($row['total_amount']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Capital - Transaction History</title>
    <style>
        :root {
            --primary: #00997b;
            --primary-dark: #008870;
            --background: #f5f7f8;
            --text: #333333;
            --light: #ffffff;
            --gray: #e2e8f0;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --positive: #22c55e;
            --negative: #ef4444;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--background);
            color: var(--text);
            line-height: 1.6;
        }
        
        .header {
            background-color: var(--light);
            color: var(--text);
            padding: 1rem 0;
            width: 100%;
            border-bottom: 1px solid var(--gray);
            box-shadow: var(--shadow);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: bold;
            font-size: 1.5rem;
            color: var(--primary);
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
        }
        
        .nav-links a {
            text-decoration: none;
            color: var(--text);
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .nav-links a:hover {
            color: var(--primary);
        }
        
        .ticker-container {
            background: var(--primary);
            width: 100%;
            overflow: hidden;
            padding: 0.5rem 0;
            color: white;
        }
        
        .ticker {
            white-space: nowrap;
            display: inline-block;
            animation: ticker 50s linear infinite;
            padding-left: 100%;
        }
        
        .ticker-item {
            display: inline-block;
            padding: 0 2rem;
        }
        
        .ticker-symbol {
            font-weight: bold;
        }
        
        .ticker-price {
            margin-left: 0.5rem;
        }
        
        .ticker-up {
            color: #22c55e;
        }
        
        .ticker-down {
            color: #ef4444;
        }
        
        @keyframes ticker {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-100%);
            }
        }
        
        .main {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .card {
            background: var(--light);
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }
        
        .transaction-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .section-title {
            font-size: 1.4rem;
            color: var(--text);
            font-weight: 600;
        }
        
        .filter-dropdown {
            position: relative;
        }
        
        select {
            padding: 0.8rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid var(--gray);
            background-color: white;
            font-size: 1rem;
            color: var(--text);
            appearance: none;
            cursor: pointer;
            min-width: 200px;
        }
        
        .select-arrow {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }
        
        .transaction-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }
        
        .transaction-table th {
            background-color: var(--primary);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 500;
        }
        
        .transaction-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--gray);
        }
        
        .transaction-table tr:hover {
            background-color: rgba(0, 153, 123, 0.05);
        }
        
        .transaction-type {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 0.25rem;
            font-weight: 600;
            text-align: center;
            min-width: 80px;
        }
        
        .type-buy {
            background-color: rgba(34, 197, 94, 0.1);
            color: var(--positive);
        }
        
        .type-sell {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--negative);
        }
        
        .total-section {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 1rem;
            background-color: var(--gray);
            border-radius: 0.5rem;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .total-label {
            margin-right: 1rem;
        }
        
        .total-value {
            color: var(--primary);
            font-size: 1.2rem;
        }
        
        .no-transactions {
            text-align: center;
            padding: 3rem 0;
            color: #666;
            font-style: italic;
        }
        
        .action-button {
            background-color: var(--primary);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .action-button:hover {
            background-color: var(--primary-dark);
        }
        
        footer {
            background-color: var(--light);
            color: var(--text);
            padding: 2rem 0;
            margin-top: 3rem;
            border-top: 1px solid var(--gray);
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
        }
        
        .footer-left {
            max-width: 400px;
        }
        
        .footer-logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .footer-right {
            display: flex;
            gap: 3rem;
        }
        
        .footer-column h3 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: var(--text);
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 0.5rem;
        }
        
        .footer-links a {
            color: #666;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--primary);
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid var(--gray);
            font-size: 0.9rem;
            color: #999;
            max-width: 1200px;
            margin: 2rem auto 0;
            padding: 2rem 2rem 0;
        }
        
        @media (max-width: 768px) {
            .transaction-table {
                display: block;
                overflow-x: auto;
            }
            
            .footer-content {
                flex-direction: column;
                gap: 2rem;
            }
            
            .footer-right {
                flex-direction: column;
                gap: 1.5rem;
            }
            
            .transaction-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .filter-dropdown {
                width: 100%;
            }
            
            select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                NOVA CAPITAL
            </div>
            <div class="nav-links">
                <a href="abhi2.html">Home</a>
                <a href="dash1.html">Dashboard</a>
                <a href="news.html">News</a>
                <a href="Average.html">Average</a>
                <a href="signin.html">Logout</a>
            </div>
        </div>
    </header>

    <div class="ticker-container">
        <div class="ticker">
            <div class="ticker-item">
                <span class="ticker-symbol">AAPL</span>
                <span class="ticker-price ticker-up">$182.63 +1.4%</span>
            </div>
            <div class="ticker-item">
                <span class="ticker-symbol">MSFT</span>
                <span class="ticker-price ticker-up">$417.32 +0.8%</span>
            </div>
            <div class="ticker-item">
                <span class="ticker-symbol">GOOG</span>
                <span class="ticker-price ticker-up">$171.23 +2.1%</span>
            </div>
            <div class="ticker-item">
                <span class="ticker-symbol">AMZN</span>
                <span class="ticker-price ticker-down">$182.54 -0.3%</span>
            </div>
            <div class="ticker-item">
                <span class="ticker-symbol">META</span>
                <span class="ticker-price ticker-up">$493.17 +1.7%</span>
            </div>
            <div class="ticker-item">
                <span class="ticker-symbol">TSLA</span>
                <span class="ticker-price ticker-down">$176.95 -1.2%</span>
            </div>
        </div>
    </div>

    <main class="main">
        <div class="card">
            <div class="transaction-header">
                <h2 class="section-title">Transaction History</h2>
                <div class="filter-dropdown">
                    <select id="transactionFilter" onchange="filterTransactions(this.value)">
                        <option value="all">All Transactions</option>
                        <option value="buy">Buy Orders</option>
                        <option value="sell">Sell Orders</option>
                    </select>
                    <div class="select-arrow">▼</div>
                </div>
            </div>

            <?php if (!empty($transactions)): ?>
                <div style="overflow-x: auto;">
                    <table class="transaction-table">
                        <thead>
                            <tr>
                                <th>Stock</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Price/Share</th>
                                <th>Total</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr class="transaction-row" data-type="<?= $transaction['transaction_type'] ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($transaction['stock_symbol']) ?></strong><br>
                                        <span style="color: #666; font-size: 0.9rem;"><?= htmlspecialchars($transaction['stock_name']) ?></span>
                                    </td>
                                    <td>
                                        <span class="transaction-type type-<?= $transaction['transaction_type'] ?>">
                                            <?= ucfirst($transaction['transaction_type']) ?>
                                        </span>
                                    </td>
                                    <td><?= number_format($transaction['quantity'], 2) ?></td>
                                    <td>$<?= number_format($transaction['price_per_share'], 2) ?></td>
                                    <td><strong>$<?= number_format($transaction['total_amount'], 2) ?></strong></td>
                                    <td><?= date('M j, Y H:i', strtotime($transaction['transaction_date'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="total-section">
                    <span class="total-label">Net Total:</span>
                    <span class="total-value">$<?= number_format($total, 2) ?></span>
                </div>
            <?php else: ?>
                <div class="no-transactions">
                    <p>No transactions found.</p>
                    <a href="#" class="action-button" style="margin-top: 1rem;">Make Your First Trade</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-left">
                <div class="footer-logo">NOVA CAPITAL</div>
                <p>Your trusted partner for stock market investments and financial guidance. We provide real-time data and expert analysis to help you make informed decisions.</p>
            </div>
            <div class="footer-right">
                <div class="footer-column">
                    <h3>Services</h3>
                    <ul class="footer-links">
                        <li><a href="#">Portfolio Management</a></li>
                        <li><a href="#">Stock Trading</a></li>
                        <li><a href="#">Market Analysis</a></li>
                        <li><a href="#">Financial Advising</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Support</h3>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">FAQs</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Nova Capital. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function filterTransactions(filter) {
            const rows = document.querySelectorAll('.transaction-row');
            rows.forEach(row => {
                const type = row.getAttribute('data-type');
                row.style.display = (filter === 'all' || type === filter) ? '' : 'none';
            });
        }
    </script>
</body>
</html>