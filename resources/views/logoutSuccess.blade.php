<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out — Craftistry</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Inter', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.logout-container {
    width: 100%;
    max-width: 420px;
}

.logout-box {
    background: #fff;
    border-radius: 16px;
    padding: 44px 36px 36px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    text-align: center;
}

/* Icon */
.success-icon {
    width: 76px;
    height: 76px;
    background: linear-gradient(135deg, #10b981, #059669);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
    font-size: 1.8rem;
    color: #fff;
    box-shadow: 0 4px 16px rgba(16,185,129,.3);
}

h1 {
    font-size: 1.4rem;
    font-weight: 800;
    color: #1a1a2e;
    margin-bottom: 8px;
}

.desc {
    font-size: 0.9rem;
    color: #6b6b8a;
    line-height: 1.6;
    margin-bottom: 4px;
}

.tagline {
    font-size: 0.82rem;
    color: #a0a0b8;
    margin-bottom: 28px;
}

/* Divider */
.divider {
    height: 1px;
    background: #f0f0f5;
    margin-bottom: 24px;
}

/* Redirect hint */
.redirect-hint {
    font-size: 0.78rem;
    color: #a0a0b8;
    margin-bottom: 20px;
}

.redirect-hint strong { color: #667eea; font-weight: 600; }

/* Buttons */
.button-group {
    display: flex;
    gap: 10px;
}

.btn {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 11px 16px;
    border-radius: 8px;
    font-family: 'Inter', sans-serif;
    font-size: 0.88rem;
    font-weight: 700;
    text-decoration: none;
    transition: opacity .15s, transform .15s;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    box-shadow: 0 3px 10px rgba(102,126,234,.28);
}

.btn-primary:hover { opacity: .88; color: #fff; text-decoration: none; }

.btn-secondary {
    background: #f0f0f5;
    color: #6b6b8a;
    border: 1.5px solid #e0e0ee;
}

.btn-secondary:hover { background: #e8e6f5; border-color: #c4b5fd; color: #667eea; text-decoration: none; }

/* Brand */
.brand-mark {
    margin-top: 20px;
    text-align: center;
    font-size: 0.76rem;
    color: rgba(255,255,255,.55);
    font-weight: 500;
}

.brand-mark span { color: rgba(255,255,255,.85); font-weight: 700; }

@media (max-width: 480px) {
    .logout-box { padding: 32px 20px 28px; }
    .button-group { flex-direction: column; }
}

    </style>
</head>
<body>

    <div class="logout-container">
        <div class="logout-box">

            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>

            <h1>Logged Out Successfully</h1>
            <p class="desc">You have been signed out of your Craftistry account.</p>
            <p class="tagline">Thank you for visiting!</p>

            <div class="divider"></div>

            <p class="redirect-hint">
                Redirecting to home in <strong id="countdown">10</strong>s
            </p>

            <div class="button-group">
                <a href="{{ route('login') }}" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login Again
                </a>
                <a href="{{ route('welcome') }}" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Go to Home
                </a>
            </div>

        </div>

        <div class="brand-mark"><span>Craftistry</span> &nbsp;·&nbsp; The Marketplace for Art in Malaysia</div>
    </div>

    <script>
        let n = 10;
        const el = document.getElementById('countdown');
        const timer = setInterval(() => {
            n--;
            if (el) el.textContent = n;
            if (n <= 0) {
                clearInterval(timer);
                window.location.href = "{{ route('welcome') }}";
            }
        }, 1000);
    </script>

</body>
</html>