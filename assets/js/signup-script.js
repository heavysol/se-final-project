/* This script handles behaviour of signup page */

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
    // Get form values
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    // Validate password match
    if (password !== confirmPassword) {
        event.preventDefault();
        alert('Passwords do not match!');
        return;
    }
    
    // Validate password length
    if (password.length < 8) {
        event.preventDefault();
        alert('Password must be at least 8 characters long!');
        return;
    }
    
    // If all validations pass, allow form to submit
    console.log("Form validation passed, submitting...");
});