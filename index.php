<?php
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>

<section class="bg-gradient-to-r from-green-50 to-blue-50 py-20">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6 leading-tight">
            Reduce Food Waste, <span class="text-primary">Feed the Needy</span>
        </h1>
        <p class="text-xl text-gray-600 mb-10 max-w-2xl mx-auto">
            Connect your surplus food with local charities and orphanages. Together, we can make a difference in our
            community and environment.
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <?php if (!isLoggedIn()): ?>
                <a href="register.php?type=donor"
                    class="px-8 py-3 bg-primary text-white rounded-full font-bold shadow-lg hover:bg-green-600 hover:shadow-xl transition duration-300 transform hover:-translate-y-1">Donate
                    Food</a>
                <a href="register.php?type=charity"
                    class="px-8 py-3 border-2 border-primary text-primary rounded-full font-bold hover:bg-primary hover:text-white transition duration-300 transform hover:-translate-y-1">Register
                    as Charity</a>
            <?php else: ?>
                <?php if (isDonor()): ?>
                    <a href="add_donation.php"
                        class="px-8 py-3 bg-primary text-white rounded-full font-bold shadow-lg hover:bg-green-600 transition duration-300">Add
                        Donation</a>
                    <a href="my_donations.php"
                        class="px-8 py-3 border-2 border-primary text-primary rounded-full font-bold hover:bg-primary hover:text-white transition duration-300">View
                        My Donations</a>
                <?php elseif (isCharity()): ?>
                    <a href="browse_donations.php"
                        class="px-8 py-3 bg-primary text-white rounded-full font-bold shadow-lg hover:bg-green-600 transition duration-300">Browse
                        Available Food</a>
                    <a href="my_requests.php"
                        class="px-8 py-3 border-2 border-primary text-primary rounded-full font-bold hover:bg-primary hover:text-white transition duration-300">View
                        My Requests</a>
                <?php elseif (isAdmin()): ?>
                    <a href="admin_dashboard.php"
                        class="px-8 py-3 bg-primary text-white rounded-full font-bold shadow-lg hover:bg-green-600 transition duration-300">Admin
                        Dashboard</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">How It Works</h2>
            <p class="text-gray-600 mt-2 max-w-xl mx-auto">Simple steps to share your surplus food and help those in
                need.</p>
            <div class="w-24 h-1 bg-primary mx-auto mt-4 rounded"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div
                class="bg-gray-50 rounded-xl p-6 text-center hover:shadow-xl transition duration-300 border border-gray-100 group">
                <div class="text-5xl mb-4 transform group-hover:scale-110 transition duration-300">📝</div>
                <h3 class="text-xl font-bold mb-2 text-gray-800">Register</h3>
                <p class="text-gray-600">Sign up as a food donor or a charitable organization to get started.</p>
            </div>
            <div
                class="bg-gray-50 rounded-xl p-6 text-center hover:shadow-xl transition duration-300 border border-gray-100 group">
                <div class="text-5xl mb-4 transform group-hover:scale-110 transition duration-300">🍲</div>
                <h3 class="text-xl font-bold mb-2 text-gray-800">List Food</h3>
                <p class="text-gray-600">Donors can easily list their surplus food items with details and pickup
                    location.</p>
            </div>
            <div
                class="bg-gray-50 rounded-xl p-6 text-center hover:shadow-xl transition duration-300 border border-gray-100 group">
                <div class="text-5xl mb-4 transform group-hover:scale-110 transition duration-300">🔍</div>
                <h3 class="text-xl font-bold mb-2 text-gray-800">Browse & Request</h3>
                <p class="text-gray-600">Charities can browse available donations and request the food they need.</p>
            </div>
            <div
                class="bg-gray-50 rounded-xl p-6 text-center hover:shadow-xl transition duration-300 border border-gray-100 group">
                <div class="text-5xl mb-4 transform group-hover:scale-110 transition duration-300">🤝</div>
                <h3 class="text-xl font-bold mb-2 text-gray-800">Connect & Transfer</h3>
                <p class="text-gray-600">Donors accept requests and coordinate pickup with the charity.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Why Choose FoodShare?</h2>
            <p class="text-gray-600 mt-2">Making food donation simple and impactful</p>
            <div class="w-24 h-1 bg-secondary mx-auto mt-4 rounded"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-xl p-8 text-center shadow-md hover:shadow-xl transition duration-300 group">
                <div
                    class="text-5xl mb-6 bg-green-100 w-20 h-20 mx-auto flex items-center justify-center rounded-full group-hover:bg-green-200 transition duration-300">
                    🌍</div>
                <h3 class="text-2xl font-bold mb-3 text-gray-800">Reduce Waste</h3>
                <p class="text-gray-600 leading-relaxed">Help reduce food waste by sharing surplus food instead of
                    throwing it away, contributing to a greener planet.</p>
            </div>
            <div class="bg-white rounded-xl p-8 text-center shadow-md hover:shadow-xl transition duration-300 group">
                <div
                    class="text-5xl mb-6 bg-red-100 w-20 h-20 mx-auto flex items-center justify-center rounded-full group-hover:bg-red-200 transition duration-300">
                    ❤️</div>
                <h3 class="text-2xl font-bold mb-3 text-gray-800">Help Communities</h3>
                <p class="text-gray-600 leading-relaxed">Support local charities and orphanages in feeding those who
                    need it most, strengthening community bonds.</p>
            </div>
            <div class="bg-white rounded-xl p-8 text-center shadow-md hover:shadow-xl transition duration-300 group">
                <div
                    class="text-5xl mb-6 bg-blue-100 w-20 h-20 mx-auto flex items-center justify-center rounded-full group-hover:bg-blue-200 transition duration-300">
                    🔒</div>
                <h3 class="text-2xl font-bold mb-3 text-gray-800">Safe & Secure</h3>
                <p class="text-gray-600 leading-relaxed">Verified organizations and a secure platform ensure that your
                    donations reach the right people safely and reliably.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>