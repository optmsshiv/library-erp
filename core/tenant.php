<?php
require_once __DIR__ . '/../config/db.php';

class Tenant {

    private static ?PDO   $configDb   = null;
    private static ?PDO   $tenantDb   = null;
    private static ?array $tenantInfo = null;

    // ─────────────────────────────────────────
    // STEP 1: Connect to master config database
    // ─────────────────────────────────────────
    private static function getConfigDb(): PDO {
        if (self::$configDb === null) {
            try {
                $dsn = "mysql:host=" . CONFIG_DB_HOST
                    . ";dbname=" . CONFIG_DB_NAME
                    . ";charset=utf8mb4";

                self::$configDb = new PDO($dsn, CONFIG_DB_USER, CONFIG_DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT         => false,
                ]);

            } catch (PDOException $e) {
                error_log("Config DB connection failed: " . $e->getMessage());
                self::showError("System error", "Could not connect to config database. [ERR:CFG]");
            }
        }
        return self::$configDb;
    }

    // ─────────────────────────────────────────
    // STEP 2: Detect subdomain from the URL
    // ─────────────────────────────────────────
    public static function getSubdomain(): string {

        // For local testing: ?subdomain=chhaya
        if (isset($_GET['subdomain'])) {
            return preg_replace('/[^a-z0-9\-]/', '', strtolower($_GET['subdomain']));
        }

        $host = $_SERVER['HTTP_HOST'] ?? '';

        // Strip port number (e.g. localhost:8080)
        $host = explode(':', $host)[0];

        // Strip leading www.
        $host = preg_replace('/^www\./', '', $host);

        $parts = explode('.', $host);

        // optms.co.in        → ['optms','co','in']        3 parts → main site, no subdomain
        // chhaya.optms.co.in → ['chhaya','optms','co','in'] 4 parts → subdomain = chhaya
        if (count($parts) >= 4) {
            // Everything before the base domain (optms.co.in) is the subdomain
            return strtolower($parts[0]);
        }

        return '';
    }

    // ─────────────────────────────────────────
    // STEP 3: Load tenant info from config DB
    // ─────────────────────────────────────────
    public static function load(): void {

        $subdomain = self::getSubdomain();

        if ($subdomain === '') {
            self::showError(
                "No library found",
                "Please visit your library subdomain — e.g. chhaya.optms.co.in"
            );
        }

        // BUG FIX: declare $tenant outside try{} so it's always in scope
        $tenant = null;

        try {
            $db   = self::getConfigDb();
            $stmt = $db->prepare(
                "SELECT * FROM subdomain_map
                  WHERE subdomain = ? AND active = 1
                  LIMIT 1"
            );
            $stmt->execute([$subdomain]);
            $tenant = $stmt->fetch(); // returns array|false

        } catch (PDOException $e) {
            error_log("Tenant lookup failed: " . $e->getMessage());
            self::showError("System error", "Could not load library data. [ERR:TBL]");
        }

        // $stmt->fetch() returns false when no row found; treat false the same as null
        if (empty($tenant)) {
            self::showError(
                "Library not found",
                "No active library found for: " . htmlspecialchars($subdomain)
            );
        }

        self::$tenantInfo = $tenant;
    }

    // ─────────────────────────────────────────
    // STEP 4: Connect to the client's own DB
    // ─────────────────────────────────────────
    public static function db(): PDO {

        if (self::$tenantDb !== null) {
            return self::$tenantDb;
        }

        if (self::$tenantInfo === null) {
            self::load();
        }

        $t = self::$tenantInfo;

        // BUG FIX: resolve db_host BEFORE building the DSN string
        // ?? inside {} in a double-quoted string is a PHP parse error
        $host = !empty($t['db_host']) ? $t['db_host'] : 'localhost';

        try {
            $dsn = "mysql:host={$host};dbname={$t['db_name']};charset=utf8mb4";

            self::$tenantDb = new PDO($dsn, $t['db_user'], $t['db_pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT         => false,
            ]);

        } catch (PDOException $e) {
            error_log("Tenant DB connection failed [{$t['subdomain']}]: " . $e->getMessage());
            self::showError(
                "Database error",
                "Could not connect to library database. [ERR:TDB]"
            );
        }

        return self::$tenantDb;
    }

    // ─────────────────────────────────────────
    // STEP 5: Get current tenant details
    // ─────────────────────────────────────────

    // BUG FIX: return type was 'array' but value can be null before load();
    // after load() it is always set or showError() exits — safe to cast
    public static function info(): array {
        if (self::$tenantInfo === null) {
            self::load();
        }
        return (array) self::$tenantInfo;
    }

    public static function name(): string {
        return self::info()['client_name'] ?? 'Library';
    }

    public static function plan(): string {
        return self::info()['plan'] ?? 'basic';
    }

    // ─────────────────────────────────────────
    // STEP 6: Clean error page (no raw PHP errors exposed)
    // ─────────────────────────────────────────
    private static function showError(string $title, string $message): never {
        // Clear any buffered output before sending our page
        if (ob_get_level()) {
            ob_end_clean();
        }
        http_response_code(503);
        echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>" . htmlspecialchars($title) . "</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 60px 20px; background: #f5f3ee; }
        h2   { color: #333; margin-bottom: 10px; }
        p    { color: #666; }
        code { background: #eee; padding: 2px 6px; border-radius: 4px; font-size: .85em; }
    </style>
</head>
<body>
    <h2>" . htmlspecialchars($title) . "</h2>
    <p>" . $message . "</p>
    <p><small>optms.co.in</small></p>
</body>
</html>";
        exit;
    }
}