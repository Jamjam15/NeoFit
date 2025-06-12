<?php
session_start();
if(isset($_SESSION['email'])){
    header('Location: landing_page.php');
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT - Login & Signup</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color:rgb(255, 255, 255);
            color: #fff;
        }

        /* Header Styles */
        header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #000000;
        }

        /* Main Content */
        main {
            flex: 1;
            padding: 0;
        }

        .content-container {
            display: flex;
            min-height: calc(100vh - 120px);
            padding: 20px;
            justify-content: center;
            align-items: center;
        }

        .image-container {
            flex: 1;
            max-width: 500%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* Single Image Style */
        .feature-image {
            width: 100%;
            max-width: 1000px;
            max-height: 800px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .auth-container {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            display: flex;
            flex-direction: column;
            background-color: #ffffff;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            margin: 20px;
            overflow-y: auto;
            height: auto;
            max-height: 90vh;
            margin-top: -50px;
        }

        .auth-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 30px;
            text-align: center;
            color: #333;
        }

        .tabs {
            display: flex;
            border-bottom: 1px solid #eaeaea;
            margin-bottom: 20px;
        }

        .tab-btn {
            flex: 1;
            background: none;
            border: none;
            padding: 10px;
            font-size: 16px;
            cursor: pointer;
            color: #999;
            transition: all 0.3s;
            position: relative;
        }

        .tab-btn.active {
            color: #333;
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #333;
        }

        .tab-content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding-bottom: 20px;
        }

        .tab-content {
            display: none;
            height: auto;
            overflow-y: auto;
            padding-right: 5px;
        }

        .tab-content.active {
            display: flex;
            flex-direction: column;
        }

        /* Add scrollbar styling */
        .tab-content::-webkit-scrollbar {
            width: 6px;
        }

        .tab-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .tab-content::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .tab-content::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .welcome-text {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            flex: 1;
        }

        .form-group {
            position: relative;
            margin-bottom: -5px;
            min-height: 60px;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #eaeaea;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
            color: #333;
        }

        .form-input:focus {
            outline: none;
            border-color: #000;
        }

        .form-footer {
            display: flex;
            justify-content: flex-end;
        }

        .forgot-link {
            font-size: 12px;
            color: #666;
            text-decoration: none;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .terms-text {
            font-size: 12px;
            color: #666;
            margin: 15px 0;
            line-height: 1.5;
        }

        .terms-text a {
            color: #333;
            text-decoration: none;
            font-weight: bold;
        }

        .terms-text a:hover {
            text-decoration: underline;
        }

        .auth-button {
            background-color: #000;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 12px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
            margin-bottom: 20px;
            width: 100%;
        }

        .auth-button:hover {
            background-color: #333;
        }

        /* Footer */
        footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
        }

        .footer-links a {
            font-size: 14px;
            color: #000000;
            text-decoration: none;
        }

        .footer-links a:hover {
            color: #fff;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .content-container {
                flex-direction: column;
                height: auto;
                padding: 10px;
            }
            
            .image-container {
                max-width: 100%;
                height: auto;
                padding: 10px;
                margin-bottom: 20px;
            }
            
            .feature-image {
                height: 350px;
            }
            
            .auth-container {
                margin-top: 20px;
                max-height: 80vh;
            }
        }

        .password-requirements {
            display: none;
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            padding: 10px;
            border: 1px solid #eaeaea;
            border-radius: 4px;
            background-color: #f9f9f9;
        }

        .requirement {
            margin: 3px 0;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .requirement.valid {
            color: #28a745;
        }

        .requirement.invalid {
            color: #dc3545;
        }

        .requirement::before {
            content: '✕';
            color: #dc3545;
        }

        .requirement.valid::before {
            content: '✓';
            color: #28a745;
        }

        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
            font-size: 14px;
            display: none;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message.pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .input-error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            font-size: 14px;
            padding: 5px;
            z-index: 2;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
        }

        .password-toggle:hover {
            color: #333;
        }

        .password-toggle i {
            font-size: 14px;
            font-style: normal;
        }

        /* Adjust positioning for signup form password fields */
        #signup-tab .password-toggle {
            top: 21px;
            transform: none;
        }

        /* Add these styles for the popup message */
        .popup-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
            text-align: center;
            min-width: 300px;
            color: #333;
        }

        .popup-message.error {
            border-left: 4px solid #dc3545;
        }

        .popup-message.success {
            border-left: 4px solid #28a745;
        }

        .popup-message .close-btn {
            position: absolute;
            right: 10px;
            top: 10px;
            cursor: pointer;
            font-size: 20px;
            color: #666;
        }

        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 999;
        }

        /* Add system message styles */
        .system-message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 12px 24px;
            border-radius: 4px;
            font-size: 14px;
            z-index: 1000;
            display: none;
            animation: slideDown 0.3s ease-out;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }

        .system-message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .system-message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        @keyframes slideDown {
            from {
                transform: translate(-50%, -100%);
                opacity: 0;
            }
            to {
                transform: translate(-50%, 0);
                opacity: 1;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">NEOFIT</div>
        </div>
    </header>

    <main>
        <div class="content-container">
            <div class="image-container">
                <!-- Single image instead of slider -->
                <img class="feature-image" src="Login Images/lgfront.png" alt="Fashion models in NEOFIT clothing">
            </div>
            
            <div class="auth-container">
                <h2 class="auth-title">NEOFIT ACCOUNT</h2>
                
                <div class="tabs">
                    <button class="tab-btn active" data-tab="login">Login</button>
                    <button class="tab-btn" data-tab="signup">Sign Up</button>
                </div>
                
                <div class="tab-content-wrapper">
                    <!-- LOGIN FORM -->
                    <div class="tab-content active" id="login-tab">
                        <p class="welcome-text">Welcome back! Log in using your email and password.</p>
                        <form class="auth-form" method="post" action="login_backend.php">
                            <div class="form-group">
                                <input type="email" id="email" name="email" class="form-input" placeholder="Email">
                            </div>
                            <div class="form-group">
                                <input type="password" id="password" name="password" class="form-input" placeholder="Password">
                                <span class="password-toggle" onmousedown="showPassword('password')" onmouseup="hidePassword('password')" onmouseleave="hidePassword('password')"><i class="fa-solid fa-eye-slash"></i></span>
                            </div>
                            <div class="form-footer">
                                <a href="forgot_password_new.php" class="forgot-link">Forgot Password?</a>
                            </div>
                            <div class="terms-text">
                                By signing up, I accept NeoFit's <a href="#" onclick="openPopup('privacy&policy.php');return false;">Privacy Policy</a> and <a href="#" onclick="openPopup('legal_statement.php');return false;">Legal Statement</a>
                            </div>
                            <button type="submit" class="auth-button" name="login_submit">Login</button>
                        </form>
                    </div>
                    <!-- END OF LOGIN FORM -->
                    
                    <!-- SIGNUP FORM -->
                    <div class="tab-content" id="signup-tab">
                        <p class="welcome-text">Create your NeoFit account to get started.</p>
                        <form class="auth-form" id="signup-form" method="POST" action="signup_backend.php">
                            <div class="form-group">
                                <input type="text" id="first_name" name="first_name" class="form-input" placeholder="First Name" 
                                    pattern="[A-Za-z ]+" title="First name can only contain letters and spaces" required>
                                <div class="input-error" id="first-name-error"></div>
                            </div>
                            <div class="form-group">
                                <input type="text" id="last_name" name="last_name" class="form-input" placeholder="Last Name" 
                                    pattern="[A-Za-z ]+" title="Last name can only contain letters and spaces" required>
                                <div class="input-error" id="last-name-error"></div>
                            </div>
                            <div class="form-group">
                                <input type="email" id="signup-email" name="email" class="form-input" placeholder="Email" required>
                            </div>
                            <div class="form-group">
                                <input type="password" id="signup-password" name="password" class="form-input" placeholder="Password" required>
                                <span class="password-toggle" onmousedown="showPassword('signup-password')" onmouseup="hidePassword('signup-password')" onmouseleave="hidePassword('signup-password')"><i class="fa-solid fa-eye-slash"></i></span>
                                <div class="password-requirements" id="password-requirements">
                                    <div class="requirement" id="length">At least 8 characters long</div>
                                    <div class="requirement" id="letter">Contains at least one letter</div>
                                    <div class="requirement" id="number">Contains at least one number</div>
                                    <div class="requirement" id="special">Contains at least one special character</div>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="password" id="confirm-password" name="confirm_password" class="form-input" placeholder="Confirm Password" required>
                                <span class="password-toggle" onmousedown="showPassword('confirm-password')" onmouseup="hidePassword('confirm-password')" onmouseleave="hidePassword('confirm-password')"><i class="fa-solid fa-eye-slash"></i></span>
                            </div>
                            <div class="form-group">
                                <label for="security_question" style="display: block; margin-bottom: 5px; color: #666; font-size: 14px;">Security Question</label>
                                <select id="security_question" name="security_question" class="form-input" required>
                                    <option value="fav_food">What is your favorite food?</option>
                                    <option value="fav_color">What is your favorite color?</option>
                                    <option value="first_pet">What was your first pet's name?</option>
                                    <option value="fav_flower">What is your favorite flower?</option>
                                    <option value="fav_place">What is your favorite place?</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="security_answer" style="display: block; margin-bottom: 5px; color: #666; font-size: 14px;">Your Answer</label>
                                <input type="text" id="security_answer" name="security_answer" class="form-input" required>
                            </div>
                            <div id="signup-message" class="message" style="display: none;"></div>
                            <div class="terms-text">
                                By signing up, I accept NeoFit's <a href="#" onclick="openPopup('privacy&policy.php');return false;">Privacy Policy</a> and <a href="#" onclick="openPopup('legal_statement.php');return false;">Legal Statement</a>
                            </div>
                            <input type="hidden" name="signup_submit" value="1">
                            <button type="submit" class="auth-button">Sign Up</button>
                        </form>
                    </div>
                    <!-- END OF SIGNUP FORM -->
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <!-- Add this right after the <body> tag -->
    <div class="popup-overlay" id="popupOverlay"></div>
    <div class="popup-message" id="popupMessage">
        <span class="close-btn" onclick="closePopup()">&times;</span>
        <p id="popupText"></p>
    </div>

    <!-- Add this after the header -->
    <div id="systemMessage" class="system-message"></div>

    <!-- Modal for policy display -->
    <div id="policyModal" class="modal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;">
      <div class="modal-content" style="background:#fff;max-width:700px;width:90vw;max-height:80vh;overflow:auto;border-radius:8px;position:relative;padding:30px 20px;">
        <span id="closePolicyModal" style="position:absolute;top:10px;right:18px;font-size:2rem;cursor:pointer;">&times;</span>
        <div id="policyModalBody">Loading...</div>
      </div>
    </div>

    <script>
        // Tab switching functionality
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove active class from all tabs
                tabBtns.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab
                btn.classList.add('active');
                const tabId = btn.getAttribute('data-tab');
                document.getElementById(`${tabId}-tab`).classList.add('active');
            });
        });

        // Password validation
        const passwordInput = document.getElementById('signup-password');
        const requirements = document.getElementById('password-requirements');
        const length = document.getElementById('length');
        const letter = document.getElementById('letter');
        const number = document.getElementById('number');
        const special = document.getElementById('special');

        // Show password requirements when password field is focused
        passwordInput.addEventListener('focus', function() {
            requirements.style.display = 'block';
        });

        // Hide password requirements when password field loses focus
        passwordInput.addEventListener('blur', function() {
            requirements.style.display = 'none';
        });

        // Check password requirements in real-time
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            
            // Check length
            if(password.length >= 8) {
                length.classList.add('valid');
            } else {
                length.classList.remove('valid');
            }
            
            // Check for letters
            if(/[a-zA-Z]/.test(password)) {
                letter.classList.add('valid');
            } else {
                letter.classList.remove('valid');
            }
            
            // Check for numbers
            if(/[0-9]/.test(password)) {
                number.classList.add('valid');
            } else {
                number.classList.remove('valid');
            }
            
            // Check for special characters
            if(/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                special.classList.add('valid');
            } else {
                special.classList.remove('valid');
            }
        });

        // Name validation
        const firstNameInput = document.getElementById('first_name');
        const lastNameInput = document.getElementById('last_name');
        const firstNameError = document.getElementById('first-name-error');
        const lastNameError = document.getElementById('last-name-error');

        function validateName(input, errorDiv) {
            const nameRegex = /^[A-Za-z ]+$/;
            const value = input.value.trim();
            
            if (!nameRegex.test(value) && value !== '') {
                errorDiv.textContent = input.title;
                errorDiv.style.display = 'block';
                input.setCustomValidity(input.title);
            } else {
                errorDiv.style.display = 'none';
                input.setCustomValidity('');
            }
        }

        firstNameInput.addEventListener('input', function() {
            validateName(this, firstNameError);
        });

        lastNameInput.addEventListener('input', function() {
            validateName(this, lastNameError);
        });

        // Add these new popup functions
        function showPopup(message, type = 'error') {
            const popup = document.getElementById('popupMessage');
            const overlay = document.getElementById('popupOverlay');
            const popupText = document.getElementById('popupText');
            
            popup.className = 'popup-message ' + type;
            popupText.textContent = message;
            
            popup.style.display = 'block';
            overlay.style.display = 'block';
            
            // Auto close after 3 seconds for success messages
            if (type === 'success') {
                setTimeout(() => {
                    closePopup();
                }, 3000);
            }
        }

        function closePopup() {
            document.getElementById('popupMessage').style.display = 'none';
            document.getElementById('popupOverlay').style.display = 'none';
        }

        // Add system message functions
        function showSystemMessage(message, type = 'error') {
            const msgElement = document.getElementById('systemMessage');
            msgElement.textContent = message;
            msgElement.className = 'system-message ' + type;
            msgElement.style.display = 'block';

            // Auto hide after 3 seconds
            setTimeout(() => {
                msgElement.style.display = 'none';
            }, 3000);
        }

        // Modify the signup form submission handler
        document.getElementById('signup-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitButton = this.querySelector('button[type="submit"]');
            const password = document.getElementById('signup-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            
            // Validate names
            const firstNameValid = /^[A-Za-z ]+$/.test(firstNameInput.value.trim());
            const lastNameValid = /^[A-Za-z ]+$/.test(lastNameInput.value.trim());

            if (!firstNameValid || !lastNameValid) {
                showSystemMessage('Names can only contain letters and spaces.');
                return;
            }
            
            // Check if passwords match
            if (password !== confirmPassword) {
                showSystemMessage('Passwords do not match.');
                return;
            }
            
            // Check if all password requirements are met
            const requirements = document.querySelectorAll('.requirement');
            let allValid = true;
            requirements.forEach(req => {
                if (!req.classList.contains('valid')) {
                    allValid = false;
                }
            });
            
            if (!allValid) {
                showSystemMessage('Please meet all password requirements.');
                return;
            }
            
            // Disable submit button and show loading state
            submitButton.disabled = true;
            submitButton.textContent = 'Signing up...';
            
            // Send form data
            fetch('signup_backend.php', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showSystemMessage(data.message, 'success');
                    // Clear form
                    this.reset();
                    requirements.forEach(req => req.classList.remove('valid'));
                    
                    // Switch to login tab after successful signup
                    setTimeout(() => {
                        document.querySelector('[data-tab="login"]').click();
                    }, 1500);
                } else {
                    showSystemMessage(data.message);
                }
            })
            .catch(error => {
                showSystemMessage('An error occurred. Please try again.');
            })
            .finally(() => {
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.textContent = 'Sign Up';
            });
        });

        // Update the login form submission code
        document.querySelector('#login-tab form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            
            // Disable submit button and show loading state
            submitButton.disabled = true;
            submitButton.textContent = 'Logging in...';
            
            fetch('login_backend.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // For admin login
                    if (data.message.includes('Admin')) {
                        window.location.href = 'Admin Pages/all_product_page.php';
                    } else {
                        // For regular user login
                        window.location.href = 'landing_page.php';
                    }
                } else {
                    showSystemMessage(data.message);
                }
            })
            .catch(error => {
                console.error('Login error:', error);
                showSystemMessage('An error occurred during login. Please try again.');
            })
            .finally(() => {
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.textContent = 'Login';
            });
        });

        // Replace the old togglePassword function with these two functions
        function showPassword(inputId) {
            const input = document.getElementById(inputId);
            const toggle = input.nextElementSibling;
            input.type = 'text';
            toggle.innerHTML = '<i class="fa-solid fa-eye"></i>';
        }

        function hidePassword(inputId) {
            const input = document.getElementById(inputId);
            const toggle = input.nextElementSibling;
            input.type = 'password';
            toggle.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
        }

        document.querySelectorAll('.open-policy').forEach(link => {
          link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('data-policy');
            const modal = document.getElementById('policyModal');
            const body = document.getElementById('policyModalBody');
            modal.style.display = 'flex';
            body.innerHTML = 'Loading...';
            fetch(url)
              .then(res => res.text())
              .then(html => {
                // Extract just the <body> content if possible
                const match = html.match(/<body[^>]*>([\s\S]*)<\/body>/i);
                body.innerHTML = match ? match[1] : html;
              })
              .catch(() => { body.innerHTML = 'Failed to load content.'; });
          });
        });
        document.getElementById('closePolicyModal').onclick = function() {
          document.getElementById('policyModal').style.display = 'none';
        };
        window.onclick = function(event) {
          const modal = document.getElementById('policyModal');
          if (event.target === modal) modal.style.display = 'none';
        };

        function openPopup(url) {
            window.open(url, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
        }
    </script>
</body>
</html>