<?php
// Pull connection variables from Vercel/Cloud Environment variables
// If they don't exist (like on your local XAMPP), it falls back to your local defaults.
$host = $_SERVER['DB_HOST'] ?? $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: "127.0.0.1";
$port = $_SERVER['DB_PORT'] ?? $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: "3306";
$dbname = $_SERVER['DB_DATABASE'] ?? $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: "food_donation_db";
$username = $_SERVER['DB_USERNAME'] ?? $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: "root";
$password = $_SERVER['DB_PASSWORD'] ?? $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: "";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

// If using a cloud database (like TiDB, PlanetScale, etc.), SSL might be required.
if ($host !== '127.0.0.1' && $host !== 'localhost') {
    // 1. Fix Deprecation Error: Use the constant() function to avoid triggering the warning just by typing it out.
    $sslVerifyConstant = defined('\Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT') 
        ? constant('\Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT') 
        : constant('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT');
    
    $options[$sslVerifyConstant] = false;

    // 2. Fix Insecure Transport Error: PDO requires a CA file path to trigger TLS encryption. 
    // Vercel runs on AWS Lambda (Amazon Linux), so we point it to the built-in system CA bundle.
    $caPath = '/etc/pki/tls/certs/ca-bundle.crt'; // Vercel / Amazon Linux default
    if (!file_exists($caPath)) {
        $caPath = '/etc/ssl/certs/ca-certificates.crt'; // Alternative Linux default
    }
    
    // Hide deprecation warning for the CA constant as well
    $sslCaConstant = defined('\Pdo\Mysql::ATTR_SSL_CA')
        ? constant('\Pdo\Mysql::ATTR_SSL_CA')
        : constant('PDO::MYSQL_ATTR_SSL_CA');
        
    if (file_exists($caPath)) {
        $options[$sslCaConstant] = $caPath;
    }
}

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname",
        $username,
        $password,
        $options
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . " (Host: $host)");
}
?>