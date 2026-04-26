<?php
// ═══════════════════════════════════════════════════════════════
//  iclock.php  —  iClock / ADMS Push Server
//  Compatible with: eSSL X990, Realtime RS9N, ZKTeco, any ADMS device
//
//  Configure on device:
//    ADMS Server IP   → your-server.com
//    ADMS Port        → 80 (or 443 for HTTPS)
//    ADMS Path        → /iclock/cdata  (device appends this automatically)
//
//  Place this file at: /iclock/cdata.php  OR set .htaccess to route
//  /iclock/* to this file.
// ═══════════════════════════════════════════════════════════════
error_reporting(0);
@ini_set('display_errors', '0');

require_once __DIR__ . '/includes/db.php';
$db = getDB();

// ── Auto-create tables ──
$db->exec("CREATE TABLE IF NOT EXISTS biometric_devices (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    serial_number VARCHAR(64)  NOT NULL UNIQUE,
    device_name   VARCHAR(128) DEFAULT '',
    ip_address    VARCHAR(64)  DEFAULT '',
    last_seen     TIMESTAMP    NULL,
    status        VARCHAR(16)  DEFAULT 'offline',
    total_punches INT          DEFAULT 0,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->exec("CREATE TABLE IF NOT EXISTS biometric_punches (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    serial_number VARCHAR(64)  NOT NULL,
    user_id       VARCHAR(32)  NOT NULL,
    punch_time    DATETIME     NOT NULL,
    punch_type    VARCHAR(16)  DEFAULT 'check_in',
    verify_type   VARCHAR(16)  DEFAULT 'fingerprint',
    student_id    VARCHAR(32)  NULL,
    processed     TINYINT(1)   DEFAULT 0,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_uid   (user_id),
    INDEX idx_time  (punch_time),
    INDEX idx_stu   (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->exec("CREATE TABLE IF NOT EXISTS student_attendance (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    student_id   VARCHAR(32) NOT NULL,
    date         DATE        NOT NULL,
    status       VARCHAR(16) NOT NULL DEFAULT 'present',
    check_in     TIME        NULL,
    check_out    TIME        NULL,
    is_late      TINYINT(1)  DEFAULT 0,
    late_minutes INT         DEFAULT 0,
    marked_by    VARCHAR(64) DEFAULT 'biometric',
    device_sn    VARCHAR(64) DEFAULT '',
    created_at   TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_stu_date (student_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── Helpers ──
function respond($text) {
    header('Content-Type: text/plain');
    echo $text;
    exit;
}

function addAct($db, $icon, $bg, $msg) {
    try {
        $db->prepare("INSERT INTO activity_log (icon,bg,text) VALUES (?,?,?)")->execute([$icon,$bg,$msg]);
        $db->exec("DELETE FROM activity_log WHERE id NOT IN
            (SELECT id FROM (SELECT id FROM activity_log ORDER BY created_at DESC LIMIT 500) t)");
    } catch(Exception $e) {}
}

// ── Get device SN and client IP ──
$sn     = $_GET['SN'] ?? $_GET['sn'] ?? ($_SERVER['HTTP_SN'] ?? 'UNKNOWN');
$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];
$ip     = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';

// ── Register / update device heartbeat ──
$db->prepare("INSERT INTO biometric_devices (serial_number, ip_address, last_seen, status)
    VALUES (?,?,NOW(),'online')
    ON DUPLICATE KEY UPDATE last_seen=NOW(), ip_address=?, status='online'")
    ->execute([$sn, $ip, $ip]);

// ═══════════════════════════════════════════════════════════════
//  GET /iclock/cdata?SN=XXXX&options=all
//  Device handshake — return server config
// ═══════════════════════════════════════════════════════════════
if ($method === 'GET' && (strpos($uri, 'cdata') !== false || isset($_GET['options']))) {
    respond(
        "GET OPTION FROM: $sn\n" .
        "Stamp=9999\n" .
        "OpStamp=9999\n" .
        "ErrorDelay=30\n" .
        "Delay=10\n" .
        "TransTimes=00:00;23:59\n" .
        "TransInterval=1\n" .
        "TransFlag=1111000000\n" .
        "Realtime=1\n" .
        "Encrypt=0\n"
    );
}

// ═══════════════════════════════════════════════════════════════
//  POST /iclock/cdata?SN=XXXX
//  Device pushes attendance logs (ATTLOG format or JSON)
// ═══════════════════════════════════════════════════════════════
if ($method === 'POST') {
    $rawBody = file_get_contents('php://input');
    $rows    = [];

    // ── Parse format A: JSON (Realtime RS9N / newer eSSL) ──
    $jsonData = json_decode($rawBody, true);
    if ($jsonData && isset($jsonData['RealTime']['PunchLog'])) {
        $pl     = $jsonData['RealTime']['PunchLog'];
        $userId = trim($pl['UserId'] ?? '');
        $time   = $pl['LogTime'] ?? date('Y-m-d H:i:s');
        $type   = strtolower($pl['Type'] ?? 'checkin');
        $verify = strtolower($pl['InputType'] ?? 'fingerprint');
        if ($userId) $rows[] = [$userId, $time, ($type === 'checkout' ? 1 : 0), $verify];

    // ── Parse format B: iClock ATTLOG text format ──
    } elseif (strpos($rawBody, 'ATTLOG') !== false || (strpos($rawBody, "\t") !== false && strlen($rawBody) > 5)) {
        foreach (explode("\n", $rawBody) as $line) {
            $line = trim($line);
            if (empty($line) || substr($line, 0, 2) === 'OK') continue;
            if (substr($line, 0, 6) === 'ATTLOG') $line = trim(substr($line, 6));
            $parts = preg_split('/[\t ]+/', $line, 6);
            if (count($parts) >= 2 && !empty($parts[0]) && !empty($parts[1])) {
                $rows[] = [
                    $parts[0],               // UserID
                    $parts[1],               // DateTime
                    (int)($parts[2] ?? 0),   // Status (0=in,1=out)
                    $parts[3] ?? 'fp',       // VerifyType
                ];
            }
        }

    // ── Parse format C: JSON array ──
    } elseif ($jsonData && is_array($jsonData)) {
        $list = isset($jsonData[0]) ? $jsonData : [$jsonData];
        foreach ($list as $rec) {
            $uid  = $rec['user_id'] ?? $rec['UserId'] ?? $rec['userid'] ?? '';
            $time = $rec['punch_time'] ?? $rec['LogTime'] ?? $rec['time'] ?? date('Y-m-d H:i:s');
            $stat = (int)($rec['status'] ?? $rec['Status'] ?? 0);
            $ver  = $rec['verify'] ?? $rec['InputType'] ?? 'fingerprint';
            if ($uid) $rows[] = [$uid, $time, $stat, $ver];
        }

    // ── Parse format D: POST form / query string ──
    } else {
        parse_str($rawBody, $params);
        $uid  = $params['user_id'] ?? $params['UserId'] ?? $_GET['user_id'] ?? '';
        $time = $params['punch_time'] ?? $params['time'] ?? date('Y-m-d H:i:s');
        $stat = (int)($params['status'] ?? 0);
        $ver  = $params['verify'] ?? 'fingerprint';
        if ($uid) $rows[] = [$uid, $time, $stat, $ver];
    }

    // ── Get fee gate setting ──
    $feeGate = 0;
    try {
        $db->exec("ALTER TABLE settings ADD COLUMN IF NOT EXISTS biometric_fee_gate TINYINT(1) DEFAULT 0");
        $sg = $db->query("SELECT biometric_fee_gate FROM settings WHERE id=1")->fetch();
        $feeGate = (int)($sg['biometric_fee_gate'] ?? 0);
    } catch(Exception $e) {}

    $processed = 0;

    foreach ($rows as [$userId, $rawTime, $statusCode, $verifyType]) {
        $userId = trim($userId);
        if (empty($userId)) continue;

        // Normalize time
        $rawTime  = trim(str_replace('T', ' ', $rawTime));
        $punchTs  = strtotime($rawTime);
        if (!$punchTs || $punchTs < 1000000) $punchTs = time();
        $punchDt  = date('Y-m-d H:i:s', $punchTs);
        $punchDate= date('Y-m-d', $punchTs);
        $punchTime= date('H:i:s', $punchTs);

        // check_in=0, check_out=1,3
        $punchType = ($statusCode == 1 || $statusCode == 3) ? 'check_out' : 'check_in';

        // Save raw punch (ignore duplicates)
        try {
            $db->prepare("INSERT IGNORE INTO biometric_punches
                (serial_number, user_id, punch_time, punch_type, verify_type)
                VALUES (?,?,?,?,?)")
                ->execute([$sn, $userId, $punchDt, $punchType, $verifyType]);
        } catch(Exception $e) {}

        // ── Find student ──
        // Try exact match first: device user_id = student id (STU-001)
        $stuStmt = $db->prepare(
            "SELECT s.*, b.name as bname, b.start_time as bstart
             FROM students s LEFT JOIN batches b ON b.id=s.batch_id
             WHERE s.id=? LIMIT 1"
        );
        $stuStmt->execute([$userId]);
        $stu = $stuStmt->fetch(PDO::FETCH_ASSOC);

        // Try numeric match (device stores 1 → STU-001)
        if (!$stu && is_numeric($userId)) {
            $padded = 'STU-' . str_pad($userId, 3, '0', STR_PAD_LEFT);
            $stuStmt->execute([$padded]);
            $stu = $stuStmt->fetch(PDO::FETCH_ASSOC);
        }

        // Try partial/fuzzy match
        if (!$stu) {
            $like = $db->prepare(
                "SELECT s.*, b.name as bname, b.start_time as bstart
                 FROM students s LEFT JOIN batches b ON b.id=s.batch_id
                 WHERE s.id LIKE ? LIMIT 1"
            );
            $like->execute(['%'.$userId.'%']);
            $stu = $like->fetch(PDO::FETCH_ASSOC);
        }

        if (!$stu) continue; // unknown user

        // Update punch with student_id
        $db->prepare("UPDATE biometric_punches SET student_id=?, processed=1
            WHERE serial_number=? AND user_id=? AND punch_time=? LIMIT 1")
            ->execute([$stu['id'], $sn, $userId, $punchDt]);

        // ── Fee gate check ──
        if ($feeGate && $stu['fee_status'] === 'overdue') {
            addAct($db, '🚫', 'rgba(239,68,68,.14)',
                "<strong>{$stu['fname']} {$stu['lname']}</strong> entry DENIED — fee overdue (biometric)");
            continue;
        }

        // ── Update attendance ──
        $existStmt = $db->prepare(
            "SELECT * FROM student_attendance WHERE student_id=? AND date=? LIMIT 1"
        );
        $existStmt->execute([$stu['id'], $punchDate]);
        $att = $existStmt->fetch(PDO::FETCH_ASSOC);

        if (!$att) {
            // First punch today = check_in with late check
            $isLate = 0; $lateMin = 0;
            if (!empty($stu['bstart'])) {
                $bTs   = strtotime($punchDate . ' ' . $stu['bstart']);
                $grace = 15 * 60;
                if ($punchTs > $bTs + $grace) {
                    $isLate  = 1;
                    $lateMin = (int)round(($punchTs - $bTs) / 60);
                }
            }
            $db->prepare(
                "INSERT INTO student_attendance
                 (student_id, date, status, check_in, is_late, late_minutes, marked_by, device_sn)
                 VALUES (?,?,?,?,?,?,?,?)
                 ON DUPLICATE KEY UPDATE
                 check_in=VALUES(check_in), is_late=VALUES(is_late),
                 late_minutes=VALUES(late_minutes), marked_by='biometric_device'"
            )->execute([$stu['id'], $punchDate, 'present', $punchTime, $isLate, $lateMin, 'biometric_device', $sn]);

            // Sync to main attendance table
            $db->prepare("INSERT INTO attendance (student_id, attendance_date, status)
                VALUES (?,?,?) ON DUPLICATE KEY UPDATE status='present'")
                ->execute([$stu['id'], $punchDate, 'present']);

            $lateMsg = $isLate ? " ⚠ Late {$lateMin}min" : '';
            addAct($db, '🖐️', 'rgba(22,163,74,.14)',
                "<strong>{$stu['fname']} {$stu['lname']}</strong> checked in via biometric at $punchTime$lateMsg");

        } elseif (!$att['check_out']) {
            // Second punch = check_out
            $db->prepare(
                "UPDATE student_attendance SET check_out=?, marked_by='biometric_device'
                 WHERE student_id=? AND date=?"
            )->execute([$punchTime, $stu['id'], $punchDate]);

            addAct($db, '🚪', 'rgba(100,116,139,.14)',
                "<strong>{$stu['fname']} {$stu['lname']}</strong> checked out at $punchTime");
        }

        // Update device total
        $db->prepare("UPDATE biometric_devices SET total_punches=total_punches+1 WHERE serial_number=?")
            ->execute([$sn]);

        $processed++;
    }

    respond("OK: $processed\n");
}

// ── Default GET (heartbeat) ──
$db->prepare("UPDATE biometric_devices SET last_seen=NOW(), status='online' WHERE serial_number=?")
    ->execute([$sn]);
respond("OK\n");
