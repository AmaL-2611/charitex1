<?php
session_start();
require_once 'config.php';

if(isset($_POST['submit'])){
    try {
        // Create a new database connection
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = md5($_POST['password']); // Note: Consider using password_hash() in production

        $select = mysqli_query($conn, "SELECT * FROM `orphanage` WHERE email = '$email' AND password = '$password'") 
            or die('Query failed: ' . mysqli_error($conn));

        if(mysqli_num_rows($select) > 0){
            $row = mysqli_fetch_assoc($select);
            $_SESSION['orphanage_id'] = $row['id'];
            $_SESSION['orphanage_name'] = $row['name'];
            $_SESSION['orphanage_email'] = $row['email'];
            header('location:orphanage_dashboard.php');
            exit();
        } else {
            $message[] = 'Incorrect email or password!';
        }

        // Close the connection
        $conn->close();

    } catch (Exception $e) {
        $message[] = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHARITEX - Orphanage Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background-image: url("n1.jpg");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
           
            z-index: -1;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            color: #1a2a6c;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            background: #ff5757;
            color: white;
            text-align: center;
        }

        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .input-group input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            border-color: #1a2a6c;
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 42, 108, 0.1);
        }

        .form-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #1a2a6c, #b21f1f);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-btn:hover {
            background: linear-gradient(135deg, #b21f1f, #1a2a6c);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .error {
            color: #ff5757;
            font-size: 0.85rem;
            margin-top: 5px;
        }

        a {
            color: #1a2a6c;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #b21f1f;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Orphanage Login</h1>
            <p>Welcome back to CHARITEX</p>
        </div>

        <form action="" method="post" id="loginForm">
            <?php
            if(isset($message)){
                foreach($message as $message){
                    echo '<div class="message">'.$message.'</div>';
                }
            }
            ?>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="email" placeholder="Enter email" required>
            </div>
            <span id="email-error" class="error"></span>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Enter password" required>
            </div>
            <span id="password-error" class="error"></span>

            <button type="submit" name="submit" class="form-btn">Login Now</button>

            <p style="text-align: center; margin-top: 1.5rem;">
                Don't have an account? <a href="signup_orphanage.php">Register now</a>
            </p>
        </form>
    </div>

    <script>
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            let isValid = true;
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            
            // Email validation
            if (!email.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                document.getElementById('email-error').textContent = 'Please enter a valid email address';
                isValid = false;
            } else {
                document.getElementById('email-error').textContent = '';
            }
            
            // Password validation
            if (password.value.length < 6) {
                document.getElementById('password-error').textContent = 'Password must be at least 6 characters long';
                isValid = false;
            } else {
                document.getElementById('password-error').textContent = '';
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html> 