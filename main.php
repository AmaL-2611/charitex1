<!DOCTYPE html>
<html lang="en" style="scroll-behavior: smooth;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHARITEX - Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a73e8;
            --secondary: #34a853;
            --accent: #fbbc04;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            min-height: 100vh;
            background-image: url("n1.jpg");
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
            z-index: 0;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease-in-out;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            transition: transform 0.3s ease;
        }

        .logo:hover img {
            transform: scale(1.1);
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: white;
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a.active {
            border-bottom: 2px solid white;
        }

        .hamburger {
            display: none;
            cursor: pointer;
            color: white;
            font-size: 1.5rem;
        }

        .hero {
            position: relative;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 1.5rem;
            z-index: 1;
            color: white;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1.2rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            animation: fadeIn 1s ease-in-out;
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            max-width: 600px;
            line-height: 1.5;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            animation: fadeIn 1.5s ease-in-out;
        }

        .cta-button {
            padding: 0.8rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            background: linear-gradient(135deg, #ff7e5f, #feb47b);
            border: none;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            animation: fadeIn 2s ease-in-out;
        }

        .cta-button:hover {
            background: linear-gradient(135deg, #feb47b, #ff7e5f);
            transform: translateY(-3px);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.8);
        }

        footer {
            text-align: center;
            padding: 1rem;
            color: white;
            background: rgba(0, 0, 0, 0.6);
            position: fixed;
            width: 100%;
            bottom: 0;
            z-index: 1;
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

        @media (max-width: 768px) {
            .hamburger {
                display: block;
            }

            .nav-links {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: rgba(0, 0, 0, 0.9);
                flex-direction: column;
                padding: 2rem;
                transition: left 0.3s ease;
            }

            .nav-links.active {
                left: 0;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">
            <img src="logo.png" alt="CHARITEX Logo">
        </a>
        <div class="hamburger">
            <i class="fas fa-bars"></i>
        </div>
        <div class="nav-links">
            <a href="#about" class="active">About Us</a>
            <a href="signup_orphanage.php">Register Orphanage</a>
            <a href="signup.php">Register as Donor</a>
            <a href="signup.php">Register as Volunteer</a>
            <a href="contact.php">Contact</a>
        </div>
    </nav>

    <section class="hero">
        <h1>Make a Difference Today</h1>
        <p>"The best way to find yourself is to lose yourself in the service of others." - Mahatma Gandhi</p>
        <a href="signup.php" class="cta-button">Join Us Today</a>
    </section>


    <script>
        // Mobile menu toggle
        const hamburger = document.querySelector('.hamburger');
        const navLinks = document.querySelector('.nav-links');

        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });

        // Navbar scroll effect
        let lastScroll = 0;
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            const navbar = document.querySelector('.navbar');

            if (currentScroll > lastScroll) {
                navbar.style.transform = 'translateY(-100%)';
            } else {
                navbar.style.transform = 'translateY(0)';
            }

            if (currentScroll === 0) {
                navbar.style.background = 'rgba(255, 255, 255, 0.1)';
            } else {
                navbar.style.background = 'rgba(0, 0, 0, 0.9)';
            }

            lastScroll = currentScroll;
        });

        // Add active class to current page
        const currentLocation = location.href;
        const menuItems = document.querySelectorAll('.nav-links a');
        menuItems.forEach(item => {
            if(item.href === currentLocation) {
                item.classList.add('active');
            }
        });
    </script>
</body>
</html>