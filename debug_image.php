<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

$stmt = $pdo->query("SELECT id, food_name, image_path FROM donations");
$donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h1>Donation Images Debug</h1>";
echo "<table border='1'><tr><th>ID</th><th>Food Name</th><th>Image Path from DB</th><th>File Exists?</th><th>Image Preview</th></tr>";

foreach ($donations as $donation) {
    echo "<tr>";
    echo "<td>" . $donation['id'] . "</td>";
    echo "<td>" . htmlspecialchars($donation['food_name']) . "</td>";
    echo "<td>" . htmlspecialchars($donation['image_path'] ?? 'NULL') . "</td>";

    $fileExists = 'No';
    if (!empty($donation['image_path']) && file_exists($donation['image_path'])) {
        $fileExists = 'Yes';
    }
    echo "<td>" . $fileExists . "</td>";

    echo "<td>";
    if (!empty($donation['image_path'])) {
        echo "<img src='" . htmlspecialchars($donation['image_path']) . "' style='width: 100px;'>";
    }
    echo "</td>";
    echo "</tr>";
}
echo "</table>";
?>