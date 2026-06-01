<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

requireLogin();
if (!isCharity()) {
    redirect('donor_dashboard.php');
}

$cityFilter = isset($_GET['city']) ? sanitize($_GET['city']) : '';
$typeFilter = isset($_GET['type']) ? sanitize($_GET['type']) : '';

$sql = "SELECT d.*, u.name as donor_name, u.phone as donor_phone, u.email as donor_email
        FROM donations d 
        JOIN users u ON d.donor_id = u.id 
        WHERE d.status = 'available'";
$params = [];

if ($cityFilter) {
    $sql .= " AND LOWER(d.city) LIKE LOWER(?)";
    $params[] = "%$cityFilter%";
}

if ($typeFilter) {
    $sql .= " AND d.food_type = ?";
    $params[] = $typeFilter;
}

$sql .= " ORDER BY d.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$donations = $stmt->fetchAll();

$stmt = $pdo->query("SELECT DISTINCT city FROM donations WHERE status = 'available' ORDER BY city");
$cities = $stmt->fetchAll(PDO::FETCH_COLUMN);

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Browse Available Donations</h1>

    <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 mb-8">
        <form method="GET" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1 w-full">
                <label for="city" class="block text-gray-700 text-sm font-bold mb-2">City</label>
                <input type="text" id="city" name="city"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                    placeholder="Search by city" value="<?php echo $cityFilter; ?>">
            </div>
            <div class="flex-1 w-full">
                <label for="type" class="block text-gray-700 text-sm font-bold mb-2">Food Type</label>
                <select id="type" name="type"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 bg-white">
                    <option value="">All Types</option>
                    <option value="Vegetarian" <?php echo $typeFilter === 'Vegetarian' ? 'selected' : ''; ?>>Vegetarian
                    </option>
                    <option value="Non-Vegetarian" <?php echo $typeFilter === 'Non-Vegetarian' ? 'selected' : ''; ?>>
                        Non-Vegetarian</option>
                    <option value="Vegan" <?php echo $typeFilter === 'Vegan' ? 'selected' : ''; ?>>Vegan</option>
                    <option value="Mixed" <?php echo $typeFilter === 'Mixed' ? 'selected' : ''; ?>>Mixed</option>
                    <option value="Desserts" <?php echo $typeFilter === 'Desserts' ? 'selected' : ''; ?>>Desserts</option>
                    <option value="Beverages" <?php echo $typeFilter === 'Beverages' ? 'selected' : ''; ?>>Beverages
                    </option>
                    <option value="Packaged" <?php echo $typeFilter === 'Packaged' ? 'selected' : ''; ?>>Packaged Food
                    </option>
                </select>
            </div>
            <div class="flex gap-2 w-full md:w-auto mt-4 md:mt-0">
                <button type="submit"
                    class="flex-1 md:flex-none bg-primary text-white font-bold py-2 px-6 rounded-lg hover:bg-green-600 transition duration-300 shadow-sm">Filter</button>
                <?php if ($cityFilter || $typeFilter): ?>
                    <a href="browse_donations.php"
                        class="flex-1 md:flex-none bg-white border border-gray-300 text-gray-700 font-bold py-2 px-6 rounded-lg hover:bg-gray-50 transition duration-300 text-center">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if (empty($donations)): ?>
        <div class="bg-white rounded-xl shadow p-12 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">No Donations Available</h3>
            <p class="text-gray-500 mb-6">There are no food donations matching your criteria at the moment.</p>
            <?php if ($cityFilter || $typeFilter): ?>
                <a href="browse_donations.php"
                    class="inline-block bg-primary text-white font-bold py-2 px-6 rounded-lg hover:bg-green-600 transition duration-300">View
                    All Donations</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p class="mb-6 text-gray-500 font-medium">Found <?php echo count($donations); ?> donation(s)</p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($donations as $donation): ?>
                <div
                    class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition duration-300 flex flex-col h-full">

                    <?php if ($donation['image_path']): // ADDED: Image thumbnail ?>
                        <div class="h-48 overflow-hidden relative group">
                            <img src="<?php echo sanitize($donation['image_path']); ?>"
                                alt="<?php echo sanitize($donation['food_name']); ?>"
                                class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                            <div class="absolute top-0 right-0 m-4">
                                <span
                                    class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-semibold shadow-sm">Available</span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="p-6 flex-grow">
                        <?php if (!$donation['image_path']): ?>
                            <div class="flex justify-between items-start mb-4">
                                <span
                                    class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-semibold">Available</span>
                            </div>
                        <?php endif; ?>

                        <h3 class="text-xl font-bold text-gray-800 mb-2 truncate"
                            title="<?php echo sanitize($donation['food_name']); ?>">
                            <?php echo sanitize($donation['food_name']); ?></h3>
                        <span
                            class="inline-block bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-medium mb-4"><?php echo sanitize($donation['food_type']); ?></span>

                        <div class="space-y-2 text-sm text-gray-600 mb-4">
                            <p class="flex items-center"><span class="font-semibold w-20">Quantity:</span>
                                <?php echo sanitize($donation['quantity']); ?></p>
                            <p class="flex items-center"><span class="font-semibold w-20">Location:</span>
                                <?php echo sanitize($donation['city']); ?></p>
                            <?php if ($donation['expiry_date']): ?>
                                <p class="flex items-center"><span class="font-semibold w-20">Expires:</span>
                                    <?php echo formatDate($donation['expiry_date']); ?></p>
                            <?php endif; ?>
                        </div>

                        <?php if ($donation['description']): ?>
                            <p class="text-gray-500 text-sm line-clamp-2">
                                <?php echo substr(sanitize($donation['description']), 0, 100); ?>...
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-between items-center mt-auto">
                        <div class="flex flex-col">
                            <span class="text-xs text-gray-500 uppercase font-semibold">Donor</span>
                            <span class="text-sm font-medium text-gray-700 truncate max-w-[120px]"
                                title="<?php echo sanitize($donation['donor_name']); ?>"><?php echo sanitize($donation['donor_name']); ?></span>
                        </div>
                        <a href="view_donation.php?id=<?php echo $donation['id']; ?>"
                            class="bg-white border border-primary text-primary hover:bg-primary hover:text-white text-sm font-bold py-2 px-4 rounded-full transition duration-300">View
                            & Request</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>