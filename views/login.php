<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/general-styles.css">
    <link rel="stylesheet" href="../assets/css/logsign-styles.css">
    
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow-lg">
            <h4 class="text-center">Log in</h4>
            
            <!-- Google Sign-in button with multicolor logo -->
            <button type="button" id="googleBtn" class="btn btn-google oauth-btn w-100 my-2">
                <span class="google-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="24px" height="24px">
                        <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
                        <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
                        <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
                        <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
                    </svg>
                </span>
                Continue with Google
            </button>
            
            <!-- SSO button -->
            <button type="button" id="ssoBtn" class="btn btn-sso oauth-btn w-100 mb-2">
                <i class="fas fa-key me-2"></i>Continue with SSO
            </button>
            
            <!-- GitHub button -->
            <button type="button" id="githubBtn" class="btn btn-github oauth-btn w-100 mb-3">
                <i class="fab fa-github me-2"></i>Continue with GitHub
            </button>
            
            <hr>
            
            <!-- Login Form -->
            <form id="loginForm">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" placeholder="Enter email" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                        <span class="input-group-text toggle-password" data-target="password">🔓</span>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Log in</button>
            </form>

            <p class="text-center mt-3">
                <a href="#">Forgot password?</a> | <a href="Signup.php">Sign up</a>
            </p>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // DOM Elements
        const loginForm = document.getElementById('loginForm');
        const togglePasswordBtns = document.querySelectorAll('.toggle-password');
        const oauthButtons = {
            google: document.getElementById('googleBtn'),
            sso: document.getElementById('ssoBtn'),
            github: document.getElementById('githubBtn')
        };
        
        // Password Toggle Functionality
        togglePasswordBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordField = document.getElementById(targetId);
                
                // Toggle password visibility
                const type = passwordField.type === 'password' ? 'text' : 'password';
                passwordField.type = type;
            });
        });
        
        // OAuth Sign-in Handlers
        oauthButtons.google.addEventListener('click', function() {
            console.log("Google sign-in initiated");
            // In production: window.location.href = '/auth/google';
            alert("Redirecting to Google authentication...");
        });
        
        oauthButtons.sso.addEventListener('click', function() {
            console.log("SSO sign-in initiated");
            // In production: window.location.href = '/auth/sso';
            alert("Redirecting to SSO authentication...");
        });
        
        oauthButtons.github.addEventListener('click', function() {
            console.log("GitHub sign-in initiated");
            // In production: window.location.href = '/auth/github';
            alert("Redirecting to GitHub authentication...");
        });
        
        // Form Submission
        loginForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            const loginData = {};
            
            for (const [key, value] of formData.entries()) {
                loginData[key] = value;
            }
            
            console.log("Login attempt:", loginData);
            alert("Login form submitted!");
            
            // In production: Submit the form data to server
            // fetch('/api/login', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify(loginData)
            // }).then(response => response.json())
            //   .then(data => {
            //     if (data.success) {
            //       window.location.href = '/dashboard';
            //     }
            //   });
        });
    </script>
</body>
</html>