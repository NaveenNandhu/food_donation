<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

requireLogin();
if (!isDonor()) {
    redirect('charity_dashboard.php');
}

$userId = $_SESSION['user_id'];
$donationFilter = isset($_GET['donation_id']) ? (int) $_GET['donation_id'] : 0;

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $requestId = (int) $_GET['id'];

    $stmt = $pdo->prepare("
        SELECT r.*, d.id as donation_id 
        FROM requests r 
        JOIN donations d ON r.donation_id = d.id 
        WHERE r.id = ? AND d.donor_id = ?
    ");
    $stmt->execute([$requestId, $userId]);
    $request = $stmt->fetch();

    if ($request) {
        if ($action === 'accept') {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("UPDATE requests SET status = 'accepted' WHERE id = ?");
                $stmt->execute([$requestId]);

                $stmt = $pdo->prepare("UPDATE requests SET status = 'rejected' WHERE donation_id = ? AND id != ? AND status = 'pending'");
                $stmt->execute([$request['donation_id'], $requestId]);

                $stmt = $pdo->prepare("UPDATE donations SET status = 'requested' WHERE id = ?");
                $stmt->execute([$request['donation_id']]);

                $pdo->commit();
                setFlashMessage('success', 'Request accepted successfully!');
            } catch (Exception $e) {
                $pdo->rollBack();
                setFlashMessage('danger', 'Failed to accept request.');
            }
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE requests SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$requestId]);

            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM requests WHERE donation_id = ? AND status IN ('pending', 'accepted')");
            $stmt->execute([$request['donation_id']]);
            $activeCount = $stmt->fetch()['count'];

            if ($activeCount == 0) {
                $stmt = $pdo->prepare("UPDATE donations SET status = 'available' WHERE id = ? AND status = 'requested'");
                $stmt->execute([$request['donation_id']]);
            }

            setFlashMessage('success', 'Request rejected.');
        }
    }

    redirect('manage_requests.php' . ($donationFilter ? "?donation_id=$donationFilter" : ''));
}

$sql = "SELECT r.*, d.food_name, d.food_type, d.quantity, d.id as donation_id,
               u.name as charity_name, u.organization_name, u.phone as charity_phone, u.email as charity_email, u.address as charity_address
        FROM requests r
        JOIN donations d ON r.donation_id = d.id
        JOIN users u ON r.charity_id = u.id
        WHERE d.donor_id = ?";
$params = [$userId];

if ($donationFilter) {
    $sql .= " AND d.id = ?";
    $params[] = $donationFilter;
}

$sql .= " ORDER BY r.status = 'pending' DESC, r.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="w-full max-w-[98%] mx-auto px-2 sm:px-4 lg:px-6 py-8 md:py-12">
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Manage Requests</h1>
            <p class="mt-2 text-sm text-gray-500">Review and manage food donation requests from charities.</p>
        </div>
    </div>

    <?php if (empty($requests)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-10 text-center flex flex-col items-center justify-center">
                <div class="bg-gray-50 rounded-full p-4 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">No Requests Found</h3>
                <p class="text-sm text-gray-500 mb-6 max-w-sm">You haven't received any requests for your donations yet.
                    Check back later!</p>
                <a href="my_donations.php"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002 2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    View My Donations
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Food Item</th>
                            <th scope="col"
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Requested By</th>
                            <th scope="col"
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Contact</th>
                            <th scope="col"
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Message</th>
                            <th scope="col"
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th scope="col"
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Date</th>
                            <th scope="col"
                                class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($requests as $request): ?>
                            <tr class="hover:bg-gray-50 transition-colors duration-150 ease-in-out">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 flex items-center justify-center bg-green-100 text-green-600 rounded-full">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">
                                                <?php echo sanitize($request['food_name']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500 flex items-center mt-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1 text-gray-400"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                                </svg>
                                                <?php echo sanitize($request['quantity']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo sanitize($request['organization_name'] ?? $request['charity_name']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500 mt-1"><?php echo sanitize($request['charity_name']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-gray-400" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        <?php echo sanitize($request['charity_phone']) ?: 'N/A'; ?>
                                    </div>
                                    <div class="text-sm text-gray-500 mt-1 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-gray-400" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <?php echo sanitize($request['charity_email']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600 max-w-xs">
                                        <?php if (!empty($request['message'])): ?>
                                            <span
                                                class="inline-block bg-gray-50 rounded px-3 py-1.5 text-xs text-gray-700 shadow-sm border border-gray-100">
                                                <?php
                                                $fullMessage = sanitize($request['message']);
                                                // Safely pass to JavaScript
                                                $jsSafeMessage = htmlspecialchars($request['message'], ENT_QUOTES, 'UTF-8');
                                                if (strlen($fullMessage) > 35) {
                                                    echo substr($fullMessage, 0, 35) . '... ';
                                                    echo '<button type="button" onclick="openMessageModal(\'' . $jsSafeMessage . '\')" class="text-primary hover:text-green-700 font-bold focus:outline-none ml-1 underline decoration-primary decoration-2 underline-offset-2 cursor-pointer transition-colors duration-200">View more</button>';
                                                } else {
                                                    echo $fullMessage;
                                                }
                                                ?>
                                            </span>
                                        <?php else: ?>
                                            <em class="text-gray-400 text-xs">No message</em>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $statusClass = '';
                                    $statusDot = '';
                                    switch ($request['status']) {
                                        case 'pending':
                                            $statusClass = 'bg-amber-100 text-amber-800 border-amber-200';
                                            $statusDot = 'bg-amber-500';
                                            break;
                                        case 'accepted':
                                            $statusClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                                            $statusDot = 'bg-emerald-500';
                                            break;
                                        case 'rejected':
                                            $statusClass = 'bg-rose-100 text-rose-800 border-rose-200';
                                            $statusDot = 'bg-rose-500';
                                            break;
                                        default:
                                            $statusClass = 'bg-gray-100 text-gray-800 border-gray-200';
                                            $statusDot = 'bg-gray-500';
                                            break;
                                    }
                                    ?>
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border <?php echo $statusClass; ?>">
                                        <span class="h-1.5 w-1.5 rounded-full <?php echo $statusDot; ?> mr-1.5"></span>
                                        <?php echo ucfirst($request['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-gray-400" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <?php echo formatDate($request['created_at']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <div class="flex justify-end space-x-2">
                                            <a href="?action=accept&id=<?php echo $request['id']; ?><?php echo $donationFilter ? "&donation_id=$donationFilter" : ''; ?>"
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-primary hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary shadow-sm transition-all hover:-translate-y-0.5"
                                                onclick="return confirm('Accept this request? Other pending requests for this donation will be rejected.')">
                                                Accept
                                            </a>
                                            <a href="?action=reject&id=<?php echo $request['id']; ?><?php echo $donationFilter ? "&donation_id=$donationFilter" : ''; ?>"
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-sm transition-all hover:-translate-y-0.5"
                                                onclick="return confirm('Reject this request?')">
                                                Reject
                                            </a>
                                        </div>
                                    <?php elseif ($request['status'] === 'accepted'): ?>
                                        <span class="inline-flex items-center text-sm text-gray-500 italic">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Waiting for pickup
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
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

<!-- Modal for Full Message -->
<div id="messageModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" aria-hidden="true"
            onclick="closeMessageModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-100">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div
                        class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-50 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-bold text-gray-900 border-b border-gray-100 pb-3 mb-4"
                            id="modal-title">
                            Charity Message
                        </h3>
                        <div class="mt-2 bg-gray-50 rounded-lg p-4 border border-gray-100">
                            <p class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed font-medium"
                                id="modalMessageContent"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                <button type="button"
                    class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-6 py-2 bg-primary text-base font-bold text-white hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm transition duration-200 hover:-translate-y-0.5"
                    onclick="closeMessageModal()">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openMessageModal(message) {
        document.getElementById('modalMessageContent').textContent = message;
        document.getElementById('messageModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeMessageModal() {
        document.getElementById('messageModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
</script>

<?php require_once 'includes/footer.php'; ?>