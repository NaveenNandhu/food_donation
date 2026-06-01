<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

if (isLoggedIn()) {
    $dashboard = isDonor() ? 'donor_dashboard.php' : (isAdmin() ? 'admin_dashboard.php' : 'charity_dashboard.php');
    redirect($dashboard);
}

$error = '';
$userType = isset($_GET['type']) && $_GET['type'] === 'charity' ? 'charity' : 'donor';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $userType = sanitize($_POST['user_type'] ?? 'donor');
    $organizationName = sanitize($_POST['organization_name'] ?? '');
    $panCard = sanitize($_POST['pan_card'] ?? ''); // ADDED: pan_card

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($userType === 'charity' && (empty($organizationName) || empty($panCard))) { // UPDATED: Check for panCard
        $error = 'Organization name and PAN Card are required for charities.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Set verification status
            $isVerified = ($userType === 'donor' || $userType === 'admin') ? 1 : 0; // Donors and Admin are immediately verified for simplicity, Charities are not.

            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, phone, address, city, user_type, organization_name, pan_card, is_verified)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            try {
                $stmt->execute([
                    $name,
                    $email,
                    $hashedPassword,
                    $phone,
                    $address,
                    $city,
                    $userType,
                    $organizationName,
                    $panCard,
                    $isVerified
                ]);

                $message = ($userType === 'charity')
                    ? 'Registration successful! Your account is pending admin verification. You will be able to make requests once verified.'
                    : 'Registration successful! Please login.';

                setFlashMessage('success', $message);
                redirect('login.php');
            } catch (PDOException $e) {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-12 flex justify-center">
    <div class="w-full max-w-2xl">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 text-center">
                <h2 class="text-2xl font-bold text-gray-800">Create Account</h2>
                <p class="text-gray-600 mt-1">Join FoodShare as a
                    <?php echo $userType === 'charity' ? 'Charity' : 'Donor'; ?></p>
            </div>
            <div class="p-8">
                <?php if ($error): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="flex gap-4 mb-8">
                    <a href="?type=donor"
                        class="flex-1 text-center py-3 rounded-lg font-bold transition duration-300 border-2 <?php echo $userType === 'donor' ? 'bg-primary border-primary text-white shadow-md' : 'border-gray-300 text-gray-500 hover:border-primary hover:text-primary'; ?>">I'm
                        a Donor</a>
                    <a href="?type=charity"
                        class="flex-1 text-center py-3 rounded-lg font-bold transition duration-300 border-2 <?php echo $userType === 'charity' ? 'bg-primary border-primary text-white shadow-md' : 'border-gray-300 text-gray-500 hover:border-primary hover:text-primary'; ?>">I'm
                        a Charity</a>
                </div>

                <form method="POST" data-validate>
                    <input type="hidden" name="user_type" value="<?php echo $userType; ?>">

                    <div class="mb-5">
                        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Full Name *</label>
                        <input type="text" id="name" name="name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                            required value="<?php echo isset($_POST['name']) ? sanitize($_POST['name']) : ''; ?>">
                    </div>

                    <?php if ($userType === 'charity'): ?>
                        <div class="mb-5">
                            <label for="organization_name" class="block text-gray-700 text-sm font-bold mb-2">Organization
                                Name *</label>
                            <input type="text" id="organization_name" name="organization_name"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                                required
                                value="<?php echo isset($_POST['organization_name']) ? sanitize($_POST['organization_name']) : ''; ?>">
                        </div>
                        <div class="mb-5">
                            <label for="pan_card" class="block text-gray-700 text-sm font-bold mb-2">Organization PAN Card
                                Number *</label>
                            <input type="text" id="pan_card" name="pan_card"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                                required
                                value="<?php echo isset($_POST['pan_card']) ? sanitize($_POST['pan_card']) : ''; ?>">
                            <p class="text-xs text-gray-500 mt-1">Required for verification purposes.</p>
                        </div>
                    <?php endif; ?>

                    <div class="mb-5">
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email Address *</label>
                        <input type="email" id="email" name="email"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                            required value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>">
                    </div>

                    <div class="mb-5">
                        <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                            value="<?php echo isset($_POST['phone']) ? sanitize($_POST['phone']) : ''; ?>">
                    </div>

                    <div class="mb-5">
                        <label for="address" class="block text-gray-700 text-sm font-bold mb-2">Address</label>
                        <textarea id="address" name="address"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                            rows="2"><?php echo isset($_POST['address']) ? sanitize($_POST['address']) : ''; ?></textarea>
                    </div>

                    <div class="mb-5">
                        <label for="city" class="block text-gray-700 text-sm font-bold mb-2">City</label>
                        <input type="text" id="city" name="city"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                            value="<?php echo isset($_POST['city']) ? sanitize($_POST['city']) : ''; ?>">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password *</label>
                            <input type="password" id="password" name="password"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                                required minlength="6">
                        </div>
                        <div>
                            <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirm
                                Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                                required>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-primary text-white font-bold py-3 px-4 rounded-lg hover:bg-green-600 transition duration-300 transform hover:-translate-y-0.5 shadow-md">Register</button>
                </form>

                <div class="mt-6 text-center border-t border-gray-100 pt-6">
                    <p class="text-gray-600">Already have an account? <a href="login.php"
                            class="text-primary font-bold hover:underline">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>