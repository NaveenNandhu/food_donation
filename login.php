<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

if (isLoggedIn()) {
    $dashboard = isDonor() ? 'donor_dashboard.php' : (isAdmin() ? 'admin_dashboard.php' : 'charity_dashboard.php');
    redirect($dashboard);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT id, name, email, user_type, password, is_verified FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            setSessionData([
                'user_id' => $user['id'],
                'user_name' => $user['name'],
                'user_email' => $user['email'],
                'user_type' => $user['user_type'],
                'is_verified' => (bool) $user['is_verified']
            ]);

            setFlashMessage('success', 'Welcome back, ' . $user['name'] . '!');

            if ($user['user_type'] === 'donor') {
                redirect('donor_dashboard.php');
            } elseif ($user['user_type'] === 'admin') {
                redirect('admin_dashboard.php');
            } else { // charity
                redirect('charity_dashboard.php');
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-12 flex justify-center">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 text-center">
                <h2 class="text-2xl font-bold text-gray-800">Welcome Back</h2>
                <p class="text-gray-600 mt-1">Login to your FoodShare account</p>
            </div>
            <div class="p-8">
                <?php if ($error): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" data-validate>
                    <div class="mb-5">
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email Address</label>
                        <input type="email" id="email" name="email"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                            required value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>">
                    </div>

                    <div class="mb-6">
                        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                        <input type="password" id="password" name="password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                            required>
                    </div>

                    <button type="submit"
                        class="w-full bg-primary text-white font-bold py-3 px-4 rounded-lg hover:bg-green-600 transition duration-300 transform hover:-translate-y-0.5 shadow-md">Login</button>
                </form>

                <div class="mt-6 text-center border-t border-gray-100 pt-6">
                    <p class="text-gray-600">Don't have an account? <a href="register.php"
                            class="text-primary font-bold hover:underline">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>