<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

requireLogin();
if (!isCharity()) {
    redirect('donor_dashboard.php');
}

$userId = $_SESSION['user_id'];
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$sql = "SELECT r.*, d.food_name, d.food_type, d.quantity, d.city, d.pickup_address,
               u.name as donor_name, u.phone as donor_phone, u.email as donor_email
        FROM requests r
        JOIN donations d ON r.donation_id = d.id
        JOIN users u ON d.donor_id = u.id
        WHERE r.charity_id = ?";
$params = [$userId];

if ($statusFilter && in_array($statusFilter, ['pending', 'accepted', 'rejected', 'completed'])) {
    $sql .= " AND r.status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY r.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">My Requests</h1>

    <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 mb-8">
        <form method="GET" class="flex flex-col md:flex-row items-end gap-4">
            <div class="w-full md:w-64">
                <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Filter by Status</label>
                <select id="status" name="status"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 bg-white"
                    onchange="this.form.submit()">
                    <option value="">All Requests</option>
                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="accepted" <?php echo $statusFilter === 'accepted' ? 'selected' : ''; ?>>Accepted
                    </option>
                    <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected
                    </option>
                    <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed
                    </option>
                </select>
            </div>
        </form>
    </div>

    <?php if (empty($requests)): ?>
        <div class="bg-white rounded-xl shadow p-12 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                    </path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">No Requests Found</h3>
            <p class="text-gray-500 mb-6">You haven't made any requests yet.</p>
            <a href="browse_donations.php"
                class="inline-block bg-primary text-white font-bold py-2 px-6 rounded-lg hover:bg-green-600 transition duration-300">Browse
                Available Donations</a>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Food
                                Item</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantity</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Location</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Donor
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Requested On</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($requests as $request): ?>
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900"><?php echo sanitize($request['food_name']); ?>
                                    </div>
                                    <div class="text-xs text-gray-500"><?php echo sanitize($request['food_type']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo sanitize($request['quantity']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo sanitize($request['city']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo sanitize($request['donor_name']); ?></div>
                                    <?php if ($request['status'] === 'accepted'): ?>
                                        <div class="text-xs text-gray-500"><?php echo sanitize($request['donor_phone']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="<?php echo getStatusBadgeClass($request['status']); ?>">
                                        <?php echo ucfirst($request['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M d, Y h:i A', strtotime($request['created_at'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <?php if ($request['status'] === 'accepted'): ?>
                                        <a href="complete_pickup.php?id=<?php echo $request['id']; ?>"
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full shadow-sm text-white bg-primary hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-200"
                                            onclick="return confirm('Confirm that you have picked up this donation?')">
                                            Mark Picked Up
                                        </a>
                                    <?php else: ?>
                                        <a href="view_donation.php?id=<?php echo $request['donation_id']; ?>"
                                            class="text-primary hover:text-green-900">View</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>