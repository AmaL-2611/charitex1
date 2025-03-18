<?php
// Prevent any output before headers
ob_start();

// Start session BEFORE any output
session_start();
require_once 'connect.php';

// Initialize variables to store errors
$signup_errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';
    $aadhar = $_FILES['aadhar'] ?? '';

    // Validate inputs
    if (empty($name)) {
        $signup_errors[] = "Name is required";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $signup_errors[] = "Valid email is required";
    }

    if (empty($password)) {
        $signup_errors[] = "Password is required";
    }

    if ($password !== $confirm_password) {
        $signup_errors[] = "Passwords do not match";
    }

    if (empty($role) || !in_array($role, ['volunteer', 'donor'])) {
        $signup_errors[] = "Invalid role selected";
    }

    if (empty($aadhar)) {
        $signup_errors[] = "Aadhar document is required";
    }

    // Check if email already exists
    if (empty($signup_errors)) {
        try {
            // Check email in both volunteers and donors tables
            $check_email_stmt = $pdo->prepare("
                SELECT 'volunteer' as user_type FROM volunteers WHERE email = :email
                UNION
                SELECT 'donor' as user_type FROM donors WHERE email = :email
            ");
            $check_email_stmt->execute([':email' => $email]);
            $existing_user = $check_email_stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_user) {
                $signup_errors[] = "Email already exists";
            }
        } catch (PDOException $e) {
            // Log the error
            error_log("Email Check Error: " . $e->getMessage());
            $signup_errors[] = "Database error occurred. Please try again.";
        }
    }

    // If no errors, proceed with account creation
    if (empty($signup_errors)) {
        try {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert into appropriate table based on role
            if ($role === 'volunteer') {
                // Additional volunteer-specific fields
                $location = trim($_POST['location'] ?? '');
                $availability = trim($_POST['availability'] ?? '');

                $insert_stmt = $pdo->prepare("
                    INSERT INTO volunteers 
                    (name, email, password, location, availability, aadhar) 
                    VALUES (:name, :email, :password, :location, :availability, :aadhar)
                ");
                $insert_stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':password' => $hashed_password,
                    ':location' => $location,
                    ':availability' => $availability,
                    ':aadhar' => $aadhar['name']
                ]);
            } else { // donor
                $insert_stmt = $pdo->prepare("
                    INSERT INTO donors 
                    (name, email, password, aadhar) 
                    VALUES (:name, :email, :password, :aadhar)
                ");
                $insert_stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':password' => $hashed_password,
                    ':aadhar' => $aadhar['name']
                ]);
            }

            // Clear output buffer and redirect
            ob_end_clean();
            header("Location: login.php?signup=success");
            exit();
        } catch (PDOException $e) {
            // Log the error
            error_log("Signup Error: " . $e->getMessage());
            $signup_errors[] = "Database error occurred. Please try again.";
        }
    }

    
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Choose Account Type - CHARITEX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

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
        background: rgba(255, 255, 255, 0.95);
        padding: 2.5rem;
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        width: 100%;
        max-width: 500px;
        animation: fadeIn 0.8s ease-in-out;
      }

      .header {
        text-align: center;
        margin-bottom: 2rem;
      }

      .header h1 {
        color: #1a2a6c;
        margin-bottom: 0.5rem;
      }

      .input-icon {
        position: relative;
      }

      .input-icon i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
      }

      .input-icon input {
        padding-left: 40px;
      }

      input, select {
        width: 100%;
        padding: 1rem 1rem 1rem 2.5rem;
        border: 2px solid #ddd;
        border-radius: 16px;
        font-size: 1rem;
        box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.1);
        transition: 0.3s;
        margin-bottom: 1rem;
      }

      input:focus, select:focus {
        outline: none;
        border-color: #1a2a6c;
        box-shadow: 0 0 8px rgba(26, 42, 108, 0.2);
      }

      .form-btn {
        width: 100%;
        padding: 1.2rem;
        background: linear-gradient(45deg, #ff416c, #ff4b2b);
        color: white;
        border: none;
        border-radius: 16px;
        font-size: 1.2rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        margin: 0 auto 1rem auto;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 5px 15px rgba(255, 65, 108, 0.3);
      }

      .form-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(255, 65, 108, 0.4);
        background: linear-gradient(45deg, #ff4b2b, #ff416c);
      }

      .form-btn:active {
        transform: translateY(-1px);
      }

      .form-btn::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(
          to right,
          transparent,
          rgba(255, 255, 255, 0.3),
          transparent
        );
        transform: rotate(45deg);
        transition: 0.5s;
      }

      .form-btn:hover::after {
        left: 100%;
      }

      .form-group {
        position: relative;
        margin-bottom: 1.5rem;
      }

      .form-group input,
      .form-group select {
        width: 100%;
        padding: 1rem;
        border: 2px solid #ddd;
        border-radius: 16px;
        font-size: 1rem;
        background: white;
        transition: all 0.3s ease;
      }

      .form-group input:focus,
      .form-group select:focus {
        outline: none;
        border-color: #1a2a6c;
        box-shadow: 0 0 15px rgba(26, 42, 108, 0.1);
      }

      .role-selector {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
      }

      .role-option {
        flex: 1;
        text-align: center;
        padding: 1rem;
        background: #f5f5f5;
        border: 2px solid #ddd;
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
      }

      .role-option:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(26, 42, 108, 0.1);
      }

      .role-option.active {
        background: linear-gradient(135deg, #1a2a6c, #4a4eff);
        color: white;
        border-color: transparent;
      }

      label {
        display: block;
        margin-bottom: 0.5rem;
        color: #333;
        font-weight: 500;
      }

      .error-message {
        color: #ff4444;
        font-size: 0.85rem;
        margin-top: 0.5rem;
        padding-left: 1rem;
        display: none;
        animation: fadeIn 0.3s ease-in-out;
      }

      .message {
        text-align: center;
        margin-bottom: 1.5rem;
        padding: 0.8rem;
        border-radius: 8px;
        background-color: rgba(255, 68, 68, 0.1);
      }

      @keyframes fadeIn {
        from {
          opacity: 0;
          transform: translateY(-5px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      @media (max-width: 480px) {
        .container {
          padding: 1.5rem;
        }
        input, select {
          font-size: 16px;
        }
      }

      /* Enhanced error message styling */
      .error-message {
        color: #ff4444;
        font-size: 0.85rem;
        margin-top: 0.5rem;
        padding-left: 1rem;
        display: none;
        animation: fadeIn 0.3s ease-in-out;
      }

      /* Success state for valid inputs */
      .form-group.valid input {
        border-color: #2ecc71;
      }

      .form-group.valid i {
        color: #2ecc71;
      }

      /* Error state for invalid inputs */
      .form-group.error input {
        border-color: #ff4444;
      }

      .form-group.error i {
        color: #ff4444;
      }

      /* Show error message when form group has error class */
      .form-group.error .error-message {
        display: block;
      }

      /* Center alignment for button and login text */
      .form-bottom {
        text-align: center;
        margin-top: 2rem;
      }

      .login-text {
        color: #666;
        font-size: 0.95rem;
        margin-top: 1rem;
      }

      .login-text a {
        color: #ff416c;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
      }

      .login-text a:hover {
        color: #ff4b2b;
      }

      /* Ensure select elements match input styling with icons */
      .form-group select {
        width: 100%;
        padding: 1rem 1rem 1rem 3rem;
        border: 2px solid #ddd;
        border-radius: 16px;
        font-size: 1rem;
        background: white;
        transition: all 0.3s ease;
        appearance: none;
        cursor: pointer;
      }

      .form-group select + i.fa-calendar-alt {
        right: 15px;
        left: auto;
      }

      .form-group select:focus {
        border-color: #1a2a6c;
        box-shadow: 0 0 15px rgba(26, 42, 108, 0.1);
      }

      .volunteer-fields {
        display: none;
      }

      .volunteer-fields.active {
        display: block;
        animation: fadeIn 0.3s ease-in-out;
      }

      /* Custom Select Styling */
      .custom-select {
        width: 100%;
        padding: 1rem;
        border: 2px solid #ddd;
        border-radius: 16px;
        font-size: 1rem;
        color: #666; /* Color for placeholder */
        background: white;
        transition: all 0.3s ease;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1em;
        padding-right: 2.5rem;
      }

      .custom-select:focus {
        outline: none;
        border-color: #1a2a6c;
        box-shadow: 0 0 15px rgba(26, 42, 108, 0.1);
        color: #333; /* Darker color when focused */
      }

      /* Style for the options */
      .custom-select option {
        color: #333; /* Normal text color for actual options */
        padding: 1rem;
        font-size: 1rem;
      }

      /* Style for the placeholder option */
      .custom-select option[value=""][disabled] {
        color: #666;
      }

      .custom-select:required:invalid {
        color: #666; /* Color for placeholder text */
      }

      /* When an option is selected */
      .custom-select option:not([value=""][disabled]) {
        color: #333;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="header">
        <h1>Join CHARITEX</h1>
        <p>Choose how you want to make a difference</p>
      </div>

      <?php
      // Display errors if any
      if(isset($_GET['error'])) {
        $errors = explode('|', urldecode($_GET['error']));
        foreach ($errors as $error) {
          echo '<div class="error-message" style="display: block; color: #ff4444; text-align: center; margin-bottom: 15px;">' . 
               htmlspecialchars($error) . '</div>';
        }
      }
      ?>

      <div class="role-cards">
        <div class="role-card" onclick="window.location.href='donor_signup.php'">
          <i class="fas fa-hand-holding-heart"></i>
          <h2>Donor</h2>
          <p>Support causes and make donations</p>
          <button class="btn btn-primary">Sign up as Donor</button>
        </div>

        <div class="role-card" onclick="window.location.href='volunteer_signup.php'">
          <i class="fas fa-users"></i>
          <h2>Volunteer</h2>
          <p>Offer your time and skills</p>
          <button class="btn btn-success">Sign up as Volunteer</button>
        </div>
      </div>

      <div class="login-link">
        <p>Already have an account? <a href="login.php">Login here</a></p>
      </div>
    </div>

    <script>
      function selectRole(role) {
        const volunteerFields = document.querySelector(".volunteer-fields");
        const roleOptions = document.querySelectorAll(".role-option");
        const roleInput = document.getElementById("role");

        roleOptions.forEach((option) => option.classList.remove("active"));

        if (role === "volunteer") {
          volunteerFields.style.display = "block";
          roleOptions[1].classList.add("active");
          roleInput.value = "volunteer";
        } else {
          volunteerFields.style.display = "none";
          roleOptions[0].classList.add("active");
          roleInput.value = "donor";
        }
      }

      function showValidation(input, message) {
        const errorElement = document.getElementById(`${input.id}-error`);
        errorElement.textContent = message;
        errorElement.style.display = "block";
      }

      function hideValidation(input) {
        const errorElement = document.getElementById(`${input.id}-error`);
        errorElement.style.display = "none";
      }

      function validateFullName(input) {
    const value = input.value.trim();
    
    // Check if the name contains only letters and spaces
    const nameRegex = /^[A-Za-z]+\s+[A-Za-z\s]+$/;
    
    if (!nameRegex.test(value)) {
        input.classList.add("error");
        input.classList.remove("valid");
        
        // Provide specific error messages based on the validation failure
        if (!/^[A-Za-z\s]+$/.test(value)) {
            showValidation(input, "Name should only contain letters and spaces");
        } else if (!value.includes(' ')) {
            showValidation(input, "Please include a space after your first name");
        } else {
            showValidation(input, "Please enter a valid full name");
        }
        return false;
    }

    // Additional check for minimum length of each part
    const nameParts = value.split(/\s+/).filter(part => part.length > 0);
    if (nameParts[0].length < 2 || nameParts[1].length < 2) {
        input.classList.add("error");
        input.classList.remove("valid");
        showValidation(input, "Each name should be at least 2 characters long");
        return false;
    }

    input.classList.remove("error");
    input.classList.add("valid");
    hideValidation(input);
    return true;
}

// Add live validation as user types
document.getElementById("name").addEventListener("input", function() {
    validateFullName(this);
});
      function validateEmail(input) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const value = input.value.trim();

        if (!emailRegex.test(value)) {
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
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;

        if (!passwordRegex.test(value)) {
          input.classList.add("error");
          input.classList.remove("valid");
          showValidation(
            input,
            "Password must be at least 8 characters with 1 uppercase, 1 lowercase, and 1 number"
          );
          return false;
        }

        input.classList.remove("error");
        input.classList.add("valid");
        hideValidation(input);
        return true;
      }

      function validateConfirmPassword(input) {
        const password = document.getElementById("password").value;
        const confirmPassword = input.value;

        if (password !== confirmPassword) {
          input.classList.add("error");
          input.classList.remove("valid");
          showValidation(input, "Passwords do not match");
          return false;
        }

        input.classList.remove("error");
        input.classList.add("valid");
        hideValidation(input);
        return true;
      }

      function validateMobile(input) {
        const value = input.value.trim();
        
        // Check if empty
        if (!value) {
            input.classList.add("error");
            input.classList.remove("valid");
            showValidation(input, "Mobile number is required");
            return false;
        }
        
        // Check if it contains only numbers
        if (!/^\d+$/.test(value)) {
            input.classList.add("error");
            input.classList.remove("valid");
            showValidation(input, "Please enter only numbers");
            return false;
        }
        
        // Check if it starts with 4
        if (value.startsWith('4')) {
            input.classList.add("error");
            input.classList.remove("valid");
            showValidation(input, "Mobile number cannot start with 4");
            return false;
        }
        
        // Check if it's exactly 10 digits
        if (value.length !== 10) {
            input.classList.add("error");
            input.classList.remove("valid");
            showValidation(input, "Mobile number must be exactly 10 digits");
            return false;
        }

        input.classList.remove("error");
        input.classList.add("valid");
        hideValidation(input);
        return true;
    }

    // Add live validation as user types
    document.getElementById("mobile").addEventListener("input", function() {
        validateMobile(this);
    });

    function validateAadhar(input) {
        const aadhar = input.value;
        const aadharPattern = /^[0-9]{12}$/;
        if (!aadharPattern.test(aadhar)) {
          input.classList.add("error");
          input.classList.remove("valid");
          showValidation(input, "Please enter a valid 12-digit Aadhar number");
          return false;
        }
        input.classList.remove("error");
        input.classList.add("valid");
        hideValidation(input);
        return true;
    }

    // Add live validation as user types
    document.getElementById("aadhar").addEventListener("input", function() {
        validateAadhar(this);
    });

    function validateForm() {
        const nameValid = validateFullName(document.getElementById("name"));
        const emailValid = validateEmail(document.getElementById("email"));
        const mobileValid = validateMobile(document.getElementById("mobile"));
        const passwordValid = validatePassword(document.getElementById("password"));
        const confirmPasswordValid = validateConfirmPassword(document.getElementById("confirm_password"));
        const aadharValid = validateAadhar(document.getElementById("aadhar"));

        const volunteerFields = document.querySelector(".volunteer-fields");
        
        const isValid = nameValid && emailValid && mobileValid && passwordValid && confirmPasswordValid && aadharValid;
        
        return isValid;
    }

    document.querySelectorAll("input").forEach((input) => {
      input.addEventListener("input", function () {
        if (this.classList.contains("error")) {
          this.classList.remove("error");
        }
      });
    });

    function togglePassword(inputId) {
      const input = document.getElementById(inputId);
      const toggle = input.parentElement.querySelector('.password-toggle');
      
      if (input.type === 'password') {
        input.type = 'text';
        toggle.textContent = '';
      } else {
        input.type = 'password';
        toggle.textContent = '';
      }
    }

    // Add validation state classes
    function setFormGroupState(input, isValid) {
        const formGroup = input.parentElement;
        formGroup.classList.remove('valid', 'error');
        formGroup.classList.add(isValid ? 'valid' : 'error');
    }

    // Update existing validation functions to use new state system
    function validateName() {
        const name = document.getElementById('name');
        const isValid = name.value.trim().split(' ').length >= 2;
        setFormGroupState(name, isValid);
        document.getElementById('name-error').textContent = 
            isValid ? '' : 'Full name must be at least 2 words';
        return isValid;
    }

    function validateEmail() {
        const email = document.getElementById('email');
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const isValid = emailPattern.test(email.value);
        setFormGroupState(email, isValid);
        document.getElementById('email-error').textContent = 
            isValid ? '' : 'Please enter a valid email address';
        return isValid;
    }

    function validateMobile() {
        const mobile = document.getElementById('mobile');
        const isValid = /^[0-9]{10}$/.test(mobile.value);
        setFormGroupState(mobile, isValid);
        document.getElementById('mobile-error').textContent = 
            isValid ? '' : 'Please enter a valid 10-digit mobile number';
        return isValid;
    }

    function validatePassword() {
        const password = document.getElementById('password');
        const isValid = password.value.length >= 8 && 
                       /[A-Z]/.test(password.value) && 
                       /[0-9]/.test(password.value);
        setFormGroupState(password, isValid);
        document.getElementById('password-error').textContent = 
            isValid ? '' : 'Password must be at least 8 characters with 1 uppercase and 1 number';
        return isValid;
    }

    function validateConfirmPassword() {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const isValid = confirmPassword.value === password.value;
        setFormGroupState(confirmPassword, isValid);
        document.getElementById('confirm-password-error').textContent = 
            isValid ? '' : 'Passwords must match';
        return isValid;
    }

    function validateAadhar() {
        const aadhar = document.getElementById('aadhar');
        const isValid = /^[0-9]{12}$/.test(aadhar.value);
        setFormGroupState(aadhar, isValid);
        document.getElementById('aadhar-error').textContent = 
            isValid ? '' : 'Please enter a valid 12-digit Aadhar number';
        return isValid;
    }

    // Add input event listeners
    document.getElementById('name').addEventListener('input', validateName);
    document.getElementById('email').addEventListener('input', validateEmail);
    document.getElementById('mobile').addEventListener('input', validateMobile);
    document.getElementById('password').addEventListener('input', validatePassword);
    document.getElementById('confirm_password').addEventListener('input', validateConfirmPassword);
    document.getElementById('aadhar').addEventListener('input', validateAadhar);
    </script>
  </body>
</html>
<?php
// Flush any remaining output
ob_end_flush();
?>