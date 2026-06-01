<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

requireLogin();

$donationId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT d.*, u.name as donor_name, u.phone as donor_phone, u.email as donor_email, u.address as donor_address
    FROM donations d 
    JOIN users u ON d.donor_id = u.id 
    WHERE d.id = ?
");
$stmt->execute([$donationId]);
$donation = $stmt->fetch();

if (!$donation) {
    setFlashMessage('danger', 'Donation not found.');
    redirect(isCharity() ? 'browse_donations.php' : 'my_donations.php');
}

$hasRequested = false;
if (isCharity()) {
    $stmt = $pdo->prepare("SELECT id FROM requests WHERE donation_id = ? AND charity_id = ?");
    $stmt->execute([$donationId, $_SESSION['user_id']]);
    $hasRequested = $stmt->fetch() !== false;
}

$error = '';

// UPDATED: Check if the user is a verified charity before allowing the POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isCharity() && isVerifiedCharity() && !$hasRequested && $donation['status'] === 'available') {
    $message = sanitize($_POST['message'] ?? '');

    $stmt = $pdo->prepare("INSERT INTO requests (donation_id, charity_id, message) VALUES (?, ?, ?)");

    try {
        $stmt->execute([$donationId, $_SESSION['user_id'], $message]);

        $stmt = $pdo->prepare("UPDATE donations SET status = 'requested' WHERE id = ? AND status = 'available'");
        $stmt->execute([$donationId]);

        setFlashMessage('success', 'Request sent successfully! The donor will review your request.');
        redirect('my_requests.php');
    } catch (PDOException $e) {
        $error = 'Failed to send request. Please try again.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isCharity() && $donation['status'] !== 'available') {
    $error = 'This donation is no longer available for requests.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isCharity() && !isVerifiedCharity()) {
    $error = 'Your account is pending verification. You cannot make requests yet.';
}

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 mb-8">
                <?php if ($donation['image_path']): // ADDED: Display Image ?>
                    <div class="w-full h-64 sm:h-80 md:h-96 relative bg-gray-100">
                        <img src="<?php echo sanitize($donation['image_path']); ?>"
                            alt="<?php echo sanitize($donation['food_name']); ?>" class="w-full h-full object-cover">
                    </div>
                <?php endif; ?>

                <div class="p-6 md:p-8">
                    <div class="flex justify-between items-start mb-6">
                        <h1 class="text-3xl font-bold text-gray-800"><?php echo sanitize($donation['food_name']); ?>
                        </h1>
                        <span
                            class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold whitespace-nowrap ml-4">
                            <?php echo sanitize($donation['food_type']); ?>
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                        <div>
                            <h4 class="text-gray-500 text-sm font-semibold uppercase tracking-wide mb-1">Quantity</h4>
                            <p class="text-lg font-medium text-gray-900"><?php echo sanitize($donation['quantity']); ?>
                            </p>
                        </div>
                        <div>
                            <h4 class="text-gray-500 text-sm font-semibold uppercase tracking-wide mb-1">City</h4>
                            <p class="text-lg font-medium text-gray-900"><?php echo sanitize($donation['city']); ?></p>
                        </div>
                        <?php if ($donation['expiry_date']): ?>
                            <div>
                                <h4 class="text-gray-500 text-sm font-semibold uppercase tracking-wide mb-1">Best Before
                                </h4>
                                <p class="text-lg font-medium text-gray-900">
                                    <?php echo formatDate($donation['expiry_date']); ?></p>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h4 class="text-gray-500 text-sm font-semibold uppercase tracking-wide mb-1">Posted On</h4>
                            <p class="text-lg font-medium text-gray-900">
                                <?php echo date('M d, Y h:i A', strtotime($donation['created_at'])); ?></p>
                        </div>
                    </div>

                    <?php if ($donation['description']): ?>
                        <div class="mb-8">
                            <h4 class="text-gray-800 font-bold mb-2">Description</h4>
                            <p class="text-gray-600 leading-relaxed">
                                <?php echo nl2br(sanitize($donation['description'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                        <h4 class="text-gray-800 font-bold mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Pickup Address
                        </h4>
                        <p class="text-gray-600"><?php echo nl2br(sanitize($donation['pickup_address'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 sticky top-24">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">Donor Information</h3>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <p class="text-gray-500 text-sm mb-1">Donor Name</p>
                        <p class="text-gray-900 font-medium text-lg"><?php echo sanitize($donation['donor_name']); ?>
                        </p>
                    </div>

                    <?php if ($donation['status'] === 'requested' || $donation['status'] === 'completed' || isDonor()): ?>
                        <div class="mb-4">
                            <p class="text-gray-500 text-sm mb-1">Phone</p>
                            <p class="text-gray-900"><?php echo sanitize($donation['donor_phone']) ?: 'Not provided'; ?></p>
                        </div>
                        <div class="mb-4">
                            <p class="text-gray-500 text-sm mb-1">Email</p>
                            <p class="text-gray-900 break-all"><?php echo sanitize($donation['donor_email']); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 text-sm text-blue-800 mb-4">
                            <p class="flex gap-2">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Contact details will be visible after your request is accepted.
                            </p>
                        </div>
                    <?php endif; ?>

                    <hr class="border-gray-100 my-6">

                    <?php if (isCharity() && $donation['status'] === 'available'): ?>
                        <?php if ($hasRequested): ?>
                            <div class="bg-blue-100 text-blue-800 px-4 py-3 rounded-lg text-center font-medium">
                                You have already requested this donation.
                            </div>
                        <?php else: ?>
                            <form method="POST">
                                <div class="mb-4">
                                    <label for="message" class="block text-gray-700 text-sm font-bold mb-2">Message to Donor
                                        (Optional)</label>
                                    <textarea id="message" name="message"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 text-sm"
                                        rows="4"
                                        placeholder="Introduce your organization and explain why you need this donation..."></textarea>
                                </div>
                                <button type="submit"
                                    class="w-full bg-primary text-white font-bold py-3 px-6 rounded-lg hover:bg-green-600 transition duration-300 shadow-md">Request
                                    This Donation</button>
                            </form>
                        <?php endif; ?>
                    <?php elseif (isDonor() && $donation['donor_id'] == $_SESSION['user_id'] && $donation['status'] === 'available'): ?>
                        <div class="flex flex-col gap-3">
                            <a href="edit_donation.php?id=<?php echo $donation['id']; ?>"
                                class="w-full bg-white border border-gray-300 text-gray-700 font-bold py-2 px-6 rounded-lg hover:bg-gray-50 transition duration-300 text-center">Edit
                                Donation</a>
                            <a href="delete_donation.php?id=<?php echo $donation['id']; ?>"
                                class="w-full bg-red-50 border border-red-200 text-red-700 font-bold py-2 px-6 rounded-lg hover:bg-red-100 transition duration-300 text-center"
                                onclick="return confirm('Are you sure you want to delete this donation?')">Delete
                                Donation</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>