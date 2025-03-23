<?php
session_start();
require_once 'config.php';
include 'connect.php';

// Check if user is logged in and is a donor
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'donor') {
    // Redirect to login page if not logged in or not a donor
    header('Location: login.php');
    exit();
}

// Fetch donor details including profile image
try {
    $stmt = $pdo->prepare("SELECT name, profile FROM donors WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $donor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($donor) {
        // Update session with latest name
        $_SESSION['name'] = $donor['name'];

        // Set profile image, default to 'images.png' if not set
        $_SESSION['profile_image'] = $donor['profile'] ?? 'images.png';
    }
} catch (PDOException $e) {
    // Log error if database fetch fails
    error_log("Error fetching donor details: " . $e->getMessage());
}
<<<<<<< HEAD
=======

// Establish database connection
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch approved beneficiaries
try {
    $stmt = $pdo->prepare("
        SELECT b.*, o.name as orphanage_name 
        FROM beneficiaries b 
        JOIN orphanage o ON b.orphanage_id = o.id 
        WHERE b.status = 'Approved' 
        ORDER BY b.reviewed_at DESC
    ");
    $stmt->execute();
    $beneficiaries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching beneficiaries: " . $e->getMessage());
}
>>>>>>> master
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CHARITEX - Empowering Change Through Giving</title>
<<<<<<< HEAD
=======
    
    <!-- Enhanced Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <!-- Existing CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
>>>>>>> master
    <style>
      /* .profile-section {
    position: fixed;
    top: 20px;
    right: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255, 255, 255, 0.9);
    padding: 8px 15px;
    border-radius: 25px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
} */
.profile {
    position: relative;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.profile img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--primary);
    transition: all 0.3s ease;
}

.profile img:hover {
    transform: scale(1.1);
    box-shadow: 0 0 15px rgba(255, 107, 107, 0.3);
}

.profile-name {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    padding: 1rem;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    display: none;
    min-width: 150px;
    text-align: center;
    margin-top: 0.5rem;
    z-index: 1000;
}

.profile-name:before {
    content: '';
    position: absolute;
    top: -8px;
    right: 20px;
    width: 0;
    height: 0;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-bottom: 8px solid white;
}
      .footer {
  background: linear-gradient(135deg, #2c3e50, black);
  padding: 2rem ;
  color: white;
  margin-top: 1rem;
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  text-align: center;
}

.footer-title {
  font-size: 2rem;
  margin-bottom: 1rem;
  font-weight: 600;
}

.footer-description {
  font-size: 1.1rem;
  margin-bottom: 2rem;
  opacity: 0.9;
}

.newsletter-form {
  display: flex;
  gap: 1rem;
  max-width: 600px;
  margin: 0 auto;
  justify-content: center;
}

.newsletter-input {
  padding: 1rem 1.5rem;
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-radius: 50px;
  background: rgba(255, 255, 255, 0.1);
  color: white;
  font-size: 1rem;
  flex: 1;
  max-width: 400px;
}

.newsletter-input::placeholder {
  color: rgba(255, 255, 255, 0.7);
}

.newsletter-input:focus {
  outline: none;
  border-color: white;
  background: rgba(255, 255, 255, 0.2);
}

.btn-subscribe {
  background: var(--primary);
  color: white;
  border: none;
  padding: 1rem 2rem;
  border-radius: 50px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-subscribe:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

@media (max-width: 768px) {
  .newsletter-form {
    flex-direction: column;
    align-items: center;
  }
  
  .newsletter-input {
    width: 100%;
  }
}
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Poppins", sans-serif;
      }

      :root {
        --primary: #ff6b6b;
        --secondary: #4ecdc4;
        --accent: #45b7d1;
<<<<<<< HEAD
        --background: #f8f9fa;
        --text: #2c3e50;
=======
        --dark: #2c3e50;
        --light: #f8f9fa;
>>>>>>> master
        --success: #2ecc71;
        --warning: #f1c40f;
      }

      body {
        background-color: var(--background);
        color: var(--text);
        line-height: 1.6;
<<<<<<< HEAD
=======
        font-family: 'Poppins', sans-serif;
>>>>>>> master
      }

      .navbar {
        background: rgba(255, 255, 255, 0.95);
        padding: 1rem 2rem;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        position: fixed;
        width: 100%;
        top: 0;
        z-index: 1000;
        transition: all 0.4s ease;
        backdrop-filter: blur(10px);
      }

      /* Update the nav-content class */
/* Update the nav-content class */
.nav-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  padding: 0 1rem;
}

/* Update the nav-links class */
.nav-links {
  display: flex;
  gap: 2.5rem;
  margin-left: auto; /* This pushes the links to the right */
  margin-right: 2rem; /* Add some right margin for spacing */
}

/* Keep the logo class as is */
.logo {
  display: flex;
  align-items: center;
  gap: 1rem;
}

      .logo-img {
        width: 50px;
        height: 50px;
        border-radius: 25px;
        object-fit: cover;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      }

      /* .nav-links {
        display: flex;
        gap: 2.5rem;
      } */
        
      .nav-links a {
        text-decoration: none;
        color: var(--text);
        font-weight: 500;
        position: relative;
        padding: 5px 0;
        transition: all 0.3s ease;
        font-size: 1.1rem;
      }

      .nav-links a:hover {
        color: var(--primary);
      }

      .nav-links a::after {
        content: "";
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        transition: width 0.3s ease;
        border-radius: 2px;
      }

      .nav-links a:hover::after {
        width: 100%;
      }

      .hero {
        background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)),
          url("https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?ixlib=rb-4.0.3");
        background-size: cover;
        background-position: center;
        height: 90vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
        margin-top: 0;
        position: relative;
      }

      /* .hero::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: 
          linear-gradient(135deg, rgba(255, 107, 107, 0.8), rgba(78, 205, 196, 0.8)),
          url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'%3E%3Ccircle cx='3' cy='3' r='3'/%3E%3Ccircle cx='13' cy='13' r='3'/%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.7;
      } */

      .hero-content {
        max-width: 900px;
        padding: 2rem;
        position: relative;
        z-index: 1;
      }

      .hero h1 {
        font-size: 4.5rem;
        margin-bottom: 1.5rem;
        animation: fadeInUp 1.2s ease;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        font-weight: 800;
      }

      .hero p {
        font-size: 1.4rem;
        margin-bottom: 2rem;
        animation: fadeInUp 1.4s ease;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
      }

      .btn {
        display: inline-block;
        padding: 1.2rem 3rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: bold;
        transition: all 0.4s ease;
        border: none;
        cursor: pointer;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
      }

      .btn-primary {
        background: linear-gradient(135deg, var(--primary), #ff8f8f);
        color: white;
        box-shadow: 0 10px 20px rgba(255, 107, 107, 0.3);
      }

      .btn-primary:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(255, 107, 107, 0.4);
      }

      .causes-section {
        padding: 6rem 2rem;
        max-width: 1200px;
        margin: 0 auto;
      }

      .section-title {
        text-align: center;
        margin-bottom: 4rem;
      }

      .section-title h2 {
        font-size: 2.8rem;
        color: var(--text);
        margin-bottom: 1rem;
        position: relative;
        display: inline-block;
      }

      .section-title h2::after {
        content: "";
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        border-radius: 2px;
      }

      .section-title p {
        font-size: 1.2rem;
        color: #666;
      }

      .causes-scroll {
        display: flex;
        overflow-x: auto;
        gap: 20px;
        padding: 20px;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #888 #f1f1f1;
      }

      .causes-scroll::-webkit-scrollbar {
        height: 8px;
      }

      .causes-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
      }

      .causes-scroll::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
      }

      .causes-scroll::-webkit-scrollbar-thumb:hover {
        background: #555;
      }

      .cause-card {
        flex: 0 0 auto;
        width: 350px;
        margin-bottom: 0;
      }

      .custom-block-wrap {
        height: 100%;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
      }

      .custom-block-wrap:hover {
        transform: translateY(-5px);
      }

      .custom-block-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
      }

      .custom-block {
        padding: 20px;
      }

      .custom-block-body {
        min-height: 200px;
      }

      .progress {
        height: 10px;
        border-radius: 5px;
        background-color: #e9ecef;
      }

      .progress-bar {
        background-color: #28a745;
        border-radius: 5px;
      }

      .custom-btn {
        width: 100%;
        text-align: center;
        padding: 10px;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        transition: background-color 0.3s ease;
      }

      .custom-btn:hover {
        background-color: #218838;
        color: white;
      }

      .contact-section {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        padding: 6rem 2rem;
      }

      .contact-form-container {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        padding: 3rem;
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
      }

      .contact-form input,
      .contact-form textarea {
        width: 100%;
        padding: 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 1.1rem;
        transition: all 0.3s ease;
      }

      .contact-form input:focus,
      .contact-form textarea:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2);
      }

      .modal-content {
        background: white;
        padding: 3rem;
        border-radius: 20px;
        width: 90%;
        max-width: 600px;
        position: relative;
        animation: modalFadeIn 0.4s ease;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
      }

      .toast {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: var(--success);
        color: white;
        padding: 1rem 2rem;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(46, 204, 113, 0.3);
        display: none;
        animation: slideIn 0.4s ease;
      }
      .signup-section {
        background: linear-gradient(135deg, #f6f8fa, #e9ecef),
          url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23000000' fill-opacity='0.03' fill-rule='evenodd'%3E%3Ccircle cx='3' cy='3' r='3'/%3E%3Ccircle cx='13' cy='13' r='3'/%3E%3C/g%3E%3C/svg%3E");
        padding: 6rem 2rem;
        color: white;
        margin-top: 4rem;
        text-align: center;
      }

      .signup-container {
        max-width: 800px;
        margin: 0 auto;
        background: rgba(255, 255, 255, 0.1);
        padding: 3rem;
        border-radius: 20px;
        backdrop-filter: blur(10px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
      }

      .signup-grid {
        display: flex;
        flex-direction: column;
        align-items: center; /* Align items to the center */
        gap: 2rem;
      }

      .signup-content h2 {
        font-size: 2.5rem;
        margin-bottom: 1.5rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
      }

      .signup-content p {
        font-size: 1.1rem;
        margin-bottom: 2rem;
        opacity: 0.9;
        text-align: center; /* Center align the text */
      }

      .signup-content ul {
        list-style: none;
        margin-bottom: 2rem;
        padding: 0;
        text-align: left; /* Ensure text aligns left for better readability */
        margin: 0 auto;
        max-width: 600px; /* Restrict width for better layout */
      }

      .signup-content ul li {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
        justify-content: center; /* Align items within the row */
      }


    .signup-form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-group label {
        font-size: 1.1rem;
        font-weight: 500;
    }

    .form-group input {
        padding: 1rem;
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }

    .form-group input::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .form-group input:focus {
        outline: none;
        border-color: white;
        background: rgba(255, 255, 255, 0.2);
    }

    .btn-white {
        background: white;
        color: var(--primary);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .btn-white:hover {
        transform: translateY(-5px);
        color: black;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
    }

    @media (max-width: 768px) {
        .signup-grid {
            grid-template-columns: 1fr;
        }
        
        .signup-content {
            padding-right: 0;
            text-align: center;
        }
    }
      @media (max-width: 768px) {
        .hero h1 {
          font-size: 3rem;
        }

        .nav-links {
          display: none;
        }

        .causes-scroll {
          flex-direction: column;
        }
      }

      @keyframes fadeInUp {
        from {
          opacity: 0;
          transform: translateY(30px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      @keyframes modalFadeIn {
        from {
          opacity: 0;
          transform: translateY(-30px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      @keyframes slideIn {
        from {
          transform: translateX(100%);
          opacity: 0;
        }
        to {
          transform: translateX(0);
          opacity: 1;
        }
      }
<<<<<<< HEAD
    </style>
  </head>
  <body>
    <!-- Rest of the HTML content remains the same, just remove inline styles since they're now in the stylesheet -->
=======

      .card {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
      }

      .card:hover {
        transform: translateY(-5px);
      }

      .card-header {
        background: linear-gradient(45deg, #007bff, #0056b3);
      }

      .btn-outline-primary {
        width: 100%;
        text-align: left;
      }

      .btn-outline-primary:hover {
        background-color: #f8f9fa;
        color: #007bff;
      }

      .card-footer {
        background-color: #fff;
        border-top: none;
        padding: 1rem;
      }

      .btn-success {
        background: linear-gradient(45deg, #28a745, #218838);
        border: none;
        padding: 10px;
      }

      .btn-success:hover {
        background: linear-gradient(45deg, #218838, #1e7e34);
        transform: scale(1.02);
      }

      .modal-header {
        background: linear-gradient(45deg, #007bff, #0056b3);
      }

      .table th {
        color: #6c757d;
        width: 40%;
      }

      .badge {
        font-size: 0.9em;
        padding: 0.5em 1em;
      }

      .modal-body h6 {
        font-weight: 600;
        margin-bottom: 1rem;
      }

      .fst-italic {
        color: #6c757d;
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 0.5rem;
      }

      .card {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
        border: none;
      }

      .card:hover {
        transform: translateY(-5px);
      }

      .card-header {
        background: linear-gradient(45deg, #007bff, #0056b3);
        padding: 1rem;
      }

      .beneficiary-details p {
        font-size: 0.95rem;
        padding: 0.5rem;
        border-bottom: 1px solid #eee;
      }

      .beneficiary-details p:last-child {
        border-bottom: none;
      }

      .beneficiary-details i {
        margin-right: 8px;
        width: 20px;
      }

      .card-footer {
        background-color: #fff;
        border-top: none;
        padding: 1rem;
      }

      .btn-success {
        background: linear-gradient(45deg, #28a745, #218838);
        border: none;
        padding: 10px;
      }

      .btn-success:hover {
        background: linear-gradient(45deg, #218838, #1e7e34);
        transform: scale(1.02);
      }

      .beneficiary-profile-img {
        height: 200px;
        object-fit: cover;
      }

      /* Add this to your existing card styles */
      .card {
        overflow: hidden;
      }

      .card:hover .beneficiary-profile-img {
        transform: scale(1.05);
        transition: transform 0.3s ease;
      }

      /* Add this to create separation between sections */
      .approved-beneficiaries-section {
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
        margin-top: -3rem; /* Adjust this value to reduce gap if needed */
      }

      /* Enhanced Typography */
      h1, h2, h3, h4, h5 {
        font-family: 'Playfair Display', serif;
        font-weight: 700;
      }

      /* Enhanced Card Design */
      .card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      }

      .card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.12);
      }

      .card-img-top {
        height: 250px;
        object-fit: cover;
        transition: all 0.5s ease;
      }

      .card:hover .card-img-top {
        transform: scale(1.1);
      }

      .card-header {
        background: linear-gradient(135deg, var(--primary), #ff8585);
        color: white;
        padding: 1.5rem;
        border: none;
      }

      .card-body {
        padding: 2rem;
      }

      /* Enhanced Buttons */
      .btn {
        padding: 12px 25px;
        border-radius: 50px;
        font-weight: 500;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
      }

      .btn-success {
        background: linear-gradient(135deg, var(--success), #27ae60);
        border: none;
      }

      .btn-success:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(46, 204, 113, 0.2);
      }

      .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(120deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: all 0.5s ease;
      }

      .btn:hover::before {
        left: 100%;
      }

      /* Enhanced Details Section */
      .beneficiary-details p {
        padding: 1rem;
        border-bottom: 1px solid #eee;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 1rem;
      }

      .beneficiary-details i {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--light);
        border-radius: 50%;
        color: var(--primary);
      }

      /* Testimonials Section */
      .testimonials {
        background: var(--light);
        padding: 5rem 0;
      }

      .testimonial-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        margin: 1rem;
        position: relative;
      }

      .testimonial-card::before {
        content: '"';
        position: absolute;
        top: -20px;
        left: 20px;
        font-size: 5rem;
        color: var(--primary);
        opacity: 0.1;
        font-family: serif;
      }

      /* Statistics Section */
      .statistics {
        padding: 3rem 0;
        background: linear-gradient(135deg, var(--primary), #ff8585);
        color: white;
      }

      .stat-item {
        text-align: center;
        padding: 2rem;
      }

      .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
      }

      /* Responsive Design */
      @media (max-width: 768px) {
        .section-title h2 {
          font-size: 2rem;
        }

        .card-body {
          padding: 1.5rem;
        }

        .stat-item {
          margin-bottom: 2rem;
        }
      }

      /* Loading Animation */
      .loading {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
      }

      .loading-spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
      }

      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }

      /* Add these styles for the amount section */
      .amount-section {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
      }

      .amount-section h6 {
        color: var(--primary);
        font-weight: 600;
      }

      .progress {
        background-color: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
      }

      .progress-bar {
        transition: width 0.3s ease;
      }

      .badge {
        padding: 0.5em 1em;
        font-weight: 500;
      }
    </style>
  </head>
  <body>
    <!-- Loading Animation -->
    <div class="loading" id="loadingScreen">
        <div class="loading-spinner"></div>
    </div>

>>>>>>> master
    <nav class="navbar">
      <div class="nav-content">
        <div class="logo">
          <img src="logo.png" alt="Logo" class="logo-img" />
          <span style="color: #ff6b6b; font-weight: bold; font-size: 24px"
            >CHARITEX</span
          >
        </div>
        <div class="nav-links">
          <a href="#home">Home</a>
          <a href="#causes">Causes</a>
<<<<<<< HEAD
          <a href="signup.php">Volunteer</a>
=======
          <a href="#volunteer">Volunteer</a>
>>>>>>> master
          <a href="#contact">About Us</a>
          <div class="nav-links">
    
    <?php
    if(isset($_SESSION['name'])) {
        echo '
        <div class="profile">
        
            <img src="' . (isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'images.png') . '" 
                 alt="Profile" 
                 onclick="toggleName()" />
            <div class="profile-name" id="profileName">
                ' . htmlspecialchars($_SESSION['name']) . '
                <br>
                <a href="update_profile.php" style="font-size: 0.9em; color: var(--primary);">Edit Profile</a>
                <br>
                <a href="logout.php" style="font-size: 0.9em; color: var(--primary);">Logout</a>
            </div>
        </div>';
    } else {
        echo '<a href="login.php">Login</a>';
    }
    ?>
</div>
          <!-- <?php 
          // echo $_SESSION['name'];
          ?> -->
          
          <!-- // if(isset($_SESSION['user_id'])){
          //   echo '<a href="logout.php">Logout</a>';
          // }
          // else{
          //   echo '<a href="login.php">Login</a>';
          } -->
        
        </div>
      </div>
    </nav>

    <section class="hero" id="home">
      <div class="hero-content">
        <h1>Make a Difference Today</h1>
        <p>
          Join us in creating positive change through charitable giving and
          volunteering
        </p>
        <div style="margin-top: 2rem">
          <a href="#causes" class="btn btn-primary">Donate Now</a>
        </div>
      </div>
    </section>

<<<<<<< HEAD
    <!-- Causes Section with updated content structure -->
    <section class="causes-section section-padding">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="section-title-wrap mb-5">
                        <h2 class="section-title">Active Causes</h2>
                        <div class="section-sub-title">
                            
                        </div>
                    </div>
                </div>
            </div>
=======

>>>>>>> master

            <div class="causes-scroll">
                <?php
                try {
                    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    // Get active causes with their donation totals
<<<<<<< HEAD
                    $stmt = $pdo->query("
                        SELECT 
=======
                    $stmt = $pdo->query(" SELECT 
>>>>>>> master
                            c.*,
                            COALESCE(SUM(d.amount), 0) as total_donations,
                            COUNT(DISTINCT d.donor_id) as donor_count
                        FROM causes c
                        LEFT JOIN donations d ON c.id = d.cause_id
                        WHERE c.status = 'active'
                        GROUP BY c.id
                        ORDER BY c.created_at DESC
                    ");
                    $causes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($causes as $cause) {
                        $progress = ($cause['total_donations'] / $cause['goal_amount']) * 100;
                        $progress = min(100, $progress); // Cap at 100%
                        ?>
                        <div class="cause-card">
                            <div class="custom-block-wrap">
                                <img src="<?php echo htmlspecialchars($cause['image_url']); ?>" 
                                     class="custom-block-image img-fluid" 
                                     alt="<?php echo htmlspecialchars($cause['title']); ?>">

                                <div class="custom-block">
                                    <div class="custom-block-body">
                                        <h5 class="mb-3"><?php echo htmlspecialchars($cause['title']); ?></h5>

                                        <p><?php echo substr(htmlspecialchars($cause['description']), 0, 150); ?>...</p>

                                        <div class="progress mt-4">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?php echo $progress; ?>%" 
                                                 aria-valuenow="<?php echo $progress; ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center my-2">
                                            <p class="mb-0">
                                                <strong>Raised:</strong> 
                                                $<?php echo number_format($cause['total_donations'], 2); ?>
                                            </p>

                                            <p class="ms-auto mb-0">
                                                <strong>Goal:</strong>
                                                $<?php echo number_format($cause['goal_amount'], 2); ?>
                                            </p>
                                        </div>
                                    </div>

                                    <a href="donate.php?cause_id=<?php echo $cause['id']; ?>" 
                                       class="custom-btn btn">Donate Now</a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } catch (PDOException $e) {
                    echo '<div class="alert alert-danger">Error loading causes. Please try again later.</div>';
                    error_log("Database Error: " . $e->getMessage());
                }
                ?>
            </div>
        </div>
    </section>
<<<<<<< HEAD
    <section class="signup-section">
=======
    <section class="orphanage-funding-section py-5">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="text-center">Active Causes</h2>
                    <p class="text-center">Support our orphanages by contributing to their specific needs</p>
                </div>
            </div>
            
            <div class="row">
                <?php
                try {
                    // Simple query to fetch only approved requests
                    $stmt = $pdo->prepare("
                        SELECT 
                            fr.*,
                            o.name as orphanage_name
                        FROM funding_requests fr
                        JOIN orphanage o ON fr.orphanage_id = o.id
                        WHERE fr.status = 'approved'
                        ORDER BY fr.created_at DESC
                    ");
                    
                    $stmt->execute();
                    $funding_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($funding_requests)) {
                        echo '<div class="col-12 text-center">';
                        echo '<p>No active funding requests available at the moment.</p>';
                        echo '</div>';
                    } else {
                        foreach ($funding_requests as $request) {
                            ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <img src="<?php echo htmlspecialchars($request['image_path']); ?>" 
                                         class="card-img-top" alt="Funding Request Image"
                                         style="height: 200px; object-fit: cover;">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($request['title']); ?></h5>
                                        <h6 class="text-muted"><?php echo htmlspecialchars($request['orphanage_name']); ?></h6>
                                        
                                        <div class="d-flex justify-content-between mb-3">
                                            <span>Goal Amount: ₹<?php echo number_format($request['goal_amount'], 2); ?></span>
                                        </div>
                                        
                                        <p class="card-text">
                                            <?php 
                                                echo substr(htmlspecialchars($request['description']), 0, 100); 
                                                echo strlen($request['description']) > 100 ? '...' : '';
                                            ?>
                                        </p>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                Duration: <?php echo date('d M Y', strtotime($request['start_date'])); ?> - 
                                                <?php echo date('d M Y', strtotime($request['end_date'])); ?>
                                            </small>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <button class="btn btn-primary w-100" 
                                                    onclick="showDonationModal(<?php echo $request['id']; ?>, 
                                                                         '<?php echo htmlspecialchars($request['title']); ?>', 
                                                                         <?php echo $request['goal_amount']; ?>)">
                                                Donate Now
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                } catch (PDOException $e) {
                    error_log("Error fetching funding requests: " . $e->getMessage());
                    echo '<div class="col-12 text-center text-danger">';
                    echo '<p>Error loading funding requests. Please try again later.</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </section>
    <section class="approved-beneficiaries-section py-5 bg-light">
        <div class="container">
            <div class="section-title text-center mb-5">
                <h2>Approved Beneficiaries</h2>
                <p>Support children in need of educational and medical assistance</p>
            </div>

            <div class="row">
                <?php if (empty($beneficiaries)): ?>
                    <div class="col-12 text-center">
                        <p class="alert alert-info">No approved beneficiaries available at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($beneficiaries as $beneficiary): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <?php if ($beneficiary['profile_image']): ?>
                                    <img src="<?php echo htmlspecialchars($beneficiary['profile_image']); ?>" 
                                         class="card-img-top beneficiary-profile-img" 
                                         alt="Child's Photo">
                                <?php endif; ?>
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">
                                        <?php echo htmlspecialchars($beneficiary['support_type']); ?> Support Request
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="beneficiary-details">
                                        <div class="amount-section mb-3">
                                            <h6 class="text-primary mb-2">Required Amount</h6>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="h4 mb-0">₹<?php echo number_format($beneficiary['required_amount'], 2); ?></span>
                                                <span class="badge bg-primary">Goal</span>
                                            </div>
                                            
                                            <div class="progress mt-2" style="height: 10px;">
                                                <div class="progress-bar bg-success" 
                                                     role="progressbar" 
                                                     style="width: <?php echo ($beneficiary['collected_amount'] ?? 0) / $beneficiary['required_amount'] * 100; ?>%" 
                                                     aria-valuenow="<?php echo ($beneficiary['collected_amount'] ?? 0) / $beneficiary['required_amount'] * 100; ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            
                                            <?php if (isset($beneficiary['collected_amount'])): ?>
                                            <small class="text-muted">
                                                Raised: ₹<?php echo number_format($beneficiary['collected_amount'], 2); ?>
                                            </small>
                                            <?php endif; ?>
                                        </div>

                                        <p class="mb-2">
                                            <i class="fas fa-home text-primary"></i>
                                            <strong>Orphanage:</strong> 
                                            <?php echo htmlspecialchars($beneficiary['orphanage_name']); ?>
                                        </p>
                                        
                                        <p class="mb-2">
                                            <i class="fas fa-calendar text-primary"></i>
                                            <strong>Date of Birth:</strong> 
                                            <?php echo date('d M Y', strtotime($beneficiary['date_of_birth'])); ?>
                                        </p>
                                        
                                        <p class="mb-2">
                                            <i class="fas fa-user-circle text-primary"></i>
                                            <strong>Age:</strong> 
                                            <?php 
                                                $dob = new DateTime($beneficiary['date_of_birth']);
                                                $now = new DateTime();
                                                $age = $now->diff($dob)->y;
                                                echo $age; 
                                            ?> years
                                        </p>
                                        
                                        <p class="mb-2">
                                            <i class="fas fa-venus-mars text-primary"></i>
                                            <strong>Gender:</strong> 
                                            <?php echo htmlspecialchars($beneficiary['gender']); ?>
                                        </p>
                                        
                                        <p class="mb-2">
                                            <i class="fas fa-graduation-cap text-primary"></i>
                                            <strong>Support Type:</strong> 
                                            <?php echo htmlspecialchars($beneficiary['support_type']); ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-success w-100" 
                                            onclick="location.href='donate.php?beneficiary_id=<?php echo $beneficiary['id']; ?>'">
                                        <i class="fas fa-hand-holding-heart"></i> Support This Child
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <!-- <section class="signup-section">
>>>>>>> master
      <div class="signup-container">
          <div class="signup-grid">
              <div class="signup-content">
                  <h2 style="color: black;">Join Our Community</h2>
                  <p style="color: black;">Create an account to track your donations, get personalized recommendations, and connect with causes you care about.</p>
                  <ul style="list-style: none; margin-bottom: 2rem; color: black">
                      <li style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; color: black">
                          ✨ Track your donation impact
                      </li>
                      <li style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; color: black">
                          ✨ Get tax deduction receipts
                      </li>
                      <li style="display: flex; align-items: center; gap: 0.5rem; color: black;">
                          ✨ Connect with like-minded donors
                      </li>
                  </ul>
                  <button class="btn btn-white ">Create an Account</button>
                  <p style="text-align: center; font-size: 0.9rem; opacity: 0.8; margin-top: 1rem;color: black;">
                      Already have an account? <a href="login.html" style="color: black; text-decoration: underline;">Log in</a>
                  </p>
              </div>
          </div>
<<<<<<< HEAD
      </div>
      
  </section>
  <footer class="footer">
    <div class="footer-content">
      <h2 class="footer-title">Stay Updated</h2>
      <p class="footer-description">Subscribe to our newsletter for updates on causes and impact stories</p>
      <form class="newsletter-form" onsubmit="event.preventDefault();">
        <input 
          type="email" 
          class="newsletter-input" 
          placeholder="Enter your email address"
          required
        >
        <button type="submit" class="btn-subscribe">Subscribe</button>
      </form>
=======
      </div> -->
      
  </section>
  
>>>>>>> master
    </div>
  </footer>
  </body>
  <script>
function toggleName() {
    const profileName = document.getElementById('profileName');
    if (profileName.style.display === 'block') {
        profileName.style.display = 'none';
    } else {
        profileName.style.display = 'block';
    }
}

// Close profile name when clicking outside
document.addEventListener('click', function(event) {
    const profile = document.querySelector('.profile');
    const profileName = document.getElementById('profileName');
    
    if (!profile.contains(event.target) && profileName.style.display === 'block') {
        profileName.style.display = 'none';
    }
});

function openEditProfileModal() {
    document.getElementById('editProfileModal').style.display = 'block';
    document.getElementById('profileName').style.display = 'none';
}

function closeEditProfileModal() {
    document.getElementById('editProfileModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('editProfileModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeEditProfileModal();
    }
});
<<<<<<< HEAD
=======

function showDonationModal(causeId, title, goalAmount) {
    document.getElementById('cause_id').value = causeId;
    const modal = new bootstrap.Modal(document.getElementById('donationModal'));
    modal.show();
}
</script>

<!-- Add this modal at the bottom of your page, before the closing </body> tag -->
<div class="modal fade" id="donationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Make a Donation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="donationForm" action="process_donation.php" method="POST">
                    <input type="hidden" name="cause_id" id="cause_id">
                    <div class="mb-3">
                        <label for="donation_amount" class="form-label">Donation Amount (₹)</label>
                        <input type="number" class="form-control" id="donation_amount" 
                               name="amount" min="100" step="100" required>
                    </div>
                    <div class="mb-3">
                        <label for="donor_name" class="form-label">Your Name</label>
                        <input type="text" class="form-control" id="donor_name" 
                               name="donor_name" value="<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="donor_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="donor_email" 
                               name="donor_email" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Proceed to Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Section -->

<!-- Testimonials Section -->
<section class="testimonials">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>What Our Donors Say</h2>
            <p>Real stories from our generous supporters</p>
        </div>
        <div class="row">
            <div class="col-md-4" data-aos="fade-up">
                <div class="testimonial-card">
                    <p>"Making a difference in children's lives through this platform has been incredibly rewarding."</p>
                    <div class="testimonial-author">
                        <strong>Rahul Sharma</strong>
                        <div>Regular Donor</div>
                    </div>
                </div>
            </div>
            <!-- Add more testimonials -->
        </div>
    </div>
</section>
<section class="statistics">
    <div class="container">
        <div class="row">
            <div class="col-md-3 col-6">
                <div class="stat-item" data-aos="fade-up">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Children Supported</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-number">₹10M+</div>
                    <div class="stat-label">Funds Raised</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Partner Orphanages</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-number">1000+</div>
                    <div class="stat-label">Active Donors</div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Enhanced Footer -->
<footer class="footer bg-dark text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>About CHARITEX</h5>
                <p>Empowering change through giving and making a difference in children's lives.</p>
                <div class="social-links">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="col-md-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-white">About Us</a></li>
                    <li><a href="#" class="text-white">How It Works</a></li>
                    <li><a href="#" class="text-white">Contact Us</a></li>
                    <li><a href="#" class="text-white">Privacy Policy</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Newsletter</h5>
                <p>Stay updated with our latest news and updates.</p>
                <form class="newsletter-form">
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Your email">
                        <button class="btn btn-primary">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // Initialize AOS
    AOS.init({
        duration: 1000,
        once: true
    });

    // Loading Screen
    window.addEventListener('load', function() {
        document.getElementById('loadingScreen').style.display = 'none';
    });

    // Success Toast
    function showSuccessToast(message) {
        // Implementation of success toast
    }
>>>>>>> master
</script>
</html>
