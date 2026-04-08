<?php
require_once 'core/tenant.php';

// This one line resolves the subdomain and connects to the right DB
$db = Tenant::db();
$info = Tenant::info();

// Now query THIS client's database normally
$books = $db->query("SELECT * FROM books LIMIT 10")->fetchAll();

echo "<h1>Welcome to " . htmlspecialchars($info['client_name']) . "</h1>";
echo "<p>Plan: " . htmlspecialchars($info['plan']) . "</p>";
echo "<h2>Books</h2><ul>";
foreach ($books as $book) {
    echo "<li>" . htmlspecialchars($book['title']) . "</li>";
}
echo "</ul>";