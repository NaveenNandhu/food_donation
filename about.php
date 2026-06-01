<?php
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>

<div class="container">
    <div style="max-width: 800px; margin: 0 auto;">
        <h1 style="margin-bottom: 30px; text-align: center;">About FoodShare</h1>
        
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-body">
                <h2 style="color: var(--primary-color); margin-bottom: 15px;">Our Mission</h2>
                <p style="font-size: 1.1rem; line-height: 1.8;">
                    FoodShare is dedicated to reducing food waste by connecting individuals and families with surplus food 
                    to local charities, orphanages, and organizations that can put it to good use. We believe that no good 
                    food should go to waste when there are people in our communities who need it.
                </p>
            </div>
        </div>

        <div class="card" style="margin-bottom: 30px;">
            <div class="card-body">
                <h2 style="color: var(--primary-color); margin-bottom: 15px;">How It Works</h2>
                
                <div style="margin-bottom: 20px;">
                    <h4>For Donors</h4>
                    <ol style="margin-left: 20px; line-height: 2;">
                        <li>Register as a food donor</li>
                        <li>List your surplus food items with details like quantity, type, and pickup location</li>
                        <li>Receive requests from verified charitable organizations</li>
                        <li>Accept a request and coordinate the pickup</li>
                        <li>Make a difference in your community!</li>
                    </ol>
                </div>

                <div>
                    <h4>For Charities & Organizations</h4>
                    <ol style="margin-left: 20px; line-height: 2;">
                        <li>Register your organization</li>
                        <li>Browse available food donations in your area</li>
                        <li>Request the donations that suit your needs</li>
                        <li>Pick up the food from the donor</li>
                        <li>Distribute to those who need it most</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 30px;">
            <div class="card-body">
                <h2 style="color: var(--primary-color); margin-bottom: 15px;">Why Food Donation Matters</h2>
                <ul style="margin-left: 20px; line-height: 2;">
                    <li>Approximately 1/3 of all food produced globally goes to waste</li>
                    <li>At the same time, millions of people face food insecurity</li>
                    <li>Food waste in landfills produces methane, contributing to climate change</li>
                    <li>Donating food helps both people and the planet</li>
                    <li>Communities become stronger when we help each other</li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="text-align: center;">
                <h2 style="color: var(--primary-color); margin-bottom: 15px;">Join Us Today</h2>
                <p style="margin-bottom: 20px;">
                    Whether you have surplus food to share or represent an organization that can use it, 
                    join FoodShare today and be part of the solution.
                </p>
                <div style="display: flex; gap: 15px; justify-content: center;">
                    <a href="register.php?type=donor" class="btn btn-primary">Register as Donor</a>
                    <a href="register.php?type=charity" class="btn btn-outline">Register as Charity</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
