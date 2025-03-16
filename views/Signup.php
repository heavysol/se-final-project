<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/general-styles.css">
    <link rel="stylesheet" href="../assets/css/logsign-styles.css">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow-lg">
            <h4 class="text-center">Create your free account</h4>
            
            <!-- Google Sign-in button with multicolor logo -->
            <button onclick="signInWithGoogle()" class="btn btn-google w-100 my-2 position-relative">
                <div class="google-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="24px" height="24px">
                        <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
                        <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
                        <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
                        <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
                    </svg>
                </div>
                Continue with Google
            </button>
            
            <!-- SSO button -->
            <button onclick="signInWithSSO()" class="btn btn-sso w-100 mb-2">
                <i class="fas fa-key me-2"></i>Continue with SSO
            </button>
            
            <!-- GitHub button -->
            <button onclick="signInWithGitHub()" class="btn btn-github w-100 mb-3">
                <i class="fab fa-github me-2"></i>Continue with GitHub
            </button>
            
            <hr>
            
            <form id="signupForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control" placeholder="Enter first name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" placeholder="Enter last name" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" placeholder="Enter email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" placeholder="Enter password" required>
                        <span class="input-group-text" onclick="togglePassword('password')">🔒</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm password" required>
                        <span class="input-group-text" onclick="togglePassword('confirmPassword')">🔒</span>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Sign Up</button>
            </form>
            
            <p class="text-center mt-3">
                Already have an account? <a href="login.php">Log in</a>
            </p>
        </div>
    </div>
    
    <script>
        function togglePassword(id) {
            let input = document.getElementById(id);
            input.type = input.type === "password" ? "text" : "password";
        }
        
        // Google Sign In function
        function signInWithGoogle() {
            console.log("Sign in with Google initiated");
            // In a real implementation, you would redirect to Google OAuth
            // window.location.href = '/auth/google';
            alert("Redirecting to Google authentication...");
        }
        
        // SSO Sign In function
        function signInWithSSO() {
            console.log("Sign in with SSO initiated");
            // In a real implementation, you would redirect to SSO provider
            // window.location.href = '/auth/sso';
            alert("Redirecting to SSO authentication...");
        }
        
        // GitHub Sign In function
        function signInWithGitHub() {
            console.log("Sign in with GitHub initiated");
            // In a real implementation, you would redirect to GitHub OAuth
            // window.location.href = '/auth/github';
            alert("Redirecting to GitHub authentication...");
        }
        
        // Form submission
        document.getElementById('signupForm').addEventListener('submit', function(event) {
            event.preventDefault();
            console.log("Form submitted");
            alert("Form submitted successfully!");
            // In a real implementation, you would handle form submission here
        });
    </script>
</body>
</html>