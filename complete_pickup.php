<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

requireLogin();
if (!isCharity()) {
    redirect('donor_dashboard.php');
}

$requestId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT r.*, d.id as donation_id 
    FROM requests r 
    JOIN donations d ON r.donation_id = d.id 
    WHERE r.id = ? AND r.charity_id = ? AND r.status = 'accepted'
");
$stmt->execute([$requestId, $_SESSION['user_id']]);
$request = $stmt->fetch();

if (!$request) {
    setFlashMessage('danger', 'Request not found or cannot be completed.');
    redirect('my_requests.php');
}

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("UPDATE requests SET status = 'completed' WHERE id = ?");
    $stmt->execute([$requestId]);
    
    $stmt = $pdo->prepare("UPDATE donations SET status = 'completed' WHERE id = ?");
    $stmt->execute([$request['donation_id']]);
    
    $pdo->commit();
    setFlashMessage('success', 'Donation pickup confirmed! Thank you for helping reduce food waste.');
} catch (Exception $e) {
    $pdo->rollBack();
    setFlashMessage('danger', 'Failed to complete the pickup.');
}

redirect('my_requests.php');
?>
