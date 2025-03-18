<?php
session_start();
require_once 'connect.php';

if (isset($_GET['error'])) {
    $error = $_GET['error'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    try {
        if ($role === 'donor') {
            $table = 'donors';
        } elseif ($role === 'volunteer') {
            $table = 'volunteers';
        } else {
            throw new Exception("Invalid role selected");
        }

        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($role === 'volunteer' && $user['status'] === 'pending') {
                $error = "Your volunteer account is pending approval";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;

                header("Location: " . ($role === 'donor' ? 'donor.php' : 'volunteer.php'));
                exit();
            }
        } else {
            $error = "Invalid email or password";
        }
    } catch (Exception $e) {
        error_log("Login Error: " . $e->getMessage());
        $error = "An error occurred during login";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CHARITEX - Login</title>
    <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
      }
      body {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
        background-image: url("n2.jpg");
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
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
        background: rgba(0, 0, 0, 0.4);
        z-index: -1;
      }
      .container {
        background: #fff;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 500px;
        animation: fadeIn 0.5s ease;
      }

      @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
      }

      .header {
        text-align: center;
        margin-bottom: 30px;
      }

      .header h1 {
        color: #333;
        margin-bottom: 10px;
        font-size: 2rem;
      }

      .header p {
        color: #666;
        font-size: 1rem;
      }

      .form-group {
        margin-bottom: 20px;
        position: relative;
      }

      .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: 500;
      }

      .form-group input,
      .form-group select {
        width: 100%;
        padding: 15px;
        border: 2px solid #ddd;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #fff;
      }

      .form-group input:focus,
      .form-group select:focus {
        border-color: #1a2a6c;
        outline: none;
        box-shadow: 0 0 10px rgba(26, 42, 108, 0.1);
      }

      .custom-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 15px;
      }

      .error-message {
        color: #ff4444;
        font-size: 0.85rem;
        margin-top: 5px;
        display: none;
      }

      .form-btn {
        width: 100%;
        padding: 15px;
        background: linear-gradient(45deg, #1a2a6c, #b21f1f);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 20px;
      }

      .form-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(26, 42, 108, 0.3);
      }

      .form-bottom {
        text-align: center;
        margin-top: 20px;
      }

      .login-text {
        color: #666;
        font-size: 0.95rem;
        margin-top: 15px;
      }

      .login-text a {
        color: #1a2a6c;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
      }

      .login-text a:hover {
        color: #b21f1f;
      }

      .remember-me {
        display: flex;
        align-items: center;
        margin: 15px 0;
      }

      .remember-me input[type="checkbox"] {
        margin-right: 10px;
        width: auto;
      }

      @media (max-width: 480px) {
        .container {
          padding: 20px;
        }
      }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="header">
        <h1>Welcome Back</h1>
        <p>Login to your account</p>
      </div>

      <form id="loginForm" method="POST" action="process_login.php">
        <?php if (isset($error)): ?>
        <div class="error-message" id="errorMessage" style="display: block">
          <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <div class="form-group">
          <label for="userType">I am a</label>
          <select id="userType" name="userType" required class="custom-select">
            <option value="" disabled selected>Select your role</option>
            <option value="donor">Donor</option>
            <option value="volunteer">Volunteer</option>
            <option value="admin">Admin</option>
          </select>
        </div>

        <div class="form-group">
          <label for="email">Email Address</label>
          <input
            type="email"
            id="email"
            name="email"
            required
            placeholder="Enter your email"
          />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            required
            placeholder="Enter your password"
          />
        </div>

        <div class="remember-me">
          <input type="checkbox" id="remember" name="remember" />
          <label for="remember">Remember me</label>
        </div>

        <button type="submit" class="form-btn">Log In</button>

        <div class="form-bottom">
          <p class="login-text">
            <a href="reading mail.php">Forgot Password?</a>
            <span style="margin: 0 10px;">|</span>
            <a href="signup.php">Create Account</a>
          </p>
        </div>
      </form>
    </div>

    <script>
      function showValidation(input, message) {
        const errorElement = document.getElementById(`${input.id}-error`);
        errorElement.textContent = message;
        errorElement.style.display = "block";
      }

      function hideValidation(input) {
        const errorElement = document.getElementById(`${input.id}-error`);
        errorElement.style.display = "none";
      }

      function validateEmail(input) {
        const value = input.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!value) {
          input.classList.add("error");
          input.classList.remove("valid");
          showValidation(input, "Email is required");
          return false;
        } else if (!emailRegex.test(value)) {
          input.classList.add("error");
          input.classList.remove("valid");
          showValidation(input, "Please enter a valid email address");
          return false;
        }

        input.classList.remove("error");
        input.classList.add("valid");
        hideValidation(input);
        return true;
      }

      function validatePassword(input) {
        const value = input.value;

        if (!value) {
          input.classList.add("error");
          input.classList.remove("valid");
          showValidation(input, "Password is required");
          return false;
        } 
        

        input.classList.remove("error");
        input.classList.add("valid");
        hideValidation(input);
        return true;
      }

      function validateForm(event) {
        const emailValid = validateEmail(document.getElementById("email"));
        const passwordValid = validatePassword(
          document.getElementById("password")
        );

        if (!emailValid || !passwordValid) {
          event.preventDefault();
          document.getElementById("errorMessage").style.display = "block";
          return false;
        }

        document.getElementById("errorMessage").style.display = "none";
        return true;
      }

      document.querySelectorAll("input").forEach((input) => {
        input.addEventListener("input", function () {
          document.getElementById("errorMessage").style.display = "none";
        });
      });

      function showForgotPasswordModal() {
        document.getElementById('forgotPasswordModal').style.display = 'block';
      }

      document.querySelector('.close').onclick = function() {
        document.getElementById('forgotPasswordModal').style.display = 'none';
      }

      function sendOTP() {
        const email = document.getElementById('forgotEmail').value;
        fetch('send_otp.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `email=${encodeURIComponent(email)}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            document.getElementById('step1').style.display = 'none';
            document.getElementById('step2').style.display = 'block';
          } else {
            alert(data.message);
          }
        });
      }

      function verifyOTP() {
        const email = document.getElementById('forgotEmail').value;
        const otp = document.getElementById('otpInput').value;
        fetch('verify_otp.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `email=${encodeURIComponent(email)}&otp=${encodeURIComponent(otp)}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            document.getElementById('step2').style.display = 'none';
            document.getElementById('step3').style.display = 'block';
          } else {
            alert(data.message);
          }
        });
      }

      function resetPassword() {
        const email = document.getElementById('forgotEmail').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (newPassword !== confirmPassword) {
          alert('Passwords do not match!');
          return;
        }

        fetch('reset_password.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(newPassword)}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Password reset successfully!');
            document.getElementById('forgotPasswordModal').style.display = 'none';
          } else {
            alert(data.message);
          }
        });
      }
    </script>
  </body>
</html>