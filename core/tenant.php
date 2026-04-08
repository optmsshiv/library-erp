<?php
require_once __DIR__ . '/config/db.php';

class Tenant {

    private static ?PDO $configDb = null;
    private static ?PDO $tenantDb = null;
    private static ?array $tenantInfo = null;

    // ─────────────────────────────────────────
    // STEP 1: Connect to master config database
    // ─────────────────────────────────────────
    private static function getConfigDb(): PDO {
        if (self::$configDb === null) {
            try {
                $dsn = "mysql:host=" . CONFIG_DB_HOST .
                    ";dbname=" . CONFIG_DB_NAME .
                    ";charset=utf8mb4";

                self::$configDb = new PDO($dsn, CONFIG_DB_USER, CONFIG_DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT         => false,
                ]);

            } catch (PDOException $e) {
                // Never show raw error to browser on Bluehost
                error_log("Config DB connection failed: " . $e->getMessage());
                die("System error. Please contact support. [ERR:CFG]");
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

        // Remove port number
        $host = explode(':', $host)[0];

        // Remove www
        $host = preg_replace('/^www\./', '', $host);

        $parts = explode('.', $host);

        // optms.co.in        = 3 parts → no subdomain (main site)
        // chhaya.optms.co.in = 4 parts → subdomain is chhaya

        if (count($parts) === 4) {
            return strtolower($parts[0]); // returns "chhaya"
        }

        return '';
    }

    // ─────────────────────────────────────────
    // STEP 3: Load tenant info from config DB
    // ─────────────────────────────────────────
    public static function load(): void {

        $subdomain = self::getSubdomain();

        if (empty($subdomain)) {
            self::showError(
                "No library found",
                "Please visit your library subdomain e.g. chhaya.library.optms.co.in"
            );
        }

        try {
            $db   = self::getConfigDb();
            $stmt = $db->prepare(
                "SELECT * FROM subdomain_map 
                 WHERE subdomain = ? AND active = 1 
                 LIMIT 1"
            );
            $stmt->execute([$subdomain]);
            $tenant = $stmt->fetch();

        } catch (PDOException $e) {
            error_log("Tenant lookup failed: " . $e->getMessage());
            self::showError("System error", "Could not load library. [ERR:TBL]");
        }

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

        try {
            $dsn = "mysql:host={$t['db_host'] ?? 'localhost'}" .
                ";dbname={$t['db_name']}" .
                ";charset=utf8mb4";

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
    public static function info(): array {
        if (self::$tenantInfo === null) {
            self::load();
        }
        return self::$tenantInfo;
    }

    public static function name(): string {
        return self::info()['client_name'] ?? 'Library';
    }

    public static function plan(): string {
        return self::info()['plan'] ?? 'basic';
    }

    // ─────────────────────────────────────────
    // STEP 6: Clean error page for Bluehost
    // ─────────────────────────────────────────
    private static function showError(string $title, string $message): void {
        http_response_code(404);
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>{$title}</title>
            <style>
                body { font-family: sans-serif; text-align: center; padding: 60px 20px; }
                h2   { color: #333; }
                p    { color: #666; }
            </style>
        </head>
        <body>
            <h2>{$title}</h2>
            <p>{$message}</p>
            <p><small>optms.co.in</small></p>
        </body>
        </html>";
        exit;
    }
}