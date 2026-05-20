<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied — Craftistry Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --accent:    #667eea;
            --accent-2:  #764ba2;
            --page-bg:   #f8fafc;
            --white:     #ffffff;
            --border:    #e5e7eb;
            --text:      #1a202c;
            --text-soft: #6b7280;
            --red:       #ef4444;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--page-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07);
            padding: 48px 40px;
            text-align: center;
            max-width: 460px;
            width: 100%;
        }
        .icon-wrap {
            width: 72px; height: 72px; border-radius: 50%;
            background: rgba(239,68,68,0.08);
            border: 1.5px solid rgba(239,68,68,0.2);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
        }
        .icon-wrap i { font-size: 28px; color: var(--red); }
        .code {
            font-size: 12px; font-weight: 700; color: var(--red);
            letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 8px;
        }
        .divider {
            width: 36px; height: 3px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            border-radius: 99px; margin: 0 auto 16px;
        }
        h1 {
            font-size: 22px; font-weight: 800; color: var(--text);
            margin-bottom: 12px; line-height: 1.3;
        }
        p {
            font-size: 14px; color: var(--text-soft);
            line-height: 1.65; margin-bottom: 32px;
        }
        .actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
        .btn-primary {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: white; border: none; border-radius: 9px;
            font-size: 13.5px; font-weight: 700;
            text-decoration: none; transition: opacity 0.15s;
        }
        .btn-primary:hover { opacity: 0.88; }
        .btn-ghost {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 10px 20px;
            background: none; color: var(--text-soft);
            border: 1.5px solid var(--border); border-radius: 9px;
            font-size: 13.5px; font-weight: 600;
            text-decoration: none; transition: all 0.15s; cursor: pointer;
        }
        .btn-ghost:hover { border-color: var(--text-soft); color: var(--text); }
    </style>
</head>
<body>

<div class="card">
    <div class="icon-wrap">
        <i class="fas fa-lock"></i>
    </div>
    <p class="code">Error 403</p>
    <div class="divider"></div>
    <h1>Access Denied</h1>
    <p>
        You don't have permission to access this page.<br>
        Please contact the super admin if you believe this is a mistake.
    </p>
    <div class="actions">
        <a href="{{ route('admin.dashboard') }}" class="btn-primary">
            <i class="fas fa-th-large"></i> Go to Dashboard
        </a>
        <button onclick="history.back()" class="btn-ghost">
            <i class="fas fa-arrow-left"></i> Go Back
        </button>
    </div>
</div>

</body>
</html>