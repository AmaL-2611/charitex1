<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .registration-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .steps-container {
            margin: 30px 0;
        }
        .step {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .download-btn {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container registration-container">
        <h2 class="text-center mb-4">Volunteer Registration Process</h2>
        
        <div class="alert alert-info" role="alert">
            <strong>Important:</strong> Please complete all steps to become a registered volunteer.
        </div>

        <div class="steps-container">
            <div class="step">
                <h5>Step 1: Download Police Verification Form</h5>
                <p>Download and print the police verification form using the button below.</p>
                <a href="./verification/verification-form.docx" 
                   class="btn btn-primary download-btn" 
                   download="verification-form.docx"
                   onclick="return confirm('Download police verification form?')">
                    <i class="fas fa-download"></i> Download Verification Form
                </a>
            </div>

            <div class="step">
                <h5>Step 2: Complete the Form</h5>
                <p>Fill out all required information in the downloaded form.</p>
            </div>

            <div class="step">
                <h5>Step 3: Police Station Verification</h5>
                <p>Visit your local police station to get the form signed and stamped.</p>
            </div>

            <div class="step">
                <h5>Step 4: Upload Verified Document</h5>
                <form action="upload_verification.php" method="post" enctype="multipart/form-data">
                   
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div> -->
                  
                    <div class="mb-3">
                        <label for="verificationDoc" class="form-label">Upload Signed Police Verification Form</label>
                        <input type="file" class="form-control" id="verificationDoc" name="verificationDoc" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                    <button type="submit" class="btn btn-success">Submit Verification Document</button>
                </form>
            </div>
        </div>

        <div class="text-center mt-4">
            <p class="text-muted">Need help? Contact our support team at charitex@gmail.com</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
</body>
</html> 