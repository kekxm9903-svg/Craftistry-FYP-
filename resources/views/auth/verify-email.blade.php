<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email – Craftistry</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 420px;
        }

        .form-card {
            background: white;
            border-radius: 16px;
            padding: 40px 32px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .logo {
            display: block;
            height: 36px;
            margin: 0 auto 24px;
        }

        h1 {
            font-size: 28px;
            font-weight: 600;
            color: #1a202c;
            text-align: center;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: #718096;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .email-badge {
            display: block;
            text-align: center;
            background: #f3f0ff;
            color: #764ba2;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 16px;
            border-radius: 8px;
            margin-bottom: 28px;
            word-break: break-all;
            border: 2px solid #e9d5ff;
        }

        .icon-wrap {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .icon-wrap i {
            font-size: 2rem;
            color: #fff;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alert-warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
        }

        .alert-icon {
            flex-shrink: 0;
            font-size: 16px;
            margin-top: 1px;
        }

        .alert-content {
            flex: 1;
            font-size: 14px;
            line-height: 1.5;
        }

        .btn-primary {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: 'Inter', sans-serif;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            width: 100%;
            background: transparent;
            color: #4a5568;
            border: 2px solid #e2e8f0;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: border-color 0.2s, color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: 'Inter', sans-serif;
        }

        .btn-secondary:hover {
            border-color: #a0aec0;
            color: #1a202c;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #cbd5e0;
            font-size: 13px;
            margin: 16px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .help-text {
            text-align: center;
            font-size: 13px;
            color: #a0aec0;
            margin-top: 20px;
            line-height: 1.6;
        }

        @media (max-width: 480px) {
            .form-card { padding: 32px 24px; }
            h1 { font-size: 24px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-card">

            <img src="{{ asset('images/Logo.png') }}" alt="Craftistry" class="logo">

            <div class="icon-wrap">
                <i class="bi bi-envelope-check"></i>
            </div>

            <h1>Verify your email</h1>
            <p class="subtitle">We sent a verification link to</p>
            <span class="email-badge">{{ auth()->user()->email }}</span>

            @if (session('status') === 'verification-link-sent')
                <div class="alert alert-success">
                    <span class="alert-icon"><i class="bi bi-check-circle-fill"></i></span>
                    <div class="alert-content">A new verification link has been sent to your email.</div>
                </div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning">
                    <span class="alert-icon"><i class="bi bi-exclamation-circle-fill"></i></span>
                    <div class="alert-content">{{ session('warning') }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn-primary">
                    <i class="bi bi-send"></i>
                    Resend Verification Email
                </button>
            </form>

            <div class="divider">or</div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-secondary">
                    <i class="bi bi-box-arrow-left"></i>
                    Log out and use another account
                </button>
            </form>

            <p class="help-text">
                Can't find the email? Check your spam folder.<br>
                The link expires after 60 minutes.
            </p>

        </div>
    </div>
</body>
</html>