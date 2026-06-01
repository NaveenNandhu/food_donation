<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

requireLogin();
if (isAdmin()) {
    $donationId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    // Check if donation exists
    $stmt = $pdo->prepare("SELECT id FROM donations WHERE id = ?");
    $stmt->execute([$donationId]);

    if ($stmt->fetch()) {
        try {
            $stmt = $pdo->prepare("DELETE FROM donations WHERE id = ?");
            $stmt->execute([$donationId]);
            setFlashMessage('success', 'Donation deleted successfully.');
        } catch (PDOException $e) {
            setFlashMessage('danger', 'Failed to delete donation.');
        }
    } else {
        setFlashMessage('danger', 'Donation not found.');
    }

    redirect('view_donations_list.php');
}

if (!isDonor()) {
    redirect('charity_dashboard.php');
}

$donationId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM donations WHERE id = ? AND donor_id = ? AND status = 'available'");
$stmt->execute([$donationId, $_SESSION['user_id']]);
$donation = $stmt->fetch();

if (!$donation) {
    setFlashMessage('danger', 'Donation not found or cannot be deleted.');
    redirect('my_donations.php');
}

try {
    $stmt = $pdo->prepare("DELETE FROM donations WHERE id = ? AND donor_id = ?");
    $stmt->execute([$donationId, $_SESSION['user_id']]);
    setFlashMessage('success', 'Donation deleted successfully.');
} catch (PDOException $e) {
    setFlashMessage('danger', 'Failed to delete donation.');
}

redirect('my_donations.php');
?>