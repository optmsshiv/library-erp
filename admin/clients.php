<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Simple admin protection - change this password
define('ADMIN_PASS', 'Admin@123');

session_start();

// Login check
if (isset($_POST['login_pass'])) {
    if ($_POST['login_pass'] === ADMIN_PASS) {
        $_SESSION['erp_admin'] = true;
    } else {
        $loginError = "Wrong password";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: clients.php");
    exit;
}

// Database connection
require_once '/home1/edrppymy/public_html/library-erp/config/db.php';

try {
    $dsn = "mysql:host=" . CONFIG_DB_HOST . ";dbname=" . CONFIG_DB_NAME . ";charset=utf8mb4";
    $db  = new PDO($dsn, CONFIG_DB_USER, CONFIG_DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Config DB connection failed. Check config/db.php");
}

$success = '';
$error   = '';

if (isset($_SESSION['erp_admin']) && $_SESSION['erp_admin'] === true) {

    // ── ADD NEW CLIENT ──
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $subdomain   = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($_POST['subdomain'])));
        $db_name     = trim($_POST['db_name']);
        $db_user     = trim($_POST['db_user']);
        $db_pass     = trim($_POST['db_pass']);
        $client_name = trim($_POST['client_name']);
        $plan        = trim($_POST['plan']);
        $active      = isset($_POST['active']) ? 1 : 0;

        if (empty($subdomain) || empty($db_name) || empty($db_user) || empty($db_pass) || empty($client_name)) {
            $error = "All fields are required.";
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO subdomain_map 
                    (subdomain, db_name, db_user, db_pass, client_name, plan, active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$subdomain, $db_name, $db_user, $db_pass, $client_name, $plan, $active]);
                $success = "Client <strong>{$client_name}</strong> added successfully. Subdomain: <strong>{$subdomain}.optms.co.in</strong>";
            } catch (PDOException $e) {
                $error = "Subdomain <strong>{$subdomain}</strong> already exists. Use a different subdomain.";
            }
        }
    }

    // ── EDIT CLIENT ──
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id          = (int)$_POST['id'];
        $client_name = trim($_POST['client_name']);
        $db_name     = trim($_POST['db_name']);
        $db_user     = trim($_POST['db_user']);
        $db_pass     = trim($_POST['db_pass']);
        $plan        = trim($_POST['plan']);

        try {
            $stmt = $db->prepare("UPDATE subdomain_map 
                SET client_name=?, db_name=?, db_user=?, db_pass=?, plan=?
                WHERE id=?");
            $stmt->execute([$client_name, $db_name, $db_user, $db_pass, $plan, $id]);
            $success = "Client updated successfully.";
        } catch (PDOException $e) {
            $error = "Update failed: " . $e->getMessage();
        }
    }

    // ── TOGGLE ACTIVE/INACTIVE ──
    if (isset($_GET['toggle'])) {
        $id      = (int)$_GET['toggle'];
        $current = (int)$_GET['current'];
        $newVal  = $current === 1 ? 0 : 1;
        $db->prepare("UPDATE subdomain_map SET active=? WHERE id=?")->execute([$newVal, $id]);
        header("Location: clients.php");
        exit;
    }

    // ── DELETE CLIENT ──
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        $db->prepare("DELETE FROM subdomain_map WHERE id=?")->execute([$id]);
        $success = "Client deleted successfully.";
    }

    // ── FETCH ALL CLIENTS ──
    $clients = $db->query("SELECT * FROM subdomain_map ORDER BY created_at DESC")->fetchAll();

    // ── FETCH SINGLE CLIENT FOR EDIT ──
    $editClient = null;
    if (isset($_GET['edit'])) {
        $id         = (int)$_GET['edit'];
        $stmt       = $db->prepare("SELECT * FROM subdomain_map WHERE id=?");
        $stmt->execute([$id]);
        $editClient = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library ERP — Client Manager</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; background: #f0f2f5; color: #333; min-height: 100vh; }

        /* ── NAVBAR ── */
        .navbar {
            background: #1a73e8;
            color: white;
            padding: 14px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 { font-size: 17px; font-weight: 500; }
        .navbar a  { color: white; font-size: 13px; text-decoration: none; opacity: 0.85; }
        .navbar a:hover { opacity: 1; }

        /* ── LOGIN ── */
        .login-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
        }
        .login-box {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 36px 40px;
            width: 100%;
            max-width: 380px;
            text-align: center;
        }
        .login-box h2 { font-size: 20px; font-weight: 500; margin-bottom: 6px; }
        .login-box p  { font-size: 13px; color: #888; margin-bottom: 24px; }

        /* ── LAYOUT ── */
        .wrap { max-width: 1100px; margin: 28px auto; padding: 0 20px; }
        .grid { display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start; }

        /* ── CARDS ── */
        .card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .card h2 {
            font-size: 15px;
            font-weight: 500;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f0f0f0;
            color: #333;
        }

        /* ── FORM ── */
        .field { margin-bottom: 14px; }
        .field label {
            display: block;
            font-size: 13px;
            color: #555;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .field input[type=text],
        .field input[type=password],
        .field select {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            color: #333;
            background: #fafafa;
            outline: none;
            transition: border 0.2s;
        }
        .field input:focus,
        .field select:focus {
            border-color: #1a73e8;
            background: white;
        }
        .field small {
            display: block;
            font-size: 11px;
            color: #999;
            margin-top: 3px;
        }
        .toggle-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
        }
        .toggle-row label { font-size: 13px; color: #555; font-weight: 500; }
        input[type=checkbox] { width: 16px; height: 16px; cursor: pointer; }

        /* ── BUTTONS ── */
        .btn {
            display: inline-block;
            padding: 9px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .btn:hover { opacity: 0.88; }
        .btn-blue  { background: #1a73e8; color: white; }
        .btn-green { background: #34a853; color: white; }
        .btn-red   { background: #ea4335; color: white; font-size: 12px; padding: 5px 12px; }
        .btn-gray  { background: #f1f3f4; color: #555; font-size: 12px; padding: 5px 12px; border: 1px solid #ddd; }
        .btn-amber { background: #fbbc04; color: #333; font-size: 12px; padding: 5px 12px; }
        .btn-full  { width: 100%; text-align: center; }

        /* ── TABLE ── */
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th {
            text-align: left;
            padding: 10px 12px;
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            font-weight: 500;
            color: #555;
            white-space: nowrap;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #f5f5f5;
            vertical-align: middle;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fafafa; }

        /* ── BADGES ── */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }
        .badge-green  { background: #e6f4ea; color: #1e7e34; }
        .badge-red    { background: #fce8e6; color: #c5221f; }
        .badge-blue   { background: #e8f0fe; color: #1a73e8; }
        .badge-amber  { background: #fef9e7; color: #b7770d; }
        .badge-purple { background: #f3e8fd; color: #7b1fa2; }

        /* ── ALERTS ── */
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .alert-success { background: #e6f4ea; color: #1e7e34; border: 1px solid #c3e6cb; }
        .alert-error   { background: #fce8e6; color: #c5221f; border: 1px solid #f5c6cb; }

        /* ── STATS ── */
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
        .stat  {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 16px 20px;
        }
        .stat .label { font-size: 12px; color: #888; margin-bottom: 4px; }
        .stat .value { font-size: 26px; font-weight: 500; color: #333; }

        /* ── ACTIONS ── */
        .actions { display: flex; gap: 6px; flex-wrap: wrap; }

        @media (max-width: 768px) {
            .grid  { grid-template-columns: 1fr; }
            .stats { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<div class="navbar">
    <h1>Library ERP — Client Manager</h1>
    <?php if (isset($_SESSION['erp_admin'])): ?>
        <a href="?logout=1">Logout</a>
    <?php endif; ?>
</div>

<?php if (!isset($_SESSION['erp_admin']) || $_SESSION['erp_admin'] !== true): ?>

    <!-- ══════════════════════════════════════ -->
    <!-- LOGIN SCREEN                           -->
    <!-- ══════════════════════════════════════ -->
    <div class="login-wrap">
        <div class="login-box">
            <h2>Admin Login</h2>
            <p>Library ERP Client Manager</p>

            <?php if (isset($loginError)): ?>
                <div class="alert alert-error"><?php echo $loginError; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label>Admin Password</label>
                    <input type="password" name="login_pass" placeholder="Enter password" required autofocus>
                </div>
                <button type="submit" class="btn btn-blue btn-full" style="margin-top:8px;">
                    Login
                </button>
            </form>
        </div>
    </div>

<?php else: ?>

    <!-- ══════════════════════════════════════ -->
    <!-- MAIN ADMIN PANEL                       -->
    <!-- ══════════════════════════════════════ -->
    <div class="wrap">

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Stats Row -->
        <?php
        $total    = count($clients);
        $active   = count(array_filter($clients, fn($c) => $c['active'] == 1));
        $inactive = $total - $active;
        ?>
        <div class="stats">
            <div class="stat">
                <div class="label">Total Clients</div>
                <div class="value"><?php echo $total; ?></div>
            </div>
            <div class="stat">
                <div class="label">Active</div>
                <div class="value" style="color:#34a853;"><?php echo $active; ?></div>
            </div>
            <div class="stat">
                <div class="label">Inactive</div>
                <div class="value" style="color:#ea4335;"><?php echo $inactive; ?></div>
            </div>
        </div>

        <div class="grid">

            <!-- ── LEFT: ADD / EDIT FORM ── -->
            <div>
                <div class="card">
                    <h2><?php echo $editClient ? 'Edit Client' : 'Add New Client'; ?></h2>

                    <form method="POST">
                        <input type="hidden" name="action" value="<?php echo $editClient ? 'edit' : 'add'; ?>">
                        <?php if ($editClient): ?>
                            <input type="hidden" name="id" value="<?php echo $editClient['id']; ?>">
                        <?php endif; ?>

                        <div class="field">
                            <label>Client / Library Name</label>
                            <input type="text" name="client_name"
                                   placeholder="e.g. Chhaya Public Library"
                                   value="<?php echo htmlspecialchars($editClient['client_name'] ?? ''); ?>"
                                   required>
                        </div>

                        <div class="field">
                            <label>Subdomain</label>
                            <input type="text" name="subdomain"
                                   placeholder="e.g. chhaya"
                                   value="<?php echo htmlspecialchars($editClient['subdomain'] ?? ''); ?>"
                                <?php echo $editClient ? 'readonly style="background:#f5f5f5;color:#999;"' : ''; ?>
                                   required>
                            <small>
                                <?php if ($editClient): ?>
                                    Subdomain cannot be changed after creation
                                <?php else: ?>
                                    Will create: <strong>chhaya.optms.co.in</strong> — lowercase, no spaces
                                <?php endif; ?>
                            </small>
                        </div>

                        <div class="field">
                            <label>Database Name</label>
                            <input type="text" name="db_name"
                                   placeholder="e.g. edrppymy_erp_chhaya"
                                   value="<?php echo htmlspecialchars($editClient['db_name'] ?? ''); ?>"
                                   required>
                            <small>Must match exactly what you created in cPanel MySQL</small>
                        </div>

                        <div class="field">
                            <label>Database Username</label>
                            <input type="text" name="db_user"
                                   placeholder="e.g. edrppymy_erpuser"
                                   value="<?php echo htmlspecialchars($editClient['db_user'] ?? ''); ?>"
                                   required>
                        </div>

                        <div class="field">
                            <label>Database Password</label>
                            <input type="password" name="db_pass"
                                   placeholder="<?php echo $editClient ? 'Leave blank to keep current' : 'Enter DB password'; ?>"
                                <?php echo $editClient ? '' : 'required'; ?>>
                        </div>

                        <div class="field">
                            <label>Plan</label>
                            <select name="plan">
                                <?php foreach (['basic','premium','enterprise'] as $p): ?>
                                    <option value="<?php echo $p; ?>"
                                        <?php echo (($editClient['plan'] ?? 'basic') === $p) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($p); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if (!$editClient): ?>
                            <div class="toggle-row">
                                <input type="checkbox" name="active" id="active" checked>
                                <label for="active">Activate immediately</label>
                            </div>
                        <?php endif; ?>

                        <div style="display:flex; gap:10px;">
                            <button type="submit" class="btn <?php echo $editClient ? 'btn-green' : 'btn-blue'; ?>" style="flex:1;">
                                <?php echo $editClient ? 'Update Client' : 'Add Client'; ?>
                            </button>
                            <?php if ($editClient): ?>
                                <a href="clients.php" class="btn btn-gray">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Quick Help -->
                <div class="card">
                    <h2>Quick Checklist</h2>
                    <div style="font-size:13px;color:#555;line-height:2;">
                        Before adding a client make sure you have:<br>
                        <span style="color:#34a853;">&#10003;</span> Created subdomain in cPanel pointing to <code style="background:#f5f5f5;padding:1px 5px;border-radius:3px;">public_html/library-erp</code><br>
                        <span style="color:#34a853;">&#10003;</span> Created the database in cPanel MySQL Databases<br>
                        <span style="color:#34a853;">&#10003;</span> Granted MySQL user access to that database<br>
                        <span style="color:#34a853;">&#10003;</span> Imported client DB tables (books, members etc.)
                    </div>
                </div>
            </div>

            <!-- ── RIGHT: CLIENTS TABLE ── -->
            <div class="card">
                <h2>All Clients (<?php echo $total; ?>)</h2>

                <?php if (empty($clients)): ?>
                    <div style="text-align:center;padding:40px 20px;color:#999;">
                        No clients added yet. Use the form to add your first client.
                    </div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table>
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Client Name</th>
                                <th>Subdomain</th>
                                <th>Database</th>
                                <th>Plan</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($clients as $i => $client): ?>
                                <tr>
                                    <td style="color:#999;"><?php echo $i + 1; ?></td>
                                    <td>
                                        <strong style="font-weight:500;">
                                            <?php echo htmlspecialchars($client['client_name']); ?>
                                        </strong><br>
                                        <small style="color:#999;">
                                            Added: <?php echo date('d M Y', strtotime($client['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <a href="https://<?php echo $client['subdomain']; ?>.optms.co.in"
                                           target="_blank"
                                           style="color:#1a73e8;text-decoration:none;font-size:13px;">
                                            <?php echo $client['subdomain']; ?>.optms.co.in
                                        </a>
                                    </td>
                                    <td>
                                        <code style="font-size:11px;background:#f5f5f5;padding:2px 6px;border-radius:3px;">
                                            <?php echo htmlspecialchars($client['db_name']); ?>
                                        </code>
                                    </td>
                                    <td>
                                        <?php
                                        $planColors = [
                                            'basic'      => 'badge-blue',
                                            'premium'    => 'badge-amber',
                                            'enterprise' => 'badge-purple',
                                        ];
                                        $planClass = $planColors[$client['plan']] ?? 'badge-blue';
                                        ?>
                                        <span class="badge <?php echo $planClass; ?>">
                                    <?php echo ucfirst($client['plan'] ?? 'basic'); ?>
                                </span>
                                    </td>
                                    <td>
                                        <?php if ($client['active']): ?>
                                            <span class="badge badge-green">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-red">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="?edit=<?php echo $client['id']; ?>"
                                               class="btn btn-gray">Edit</a>

                                            <a href="?toggle=<?php echo $client['id']; ?>&current=<?php echo $client['active']; ?>"
                                               class="btn <?php echo $client['active'] ? 'btn-amber' : 'btn-green'; ?>"
                                               onclick="return confirm('<?php echo $client['active'] ? 'Deactivate' : 'Activate'; ?> this client?')">
                                                <?php echo $client['active'] ? 'Deactivate' : 'Activate'; ?>
                                            </a>

                                            <a href="?delete=<?php echo $client['id']; ?>"
                                               class="btn btn-red"
                                               onclick="return confirm('DELETE <?php echo htmlspecialchars($client['client_name']); ?>? This cannot be undone.')">
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

<?php endif; ?>

</body>
</html>