<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

requireLogin();
if (!isDonor()) {
    redirect('charity_dashboard.php');
}

$userId = $_SESSION['user_id'];
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$sql = "SELECT d.*, 
        (SELECT COUNT(*) FROM requests WHERE donation_id = d.id AND status = 'pending') as pending_requests
        FROM donations d 
        WHERE d.donor_id = ?";
$params = [$userId];

if ($statusFilter && in_array($statusFilter, ['available', 'requested', 'completed', 'expired'])) {
    $sql .= " AND d.status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY d.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$donations = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <h1 class="text-3xl font-bold text-gray-800">My Donations</h1>
        <a href="add_donation.php"
            class="bg-primary text-white font-bold py-2 px-6 rounded-lg hover:bg-green-600 transition duration-300 shadow-md">Add
            New Donation</a>
    </div>

    <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 mb-8">
        <form method="GET" class="flex flex-col md:flex-row items-end gap-4">
            <div class="w-full md:w-64">
                <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Filter by Status</label>
                <select id="status" name="status"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 bg-white"
                    onchange="this.form.submit()">
                    <option value="">All Donations</option>
                    <option value="available" <?php echo $statusFilter === 'available' ? 'selected' : ''; ?>>Available
                    </option>
                    <option value="requested" <?php echo $statusFilter === 'requested' ? 'selected' : ''; ?>>Requested
                    </option>
                    <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed
                    </option>
                    <option value="expired" <?php echo $statusFilter === 'expired' ? 'selected' : ''; ?>>Expired</option>
                </select>
            </div>
        </form>
    </div>

    <?php if (empty($donations)): ?>
        <div class="bg-white rounded-xl shadow p-12 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                    </path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">No Donations Found</h3>
            <p class="text-gray-500 mb-6">You haven't added any donations yet.</p>
            <a href="add_donation.php"
                class="inline-block bg-primary text-white font-bold py-2 px-6 rounded-lg hover:bg-green-600 transition duration-300">Add
                Your First Donation</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($donations as $donation): ?>
                <div
                    class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition duration-300 flex flex-col h-full">

                    <?php if ($donation['image_path']): // ADDED: Image thumbnail ?>
                        <div class="h-48 overflow-hidden relative">
                            <img src="<?php echo sanitize($donation['image_path']); ?>"
                                alt="<?php echo sanitize($donation['food_name']); ?>" class="w-full h-full object-cover">
                        </div>
                    <?php endif; ?>

                    <div class="p-6 flex-grow">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-800 truncate"
                                    title="<?php echo sanitize($donation['food_name']); ?>">
                                    <?php echo sanitize($donation['food_name']); ?></h3>
                                <span
                                    class="text-sm text-gray-500 font-medium"><?php echo sanitize($donation['food_type']); ?></span>
                            </div>
                            <span
                                class="<?php echo getStatusBadgeClass($donation['status']); ?> px-2 py-1 text-xs rounded-full font-semibold">
                                <?php echo ucfirst($donation['status']); ?>
                            </span>
                        </div>

                        <div class="space-y-2 text-sm text-gray-600 mb-4">
                            <p class="flex items-center"><span class="font-semibold w-20">Quantity:</span>
                                <?php echo sanitize($donation['quantity']); ?></p>
                            <p class="flex items-center"><span class="font-semibold w-20">Location:</span>
                                <?php echo sanitize($donation['city']); ?></p>
                            <?php if ($donation['expiry_date']): ?>
                                <p class="flex items-center"><span class="font-semibold w-20">Expires:</span>
                                    <?php echo formatDate($donation['expiry_date']); ?></p>
                            <?php endif; ?>
                            <p class="flex items-center"><span class="font-semibold w-20">Added:</span>
                                <?php echo date('M d, Y', strtotime($donation['created_at'])); ?></p>
                        </div>

                        <?php if ($donation['pending_requests'] > 0): ?>
                            <div
                                class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 mb-4">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <?php echo $donation['pending_requests']; ?> pending request(s)
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                        <?php if ($donation['status'] === 'available'): ?>
                            <a href="edit_donation.php?id=<?php echo $donation['id']; ?>"
                                class="text-gray-600 hover:text-primary font-medium text-sm border border-gray-300 hover:border-primary rounded-lg px-3 py-1.5 transition duration-300">Edit</a>
                            <a href="delete_donation.php?id=<?php echo $donation['id']; ?>"
                                class="text-red-500 hover:text-red-700 font-medium text-sm border border-red-200 hover:border-red-400 rounded-lg px-3 py-1.5 transition duration-300"
                                onclick="return confirm('Are you sure you want to delete this donation?')">Delete</a>
                        <?php endif; ?>
                        <?php if ($donation['pending_requests'] > 0): ?>
                            <a href="manage_requests.php?donation_id=<?php echo $donation['id']; ?>"
                                class="bg-primary text-white hover:bg-green-600 font-medium text-sm rounded-lg px-3 py-1.5 transition duration-300 shadow-sm">View
                                Requests</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>