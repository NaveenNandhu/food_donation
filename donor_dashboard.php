<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

requireLogin();
if (!isDonor()) {
    redirect('charity_dashboard.php');
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM donations WHERE donor_id = ?");
$stmt->execute([$userId]);
$totalDonations = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM donations WHERE donor_id = ? AND status = 'available'");
$stmt->execute([$userId]);
$availableDonations = $stmt->fetch()['total'];

$stmt = $pdo->prepare("
    SELECT COUNT(*) as total FROM requests r 
    JOIN donations d ON r.donation_id = d.id 
    WHERE d.donor_id = ? AND r.status = 'pending'
");
$stmt->execute([$userId]);
$pendingRequests = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM donations WHERE donor_id = ? AND status = 'completed'");
$stmt->execute([$userId]);
$completedDonations = $stmt->fetch()['total'];

$stmt = $pdo->prepare("
    SELECT r.*, d.food_name, d.food_type, u.name as charity_name, u.organization_name
    FROM requests r
    JOIN donations d ON r.donation_id = d.id
    JOIN users u ON r.charity_id = u.id
    WHERE d.donor_id = ? AND r.status = 'pending'
    ORDER BY r.created_at DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$recentRequests = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT * FROM donations 
    WHERE donor_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute([$userId]);
$recentDonations = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Donor Dashboard</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-primary">
            <div class="text-3xl font-bold text-gray-800"><?php echo $totalDonations; ?></div>
            <div class="text-gray-500 text-sm uppercase tracking-wide font-semibold mt-1">Total Donations</div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-green-500">
            <div class="text-3xl font-bold text-gray-800"><?php echo $availableDonations; ?></div>
            <div class="text-gray-500 text-sm uppercase tracking-wide font-semibold mt-1">Available</div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-yellow-500">
            <div class="text-3xl font-bold text-gray-800"><?php echo $pendingRequests; ?></div>
            <div class="text-gray-500 text-sm uppercase tracking-wide font-semibold mt-1">Pending Requests</div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-blue-500">
            <div class="text-3xl font-bold text-gray-800"><?php echo $completedDonations; ?></div>
            <div class="text-gray-500 text-sm uppercase tracking-wide font-semibold mt-1">Completed</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Pending Requests Card -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h2 class="text-lg font-bold text-gray-800">Pending Requests</h2>
                <a href="manage_requests.php"
                    class="text-sm text-primary hover:text-green-700 font-medium hover:underline">View All</a>
            </div>
            <div class="p-0">
                <?php if (empty($recentRequests)): ?>
                    <div class="p-8 text-center text-gray-500">
                        <p>No pending requests at the moment.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Food Item</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Requested By</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($recentRequests as $request): ?>
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo sanitize($request['food_name']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo sanitize($request['organization_name'] ?? $request['charity_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="manage_requests.php?action=view&id=<?php echo $request['id']; ?>"
                                                class="text-primary hover:text-green-900 font-bold bg-green-50 px-3 py-1 rounded-full hover:bg-green-100 transition duration-200">Review</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Donations Card -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h2 class="text-lg font-bold text-gray-800">Recent Donations</h2>
                <a href="my_donations.php"
                    class="text-sm text-primary hover:text-green-700 font-medium hover:underline">View All</a>
            </div>
            <div class="p-0">
                <?php if (empty($recentDonations)): ?>
                    <div class="p-8 text-center text-gray-500">
                        <p>No donations yet. <a href="add_donation.php" class="text-primary hover:underline">Add your first
                                donation</a></p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Food Item</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($recentDonations as $donation): ?>
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo sanitize($donation['food_name']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="<?php echo getStatusBadgeClass($donation['status']); ?>">
                                                <?php echo ucfirst($donation['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($donation['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="mt-8 text-center">
        <a href="add_donation.php"
            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-full shadow-sm text-white bg-primary hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition duration-300 transform hover:-translate-y-0.5">
            <svg class="mr-2 -ml-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New Donation
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>