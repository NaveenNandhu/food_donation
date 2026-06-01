<?php
ob_start(); // Enable output buffering to prevent "headers already sent" errors

// Prevent Vercel from caching dynamic pages and causing redirect loops
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Simulate session using cookies for Serverless environments (Vercel)
function getSessionData() {
    if (isset($_COOKIE['foodshare_session'])) {
        return json_decode(base64_decode($_COOKIE['foodshare_session']), true) ?: [];
    }
    return [];
}

function setSessionData($data) {
    $_SESSION = array_merge($_SESSION ?? [], $data);
    setcookie('foodshare_session', base64_encode(json_encode($_SESSION)), time() + (86400 * 30), '/');
}

function destroySession() {
    setcookie('foodshare_session', '', time() - 3600, '/');
    $_SESSION = [];
}

// Initialize $_SESSION from cookie so existing code works
$_SESSION = getSessionData();

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function getUserType()
{
    return $_SESSION['user_type'] ?? null;
}

function isDonor()
{
    return getUserType() === 'donor';
}

function isCharity()
{
    return getUserType() === 'charity';
}

function isAdmin()
{
    return getUserType() === 'admin';
}

function isVerifiedCharity()
{
    return isCharity() && (isset($_SESSION['is_verified']) && $_SESSION['is_verified'] == true);
}

function requireAdmin()
{
    if (!isAdmin()) {
        redirect('index.php');
    }
}

function sanitize($data)
{
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

function redirect($url)
{
    header("Location: $url");
    exit;
}

function setFlashMessage($type, $message)
{
    setSessionData(['flash' => ['type' => $type, 'message' => $message]]);
}

function getFlashMessage()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        setcookie('foodshare_session', base64_encode(json_encode($_SESSION)), time() + (86400 * 30), '/');
        return $flash;
    }
    return null;
}

function formatDate($date)
{
    return date('M d, Y', strtotime($date));
}

function getStatusBadgeClass($status)
{
    $baseClasses = 'px-2 py-1 rounded-full text-xs font-semibold';
    $colorClasses = [
        'available' => 'bg-green-100 text-green-800',
        'requested' => 'bg-yellow-100 text-yellow-800',
        'pending' => 'bg-yellow-100 text-yellow-800',
        'completed' => 'bg-blue-100 text-blue-800',
        'expired' => 'bg-red-100 text-red-800',
        'rejected' => 'bg-red-100 text-red-800',
        'accepted' => 'bg-green-100 text-green-800',
    ];
    $colorClass = $colorClasses[$status] ?? 'bg-gray-100 text-gray-800';

    return $baseClasses . ' ' . $colorClass;
}
?>