<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

requireLogin();
if (!isDonor()) {
    redirect('charity_dashboard.php');
}

$donationId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM donations WHERE id = ? AND donor_id = ? AND status = 'available'");
$stmt->execute([$donationId, $_SESSION['user_id']]);
$donation = $stmt->fetch();

if (!$donation) {
    setFlashMessage('danger', 'Donation not found or cannot be edited.');
    redirect('my_donations.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $foodName = sanitize($_POST['food_name'] ?? '');
    $foodType = sanitize($_POST['food_type'] ?? '');
    $quantity = sanitize($_POST['quantity'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $expiryDate = $_POST['expiry_date'] ?? null;
    $pickupAddress = sanitize($_POST['pickup_address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');

    if (empty($foodName) || empty($foodType) || empty($quantity) || empty($pickupAddress) || empty($city)) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $pdo->prepare("
            UPDATE donations 
            SET food_name = ?, food_type = ?, quantity = ?, description = ?, expiry_date = ?, pickup_address = ?, city = ?
            WHERE id = ? AND donor_id = ?
        ");

        try {
            $stmt->execute([
                $foodName,
                $foodType,
                $quantity,
                $description,
                $expiryDate ?: null,
                $pickupAddress,
                $city,
                $donationId,
                $_SESSION['user_id']
            ]);
            setFlashMessage('success', 'Donation updated successfully!');
            redirect('my_donations.php');
        } catch (PDOException $e) {
            $error = 'Failed to update donation. Please try again.';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8 flex justify-center">
    <div class="w-full max-w-3xl">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                <h2 class="text-xl font-bold text-gray-800">Edit Donation</h2>
            </div>

            <div class="p-6 md:p-8">
                <?php if ($error): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" data-validate class="space-y-6">
                    <div>
                        <label for="food_name" class="block text-gray-700 text-sm font-bold mb-2">Food Name *</label>
                        <input type="text" id="food_name" name="food_name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                            required value="<?php echo sanitize($donation['food_name']); ?>">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="food_type" class="block text-gray-700 text-sm font-bold mb-2">Food Type
                                *</label>
                            <select id="food_type" name="food_type"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 bg-white"
                                required>
                                <option value="">Select Type</option>
                                <option value="Vegetarian" <?php echo $donation['food_type'] === 'Vegetarian' ? 'selected' : ''; ?>>Vegetarian</option>
                                <option value="Non-Vegetarian" <?php echo $donation['food_type'] === 'Non-Vegetarian' ? 'selected' : ''; ?>>Non-Vegetarian</option>
                                <option value="Vegan" <?php echo $donation['food_type'] === 'Vegan' ? 'selected' : ''; ?>>
                                    Vegan</option>
                                <option value="Mixed" <?php echo $donation['food_type'] === 'Mixed' ? 'selected' : ''; ?>>
                                    Mixed</option>
                                <option value="Desserts" <?php echo $donation['food_type'] === 'Desserts' ? 'selected' : ''; ?>>Desserts</option>
                                <option value="Beverages" <?php echo $donation['food_type'] === 'Beverages' ? 'selected' : ''; ?>>Beverages</option>
                                <option value="Packaged" <?php echo $donation['food_type'] === 'Packaged' ? 'selected' : ''; ?>>Packaged Food</option>
                            </select>
                        </div>

                        <div>
                            <label for="quantity" class="block text-gray-700 text-sm font-bold mb-2">Quantity *</label>
                            <input type="text" id="quantity" name="quantity"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                                required value="<?php echo sanitize($donation['quantity']); ?>">
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                        <textarea id="description" name="description"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                            rows="3"><?php echo sanitize($donation['description']); ?></textarea>
                    </div>

                    <div>
                        <label for="expiry_date" class="block text-gray-700 text-sm font-bold mb-2">Best Before
                            Date</label>
                        <input type="date" id="expiry_date" name="expiry_date"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                            value="<?php echo $donation['expiry_date']; ?>">
                    </div>

                    <div>
                        <label for="pickup_address" class="block text-gray-700 text-sm font-bold mb-2">Pickup Address
                            *</label>
                        <textarea id="pickup_address" name="pickup_address"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                            rows="2" required><?php echo sanitize($donation['pickup_address']); ?></textarea>
                    </div>

                    <div>
                        <label for="city" class="block text-gray-700 text-sm font-bold mb-2">City *</label>
                        <input type="text" id="city" name="city"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                            required value="<?php echo sanitize($donation['city']); ?>">
                    </div>

                    <div class="flex items-center gap-4 pt-4">
                        <button type="submit"
                            class="bg-primary text-white font-bold py-3 px-8 rounded-lg hover:bg-green-600 transition duration-300 shadow-md">Update
                            Donation</button>
                        <a href="my_donations.php"
                            class="text-gray-600 font-bold hover:text-gray-800 transition duration-300">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>