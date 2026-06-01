<?php
// Define the password you want to hash
$raw_password = 'password123'; 

// IMPORTANT: Use PASSWORD_DEFAULT for the strongest, modern hashing algorithm.
$hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Password Hash Generator</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h1 { color: #2ecc71; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        code { background-color: #f0f0f0; padding: 2px 6px; border-radius: 4px; }
        .result { margin-top: 20px; padding: 15px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Password Hash Generator</h1>

        <p><strong>Raw Password:</strong> <span class='warning'>" . htmlspecialchars($raw_password) . "</span></p>
        
        <p><strong>Generated Hash:</strong></p>
        <div class='result success'>
            <code>" . htmlspecialchars($hashed_password) . "</code>
        </div>
        <p style='margin-top: 10px; font-size: 0.9em; color: #95a5a6;'>
            Copy the full hash above and use it when inserting the admin password into the MySQL database.
        </p>

        <h3 style='margin-top: 30px;'>Verification Test</h3>";

        // Test the hash immediately
        if (password_verify($raw_password, $hashed_password)) {
            echo "<div class='result success'>Verification successful! The generated hash works correctly.</div>";
        } else {
            echo "<div class='result warning'>Verification FAILED!</div>";
        }

echo "
    </div>
</body>
</html>";
?>