<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

requireLogin();
if (!isDonor()) {
    redirect('charity_dashboard.php');
}

$error = '';
$success = '';
$imagePath = null;

$stmt = $pdo->prepare("SELECT address, city FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userInfo = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $foodName = sanitize($_POST['food_name'] ?? '');
    $foodType = sanitize($_POST['food_type'] ?? '');
    $quantity = sanitize($_POST['quantity'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $expiryDate = $_POST['expiry_date'] ?? null;
    $pickupAddress = sanitize($_POST['pickup_address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');

    // --- Image Upload Logic Start ---
    if (isset($_FILES['food_image']) && $_FILES['food_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['food_image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxFileSize = 3 * 1024 * 1024; // 3MB limit for Base64 storage

        if (!in_array($file['type'], $allowedTypes)) {
            $error = 'Invalid image file type. Only JPEG, PNG, GIF, and WEBP are allowed.';
        } elseif ($file['size'] > $maxFileSize) {
            $error = 'Image file size must be less than 3MB.';
        } else {
            // Read file and convert to Base64 for Serverless/Vercel compatibility
            $imageData = file_get_contents($file['tmp_name']);
            $base64 = base64_encode($imageData);
            $mimeType = $file['type'];
            
            // Format as a data URI so it can be used directly in <img src="...">
            $imagePath = 'data:' . $mimeType . ';base64,' . $base64;
        }
    }
    // --- Image Upload Logic End ---

    if (empty($foodName) || empty($foodType) || empty($quantity) || empty($pickupAddress) || empty($city)) {
        $error = 'Please fill in all required fields.';
    }

    // Only proceed to DB if no errors
    if (empty($error)) {
        $stmt = $pdo->prepare("
            INSERT INTO donations (donor_id, food_name, food_type, quantity, description, expiry_date, pickup_address, city, image_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        try {
            $stmt->execute([
                $_SESSION['user_id'],
                $foodName,
                $foodType,
                $quantity,
                $description,
                $expiryDate ?: null,
                $pickupAddress,
                $city,
                $imagePath
            ]);
            setFlashMessage('success', 'Donation added successfully!');
            redirect('my_donations.php');
        } catch (PDOException $e) {
            $error = 'Failed to add donation. Please try again.';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8 flex justify-center">
    <div class="w-full max-w-3xl">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Add New Donation</h2>
                    <p class="text-sm text-gray-500 mt-1">Share your surplus food with those in need</p>
                </div>
            </div>
            <div class="p-6 md:p-8">
                <?php if ($error): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" data-validate enctype="multipart/form-data">
                    <div class="mb-6">
                        <label for="food_name" class="block text-gray-700 text-sm font-bold mb-2">Food Name *</label>
                        <input type="text" id="food_name" name="food_name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                            required placeholder="e.g., Biryani, Vegetable Curry, Rice"
                            value="<?php echo isset($_POST['food_name']) ? sanitize($_POST['food_name']) : ''; ?>">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="food_type" class="block text-gray-700 text-sm font-bold mb-2">Food Type
                                *</label>
                            <select id="food_type" name="food_type"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 bg-white"
                                required>
                                <option value="">Select Type</option>
                                <option value="Vegetarian" <?php echo (isset($_POST['food_type']) && $_POST['food_type'] === 'Vegetarian') ? 'selected' : ''; ?>>Vegetarian</option>
                                <option value="Non-Vegetarian" <?php echo (isset($_POST['food_type']) && $_POST['food_type'] === 'Non-Vegetarian') ? 'selected' : ''; ?>>Non-Vegetarian
                                </option>
                                <option value="Vegan" <?php echo (isset($_POST['food_type']) && $_POST['food_type'] === 'Vegan') ? 'selected' : ''; ?>>Vegan</option>
                                <option value="Mixed" <?php echo (isset($_POST['food_type']) && $_POST['food_type'] === 'Mixed') ? 'selected' : ''; ?>>Mixed</option>
                                <option value="Desserts" <?php echo (isset($_POST['food_type']) && $_POST['food_type'] === 'Desserts') ? 'selected' : ''; ?>>Desserts</option>
                                <option value="Beverages" <?php echo (isset($_POST['food_type']) && $_POST['food_type'] === 'Beverages') ? 'selected' : ''; ?>>Beverages</option>
                                <option value="Packaged" <?php echo (isset($_POST['food_type']) && $_POST['food_type'] === 'Packaged') ? 'selected' : ''; ?>>Packaged Food</option>
                            </select>
                        </div>

                        <div>
                            <label for="quantity" class="block text-gray-700 text-sm font-bold mb-2">Quantity *</label>
                            <input type="text" id="quantity" name="quantity"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                                required placeholder="e.g., 20 servings, 5 kg, 10 packets"
                                value="<?php echo isset($_POST['quantity']) ? sanitize($_POST['quantity']) : ''; ?>">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="food_image" class="block text-gray-700 text-sm font-bold mb-2">Food Image
                            (Optional)</label>
                        <input type="file" id="food_image" name="food_image"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 bg-gray-50 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-green-600"
                            accept="image/*">
                        <p class="text-xs text-gray-500 mt-1">Max 5MB. JPEG, PNG, or GIF only.</p>
                    </div>

                    <div class="mb-6">
                        <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                        <textarea id="description" name="description"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                            rows="3"
                            placeholder="Provide additional details about the food..."><?php echo isset($_POST['description']) ? sanitize($_POST['description']) : ''; ?></textarea>
                    </div>

                    <div class="mb-6">
                        <label for="expiry_date" class="block text-gray-700 text-sm font-bold mb-2">Best Before
                            Date</label>
                        <input type="date" id="expiry_date" name="expiry_date"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                            value="<?php echo isset($_POST['expiry_date']) ? $_POST['expiry_date'] : ''; ?>">
                        <p class="text-xs text-gray-500 mt-1">When should this food be consumed by?</p>
                    </div>

                    <div class="mb-6">
                        <label for="pickup_address" class="block text-gray-700 text-sm font-bold mb-2">Pickup Address
                            *</label>
                        <textarea id="pickup_address" name="pickup_address"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                            rows="2" required
                            placeholder="Where can the food be picked up from?"><?php echo isset($_POST['pickup_address']) ? sanitize($_POST['pickup_address']) : ($userInfo['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-8">
                        <label for="city" class="block text-gray-700 text-sm font-bold mb-2">City *</label>
                        <input type="text" id="city" name="city"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                            required placeholder="Enter city name"
                            value="<?php echo isset($_POST['city']) ? sanitize($_POST['city']) : ($userInfo['city'] ?? ''); ?>">
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit"
                            class="bg-primary text-white font-bold py-3 px-6 rounded-lg hover:bg-green-600 transition duration-300 shadow-md">Add
                            Donation</button>
                        <a href="my_donations.php"
                            class="bg-white border border-gray-300 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-50 transition duration-300">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>