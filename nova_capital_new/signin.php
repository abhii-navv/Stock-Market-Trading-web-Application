<?php
// Start session only once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// FIX: Removed hardcoded $_SESSION['user_id'] = 1 — was bypassing login for everyone
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Sign In | Nova Capital</title>
    <link rel="icon" type="image/png" href="Bull-removebg-preview.png" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
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
            --error:        #dc2626;
            --error-bg:     #fef2f2;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--off-white);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 10% 20%, rgba(0,200,150,0.08) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 90% 80%, rgba(0,61,48,0.06) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        .page-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1000px;
            margin: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-radius: 24px;
            overflow: hidden;
            box-shadow:
                0 32px 64px rgba(0,61,48,0.18),
                0 0 0 1px rgba(0,200,150,0.1);
            animation: pageIn 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes pageIn {
            from { opacity: 0; transform: translateY(30px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0)    scale(1);    }
        }

        /* ── Left Panel ── */
        .left-panel {
            background: var(--green-deep);
            padding: 56px 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0,200,150,0.15), transparent 70%);
            top: -100px;
            right: -100px;
            pointer-events: none;
        }

        .left-panel::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(201,168,76,0.1), transparent 70%);
            bottom: -80px;
            left: -80px;
            pointer-events: none;
        }

        .brand { position: relative; z-index: 1; }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(0,200,150,0.25);
            border-radius: 100px;
            padding: 6px 14px;
            margin-bottom: 32px;
        }

        .brand-badge-dot {
            width: 7px;
            height: 7px;
            background: var(--green-bright);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.6; transform: scale(1.3); }
        }

        .brand-badge span {
            font-size: 11px;
            font-weight: 600;
            color: var(--green-bright);
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 38px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.1;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .brand-name span { color: var(--gold); }

        .brand-tagline {
            font-size: 14px;
            color: rgba(255,255,255,0.55);
            line-height: 1.7;
            font-weight: 300;
            max-width: 260px;
        }

        .left-bottom { position: relative; z-index: 1; }

        /* Market ticker */
        .market-list {
            list-style: none;
            margin-bottom: 28px;
        }

        .market-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 14px;
            border-radius: 10px;
            margin-bottom: 6px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
            transition: background 0.2s;
        }

        .market-item:hover {
            background: rgba(255,255,255,0.07);
        }

        .market-symbol {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,0.85);
            letter-spacing: 0.5px;
        }

        .market-price {
            font-size: 12px;
            color: rgba(255,255,255,0.45);
        }

        .market-change {
            font-size: 12px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
        }

        .market-change.up {
            color: #22c55e;
            background: rgba(34,197,94,0.12);
        }

        .market-change.down {
            color: #ef4444;
            background: rgba(239,68,68,0.12);
        }

        .secure-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(201,168,76,0.08);
            border: 1px solid rgba(201,168,76,0.2);
            border-radius: 12px;
            padding: 14px 16px;
        }

        .secure-badge-icon {
            font-size: 20px;
        }

        .secure-badge-text strong {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--gold-light);
            margin-bottom: 2px;
        }

        .secure-badge-text span {
            font-size: 11px;
            color: rgba(255,255,255,0.35);
        }

        /* ── Right Panel ── */
        .right-panel {
            background: #ffffff;
            padding: 52px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            margin-bottom: 32px;
        }

        .form-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
            letter-spacing: -0.3px;
        }

        .form-header p {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 300;
        }

        /* Success message */
        .success-message {
            background: #f0fdf4;
            border: 1px solid rgba(34,197,94,0.3);
            border-left: 3px solid #22c55e;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 13px;
            color: #166534;
            margin-bottom: 20px;
            display: <?php echo isset($_GET['registered']) ? 'block' : 'none'; ?>;
        }

        /* Error message */
        .error-message {
            background: var(--error-bg);
            border: 1px solid rgba(220,38,38,0.2);
            border-left: 3px solid var(--error);
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 13px;
            color: var(--error);
            margin-bottom: 20px;
            display: <?php echo isset($_GET['error']) ? 'block' : 'none'; ?>;
            animation: shakeIn 0.4s ease;
        }

        @keyframes shakeIn {
            0%, 100% { transform: translateX(0); }
            20%       { transform: translateX(-6px); }
            40%       { transform: translateX(6px); }
            60%       { transform: translateX(-4px); }
            80%       { transform: translateX(4px); }
        }

        /* Fields */
        .field-group { margin-bottom: 16px; }

        .field-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-dark);
            letter-spacing: 0.6px;
            text-transform: uppercase;
            margin-bottom: 7px;
        }

        .field-wrapper input {
            width: 100%;
            padding: 13px 16px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            color: var(--text-dark);
            background: #fafafa;
            outline: none;
            transition: all 0.2s ease;
        }

        .field-wrapper input:focus {
            border-color: var(--green-mid);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(0,105,79,0.08);
        }

        .field-wrapper input::placeholder {
            color: #c4c9d4;
            font-weight: 300;
        }

        /* Forgot password */
        .forgot-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
            margin-top: -8px;
        }

        .forgot-link {
            font-size: 12px;
            color: var(--green-mid);
            cursor: pointer;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: var(--green-deep);
            text-decoration: underline;
        }

        /* Submit button */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--green-deep);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            letter-spacing: 0.4px;
            cursor: pointer;
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
            margin-bottom: 16px;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--green-mid), var(--green-deep));
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .btn-submit:hover::before { opacity: 1; }

        .btn-submit span {
            position: relative;
            z-index: 1;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,61,48,0.3);
        }

        .btn-submit:active { transform: translateY(0); }

        /* Secondary buttons row */
        .btn-row {
            display: flex;
            gap: 10px;
            margin-bottom: 24px;
        }

        .btn-outline {
            flex: 1;
            padding: 11px;
            background: transparent;
            color: var(--green-mid);
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-outline:hover {
            border-color: var(--green-mid);
            background: rgba(0,105,79,0.04);
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .divider span {
            font-size: 12px;
            color: #9ca3af;
            white-space: nowrap;
        }

        /* Signup link */
        .signup-link {
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
        }

        .signup-link a {
            color: var(--green-mid);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .signup-link a:hover {
            color: var(--green-deep);
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 700px) {
            .page-wrapper {
                grid-template-columns: 1fr;
                margin: 0;
                border-radius: 0;
                min-height: 100vh;
            }
            .left-panel { padding: 40px 32px; }
            .market-list { display: none; }
            .right-panel { padding: 40px 32px; }
        }
    </style>
</head>
<body>
<div class="page-wrapper">

    <!-- ── Left Panel ── -->
    <div class="left-panel">
        <div class="brand">
            <div class="brand-badge">
                <div class="brand-badge-dot"></div>
                <span>Live Markets</span>
            </div>
            <div class="brand-name">Nova<br><span>Capital</span></div>
            <p class="brand-tagline">Monitor your portfolio and execute trades with real-time market intelligence.</p>
        </div>

        <div class="left-bottom">
            <ul class="market-list">
                <li class="market-item">
                    <span class="market-symbol">AAPL</span>
                    <span class="market-price">$182.63</span>
                    <span class="market-change up">+1.4%</span>
                </li>
                <li class="market-item">
                    <span class="market-symbol">MSFT</span>
                    <span class="market-price">$417.32</span>
                    <span class="market-change up">+0.8%</span>
                </li>
                <li class="market-item">
                    <span class="market-symbol">TSLA</span>
                    <span class="market-price">$176.95</span>
                    <span class="market-change down">-1.2%</span>
                </li>
                <li class="market-item">
                    <span class="market-symbol">GOOG</span>
                    <span class="market-price">$171.23</span>
                    <span class="market-change up">+2.1%</span>
                </li>
            </ul>

            <div class="secure-badge">
                <div class="secure-badge-icon">🔒</div>
                <div class="secure-badge-text">
                    <strong>256-bit SSL Encrypted</strong>
                    <span>Your data is safe and secure</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Right Panel ── -->
    <div class="right-panel">
        <div class="form-header">
            <h2>Welcome Back</h2>
            <p>Sign in to access your portfolio and market data.</p>
        </div>

        <!-- Success message after registration -->
        <div class="success-message">
            ✅ Account created successfully! Please sign in.
        </div>

        <!-- Error message -->
        <div class="error-message" id="errorMessage">
            <?php if (isset($_GET['error'])): ?>
                <?php echo htmlspecialchars($_GET['error']); ?>
            <?php endif; ?>
        </div>

        <form action="login.php" method="POST">

            <div class="field-group">
                <label class="field-label" for="user_identifier">Email or Username</label>
                <div class="field-wrapper">
                    <input
                        type="text"
                        id="user_identifier"
                        name="user_identifier"
                        placeholder="Enter your email or username"
                        required
                        autocomplete="username"
                        value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>"
                    />
                </div>
            </div>

            <div class="field-group">
                <label class="field-label" for="password">Password</label>
                <div class="field-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    />
                </div>
            </div>

            <div class="forgot-row">
                <a class="forgot-link" onclick="alert('Password reset will be implemented soon.')">Forgot Password?</a>
            </div>

            <button type="submit" class="btn-submit">
                <span>Sign In to Dashboard →</span>
            </button>

            <div class="btn-row">
                <button type="button" class="btn-outline" onclick="window.location.href='index.html'">← Home</button>
                <button type="button" class="btn-outline" onclick="window.location.href='signup.php'">Create Account</button>
            </div>

        </form>

        <div class="divider"><span>New to Nova Capital?</span></div>

        <div class="signup-link">
            <a href="signup.php">Create a free account — get $10,000 to start</a>
        </div>
    </div>

</div>
</body>
</html>