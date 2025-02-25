<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CHARITEX - Empowering Change Through Giving</title>
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
        --background: #f8f9fa;
        --text: #2c3e50;
        --success: #2ecc71;
        --warning: #f1c40f;
      }

      body {
        background-color: var(--background);
        color: var(--text);
        line-height: 1.6;
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

      .causes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 2.5rem;
      }

      .cause-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.4s ease;
        position: relative;
      }

      .cause-card:hover {
        transform: translateY(-15px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
      }

      .cause-image {
        width: 100%;
        height: 250px;
        position: relative;
        overflow: hidden;
      }

      .cause-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
      }

      .cause-card:hover .cause-image img {
        transform: scale(1.1);
      }

      .cause-content {
        padding: 2rem;
      }

      .cause-content h3 {
        font-size: 1.8rem;
        margin-bottom: 1rem;
        color: var(--text);
      }

      .cause-content p {
        color: #666;
        margin-bottom: 1.5rem;
        font-size: 1.1rem;
      }

      .progress-bar {
        width: 100%;
        height: 12px;
        background: #e2e8f0;
        border-radius: 6px;
        margin: 1.5rem 0;
        overflow: hidden;
      }

      .progress {
        width: 0;
        height: 100%;
        background: linear-gradient(90deg, var(--success), #27ae60);
        border-radius: 6px;
        transition: width 1.5s ease-in-out;
      }

      .stats {
        display: flex;
        justify-content: space-between;
        color: #64748b;
        font-size: 1.1rem;
        margin-bottom: 1.5rem;
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

        .causes-grid {
          grid-template-columns: 1fr;
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
    </style>
  </head>
  <body>
    <!-- Rest of the HTML content remains the same, just remove inline styles since they're now in the stylesheet -->
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
          <a href="#volunteer">Volunteer</a>
          <a href="#contact">About Us</a>
          <a href="login.php">Login</a>
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

    <!-- Causes Section with updated content structure -->
    <section class="causes-section" id="causes">
      <div class="section-title">
        <h2>Active Causes</h2>
        <p>
          Support these meaningful initiatives and help create lasting change
        </p>
      </div>

      <div class="causes-grid">
        <!-- Cause cards with the same structure but remove inline styles -->
        <!-- Education Support Card -->
        <div class="cause-card">
          <div class="cause-image">
            <img src="edu.jpg" alt="Education Support" />
          </div>
          <div class="cause-content">
            <h3>Education Support</h3>
            <p>Help provide quality education to underprivileged children</p>
            <div class="progress-bar">
              <div class="progress" data-progress="75"></div>
            </div>
            <div class="stats">
              <span>Raised: ₹75,000</span>
              <span>Goal: ₹100,000</span>
            </div>
            <button
              onclick="openDonateModal('Education Support')"
              class="btn btn-primary"
            >
              Donate
            </button>
          </div>
        </div>

        <!-- Orphan Care Card -->
        <div class="cause-card">
          <div class="cause-image">
            <img src="orphan.jpg" alt="Orphan Care" />
          </div>
          <div class="cause-content">
            <h3>Orphan Care</h3>
            <p>Support children in need of care and protection</p>
            <div class="progress-bar">
              <div class="progress" data-progress="65"></div>
            </div>
            <div class="stats">
              <span>Raised: ₹60,000</span>
              <span>Goal: ₹80,000</span>
            </div>
            <button
              onclick="openDonateModal('Orphan Care')"
              class="btn btn-primary"
            >
              Donate
            </button>
          </div>
        </div>

        <!-- Elder Support Card -->
        <div class="cause-card">
          <div class="cause-image">
            <img src="elder.png" alt="Elder Support" />
          </div>
          <div class="cause-content">
            <h3>Elder Support</h3>
            <p>You're not alone; we're here for you.</p><br>
            <div class="progress-bar">
              <div class="progress" data-progress="65"></div>
            </div>
            <div class="stats">
              <span>Raised: ₹60,000</span>
              <span>Goal: ₹80,000</span>
            </div>
            <button
              onclick="openDonateModal('Orphan Care')"
              class="btn btn-primary"
            >
              Donate
            </button>
          </div>
        </div>
          </div>
        </div>
      </div>
    </section>
    <section class="signup-section">
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
    </div>
  </footer>
  </body>
</html>
