<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Nova Capital</title>
    <link rel="icon" type="image/png" href="Bull-removebg-preview.png">
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

        /* ── Animated background ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 10% 20%, rgba(0, 200, 150, 0.08) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 90% 80%, rgba(0, 61, 48, 0.06) 0%, transparent 60%);
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
                0 32px 64px rgba(0, 61, 48, 0.18),
                0 0 0 1px rgba(0, 200, 150, 0.1);
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

        .brand {
            position: relative;
            z-index: 1;
        }

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

        .brand-name span {
            color: var(--gold);
        }

        .brand-tagline {
            font-size: 14px;
            color: rgba(255,255,255,0.55);
            line-height: 1.7;
            font-weight: 300;
            max-width: 260px;
        }

        .left-bottom {
            position: relative;
            z-index: 1;
        }

        .stats-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 32px;
        }

        .stat-box {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            padding: 16px;
        }

        .stat-value {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: var(--gold-light);
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 11px;
            color: rgba(255,255,255,0.4);
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .bonus-banner {
            background: linear-gradient(135deg, rgba(201,168,76,0.15), rgba(201,168,76,0.05));
            border: 1px solid rgba(201,168,76,0.3);
            border-radius: 14px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .bonus-icon {
            width: 40px;
            height: 40px;
            background: rgba(201,168,76,0.15);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .bonus-text strong {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--gold-light);
            margin-bottom: 2px;
        }

        .bonus-text span {
            font-size: 11px;
            color: rgba(255,255,255,0.4);
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

        /* ── Error Message ── */
        .error-message {
            background: var(--error-bg);
            border: 1px solid rgba(220, 38, 38, 0.2);
            border-left: 3px solid var(--error);
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 13px;
            color: var(--error);
            margin-bottom: 20px;
            display: <?php echo isset($_GET['password_error']) || isset($_GET['error']) ? 'block' : 'none'; ?>;
            animation: shakeIn 0.4s ease;
        }

        @keyframes shakeIn {
            0%, 100% { transform: translateX(0); }
            20%       { transform: translateX(-6px); }
            40%       { transform: translateX(6px); }
            60%       { transform: translateX(-4px); }
            80%       { transform: translateX(4px); }
        }

        /* ── Form Fields ── */
        .field-group {
            margin-bottom: 16px;
        }

        .field-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-dark);
            letter-spacing: 0.6px;
            text-transform: uppercase;
            margin-bottom: 7px;
        }

        .field-wrapper {
            position: relative;
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
            box-shadow: 0 0 0 3px rgba(0, 105, 79, 0.08);
        }

        .field-wrapper input::placeholder {
            color: #c4c9d4;
            font-weight: 300;
        }

        .password-message {
            font-size: 12px;
            color: var(--error);
            margin-top: 6px;
            white-space: pre-line;
            line-height: 1.5;
        }

        /* ── Checkbox ── */
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 20px;
            margin-top: 4px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--green-mid);
            flex-shrink: 0;
            margin-top: 2px;
            cursor: pointer;
        }

        .checkbox-group label {
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.5;
            cursor: pointer;
        }

        .checkbox-group label a {
            color: var(--green-mid);
            text-decoration: none;
            font-weight: 500;
        }

        /* ── Submit Button ── */
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
            margin-bottom: 20px;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--green-mid), var(--green-deep));
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .btn-submit:hover::before {
            opacity: 1;
        }

        .btn-submit span {
            position: relative;
            z-index: 1;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 61, 48, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* ── Divider ── */
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

        /* ── Sign In Link ── */
        .signin-link {
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
        }

        .signin-link a {
            color: var(--green-mid);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .signin-link a:hover {
            color: var(--green-deep);
            text-decoration: underline;
        }

        /* ── Responsive ── */
        @media (max-width: 700px) {
            .page-wrapper {
                grid-template-columns: 1fr;
                margin: 0;
                border-radius: 0;
                min-height: 100vh;
            }
            .left-panel {
                padding: 40px 32px;
            }
            .stats-row { display: none; }
            .right-panel {
                padding: 40px 32px;
            }
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
            <p class="brand-tagline">Your gateway to intelligent stock portfolio management and real-time market insights.</p>
        </div>

        <div class="left-bottom">
            <div class="stats-row">
                <div class="stat-box">
                    <div class="stat-value">6</div>
                    <div class="stat-label">Companies</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">$10K</div>
                    <div class="stat-label">Start Capital</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">Live</div>
                    <div class="stat-label">Market Data</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">Free</div>
                    <div class="stat-label">To Join</div>
                </div>
            </div>

            <div class="bonus-banner">
                <div class="bonus-icon">💰</div>
                <div class="bonus-text">
                    <strong>$10,000 Welcome Bonus</strong>
                    <span>Virtual capital credited on signup</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Right Panel ── -->
    <div class="right-panel">
        <div class="form-header">
            <h2>Create Account</h2>
            <p>Start your investment journey today — it's free.</p>
        </div>

        <!-- Error Message -->
        <div class="error-message" id="errorMessage">
            <?php if (isset($_GET['password_error'])): ?>
                Password must include: uppercase, lowercase, number, special character and be at least 8 characters.
            <?php elseif (isset($_GET['error'])): ?>
                <?php
                    $errors = [
                        'missing_fields'      => 'Please fill in all required fields.',
                        'invalid_email'       => 'Please enter a valid email address.',
                        'invalid_username'    => 'Username must be between 3 and 50 characters.',
                        'weak_password'       => 'Password does not meet the required criteria.',
                        'username_exists'     => 'That username is already taken. Please choose another.',
                        'registration_failed' => 'Registration failed. Please try again.',
                        'server_error'        => 'A server error occurred. Please try again later.',
                    ];
                    $code = $_GET['error'];
                    echo htmlspecialchars($errors[$code] ?? 'An unexpected error occurred.');
                ?>
            <?php endif; ?>
        </div>

        <form action="registration.php" method="POST" id="signupForm">

            <div class="field-group">
                <label class="field-label" for="name">Full Name</label>
                <div class="field-wrapper">
                    <input type="text" id="name" name="name" placeholder="John Doe" required autocomplete="name">
                </div>
            </div>

            <div class="field-group">
                <label class="field-label" for="email">Email Address</label>
                <div class="field-wrapper">
                    <input type="email" id="email" name="email" placeholder="john@example.com" required autocomplete="email">
                </div>
            </div>

            <div class="field-group">
                <label class="field-label" for="password">Password</label>
                <div class="field-wrapper">
                    <input type="password" id="password" name="password" placeholder="Min 8 chars, include A-z, 0-9, @$!%*?&" required>
                </div>
                <div class="password-message" id="passwordMessage"></div>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="agree" name="agree" required>
                <label for="agree">I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a></label>
            </div>

            <button type="submit" class="btn-submit">
                <span>Create My Account →</span>
            </button>
        </form>

        <div class="divider"><span>Already a member?</span></div>

        <div class="signin-link">
            <a href="signin.php">Sign in to your account</a>
        </div>
    </div>

</div>

<script>
    document.getElementById('signupForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const name            = document.querySelector('#name').value.trim();
        const email           = document.querySelector('#email').value.trim();
        const password        = document.querySelector('#password').value;
        const agree           = document.querySelector('#agree').checked;
        const passwordMessage = document.getElementById('passwordMessage');
        const errorMessage    = document.getElementById('errorMessage');

        // Reset
        passwordMessage.textContent = '';
        errorMessage.style.display  = 'none';

        if (!name || !email || !password || !agree) {
            errorMessage.textContent = 'Please fill in all fields and agree to the terms.';
            errorMessage.style.display = 'block';
            return;
        }

        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            errorMessage.textContent = 'Please enter a valid email address.';
            errorMessage.style.display = 'block';
            return;
        }

        const passwordCriteria = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!passwordCriteria.test(password)) {
            let message = 'Password must include:';
            if (!/(?=.*[a-z])/.test(password))      message += '\n• At least one lowercase letter';
            if (!/(?=.*[A-Z])/.test(password))      message += '\n• At least one uppercase letter';
            if (!/(?=.*\d)/.test(password))          message += '\n• At least one number';
            if (!/(?=.*[@$!%*?&])/.test(password))  message += '\n• At least one special character (@$!%*?&)';
            if (password.length < 8)                 message += '\n• Minimum 8 characters';
            passwordMessage.textContent = message;
            return;
        }

        this.submit();
    });
</script>
</body>
</html>