
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
          <div class="form-group">
            <a href="volunteer_registration.php" class="btn btn-primary">Sign up as Volunteer</a>
          </div>
        </div>
      </div>

      <div class="login-link">
        <p>Already have an account? <a href="login.php">Login here</a></p>
      </div>
    </div>

    