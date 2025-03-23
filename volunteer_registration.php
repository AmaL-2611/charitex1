<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Registration - CHARITEX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .steps-container {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .step {
            padding: 15px;
            border-left: 3px solid #1a2a6c;
            margin-bottom: 15px;
            background: white;
            border-radius: 8px;
        }

        .step-number {
            color: #1a2a6c;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .download-btn {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 10px 0;
            transition: all 0.3s ease;
        }

        .download-btn:hover {
            background: #218838;
            color: white;
            transform: translateY(-2px);
        }

        .proceed-btn {
            background: linear-gradient(45deg, #1a2a6c, #b21f1f);
            color: white;
            padding: 15px 30px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 20px 0;
            transition: all 0.3s ease;
            border: none;
            font-size: 1.1rem;
        }

        .proceed-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26,42,108,0.2);
            color: white;
        }

        .verification-alert {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <!-- Verification Steps Section -->
        <div class="verification-alert">
            <h4><i class="fas fa-exclamation-triangle me-2"></i>Important Notice</h4>
            <p>Please complete all steps to become a registered volunteer.</p>
        </div>

        <div class="steps-container">
            <div class="step">
                <div class="step-number">Step 1: Download Police Verification Form</div>
                <p>Download and print the police verification form using the button below.</p>
                <a href="verification/verification-form.docx" class="download-btn" download>
                    <i class="fas fa-download"></i> Download Form
                </a>
            </div>

            <div class="step">
                <div class="step-number">Step 2: Complete the Form</div>
                <p>Fill out all required information in the downloaded form.</p>
            </div>

            <div class="step">
                <div class="step-number">Step 3: Police Station Verification</div>
                <p>Visit your local police station to get the form signed and stamped.</p>
            </div>
        </div>

        <!-- Proceed Button -->
        <div class="text-center">
            <a href="volunteer_signup.php" class="proceed-btn">
                <i class="fas fa-user-plus me-2"></i>
                Proceed to Volunteer Registration
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 