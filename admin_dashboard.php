<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

requireLogin();
requireAdmin();

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE user_type = 'charity' AND is_verified = FALSE");
$stmt->execute();
$pendingCharities = $stmt->fetch()['total'];

$stmt = $pdo->prepare("
    SELECT * FROM users 
    WHERE user_type = 'charity' AND is_verified = FALSE
    ORDER BY created_at ASC
");
$stmt->execute();
$pendingList = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Admin Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-yellow-500">
            <div class="text-3xl font-bold text-gray-800"><?php echo $pendingCharities; ?></div>
            <div class="text-gray-500 text-sm uppercase tracking-wide font-semibold mt-1">Charities Pending Verification
            </div>
        </div>

        <div onclick="location.href='view_verified_charities.php'"
            class="bg-white p-6 rounded-xl shadow-md border-l-4 border-green-500 cursor-pointer hover:shadow-lg hover:bg-gray-50 transition duration-300">
            <div class="text-3xl font-bold text-gray-800">
                <?php
                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE user_type = 'charity' AND is_verified = TRUE");
                $stmt->execute();
                echo $stmt->fetch()['total'];
                ?>
            </div>
            <div class="text-gray-500 text-sm uppercase tracking-wide font-semibold mt-1">Verified Charities</div>
        </div>

        <div onclick="location.href='view_donations_list.php'"
            class="bg-white p-6 rounded-xl shadow-md border-l-4 border-blue-500 cursor-pointer hover:shadow-lg hover:bg-gray-50 transition duration-300">
            <div class="text-3xl font-bold text-gray-800">
                <?php
                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM donations");
                $stmt->execute();
                echo $stmt->fetch()['total'];
                ?>
            </div>
            <div class="text-gray-500 text-sm uppercase tracking-wide font-semibold mt-1">Total Donations</div>
        </div>

        <div onclick="location.href='view_all_users.php'"
            class="bg-white p-6 rounded-xl shadow-md border-l-4 border-purple-500 cursor-pointer hover:shadow-lg hover:bg-gray-50 transition duration-300">
            <div class="text-3xl font-bold text-gray-800">
                <?php
                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE user_type != 'admin'");
                $stmt->execute();
                echo $stmt->fetch()['total'];
                ?>
            </div>
            <div class="text-gray-500 text-sm uppercase tracking-wide font-semibold mt-1">Total Users</div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 mt-8">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h2 class="text-lg font-bold text-gray-800">Charities Awaiting Verification</h2>
        </div>
        <div class="p-0">
            <?php if (empty($pendingList)): ?>
                <div class="p-8 text-center text-gray-500">
                    <p>No charities currently awaiting verification.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Organization Name</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Contact Person</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    PAN Card</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Joined</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($pendingList as $charity): ?>
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                        <?php echo sanitize($charity['organization_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <div class="font-medium text-gray-900"><?php echo sanitize($charity['name']); ?></div>
                                        <div class="text-gray-500 text-xs"><?php echo sanitize($charity['email']); ?></div>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-700 bg-gray-50 rounded px-2">
                                        <?php echo sanitize($charity['pan_card']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo formatDate($charity['created_at']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="verify_charity.php?id=<?php echo $charity['id']; ?>&action=verify"
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-200"
                                            onclick="return confirm('Are you sure you want to VERIFY this charity?')">
                                            Verify
                                        </a>
                                        <a href="verify_charity.php?id=<?php echo $charity['id']; ?>&action=reject"
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-200"
                                            onclick="return confirm('Are you sure you want to REJECT this charity? This will delete their account.')">
                                            Reject
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
</div>
<?php require_once 'includes/footer.php'; ?>