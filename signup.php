<?php
include 'connect.php';

$signup_errors = array();
$districts = [
  'Thiruvananthapuram', 'Kollam', 'Pathanamthitta', 'Alappuzha',
  'Kottayam', 'Idukki', 'Ernakulam', 'Thrissur',
  'Palakkad', 'Malappuram', 'Kozhikode', 'Wayanad',
  'Kannur', 'Kasaragod'
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST["name"];
  $email = $_POST["email"];
  $mobile = $_POST["mobile"];
  $password = $_POST["password"];
  $confirm_password = $_POST["confirm_password"];
  $role = $_POST["role"];

  if ($role === 'volunteer') {
    $location = isset($_POST["location"]) ? $_POST["location"] : '';
    $availability = $_POST["availability"];
    $skills = $_POST["skills"];

    // Add location validation for volunteers
    if (empty($location)) {
      $signup_errors[] = "Please select your district";
    } elseif (!in_array($location, $districts)) {
      $signup_errors[] = "Please select a valid district";
    }
  }

  // Validate form data
  if (empty($name)) {
    $signup_errors[] = "Name is required";
  }
  if (empty($email)) {
    $signup_errors[] = "Email is required";
  }
  if (empty($mobile)) {
    $signup_errors[] = "Mobile number is required";
  }
  if (empty($password)) {
    $signup_errors[] = "Password is required";
  }
  if (empty($confirm_password)) {
    $signup_errors[] = "Confirm password is required";
  }
  if ($password !== $confirm_password) {
    $signup_errors[] = "Passwords do not match";
  }

  // If no errors, proceed with account creation
  if (empty($signup_errors)) {
    try {
      // Hash password
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);

      // Insert into appropriate table based on role
      if ($role === 'volunteer') {
        $insert_stmt = $pdo->prepare(
          "INSERT INTO volunteers 
          (name, email, mobile, password, location, skills, availability) 
          VALUES (:name, :email, :mobile, :password, :location, :skills, :availability)"
        );
        $insert_stmt->execute([
          ':name' => $name,
          ':email' => $email,
          ':mobile' => $mobile,
          ':password' => $hashed_password,
          ':location' => $location,
          ':skills' => $skills,
          ':availability' => $availability
        ]);
      } else { // donor
        $insert_stmt = $pdo->prepare(
          "INSERT INTO donors 
          (name, email, mobile, password) 
          VALUES (:name, :email, :mobile, :password)"
        );
        $insert_stmt->execute([
          ':name' => $name,
          ':email' => $email,
          ':mobile' => $mobile,
          ':password' => $hashed_password
        ]);
      }

      // Redirect to login page
      header("Location: login.php");
      exit();
    } catch (PDOException $e) {
      // Log the error
      error_log("Signup Error: " . $e->getMessage());
      // Optionally, you can display a generic error message to the user
      $signup_errors[] = "An error occurred during registration. Please try again.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CHARITEX - Sign Up</title>

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
        background: rgba(255, 255, 255, 0.95);
        padding: 3rem;
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        width: 100%;
        max-width: 500px;
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

      .role-selector {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
      }

      .role-option {
        flex: 1;
        text-align: center;
        padding: 1.5rem;
        border: 2px solid #e1e1e1;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.9);
      }

      .role-option h3 {
        color: #1a2a6c;
        margin-bottom: 0.5rem;
        font-size: 1.2rem;
      }

      .role-option p {
        color: #666;
        font-size: 0.9rem;
      }

      .role-option:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(26, 42, 108, 0.1);
      }

      .role-option.active {
        border-color: #1a2a6c;
        background: rgba(26, 42, 108, 0.05);
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

      input, select {
        width: 100%;
        padding: 1rem;
        border: 2px solid #e1e1e1;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.9);
      }

      input:focus, select:focus {
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

      .error-message {
        color: #ff4444;
        font-size: 0.85rem;
        margin-top: 0.5rem;
        display: none;
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

      .login-link {
        text-align: center;
        color: #666;
        font-size: 0.95rem;
      }

      .login-link a {
        color: #1a2a6c;
        text-decoration: none;
      }

      .volunteer-fields {
        display: none;
      }

      .district-select {
        width: 100%;
        padding: 12px;
        border: 2px solid #e1e1e1;
        border-radius: 8px;
        font-size: 1rem;
        color: #333;
        background-color: #fff;
        transition: all 0.3s ease;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 16px;
      }

      .district-select:focus {
        outline: none;
        border-color: #1a2a6c;
        box-shadow: 0 0 0 3px rgba(26, 42, 108, 0.1);
      }

      .district-select:hover {
        border-color: #1a2a6c;
      }

      .district-select option {
        padding: 12px;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="header">
        <h1>Welcome to CHARITEX</h1>
        <p>Join us in making a difference</p>
      </div>

      <form id="signupForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" onsubmit="return validateForm()">
        <?php
          if(isset($signup_errors)) {
            foreach ($signup_errors as $error) {
              echo '<div class="error-message" style="display: block; color: #ff4444; text-align: center; margin-bottom: 15px;">' . $error . '</div>';
            }
          }
        ?>
        <div class="role-selector">
          <div class="role-option active" onclick="selectRole('donor')">
            <h3>Donor</h3>
            <p>Support causes</p>
            <input type="hidden" name="role" id="role" value="donor">
          </div>
          <div class="role-option" onclick="selectRole('volunteer')">
            <h3>Volunteer</h3>
            <p>Offer your time</p>
          </div>
        </div>

        <div class="form-group">
          <label for="name">Full Name</label>
          <input
            type="text"
            id="name"
            name="name"
            required
            onfocus="showValidation(this, 'Full name must be at least 2 words')"
            onblur="validateFullName(this)"
          />
          <div class="error-message" id="name-error"></div>
        </div>

        <div class="form-group">
          <label for="email">Email Address</label>
          <input
            type="email"
            id="email"
            name="email"
            required
            onfocus="showValidation(this, 'Enter a valid email address')"
            onblur="validateEmail(this)"
          />
          <div class="error-message" id="email-error"></div>
        </div>

        <div class="form-group">
          <label for="mobile">Mobile Number</label>
          <input
            type="tel"
            id="mobile"
            name="mobile"
            required
            pattern="[0-9]{10}"
            onfocus="showValidation(this, 'Enter a valid 10-digit mobile number')"
            onblur="validateMobile(this)"
            onkeyup="validateMobile(this)"
            placeholder="Enter 10-digit mobile number"
          />
          <div class="error-message" id="mobile-error"></div>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="password-container" style="position: relative;">
            <input
              type="password"
              id="password"
              name="password"
              required
              onfocus="showValidation(this, 'Password must be at least 8 characters with 1 uppercase, 1 lowercase, and 1 number')"
              onblur="validatePassword(this)"
            />
            <span class="password-toggle" onclick="togglePassword('password')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer;">
              
            </span>
          </div>
          <div class="error-message" id="password-error"></div>
        </div>

        <div class="form-group">
          <label for="confirm_password">Confirm Password</label>
          <div class="password-container" style="position: relative;">
            <input
              type="password"
              id="confirm_password"
              name="confirm_password"
              required
              onfocus="showValidation(this, 'Passwords must match')"
              onblur="validateConfirmPassword(this)"
            />
            <span class="password-toggle" onclick="togglePassword('confirm_password')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer;">
              
            </span>
          </div>
          <div class="error-message" id="confirm_password-error"></div>
        </div>

        <div class="volunteer-fields" style="display: none;">
          <div class="form-group">
            <label for="location">Select Your District</label>
            <select 
              id="location" 
              name="location" 
              class="district-select"
              onchange="validateLocation(this)"
            >
              <option value="">-- Select District --</option>
              <?php
              foreach ($districts as $district) {
                echo '<option value="' . $district . '">' . $district . '</option>';
              }
              ?>
            </select>
            <div class="error-message" id="location-error"></div>
          </div>

          <div class="form-group">
            <label for="availability">Availability</label>
            <select id="availability" name="availability">
              <option value="weekdays">Weekdays</option>
              <option value="weekends">Weekends</option>
              <option value="flexible">Flexible</option>
            </select>
          </div>

          <div class="form-group">
            <!-- <label>Support Areas</label> -->
            <!-- <div>
              <input type="checkbox" id="supportEducation" name="supportEducation" value="true">
              <label for="supportEducation">Education</label>
              <input type="checkbox" id="supportOrphans" name="supportOrphans" value="true">
              <label for="supportOrphans">Orphans</label>
              <input type="checkbox" id="supportElders" name="supportElders" value="true">
              <label for="supportElders">Elders</label>
            </div> -->
          </div>

          <div class="form-group">
            <!-- <label for="skills">Skills (Optional)</label>
            <input
              type="text"
              id="skills"
              name="skills"
              placeholder="e.g., Teaching, Cooking, Medical"
            /> -->
          </div>
        </div>

        <button type="submit">Create Account</button>

        <div class="login-link">
          Already have an account? <a href="login.php">Log in</a>
        </div>
      </form>
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

    function validateLocation() {
        const errorElement = document.getElementById('location-error');
        
        if (document.querySelector('.role-option.active').textContent.includes('Volunteer')) {
          const selectedDistrict = document.querySelector('#location').value;
          
          if (!selectedDistrict) {
            errorElement.textContent = 'Please select your district';
            errorElement.style.display = 'block';
            return false;
          } else {
            errorElement.style.display = 'none';
            return true;
          }
        }
        return true;
      }

      function validateForm() {
        const isNameValid = validateFullName(document.getElementById('name'));
        const isEmailValid = validateEmail(document.getElementById('email'));
        const isMobileValid = validateMobile(document.getElementById('mobile'));
        const isPasswordValid = validatePassword(document.getElementById('password'));
        const isConfirmPasswordValid = validateConfirmPassword(document.getElementById('confirm_password'));
        const isLocationValid = validateLocation();

        return isNameValid && isEmailValid && isMobileValid && 
               isPasswordValid && isConfirmPasswordValid && isLocationValid;
      }

      function showError(input, errorElement, message) {
        input.classList.add("error");
        input.classList.remove("valid");
        errorElement.textContent = message;
        errorElement.style.display = "block";
      }

      function hideError(input, errorElement) {
        input.classList.remove("error");
        input.classList.add("valid");
        errorElement.style.display = "none";
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
    </script>
  </body>
</html>