<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';

if (!isset($_SESSION['id'])) {
    header("Location: signin.php?error=Please+log+in+to+view+transaction+history");
    exit();
}

$user_id = $_SESSION['id'];

try {
    $stmt = $pdo->prepare("
        SELECT id, stock_symbol, stock_name, transaction_type,
               quantity, price_per_share, total_amount,
               transaction_fees, transaction_date, status
        FROM transactions
        WHERE user_id = :user_id
        ORDER BY transaction_date DESC
    ");
    $stmt->execute([':user_id' => $user_id]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_investment = 0;
    foreach ($transactions as $tx) {
        if ($tx['transaction_type'] === 'buy') {
            $total_investment += $tx['total_amount'] + $tx['transaction_fees'];
        } else {
            $total_investment -= ($tx['total_amount'] - $tx['transaction_fees']);
        }
    }

} catch (PDOException $e) {
    error_log("Transaction history error: " . $e->getMessage());
    $transactions = [];
    $total_investment = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History | Nova Capital</title>
    <link rel="icon" type="image/png" href="Bull-removebg-preview.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --green-deep:#003d30; --green-mid:#00694f; --green-bright:#00c896;
            --gold:#c9a84c; --off-white:#f4f1eb; --cream:#faf8f3;
            --text-dark:#1a1a1a; --text-muted:#6b7280;
            --white:#ffffff; --positive:#22c55e; --negative:#ef4444;
        }
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'DM Sans',sans-serif;background:var(--off-white);color:var(--text-dark);min-height:100vh;}

        /* HEADER */
        header{background:var(--green-deep);position:sticky;top:0;z-index:200;border-bottom:1px solid rgba(255,255,255,0.05);}
        .hdr{max-width:1280px;margin:0 auto;padding:0 40px;height:68px;display:flex;justify-content:space-between;align-items:center;}
        .logo-link{font-family:'Playfair Display',serif;font-size:19px;font-weight:700;color:var(--white);display:flex;align-items:center;gap:10px;text-decoration:none;}
        .logo-link i{color:var(--gold);font-size:16px;}
        .nav{display:flex;align-items:center;gap:2px;}
        .nav a{text-decoration:none;color:rgba(255,255,255,0.52);font-size:13.5px;font-weight:500;padding:8px 15px;border-radius:9px;transition:all 0.2s;}
        .nav a:hover{background:rgba(255,255,255,0.07);color:var(--white);}
        .nav a.active{background:rgba(0,200,150,0.13);color:var(--green-bright);}

        /* HERO */
        .hero{background:var(--green-deep);padding:44px 40px 36px;position:relative;overflow:hidden;}
        .hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 78% 50%,rgba(0,200,150,0.08),transparent 60%);pointer-events:none;}
        .hero-in{max-width:1280px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;position:relative;z-index:1;}
        .eyebrow{font-size:10px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--green-bright);margin-bottom:9px;}
        .hero-title{font-family:'Playfair Display',serif;font-size:28px;font-weight:700;color:var(--white);}
        .hero-sub{font-size:13.5px;color:rgba(255,255,255,0.38);margin-top:6px;font-weight:300;}
        .back-btn{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.65);padding:10px 20px;border-radius:10px;font-size:13px;font-weight:500;text-decoration:none;transition:all 0.2s;white-space:nowrap;}
        .back-btn:hover{background:rgba(255,255,255,0.13);color:var(--white);}

        /* MAIN */
        .wrap{max-width:1280px;margin:0 auto;padding:36px 40px 80px;}

        /* Stats row */
        .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:28px;}
        .scard{background:var(--white);border:1px solid rgba(0,0,0,0.06);border-radius:16px;padding:20px 22px;display:flex;align-items:center;gap:14px;transition:transform 0.22s,box-shadow 0.22s;}
        .scard:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,61,48,0.09);}
        .sicon{width:44px;height:44px;border-radius:13px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:16px;background:rgba(0,105,79,0.09);color:var(--green-mid);}
        .slabel{font-size:10.5px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;}
        .sval{font-family:'DM Mono',monospace;font-size:17px;font-weight:600;color:var(--text-dark);}
        .sval.up{color:var(--positive);} .sval.dn{color:var(--negative);}

        /* Card */
        .card{background:var(--white);border:1px solid rgba(0,0,0,0.06);border-radius:20px;overflow:hidden;box-shadow:0 2px 12px rgba(0,61,48,0.05);}
        .card-hd{padding:22px 28px;border-bottom:1px solid var(--off-white);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;}
        .card-title{font-family:'Playfair Display',serif;font-size:18px;font-weight:700;}

        /* Filter */
        .filter-wrap{display:flex;align-items:center;gap:8px;}
        .filter-wrap label{font-size:12px;color:var(--text-muted);font-weight:500;}
        .filter-sel{padding:8px 14px;border:1.5px solid rgba(0,0,0,0.1);border-radius:9px;font-family:'DM Sans',sans-serif;font-size:13px;color:var(--text-dark);background:var(--white);cursor:pointer;outline:none;transition:border-color 0.2s;appearance:none;padding-right:32px;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%236b7280' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;}
        .filter-sel:focus{border-color:var(--green-mid);}

        /* Table */
        .tbl-wrap{overflow-x:auto;}
        table{width:100%;border-collapse:collapse;}
        thead tr{background:var(--cream);}
        th{padding:13px 20px;text-align:left;font-size:10.5px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--text-muted);white-space:nowrap;}
        td{padding:16px 20px;border-bottom:1px solid var(--off-white);font-size:14px;vertical-align:middle;}
        tbody tr:last-child td{border-bottom:none;}
        tbody tr{transition:background 0.15s;}
        tbody tr:hover{background:var(--cream);}

        .pill{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:700;padding:5px 11px;border-radius:20px;white-space:nowrap;}
        .pill.buy {background:rgba(34,197,94,0.1);color:var(--positive);}
        .pill.sell{background:rgba(239,68,68,0.1);color:var(--negative);}
        .pill.completed{background:rgba(0,105,79,0.08);color:var(--green-mid);}

        .mono{font-family:'DM Mono',monospace;font-size:13px;}

        .co-cell{display:flex;flex-direction:column;gap:2px;}
        .co-sym{font-family:'DM Mono',monospace;font-size:13px;font-weight:600;}
        .co-nm {font-size:11.5px;color:var(--text-muted);}

        /* Empty state */
        .empty{text-align:center;padding:60px 20px;}
        .empty i{font-size:40px;color:rgba(0,105,79,0.15);margin-bottom:14px;display:block;}
        .empty p{color:var(--text-muted);font-size:15px;}

        /* Summary bar */
        .summary-bar{padding:18px 28px;background:var(--cream);border-top:1px solid var(--off-white);display:flex;justify-content:flex-end;align-items:center;gap:8px;}
        .summary-label{font-size:13px;color:var(--text-muted);font-weight:500;}
        .summary-val{font-family:'DM Mono',monospace;font-size:16px;font-weight:700;}
        .summary-val.pos{color:var(--positive);}
        .summary-val.neg{color:var(--negative);}

        /* FOOTER */
        footer{background:var(--green-deep);padding:50px 40px 28px;position:relative;overflow:hidden;}
        footer::before{content:'';position:absolute;bottom:-140px;right:-60px;width:380px;height:380px;border-radius:50%;background:radial-gradient(circle,rgba(0,200,150,0.06),transparent);pointer-events:none;}
        .fgrid{max-width:1280px;margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr;gap:60px;margin-bottom:40px;position:relative;z-index:1;}
        .fbrand{font-family:'Playfair Display',serif;font-size:19px;font-weight:700;color:var(--white);display:flex;align-items:center;gap:10px;margin-bottom:12px;}
        .fbrand i{color:var(--gold);}
        .fdesc{font-size:13px;color:rgba(255,255,255,0.32);line-height:1.85;max-width:260px;}
        .fch{font-size:10px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,0.28);margin-bottom:16px;}
        .fl{list-style:none;}
        .fl li{margin-bottom:10px;}
        .fl a{font-size:13px;color:rgba(255,255,255,0.45);text-decoration:none;transition:color 0.2s;}
        .fl a:hover{color:var(--white);}
        .fbot{max-width:1280px;margin:0 auto;position:relative;z-index:1;border-top:1px solid rgba(255,255,255,0.07);padding-top:24px;text-align:center;font-size:12px;color:rgba(255,255,255,0.22);}

        @media(max-width:1024px){.stats{grid-template-columns:repeat(2,1fr);}}
        @media(max-width:768px){
            .hdr,.hero,.wrap,footer{padding-left:20px;padding-right:20px;}
            .hero-in{flex-direction:column;align-items:flex-start;gap:18px;}
            .stats{grid-template-columns:1fr 1fr;}
            .fgrid{grid-template-columns:1fr;gap:28px;}
            th:nth-child(1),td:nth-child(1),th:nth-child(8),td:nth-child(8){display:none;}
        }
    </style>
</head>
<body>

<header>
    <div class="hdr">
        <a href="dash1.php" class="logo-link"><i class="fas fa-chart-line"></i> NOVA CAPITAL</a>
        <nav class="nav">
            <a href="dash1.php">Dashboard</a>
            <a href="main.html">Buy/Sell</a>
            <a href="stock-history.php" class="active">History</a>
            <a href="dashboard.html">News</a>
            <a href="Average.html">Averages</a>
        </nav>
    </div>
</header>

<div class="hero">
    <div class="hero-in">
        <div>
            <div class="eyebrow">Portfolio Activity</div>
            <div class="hero-title">Transaction History</div>
            <div class="hero-sub">A complete record of all your trades — for <?php echo htmlspecialchars($_SESSION['user'] ?? 'Investor'); ?></div>
        </div>
        <a href="dash1.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>

<main class="wrap">

    <?php
        $buys  = array_filter($transactions, fn($t) => $t['transaction_type'] === 'buy');
        $sells = array_filter($transactions, fn($t) => $t['transaction_type'] === 'sell');
        $total_spent    = array_sum(array_column(array_values($buys),  'total_amount'));
        $total_received = array_sum(array_column(array_values($sells), 'total_amount'));
    ?>

    <!-- Stats -->
    <div class="stats">
        <div class="scard">
            <div class="sicon"><i class="fas fa-list"></i></div>
            <div><div class="slabel">Total Trades</div><div class="sval"><?= count($transactions) ?></div></div>
        </div>
        <div class="scard">
            <div class="sicon" style="background:rgba(34,197,94,0.09);color:var(--positive)"><i class="fas fa-arrow-down"></i></div>
            <div><div class="slabel">Total Buys</div><div class="sval up"><?= count($buys) ?></div></div>
        </div>
        <div class="scard">
            <div class="sicon" style="background:rgba(239,68,68,0.09);color:var(--negative)"><i class="fas fa-arrow-up"></i></div>
            <div><div class="slabel">Total Sells</div><div class="sval dn"><?= count($sells) ?></div></div>
        </div>
        <div class="scard">
            <div class="sicon"><i class="fas fa-wallet"></i></div>
            <div>
                <div class="slabel">Net Investment</div>
                <div class="sval <?= $total_investment >= 0 ? 'dn' : 'up' ?>" style="font-size:14px">
                    <?= $total_investment >= 0 ? '-$' . number_format($total_investment,2) : '+$' . number_format(abs($total_investment),2) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-hd">
            <div class="card-title">All Transactions</div>
            <div class="filter-wrap">
                <label for="txFilter">Filter:</label>
                <select class="filter-sel" id="txFilter" onchange="filterTx(this.value)">
                    <option value="all">All Transactions</option>
                    <option value="buy">Buy Only</option>
                    <option value="sell">Sell Only</option>
                    <option value="completed">Completed Only</option>
                </select>
            </div>
        </div>

        <?php if (empty($transactions)): ?>
        <div class="empty">
            <i class="fas fa-receipt"></i>
            <p>No transactions found. Start trading to see your history here.</p>
        </div>
        <?php else: ?>
        <div class="tbl-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date &amp; Time</th>
                        <th>Type</th>
                        <th>Stock</th>
                        <th>Qty</th>
                        <th>Price/Share</th>
                        <th>Total</th>
                        <th>Fees</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx): ?>
                    <tr class="tx-row" data-type="<?= $tx['transaction_type'] ?>" data-status="<?= $tx['status'] ?>">
                        <td class="mono" style="color:var(--text-muted)"><?= $tx['id'] ?></td>
                        <td class="mono" style="font-size:12px"><?= date('M d, Y · H:i', strtotime($tx['transaction_date'])) ?></td>
                        <td>
                            <span class="pill <?= $tx['transaction_type'] ?>">
                                <i class="fas fa-arrow-<?= $tx['transaction_type'] === 'buy' ? 'down' : 'up' ?>"></i>
                                <?= ucfirst($tx['transaction_type']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="co-cell">
                                <div class="co-sym"><?= htmlspecialchars($tx['stock_symbol']) ?></div>
                                <div class="co-nm"><?= htmlspecialchars($tx['stock_name']) ?></div>
                            </div>
                        </td>
                        <td class="mono"><?= $tx['quantity'] ?></td>
                        <td class="mono">$<?= number_format($tx['price_per_share'], 2) ?></td>
                        <td class="mono" style="font-weight:600">$<?= number_format($tx['total_amount'], 2) ?></td>
                        <td class="mono" style="color:var(--text-muted)">$<?= number_format($tx['transaction_fees'], 2) ?></td>
                        <td><span class="pill completed"><?= htmlspecialchars($tx['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="summary-bar">
            <span class="summary-label">Net Investment:</span>
            <span class="summary-val <?= $total_investment >= 0 ? 'neg' : 'pos' ?>">
                <?= $total_investment >= 0
                    ? '-$' . number_format($total_investment, 2)
                    : '+$' . number_format(abs($total_investment), 2) ?>
            </span>
        </div>
        <?php endif; ?>
    </div>

</main>

<footer>
    <div class="fgrid">
        <div>
            <div class="fbrand"><i class="fas fa-chart-line"></i> NOVA CAPITAL</div>
            <p class="fdesc">Your trusted partner for stock market investments and portfolio management.</p>
        </div>
        <div>
            <div class="fch">Navigate</div>
            <ul class="fl">
                <li><a href="dash1.php">Dashboard</a></li>
                <li><a href="main.html">Buy / Sell</a></li>
                <li><a href="stock-history.php">Transaction History</a></li>
                <li><a href="Average.html">Market Averages</a></li>
            </ul>
        </div>
        <div>
            <div class="fch">Support</div>
            <ul class="fl">
                <li><a href="Feedback.html">Feedback</a></li>
                <li><a href="#">Help Center</a></li>
                <li><a href="#">Terms of Service</a></li>
            </ul>
        </div>
    </div>
    <div class="fbot">© 2025 Nova Capital. All rights reserved.</div>
</footer>

<script>
    function filterTx(val) {
        document.querySelectorAll('.tx-row').forEach(row => {
            const type   = row.dataset.type;
            const status = row.dataset.status;
            if (val === 'all')                     row.style.display = '';
            else if (val === 'buy')                row.style.display = type   === 'buy'       ? '' : 'none';
            else if (val === 'sell')               row.style.display = type   === 'sell'      ? '' : 'none';
            else if (val === 'completed')          row.style.display = status === 'completed' ? '' : 'none';
        });
    }
</script>
</body>
</html>