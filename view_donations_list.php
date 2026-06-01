<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

requireLogin();
requireAdmin();

// Fetch all donations with donor details
$stmt = $pdo->prepare("
    SELECT d.*, 
           u.name as donor_name, 
           u.email as donor_email,
           u.phone as donor_phone,
           u.organization_name
    FROM donations d 
    JOIN users u ON d.donor_id = u.id 
    ORDER BY d.created_at DESC
");
$stmt->execute();
$donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">All Donations</h1>
        <a href="admin_dashboard.php"
            class="bg-gray-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-gray-600 transition duration-300 shadow-sm">Back
            to Dashboard</a>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h2 class="text-xl font-bold text-gray-800">Detailed Donation List</h2>
        </div>

        <?php if (empty($donations)): ?>
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                        </path>
                    </svg>
                </div>
                <p class="text-gray-500 text-lg">No donations found.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Donor
                                Details</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Food
                                Item</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantity/Type</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pickup Info</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($donations as $donation): ?>
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">
                                        <?php echo sanitize($donation['donor_name']); ?></div>
                                    <?php if (!empty($donation['organization_name'])): ?>
                                        <div class="text-xs text-gray-500 italic mb-1">
                                            <?php echo sanitize($donation['organization_name']); ?></div>
                                    <?php endif; ?>
                                    <div class="text-sm text-gray-500"><?php echo sanitize($donation['donor_email']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo sanitize($donation['donor_phone']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo sanitize($donation['food_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo sanitize($donation['quantity']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo sanitize($donation['food_type']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 line-clamp-2"
                                        title="<?php echo sanitize($donation['pickup_address']); ?>">
                                        <?php echo sanitize($donation['pickup_address']); ?></div>
                                    <div class="text-xs text-gray-500 font-medium mt-1">
                                        <?php echo sanitize($donation['city']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="<?php echo getStatusBadgeClass($donation['status']); ?> px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        <?php echo ucfirst($donation['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M d, Y', strtotime($donation['created_at'])); ?><br>
                                    <span
                                        class="text-xs"><?php echo date('h:i A', strtotime($donation['created_at'])); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a href="view_donation.php?id=<?php echo $donation['id']; ?>"
                                        class="text-primary hover:text-green-900 transition duration-200">View</a>
                                    <a href="delete_donation.php?id=<?php echo $donation['id']; ?>"
                                        class="text-red-500 hover:text-red-900 transition duration-200"
                                        onclick="return confirm('Are you sure you want to delete this donation? This action cannot be undone.')">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>