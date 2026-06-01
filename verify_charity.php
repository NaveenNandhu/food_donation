<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

requireLogin();
requireAdmin();

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($userId === 0 || !in_array($action, ['verify', 'reject'])) {
    setFlashMessage('danger', 'Invalid request.');
    redirect('admin_dashboard.php');
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND user_type = 'charity'");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('danger', 'Charity not found.');
    redirect('admin_dashboard.php');
}

try {
    if ($action === 'verify') {
        $stmt = $pdo->prepare("UPDATE users SET is_verified = TRUE WHERE id = ?");
        $stmt->execute([$userId]);
        setFlashMessage('success', 'Charity ' . sanitize($user['organization_name']) . ' verified successfully!');
    } elseif ($action === 'reject') {
        // Delete the user account. ON DELETE CASCADE will clean up any associated requests.
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        setFlashMessage('danger', 'Charity ' . sanitize($user['organization_name']) . ' rejected and account deleted.');
    }
} catch (PDOException $e) {
    setFlashMessage('danger', 'Database error: Failed to process verification.');
}

redirect('admin_dashboard.php');
?>