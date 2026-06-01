<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

requireLogin();
if (!isCharity()) {
    redirect('donor_dashboard.php');
}

$userId = $_SESSION['user_id'];
$isVerified = isVerifiedCharity(); // Check verification status

if (!$isVerified) {
    // If not verified, set a flash message for the header to display
    setFlashMessage('warning', 'Your charity account is currently under review. You will be able to browse and request food once your PAN Card verification is complete.');
}

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM donations WHERE status = 'available'");
$stmt->execute();
$availableDonations = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM requests WHERE charity_id = ?");
$stmt->execute([$userId]);
$totalRequests = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM requests WHERE charity_id = ? AND status = 'pending'");
$stmt->execute([$userId]);
$pendingRequests = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM requests WHERE charity_id = ? AND status = 'accepted'");
$stmt->execute([$userId]);
$acceptedRequests = $stmt->fetch()['total'];

$stmt = $pdo->prepare("
    SELECT d.*, u.name as donor_name, u.phone as donor_phone
    FROM donations d
    JOIN users u ON d.donor_id = u.id
    WHERE d.status = 'available'
    ORDER BY d.created_at DESC
    LIMIT 6
");
$stmt->execute();
$recentDonations = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT r.*, d.food_name, d.food_type, u.name as donor_name
    FROM requests r
    JOIN donations d ON r.donation_id = d.id
    JOIN users u ON d.donor_id = u.id
    WHERE r.charity_id = ?
    ORDER BY r.created_at DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$myRequests = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Charity Dashboard</h1>

    <?php if (!$isVerified): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8 rounded-r shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Verification Pending</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>Your account is currently pending verification. You cannot make any requests until an
                            administrator verifies your organization's documentation (PAN Card).</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-primary">
            <div class="text-3xl font-bold text-gray-800"><?php echo $availableDonations; ?></div>
            <div class="text-gray-500 text-sm uppercase tracking-wide font-semibold mt-1">Available Donations</div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-blue-500">
            <div class="text-3xl font-bold text-gray-800"><?php echo $totalRequests; ?></div>
            <div class="text-gray-500 text-sm uppercase tracking-wide font-semibold mt-1">Total Requests</div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-yellow-500">
            <div class="text-3xl font-bold text-gray-800"><?php echo $pendingRequests; ?></div>
            <div class="text-gray-500 text-sm uppercase tracking-wide font-semibold mt-1">Pending</div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-green-500">
            <div class="text-3xl font-bold text-gray-800"><?php echo $acceptedRequests; ?></div>
            <div class="text-gray-500 text-sm uppercase tracking-wide font-semibold mt-1">Accepted</div>
        </div>
    </div>

    <!-- Available Donations Section -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 mb-8">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h2 class="text-lg font-bold text-gray-800">Available Food Donations</h2>
            <a href="browse_donations.php"
                class="text-sm text-primary hover:text-green-700 font-medium hover:underline">Browse All</a>
        </div>
        <div class="p-6">
            <?php if (empty($recentDonations)): ?>
                <div class="text-center py-8">
                    <h3 class="text-lg font-medium text-gray-900">No Donations Available</h3>
                    <p class="mt-1 text-sm text-gray-500">Check back later for new food donations.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($recentDonations as $donation): ?>
                        <div
                            class="bg-white border rounded-xl overflow-hidden shadow-sm hover:shadow-md transition duration-300 flex flex-col h-full">
                            <div class="p-5 flex-grow">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="text-xl font-bold text-gray-800 truncate"
                                        title="<?php echo sanitize($donation['food_name']); ?>">
                                        <?php echo sanitize($donation['food_name']); ?></h3>
                                    <span
                                        class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-semibold">Available</span>
                                </div>
                                <span
                                    class="inline-block bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs font-medium mb-4"><?php echo sanitize($donation['food_type']); ?></span>

                                <div class="space-y-2 text-sm text-gray-600">
                                    <p class="flex items-center"><span class="font-semibold w-20">Quantity:</span>
                                        <?php echo sanitize($donation['quantity']); ?></p>
                                    <p class="flex items-center"><span class="font-semibold w-20">Location:</span>
                                        <?php echo sanitize($donation['city']); ?></p>
                                    <?php if ($donation['expiry_date']): ?>
                                        <p class="flex items-center"><span class="font-semibold w-20">Expires:</span>
                                            <?php echo date('M d, Y', strtotime($donation['expiry_date'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-5 py-3 border-t border-gray-100 flex justify-between items-center">
                                <span class="text-xs text-gray-500">By: <?php echo sanitize($donation['donor_name']); ?></span>
                                <a href="view_donation.php?id=<?php echo $donation['id']; ?>"
                                    class="text-primary hover:text-green-700 text-sm font-semibold">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- My Recent Requests Section -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h2 class="text-lg font-bold text-gray-800">My Recent Requests</h2>
            <a href="my_requests.php" class="text-sm text-primary hover:text-green-700 font-medium hover:underline">View
                All</a>
        </div>
        <div class="p-0">
            <?php if (empty($myRequests)): ?>
                <div class="p-8 text-center text-gray-500">
                    <p>You haven't made any requests yet. <a href="browse_donations.php"
                            class="text-primary hover:underline">Browse available donations</a></p>
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
                                    Donor</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($myRequests as $request): ?>
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo sanitize($request['food_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo sanitize($request['donor_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="<?php echo getStatusBadgeClass($request['status']); ?>">
                                            <?php echo ucfirst($request['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('M d, Y h:i A', strtotime($request['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>