<?php
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_path', '/');
session_start();

require_once __DIR__ . '/core/tenant.php';

$token   = $_GET['token'] ?? '';
$error   = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token    = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        try {
            $db   = Tenant::db();
            $stmt = $db->prepare(
                "SELECT * FROM password_resets
                  WHERE token = ? AND used = 0 AND expires_at > NOW()
                  LIMIT 1"
            );
            $stmt->execute([$token]);
            $reset = $stmt->fetch();

            if (!$reset) {
                $error = 'This reset link has expired or already been used. Please request a new one.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $db->prepare("UPDATE staff SET password_hash = ? WHERE id = ?")
                    ->execute([$hash, $reset['staff_id']]);
                $db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?")
                    ->execute([$token]);
                $success = true;
            }
        } catch (PDOException $e) {
            error_log("Reset password error: " . $e->getMessage());
            $error = 'A system error occurred. Please try again.';
        }
    }
}

// Build the login URL dynamically from current host
$loginUrl = '/login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>Reset Password – OPTMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --ac:  #4a7c6f;
            --ac2: #5a9186;
            --ro:  #c0444f;
            --tx:  #2c2825;
            --tx2: #5a534c;
            --tx3: #8a8078;
            --bg:  #f0ede8;
            --sf:  #faf8f5;
            --sf2: #ede9e3;
            --br:  #d8d3cc;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            background-image:
                    radial-gradient(ellipse 70% 50% at 20% 20%, rgba(74,124,111,.08) 0%, transparent 60%),
                    radial-gradient(ellipse 60% 50% at 80% 80%, rgba(124,92,191,.06) 0%, transparent 60%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .card {
            background: var(--sf);
            border: 1px solid var(--br);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(60,50,40,.18);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
            animation: rise .4s cubic-bezier(.22,1,.36,1) both;
        }

        @keyframes rise {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Header ── */
        .head {
            padding: 28px 28px 22px;
            text-align: center;
            border-bottom: 1px solid var(--br);
            background: linear-gradient(135deg, rgba(74,124,111,.04), rgba(124,92,191,.03));
        }

        .ic {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, var(--ac), #7c5cbf);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            margin: 0 auto 14px;
            box-shadow: 0 4px 14px rgba(74,124,111,.28);
        }

        h1 {
            font-family: 'DM Serif Display', serif;
            font-size: 22px;
            color: var(--tx);
            margin-bottom: 4px;
        }

        .sub { font-size: 12px; color: var(--tx3); }

        /* ── Body ── */
        .body { padding: 26px 28px 28px; }

        /* ── Form ── */
        .fgi {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-bottom: 14px;
        }

        label {
            font-size: 11px;
            font-weight: 600;
            color: var(--tx2);
            letter-spacing: .3px;
        }

        .pw-wrap { position: relative; }

        .pw-wrap input { padding-right: 40px; }

        input {
            padding: 10px 13px;
            border: 1.5px solid var(--br);
            border-radius: 8px;
            background: var(--sf2);
            color: var(--tx);
            font-size: 13px;
            font-family: inherit;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            width: 100%;
        }

        input:focus {
            border-color: var(--ac);
            box-shadow: 0 0 0 3px rgba(74,124,111,.12);
        }

        input::placeholder { color: var(--tx3); }

        /* password toggle */
        .pw-toggle {
            position: absolute;
            right: 10px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer; color: var(--tx3);
            font-size: 15px; padding: 2px;
            transition: color .2s;
            line-height: 1;
        }
        .pw-toggle:hover { color: var(--tx2); }

        /* strength bar */
        .strength-wrap { margin-top: 6px; }
        .strength-bar {
            height: 3px;
            background: var(--br);
            border-radius: 2px;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            width: 0%;
            border-radius: 2px;
            transition: width .3s, background .3s;
        }
        .strength-label {
            font-size: 10px;
            color: var(--tx3);
            margin-top: 3px;
        }

        /* ── Button ── */
        .btn {
            width: 100%;
            padding: 12px;
            background: var(--ac);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            margin-top: 8px;
            transition: background .2s, transform .1s;
        }
        .btn:hover { background: var(--ac2); }
        .btn:active { transform: scale(.98); }

        /* ── Alerts ── */
        .err {
            background: rgba(192,68,79,.08);
            border: 1px solid rgba(192,68,79,.25);
            border-radius: 8px;
            padding: 10px 13px;
            color: var(--ro);
            font-size: 12.5px;
            margin-bottom: 16px;
            display: flex; gap: 8px; align-items: flex-start;
        }

        .ok {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 20px 16px;
            color: #166534;
            text-align: center;
            line-height: 1.6;
        }

        .ok .ok-icon { font-size: 28px; margin-bottom: 8px; }
        .ok strong { display: block; font-size: 15px; margin-bottom: 4px; }
        .ok span { font-size: 12.5px; opacity: .85; }

        /* ── Back link ── */
        .back {
            display: block;
            text-align: center;
            margin-top: 16px;
            font-size: 12.5px;
            color: var(--ac);
            text-decoration: none;
            font-weight: 500;
            transition: opacity .2s;
        }
        .back:hover { opacity: .7; }
    </style>
</head>
<body>
<div class="card">

    <div class="head">
        <div class="ic">🔑</div>
        <h1>Reset Password</h1>
        <div class="sub">Enter your new password below</div>
    </div>

    <div class="body">

        <?php if ($success): ?>
            <div class="ok">
                <div class="ok-icon">✅</div>
                <strong>Password updated successfully!</strong>
                <span>You can now log in with your new password.</span>
            </div>
            <a href="<?= $loginUrl ?>" class="back">← Back to Login</a>

        <?php elseif (!$token): ?>
            <div class="err">⚠ Invalid reset link. Please request a new one from the login page.</div>
            <a href="<?= $loginUrl ?>" class="back">← Back to Login</a>

        <?php else: ?>
            <?php if ($error): ?>
                <div class="err">⚠ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" id="resetForm">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="fgi">
                    <label for="password">New Password</label>
                    <div class="pw-wrap">
                        <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Min. 6 characters"
                                required
                                minlength="6"
                                autofocus
                                autocomplete="new-password"
                                oninput="checkStrength(this.value)"
                        >
                        <button type="button" class="pw-toggle" onclick="togglePw('password', this)">👁</button>
                    </div>
                    <div class="strength-wrap">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <div class="strength-label" id="strengthLabel"></div>
                    </div>
                </div>

                <div class="fgi">
                    <label for="confirm">Confirm Password</label>
                    <div class="pw-wrap">
                        <input
                                type="password"
                                id="confirm"
                                name="confirm"
                                placeholder="Repeat new password"
                                required
                                autocomplete="new-password"
                        >
                        <button type="button" class="pw-toggle" onclick="togglePw('confirm', this)">👁</button>
                    </div>
                </div>

                <button class="btn" type="submit">🔒 Set New Password</button>
            </form>
            <a href="<?= $loginUrl ?>" class="back">← Back to Login</a>
        <?php endif; ?>

    </div>
</div>

<script>
    function togglePw(id, btn) {
        const inp = document.getElementById(id);
        inp.type = inp.type === 'password' ? 'text' : 'password';
        btn.textContent = inp.type === 'password' ? '👁' : '🙈';
    }

    function checkStrength(val) {
        const fill  = document.getElementById('strengthFill');
        const label = document.getElementById('strengthLabel');
        if (!val) { fill.style.width = '0%'; label.textContent = ''; return; }

        let score = 0;
        if (val.length >= 6)  score++;
        if (val.length >= 10) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const levels = [
            { pct: '20%', color: '#e53e3e', text: 'Very weak'  },
            { pct: '40%', color: '#dd6b20', text: 'Weak'       },
            { pct: '60%', color: '#d69e2e', text: 'Fair'       },
            { pct: '80%', color: '#38a169', text: 'Strong'     },
            { pct:'100%', color: '#2f855a', text: 'Very strong'},
        ];
        const lv = levels[Math.min(score - 1, 4)] ?? levels[0];
        fill.style.width      = lv.pct;
        fill.style.background = lv.color;
        label.textContent     = lv.text;
        label.style.color     = lv.color;
    }

    // Client-side confirm match check
    document.getElementById('resetForm')?.addEventListener('submit', function(e) {
        const pw  = document.getElementById('password').value;
        const con = document.getElementById('confirm').value;
        if (pw !== con) {
            e.preventDefault();
            document.getElementById('confirm').style.borderColor = '#c0444f';
            document.getElementById('confirm').focus();
        }
    });
</script>
</body>
</html>