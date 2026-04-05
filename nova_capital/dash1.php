<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'nova_capital');
define('DB_USER', 'root');
define('DB_PASS', 'Abhi');

// Function to get dashboard data (only user initials now)
function getDashboardData($pdo, $user_id) {
    $data = [
        'userInitials' => 'JD' // Default initials
    ];
    
    return $data;
}

// Handle AJAX refresh request (simplified)
if (isset($_GET['refresh']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $user_id = $_SESSION['user_id'];
        $data = getDashboardData($pdo, $user_id);
        echo json_encode(['success' => true, ...$data]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Main page logic
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    $user_id = $_SESSION['user_id'];
    $data = getDashboardData($pdo, $user_id);
    extract($data);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $userInitials = "JD";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Nova Capital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        
        .stats-container {
            display: none; /* Hide the entire stats section */
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background: linear-gradient(to bottom right, var(--light), #e6f0fa);
            min-height: 100vh;
            display: flex;
            color: var(--dark);
            line-height: 1.6;
        }

        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, var(--primary-dark), var(--primary));
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.6s ease-out;
        }

        .loader {
            width: 120px;
            height: 120px;
            position: relative;
            perspective: 1000px;
        }

        .loader-circle {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 6px solid var(--accent);
            border-radius: 50%;
            animation: spin 2s cubic-bezier(0.4, 0, 0.2, 1) infinite;
            box-shadow: 0 0 20px rgba(255, 140, 0, 0.3);
        }

        .loader-inner {
            position: absolute;
            width: 70%;
            height: 70%;
            top: 15%;
            left: 15%;
            border: 4px solid var(--secondary);
            border-radius: 50%;
            animation: spin 1.5s cubic-bezier(0.4, 0, 0.2, 1) infinite reverse;
        }

        @keyframes spin {
            0% { transform: rotate(0deg) rotateX(0deg); }
            100% { transform: rotate(360deg) rotateX(360deg); }
        }

        .loading-logo {
            font-size: 32px;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
            display: flex;
            align-items: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .loading-logo i {
            color: var(--accent);
            margin-right: 12px;
            font-size: 36px;
        }

        .loading-text {
            color: var(--secondary);
            font-size: 20px;
            font-weight: 500;
            letter-spacing: 1px;
            animation: pulseText 1.5s infinite;
        }

        @keyframes pulseText {
            0%, 100% { opacity: 0.7; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.02); }
        }

        .sidebar {
            width: 260px;
            background: linear-gradient(to bottom, var(--primary-dark), var(--primary));
            color: var(--secondary);
            padding: 30px 0;
            height: 100vh;
            position: fixed;
            box-shadow: 2px 0 15px var(--shadow);
            transition: all 0.3s ease;
        }

        .sidebar-header {
            padding: 0 25px 25px;
            border-bottom: 1px solid var(--glass);
            margin-bottom: 50px;
        }

        .logo {
            font-size: 26px;
            font-weight: 700;
            color: var(--secondary);
            text-decoration: none;
            letter-spacing: 1.5px;
            display: flex;
            align-items: center;
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .logo i {
            color: var(--accent);
            margin-right: 12px;
            font-size: 30px;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 15px;
        }

        .nav-link {
            padding: 16px 25px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: var(--accent);
            left: -100%;
            top: 0;
            transition: all 0.3s ease;
            z-index: -1;
            opacity: 0.1;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--secondary);
            background: var(--primary-light);
        }

        .nav-link:hover::after {
            left: 0;
        }

        .nav-link i {
            margin-right: 12px;
            width: 24px;
            text-align: center;
        }

        .logout-button {
            position: absolute;
            bottom: 30px;
            width: 100%;
            padding: 0 25px;
        }

        .logout-link {
            padding: 12px 25px;
            color: var(--secondary);
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
        }

        .logout-link:hover {
            background: var(--primary-dark);
            transform: translateX(5px);
        }

        .main-wrapper {
            flex: 1;
            margin-left: 260px;
            padding: 40px;
            opacity: 0;
            transition: opacity 0.5s ease-in;
        }

        .page-header {
            background: var(--secondary);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px var(--shadow);
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-section h1 {
            color: var(--primary);
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-section p {
            color: var(--gray);
            font-size: 16px;
        }

        .user-section {
            display: flex;
            align-items: center;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 600;
            color: var(--secondary);
            box-shadow: 0 0 10px var(--shadow);
            transition: transform 0.3s ease;
        }

        .user-avatar:hover {
            transform: rotate(360deg);
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--secondary);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px var(--shadow);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 140, 0, 0.1) 0%, transparent 70%);
            transition: all 0.3s ease;
            opacity: 0;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px var(--shadow);
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            font-size: 20px;
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary);
            color: var(--secondary);
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.1);
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            transition: color 0.3s ease;
        }

        .stat-desc {
            color: var(--gray);
            font-size: 14px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
            margin: 0 20px;
        }

        .feature-card {
            background: var(--secondary);
            border-radius: 12px;
            padding: 35px;
            box-shadow: 0 4px 15px var(--shadow);
            transition: all 0.3s ease;
            border-left: 5px solid var(--primary);
            margin-bottom: 20px;
            cursor: pointer;
        }

        .feature-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 10px 30px var(--shadow);
        }

        .feature-icon {
            width: 65px;
            height: 65px;
            font-size: 24px;
            box-shadow: 0 4px 15px var(--shadow);
            transition: all 0.3s ease;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary);
            color: var(--secondary);
            border-radius: 50%;
        }

        .feature-card:hover .feature-icon {
            transform: rotate(360deg);
        }

        .feature-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--dark);
        }

        .feature-desc {
            color: var(--gray);
            margin-bottom: 25px;
            line-height: 1.7;
        }

        .feature-action {
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            box-shadow: 0 2px 10px var(--shadow);
            background: var(--primary);
            color: var(--secondary);
            display: inline-block;
            transition: all 0.3s ease;
        }

        .feature-action:hover {
            transform: translateY(-2px);
            background: var(--primary-light);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            border-radius: 12px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            margin: 15% auto;
            width: 350px;
            text-align: center;
        }

        .modal-icon {
            font-size: 60px;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }

        .modal-text {
            margin-bottom: 25px;
            font-size: 18px;
            color: var(--dark);
        }

        .modal-button {
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            box-shadow: 0 2px 10px var(--shadow);
            border: none;
            cursor: pointer;
            margin: 0 10px;
        }

        .confirm-button {
            background-color: var(--primary);
            color: white;
        }

        .confirm-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 109, 91, 0.3);
        }

        .cancel-button {
            background-color: #eee;
            color: var(--dark);
        }

        .cancel-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        :root {
            --primary: #006d5b;
            --primary-light: #009b88;
            --primary-dark: #004d40;
            --secondary: #ffffff;
            --accent: #ff8c00;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray: #64748b;
            --shadow: rgba(0, 0, 0, 0.1);
            --glass: rgba(255, 255, 255, 0.15);
        }
    </style>
</head>
<body>
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-logo">
            <i class="fas fa-chart-line"></i>
            NOVA CAPITAL
        </div>
        <div class="loader">
            <div class="loader-circle"></div>
            <div class="loader-inner"></div>
        </div>
        <div class="loading-text">Preparing Your Dashboard</div>
    </div>

    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="dash1.php" class="logo">
                <i class="fas fa-chart-line"></i>
                NOVA CAPITAL
            </a>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dash1.php" class="nav-link active">
                    <i class="fas fa-th-large"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="main.html" class="nav-link">
                    <i class="fas fa-exchange-alt"></i>
                    Trade
                </a>
            </li>
            <li class="nav-item">
                <a href="Average.html" class="nav-link">
                    <i class="fas fa-chart-bar"></i>
                    Market Analysis
                </a>
            </li>
            <li class="nav-item">
                <a href="stock-history.php" class="nav-link">
                    <i class="fas fa-history"></i>
                    Transaction History
                </a>
            </li>
            <li class="nav-item">
                <a href="dashboard.html" class="nav-link">
                    <i class="fas fa-newspaper"></i>
                    Market News
                </a>
            </li>
            <li class="nav-item"> 
                <a href="feedback.html" class="nav-link">
                    <i class="fas fa-cog"></i>
                    Feedback
                </a>
                </li>
        </ul>

        <div class="logout-button">
            <a href="#" class="logout-link" onclick="showLogoutConfirmation()" aria-label="Logout">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </aside>

    <div class="main-wrapper" id="mainContent">
        <div class="page-header">
            <div class="welcome-section">
                <h1>Welcome Investor</h1>
                <p>Track your portfolio performance and market trends</p>
            </div>
            <!-- <div class="user-section">
                <div class="user-avatar"><?php echo htmlspecialchars($userInitials); ?></div>
            </div> -->
        </div>

        <!-- Stats container removed -->
        
        <div class="features-grid">
            <div class="feature-card" onclick="navigateTo('Average.html')">
                <div class="feature-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3 class="feature-title">Market Analysis</h3>
                <p class="feature-desc">Analyze market trends and stock performance with advanced visualizations.</p>
                <span class="feature-action">View Analysis</span>
            </div>
            <div class="feature-card" onclick="navigateTo('main.html')">
                <div class="feature-icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h3 class="feature-title">Trade Center</h3>
                <p class="feature-desc">Execute secure trades with real-time market data and analytics.</p>
                <span class="feature-action">Start Trading</span>
            </div>
            <div class="feature-card" onclick="navigateTo('stock-history.php')">
                <div class="feature-icon">
                    <i class="fas fa-history"></i>
                </div>
                <h3 class="feature-title">Transaction History</h3>
                <p class="feature-desc">Review your trading history and portfolio performance metrics.</p>
                <span class="feature-action">View History</span>
            </div>
            <div class="feature-card" onclick="navigateTo('dashboard.html')">
                <div class="feature-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <h3 class="feature-title">Market News</h3>
                <p class="feature-desc">Stay informed with the latest financial news and market updates.</p>
                <span class="feature-action">Read News</span>
            </div>
        </div>
    </div>

    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <div class="modal-text">Ready to sign out?</div>
            <div class="modal-buttons">
                <button class="modal-button confirm-button" onclick="logout()" aria-label="Confirm Logout">Yes</button>
                <button class="modal-button cancel-button" onclick="closeModal()" aria-label="Cancel Logout">No</button>
            </div>
        </div>
    </div>

    <script>
        function navigateTo(page) {
            window.location.href = page;
        }

        function showLogoutConfirmation() {
            document.getElementById('logoutModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('logoutModal').style.display = 'none';
        }

        function logout() {
            window.location.href = 'signin.php';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('logoutModal');
            if (event.target == modal) {
                closeModal();
            }
       }

        // Debugging function to log messages
        function debugInfo(info) {
            console.log("Debug: " + info);
        }
        
        debugInfo("Page loading");
        
        function manageLoadingScreen(show) {
            debugInfo("Managing loading screen: " + (show ? "show" : "hide"));
            const loadingScreen = document.getElementById('loadingScreen');
            const mainContent = document.getElementById('mainContent');
            
            if (show) {
                loadingScreen.style.display = 'flex';
                loadingScreen.style.opacity = '1';
                mainContent.style.opacity = '0';
            } else {
                loadingScreen.style.opacity = '0';
                setTimeout(() => {
                    loadingScreen.style.display = 'none';
                    mainContent.style.opacity = '1';
                }, 600);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            debugInfo("DOM loaded");
            if (performance.navigation.type === 1 || !sessionStorage.getItem('dashboardLoaded')) {
                manageLoadingScreen(true);
                setTimeout(() => {
                    manageLoadingScreen(false);
                    sessionStorage.setItem('dashboardLoaded', 'true');
                }, 2000);
            } else {
                manageLoadingScreen(false);
            }
        });

        window.addEventListener('pageshow', function(event) {
            debugInfo("Page show event");
            if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
                manageLoadingScreen(false);
            }
        });

        function navigateTo(page) {
            debugInfo("Navigating to: " + page);
            manageLoadingScreen(true);
            document.querySelector('.loading-text').textContent = 'Loading...';
            setTimeout(() => {
                window.location.href = page;
            }, 600);
        }

        function showLogoutConfirmation() {
            debugInfo("Showing logout confirmation");
            document.getElementById('logoutModal').style.display = 'flex';
        }

        function closeModal() {
            debugInfo("Closing modal");
            document.getElementById('logoutModal').style.display = 'none';
        }

        function logout() {
            debugInfo("Logging out");
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'logout.php';
            document.body.appendChild(form);
            
            manageLoadingScreen(true);
            document.querySelector('.loading-text').textContent = 'Signing Out...';
            
            setTimeout(() => {
                form.submit();
            }, 1000);
        }

        window.onclick = function(event) {
            const modal = document.getElementById('logoutModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>