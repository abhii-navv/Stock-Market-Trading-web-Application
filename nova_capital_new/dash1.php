<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: signin.php');
    exit;
}
require_once 'config.php';
$user_id = $_SESSION['id'];

function getDashboardData($pdo, $user_id) {
    // Get balance and username
    $stmt = $pdo->prepare("SELECT username, balance FROM users WHERE id = :uid");
    $stmt->execute([':uid' => $user_id]);
    $user = $stmt->fetch();

    $username     = $user['username'] ?? 'Investor';
    $balance      = floatval($user['balance'] ?? 10000);
    $initials     = strtoupper(substr($username, 0, 2));

    // Get total trades
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM transactions WHERE user_id = :uid");
    $stmt->execute([':uid' => $user_id]);
    $totalTrades = intval($stmt->fetch()['total']);

    // Calculate portfolio return
    $stmt = $pdo->prepare("SELECT 
        SUM(CASE WHEN transaction_type='buy'  THEN total_amount + transaction_fees ELSE 0 END) -
        SUM(CASE WHEN transaction_type='sell' THEN total_amount - transaction_fees ELSE 0 END) as invested
        FROM transactions WHERE user_id = :uid");
    $stmt->execute([':uid' => $user_id]);
    $invested = floatval($stmt->fetch()['invested'] ?? 0);
    $startCapital = 10000.00;
    $portfolioReturn = $invested > 0
        ? round((($balance - $startCapital) / $startCapital) * 100, 1)
        : 0.0;

    return [
        'userInitials'    => $initials,
        'username'        => $username,
        'balance'         => $balance,
        'totalTrades'     => $totalTrades,
        'portfolioReturn' => $portfolioReturn,
    ];
}

if (isset($_GET['refresh']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    try {
        $data = getDashboardData($pdo, $user_id);
        echo json_encode(['success' => true,
            'balance'         => $data['balance'],
            'totalTrades'     => $data['totalTrades'],
            'portfolioReturn' => $data['portfolioReturn'],
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

try {
    $data = getDashboardData($pdo, $user_id);
    extract($data);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $userInitials = "JD";
}

$username = isset($username) ? htmlspecialchars($username) : (isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']) : 'Investor');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
        <link rel="icon" type="image/png" href="Bull-removebg-preview.png">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Nova Capital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --green-deep:   #003d30;
            --green-mid:    #00694f;
            --green-bright: #00c896;
            --gold:         #c9a84c;
            --gold-light:   #f0d080;
            --off-white:    #f4f1eb;
            --text-dark:    #1a1a1a;
            --text-muted:   #6b7280;
            --white:        #ffffff;
            --sidebar-w:    260px;
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--off-white);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
        }

        /* ══ LOADING SCREEN ══ */
        .loading-screen {
            position: fixed;
            inset: 0;
            background: var(--green-deep);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.6s ease;
        }

        .loading-screen::before {
            content: '';
            position: absolute;
            width: 600px; height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0,200,150,0.1), transparent 65%);
            top: -150px; right: -150px;
            pointer-events: none;
        }

        .loading-logo {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 36px;
            letter-spacing: 1px;
            position: relative;
            z-index: 1;
        }

        .loading-logo i { color: var(--gold); }

        .loader {
            width: 56px; height: 56px;
            position: relative;
            margin-bottom: 24px;
            z-index: 1;
        }

        .loader-ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 3px solid transparent;
        }

        .loader-ring:nth-child(1) {
            border-top-color: var(--green-bright);
            animation: spin 1s linear infinite;
        }

        .loader-ring:nth-child(2) {
            inset: 8px;
            border-top-color: var(--gold);
            animation: spin 1.5s linear infinite reverse;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .loading-text {
            font-size: 12px;
            font-weight: 500;
            color: rgba(255,255,255,0.35);
            letter-spacing: 2.5px;
            text-transform: uppercase;
            position: relative;
            z-index: 1;
            animation: fadePulse 1.5s infinite;
        }

        @keyframes fadePulse { 0%,100%{opacity:.35} 50%{opacity:1} }

        /* ══ SIDEBAR ══ */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--green-deep);
            height: 100vh;
            position: fixed;
            top:0; left:0;
            display: flex;
            flex-direction: column;
            z-index: 100;
            overflow: hidden;
        }

        .sidebar::after {
            content: '';
            position: absolute;
            width: 280px; height: 280px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0,200,150,0.07), transparent);
            bottom: -80px; right: -80px;
            pointer-events: none;
        }

        .sidebar-header {
            padding: 32px 24px 22px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }

        .sidebar-logo {
            font-family: 'Playfair Display', serif;
            font-size: 19px;
            font-weight: 700;
            color: var(--white);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: opacity 0.2s;
        }

        .sidebar-logo:hover { opacity: 0.8; }
        .sidebar-logo i { color: var(--gold); }

        .sidebar-section-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.22);
            padding: 22px 24px 8px;
        }

        .nav-menu { list-style:none; flex:1; padding: 0 12px; }
        .nav-item  { margin-bottom: 2px; }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            color: rgba(255,255,255,0.55);
            text-decoration: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            position: relative;
        }

        .nav-link i { width:18px; text-align:center; font-size:14px; flex-shrink:0; }

        .nav-link:hover { background: rgba(255,255,255,0.05); color: var(--white); }

        .nav-link.active {
            background: rgba(0,200,150,0.1);
            color: var(--green-bright);
            font-weight: 600;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0; top: 20%; bottom: 20%;
            width: 3px;
            background: var(--green-bright);
            border-radius: 0 3px 3px 0;
        }

        .sidebar-footer {
            padding: 14px 12px 28px;
            border-top: 1px solid rgba(255,255,255,0.07);
            position: relative;
            z-index: 1;
        }

        .logout-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            color: rgba(255,255,255,0.45);
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .logout-link:hover { background: rgba(239,68,68,0.1); color: #f87171; }

        /* ══ MAIN ══ */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            flex: 1;
            padding: 36px 40px;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        /* Top bar */
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .topbar-greeting {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .topbar-title {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--text-dark);
            letter-spacing: -0.3px;
        }

        .topbar-right { display:flex; align-items:center; gap:12px; }

        .topbar-chip {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--white);
            border: 1px solid rgba(0,0,0,0.07);
            border-radius: 10px;
            padding: 8px 14px;
            font-size: 13px;
            color: var(--text-muted);
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        }

        .topbar-chip i { color: var(--green-mid); font-size:12px; }

        .live-dot {
            width:7px; height:7px;
            background: #22c55e;
            border-radius: 50%;
            animation: blink 2s infinite;
        }

        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }

        .avatar {
            width:42px; height:42px;
            background: var(--green-deep);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Playfair Display', serif;
            font-size: 15px;
            font-weight: 700;
            color: var(--gold-light);
            cursor: pointer;
            transition: transform 0.2s;
        }

        .avatar:hover { transform: scale(1.06); }

        /* Stats strip */
        .stats-strip {
            display: grid;
            grid-template-columns: repeat(4,1fr);
            gap: 14px;
            margin-bottom: 32px;
        }

        .stat-pill {
            background: var(--white);
            border: 1px solid rgba(0,0,0,0.06);
            border-radius: 14px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all 0.25s;
        }

        .stat-pill:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,61,48,0.08);
        }

        .stat-pill-icon {
            width:42px; height:42px;
            border-radius: 12px;
            background: rgba(0,105,79,0.07);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: var(--green-mid);
            flex-shrink: 0;
        }

        .stat-pill-value {
            font-family: 'DM Mono', monospace;
            font-size: 17px;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 2px;
        }

        .stat-pill-label {
            font-size: 11px;
            color: var(--text-muted);
        }

        /* Feature cards */
        .section-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 14px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(2,1fr);
            gap: 18px;
        }

        .feature-card {
            background: var(--white);
            border: 1px solid rgba(0,0,0,0.06);
            border-radius: 18px;
            padding: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::after {
            content: '';
            position: absolute;
            bottom:0; left:0;
            width:100%; height:3px;
            background: linear-gradient(90deg, var(--green-mid), var(--green-bright));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .feature-card:hover::after { transform: scaleX(1); }

        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 40px rgba(0,61,48,0.1);
            border-color: rgba(0,105,79,0.1);
        }

        .feature-card.featured {
            background: var(--green-deep);
            border-color: transparent;
        }

        .feature-card.featured::after {
            background: linear-gradient(90deg, var(--gold), var(--gold-light));
        }

        .feature-card.featured .feature-icon  { background: rgba(0,200,150,0.12); color: var(--green-bright); }
        .feature-card.featured .feature-title { color: var(--white); }
        .feature-card.featured .feature-desc  { color: rgba(255,255,255,0.45); }
        .feature-card.featured .feature-cta   { background: var(--gold); color: var(--green-deep); }
        .feature-card.featured .feature-cta:hover { background: var(--gold-light); }

        .feature-icon {
            width:50px; height:50px;
            background: rgba(0,105,79,0.07);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            color: var(--green-mid);
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .feature-card:hover .feature-icon { background: var(--green-deep); color: var(--green-bright); transform: scale(1.06); }
        .feature-card.featured:hover .feature-icon { background: rgba(0,200,150,0.18); }

        .feature-title {
            font-family: 'Playfair Display', serif;
            font-size: 19px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .feature-desc {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.75;
            margin-bottom: 22px;
            font-weight: 300;
        }

        .feature-cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--off-white);
            color: var(--green-mid);
            padding: 9px 16px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .feature-cta:hover { background: var(--green-deep); color: var(--white); }

        /* ══ MODAL ══ */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(5px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.open { display: flex; }

        .modal-box {
            background: var(--white);
            border-radius: 20px;
            padding: 40px;
            width: 350px;
            text-align: center;
            box-shadow: 0 32px 64px rgba(0,0,0,0.16);
            animation: popIn 0.3s cubic-bezier(0.22,1,0.36,1);
        }

        @keyframes popIn {
            from { opacity:0; transform:scale(0.9) translateY(16px); }
            to   { opacity:1; transform:scale(1) translateY(0); }
        }

        .modal-icon-wrap {
            width:60px; height:60px;
            background: rgba(239,68,68,0.08);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-size: 22px;
            color: #ef4444;
        }

        .modal-title {
            font-family: 'Playfair Display', serif;
            font-size: 21px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .modal-sub {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 28px;
            font-weight: 300;
        }

        .modal-btns { display:flex; gap:10px; }

        .mbtn {
            flex:1;
            padding: 13px;
            border-radius: 11px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            transition: all 0.2s;
        }

        .mbtn-cancel  { background: var(--off-white); color: var(--text-dark); }
        .mbtn-cancel:hover { background: #e5e7eb; }
        .mbtn-confirm { background: #ef4444; color: var(--white); }
        .mbtn-confirm:hover { background: #dc2626; transform: translateY(-1px); }
    </style>
</head>
<body>

    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-logo"><i class="fas fa-chart-line"></i> NOVA CAPITAL</div>
        <div class="loader">
            <div class="loader-ring"></div>
            <div class="loader-ring"></div>
        </div>
        <div class="loading-text" id="loadingText">Preparing your dashboard</div>
    </div>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="dash1.php" class="sidebar-logo">
                <i class="fas fa-chart-line"></i> NOVA CAPITAL
            </a>
        </div>

        <div class="sidebar-section-label">Main Menu</div>

        <ul class="nav-menu">
            <li class="nav-item"><a href="dash1.php" class="nav-link active"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li class="nav-item"><a href="main.html" class="nav-link"><i class="fas fa-exchange-alt"></i> Trade</a></li>
            <li class="nav-item"><a href="Average.html" class="nav-link"><i class="fas fa-chart-bar"></i> Market Analysis</a></li>
            <li class="nav-item"><a href="stock-history.php" class="nav-link"><i class="fas fa-history"></i> Transaction History</a></li>
            <li class="nav-item"><a href="dashboard.html" class="nav-link"><i class="fas fa-newspaper"></i> Market News</a></li>
            <li class="nav-item"><a href="feedback.html" class="nav-link"><i class="fas fa-comment-alt"></i> Feedback</a></li>
        </ul>

        <div class="sidebar-footer">
            <a class="logout-link" onclick="showLogoutConfirmation()">
                <i class="fas fa-sign-out-alt"></i> Sign Out
            </a>
        </div>
    </aside>

    <!-- Main -->
    <div class="main-wrapper" id="mainContent">

        <div class="topbar">
            <div>
                <div class="topbar-greeting">Good day, <?= $username ?></div>
                <div class="topbar-title">Dashboard</div>
            </div>
            <div class="topbar-right">
                <div class="topbar-chip"><span class="live-dot"></span> Markets Open</div>
                <div class="topbar-chip"><i class="fas fa-calendar-alt"></i> <?= date('M d, Y') ?></div>
                <div class="avatar"><?= strtoupper(substr($username, 0, 2)) ?></div>
            </div>
        </div>

        <div class="stats-strip">
            <div class="stat-pill">
                <div class="stat-pill-icon"><i class="fas fa-wallet"></i></div>
                <div><div class="stat-pill-value balance-val">$<?= number_format($balance, 2) ?></div><div class="stat-pill-label">Portfolio Balance</div></div>
            </div>
            <div class="stat-pill">
                <div class="stat-pill-icon"><i class="fas fa-layer-group"></i></div>
                <div><div class="stat-pill-value">6</div><div class="stat-pill-label">Available Stocks</div></div>
            </div>
            <div class="stat-pill">
                <div class="stat-pill-icon"><i class="fas fa-exchange-alt"></i></div>
                <div><div class="stat-pill-value trades-val"><?= $totalTrades ?></div><div class="stat-pill-label">Total Trades</div></div>
            </div>
            <div class="stat-pill">
                <div class="stat-pill-icon"><i class="fas fa-chart-line"></i></div>
                <div><div class="stat-pill-value return-val"><?= ($portfolioReturn >= 0 ? '+' : '') . $portfolioReturn ?>%</div><div class="stat-pill-label">Portfolio Return</div></div>
            </div>
        </div>

        <div class="section-label">Quick Access</div>
        <div class="features-grid">

            <div class="feature-card featured" onclick="navigateTo('main.html')">
                <div class="feature-icon"><i class="fas fa-exchange-alt"></i></div>
                <div class="feature-title">Trade Center</div>
                <p class="feature-desc">Execute secure buy and sell orders with real-time market data across all 6 companies.</p>
                <span class="feature-cta">Start Trading <i class="fas fa-arrow-right"></i></span>
            </div>

            <div class="feature-card" onclick="navigateTo('Average.html')">
                <div class="feature-icon"><i class="fas fa-chart-bar"></i></div>
                <div class="feature-title">Market Analysis</div>
                <p class="feature-desc">Analyze market trends and stock performance with advanced data visualizations.</p>
                <span class="feature-cta">View Analysis <i class="fas fa-arrow-right"></i></span>
            </div>

            <div class="feature-card" onclick="navigateTo('stock-history.php')">
                <div class="feature-icon"><i class="fas fa-history"></i></div>
                <div class="feature-title">Transaction History</div>
                <p class="feature-desc">Review your complete trading history and track portfolio performance over time.</p>
                <span class="feature-cta">View History <i class="fas fa-arrow-right"></i></span>
            </div>

            <div class="feature-card" onclick="navigateTo('dashboard.html')">
                <div class="feature-icon"><i class="fas fa-newspaper"></i></div>
                <div class="feature-title">Market News</div>
                <p class="feature-desc">Stay informed with the latest financial news and real-time market updates.</p>
                <span class="feature-cta">Read News <i class="fas fa-arrow-right"></i></span>
            </div>

        </div>
    </div>

    <!-- Logout Modal -->
    <div id="logoutModal" class="modal">
        <div class="modal-box">
            <div class="modal-icon-wrap"><i class="fas fa-sign-out-alt"></i></div>
            <div class="modal-title">Sign Out?</div>
            <div class="modal-sub">You'll need to sign in again to access your portfolio.</div>
            <div class="modal-btns">
                <button class="mbtn mbtn-cancel" onclick="closeModal()">Cancel</button>
                <button class="mbtn mbtn-confirm" onclick="logout()">Sign Out</button>
            </div>
        </div>
    </div>

    <script>
        function manageLoadingScreen(show) {
            const ls = document.getElementById('loadingScreen');
            const mc = document.getElementById('mainContent');
            if (show) {
                ls.style.display = 'flex'; ls.style.opacity = '1'; mc.style.opacity = '0';
            } else {
                ls.style.opacity = '0';
                setTimeout(() => { ls.style.display = 'none'; mc.style.opacity = '1'; }, 600);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (performance.navigation.type === 1 || !sessionStorage.getItem('dashboardLoaded')) {
                manageLoadingScreen(true);
                setTimeout(() => { manageLoadingScreen(false); sessionStorage.setItem('dashboardLoaded','true'); }, 2000);
            } else {
                manageLoadingScreen(false);
            }
        });

        window.addEventListener('pageshow', e => {
            if (e.persisted || (window.performance && window.performance.navigation.type === 2)) manageLoadingScreen(false);
        });

        function navigateTo(page) {
            manageLoadingScreen(true);
            document.getElementById('loadingText').textContent = 'Loading...';
            setTimeout(() => window.location.href = page, 600);
        }

        function showLogoutConfirmation() { document.getElementById('logoutModal').classList.add('open'); }
        function closeModal()             { document.getElementById('logoutModal').classList.remove('open'); }

        function logout() {
            const form = document.createElement('form');
            form.method = 'POST'; form.action = 'logout.php';
            document.body.appendChild(form);
            manageLoadingScreen(true);
            document.getElementById('loadingText').textContent = 'Signing out...';
            setTimeout(() => form.submit(), 1000);
        }

        window.addEventListener('click', e => {
            if (e.target === document.getElementById('logoutModal')) closeModal();
        });

        // ── REAL-TIME DASHBOARD POLLING ──
        async function refreshDashboardStats() {
            try {
                const res = await fetch('dash1.php?refresh=1');
                const data = await res.json();
                if (!data.success) return;

                // Balance
                const balEl = document.querySelector('.stat-pill-value.balance-val');
                if (balEl) balEl.textContent = '$' + parseFloat(data.balance).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});

                // Total trades
                const tradeEl = document.querySelector('.stat-pill-value.trades-val');
                if (tradeEl) tradeEl.textContent = data.totalTrades;

                // Portfolio return
                const retEl = document.querySelector('.stat-pill-value.return-val');
                if (retEl) retEl.textContent = (data.portfolioReturn >= 0 ? '+' : '') + data.portfolioReturn + '%';

            } catch(e) { console.error('Dashboard refresh error:', e); }
        }

        // Poll every 5 seconds
        setInterval(refreshDashboardStats, 5000);
    </script>
</body>
</html>