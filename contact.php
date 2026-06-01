<?php
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>

<div class="container">
    <div style="max-width: 600px; margin: 0 auto;">
        <h1 style="margin-bottom: 30px; text-align: center;">Contact Us</h1>
        
        <div class="card">
            <div class="card-body">
                <p style="text-align: center; margin-bottom: 30px;">
                    Have questions or feedback? We'd love to hear from you!
                </p>

                <div style="display: grid; gap: 25px; margin-bottom: 30px;">
                    <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: var(--radius);">
                        <h4 style="margin-bottom: 10px;">Email</h4>
                        <p>info@foodshare.com</p>
                    </div>
                    
                    <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: var(--radius);">
                        <h4 style="margin-bottom: 10px;">Phone</h4>
                        <p>+1 234 567 890</p>
                    </div>
                    
                    <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: var(--radius);">
                        <h4 style="margin-bottom: 10px;">Address</h4>
                        <p>123 Food Street<br>Charity City, CC 12345</p>
                    </div>
                </div>

                <h3 style="margin-bottom: 20px;">Send us a Message</h3>
                <form>
                    <div class="form-group">
                        <label for="name">Your Name</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" class="form-control" rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
