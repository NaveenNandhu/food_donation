<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

requireLogin();

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $organizationName = sanitize($_POST['organization_name'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($name)) {
        $error = 'Name is required.';
    } elseif ($user['user_type'] === 'charity' && empty($organizationName)) {
        $error = 'Organization name is required.';
    } else {
        if (!empty($newPassword)) {
            if (empty($currentPassword)) {
                $error = 'Current password is required to change password.';
            } elseif (!password_verify($currentPassword, $user['password'])) {
                $error = 'Current password is incorrect.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'New passwords do not match.';
            } elseif (strlen($newPassword) < 6) {
                $error = 'New password must be at least 6 characters.';
            }
        }

        if (empty($error)) {
            $sql = "UPDATE users SET name = ?, phone = ?, address = ?, city = ?, organization_name = ?";
            $params = [$name, $phone, $address, $city, $organizationName];

            if (!empty($newPassword)) {
                $sql .= ", password = ?";
                $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id = ?";
            $params[] = $userId;

            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                $_SESSION['user_name'] = $name;

                setFlashMessage('success', 'Profile updated successfully!');
                redirect('profile.php');
            } catch (PDOException $e) {
                $error = 'Failed to update profile. Please try again.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Profile Settings -->
        <div class="w-full lg:w-2/3">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800">My Profile</h2>
                </div>
                <div class="p-6 md:p-8">
                    <?php if ($error): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email
                                    Address</label>
                                <input type="email" id="email"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed"
                                    value="<?php echo sanitize($user['email']); ?>" disabled>
                                <p class="text-xs text-gray-500 mt-1">Email cannot be changed</p>
                            </div>

                            <div>
                                <label for="user_type" class="block text-gray-700 text-sm font-bold mb-2">Account
                                    Type</label>
                                <input type="text" id="user_type"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed"
                                    value="<?php echo ucfirst($user['user_type']); ?>" disabled>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Full Name *</label>
                            <input type="text" id="name" name="name"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                                required value="<?php echo sanitize($user['name']); ?>">
                        </div>

                        <?php if ($user['user_type'] === 'charity'): ?>
                            <div class="mb-6">
                                <label for="organization_name"
                                    class="block text-gray-700 text-sm font-bold mb-2">Organization Name *</label>
                                <input type="text" id="organization_name" name="organization_name"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                                    required value="<?php echo sanitize($user['organization_name']); ?>">
                            </div>
                        <?php endif; ?>

                        <div class="mb-6">
                            <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Phone Number</label>
                            <input type="tel" id="phone" name="phone"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                                value="<?php echo sanitize($user['phone']); ?>">
                        </div>

                        <div class="mb-6">
                            <label for="address" class="block text-gray-700 text-sm font-bold mb-2">Address</label>
                            <textarea id="address" name="address"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                                rows="2"><?php echo sanitize($user['address']); ?></textarea>
                        </div>

                        <div class="mb-8">
                            <label for="city" class="block text-gray-700 text-sm font-bold mb-2">City</label>
                            <input type="text" id="city" name="city"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                                value="<?php echo sanitize($user['city']); ?>">
                        </div>

                        <hr class="border-gray-200 my-8">

                        <h4 class="text-lg font-bold text-gray-800 mb-2">Change Password</h4>
                        <p class="text-gray-500 text-sm mb-6">Leave blank if you don't want to change your password</p>

                        <div class="mb-6">
                            <label for="current_password" class="block text-gray-700 text-sm font-bold mb-2">Current
                                Password</label>
                            <input type="password" id="current_password" name="current_password"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div>
                                <label for="new_password" class="block text-gray-700 text-sm font-bold mb-2">New
                                    Password</label>
                                <input type="password" id="new_password" name="new_password"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                                    minlength="6">
                            </div>

                            <div>
                                <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirm
                                    New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full md:w-auto bg-primary text-white font-bold py-3 px-8 rounded-lg hover:bg-green-600 transition duration-300 shadow-md">Update
                            Profile</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Account Statistics -->
        <div class="w-full lg:w-1/3">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 sticky top-24">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">Account Statistics</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php if ($user['user_type'] === 'donor'): ?>
                            <?php
                            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM donations WHERE donor_id = ?");
                            $stmt->execute([$userId]);
                            $totalDonations = $stmt->fetch()['total'];

                            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM donations WHERE donor_id = ? AND status = 'completed'");
                            $stmt->execute([$userId]);
                            $completedDonations = $stmt->fetch()['total'];
                            ?>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-600">Total Donations Posted</span>
                                <span class="font-bold text-gray-800 text-lg"><?php echo $totalDonations; ?></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-600">Donations Completed</span>
                                <span class="font-bold text-green-600 text-lg"><?php echo $completedDonations; ?></span>
                            </div>
                        <?php else: ?>
                            <?php
                            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM requests WHERE charity_id = ?");
                            $stmt->execute([$userId]);
                            $totalRequests = $stmt->fetch()['total'];

                            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM requests WHERE charity_id = ? AND status = 'completed'");
                            $stmt->execute([$userId]);
                            $completedRequests = $stmt->fetch()['total'];
                            ?>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-600">Total Requests Made</span>
                                <span class="font-bold text-gray-800 text-lg"><?php echo $totalRequests; ?></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-600">Donations Received</span>
                                <span class="font-bold text-green-600 text-lg"><?php echo $completedRequests; ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="flex justify-between items-center py-2 pt-4">
                            <span class="text-gray-500 text-sm">Member Since</span>
                            <span
                                class="text-gray-700 font-medium text-sm"><?php echo formatDate($user['created_at']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>