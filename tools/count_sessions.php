<?php
$host = '127.0.0.1';
$port = 3306;
$db   = 'sheelearn';
$user = 'root';
$pass = 'mysql';
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->query('SELECT COUNT(*) AS cnt FROM sessions');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "sessions_count=" . ($row['cnt'] ?? '0') . PHP_EOL;
} catch (Exception $e) {
    echo "error: " . $e->getMessage() . PHP_EOL;
}
