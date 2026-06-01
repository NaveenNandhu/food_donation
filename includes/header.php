<?php $flash = getFlashMessage(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodShare - Reduce Food Waste</title>
    <script>
        // Prevent Flash of Unstyled Content (FOUC) while loading Tailwind
        if (navigator.onLine) {
            document.documentElement.style.visibility = 'hidden';
        }

        function loadTailwind() {
            if (navigator.onLine) {
                const script = document.createElement('script');
                // Removing dynamic timestamp caching here to allow browser caching the CDN payload for instant subsequent loads
                script.src = "https://cdn.tailwindcss.com";
                script.onload = function () {
                    tailwind.config = {
                        theme: {
                            extend: {
                                colors: {
                                    primary: '#10B981', // emerald-500
                                    secondary: '#3B82F6', // blue-500
                                    dark: '#1F2937', // gray-800
                                }
                            }
                        }
                    };
                    // Reveal content once styling is applied
                    document.documentElement.style.visibility = '';
                };
                script.onerror = function () {
                    // Reveal content even if Tailwind fails to load
                    document.documentElement.style.visibility = '';
                };
                document.head.appendChild(script);
            }
        }

        // Check internet status on page load
        loadTailwind();

        // Automatically reload the page when the internet connection is restored
        window.addEventListener('online', function () {
            window.location.reload();
        });
    </script>
    <style>
        /* Mobile Menu Transitions */
        .nav-links {
            transition: all 0.3s ease-in-out;
        }

        .nav-links.active {
            display: flex !important;
            flex-direction: column;
            position: absolute;
            top: 64px;
            left: 0;
            width: 100%;
            background-color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            z-index: 50;
        }

        .hamburger span {
            display: block;
            width: 25px;
            height: 3px;
            background-color: #1F2937;
            margin: 5px auto;
            transition: all 0.3s ease-in-out;
        }

        .hamburger.active span:nth-child(1) {
            transform: translateY(8px) rotate(45deg);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active span:nth-child(3) {
            transform: translateY(-8px) rotate(-45deg);
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans leading-normal tracking-normal flex flex-col min-h-screen">
    <nav class="bg-white shadow-md fixed w-full z-10 top-0">
        <div class="container mx-auto px-4 h-16 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-primary flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                FoodShare
            </a>

            <ul class="nav-links hidden md:flex space-x-6 items-center">
                <?php if (isLoggedIn()): ?>
                    <?php if (isDonor()): ?>
                        <li><a href="donor_dashboard.php"
                                class="hover:text-primary font-medium transition duration-300">Dashboard</a></li>
                        <li><a href="add_donation.php" class="hover:text-primary font-medium transition duration-300">Add
                                Donation</a></li>
                        <li><a href="my_donations.php" class="hover:text-primary font-medium transition duration-300">My
                                Donations</a></li>
                    <?php elseif (isCharity()): ?>
                        <li><a href="charity_dashboard.php"
                                class="hover:text-primary font-medium transition duration-300">Dashboard</a></li>
                        <li><a href="browse_donations.php" class="hover:text-primary font-medium transition duration-300">Browse
                                Food</a></li>
                        <li><a href="my_requests.php" class="hover:text-primary font-medium transition duration-300">My
                                Requests</a></li>
                    <?php elseif (isAdmin()): ?>
                        <li><a href="admin_dashboard.php" class="hover:text-primary font-medium transition duration-300">Admin
                                Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="profile.php" class="hover:text-primary font-medium transition duration-300">Profile</a>
                    </li>
                    <li><a href="logout.php"
                            class="px-4 py-2 border border-primary text-primary rounded-full hover:bg-primary hover:text-white transition duration-300">Logout</a>
                    </li>
                <?php else: ?>
                    <li><a href="login.php" class="hover:text-primary font-medium transition duration-300">Login</a></li>
                    <li><a href="register.php"
                            class="px-4 py-2 bg-primary text-white rounded-full hover:bg-green-600 transition duration-300 shadow-md transform hover:scale-105">Register</a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="hamburger md:hidden cursor-pointer">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>
    <main class="flex-grow pt-20 pb-8 container mx-auto px-4">
        <?php
        if ($flash):
            $alertClass = match ($flash['type']) {
                'success' => 'bg-green-100 border-green-400 text-green-700',
                'error' => 'bg-red-100 border-red-400 text-red-700',
                'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
                default => 'bg-blue-100 border-blue-400 text-blue-700'
            };
            ?>
            <div class="mb-6">
                <div class="alert <?php echo $alertClass; ?> border-l-4 p-4 rounded shadow-sm relative" role="alert">
                    <span class="block sm:inline"><?php echo $flash['message']; ?></span>
                </div>
            </div>
        <?php endif; ?>