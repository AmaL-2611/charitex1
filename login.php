<?php
session_start();
if (isset($_GET['error'])) {
    $error = $_GET['error'];
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
        font-family: "Segoe UI", "Arial", sans-serif;
      }

      body {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
        background-image: url("https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?ixlib=rb-4.0.3");
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        position: relative;
      }

      /* Update the overlay to match signup page */
      body::before {
        content: "";
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(
          135deg,
          rgba(26, 42, 108, 0.8),
          rgba(178, 31, 31, 0.8)
        );
        z-index: -1;
      }

      .container {
        position: relative;
        z-index: 2;  /* Add this to ensure container appears above the overlay */
        background: rgba(255, 255, 255, 0.95);
        padding: 3rem;
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 440px;
        backdrop-filter: blur(10px);
        transform: translateY(0);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
      }

      .container:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
      }

      .header {
        text-align: center;
        margin-bottom: 2.5rem;
      }

      .header h1 {
        color: #1a2a6c;
        margin-bottom: 0.8rem;
        font-size: 2.2rem;
        font-weight: 600;
      }

      .header p {
        color: #666;
        font-size: 1.1rem;
      }

      .form-group {
        margin-bottom: 1.8rem;
        position: relative;
      }

      label {
        display: block;
        margin-bottom: 0.7rem;
        color: #1a2a6c;
        font-weight: 500;
        font-size: 0.95rem;
      }

      input {
        width: 100%;
        padding: 1rem;
        border: 2px solid #e1e1e1;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.9);
      }

      input:focus {
        outline: none;
        border-color: #1a2a6c;
        box-shadow: 0 0 0 3px rgba(26, 42, 108, 0.1);
      }

      input.error {
        border-color: #ff4444;
        background-color: rgba(255, 68, 68, 0.05);
      }

      input.valid {
        border-color: #00c851;
        background-color: rgba(0, 200, 81, 0.05);
      }

      .field-error {
        color: #ff4444;
        font-size: 0.85rem;
        margin-top: 0.5rem;
        display: none;
      }

      .remember-me {
        display: flex;
        align-items: center;
        margin-bottom: 2rem;
      }

      .remember-me input {
        width: auto;
        margin-right: 0.8rem;
      }

      .remember-me label {
        margin-bottom: 0;
        font-weight: normal;
        color: #666;
      }

      button {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, #1a2a6c, #2a3a7c);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
      }

      button:hover {
        background: linear-gradient(135deg, #2a3a7c, #3a4a8c);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(26, 42, 108, 0.2);
      }

      button:active {
        transform: translateY(0);
      }

      .error-message {
        background-color: rgba(255, 68, 68, 0.1);
        border: 1px solid rgba(255, 68, 68, 0.2);
        padding: 1rem;
        border-radius: 12px;
        color: #ff4444;
        margin-bottom: 1.5rem;
        display: none;
        text-align: center;
      }

      .links {
        text-align: center;
        color: #666;
        font-size: 0.95rem;
      }

      .links a {
        color: #1a2a6c;
        text-decoration: none;
        margin: 0 0.8rem;
        transition: color 0.3s ease;
      }

      .links a:hover {
        color: #b21f1f;
      }

      .divider {
        color: #ccc;
      }

      select.form-control {
        width: 100%;
        padding: 1rem;
        border: 2px solid #e1e1e1;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.9);
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%231a2a6c' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        cursor: pointer;
      }

      select.form-control:focus {
        outline: none;
        border-color: #1a2a6c;
        box-shadow: 0 0 0 3px rgba(26, 42, 108, 0.1);
      }

      .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
      }

      .modal-content {
        background-color: white;
        margin: 15% auto;
        padding: 20px;
        border-radius: 12px;
        width: 80%;
        max-width: 400px;
      }

      .close {
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="header">
        <h1>Welcome Back</h1>
        <p>Login to your CHARITEX account</p>
      </div>

      <form id="loginForm" method="POST" action="process_login.php" onsubmit="return validateForm(event)">
        <?php if (isset($error)): ?>
        <div class="error-message" id="errorMessage" style="display: block">
          <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <div class="form-group">
          <label for="userType">I am a</label>
          <select id="userType" name="userType" required class="form-control">
            <option value="">Select your role</option>
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
            onfocus="showValidation(this, 'Enter your registered email address')"
            onblur="validateEmail(this)"
            onkeyup="validateEmail(this)"
          />
          <div class="field-error" id="email-error"></div>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            required
            onfocus="showValidation(this, 'Enter your password')"
            onblur="validatePassword(this)"
            onkeyup="validatePassword(this)"
          />
          <div class="field-error" id="password-error"></div>
        </div>

        <!-- <div class="remember-me">
          <input type="checkbox" id="remember" name="remember" />
          <label for="remember">Remember me</label>
        </div> -->

        <button type="submit">Log In</button>

        <div class="links">
          <a href="reading mail.php" onclick="showForgotPasswordModal(); return false;">Forgot Password?</a>
          <span class="divider">|</span>
          <a href="signup.php">Create Account</a>
        </div>
      </form>
    </div>

    <!-- Add Forgot Password Modal
    <div id="forgotPasswordModal" class="modal" style="display: none;">
      <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Forgot Password</h2>
        <div id="step1">
          <p>Enter your email address to receive an OTP</p>
          <div class="form-group">
            <input type="email" id="forgotEmail" placeholder="Enter your email" required>
          </div>
          <button onclick="sendOTP()">Send OTP</button>
        </div>
        <div id="step2" style="display: none;">
          <p>Enter the OTP sent to your email</p>
          <div class="form-group">
            <input type="text" id="otpInput" placeholder="Enter OTP" required>
          </div>
          <button onclick="verifyOTP()">Verify OTP</button>
        </div>
        <div id="step3" style="display: none;">
          <p>Enter your new password</p>
          <div class="form-group">
            <input type="password" id="newPassword" placeholder="New Password" required>
          </div>
          <div class="form-group">
            <input type="password" id="confirmPassword" placeholder="Confirm Password" required>
          </div>
          <button onclick="resetPassword()">Reset Password</button>
        </div>
      </div>
    </div> -->

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