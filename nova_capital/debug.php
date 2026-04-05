<?php
// This file helps diagnose if PHP is working correctly on your server
echo "<h1>PHP Test Page</h1>";
echo "<p>This page confirms that PHP is working on your server.</p>";

echo "<h2>PHP Version Information:</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";

echo "<h2>Server Information:</h2>";
echo "<p>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

echo "<h2>PHP Configuration:</h2>";
$extensions = get_loaded_extensions();
echo "<p>Loaded Extensions: " . implode(", ", $extensions) . "</p>";

echo "<h2>PDO Support:</h2>";
if (extension_loaded('pdo')) {
    echo "<p>PDO is enabled.</p>";
    echo "<p>Available PDO Drivers: " . implode(", ", PDO::getAvailableDrivers()) . "</p>";
} else {
    echo "<p>PDO is not enabled.</p>";
}

echo "<h2>Database Connection Test:</h2>";
try {
    $conn = new PDO("mysql:host=localhost;dbname=nova_capital", "root", "Abhi");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>Database connection successful!</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>Database connection failed: " . $e->getMessage() . "</p>";
}
?>